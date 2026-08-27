@echo off
echo Checking Apache Configuration...
echo.
echo Current Directory:
cd
echo.
echo Testing localhost:8080...
curl -s http://localhost:8080/ > nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] Apache is responding on port 8080
) else (
    echo [FAIL] Apache not responding on port 8080
)
echo.
echo Testing localhost:8080/new/...
curl -s http://localhost:8080/new/ > nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] /new/ directory accessible
) else (
    echo [FAIL] /new/ directory not found
)
echo.
echo Listing htdocs contents:
dir C:\xampp\htdocs
echo.
pause
