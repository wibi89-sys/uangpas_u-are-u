<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SolutionFingerprintService
{
    /**
     * Test connection to Solution X100C / ZKTeco device
     *
     * @param string $ip
     * @param int $port
     * @param int $timeout
     * @return array
     */
    public function testConnection(string $ip, int $port = 80, int $timeout = 3): array
    {
        $startTime = microtime(true);
        $ip = trim($ip);

        // 1. Quick TCP Socket check
        $socket = @fsockopen($ip, $port, $errno, $errstr, $timeout);
        if (!$socket) {
            return [
                'success' => false,
                'message' => "Gagal terhubung ke {$ip}:{$port}. Periksa kabel LAN, daya listrik mesin, atau IP address ({$errstr})",
                'latency_ms' => null,
            ];
        }
        fclose($socket);

        // 2. HTTP SOAP Handshake
        try {
            $soapXml = '<GetAttLog><ArgComKey xsi:type="xsd:integer">0</ArgComKey><Arg><PIN xsi:type="xsd:integer">0</PIN></Arg></GetAttLog>';
            $response = $this->sendSoapRequest($ip, $soapXml, $port, $timeout);
            $latency = round((microtime(true) - $startTime) * 1000);

            if ($response && (str_contains($response, 'GetAttLogResponse') || str_contains($response, '<Row>') || str_contains($response, 'iWsService'))) {
                return [
                    'success' => true,
                    'message' => "Terhubung! Mesin Solution X100C di {$ip}:{$port} aktif dan merespon protokol SOAP.",
                    'latency_ms' => $latency,
                ];
            }

            return [
                'success' => true,
                'message' => "Port {$port} terbuka pada IP {$ip}. Respon diterima dalam {$latency} ms.",
                'latency_ms' => $latency,
            ];
        } catch (Exception $e) {
            $latency = round((microtime(true) - $startTime) * 1000);
            return [
                'success' => true,
                'message' => "Port {$port} aktif ({$latency} ms), namun SOAP error: " . $e->getMessage(),
                'latency_ms' => $latency,
            ];
        }
    }

    /**
     * Get attendance/tap logs from Solution device
     *
     * @param string $ip
     * @param int $comKey
     * @param int|null $filterPin
     * @param int $port
     * @return array
     */
    public function getAttendanceLogs(string $ip, int $comKey = 0, ?int $filterPin = null, int $port = 80): array
    {
        $innerXml = ($filterPin !== null)
            ? "<Arg><PIN xsi:type=\"xsd:integer\">{$filterPin}</PIN></Arg>"
            : "<Arg><PIN xsi:type=\"xsd:integer\">All</PIN></Arg>";

        $soapBody = "<GetAttLog>
            <ArgComKey xsi:type=\"xsd:integer\">{$comKey}</ArgComKey>
            {$innerXml}
        </GetAttLog>";

        $xmlString = $this->sendSoapRequest($ip, $soapBody, $port);
        if (!$xmlString) {
            return [];
        }

        return $this->parseAttLogXml($xmlString);
    }

    /**
     * Clear all attendance logs from Solution device memory
     *
     * @param string $ip
     * @param int $comKey
     * @param int $port
     * @return bool
     */
    public function clearAttendanceLogs(string $ip, int $comKey = 0, int $port = 80): bool
    {
        $soapBody = "<ClearData>
            <ArgComKey xsi:type=\"xsd:integer\">{$comKey}</ArgComKey>
            <Value xsi:type=\"xsd:integer\">3</Value>
        </ClearData>";

        $response = $this->sendSoapRequest($ip, $soapBody, $port);
        return $response && str_contains($response, '<ClearDataResponse>');
    }

    /**
     * Send inmate info (PIN and Name) to Solution X100C device
     *
     * @param string $ip
     * @param string|int $pin
     * @param string $name
     * @param int $comKey
     * @param int $port
     * @return bool
     */
    public function setUserInfo(string $ip, $pin, string $name, int $comKey = 0, int $port = 80): bool
    {
        // Clean name max 24 chars for standard Solution LCD
        $cleanName = substr(preg_replace('/[^A-Za-z0-9\s]/', '', $name), 0, 24);

        $soapBody = "<SetUserInfo>
            <ArgComKey xsi:type=\"xsd:integer\">{$comKey}</ArgComKey>
            <Arg>
                <PIN>{$pin}</PIN>
                <Name>{$cleanName}</Name>
                <Password></Password>
                <Privilege>0</Privilege>
                <Card></Card>
            </Arg>
        </SetUserInfo>";

        $response = $this->sendSoapRequest($ip, $soapBody, $port);
        return $response && str_contains($response, '<SetUserInfoResponse>');
    }

    /**
     * Download biometric fingerprint template from Solution device
     *
     * @param string $ip
     * @param string|int $pin
     * @param int $fingerId (0-9, default 0 for right thumb/index)
     * @param int $comKey
     * @param int $port
     * @return string|null Base64 biometric template string
     */
    public function getUserTemplate(string $ip, $pin, int $fingerId = 0, int $comKey = 0, int $port = 80): ?string
    {
        $soapBody = "<GetUserTemplate>
            <ArgComKey xsi:type=\"xsd:integer\">{$comKey}</ArgComKey>
            <PIN>{$pin}</PIN>
            <FingerID>{$fingerId}</FingerID>
        </GetUserTemplate>";

        $response = $this->sendSoapRequest($ip, $soapBody, $port);
        if (!$response) return null;

        // Extract <Template>...</Template>
        if (preg_match('/<Template>(.*?)<\/Template>/s', $response, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Upload biometric fingerprint template into Solution device memory
     *
     * @param string $ip
     * @param string|int $pin
     * @param int $fingerId
     * @param string $templateBase64
     * @param int $comKey
     * @param int $port
     * @return bool
     */
    public function setUserTemplate(string $ip, $pin, int $fingerId, string $templateBase64, int $comKey = 0, int $port = 80): bool
    {
        $soapBody = "<SetUserTemplate>
            <ArgComKey xsi:type=\"xsd:integer\">{$comKey}</ArgComKey>
            <PIN>{$pin}</PIN>
            <FingerID>{$fingerId}</FingerID>
            <Template>{$templateBase64}</Template>
        </SetUserTemplate>";

        $response = $this->sendSoapRequest($ip, $soapBody, $port);
        return $response && str_contains($response, '<SetUserTemplateResponse>');
    }

    /**
     * Send raw HTTP POST to /iWsService
     */
    protected function sendSoapRequest(string $ip, string $bodyXml, int $port = 80, int $timeout = 4): ?string
    {
        $url = "http://{$ip}:{$port}/iWsService";

        try {
            $response = Http::timeout($timeout)
                ->withHeaders([
                    'Content-Type' => 'text/xml',
                ])
                ->withBody($bodyXml, 'text/xml')
                ->post($url);

            if ($response->successful()) {
                return $response->body();
            }

            Log::warning("Solution X100C request failed: HTTP {$response->status()} from {$url}");
            return null;
        } catch (Exception $e) {
            Log::warning("Solution X100C communication error to {$url}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Parse SOAP XML response from Solution X100C GetAttLog
     */
    protected function parseAttLogXml(string $xmlContent): array
    {
        $logs = [];

        try {
            // Using SimpleXML for clean parsing
            $cleanXml = preg_replace('/(<\/?)(\w+):([^>]*>)/', '$1$2$3', $xmlContent);
            $xml = @simplexml_load_string($cleanXml);

            if ($xml && isset($xml->Row)) {
                foreach ($xml->Row as $row) {
                    $pin = isset($row->PIN) ? (string)$row->PIN : '';
                    $dateTime = isset($row->DateTime) ? (string)$row->DateTime : '';
                    $verified = isset($row->Verified) ? (int)$row->Verified : 1;
                    $status = isset($row->Status) ? (int)$row->Status : 0;

                    if (!empty($pin)) {
                        $logs[] = [
                            'pin' => $pin,
                            'datetime' => $dateTime,
                            'verified' => $verified,
                            'status' => $status,
                        ];
                    }
                }
            } else {
                // Regex fallback parser if XML namespace issue occurs
                if (preg_match_all('/<Row>(.*?)<\/Row>/s', $xmlContent, $rowMatches)) {
                    foreach ($rowMatches[1] as $r) {
                        preg_match('/<PIN>(.*?)<\/PIN>/', $r, $pinMatch);
                        preg_match('/<DateTime>(.*?)<\/DateTime>/', $r, $dateMatch);
                        preg_match('/<Verified>(.*?)<\/Verified>/', $r, $verMatch);

                        $p = isset($pinMatch[1]) ? trim($pinMatch[1]) : '';
                        $d = isset($dateMatch[1]) ? trim($dateMatch[1]) : '';
                        $v = isset($verMatch[1]) ? (int)$verMatch[1] : 1;

                        if (!empty($p)) {
                            $logs[] = [
                                'pin' => $p,
                                'datetime' => $d,
                                'verified' => $v,
                                'status' => 0,
                            ];
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Log::error("Error parsing Solution X100C XML log: " . $e->getMessage());
        }

        return $logs;
    }
}
