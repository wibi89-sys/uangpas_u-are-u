-------------------------------------------------------------------

                           DigitalPersona
                        U.are.U SDK for Windows

                           Version 2.3.1
                           June 23, 2015
				
      (c) 1996-2015 DigitalPersona, Inc. All Rights Reserved. 
-------------------------------------------------------------------

This document provides late-breaking or other information that supplements the DigitalPersona U.are.U SDK for Windows documentation.


-------------------------
How to Use This Document
-------------------------

To view the Readme file on-screen in Windows Notepad, maximize the Notepad window. On the Format menu, click Word Wrap. To print the Readme file, open it in Notepad or another word processor, and then use the Print command on the File menu.


---------
CONTENTS
---------

1.   INSTALLATION
2.   COMPATIBILITY
3.   SYSTEM REQUIREMENTS
4.   RELEASE NOTES
5.   KNOWN ISSUES
6.   SUPPORT AND FEEDBACK

 
----------------
1. INSTALLATION
----------------

You must have local administrator rights to install this product on supported Windows systems.

U.are.U SDK for Windows 2.3.1
1- Open/load the U.are.U SDK for Windows product package
2- Run SDK\x86\setup.exe OR SDK\x64\setup.exe located in the SDK folder
3- Follow the installation instructions
4- Connect the U.are.U Fingerprint Reader

-----------------
2. COMPATIBILITY
-----------------

DigitalPersona U.are.U SDK for Windows v2.3.1 is compatible with the following DigitalPersona products:

- DigitalPersona Pro for AD Workstation 5.5.0 and later
- DigitalPersona Pro for AD Kiosk 5.5.0 and later
- TouchChip Device Add-On for U.are.U SDK for Windows v1.0.1

Fingerprint templates produced by the U.are.U SDK for Windows are also compatible with templates from the following DigitalPersona SDKs:

- Gold SDK
- Gold CE SDK
- One Touch for Windows SDK, all previous versions
- One Touch for Linux SDK, all distributions

Platinum SDK registration templates must be converted to a compatible format to work with U.are.U SDK for Windows v2.3.1. See the Developer Guide for instructions on performing this conversion. 

DigitalPersona U.are.U SDK for Windows v2.3.1 is not compatible with any other DigitalPersona product.


-----------------------
3. SYSTEM REQUIREMENTS
-----------------------

- Microsoft Windows XP (32-bit), Microsoft Windows 7 (32/64-bit) or Microsoft Windows 8.1 (32/64-bit)
- JRE or JDK (1.6 or 1.7) - To run samples and completed applications if developing in Java
- .NET Framework v3.5 or newer - To run samples and develop applications for .NET
- USB port on the computer where the fingerprint reader is to be connected


-----------------
4. RELEASE NOTES
-----------------

4.1 This release supports the following APIs:

- C++
- .NET
- ActiveX  
- Java


4.2 SDK and RTE cannot be installed on the same computer.

4.3 Image enhancement, for 5xxx devices, is incorporated in this release.

4.4 Only U.are.U 5xxx devices will support streaming mode


------------------
5.   KNOWN ISSUES
------------------


5.1 tabclt32.ocx needs to be manually copied and registered to run the OPOS sample with RTE installation.

5.2 Path of jpos.properties needs to be set to run the JavaPos sample with RTE installation.

5.3 WSQ and NFIQ have been introduced into .NET wrappers. CSharp samples from previous release need to be rebuilt against new assemblies.

5.4 Effective with the v2.3.1 release of U.are.U SDK for Windows, only U.are.U 5xxx devices support streaming mode. Streaming mode is disabled for the U.are.U 45xx devices.

5.5 TouchChip Device Add-on must be manually uninstalled prior to uninstalling U.are.U SDK/RTE for Windows v2.3.1.

5.6 Upgrading from a prior version of U.are.U SDK/RTE for Windows to a service-less install of U.are.U SDK/RTE for Windows v2.3.1 is not supported. An error message will popup for such upgrade.


------------------------
6. SUPPORT AND FEEDBACK
------------------------

The latest version of documentation is available at http://www.crossmatch.com/Support/Reference-Material/
Free technical support is available through the Crossmatch Developer WebPortal at http://devportal.digitalpersona.com.
You can also purchase a Developer Support package at our web store: http://www.crossmatch.com


