@echo off
echo Configuring existing PHP installation...

REM Set variables
set PHP_PATH=C:\php
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

REM Add handler mapping
%windir%\system32\inetsrv\appcmd.exe set config /section:system.webServer/handlers /+[name='PHP_via_FastCGI',path='*.php',verb='*',modules='FastCgiModule',scriptProcessor='%PHP_PATH%\php-cgi.exe',resourceType='Either'] /commit:apphost

REM Configure site specific settings
%windir%\system32\inetsrv\appcmd.exe set config "%SITE_NAME%" -section:system.webServer/handlers /+[name='PHP_via_FastCGI',path='*.php',verb='*',modules='FastCgiModule',scriptProcessor='%PHP_PATH%\php-cgi.exe',resourceType='Either'] /commit:apphost

REM Create required directories
if not exist "%SITE_PATH%\storage" mkdir "%SITE_PATH%\storage"
if not exist "%SITE_PATH%\storage\logs" mkdir "%SITE_PATH%\storage\logs"
if not exist "%SITE_PATH%\storage\cache" mkdir "%SITE_PATH%\storage\cache"
if not exist "%SITE_PATH%\storage\uploads" mkdir "%SITE_PATH%\storage\uploads"
if not exist "%SITE_PATH%\storage\sessions" mkdir "%SITE_PATH%\storage\sessions"

REM Set permissions
icacls "%SITE_PATH%\storage" /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls "%SITE_PATH%\storage" /grant "IUSR:(OI)(CI)F" /T
icacls "%SITE_PATH%\storage" /grant "IIS APPPOOL\%SITE_NAME%":(OI)(CI)F /T

REM Create test file
echo ^<?php phpinfo(); ?^> > "%SITE_PATH%\test.php"

REM Reset IIS
echo Resetting IIS...
iisreset

echo.
echo Configuration complete!
echo Please try accessing http://localhost/test.php
echo.
pause 