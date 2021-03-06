@echo off
ECHO Starting PHP installation... >> ..\startup-tasks-log.txt

md "%~dp0appdata"
cd "%~dp0appdata"
cd ..

reg add "hku\.default\software\microsoft\windows\currentversion\explorer\user shell folders" /v "Local AppData" /t REG_EXPAND_SZ /d "%~dp0appdata" /f
"..\app\azure\resources\WebPICmdLine\WebpiCmd.exe" /Install /Products:PHP55 /AcceptEula  >> ..\startup-tasks-log.txt 2>>..\startup-tasks-error-log.txt
reg add "hku\.default\software\microsoft\windows\currentversion\explorer\user shell folders" /v "Local AppData" /t REG_EXPAND_SZ /d %%USERPROFILE%%\AppData\Local /f

ECHO Completed PHP installation. >> ..\startup-tasks-log.txt
