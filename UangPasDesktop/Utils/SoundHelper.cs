using System;
using System.IO;
using System.Media;
using System.Threading;

namespace UangPasDesktop
{
    public static class SoundHelper
    {
        private static bool soundEnabled = true;

        public static bool SoundEnabled
        {
            get { return soundEnabled; }
            set { soundEnabled = value; }
        }

        // Generate in-memory WAV byte array for pure synthesized sine wave tones
        private static byte[] GenerateToneWav(int[] frequencies, int[] durations, double volume)
        {
            const int sampleRate = 44100;
            const short bitsPerSample = 16;
            const short channels = 1;

            using (MemoryStream ms = new MemoryStream())
            using (BinaryWriter bw = new BinaryWriter(ms))
            {
                int totalSamples = 0;
                for (int t = 0; t < frequencies.Length; t++)
                {
                    totalSamples += (int)(sampleRate * (durations[t] / 1000.0));
                }

                int dataChunkSize = totalSamples * channels * (bitsPerSample / 8);

                // RIFF Header
                bw.Write(new char[] { 'R', 'I', 'F', 'F' });
                bw.Write(36 + dataChunkSize);
                bw.Write(new char[] { 'W', 'A', 'V', 'E' });

                // fmt chunk
                bw.Write(new char[] { 'f', 'm', 't', ' ' });
                bw.Write(16); // subchunk1 size
                bw.Write((short)1); // PCM format
                bw.Write(channels);
                bw.Write(sampleRate);
                bw.Write(sampleRate * channels * (bitsPerSample / 8));
                bw.Write((short)(channels * (bitsPerSample / 8)));
                bw.Write(bitsPerSample);

                // data chunk
                bw.Write(new char[] { 'd', 'a', 't', 'a' });
                bw.Write(dataChunkSize);

                // Write samples with envelope smoothing
                for (int t = 0; t < frequencies.Length; t++)
                {
                    int freq = frequencies[t];
                    int dur = durations[t];
                    int count = (int)(sampleRate * (dur / 1000.0));
                    double maxAmp = 32767.0 * Math.Max(0.05, Math.Min(1.0, volume));

                    for (int i = 0; i < count; i++)
                    {
                        double tSec = (double)i / sampleRate;
                        double envelope = 1.0;
                        if (i > count - 200) envelope = (double)(count - i) / 200.0;
                        else if (i < 100) envelope = (double)i / 100.0;

                        short sample = (short)(Math.Sin(2.0 * Math.PI * freq * tSec) * maxAmp * envelope);
                        bw.Write(sample);
                    }
                }

                return ms.ToArray();
            }
        }

        private static void PlayTonesAsync(int[] freqs, int[] durs, double vol)
        {
            if (!soundEnabled) return;
            ThreadPool.QueueUserWorkItem(delegate(object state)
            {
                try
                {
                    byte[] wav = GenerateToneWav(freqs, durs, vol);
                    using (MemoryStream ms = new MemoryStream(wav))
                    using (SoundPlayer player = new SoundPlayer(ms))
                    {
                        player.PlaySync();
                    }
                }
                catch
                {
                    try { SystemSounds.Beep.Play(); } catch { }
                }
            });
        }

        // Pleasant 2-tone melodic chime for transaction success & fingerprint match (G5 -> C6)
        public static void PlaySuccess()
        {
            PlayTonesAsync(new int[] { 784, 1046 }, new int[] { 90, 160 }, 0.45);
        }

        // Crisp scanner beep when item is added to cart (A6)
        public static void PlayBarcodeScan()
        {
            PlayTonesAsync(new int[] { 1760 }, new int[] { 45 }, 0.35);
        }

        // Soft blip when finger touches the scanner
        public static void PlayTouch()
        {
            PlayTonesAsync(new int[] { 1200 }, new int[] { 30 }, 0.25);
        }

        // Gentle warning chord (insufficient balance or limit reached)
        public static void PlayWarning()
        {
            PlayTonesAsync(new int[] { 523, 415 }, new int[] { 120, 150 }, 0.45);
        }

        // Low buzz error sound (A3 -> G3)
        public static void PlayError()
        {
            PlayTonesAsync(new int[] { 220, 196 }, new int[] { 140, 180 }, 0.5);
        }
    }
}
