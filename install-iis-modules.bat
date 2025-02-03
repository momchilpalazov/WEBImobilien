@echo off
echo Installing IIS modules...

REM Download URL Rewrite Module
echo Downloading URL Rewrite Module...
powershell -Command "Invoke-WebRequest -Uri 'https://download.microsoft.com/download/1/2/8/128E2E22-C1B9-44A4-BE2A-5859ED1D4592/rewrite_amd64_en-US.msi' -OutFile 'rewrite_amd64.msi'"

REM Install URL Rewrite Module
echo Installing URL Rewrite Module...
msiexec /i rewrite_amd64.msi /quiet /norestart

REM Clean up
del rewrite_amd64.msi

REM Reset IIS
echo Resetting IIS...
iisreset /restart

echo Installation complete!
pause 