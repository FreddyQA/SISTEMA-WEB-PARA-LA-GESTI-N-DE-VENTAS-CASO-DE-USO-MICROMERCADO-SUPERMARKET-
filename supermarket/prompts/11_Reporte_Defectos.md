# 11 — Informe de Defectos y Discrepancias del Sistema (SISMICRO / Supermarket)

> **Informe Técnico de Gestión de Defectos y Discrepancias (Defect Tracking Report)**  
> **Proyecto:** Sistema Web de Ventas e Inventario para el Micromercado SuperMarket  
> **Ámbito:** Proyecto de Grado / Titulación Académica  
> **Fecha de Emisión:** 22 de Septiembre de 2026  
> **Versión:** 1.0  
> **Fuentes de Verdad:** Repositorio real (`app/`, `routes/`, `resources/views/`, `database/migrations/`, `docs/BASE_DE_DATOS.md`, `tests/`), documentos previos `02_Requisitos_Verificables.md`, `04_Casos_Prueba.md`, `07_Pruebas_MySQL_Persistencia.md`, `08_Pruebas_Integracion.md` y `10_Matriz_Trazabilidad.md`.  
> **Estado de la Suite Automatizada:** EJECUTADO Y VERIFICADO (41 pruebas unitarias y de integración pasadas en PHPUnit, 155 aserciones, 0 fallos).  
> **Directiva de Integridad:** Se analizan únicamente defectos confirmados por inspección de código real, discrepancias comprobadas entre capas arquitectónicas y defectos históricos documentados con solución verificada. No se inventa información ni resultados.

---

## 1. Introducción y Metodología de Clasificación

El presente documento consolida el reporte formal de defectos y discrepancias detectados en el sistema **SuperMarket**. En cumplimiento estricto de las directrices de auditoría académica y de ingeniería de software, los defectos catalogados provienen de dos fuentes comprobables:

1. **Defectos Activos y Discrepancias Arquitectónicas (`DEF-001` a `DEF-005` y `DEF-012`):** Hallazgos identificados mediante inspección estática del código fuente, análisis del flujo transversal entre capas (Rutas $\rightarrow$ Middleware $\rightarrow$ Controladores $\rightarrow$ Query Builder $\rightarrow$ Vistas Blade) y evaluación de las limitaciones del entorno de pruebas frente al entorno de producción real.
2. **Defectos Históricos Subsanados (`DEF-006` a `DEF-011`):** Anomalías críticas detectadas durante la fase de desarrollo y revisión del esquema de base de datos (`docs/BASE_DE_DATOS.md`, Sección 4), las cuales fueron corregidas y verificadas para asegurar la estabilidad operativa del aplicativo.

### 1.1. Criterios de Severidad
- **Crítica:** Defectos que impiden la ejecución del sistema, abortan migraciones desde cero (`migrate:fresh`), bloquean el acceso total a usuarios legítimos o comprometen de forma irreversible la consistencia de los datos.
- **Alta:** Anomalías que provocan errores fatales (HTTP 500) en flujos de negocio principales (como el registro de ventas), violaciones de integridad referencial o desbordamientos lógicos no mitigados por la base de datos.
- **Media:** Inconsistencias de interfaz de usuario que generan respuestas HTTP 403 inesperadas, omisión de filtros de eliminación lógica (`SoftDeletes`) en consultas analíticas, o discrepancias funcionales entre el motor de pruebas y el motor de producción.
- **Baja:** Código residual, ausencia de métodos de navegación inversa no requeridos por la ruta crítica, o archivos huérfanos de scaffolding que no afectan el flujo operativo del usuario.

### 1.2. Criterios de Prioridad
- **Alta:** Requiere resolución inmediata antes de cualquier despliegue o presentación formal ante el tribunal de titulación.
- **Media:** Debe programarse para el siguiente sprint o ciclo de mantenimiento.
- **Baja:** Mejora cosmética o de refactorización arquitectónica menor sin impacto funcional directo.

### 1.3. Criterios de Estado
- **CORREGIDO:** El defecto fue identificado, parchado en el código fuente o migraciones, y verificado mediante pruebas reales.
- **PENDIENTE DE CORRECCIÓN:** Defecto activo comprobado en el código actual que cuenta con propuesta de solución técnica documentada.
- **REQUIERE VERIFICACIÓN:** Hallazgo que depende de una definición de negocio externa o de la ejecución de pruebas sobre infraestructura específica (e.g. concurrencia real sobre MySQL 8 en red física).

---

## 2. Resumen Estadístico de Defectos

| Categoría de Defecto | Crítica | Alta | Media | Baja | Total |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Defectos Activos / Discrepancias entre Capas** | 0 | 0 | 3 | 3 | **6** |
| **Defectos Históricos Corregidos en Desarrollo** | 2 | 2 | 1 | 1 | **6** |
| **TOTAL CONSOLIDADO** | **2** | **2** | **4** | **4** | **12** |

### Distribución por Estado Operativo
- **CORREGIDO:** 6 defectos (50.0 %) — Historial de estabilidad DDL, hashes, dependencias y credenciales.
- **PENDIENTE DE CORRECCIÓN:** 4 defectos (33.3 %) — Discrepancias de UI, validación residual, consultas de dashboard y relaciones de modelo.
- **REQUIERE VERIFICACIÓN:** 2 defectos (16.7 %) — Restricciones de motor en SQLite vs MySQL y código de scaffolding inactivo.

---

## 3. Registro Detallado de Defectos Activos y Discrepancias

### DEF-001: Enlace a "Reportes" expuesto en menú lateral a usuarios con rol `invitado` genera respuesta HTTP 403 Forbidden

```yaml
ID: DEF-001
Caso de Prueba: CP-017 (y relacionado a RF-013)
Requisito Asociado: RF-012, RF-013
Módulo: Interfaz de Usuario / Control de Acceso (RBAC)
Título: Enlace de navegación a "Reportes" visible para rol invitado desencadena HTTP 403
Severidad: Media
Prioridad: Media
Estado: PENDIENTE DE CORRECCIÓN
Fecha de Detección: 2026-09-22
Responsable: Equipo de Desarrollo / Auditoría QA
```

