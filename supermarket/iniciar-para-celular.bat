@echo off
title Supermarket - Servidor para el celular
cd /d "%~dp0"
echo ==============================================================
echo   SUPERMARKET - Servidor accesible desde el celular
echo ==============================================================
echo.
echo Tu PC tiene estas direcciones en la red (usa la que empieza
echo con 192.168. o 10.0. en la app del celular):
echo.
ipconfig | findstr /i "IPv4"
echo.
echo En la app: Login ^> "Direccion del servidor" ^> escribe
echo   http://TU-IP:8000     (ejemplo: http://192.168.0.105:8000)
echo y toca "Probar conexion".
echo.
echo Si dice que no se puede conectar: celular y PC deben estar en la
echo MISMA red Wi-Fi, y el Firewall de Windows debe permitir PHP
echo (aparece un aviso la primera vez: elige "Permitir acceso").
echo.
echo Para detener el servidor cierra esta ventana o presiona Ctrl+C.
echo ==============================================================
echo.
php artisan serve --host=0.0.0.0 --port=8000
pause
