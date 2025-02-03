@echo off
echo Starting development environment...

REM Set paths
set PHP_PATH=C:\Program Files\PHP\v8.0
set MYSQL_PATH=C:\Program Files\MySQL\MySQL Server 8.0\bin
set PROJECT_PATH=C:\inetpub\Sites\Imobilien

REM Add PHP and MySQL to path
set PATH=%PHP_PATH%;%MYSQL_PATH%;%PATH%

REM Install dependencies if needed
if not exist vendor (
    echo Installing PHP dependencies...
    composer install
)

if not exist node_modules (
    echo Installing Node dependencies...
    npm install
)

REM Create required directories if they don't exist
if not exist storage mkdir storage
if not exist storage\logs mkdir storage\logs
if not exist storage\cache mkdir storage\cache
if not exist storage\uploads mkdir storage\uploads

REM Set permissions (requires admin rights)
icacls storage /grant "IUSR:(OI)(CI)F" /T
icacls storage /grant "IIS_IUSRS:(OI)(CI)F" /T

REM Start webpack in background
start npm run dev

REM Restart IIS application pool
echo Restarting IIS application pool...
%windir%\system32\inetsrv\appcmd.exe stop apppool /apppool.name:Imobilien
%windir%\system32\inetsrv\appcmd.exe start apppool /apppool.name:Imobilien

echo Development environment is ready!
echo Access the site at http://localhost/

REM Keep window open
pause 