- **Descripción:**  
  En la plantilla del layout principal (`resources/views/layouts/app.blade.php`), el bloque de navegación que contiene el enlace a la ruta `reportes.ventas` (líneas 2693 a 2699) se encuentra ubicado fuera de cualquier directiva condicional de autorización. En contraste, la ruta subyacente en `routes/web.php` (líneas 142 y 150) está estrictamente protegida por el grupo de middleware `Route::middleware(['auth', 'rol:administrador'])`. Cuando un usuario autenticado con el rol `invitado` (e.g., cajero) inicia sesión, observa el botón de "Reportes" en el menú lateral. Al hacer clic sobre él, el sistema le deniega el acceso mostrando la vista de error HTTP 403 Forbidden (`errors/403.blade.php`). Cabe destacar que en la vista del panel (`resources/views/dashboard.blade.php`, líneas 400 y 499), los enlaces a reportes sí cuentan con la directiva `@if(auth()->user()->rol === 'administrador')`, evidenciando una inconsistencia exclusiva del menú lateral.
- **Pasos para Reproducir:**  
  1. Iniciar sesión con un usuario que posea el rol `invitado` (credenciales: `invitado@supermarket.com` / `invitado123`).
  2. El sistema redirige al usuario al panel principal `/dashboard`.
  3. En la barra de navegación lateral izquierda (sidebar), localizar el elemento "Reportes" (ícono `fa-chart-line`).
  4. Hacer clic sobre el enlace "Reportes".
- **Resultado Esperado:**  
  El menú de navegación debe ocultar dinámicamente aquellos módulos para los cuales el usuario autenticado no posee privilegios de acceso, evitando la exposición de rutas restringidas y la generación innecesaria de errores de autorización 403.
- **Resultado Obtenido:**  
  El elemento "Reportes" es visible y clickeable en el menú lateral; al interactuar con él, el navegador es redirigido a `/reportes/ventas` y el servidor responde con código de estado HTTP 403 Forbidden.
- **Evidencia Técnica:**  
  - `resources/views/layouts/app.blade.php` (L2693-2701):  
    ```blade
    <a href="{{ route('reportes.ventas') }}"
       class="sidebar-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}"
       data-bs-toggle="tooltip" data-bs-placement="right" title="Reportes">
        <i class="fas fa-chart-line"></i>
        <span class="link-label">Reportes</span>
    </a>

    @if(auth()->check() && strtolower(auth()->user()->rol) === 'administrador')
        <div class="nav-section-title">Administración</div>
        ...
    ```
  - `routes/web.php` (L142-150):  
    ```php
    Route::middleware(['auth', 'rol:administrador'])->group(function () {
        ...
        Route::get('/reportes/ventas', [ReporteController::class, 'ventas'])->name('reportes.ventas');
        ...
    });
    ```
- **Causa Probable:**  
  Ubicación errónea del bloque HTML del enlace a reportes por encima de la directiva `@if(auth()->check() && strtolower(auth()->user()->rol) === 'administrador')` en la plantilla Blade del menú lateral, a diferencia de la sección "Administración" que sí fue protegida.
- **Corrección Aplicada / Recomendada:**  
  Mover el bloque del enlace `<a href="{{ route('reportes.ventas') }}">...</a>` dentro del condicional de verificación de rol administrador en `resources/views/layouts/app.blade.php`, o envolverlo en su propia cláusula `@if(auth()->user()->esAdmin())`.

---

### DEF-002: Regla de validación residual de reCAPTCHA en el controlador de inicio de sesión sin integración en la vista

```yaml
ID: DEF-002
Caso de Prueba: CP-005 (y relacionado a RF-008)
Requisito Asociado: RF-008
Módulo: Autenticación
Título: Declaración de validación 'g-recaptcha-response' => 'nullable' residual sin widget en formulario de login
Severidad: Baja
Prioridad: Baja
Estado: PENDIENTE DE CORRECCIÓN
Fecha de Detección: 2026-09-22
Responsable: Equipo de Desarrollo / Auditoría QA
```

- **Descripción:**  
  En el controlador `app/Http/Controllers/Auth/LoginController.php`, dentro del método `login()`, la definición del validador de peticiones incluye la regla `'g-recaptcha-response' => 'nullable'` (línea 23). No obstante, al auditar la vista del formulario de acceso (`resources/views/auth/login.blade.php`), no existe ningún elemento HTML de reCAPTCHA, no se importan los scripts oficiales de Google (`https://www.google.com/recaptcha/api.js`), y el archivo de configuración de entorno `.env.example` carece de claves API (`RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY`). Aunque la regla `nullable` no rompe el flujo de autenticación estándar (permite valores nulos sin emitir fallos), representa código muerto y engañoso que sugiere la existencia de una capa de seguridad antibot inexistente.
- **Pasos para Reproducir:**  
  1. Abrir el archivo `app/Http/Controllers/Auth/LoginController.php` y revisar la línea 23.
  2. Abrir el archivo `resources/views/auth/login.blade.php` y buscar referencias a `recaptcha` o `g-recaptcha`.
  3. Revisar el archivo `.env.example`.
- **Resultado Esperado:**  
  Consistencia estricta entre el contrato de validación del backend y la estructura del formulario en el frontend. Si reCAPTCHA no está implementado, no deben existir reglas asociadas en el controlador; si es un requisito de seguridad, debe incorporarse el widget interactivo.
- **Resultado Obtenido:**  
  El backend valida la presencia opcional de un campo que el frontend jamás envía ni soporta, generando una discrepancia arquitectónica documentada en `02_Requisitos_Verificables.md` como `REQUIERE VERIFICACIÓN`.
- **Evidencia Técnica:**  
  - `app/Http/Controllers/Auth/LoginController.php` (L20-24):  
    ```php
    $request->validate([
        'email'    => 'required|email|max:255',
        'password' => 'required|min:6|max:255',
        'g-recaptcha-response' => 'nullable',
    ], ...);
    ```
  - Búsqueda en `resources/views/auth/login.blade.php`: Cero coincidencias de la cadena `recaptcha`.
- **Causa Probable:**  
  Residuo de código proveniente de un template comercial o scaffolding previo donde se removió la integración visual de Google reCAPTCHA pero se olvidó depurar la matriz de validación en el controlador.
- **Corrección Aplicada / Recomendada:**  
  Eliminar la línea `'g-recaptcha-response' => 'nullable',` en `LoginController.php` para depurar el código de validación, o implementar formalmente la clave y el componente en Blade si el proyecto exigiese protección antibot.

