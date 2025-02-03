@echo off
echo Setting up IIS permissions...

REM Set variables
set SITE_PATH=C:\inetpub\Sites\Imobilien
set APP_POOL_NAME=Imobilien

REM Create application pool if it doesn't exist
%windir%\system32\inetsrv\appcmd.exe add apppool /name:%APP_POOL_NAME% /managedRuntimeVersion:"" /managedPipelineMode:"Integrated"

REM Set app pool identity to ApplicationPoolIdentity
%windir%\system32\inetsrv\appcmd.exe set apppool /apppool.name:%APP_POOL_NAME% /processModel.identityType:ApplicationPoolIdentity

REM Create website if it doesn't exist
%windir%\system32\inetsrv\appcmd.exe add site /name:%APP_POOL_NAME% /physicalPath:%SITE_PATH% /bindings:http/*:80:localhost

REM Assign application pool to website
%windir%\system32\inetsrv\appcmd.exe set site /site.name:%APP_POOL_NAME% /[path='/'].applicationPool:%APP_POOL_NAME%

REM Set folder permissions
echo Setting folder permissions...

REM Grant permissions to IIS_IUSRS group
icacls %SITE_PATH% /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls %SITE_PATH% /grant "IUSR:(OI)(CI)F" /T

REM Grant specific permissions for storage folders
icacls %SITE_PATH%\storage /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls %SITE_PATH%\storage\logs /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls %SITE_PATH%\storage\cache /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls %SITE_PATH%\storage\uploads /grant "IIS_IUSRS:(OI)(CI)F" /T

REM Grant permissions to the ApplicationPoolIdentity
icacls %SITE_PATH% /grant "IIS APPPOOL\%APP_POOL_NAME%:(OI)(CI)F" /T

REM Verify web.config permissions
icacls %SITE_PATH%\web.config /grant "IIS_IUSRS:R" /T
icacls %SITE_PATH%\web.config /grant "IUSR:R" /T
icacls %SITE_PATH%\web.config /grant "IIS APPPOOL\%APP_POOL_NAME%:R" /T

REM Reset IIS
iisreset

echo Setup complete!
echo Please check http://localhost/ in your browser
pause 