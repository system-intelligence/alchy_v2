@echo off
echo ========================================
echo Starting Alchy Development Environment
echo ========================================
echo.
echo [1/2] Starting Vite (npm run dev)...
start "Vite Dev Server" cmd /k "npm run dev"
timeout /t 3 /nobreak > nul
echo.
echo [2/2] Starting Laravel Server...
start "Laravel Server" cmd /k "php artisan serve"
echo.
echo ========================================
echo Development servers are starting!
echo ========================================
echo.
echo Laravel: http://localhost:8000
echo Vite: Check the Vite terminal window
echo.
echo Press any key to exit this window...
pause > nul
