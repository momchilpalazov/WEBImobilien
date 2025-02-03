@echo off
echo Installing PHP for IIS...

REM Set variables
set PHP_VERSION=8.0.30
set PHP_PATH=C:\Program Files\PHP\v8.0
set DOWNLOAD_PATH=%TEMP%\php-%PHP_VERSION%-nts-Win32-vs16-x64.zip
set VCREDIST_PATH=%TEMP%\vc_redist.x64.exe

REM Create PHP directory if it doesn't exist
if not exist "%PHP_PATH%" mkdir "%PHP_PATH%"

REM Download Visual C++ Redistributable
echo Downloading Visual C++ Redistributable...
powershell -Command "Invoke-WebRequest -Uri 'https://aka.ms/vs/16/release/vc_redist.x64.exe' -OutFile '%VCREDIST_PATH%'"

REM Install Visual C++ Redistributable
echo Installing Visual C++ Redistributable...
%VCREDIST_PATH% /quiet /norestart

REM Download PHP
echo Downloading PHP %PHP_VERSION%...
powershell -Command "Invoke-WebRequest -Uri 'https://windows.php.net/downloads/releases/php-%PHP_VERSION%-nts-Win32-vs16-x64.zip' -OutFile '%DOWNLOAD_PATH%'"

REM Extract PHP
echo Extracting PHP...
powershell -Command "Expand-Archive -Path '%DOWNLOAD_PATH%' -DestinationPath '%PHP_PATH%' -Force"

REM Copy and rename php.ini
echo Setting up php.ini...
if exist "%PHP_PATH%\php.ini-development" (
    copy "%PHP_PATH%\php.ini-development" "%PHP_PATH%\php.ini"
)

REM Configure php.ini
echo Configuring php.ini...
powershell -Command "(Get-Content '%PHP_PATH%\php.ini') | ForEach-Object { $_ -replace ';extension=mysqli', 'extension=mysqli' -replace ';extension=openssl', 'extension=openssl' -replace ';extension=pdo_mysql', 'extension=pdo_mysql' } | Set-Content '%PHP_PATH%\php.ini'"

REM Add PHP to system PATH
echo Adding PHP to system PATH...
setx PATH "%PATH%;%PHP_PATH%" /M

REM Clean up
echo Cleaning up...
del "%DOWNLOAD_PATH%"
del "%VCREDIST_PATH%"

REM Verify installation
echo Verifying PHP installation...
"%PHP_PATH%\php.exe" -v

REM Register PHP with IIS
echo Registering PHP with IIS...
%windir%\system32\inetsrv\appcmd.exe set config /section:system.webServer/fastCgi /+[fullPath='%PHP_PATH%\php-cgi.exe']

echo.
echo PHP installation complete!
echo Please run fix-503.bat again to configure IIS with the new PHP installation.
echo.
pause 