@echo off
echo Initializing project...

REM Set variables
set PROJECT_PATH=C:\inetpub\Sites\Imobilien

REM Install Composer dependencies
echo Installing Composer dependencies...
composer install

REM Install NPM dependencies
echo Installing NPM dependencies...
npm install

REM Create required directories
echo Creating required directories...
if not exist "%PROJECT_PATH%\storage" mkdir "%PROJECT_PATH%\storage"
if not exist "%PROJECT_PATH%\storage\logs" mkdir "%PROJECT_PATH%\storage\logs"
if not exist "%PROJECT_PATH%\storage\cache" mkdir "%PROJECT_PATH%\storage\cache"
if not exist "%PROJECT_PATH%\storage\uploads" mkdir "%PROJECT_PATH%\storage\uploads"
if not exist "%PROJECT_PATH%\storage\sessions" mkdir "%PROJECT_PATH%\storage\sessions"

REM Set directory permissions
echo Setting directory permissions...
icacls "%PROJECT_PATH%\storage" /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls "%PROJECT_PATH%\storage" /grant "IUSR:(OI)(CI)F" /T
icacls "%PROJECT_PATH%\storage" /grant "IIS APPPOOL\Imobilien:(OI)(CI)F" /T

REM Create database if not exists
echo Creating database...
mysql -u root -p1 -e "CREATE DATABASE IF NOT EXISTS imobilien CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

REM Import database schema
echo Importing database schema...
mysql -u root -p1 imobilien < database.sql

REM Build frontend assets
echo Building frontend assets...
npm run build

REM Clear cache
echo Clearing cache...
del /Q /S "%PROJECT_PATH%\storage\cache\*"

REM Restart IIS
echo Restarting IIS...
iisreset

echo.
echo Project initialization complete!
echo Please check http://localhost/ in your browser
echo.

REM Run basic tests
echo Running basic tests...
echo Testing PHP...
php -v
echo.
echo Testing MySQL connection...
mysql -u root -p1 -e "SELECT VERSION();"
echo.
echo Testing web server...
powershell -Command "Invoke-WebRequest -Uri http://localhost/check-php.php -UseBasicParsing"

pause 