@echo off
echo Checking MySQL service...
net start mysql
echo.
echo Testing connection...
C:\xampp\mysql\bin\mysql.exe -u root -p"" -e "SELECT 1 as connected;" sakam_sms
if %errorlevel%==0 (
    echo Connection successful!
) else (
    echo Connection failed!
)