# Sistema web de ventas para el micromercado SuperMarket

Aplicación web desarrollada con **Laravel 12**, **PHP 8.2 o superior**, **MySQL**, **Bootstrap 5** y **Vite** para apoyar la administración de productos, categorías, clientes y pedidos de un micromercado.

> **Estado de esta documentación:** describe los módulos y rutas encontrados en la rama `main`. Antes de presentar el sistema como terminado, se deben ejecutar las pruebas y comprobar cada flujo en un entorno local.

## Funcionalidades identificadas en el código

- Inicio de sesión y cierre de sesión.
- Panel principal con indicadores y gráficos de ventas, pedidos, productos destacados, clientes y stock bajo.
- Consulta de categorías, productos, clientes y pedidos.
- Operaciones de administración para categorías, productos, clientes, pedidos y usuarios, protegidas por autenticación y middleware de rol.
- Gestión de proveedores mediante modelos y migraciones.
- Papelera, restauración y eliminación definitiva de productos.
- Exportación de productos y reportes de ventas a PDF y Excel.
- Campos y paquetes para códigos QR y códigos de barras; comprobar su integración en la interfaz antes de afirmar que todo el flujo está terminado.

La aplicación usa roles configurados en el código. Verifica los nombres de rol permitidos y los permisos de cada pantalla en el entorno de ejecución antes de asignar cuentas reales.

## Tecnologías y dependencias

- PHP `^8.2`
- Laravel `^12.0`
- MySQL
- Node.js y npm
- Bootstrap 5, Sass y Vite
- DomPDF para exportación PDF
- Maatwebsite Excel para exportación Excel
- Bibliotecas QR y códigos de barras

Las versiones exactas instaladas se encuentran en `composer.lock` y `package-lock.json` si este último está presente.

## Requisitos previos

Instala y verifica:

1. PHP 8.2 o superior, con las extensiones requeridas por Laravel.
2. Composer.
3. Node.js y npm.
4. MySQL (por ejemplo, mediante XAMPP).
5. Git, si clonarás el repositorio.

Comprueba las versiones con:

```bash
php -v
composer --version
node -v
npm -v
```

## Instalación local

### 1. Clonar el repositorio

```bash
git clone https://github.com/FreddyQA/SISTEMA-WEB-PARA-LA-GESTI-N-DE-VENTAS-CASO-DE-USO-MICROMERCADO-SUPERMARKET-.git
cd SISTEMA-WEB-PARA-LA-GESTI-N-DE-VENTAS-CASO-DE-USO-MICROMERCADO-SUPERMARKET-
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Crear el archivo de entorno

En Windows CMD:

```bat
copy .env.example .env
```

En PowerShell o Git Bash también puedes copiar `.env.example` a `.env`.

### 4. Crear y configurar la base de datos

En MySQL crea una base de datos llamada `supermarket` (o cambia el nombre en `.env`). Configura las variables de conexión en `.env`:

```dotenv
APP_NAME=Supermarket
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=supermarket
DB_USERNAME=root
DB_PASSWORD=
```

Ajusta el usuario y la contraseña a la configuración de tu MySQL. No copies el archivo `.env` al repositorio ni compartas sus secretos.

### 5. Generar la clave y ejecutar migraciones

```bash
php artisan key:generate
php artisan migrate
```

Si necesitas datos de demostración, revisa primero `database/seeders/DatabaseSeeder.php`. El seeder actual crea cuentas de ejemplo con contraseñas conocidas por estar definidas en el código; **no lo ejecutes en producción**. Para una instalación local de pruebas, puedes ejecutar:

```bash
php artisan db:seed
```

Después de usar datos de demostración, cambia o elimina las cuentas y contraseñas de ejemplo.

### 6. Instalar dependencias de interfaz

```bash
npm install
npm run build
```

### 7. Enlazar almacenamiento público (si se usan imágenes)

```bash
php artisan storage:link
```

### 8. Iniciar el servidor

```bash
php artisan serve
```

Abre [http://127.0.0.1:8000](http://127.0.0.1:8000).

## Integración continua

El flujo `.github/workflows/laravel-checks.yml` está configurado para ejecutar migraciones y pruebas con MySQL 8, además de compilar los recursos frontend, al abrir un Pull Request hacia `main` o al publicar cambios en `main`. El resultado debe revisarse en la pestaña **Actions** de GitHub; la existencia del flujo no significa que ya haya pasado.

## Pruebas

Ejecuta:

```bash
php artisan test
```

La rama revisada contiene pruebas de ejemplo en `tests/Feature/ExampleTest.php` y `tests/Unit/ExampleTest.php`. No se debe dar por confirmada ninguna cifra de pruebas o aserciones hasta ejecutar el comando y guardar la salida real.

## Estructura principal

- `app/Models`: modelos de la aplicación.
- `app/Http/Controllers`: controladores de módulos, autenticación y reportes.
- `app/Http/Middleware`: middleware de autorización por rol.
- `database/migrations`: estructura de base de datos.
- `database/seeders`: datos de demostración.
- `resources/views`: vistas Blade.
- `routes/web.php`: rutas web.
- `tests`: pruebas automatizadas.

## Alcance y limitaciones que deben verificarse

- En la rama revisada no se encontró `routes/api.php` ni una carpeta de aplicación móvil React Native/Expo. Si la aplicación móvil forma parte de la entrega, enlaza su repositorio o incorpórala de forma controlada y actualiza esta sección.
- La memoria del proyecto debe coincidir con los módulos y flujos que puedan demostrarse en el código y en una ejecución real.
- No describas facturación electrónica, compras ni API móvil como implementadas sin evidencia verificable.

## Seguridad antes de publicar o desplegar

- Mantén `.env`, credenciales, claves y tokens fuera de Git.
- Usa `APP_DEBUG=false` en producción.
- No uses cuentas ni contraseñas de demostración en un entorno real.
- Configura HTTPS y credenciales de base de datos específicas para el entorno.
- Revisa el historial de commits si sospechas que alguna clave o archivo sensible se publicó anteriormente.

## Licencia

No se ha confirmado una licencia específica para este proyecto. Añade una sección de licencia solo después de definirla.
