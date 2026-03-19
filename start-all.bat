@echo off
echo ==========================================
echo   JadwalKuliah - Startup All Services
echo ==========================================
echo.

echo [1/3] Starting Laravel (port 8000)...
start "Laravel" /MIN cmd /c "cd /d G:\Aplikasi\DashboardJadwalKuliah && php artisan serve --port=8000"

echo [2/3] Starting Laravel WebSockets (port 6001)...
start "WebSockets" /MIN cmd /c "cd /d G:\Aplikasi\DashboardJadwalKuliah && php artisan websockets:serve"

echo [3/3] Starting FastAPI Python Service (port 8001)...
start "FastAPI" /MIN cmd /c "cd /d G:\Aplikasi\DashboardJadwalKuliah\python-service && python -m uvicorn main:app --host 127.0.0.1 --port 8001 --reload"

echo.
echo ==========================================
echo  Services started!
echo.
echo  Laravel:    http://localhost:8000
echo  WebSockets: ws://localhost:6001
echo  FastAPI:    http://localhost:8001
echo  API Docs:   http://localhost:8001/docs
echo.
echo  Login Accounts:
echo  BAAK:       baak@jadwalkuliah.com / password
echo  Dosen:      dosen@jadwalkuliah.com / password
echo  Mahasiswa:  mahasiswa@jadwalkuliah.com / password
echo ==========================================
pause