---

### DEF-003: Consulta analítica `$topProductos` en Dashboard no filtra productos en papelera (`SoftDeletes`) debido al uso de `JOIN` crudo

```yaml
ID: DEF-003
Caso de Prueba: CP-019, CP-020
Requisito Asociado: RF-014, RF-016, RNF-008
Módulo: Dashboard / Persistencia
Título: Consulta de ranking de productos más vendidos omite filtro de soft-deletes en tablas relacionadas
Severidad: Media
Prioridad: Media
Estado: PENDIENTE DE CORRECCIÓN
Fecha de Detección: 2026-09-22
Responsable: Equipo de Desarrollo / Auditoría QA
```

- **Descripción:**  
  En el archivo de rutas `routes/web.php` (líneas 71 a 80), la métrica del Dashboard encargada de listar los 5 productos con mayor facturación (`$topProductos`) se construye a partir de una consulta con `JOIN` relacional directo sobre la tabla `detalle_pedidos`:
  ```php
  DetallePedido::join('productos', 'detalle_pedidos.idProducto', '=', 'productos.idProducto')
      ->join('pedidos', 'detalle_pedidos.idPedido', '=', 'pedidos.idPedido')
      ->where('pedidos.estado', '!=', 'anulado')
      ...
  ```
  Debido a que el modelo `DetallePedido` no utiliza el trait `SoftDeletes` y la consulta se formula mediante métodos de Query Builder crudo (`join()`), Eloquent **no inyecta automáticamente** la cláusula `WHERE productos.deleted_at IS NULL` ni `WHERE pedidos.deleted_at IS NULL`. Como consecuencia, si un producto vendido en el pasado es eliminado lógicamente (enviado a la papelera mediante soft-delete), dicho producto continúa apareciendo en el ranking de los productos más vendidos del catálogo activo. Esto crea una inconsistencia con los contadores de inventario del mismo dashboard (`$stockBajoCant` e `$inventarioValuado`), los cuales sí excluyen automáticamente registros en papelera al originarse en el modelo `Producto`.
- **Pasos para Reproducir:**  
  1. Registrar una venta de 20 unidades del "Producto A".
  2. En el módulo de productos, eliminar lógicamente el "Producto A" (`DELETE /productos/{id}`).
  3. Ingresar al panel principal `/dashboard`.
  4. Revisar la tabla "Top Productos Más Vendidos".
- **Resultado Esperado:**  
  Las métricas de catálogo del panel de control deben mantener coherencia relacional: los productos en papelera no deben figurar como productos activos destacados, o deben estar debidamente etiquetados como dados de baja, aplicando el filtro `whereNull('productos.deleted_at')`.
- **Resultado Obtenido:**  
  El producto enviado a papelera sigue figurando en el ranking de productos líderes en ventas sin distinción respecto a los productos vigentes en catálogo.
- **Evidencia Técnica:**  
  `routes/web.php` líneas 71-80:  
  ```php
  $topProductos = DetallePedido::join('productos', 'detalle_pedidos.idProducto', '=', 'productos.idProducto')
      ->join('pedidos', 'detalle_pedidos.idPedido', '=', 'pedidos.idPedido')
      ->where('pedidos.estado', '!=', 'anulado')
      ->selectRaw('productos.idProducto, productos.nombre,
                   SUM(detalle_pedidos.cantidad) as unidades,
                   SUM(detalle_pedidos.subtotal) as ingresos')
      ->groupBy('productos.idProducto', 'productos.nombre')
      ->orderByDesc('ingresos')
      ->limit(5)
      ->get();
  ```
- **Causa Probable:**  
  Uso de un `INNER JOIN` de base de datos directamente desde el modelo pivote/detalle sin incorporar explícitamente las condiciones de auditoría de eliminación lógica de las entidades padre (`productos` y `pedidos`).
- **Corrección Aplicada / Recomendada:**  
  Agregar los filtros explícitos de eliminación lógica en la consulta de `routes/web.php`:
  ```php
  ->whereNull('productos.deleted_at')
  ->whereNull('pedidos.deleted_at')
  ```

---

### DEF-004: Modelo Eloquent `User` carece de método de relación inversa `pedidos()` hacia el modelo `Pedido`

```yaml
ID: DEF-004
Caso de Prueba: CP-BD-006, CP-INT-027
Requisito Asociado: RF-064, RNF-008
Módulo: Gestión de Usuarios / Modelos Eloquent
Título: Ausencia de método relacional 'pedidos()' en modelo User impide navegación bidireccional
Severidad: Baja
Prioridad: Baja
Estado: PENDIENTE DE CORRECCIÓN
Fecha de Detección: 2026-09-22
Responsable: Equipo de Desarrollo / Auditoría QA
```

- **Descripción:**  
  En el modelo `app/Models/Pedido.php` (líneas 40 a 43), existe la relación de pertenencia correctamente implementada hacia el usuario cajero:
  ```php
  public function usuario()
  {
      return $this->belongsTo(User::class, 'idUsuario', 'id');
  }
  ```
  Sin embargo, en el modelo `app/Models/User.php` (líneas 1 a 45), no se ha definido el método de relación inversa `public function pedidos()`. Si en una vista administrativa o proceso de auditoría se intentara obtener el historial de transacciones emitidas por un usuario determinado mediante `$usuario->pedidos`, el motor de Laravel lanzará una excepción fatal `BadMethodCallException: Call to undefined method App\Models\User::pedidos()`.
- **Pasos para Reproducir:**  
  1. Abrir la consola interactiva de Laravel: `php artisan tinker`.
  2. Ejecutar la instrucción: `$u = App\Models\User::first(); $u->pedidos;`.
- **Resultado Esperado:**  
  Eloquent debe retornar una instancia de `Illuminate\Database\Eloquent\Collection` con el conjunto de pedidos asociados al usuario consultado.
- **Resultado Obtenido:**  
  Se interrumpe la ejecución con el error: `BadMethodCallException: Call to undefined method App\Models\User::pedidos()`.
- **Evidencia Técnica:**  
  Inspección completa de `app/Models/User.php`: el modelo únicamente contiene los traits `HasFactory`, `Notifiable`, los atributos `$fillable`, `$hidden`, el método `casts()` y los helpers booleanos `esAdmin()` y `esInvitado()`. Carece por completo de declaraciones relacionales.
