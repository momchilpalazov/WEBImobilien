@echo off
echo Checking IIS Configuration...

REM Check IIS Installation
echo Checking IIS Features...
dism /online /get-featureinfo /featurename:IIS-WebServerRole
dism /online /get-featureinfo /featurename:IIS-CGI
dism /online /get-featureinfo /featurename:IIS-FastCGI

REM Check PHP Installation
echo.
echo Checking PHP Installation...
if exist "C:\Program Files\PHP\v8.0\php-cgi.exe" (
    echo PHP CGI found
    "C:\Program Files\PHP\v8.0\php-cgi.exe" -v
) else (
    echo PHP CGI not found
)

REM Check FastCGI Configuration
echo.
echo Checking FastCGI Configuration...
%windir%\system32\inetsrv\appcmd.exe list config /section:fastCgi

REM Check Handler Mappings
echo.
echo Checking Handler Mappings...
%windir%\system32\inetsrv\appcmd.exe list config /section:handlers

REM Check Application Pool
echo.
echo Checking Application Pool...
%windir%\system32\inetsrv\appcmd.exe list apppool /name:Imobilien

REM Check Website
echo.
echo Checking Website...
%windir%\system32\inetsrv\appcmd.exe list site /name:Imobilien

REM Check Permissions
echo.
echo Checking Permissions...
echo Site Path Permissions:
icacls "C:\inetpub\Sites\Imobilien"
echo.
echo PHP Path Permissions:
icacls "C:\Program Files\PHP\v8.0"

echo.
echo IIS Configuration check complete!
echo.
pause 