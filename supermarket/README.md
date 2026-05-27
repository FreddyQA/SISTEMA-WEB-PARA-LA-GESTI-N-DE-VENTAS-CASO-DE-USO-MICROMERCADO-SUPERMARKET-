# Supermarket — Sistema de Gestión de Ventas e Inventario

Aplicación web para la gestión integral de un micromercado: catálogo de
productos con inventario por lotes y control de vencimientos, categorías,
clientes, registro de ventas y reportes. Desarrollada con **Laravel 12**.

> Proyecto de grado. Este README documenta cómo instalar, poblar y probar
> el sistema, y describe las decisiones de diseño de la base de datos.

---

## 1. Stack técnico

| Capa            | Tecnología                                                |
|-----------------|------------------------------------------------------------|
| Backend         | PHP 8.2+, Laravel 12                                       |
| Base de datos   | MySQL 8.0.16+ o MariaDB 10.2.1+ (recomendado) · SQLite (desarrollo rápido) |
| Frontend        | Blade, Bootstrap 5, Sass, Vite                              |
| Reportes        | barryvdh/laravel-dompdf (PDF), maatwebsite/excel (Excel)    |
| Códigos         | picqer/php-barcode-generator, endroid/qr-code                |
| Testing         | PHPUnit (Feature/Unit)                                      |

**Extensiones PHP requeridas:** `pdo_mysql` (o `pdo_sqlite`), `mbstring`,
`bcmath` *(usado por `VentaService` para calcular totales sin errores de
punto flotante — declarada en `composer.json` como `ext-bcmath`)*, `gd`, `zip`.

---

## 2. Funcionalidades

- **Autenticación y roles**: `administrador` (acceso completo) e `invitado`
  (solo lectura), reforzado con middleware (`rol:administrador`) en cada ruta
  de escritura — no solo ocultando botones en la vista.
- **Productos**: alta/edición/baja (soft delete + papelera + restauración
  permanente), imagen, precio, categoría y lotes de inventario con fecha
  opcional de vencimiento. Las ventas consumen primero los lotes vigentes más
  próximos a vencer; no se permite vender lotes vencidos.
- **Categorías**: con protección de integridad referencial (no se puede borrar
  una categoría con productos asociados).
- **Clientes**: alta/edición/baja con soft delete.
- **Ventas**: registro de una venta con múltiples líneas de
  producto. El descuento de stock ocurre dentro de una transacción con
  bloqueo de fila (`lockForUpdate`) para que dos ventas simultáneas no
  puedan vender el mismo stock dos veces; si el stock no alcanza, se
  revierte toda la venta (nada queda a medias).
- **Reportes de ventas**: por rango de fechas, top productos, top clientes,
  con exportación a PDF/Excel.
- **Panel (dashboard)**: gráficos de ventas mensuales.

---

## 3. Instalación

### 3.1. Requisitos previos

- PHP 8.2+ con las extensiones listadas arriba
- Composer 2.x
- Node.js 18+ y npm
- Un servidor MySQL 8.0.16+ / MariaDB 10.2.1+ (o usa SQLite, ver 3.4)

### 3.2. Pasos

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
```

Edita `.env` y ajusta `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` según tu
gestor local (por defecto asume MySQL en `127.0.0.1:3306`, usuario `root`
sin contraseña — típico de XAMPP/Laragon).

Crea la base de datos vacía (una sola vez):

```sql
CREATE DATABASE supermarket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3.3. Levantar el esquema

```bash
php artisan migrate --seed
```

Esto aplica las migraciones en orden y siembra datos de demostración
(usuarios, categorías, productos, clientes y ventas de ejemplo) — ver
`database/seeders/`.

Las migraciones son la única fuente de verdad. Los respaldos SQL de
`database/` son instantáneas antiguas y no incluyen el inventario por lotes
ni la eliminación del módulo de proveedores; no los importes para una
instalación nueva.

