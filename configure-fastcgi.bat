@echo off
echo Configuring FastCGI for PHP in IIS...

REM Set variables
set PHP_PATH=C:\Program Files\PHP\v8.0
set SITE_NAME=Imobilien
set SITE_PATH=C:\inetpub\Sites\Imobilien

REM Remove existing FastCGI configuration
%windir%\system32\inetsrv\appcmd.exe delete config -section:system.webServer/fastCgi /commit:apphost
%windir%\system32\inetsrv\appcmd.exe delete config -section:system.webServer/handlers /commit:apphost

REM Add FastCGI application
%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /+[fullPath='%PHP_PATH%\php-cgi.exe'].environmentVariables.[name='PHP_FCGI_MAX_REQUESTS',value='10000'] /commit:apphost
%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /+[fullPath='%PHP_PATH%\php-cgi.exe'].environmentVariables.[name='PHPRC',value='%PHP_PATH%'] /commit:apphost

REM Configure FastCGI settings
%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /[fullPath='%PHP_PATH%\php-cgi.exe'].instanceMaxRequests:"10000" /commit:apphost
%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /[fullPath='%PHP_PATH%\php-cgi.exe'].monitorChangesTo:"%PHP_PATH%\php.ini" /commit:apphost
%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /[fullPath='%PHP_PATH%\php-cgi.exe'].activityTimeout:"300" /commit:apphost
%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /[fullPath='%PHP_PATH%\php-cgi.exe'].requestTimeout:"300" /commit:apphost
%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /[fullPath='%PHP_PATH%\php-cgi.exe'].queueLength:"1000" /commit:apphost

REM Add handler mapping
%windir%\system32\inetsrv\appcmd.exe set config /section:system.webServer/handlers /+[name='PHP_via_FastCGI',path='*.php',verb='*',modules='FastCgiModule',scriptProcessor='%PHP_PATH%\php-cgi.exe',resourceType='Either'] /commit:apphost

REM Configure site specific settings
%windir%\system32\inetsrv\appcmd.exe set config "%SITE_NAME%" -section:system.webServer/handlers /+[name='PHP_via_FastCGI',path='*.php',verb='*',modules='FastCgiModule',scriptProcessor='%PHP_PATH%\php-cgi.exe',resourceType='Either'] /commit:apphost

REM Set FastCGI process model
%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /[fullPath='%PHP_PATH%\php-cgi.exe'].maxInstances:"0" /commit:apphost

REM Verify PHP installation
echo Verifying PHP installation...
"%PHP_PATH%\php-cgi.exe" -v

REM Create test file
echo ^<?php phpinfo(); ?^> > "%SITE_PATH%\test.php"

REM Set permissions
icacls "%PHP_PATH%" /grant "IIS APPPOOL\%SITE_NAME%":(OI)(CI)RX /T
icacls "%SITE_PATH%" /grant "IIS APPPOOL\%SITE_NAME%":(OI)(CI)M /T

REM Reset IIS
echo Resetting IIS...
iisreset

echo.
echo FastCGI configuration complete!
echo Please try accessing http://localhost/test.php
echo.
pause 