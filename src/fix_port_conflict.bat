@echo off
echo ========================================
echo XAMPP Port 443 Conflict Resolver
echo ========================================
echo.

echo Checking what's using port 443...
echo.
netstat -ano | findstr :443
echo.

echo ========================================
echo Found process using port 443 (PID shown above)
echo.
echo Options:
echo 1. Kill the process automatically
echo 2. Exit and kill manually
echo.
set /p choice="Enter your choice (1 or 2): "

if "%choice%"=="1" (
    echo.
    echo Attempting to kill process on port 443...
    for /f "tokens=5" %%a in ('netstat -ano ^| findstr :443 ^| findstr LISTENING') do (
        echo Killing PID: %%a
        taskkill /PID %%a /F
    )
    echo.
    echo Process killed. Try starting Apache now.
) else (
    echo.
    echo Exiting. You can manually kill the process using:
    echo taskkill /PID [PID_NUMBER] /F
)

echo.
pause
