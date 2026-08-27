@echo off
echo ========================================
echo MySQL Reset Script for XAMPP
echo ========================================
echo.
echo WARNING: This will backup and reset MySQL data
echo Press Ctrl+C to cancel, or
pause
echo.

echo Step 1: Stopping MySQL if running...
taskkill /F /IM mysqld.exe 2>nul
timeout /t 2 >nul
echo.

echo Step 2: Backing up current data folder...
if exist "C:\xampp\mysql\data_backup" (
    echo Removing old backup...
    rmdir /s /q "C:\xampp\mysql\data_backup"
)
echo Creating backup...
xcopy "C:\xampp\mysql\data" "C:\xampp\mysql\data_backup\" /E /I /Q
echo Backup created at: C:\xampp\mysql\data_backup
echo.

echo Step 3: Removing problematic files...
del "C:\xampp\mysql\data\ibdata1" 2>nul
del "C:\xampp\mysql\data\ib_logfile*" 2>nul
echo.

echo Step 4: Checking MySQL configuration...
if exist "C:\xampp\mysql\bin\my.ini" (
    echo Configuration file found: my.ini
) else (
    echo WARNING: my.ini not found!
)
echo.

echo ========================================
echo Done! Now try these steps:
echo ========================================
echo 1. Open XAMPP Control Panel as Administrator
echo 2. Click "Start" for MySQL
echo 3. If it still fails, check the error log:
echo    C:\xampp\mysql\data\mysql_error.log
echo.
echo If MySQL still won't start, you may need to:
echo - Reinstall XAMPP
echo - Or restore from backup: C:\xampp\mysql\data_backup
echo.
pause