### 3.4. Alternativa rápida sin MySQL (solo para probar en local)

```bash
cp .env.example .env
sed -i "s/^DB_CONNECTION=mysql/DB_CONNECTION=sqlite/" .env
touch database/database.sqlite
php artisan migrate --seed
```

> El `CHECK (stock >= 0)` es sintaxis de MySQL/MariaDB y en SQLite se omite
> automáticamente (ver `database/migrations/..._fix_database_consistency.php`);
> la regla de "stock nunca negativo" igual se aplica en la capa de
> aplicación (validación + `lockForUpdate` en `VentaService`), así que el
> comportamiento del sistema no cambia — solo pierdes esa segunda capa de
> defensa a nivel de motor de base de datos. Para producción/defensa, usa
> MySQL o MariaDB.

### 3.5. Credenciales de acceso (datos de demo)

| Rol            | Email                       | Contraseña    |
|----------------|------------------------------|---------------|
| Administrador  | `dueno@supermarket.com`      | `password123` |
| Invitado       | `empleado@supermarket.com`   | `password123` |

### 3.6. Servir la aplicación

```bash
composer run dev
```

Levanta `php artisan serve`, el worker de colas, logs (`pail`) y Vite en
paralelo. La app queda disponible en `http://localhost:8000`.

---

## 4. Tests

```bash
php artisan test
```

La suite corre contra SQLite en memoria (ver `phpunit.xml`) y cubre, entre
otras cosas, las reglas de negocio más sensibles de la base de datos:

- Una venta descuenta stock correctamente y calcula bien el total.
- No se puede vender más stock del disponible, y si falla, **no** queda un
  registro de venta a medias ni stock parcialmente descontado (atomicidad).
- Eliminar una venta restituye el stock.
- No se puede eliminar una categoría con productos asociados.
- No se permiten dos categorías activas con el mismo nombre.

### 4.1. Auditoría Lighthouse

La auditoría reproducible está en `scripts/lighthouse-audit.ps1`. Genera
informes JSON y un resumen CSV dentro de `storage/app/lighthouse-audit/`
(fuera del directorio público), con ejecuciones móviles y de escritorio para
las páginas principales. No guarda credenciales.

1. Inicia la aplicación local con una base de demostración y datos de prueba;
   no uses una base de producción.
2. Abre Chrome con un perfil temporal y depuración remota:

   ```powershell
   & "$env:ProgramFiles\Google\Chrome\Application\chrome.exe" --remote-debugging-port=9223 --user-data-dir="$env:TEMP\supermarket-lighthouse"
   ```

3. Inicia sesión manualmente en esa ventana con un usuario administrador
   local para que las páginas protegidas puedan auditarse.
4. En PowerShell, desde la raíz del proyecto, guarda la línea base:

   ```powershell
   .\scripts\lighthouse-audit.ps1 -BaseUrl http://127.0.0.1:8000 -OutputDirectory .\storage\app\lighthouse-baseline
   ```

5. Repite después de los cambios, comparando las puntuaciones:

   ```powershell
   .\scripts\lighthouse-audit.ps1 -BaseUrl http://127.0.0.1:8000 -BaselineDirectory .\storage\app\lighthouse-baseline -OutputDirectory .\storage\app\lighthouse-after
   ```

La ruta de edición usa el ID `1` por defecto; para otra base de demostración,
pasa `-EditId <id>`. Los informes privados no deben copiarse a `public/` ni
publicarse. El script verifica que cada URL termine en la ruta solicitada y
responda HTTP 200, para no aceptar una redirección al login como página medida.
La página de login se abre en un Chrome aislado. Antes de cada medición privada,
el script limpia la caché HTTP mediante CDP sin borrar la cookie de sesión.
Las rutas privadas usan la sesión ya iniciada en el puerto de depuración. La
ruta `/ruta-inexistente` devuelve HTTP 404 autenticada, pero Lighthouse aborta
antes de generar puntuaciones para respuestas 404; por eso no se incluye en la
matriz de puntuaciones.
Las páginas privadas envían
`noindex, nofollow`, por lo que su puntuación SEO no se debe optimizar abriendo
el panel a rastreadores.

