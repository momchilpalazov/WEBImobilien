@echo off
echo Installing PHP Extensions...

REM Set variables
set PHP_PATH=C:\Program Files\PHP\v8.0
set VC_REDIST_URL=https://aka.ms/vs/16/release/vc_redist.x64.exe
set PHP_THREAD_SAFE_URL=https://windows.php.net/downloads/releases/php-8.0.30-Win32-vs16-x64.zip
set TEMP_PATH=%TEMP%\php-install

REM Create temp directory
if not exist "%TEMP_PATH%" mkdir "%TEMP_PATH%"

REM Download and install Visual C++ Redistributable
echo Downloading Visual C++ Redistributable...
powershell -Command "Invoke-WebRequest -Uri '%VC_REDIST_URL%' -OutFile '%TEMP_PATH%\vc_redist.x64.exe'"
echo Installing Visual C++ Redistributable...
"%TEMP_PATH%\vc_redist.x64.exe" /quiet /norestart

REM Download thread-safe PHP
echo Downloading PHP...
powershell -Command "Invoke-WebRequest -Uri '%PHP_THREAD_SAFE_URL%' -OutFile '%TEMP_PATH%\php.zip'"

REM Extract PHP
echo Extracting PHP...
powershell -Command "Expand-Archive -Path '%TEMP_PATH%\php.zip' -DestinationPath '%PHP_PATH%' -Force"

REM Copy required DLLs
echo Copying required DLLs...
copy "%PHP_PATH%\libssh2.dll" "%windir%\system32\"
copy "%PHP_PATH%\libcrypto-1_1-x64.dll" "%windir%\system32\"
copy "%PHP_PATH%\libssl-1_1-x64.dll" "%windir%\system32\"
copy "%PHP_PATH%\php8ts.dll" "%windir%\system32\"

REM Enable extensions in php.ini
echo Configuring php.ini...
if exist "%PHP_PATH%\php.ini-development" (
    copy "%PHP_PATH%\php.ini-development" "%PHP_PATH%\php.ini"
)

REM Enable required extensions
powershell -Command "(Get-Content '%PHP_PATH%\php.ini') | ForEach-Object { $_ -replace ';extension=curl', 'extension=curl' -replace ';extension=openssl', 'extension=openssl' -replace ';extension=pdo_mysql', 'extension=pdo_mysql' -replace ';extension=mbstring', 'extension=mbstring' } | Set-Content '%PHP_PATH%\php.ini'"

REM Add PHP extensions directory to PATH
setx PATH "%PATH%;%PHP_PATH%;%PHP_PATH%\ext" /M

REM Clean up
echo Cleaning up...
rd /s /q "%TEMP_PATH%"

REM Verify installation
echo Verifying PHP installation...
"%PHP_PATH%\php.exe" -m

echo.
echo PHP Extensions installation complete!
echo Please run configure-fastcgi.bat again.
echo.
pause 