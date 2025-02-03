@echo off
echo Fixing 503 Service Unavailable error...

REM Set variables
set SITE_PATH=C:\inetpub\Sites\Imobilien
set APP_POOL_NAME=Imobilien
set PHP_PATH=C:\Program Files\PHP\v8.0

REM Check if PHP is installed correctly
if not exist "%PHP_PATH%\php-cgi.exe" (
    echo ERROR: PHP CGI not found at %PHP_PATH%\php-cgi.exe
    echo Please verify PHP installation
    pause
    exit /b 1
)

REM Register PHP with IIS
echo Registering PHP with IIS...
%windir%\system32\inetsrv\appcmd.exe set config /section:system.webServer/fastCgi /+[fullPath='%PHP_PATH%\php-cgi.exe']
%windir%\system32\inetsrv\appcmd.exe set config /section:system.webServer/handlers /+[name='PHP_via_FastCGI',path='*.php',verb='*',modules='FastCgiModule',scriptProcessor='%PHP_PATH%\php-cgi.exe',resourceType='Either']

REM Configure FastCGI Settings
echo Configuring FastCGI settings...
%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /[fullPath='%PHP_PATH%\php-cgi.exe'].instanceMaxRequests:10000
%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /[fullPath='%PHP_PATH%\php-cgi.exe'].monitoring.requestTimeout:00:02:00
%windir%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /+[fullPath='%PHP_PATH%\php-cgi.exe'].environmentVariables.[name='PHP_FCGI_MAX_REQUESTS',value='10000']

REM Stop the application pool
echo Stopping application pool...
%windir%\system32\inetsrv\appcmd.exe stop apppool /apppool.name:%APP_POOL_NAME%

REM Reconfigure application pool
echo Configuring application pool...
%windir%\system32\inetsrv\appcmd.exe set apppool "%APP_POOL_NAME%" /processModel.identityType:ApplicationPoolIdentity
%windir%\system32\inetsrv\appcmd.exe set apppool "%APP_POOL_NAME%" /managedRuntimeVersion:""
%windir%\system32\inetsrv\appcmd.exe set apppool "%APP_POOL_NAME%" /managedPipelineMode:Integrated
%windir%\system32\inetsrv\appcmd.exe set apppool "%APP_POOL_NAME%" /recycling.periodicRestart.time:00:00:00

REM Set up PHP handler mapping
echo Setting up PHP handler mapping...
%windir%\system32\inetsrv\appcmd.exe set config "%APP_POOL_NAME%" -section:system.webServer/handlers /+"[name='PHP_via_FastCGI',path='*.php',verb='*',modules='FastCgiModule',scriptProcessor='%PHP_PATH%\php-cgi.exe',resourceType='Either']"

REM Verify and fix permissions
echo Verifying permissions...
icacls "%PHP_PATH%" /grant "IIS APPPOOL\%APP_POOL_NAME%":(OI)(CI)RX /T
icacls "%SITE_PATH%" /grant "IIS APPPOOL\%APP_POOL_NAME%":(OI)(CI)M /T
icacls "%SITE_PATH%\storage" /grant "IIS APPPOOL\%APP_POOL_NAME%":(OI)(CI)F /T

REM Start the application pool
echo Starting application pool...
%windir%\system32\inetsrv\appcmd.exe start apppool /apppool.name:%APP_POOL_NAME%

REM Create test PHP file
echo ^<?php phpinfo(); ?^> > "%SITE_PATH%\test.php"

REM Reset IIS
echo Resetting IIS...
iisreset /restart

echo.
echo Fix complete! Please try the following URLs in your browser:
echo http://localhost/test.php
echo http://localhost/check-php.php
echo.
echo If you still see 503 error, check the following:
echo 1. Event Viewer ^> Windows Logs ^> Application
echo 2. %SITE_PATH%\storage\logs\php_errors.log
echo.
pause 