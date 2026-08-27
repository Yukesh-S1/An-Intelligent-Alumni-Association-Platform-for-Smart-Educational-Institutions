@echo off
echo ========================================
echo MySQL Crash Fix for XAMPP
echo ========================================
echo.

echo Step 1: Checking for port conflicts on 3306...
netstat -ano | findstr :3306
echo.

echo Step 2: Checking if MySQL process is running...
tasklist | findstr mysqld
echo.

echo Step 3: Killing any stuck MySQL processes...
taskkill /F /IM mysqld.exe 2>nul
if %errorlevel% equ 0 (
    echo MySQL process killed successfully
) else (
    echo No MySQL process found to kill
)
echo.

echo Step 4: Checking MySQL error log location...
echo Error log should be at: C:\xampp\mysql\data\mysql_error.log
echo.

echo ========================================
echo Common Solutions:
echo ========================================
echo 1. Port 3306 might be in use by another MySQL instance
echo 2. Corrupt MySQL data files
echo 3. Missing my.ini configuration
echo 4. Insufficient permissions
echo.
echo Next steps:
echo - Check XAMPP Control Panel and click "Logs" for MySQL
echo - Look for mysql_error.log in C:\xampp\mysql\data\
echo - Try changing MySQL port in my.ini if port conflict exists
echo.
pause
