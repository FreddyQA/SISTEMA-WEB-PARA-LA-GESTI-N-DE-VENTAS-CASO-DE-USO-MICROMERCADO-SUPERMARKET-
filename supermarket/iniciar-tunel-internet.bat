@echo off
title Supermarket - Tunel de Acceso por Internet
cd /d "%~dp0"
echo ==============================================================
echo   SUPERMARKET - Acceso por Internet (Datos Moviles / Remoto)
echo ==============================================================
echo.
echo Este script crea una direccion publica temporal en Internet para que
echo puedas conectar la app desde tu celular usando datos moviles (4G/5G)
echo o cualquier red Wi-Fi distinta a la de tu computadora.
echo.
echo IMPORTANTE:
echo 1. Debes tener tu servidor Laravel encendido en otra ventana
echo    (ejecutando "iniciar-para-celular.bat").
echo 2. Copia la URL que aparezca abajo (ej: https://xxxx.loca.lt)
echo 3. En tu celular abre la app, toca "Direccion del servidor",
echo    pega esa URL y pulsa "Probar conexion" y "Guardar".
echo.
echo Presiona Ctrl+C para detener el tunel cuando termines.
echo ==============================================================
echo.
cmd /c npx -y localtunnel --port 8000
pause