- **Causa Probable:**  
  Diseño unidireccional de relaciones en la fase inicial del proyecto, donde las consultas de pedidos solo se requerían desde la venta hacia el usuario para desplegar el nombre del cajero en la factura.
- **Corrección Aplicada / Recomendada:**  
  Incorporar el método relacional en `app/Models/User.php`:
  ```php
  public function pedidos()
  {
      return $this->hasMany(Pedido::class, 'idUsuario', 'id');
  }
  ```

---

### DEF-005: Restricciones físicas de motor (`CHECK` e `INT UNSIGNED`) no son evaluadas en la suite de pruebas automatizadas (SQLite in-memory)

```yaml
ID: DEF-005
Caso de Prueba: CP-047, CP-081, CP-BD-001
Requisito Asociado: RNF-006, RF-052
Módulo: Base de Datos / Integridad de Motor
Título: Restricción física CHECK (stock >= 0) y tipo UNSIGNED omitidos en pruebas automáticas por limitación de driver SQLite
Severidad: Media
Prioridad: Media
Estado: REQUIERE VERIFICACIÓN (en entorno MySQL real)
Fecha de Detección: 2026-09-22
Responsable: Administrador de Base de Datos / QA
```

- **Descripción:**  
  En las migraciones `database/migrations/2026_07_30_070000_fix_database_consistency.php` (líneas 53 a 55) y `2026_09_14_044922_make_stock_and_cantidad_unsigned.php` (líneas 27 a 30), las sentencias DDL encargadas de aplicar la restricción física `CHECK (stock >= 0)` y modificar las columnas a `INT UNSIGNED` están condicionadas explícitamente al motor MySQL mediante la cláusula `if (DB::getDriverName() === 'mysql')`. La configuración de pruebas automatizadas (`phpunit.xml`, líneas 25 y 26) utiliza una base de datos SQLite en memoria (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`). Por consiguiente, la suite de pruebas automáticas (`php artisan test`) valida la consistencia de inventario únicamente a través de la capa de aplicación (`lockForUpdate()` y validación en `PedidoController`). Si existiese una vía alterna o fallo de código que permitiese insertar stock negativo directamente vía DML, SQLite en memoria lo aceptará sin disparar una violación de integridad física de motor.
- **Pasos para Reproducir:**  
  1. Ejecutar la consola de pruebas o tinker bajo entorno de pruebas SQLite.
  2. Ejecutar: `DB::table('productos')->insert(['nombre' => 'Test', 'codigo' => '999', 'precio' => 10, 'stock' => -5, 'estado' => 'activo']);`.
- **Resultado Esperado (en Producción / MySQL 8):**  
  El motor debe rechazar la instrucción con el error de integridad: `Integrity constraint violation: 3819 Check constraint 'chk_productos_stock_no_negativo' is violated`.
- **Resultado Obtenido (en Testing / SQLite):**  
  La fila se inserta exitosamente en la base de datos de pruebas porque SQLite no recibió la directiva `CHECK` ni aplica tipado no negativo en migraciones modificadas con `ALTER TABLE`.
- **Evidencia Técnica:**  
  - `2026_07_30_070000_fix_database_consistency.php` (L53-55):  
    ```php
    if (DB::getDriverName() === 'mysql') {
        DB::statement('ALTER TABLE productos ADD CONSTRAINT chk_productos_stock_no_negativo CHECK (stock >= 0)');
    }
    ```
  - `2026_09_14_044922_make_stock_and_cantidad_unsigned.php` (L27-30):  
    ```php
    if (DB::getDriverName() === 'mysql') {
        DB::statement('ALTER TABLE productos MODIFY stock INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE detalle_pedidos MODIFY cantidad INT UNSIGNED NOT NULL');
    }
    ```
- **Causa Probable:**  
  Limitación técnica documentada de SQLite: no permite añadir restricciones `CHECK` a tablas preexistentes mediante `ALTER TABLE` sin recrear la tabla completa, y carece de representación física nativa para campos enteros estrictamente sin signo.
- **Corrección Aplicada / Recomendada:**  
  El comportamiento está mitigado en desarrollo para no romper la ejecución de PHPUnit. Como corrección para la garantía de calidad, se recomienda configurar un servicio de base de datos MySQL 8 en el flujo de CI/CD para ejecutar una suite de pruebas complementaria que audite las restricciones físicas reales.

---

### DEF-012: Presencia de controladores y vistas de scaffolding huérfanos sin mapeo en rutas activas

```yaml
ID: DEF-012
Caso de Prueba: Sin caso formal en 04 (relacionado a arquitectura)
Requisito Asociado: RF-007, RNF-005
Módulo: Arquitectura / Código Residual
Título: Archivos de scaffolding huérfanos (HomeController, home.blade.php, welcome.blade.php) en el árbol del proyecto
Severidad: Baja
Prioridad: Baja
Estado: REQUIERE VERIFICACIÓN
Fecha de Detección: 2026-09-22
Responsable: Equipo de Desarrollo
```

- **Descripción:**  
  Dentro del árbol de archivos del aplicativo persisten componentes generados por el scaffolding inicial de Laravel:
  - `app/Http/Controllers/HomeController.php`
  - `resources/views/home.blade.php`
  - `resources/views/welcome.blade.php`
  Al realizar la auditoría de rutas en `routes/web.php`, se comprueba que la ruta raíz `/` es gestionada directamente por una función anónima (Closure) que redirige según el estado de autenticación (`/login` o `/dashboard`), y no existe ninguna referencia a `HomeController` ni a las vistas `home` o `welcome`. Estos archivos representan código inactivo (dead code) que no aporta valor operativo al sistema.
- **Pasos para Reproducir:**  
  1. Ejecutar `php artisan route:list`.
  2. Buscar rutas que invoquen a `HomeController` o retornen `welcome.blade.php`.
- **Resultado Esperado:**  
  El repositorio debe contener exclusivamente código fuente, controladores y vistas enlazados a rutas operativas o servicios del sistema.
- **Resultado Obtenido:**  
  Los archivos existen físicamente en disco pero no tienen ninguna ruta asociada en el enrutador de Laravel.
- **Evidencia Técnica:**  
  `routes/web.php` líneas 15 a 21:  
  ```php
  Route::get('/', function () {
      if (auth()->check()) {
          return redirect()->route('dashboard');
      }
      return redirect()->route('login');
  });
  ```
- **Causa Probable:**  
  Instalación inicial del paquete `laravel/ui` o scaffolding de autenticación donde se sustituyó la pantalla de bienvenida por la redirección directa al login sin depurar los archivos generados automáticamente.
- **Corrección Aplicada / Recomendada:**  
  Eliminar los archivos no utilizados `HomeController.php`, `home.blade.php` y `welcome.blade.php` para mantener un árbol de código limpio y facilitar futuras auditorías técnicas.

---

## 4. Registro de Defectos Históricos Corregidos en Desarrollo

Esta sección documenta formalmente los 6 defectos críticos identificados y subsanados durante la fase de estabilización del esquema de base de datos y dependencias (`docs/BASE_DE_DATOS.md`, Sección 4). Su inclusión es indispensable para demostrar la trazabilidad histórica de resolución de no conformidades del proyecto.

### DEF-006: Conflicto por migración duplicada de la tabla `users` que abortaba la inicialización del esquema

```yaml
ID: DEF-006
Caso de Prueba: CP-HIST-001 (Migraciones iniciales DDL)
Requisito Asociado: RNF-001, RNF-008
Módulo: Base de Datos / Migraciones
Título: Intento de doble creación de la tabla 'users' provocaba fallo fatal en migrate:fresh
Severidad: Crítica
Prioridad: Alta
Estado: CORREGIDO
Fecha de Detección: 2026-07-28
Responsable: Equipo de Desarrollo / Administrador de BD
```

- **Descripción:**  
  Existían dos archivos de migración independientes que intentaban ejecutar `Schema::create('users')`: la migración nativa de Laravel (`2014_10_12_000000_create_users_table.php`) y una migración posterior agregada durante el desarrollo (`2024_03_20_000001_create_users_table.php`). Al ejecutar el comando de instalación desde cero `php artisan migrate:fresh`, el proceso abortaba con una excepción fatal de base de datos por tabla existente.
- **Pasos para Reproducir (Histórico):**  
  1. Disponer del repositorio original sin parchar.
  2. Ejecutar en consola: `php artisan migrate:fresh`.
- **Resultado Esperado:**  
  Creación ordenada y secuencial de todas las tablas relacionales sin colisiones DDL.
- **Resultado Obtenido:**  
  Excepción de SQL: `SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'users' already exists`, deteniendo por completo el aprovisionamiento de la base de datos.
- **Evidencia Técnica:**  
  Documentado en `docs/BASE_DE_DATOS.md`, Sección 4, Ítem 1.
- **Causa Probable:**  
  Creación errónea de una segunda migración de tipo `create` en lugar de una migración de tipo `table` (modificación) al incorporar los campos personalizados `rol` y `foto`.
- **Corrección Aplicada:**  
  Se refactorizó la segunda migración (`2024_03_20_000001_add_rol_and_foto_to_users_table.php`) transformándola en un `ALTER TABLE` mediante `Schema::table('users', function (Blueprint $table) { $table->string('rol')->default('invitado'); $table->string('foto')->nullable(); });`.

---

### DEF-007: Presencia de migración obsoleta para tabla `password_resets` no soportada por Laravel 12

```yaml
ID: DEF-007
Caso de Prueba: CP-HIST-002 (Esquema de autenticación)
Requisito Asociado: RF-001, RNF-003
Módulo: Base de Datos / Autenticación
Título: Coexistencia de tabla redundante 'password_resets' junto al estándar 'password_reset_tokens'
Severidad: Baja
Prioridad: Baja
Estado: CORREGIDO
Fecha de Detección: 2026-07-29
Responsable: Equipo de Desarrollo / Administrador de BD
```

- **Descripción:**  
  El directorio de migraciones contenía la definición de la tabla `password_resets` (propia de versiones previas de Laravel), coexistiendo de forma inútil con la tabla `password_reset_tokens` (la cual es la utilizada oficialmente por el subsistema de autenticación en Laravel 12 según `config/auth.php`). Esto generaba tablas fantasma sin uso en la base de datos.
- **Pasos para Reproducir (Histórico):**  
  1. Ejecutar `php artisan migrate:fresh`.
  2. Inspeccionar la base de datos mediante cliente SQL (`SHOW TABLES`).
- **Resultado Esperado:**  
  Únicamente tablas activas y con función demostrable dentro del esquema del sistema.
- **Resultado Obtenido:**  
  Creación de la tabla `password_resets` que permanecía permanentemente vacía y sin enlaces en el framework.
- **Evidencia Técnica:**  
  Documentado en `docs/BASE_DE_DATOS.md`, Sección 4, Ítem 2.
- **Causa Probable:**  
  Arrastre de migraciones predeterminadas de proyectos antiguos al actualizar o migrar a Laravel 12.
- **Corrección Aplicada:**  
  Se eliminó el archivo de migración obsoleto, preservando exclusivamente la migración oficial de `password_reset_tokens`.

---

### DEF-008: Restricción `CHECK` con sintaxis no portable provocaba fallo en ejecuciones SQLite

```yaml
ID: DEF-008
Caso de Prueba: CP-HIST-003 (Portabilidad DDL / Testing)
Requisito Asociado: RNF-006, RNF-012
Módulo: Base de Datos / Migraciones
Título: Sentencia 'ALTER TABLE ADD CONSTRAINT CHECK' rompía 'migrate:fresh' en SQLite
Severidad: Alta
Prioridad: Alta
Estado: CORREGIDO
Fecha de Detección: 2026-07-30
Responsable: Equipo de Desarrollo / Administrador de BD
```

- **Descripción:**  
  La migración destinada a garantizar que el stock de productos no adquiriese valores negativos ejecutaba de forma incondicional una sentencia de SQL crudo: `DB::statement('ALTER TABLE productos ADD CONSTRAINT chk_productos_stock_no_negativo CHECK (stock >= 0)')`. Aunque esta sintaxis es válida en MySQL 8.0.16+, SQLite no admite agregar restricciones `CHECK` a tablas existentes mediante `ALTER TABLE`, provocando que cualquier intento de ejecutar pruebas automatizadas o levantar el entorno local en SQLite fracasara de inmediato.
- **Pasos para Reproducir (Histórico):**  
  1. Configurar `DB_CONNECTION=sqlite` en el archivo `.env`.
  2. Ejecutar `php artisan migrate:fresh`.
- **Resultado Esperado:**  
  Ejecución portable del esquema en entornos de testing locales y motores de producción.
- **Resultado Obtenido:**  
  Excepción DDL: `QueryException: near "ADD": syntax error` abortando la migración en SQLite.
- **Evidencia Técnica:**  
  Documentado en `docs/BASE_DE_DATOS.md`, Sección 4, Ítem 3; migración `database/migrations/2026_07_30_070000_fix_database_consistency.php`.
- **Causa Probable:**  
  Uso de DDL específico de MySQL sin encapsulamiento condicional de driver en la capa de base de datos de Laravel.
- **Corrección Aplicada:**  
  Se condicionó la ejecución de la sentencia mediante `if (DB::getDriverName() === 'mysql')`, permitiendo la portabilidad a SQLite mientras se preserva la integridad física en MySQL.

---

### DEF-009: Hash de contraseña corrupto o inválido en el respaldo de base de datos `.sql`

```yaml
ID: DEF-009
Caso de Prueba: CP-HIST-004 (Autenticación / Restauración SQL)
Requisito Asociado: RF-001, RNF-001
Módulo: Seguridad / Base de Datos
Título: Hash bcrypt inválido en volcado supermarket.sql impedía autenticación tras importación
Severidad: Crítica
Prioridad: Alta
Estado: CORREGIDO
Fecha de Detección: 2026-07-30
Responsable: Administrador de BD / Seguridad
```

- **Descripción:**  
  El volcado SQL provisto inicialmente en la documentación (`supermarket.sql`) contenía cadenas de hash bcrypt generadas incorrectamente o no coincidentes con las contraseñas documentadas para los usuarios de prueba (`admin123` e `invitado123`). Al importar el script directamente en MySQL, ningún usuario podía autenticarse en el formulario `/login`, lo que habría invalidado cualquier demostración en vivo.
- **Pasos para Reproducir (Histórico):**  
  1. Importar `supermarket.sql` en un servidor MySQL limpio.
  2. Ingresar a `/login` e ingresar: `admin@supermarket.com` / `admin123`.
- **Resultado Esperado:**  
  Autenticación exitosa y redirección automática al panel `/dashboard`.
- **Resultado Obtenido:**  
  Rechazo de credenciales con mensaje de error "Estas credenciales no coinciden con nuestros registros", impidiendo todo acceso.
- **Evidencia Técnica:**  
  Documentado en `docs/BASE_DE_DATOS.md`, Sección 4, Ítem 4.
- **Causa Probable:**  
  Exportación de datos de un entorno donde las contraseñas fueron modificadas arbitrariamente o procesadas con un costo/salt diferente al estándar de la aplicación.
- **Corrección Aplicada:**  
  Se regeneraron los hashes utilizando la función nativa `Hash::make()` de Laravel con costo estándar 12, validando su correspondencia mediante `password_verify('admin123', $hash)` antes de generar el volcado definitivo `supermarket.sql`.

---

### DEF-010: Disparidad de credenciales entre `DatabaseSeeder` y el volcado `supermarket.sql`

```yaml
ID: DEF-010
Caso de Prueba: CP-HIST-005 (Semillas y Despliegue)
Requisito Asociado: RF-001, RF-002
Módulo: Base de Datos / Seeders
Título: Desincronización en credenciales y cuentas de prueba según la vía de instalación
Severidad: Media
Prioridad: Media
Estado: CORREGIDO
Fecha de Detección: 2026-07-30
Responsable: Equipo de Desarrollo / Administrador de BD
```

- **Descripción:**  
  Existía una inconsistencia en las cuentas de usuario sembradas según la vía de instalación elegida: el comando `php artisan db:seed` creaba un conjunto de usuarios con ciertas credenciales, mientras que la importación de `supermarket.sql` creaba usuarios con otros correos y contraseñas. Esto provocaba desorientación a los evaluadores del proyecto y fallos en las pruebas funcionales.
- **Pasos para Reproducir (Histórico):**  
  1. Instalar el sistema mediante `php artisan migrate --seed` y verificar usuarios.
  2. Instalar el sistema mediante importación de `supermarket.sql` y comparar usuarios.
- **Resultado Esperado:**  
  Paridad total y unificada de cuentas y contraseñas independientemente del método de instalación utilizado.
- **Resultado Obtenido:**  
  Discrepancia en correos y nombres de usuario, rompiendo los manuales de usuario y pruebas.
- **Evidencia Técnica:**  
  Documentado en `docs/BASE_DE_DATOS.md`, Sección 4, Ítem 5.
- **Causa Probable:**  
  Falta de sincronización entre el archivo de seeders de Laravel (`database/seeders/DatabaseSeeder.php`) y las exportaciones manuales realizadas desde herramientas gráficas como phpMyAdmin.
- **Corrección Aplicada:**  
  Se unificaron ambos orígenes con exactamente los mismos datos:
  - **Administrador:** `admin@supermarket.com` / `admin123`
  - **Invitado / Cajero:** `invitado@supermarket.com` / `invitado123`

---

### DEF-011: Omisión de la extensión `ext-bcmath` en `composer.json` provocaba caída fatal en registro de ventas

```yaml
ID: DEF-011
Caso de Prueba: CP-HIST-006 (Dependencias / Ventas)
Requisito Asociado: RF-043, RNF-004
Módulo: Ventas / Dependencias del Sistema
Título: Ausencia de declaración de ext-bcmath en composer.json provocaba error fatal 500 al vender
Severidad: Alta
Prioridad: Alta
Estado: CORREGIDO
Fecha de Detección: 2026-07-30
Responsable: Arquitecto de Software / QA
```

- **Descripción:**  
  El controlador de ventas `app/Http/Controllers/PedidoController.php` requiere obligatoriamente las funciones matemáticas de precisión arbitraria `bcmul()` y `bcadd()` para realizar el cálculo de subtotales y total general de cada pedido sin pérdida por redondeo flotante. No obstante, el archivo de configuración `composer.json` no declaraba la extensión `"ext-bcmath": "*"` en su bloque de dependencias requeridas. Si la aplicación era instalada en un servidor o contenedor PHP que carecía del módulo BCMath compilado, `composer install` finalizaba sin errores, pero al intentar procesar una venta en `/pedidos/create`, la aplicación colapsaba con un error HTTP 500 fatal por función indefinida.
- **Pasos para Reproducir (Histórico):**  
  1. Desplegar la aplicación en un entorno con PHP sin el módulo BCMath activo.
  2. Ejecutar `composer install` (se completaba con éxito falsamente).
  3. Iniciar sesión e intentar registrar una venta.
- **Resultado Esperado:**  
  El gestor de dependencias Composer debe validar los requisitos de plataforma durante la instalación (`composer check-platform-reqs`), impidiendo el despliegue en entornos no aptos.
- **Resultado Obtenido:**  
  Error fatal en tiempo de ejecución: `Error: Call to undefined function bcmul()`, dejando el sistema inutilizable para transacciones de caja.
- **Evidencia Técnica:**  
  Documentado en `docs/BASE_DE_DATOS.md`, Sección 4, Ítem 6; `composer.json` línea 10.
- **Causa Probable:**  
  Uso de extensiones nativas de PHP en la lógica de negocio sin declarar el requisito explícito en el manifiesto de Composer del proyecto.
- **Corrección Aplicada:**  
  Se incorporó `"ext-bcmath": "*"` en la sección `"require"` de `composer.json`, garantizando que la instalación valide la presencia de la extensión antes de permitir la puesta en marcha del sistema.

---

## 5. Matriz Consolidada de Trazabilidad de Defectos

La siguiente matriz establece la correlación bidireccional entre los defectos reportados, los requisitos del sistema (`02_Requisitos_Verificables.md`), los casos de prueba asociados (`04_Casos_Prueba.md`, `07_Pruebas_MySQL_Persistencia.md`, `08_Pruebas_Integracion.md`) y su estado actual:

| ID Defecto | Título Sintético | Módulo Afectado | Requisito Relacionado | Caso de Prueba | Severidad | Prioridad | Estado Actual |
| :---: | :--- | :--- | :---: | :---: | :---: | :---: | :---: |
| **DEF-001** | Enlace a Reportes visible en sidebar para rol invitado $\rightarrow$ 403 | RBAC / UI | `RF-012`, `RF-013` | CP-017, CP-INT-004 | Media | Media | PENDIENTE |
| **DEF-002** | Validación residual 'g-recaptcha-response' en LoginController | Autenticación | `RF-008` | CP-005 | Baja | Baja | PENDIENTE |
| **DEF-003** | `$topProductos` en Dashboard incluye ítems en papelera por JOIN crudo | Dashboard | `RF-014`, `RNF-008` | CP-019, CP-020 | Media | Media | PENDIENTE |
| **DEF-004** | Modelo `User` carece de relación inversa `pedidos()` | Usuarios / Eloquent | `RF-064` | CP-BD-006 | Baja | Baja | PENDIENTE |
| **DEF-005** | Restricciones CHECK y UNSIGNED no evaluadas en suite SQLite | Base de Datos | `RNF-006`, `RF-052` | CP-047, CP-BD-001 | Media | Media | REQUIERE VERIF. |
| **DEF-006** | Migración duplicada de tabla `users` rompía `migrate:fresh` | BD / Migraciones | `RNF-001`, `RNF-008` | CP-HIST-001 | Crítica | Alta | **CORREGIDO** |
| **DEF-007** | Tabla huérfana `password_resets` coexistía con tokens | BD / Auth | `RF-001`, `RNF-003` | CP-HIST-002 | Baja | Baja | **CORREGIDO** |
| **DEF-008** | Sintaxis no portable de CHECK rompía migraciones en SQLite | BD / Portabilidad | `RNF-006`, `RNF-012` | CP-HIST-003 | Alta | Alta | **CORREGIDO** |
| **DEF-009** | Hash bcrypt inválido en volcado `.sql` impedía autenticación | BD / Seguridad | `RF-001`, `RNF-001` | CP-HIST-004 | Crítica | Alta | **CORREGIDO** |
| **DEF-010** | Disparidad de credenciales entre Seeder y volcado SQL | BD / Semillas | `RF-001`, `RF-002` | CP-HIST-005 | Media | Media | **CORREGIDO** |
| **DEF-011** | Extensión `ext-bcmath` omitida en composer.json provocaba 500 | Ventas / Dependencias | `RF-043`, `RNF-004` | CP-HIST-006 | Alta | Alta | **CORREGIDO** |
| **DEF-012** | Archivos de scaffolding huérfanos sin ruta (HomeController, vistas) | Arquitectura / Código | `RF-007`, `RNF-005` | N/A | Baja | Baja | REQUIERE VERIF. |

---

## 6. Evidencia Real de Ejecución de Pruebas Automatizadas

Para validar que no existen fallos ni regresiones de código no documentados, se ejecutó la suite completa de pruebas automatizadas del proyecto mediante la herramienta oficial PHPUnit integrada en el framework Laravel.

### 6.1. Registro de Ejecución en Tiempo Real
- **Comando:** `php artisan test`
- **Fecha de Ejecución:** 22 de Septiembre de 2026, 17:00 UTC-4
- **Entorno:** PHP 8.2+ / Laravel 12.0 / SQLite in-memory (`:memory:`)
- **Resultado General:** **41 pruebas ejecutadas, 41 PASADAS (100.0 %), 0 FALLIDAS, 155 aserciones superadas**.
- **Duración Total:** 8.73 segundos.

### 6.2. Detalle de Pruebas Automatizadas Verificadas

```text
   PASS  Tests\Unit\SkuTest
  ✓ formatea con prefijo y ceros a la izquierda                                                                  0.44s  
  ✓ no trunca ids mas grandes que el ancho de relleno

   PASS  Tests\Unit\TimezoneTest
  ✓ la aplicacion usa el horario de bolivia                                                                      1.08s  

   PASS  Tests\Feature\CategoriaIntegrityTest
  ✓ no se puede eliminar una categoria con productos asociados                                                   1.49s  
  ✓ no se permiten dos categorias activas con el mismo nombre                                                    0.10s  

   PASS  Tests\Feature\ClienteBusquedaTest
  ✓ busqueda filtra por nombre o email                                                                           0.34s  

   PASS  Tests\Feature\HomeRedirectTest
  ✓ guest root redirects to login                                                                                0.02s  
  ✓ authenticated user root redirects to dashboard                                                               0.02s  
  ✓ login page shows branding                                                                                    0.05s  

   PASS  Tests\Feature\PaginacionYDashboardTest
  ✓ listados paginados cargan correctamente con muchos registros                                                 0.09s  
  ✓ dashboard excluye pedidos anulados de metricas de ventas                                                     0.04s  
  ✓ reportes ventas pagina la tabla pero las metricas usan el total filtrado                                     0.04s  

   PASS  Tests\Feature\PaginasDeErrorTest
  ✓ las vistas de error personalizadas renderizan sin usuario autenticado                                        0.02s  
  ✓ ruta inexistente devuelve 404                                                                                0.11s  

   PASS  Tests\Feature\PedidoAnulacionStockTest
  ✓ completado a anulado devuelve stock y no lo vuelve a descontar                                               0.09s  
  ✓ anulado a anulado no toca el stock                                                                           0.02s  
  ✓ anulado a completado reactiva y descuenta stock                                                              0.02s  
  ✓ completado a completado edita cantidad correctamente                                                         0.02s  
  ✓ anular no valida stock disponible                                                                            0.02s  

   PASS  Tests\Feature\PedidoEliminarAnuladoTest
  ✓ eliminar pedido completado restaura stock                                                                    0.05s  
  ✓ eliminar pedido anulado no crea stock fantasma                                                               0.03s  

   PASS  Tests\Feature\PedidoInvitadoYDescargasTest
  ✓ invitado puede ver formulario de crear pedido                                                                0.03s  
  ✓ invitado puede registrar un pedido                                                                           0.03s  
  ✓ invitado no puede editar ni eliminar pedido                                                                  0.03s  
  ✓ administrador si puede editar y eliminar pedido                                                              0.02s  
  ✓ descargar qr producto                                                                                        0.07s  
  ✓ descargar barcode producto                                                                                   0.14s  

   PASS  Tests\Feature\PedidoStockConsistencyTest
  ✓ registrar pedido descuenta stock y calcula el total                                                          0.14s  
  ✓ no se puede pedir mas stock del disponible                                                                   0.02s  
  ✓ eliminar un pedido restituye el stock                                                                        0.05s  

   PASS  Tests\Feature\ProveedorTest
  ✓ usuario autenticado puede ver listado proveedores                                                            0.03s  
  ✓ invitado no puede crear proveedor                                                                            0.03s  
  ✓ admin puede crear proveedor                                                                                  0.02s  
  ✓ validacion duplicado y restauracion desde papelera                                                           0.02s  
  ✓ admin puede editar proveedor                                                                                 0.02s  
  ✓ no se puede eliminar proveedor con productos asociados                                                       0.02s  
  ✓ proveedor sin productos se elimina correctamente                                                             0.02s  

   PASS  Tests\Feature\ReporteFiltrosVaciosTest
  ✓ reporte ventas con cero registros retorna 200                                                                0.03s  
  ✓ filtro fechas sin coincidencias retorna 200                                                                  0.06s  

   PASS  Tests\Feature\ReporteVentasMetricasTest
  ✓ unidades vendidas excluye pedidos anulados                                                                   0.04s  

   PASS  Tests\Feature\SmokeVistasModificadasTest
  ✓ paginas modificadas cargan para ambos roles                                                                  0.10s  

  Tests:    41 passed (155 assertions)
  Duration: 8.73s
```

### 6.3. Conclusión del Análisis de Ejecución
La ausencia de fallos en la suite de 41 pruebas demuestra que los defectos históricos fueron resueltos satisfactoriamente y que las reglas de negocio esenciales (restitución de stock, anulación de pedidos, exclusión de ventas anuladas, protección contra eliminación de categorías y proveedores enlazados) se encuentran operativas y estables. Los defectos pendientes detectados en este reporte (`DEF-001` a `DEF-005`) corresponden a **brechas entre capas no cubiertas por las pruebas unitarias existentes**: presentación en vista Blade vs middleware, consultas analíticas con `JOIN` relacional crudo sin scopes de soft-delete, y discrepancias de tipado estricto entre el motor SQLite en memoria y el motor físico MySQL.

---

## 7. Plan de Acción y Recomendaciones Técnicas de Corrección

Con base en la auditoría realizada, se definen las siguientes acciones correctivas para cerrar las discrepancias pendientes:

1. **Parche de UI en Menú Lateral (`DEF-001`):**
   - Modificar `resources/views/layouts/app.blade.php` para reubicar o envolver el elemento de enlace a `reportes.ventas` con `@if(auth()->user()->esAdmin())`.
   - Impacto: Cierre de la discrepancia de navegación y eliminación del error 403 innecesario para usuarios con rol invitado.
2. **Depuración de Validación en Login (`DEF-002`):**
   - Eliminar la línea `'g-recaptcha-response' => 'nullable'` en `app/Http/Controllers/Auth/LoginController.php`.
   - Impacto: Eliminación de código muerto y consistencia total con el formulario visual.
3. **Refactorización de Consulta Analítica en Dashboard (`DEF-003`):**
   - Agregar `->whereNull('productos.deleted_at')->whereNull('pedidos.deleted_at')` a la consulta de `$topProductos` en `routes/web.php`.
   - Impacto: Integridad analítica estricta que impide que productos dados de baja figuren como productos líderes del inventario activo.
4. **Completitud del Modelo Eloquent `User` (`DEF-004`):**
   - Declarar el método `pedidos()` en `app/Models/User.php` para habilitar la relación bidireccional `hasMany(Pedido::class, 'idUsuario', 'id')`.
   - Impacto: Capacidad de auditoría directa de ventas emitidas por usuario cajero sin excepciones de método no definido.
5. **Configuración de Pipeline de Integración Continua con MariaDB/MySQL (`DEF-005`):**
   - Configurar un workflow de GitHub Actions o GitLab CI con un servicio de MySQL 8 para ejecutar las pruebas que verifiquen las restricciones físicas de motor (`CHECK` y `UNSIGNED`), complementando el ciclo rápido de pruebas locales con SQLite.
   - Impacto: Verificación automatizada de la defensa en profundidad en base de datos.
6. **Limpieza de Archivos Residuales de Scaffolding (`DEF-012`):**
   - Remover `HomeController.php`, `home.blade.php` y `welcome.blade.php`.
   - Impacto: Reducción de deuda técnica y facilidad de mantenimiento.
