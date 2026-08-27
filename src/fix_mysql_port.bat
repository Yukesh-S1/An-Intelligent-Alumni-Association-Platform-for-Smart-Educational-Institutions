@echo off
echo ========================================
echo MySQL Port 3307 Conflict Fix
echo ========================================
echo.

echo Checking for processes using port 3307...
netstat -ano | findstr :3307
echo.

echo Checking for any MySQL processes...
tasklist | findstr mysqld
echo.

echo Killing all MySQL processes...
taskkill /F /IM mysqld.exe /T 2>nul
if %errorlevel% equ 0 (
    echo MySQL processes terminated
    timeout /t 3 >nul
) else (
    echo No MySQL processes found
)
echo.

echo Checking port 3307 again...
netstat -ano | findstr :3307
echo.

echo ========================================
echo Now try starting MySQL in XAMPP
echo ========================================
echo.
pause