Para una comprobación rápida de páginas concretas, usa, por ejemplo,
`-Devices mobile -PageNames dashboard,ventas-edit`.

---

## 5. Base de datos

Ver [`docs/BASE_DE_DATOS.md`](docs/BASE_DE_DATOS.md) para el diagrama
entidad-relación, la descripción de cada tabla, las reglas de integridad
aplicadas (llaves foráneas, `CHECK`, índices únicos) y las decisiones de
diseño (por qué `ON DELETE RESTRICT` aquí y `SET NULL` allá, por qué
`detalle_ventas` guarda su propio `precio_unitario` en vez de recalcularlo
desde `productos`, etc.).
La migración `2026_10_16_180000_rename_pedidos_to_ventas` renombra las tablas
heredadas conservando las ventas, sus detalles y sus asignaciones de lotes.

---

## 6. Estructura del proyecto (resumen)

```
app/
  Http/Controllers/     Lógica de cada módulo (Producto, Categoria, Cliente, Venta, Reporte, Usuario)
  Http/Requests/         Validación de formularios (FormRequest)
  Models/                 Eloquent: relaciones, casts, soft deletes
database/
  migrations/             Historial versionado del esquema (fuente de verdad)
  seeders/                Datos de demostración (alineados con el .sql)
  schema_supermarket_completo.sql   Respaldo completo importable directamente
docs/
  BASE_DE_DATOS.md        Diagrama ER y documentación del esquema
resources/views/         Blade + Bootstrap 5
routes/web.php            Rutas agrupadas por autenticación y rol
tests/Feature/            Tests de integridad y reglas de negocio
```

---

## 7. Notas de mantenimiento

- Las contraseñas se almacenan con `bcrypt` (cost 12, vía `Hash::make`).
  Las de los usuarios de demo fueron generadas y verificadas con
  `password_verify()` antes de publicarse en el repositorio.
- `database/database.sqlite` se incluye ya migrado y sembrado, listo para
  una demo rápida sin configurar MySQL — pero no reemplaza a MySQL para una
  presentación formal (ver 3.4).
- Hay vistas heredadas del scaffold de `laravel/ui` (`resources/views/home.blade.php`,
  `resources/views/welcome.blade.php`, `app/Http/Controllers/HomeController.php`)
  que ninguna ruta activa renderiza actualmente (`/` redirige directo a
  `login` o `dashboard`). Se dejaron intactas por no tener certeza de si
  había contenido intencional ahí, pero es limpieza pendiente si no se van
  a usar.
- **Imágenes de productos/usuarios**: `public/storage` en este proyecto NO es
  el symlink que crea `php artisan storage:link` — es una carpeta real a la
  que `App\Http\Controllers\Concerns\SincronizaImagenPublica` copia cada
  archivo manualmente después de guardarlo en `storage/app/public`. Es una
  decisión deliberada (evita depender de symlinks, que dan problemas en
  algunos hostings/XAMPP en Windows), pero significa que ambas carpetas
  pueden desincronizarse si algún archivo se sube o se borra por fuera de
  la aplicación (por FTP, por ejemplo). Si en algún momento se migra a un
  hosting que sí soporte symlinks, se puede reemplazar por
  `php artisan storage:link` y quitar el trait.
- **Antes de desplegar en un servidor real** (no localhost/XAMPP): cambiar
  `APP_ENV=local` a `APP_ENV=production` y `APP_DEBUG=true` a
  `APP_DEBUG=false` en `.env`. Con `APP_DEBUG=true` cualquier error de la
  aplicación muestra la traza completa (rutas del servidor, consultas SQL,
  a veces variables de entorno) a quien sea que esté viendo la página —
  aceptable para desarrollo y para la demo de tesis, pero no para producción.
