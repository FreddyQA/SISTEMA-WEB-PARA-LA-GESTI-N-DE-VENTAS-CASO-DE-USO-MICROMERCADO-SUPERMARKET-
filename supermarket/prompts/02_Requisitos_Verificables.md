# 02 — Especificación de Requisitos Verificables (SISMICRO / Supermarket)

> **Documento de Requisitos de Software para Pruebas**  
> **Proyecto:** Sistema Web de Ventas e Inventario para el Micromercado SuperMarket  
> **Fecha de Elaboración:** Septiembre 2026  
> **Fuente de Verdad:** Repositorio real de código fuente (Laravel 12 / PHP 8.2+)  
> **Estado de Requisitos:** VERIFICADO (obtenidos directamente de migraciones, rutas, modelos, controladores y vistas)  
> **Estado de Pruebas:** PLANIFICADO / PENDIENTE DE EJECUCIÓN (sin ejecuciones ni marcas PASS/FAIL en esta fase)  

---

## 1. Convenciones y Reglas de Documentación

1. **Nomenclatura:**
   - **Requisitos Funcionales:** `RF-001`, `RF-002`, `RF-003`, ...
   - **Requisitos No Funcionales:** `RNF-001`, `RNF-002`, `RNF-003`, ...
   - **Casos de Prueba Proyectados:** `CP-001`, `CP-002`, ...
2. **Prioridad:**
   - **Alta:** Seguridad, control de acceso, atomicidad de ventas, integridad del stock y preservación contable.
   - **Media:** Operaciones CRUD, validaciones de formularios, búsquedas y paginación.
   - **Baja:** Descargas de códigos auxiliares, diseño visual y vistas informativas.
3. **Estados de Verificación del Requisito:**
   - **VERIFICADO:** La regla de negocio o restricción técnica existe explícitamente y se demuestra en el código fuente del repositorio.
   - **REQUIERE VERIFICACIÓN:** La funcionalidad presenta discrepancias entre capas (ej. vista vs. controlador) o depende de factores externos no demostrables de forma estática en el código.

---

## 2. Requisitos Funcionales (RF)

### 2.1. Módulo de Autenticación y Sesión

| ID | Requisito | Módulo | Prioridad | Criterio de Aceptación (Verificable) | Método de Verificación | Casos Relacionados | Estado |
| :--- | :--- | :--- | :---: | :--- | :--- | :---: | :---: |
| **RF-001** | Inicio de sesión con credenciales válidas | Autenticación | Alta | Al enviar `email` y `password` correctos mediante `POST /login`, el sistema inicia sesión, regenera el ID de sesión y redirige a la ruta `dashboard` con estado HTTP 302. | Prueba de Integración (`LoginController::login`) | CP-001 | VERIFICADO |
| **RF-002** | Rechazo de credenciales inválidas | Autenticación | Alta | Si el `email` no existe o la `password` no coincide, el sistema incrementa el contador de intentos fallidos y retorna al formulario con error `'email' => 'Correo o contraseña incorrectos...'`. | Prueba de Integración (`LoginController::login`) | CP-002 | VERIFICADO |
| **RF-003** | Validación de formato en login | Autenticación | Media | `email` es obligatorio, debe tener formato de correo válido y no superar 255 caracteres; `password` es obligatoria, mínimo 6 y máximo 255 caracteres. Ante infracción, retorna errores específicos en sesión. | Prueba Funcional (`$request->validate`) | CP-003 | VERIFICADO |
| **RF-004** | Bloqueo temporal por fuerza bruta (Rate Limiting) | Autenticación | Alta | Tras 5 intentos fallidos acumulados para una clave `email\|IP`, el método `hasTooManyLoginAttempts` bloquea el acceso durante 120 segundos indicando el tiempo restante exacto. | Prueba de Integración (`ThrottlesLogins`) | CP-004 | VERIFICADO |
| **RF-005** | Persistencia de sesión (Remember Me) | Autenticación | Media | Al marcar el checkbox `remember` (`$request->boolean('remember')`), el sistema emite una cookie persistente de autenticación según la configuración de Laravel. | Prueba Funcional (`Auth::attempt`) | CP-005 | VERIFICADO |
| **RF-006** | Cierre de sesión seguro | Autenticación | Alta | Al emitir `POST /logout`, se desautentica el usuario (`Auth::logout()`), se invalida la sesión, se regenera el token CSRF y se redirige a `/login`. | Prueba de Integración (`LoginController::logout`) | CP-006 | VERIFICADO |
| **RF-007** | Redirección de ruta raíz (`/`) | Autenticación | Media | `GET /` redirige a `dashboard` si `auth()->check()` es verdadero, o redirige a `login` si el usuario es invitado no autenticado. | Prueba de Integración (`routes/web.php:L21-27`) | CP-007 | VERIFICADO |
| **RF-008** | Campo reCAPTCHA en login | Autenticación | Baja | El validador incluye `'g-recaptcha-response' => 'nullable'`. No se observan scripts ni llaves activas en `login.blade.php`. | Inspección de Código (`LoginController:L23`) | CP-008 | REQUIERE VERIFICACIÓN |

---

### 2.2. Módulo de Seguridad y Control de Acceso (RBAC)

| ID | Requisito | Módulo | Prioridad | Criterio de Aceptación (Verificable) | Método de Verificación | Casos Relacionados | Estado |
| :--- | :--- | :--- | :---: | :--- | :--- | :---: | :---: |
| **RF-009** | Protección de rutas para no autenticados | Seguridad | Alta | Cualquier petición no autenticada a rutas dentro del grupo `middleware(['auth'])` es interceptada y redirigida a `login`. | Prueba de Integración (`Authenticate` middleware) | CP-009 | VERIFICADO |
| **RF-010** | Restricción por rol de Administrador | Seguridad | Alta | Rutas asignadas al middleware `rol:administrador` o métodos con `soloDueno()` devuelven respuesta `HTTP 403 Forbidden` si el usuario autenticado tiene rol `invitado`. | Prueba de Integración (`CheckRol`, `soloDueno`) | CP-010 | VERIFICADO |
| **RF-011** | Permisos operativos del rol Invitado | Seguridad | Alta | Un usuario con rol `invitado` puede: acceder a `dashboard`, listar categorías, proveedores, productos y clientes; acceder a `GET /pedidos/create` y registrar ventas (`POST /pedidos`); descargar QR/códigos de barras y exportar productos. | Prueba de Integración (`routes/web.php`) | CP-011 | VERIFICADO |
| **RF-012** | Prohibición operativa del rol Invitado | Seguridad | Alta | Un usuario con rol `invitado` recibe HTTP 403 al intentar: crear/editar/eliminar categorías, proveedores, productos, clientes o usuarios; editar/eliminar pedidos (`pedidos.edit`, `update`, `destroy`); acceder a cualquier ruta de reportes (`/reportes/*`) o papelera de productos. | Prueba de Integración (`routes/web.php`) | CP-012 | VERIFICADO |
| **RF-013** | Visibilidad del menú lateral vs autorización | Seguridad / UI | Media | En `resources/views/layouts/app.blade.php`, el enlace a `reportes.ventas` se muestra a ambos roles, pero la ruta backend rechaza a `invitado` con 403. | Inspección de Código / Smoke Test | CP-013 | REQUIERE VERIFICACIÓN |

---

### 2.3. Módulo Dashboard (Panel de Control)

| ID | Requisito | Módulo | Prioridad | Criterio de Aceptación (Verificable) | Método de Verificación | Casos Relacionados | Estado |
| :--- | :--- | :--- | :---: | :--- | :--- | :---: | :---: |
| **RF-014** | Contadores globales de entidades | Dashboard | Media | La vista recibe y despliega `totalCategorias`, `totalProveedores`, `totalProductos`, `totalClientes` y `totalPedidos` calculados con `count()` sobre los modelos respectivos. | Prueba Funcional (`dashboard` closure) | CP-014 | VERIFICADO |
| **RF-015** | Desglose de estado de pedidos | Dashboard | Media | Se calculan separadamente `totalPedidosCompletados` (`estado = 'completado'`) y `totalPedidosAnulados` (`estado = 'anulado'`). | Prueba Funcional (`dashboard` closure) | CP-015 | VERIFICADO |
| **RF-016** | Sumatoria de ventas netas | Dashboard | Alta | `totalVentas` suma exclusivamente la columna `total` de pedidos donde `estado != 'anulado'`. Los pedidos anulados no suman ingresos. | Prueba Funcional / Integración | CP-016 | VERIFICADO |
| **RF-017** | Valuación total del inventario | Dashboard | Alta | `inventarioValuado` computa la sumatoria de `stock * precio` de todos los productos activos. | Prueba Funcional (`Producto::sum(...)`) | CP-017 | VERIFICADO |
| **RF-018** | Alerta cuantitativa de stock bajo | Dashboard | Media | `stockBajoCant` cuenta el número de productos activos cuyo stock actual es menor o igual a 5 unidades. | Prueba Funcional (`Producto::where('stock', '<=', 5)`) | CP-018 | VERIFICADO |
| **RF-019** | Series temporales compatibles por motor | Dashboard | Media | El agrupamiento de ventas por mes utiliza `DATE_FORMAT(fecha, '%Y-%m')` en MySQL y `strftime('%Y-%m', fecha)` en SQLite según el driver detectado. | Prueba de Integración (`DB::getDriverName()`) | CP-019 | VERIFICADO |
| **RF-020** | Top 5 productos con mayores ingresos | Dashboard | Media | Retorna los 5 productos con mayor `SUM(detalle_pedidos.subtotal)` considerando únicamente pedidos donde `estado != 'anulado'`. | Prueba Funcional (Consulta agregada) | CP-020 | VERIFICADO |
| **RF-021** | Top 5 clientes con mayor compra | Dashboard | Media | Retorna los 5 clientes con mayor `SUM(pedidos.total)` considerando únicamente pedidos no anulados. | Prueba Funcional (Consulta agregada) | CP-021 | VERIFICADO |

---

### 2.4. Módulo de Categorías

| ID | Requisito | Módulo | Prioridad | Criterio de Aceptación (Verificable) | Método de Verificación | Casos Relacionados | Estado |
| :--- | :--- | :--- | :---: | :--- | :--- | :---: | :---: |
| **RF-022** | Paginación y listado de categorías | Categorías | Media | `GET /categorias` pagina a 15 registros por página, ordenados alfabéticamente por `nombre` y con carga ansiosa de relación `productos`. | Prueba Funcional (`CategoriaController::index`) | CP-022 | VERIFICADO |
| **RF-023** | Validación de campos al registrar categoría | Categorías | Media | `nombre` obligatorio, longitud 2-100, regex `^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.\,\-\_]+$`; `descripcion` opcional, máximo 500 caracteres. | Prueba Funcional (`CategoriaController::store`) | CP-023 | VERIFICADO |
| **RF-024** | Unicidad y auto-restauración de categoría | Categorías | Alta | Si se registra una categoría con un `nombre` que ya existe en soft-delete (`trashed`), se restaura (`restore()`), se actualizan sus datos y redirige con mensaje de éxito; si ya existe activa, rechaza con error de validación. | Prueba de Integración (`CategoriaController::store`) | CP-024 | VERIFICADO |
| **RF-025** | Edición de categoría | Categorías | Media | `PUT /categorias/{id}` actualiza nombre y descripción. Si otro registro (activo o archivado) posee el mismo nombre, rechaza con error. | Prueba Funcional (`CategoriaController::update`) | CP-025 | VERIFICADO |
| **RF-026** | Protección de integridad en borrado de categoría | Categorías | Alta | `DELETE /categorias/{id}` bloquea la eliminación si `categoria->productos()->count() > 0`, retornando error explicativo en sesión sin borrar el registro. | Prueba de Integración (`CategoriaController::destroy`) | CP-026 | VERIFICADO |

---

### 2.5. Módulo de Proveedores

| ID | Requisito | Módulo | Prioridad | Criterio de Aceptación (Verificable) | Método de Verificación | Casos Relacionados | Estado |
| :--- | :--- | :--- | :---: | :--- | :--- | :---: | :---: |
| **RF-027** | Búsqueda y paginación de proveedores | Proveedores | Media | `GET /proveedores` pagina a 15 registros y filtra por parámetro `q` contra `nombre`, `nit`, `email` o `telefono`, incluyendo `withCount('productos')`. | Prueba Funcional (`ProveedorController::index`) | CP-027 | VERIFICADO |
| **RF-028** | Validación de campos de proveedor | Proveedores | Media | `nombre` obligatorio (2-150 caracteres); `nit` opcional regex `^[0-9\-]+$` (max 20); `telefono` opcional regex `^[\+]?[0-9\s\-\(\)]+$` (7-20); `email` opcional válido (max 100); `direccion` opcional (4-150); `activo` booleano (por defecto true). | Prueba Funcional (`ProveedorController::store`) | CP-028 | VERIFICADO |
| **RF-029** | Unicidad y restauración de proveedor | Proveedores | Alta | Si se intenta registrar un proveedor cuyo `nombre` coincide con uno archivado en papelera, se restaura y actualiza en vez de duplicarse o fallar. | Prueba de Integración (`ProveedorController::store`) | CP-029 | VERIFICADO |
| **RF-030** | Edición de proveedor | Proveedores | Media | `PUT /proveedores/{id}` actualiza los datos validando unicidad de nombre contra terceros registros. | Prueba Funcional (`ProveedorController::update`) | CP-030 | VERIFICADO |
| **RF-031** | Protección de integridad en baja de proveedor | Proveedores | Alta | `DELETE /proveedores/{id}` bloquea la baja si `proveedor->productos()->count() > 0`, indicando que debe reasignar o desvincular los productos primero. | Prueba de Integración (`ProveedorController::destroy`) | CP-031 | VERIFICADO |

---

### 2.6. Módulo de Productos e Inventario

| ID | Requisito | Módulo | Prioridad | Criterio de Aceptación (Verificable) | Método de Verificación | Casos Relacionados | Estado |
| :--- | :--- | :--- | :---: | :--- | :--- | :---: | :---: |
| **RF-032** | Paginación del catálogo | Productos | Media | `GET /productos` muestra 15 productos activos por página con sus relaciones `categoria` y `proveedor`, ordenados por nombre. | Prueba Funcional (`ProductoController::index`) | CP-032 | VERIFICADO |
| **RF-033** | Validación estricta al crear producto | Productos | Alta | `nombre` obligatorio (2-100 car.); `precio` numérico entre 0 y 999999.99 (max 2 decimales); `stock` entero entre 0 y 99999; `idCategoria` obligatoria y existente; `idProveedor` opcional y existente; `imagen` opcional (jpg, jpeg, png, webp $\le 2048$ KB). | Prueba Funcional (`ProductoController::store`) | CP-033 | VERIFICADO |
| **RF-034** | Restauración automática desde papelera | Productos | Alta | Si se crea un producto cuyo `nombre` está en papelera, se restaura, se reemplaza la imagen si se envía una nueva y se actualizan los datos. Si está activo, rechaza con error de duplicado. | Prueba de Integración (`ProductoController::store`) | CP-034 | VERIFICADO |
| **RF-035** | Sustitución física de imagen en actualización | Productos | Media | Al actualizar un producto con un nuevo archivo de imagen, el sistema borra el archivo previo tanto de `storage/app/public` como de `public/storage` y guarda la nueva ruta. | Prueba Funcional (`SincronizaImagenPublica`) | CP-035 | VERIFICADO |
| **RF-036** | Baja lógica (Soft Delete) | Productos | Alta | `DELETE /productos/{id}` ejecuta soft delete (`deleted_at != null`). El producto desaparece del catálogo activo y de la lista de selección en pedidos nuevos. | Prueba de Integración (`ProductoController::destroy`) | CP-036 | VERIFICADO |
| **RF-037** | Papelera y restauración de productos | Productos | Alta | `GET /productos/papelera` lista productos en soft delete; `PUT /productos/{id}/restore` reactiva el producto y lo devuelve al catálogo normal. | Prueba Funcional (`ProductoController::restore`) | CP-037 | VERIFICADO |
| **RF-038** | Prohibición de borrado permanente con ventas | Productos | Alta | `DELETE /productos/{id}/force-delete` bloquea el borrado si `producto->detallePedidos()->count() > 0`. Solo se permite si no existen ventas históricas asociadas. | Prueba de Integración (`ProductoController::forceDelete`) | CP-038 | VERIFICADO |
| **RF-039** | Descarga de código QR del producto | Productos | Media | `GET /productos/{id}/qr` retorna un archivo PNG (MIME `image/png`) con un JSON conteniendo SKU formateado, nombre y precio. | Prueba de Integración (`ProductoController::descargarQr`) | CP-039 | VERIFICADO |
| **RF-040** | Descarga de código de barras Code128 | Productos | Media | `GET /productos/{id}/barcode` retorna un archivo PNG con el código de barras Code128 generado a partir del SKU estándar formateado. | Prueba de Integración (`ProductoController::descargarBarcode`) | CP-040 | VERIFICADO |
| **RF-041** | Formato estándar de SKU | Productos | Media | `App\Support\Sku::format(id)` genera de forma determinista la cadena `'PRD-'` seguida del ID numérico rellenado a 6 dígitos con ceros a la izquierda (ej. `PRD-000001`). | Prueba Unitaria (`SkuTest`) | CP-041 | VERIFICADO |
| **RF-042** | Exportación de productos a PDF | Productos | Media | `GET /productos-pdf` descarga un archivo PDF en orientación apaisada (Landscape) con resumen de valuación, totales de stock y agrupación por categoría. | Prueba Funcional (`ProductoController::exportPDF`) | CP-042 | VERIFICADO |
| **RF-043** | Exportación de productos a Excel | Productos | Media | `GET /productos-excel` genera y descarga un libro `.xlsx` mediante `ProductosExport`. | Prueba Funcional (`ProductoController::exportExcel`) | CP-043 | VERIFICADO |

---

### 2.7. Módulo de Clientes

| ID | Requisito | Módulo | Prioridad | Criterio de Aceptación (Verificable) | Método de Verificación | Casos Relacionados | Estado |
| :--- | :--- | :--- | :---: | :--- | :--- | :---: | :---: |
| **RF-044** | Búsqueda y paginación de clientes | Clientes | Media | `GET /clientes` pagina a 15 registros y filtra por parámetro `q` coincidiendo contra `nombre` o `email`. | Prueba Funcional (`ClienteBusquedaTest`) | CP-044 | VERIFICADO |
| **RF-045** | Validación al registrar cliente | Clientes | Media | `nombre` obligatorio (2-100 car.); `email` obligatorio con formato email válido (max 100); `telefono` opcional (7-20); `direccion` opcional (5-150 caracteres). | Prueba Funcional (`ClienteController::store`) | CP-045 | VERIFICADO |
| **RF-046** | Unicidad de email y auto-restauración | Clientes | Alta | Si el `email` ya existe en papelera, se restaura el registro del cliente y se actualizan sus datos; si ya existe activo, rechaza con error de duplicado. | Prueba de Integración (`ClienteController::store`) | CP-046 | VERIFICADO |
| **RF-047** | Edición de cliente | Clientes | Media | `PUT /clientes/{id}` actualiza datos validando unicidad de correo respecto a terceros clientes. | Prueba Funcional (`ClienteController::update`) | CP-047 | VERIFICADO |
| **RF-048** | Protección de integridad en baja de cliente | Clientes | Alta | `DELETE /clientes/{id}` bloquea la baja si `cliente->pedidos()->count() > 0`, indicando la imposibilidad de eliminar clientes con historial. | Prueba de Integración (`ClienteController::destroy`) | CP-048 | VERIFICADO |

---

### 2.8. Módulo de Pedidos y Ventas

| ID | Requisito | Módulo | Prioridad | Criterio de Aceptación (Verificable) | Método de Verificación | Casos Relacionados | Estado |
| :--- | :--- | :--- | :---: | :--- | :--- | :---: | :---: |
| **RF-049** | Listado histórico de pedidos | Pedidos | Media | `GET /pedidos` pagina a 15 pedidos ordenados descendentemente por fecha, con `cliente`, `usuario` registrador y detalles de productos. | Prueba Funcional (`PedidoController::index`) | CP-049 | VERIFICADO |
| **RF-050** | Filtro de catálogo en formulario de venta | Pedidos | Media | `GET /pedidos/create` lista exclusivamente clientes activos y productos que posean stock mayor a cero (`stock > 0`). | Prueba Funcional (`PedidoController::create`) | CP-050 | VERIFICADO |
| **RF-051** | Validación de estructura del pedido | Pedidos | Alta | `StorePedidoRequest` exige: `idCliente` existente; `fecha` obligatoria no futura (`before_or_equal:today`); array `items` de 1 a 100 elementos; `items.*.idProducto` existente y sin duplicados en el array (`distinct`); `items.*.cantidad` entera entre 1 y 9999. | Prueba Funcional (`StorePedidoRequest`) | CP-051 | VERIFICADO |
| **RF-052** | Descuento atómico de stock y cálculo de total | Pedidos | Alta | `POST /pedidos` descuenta el stock de cada producto bajo transacción `lockForUpdate()`, crea `DetallePedido` con copia exacta del precio actual (`precio_unitario`), computa subtotales y total con `bcmath` y asigna `estado = 'completado'`. | Prueba de Integración (`PedidoStockConsistencyTest`) | CP-052 | VERIFICADO |
| **RF-053** | Rollback total ante stock insuficiente | Pedidos | Alta | Si un producto no tiene suficiente stock (`stock < cantidad`), se lanza `ValidationException`, se cancela la transacción completa, no se crea el pedido y ningún producto ve decrementado su stock. | Prueba de Integración (`PedidoStockConsistencyTest`) | CP-053 | VERIFICADO |
| **RF-054** | Edición y transición a Anulado | Pedidos | Alta | Al cambiar el estado de un pedido de `completado` a `anulado` (`PUT /pedidos/{id}`), el stock de los ítems devueltos se restituye al inventario (`increment`) y no se vuelve a descontar. | Prueba de Integración (`PedidoAnulacionStockTest`) | CP-054 | VERIFICADO |
| **RF-055** | Edición y transición a Completado | Pedidos | Alta | Al cambiar un pedido de `anulado` a `completado`, el sistema valida la disponibilidad de inventario actual y descuenta nuevamente el stock. | Prueba de Integración (`PedidoAnulacionStockTest`) | CP-055 | VERIFICADO |
| **RF-056** | Anulación idempotente de stock | Pedidos | Alta | Actualizar un pedido que ya nació o estaba `anulado` hacia `anulado` no incrementa ni decrementa el inventario bajo ningún escenario. | Prueba de Integración (`PedidoAnulacionStockTest`) | CP-056 | VERIFICADO |
| **RF-057** | Anular sin validar stock disponible | Pedidos | Alta | Si el inventario de un producto llegó a 0 por otras ventas posteriores, un pedido previamente completado puede anularse con éxito devolviendo sus unidades sin lanzar error de "stock insuficiente". | Prueba de Integración (`PedidoAnulacionStockTest`) | CP-057 | VERIFICADO |
| **RF-058** | Restitución de stock al eliminar pedido | Pedidos | Alta | Al ejecutar `DELETE /pedidos/{id}` sobre un pedido `completado`, se devuelven todas las cantidades de sus detalles al stock físico y se marca el pedido con soft delete. | Prueba de Integración (`PedidoEliminarAnuladoTest`) | CP-058 | VERIFICADO |
| **RF-059** | Prevención de stock fantasma al eliminar pedido anulado | Pedidos | Alta | Al ejecutar `DELETE /pedidos/{id}` sobre un pedido ya `anulado`, el stock **NO** se incrementa nuevamente, evitando la generación de inventario ficticio. | Prueba de Integración (`PedidoEliminarAnuladoTest`) | CP-059 | VERIFICADO |

---

### 2.9. Módulo de Reportes de Ventas

| ID | Requisito | Módulo | Prioridad | Criterio de Aceptación (Verificable) | Método de Verificación | Casos Relacionados | Estado |
| :--- | :--- | :--- | :---: | :--- | :--- | :---: | :---: |
| **RF-060** | Validación de coherencia en fechas | Reportes | Media | `GET /reportes/ventas` valida que `fecha_fin` sea `after_or_equal:fecha_inicio`. Si la fecha final es anterior a la inicial, rechaza con error de validación. | Prueba Funcional (`ReporteController::ventas`) | CP-060 | VERIFICADO |
| **RF-061** | Filtrado múltiple de ventas | Reportes | Media | Permite filtrar por rango de fechas, cliente específico (`id_cliente`) y estado (`todos`, `completado`, `anulado`). | Prueba Funcional (`ReporteController::ventas`) | CP-061 | VERIFICADO |
| **RF-062** | Cálculo global de métricas contables | Reportes | Alta | `totalVentas`, `totalUnidades` y `promedioVenta` se calculan sobre el conjunto total filtrado excluyendo pedidos anulados, sin verse limitados por las 15 filas de la página visual. | Prueba de Integración (`ReporteVentasMetricasTest`, `PaginacionYDashboardTest`) | CP-062 | VERIFICADO |
| **RF-063** | Manejo de reportes sin registros coincidentes | Reportes | Media | Si un filtro no arroja resultados o la tabla está vacía, el controlador responde con HTTP 200, métricas en 0.0 y vista con mensaje de "No hay ventas registradas...". | Prueba de Integración (`ReporteFiltrosVaciosTest`) | CP-063 | VERIFICADO |
| **RF-064** | Exportación PDF respetando filtros | Reportes | Media | `GET /reportes/ventas/pdf` genera un documento PDF landscape que refleja los mismos filtros aplicados en pantalla. | Prueba Funcional (`ReporteController::exportPDF`) | CP-064 | VERIFICADO |
| **RF-065** | Exportación Excel respetando filtros | Reportes | Media | `GET /reportes/ventas/excel` descarga un archivo `.xlsx` estructurado mediante `VentasExport` conservando los filtros de fecha, cliente y estado. | Prueba Funcional (`ReporteController::exportExcel`) | CP-065 | VERIFICADO |

---

### 2.10. Módulo de Gestión de Usuarios

| ID | Requisito | Módulo | Prioridad | Criterio de Aceptación (Verificable) | Método de Verificación | Casos Relacionados | Estado |
| :--- | :--- | :--- | :---: | :--- | :--- | :---: | :---: |
| **RF-066** | Paginación y visualización de usuarios | Usuarios | Media | `GET /usuarios` lista usuarios paginados a 15 registros con avatar y distinción gráfica de rol (Administrador vs. Invitado). | Prueba Funcional (`UsuarioController::index`) | CP-066 | VERIFICADO |
| **RF-067** | Validación al crear usuario | Usuarios | Alta | `name` obligatorio (2-255); `email` obligatorio único; `password` obligatorio (min 6, max 255, confirmado, regex alfanumérico `^(?=.*[A-Za-z])(?=.*\d).+$`); `rol` en `administrador,invitado`; `foto` imagen opcional ($\le 2048$ KB). | Prueba Funcional (`UsuarioController::store`) | CP-067 | VERIFICADO |
| **RF-068** | Sustitución de foto de perfil | Usuarios | Media | Al actualizar la foto de perfil en `update`, el sistema elimina el archivo anterior del storage público y copia el nuevo archivo. | Prueba Funcional (`UsuarioController::update`) | CP-068 | VERIFICADO |
| **RF-069** | Protección contra auto-degradación de rol | Usuarios | Alta | Si el usuario autenticado intenta modificar su propio rol a `invitado` (`auth()->id() == usuario->id && rol != rol_actual`), la operación se bloquea y retorna mensaje de error. | Prueba de Integración (`UsuarioController::update:L124`) | CP-069 | VERIFICADO |
| **RF-070** | Protección contra auto-eliminación de cuenta | Usuarios | Alta | Si el usuario autenticado intenta eliminar su propio registro (`auth()->id() == usuario->id`), el controlador cancela la acción y retorna error. | Prueba de Integración (`UsuarioController::destroy:L172`) | CP-070 | VERIFICADO |

---

### 2.11. Módulo de Respuestas y Páginas de Error HTTP

| ID | Requisito | Módulo | Prioridad | Criterio de Aceptación (Verificable) | Método de Verificación | Casos Relacionados | Estado |
| :--- | :--- | :--- | :---: | :--- | :--- | :---: | :---: |
| **RF-071** | Renderizado de vistas de error HTTP personalizadas | Errores | Media | Las vistas de error personalizadas para códigos 403, 404, 419, 429, 500 y 503 renderizan correctamente con la identidad visual corporativa sin requerir usuario autenticado. | Prueba de Integración (`PaginasDeErrorTest`) | CP-071 | VERIFICADO |
| **RF-072** | Respuesta estándar ante rutas inexistentes | Errores | Media | Cualquier petición a una URI no declarada en `routes/web.php` devuelve código de estado HTTP 404 Not Found. | Prueba de Integración (`PaginasDeErrorTest`) | CP-072 | VERIFICADO |

---

## 3. Requisitos No Funcionales (RNF)

| ID | Tipo | Requisito No Funcional | Módulo / Componente | Prioridad | Criterio de Aceptación (Verificable) | Método de Verificación | Casos Relacionados | Estado |
| :--- | :--- | :--- | :--- | :---: | :--- | :--- | :---: | :---: |
| **RNF-001** | **Seguridad** | Cifrado robusto de contraseñas | Seguridad / Auth | Alta | Las contraseñas deben almacenarse mediante algoritmo `bcrypt` con costo de trabajo 12 (`BCRYPT_ROUNDS=12` en `.env` y cast `hashed`). Ninguna contraseña en texto plano es persistida. | Inspección de Código y DB (`User.php`, `UsuarioController.php`) | CP-073 | VERIFICADO |
| **RNF-002** | **Seguridad** | Mitigación de ataques de fuerza bruta | Seguridad / Auth | Alta | La tasa de intentos de autenticación está limitada a un máximo de 5 peticiones erróneas antes de imponer un bloqueo temporal de 2 minutos por IP y dirección de correo. | Prueba de Integración (`LoginController::throttleKey`) | CP-074 | VERIFICADO |
| **RNF-003** | **Seguridad** | Integridad y protección de sesión HTTP | Seguridad / Auth | Alta | Regeneración obligatoria del identificador de sesión ante inicio de sesión exitoso y re-emisión de token CSRF (`@csrf`) al cerrar sesión para prevenir fijación de sesión y suplantación de identidad. | Prueba de Integración (`LoginController.php:L47,78`) | CP-075 | VERIFICADO |
| **RNF-004** | **Fiabilidad** | Precisión matemática sin errores de redondeo | Ventas / Pedidos | Alta | Los cálculos de precios, subtotales y totales en pedidos y reportes deben emplear aritmética de precisión arbitraria a 2 decimales mediante funciones `bcmath` (`bcmul`, `bcadd`), evitando el tipo flotante nativo de PHP. | Inspección de Código y Pruebas Unitarias (`PedidoController:L158,172`) | CP-076 | VERIFICADO |
| **RNF-005** | **Concurrencia** | Atomicidad y prevención de condiciones de carrera | Ventas / Inventario | Alta | El descuento de inventario en ventas debe ejecutarse bajo una transacción atómica `DB::transaction` con bloqueo pesimista `lockForUpdate()` en los registros de producto, garantizando aislamiento ACID. | Prueba de Integración (`PedidoStockConsistencyTest`) | CP-077 | VERIFICADO |
| **RNF-006** | **Integridad** | Defensa en profundidad del inventario en motor BD | Base de Datos | Alta | El stock físico nunca debe registrar valores negativos; reforzado en MySQL/MariaDB mediante restricción nativa `CHECK (stock >= 0)` y tipo de columna `INT UNSIGNED` en tablas `productos` y `detalle_pedidos`. | Inspección de Migraciones (`fix_database_consistency`, `unsigned`) | CP-078 | VERIFICADO |
| **RNF-007** | **Rendimiento** | Paginación del lado del servidor | Datos / UI | Media | Todas las tablas de listados principales (productos, categorías, proveedores, clientes, pedidos, usuarios) deben paginar en bloques de 15 filas desde el servidor con `paginate(15)->withQueryString()`, evitando sobrecarga de memoria en el cliente. | Prueba Funcional (`PaginacionYDashboardTest`) | CP-079 | VERIFICADO |
| **RNF-008** | **Trazabilidad** | Eliminación no destructiva de datos de negocio | Base de Datos | Alta | Las entidades críticas (`categorias`, `proveedores`, `productos`, `clientes`, `pedidos`) deben implementar el trait `SoftDeletes` (`deleted_at`), preservando la integridad referencial histórica ante auditorías. | Inspección de Modelos y Migraciones | CP-080 | VERIFICADO |
| **RNF-009** | **Configuración** | Consistencia temporal y huso horario | Sistema / Kernel | Media | La aplicación debe operar estrictamente bajo la zona horaria de Bolivia (`America/La_Paz`, UTC-4) para el registro de ventas, reportes y marcas temporales de auditoría. | Prueba Unitaria (`TimezoneTest`) | CP-081 | VERIFICADO |
| **RNF-010** | **Compatibilidad** | Almacenamiento desacoplado de symlinks | Archivos / Storage | Media | La gestión de archivos multimedia (fotos de productos y usuarios) no debe depender de enlaces simbólicos del sistema operativo (`storage:link`), implementando copia física a `public/storage` para compatibilidad con Windows y hostings compartidos. | Inspección de Código (`SincronizaImagenPublica`) | CP-082 | VERIFICADO |
| **RNF-011** | **Usabilidad** | Prevención de parpadeo de tema (Anti-FOUC) | Frontend / UI | Baja | La aplicación debe evaluar y aplicar la preferencia de modo oscuro (`localStorage.getItem('darkMode')`) mediante un script síncrono previo al renderizado del DOM en el `<head>`, impidiendo el destello blanco visual. | Inspección de Vista (`layouts/app.blade.php:L18-33`) | CP-083 | VERIFICADO |
| **RNF-012** | **Portabilidad** | Compatibilidad dual de motor SQL en desarrollo | Base de Datos | Media | Las consultas agregadas del dashboard deben resolver dinámicamente las funciones de fecha (`DATE_FORMAT` en MySQL vs `strftime` en SQLite) para permitir el ciclo de pruebas local sin requerir un servidor MySQL activo. | Inspección de Código (`routes/web.php:L48-50`) | CP-084 | VERIFICADO |

---

## 4. Requisitos e Ítems que "REQUIEREN VERIFICACIÓN"

Durante el análisis del código fuente se identificaron los siguientes aspectos donde el comportamiento no puede darse por concluido de forma estática o presenta discrepancias entre capas:

1. **Discrepancia en la UI para el Módulo de Reportes (RF-013):**
   - *Hallazgo:* En `resources/views/layouts/app.blade.php` (línea 2693), el enlace a `reportes.ventas` no cuenta con la directiva `@if(auth()->user()->rol === 'administrador')`. Como resultado, los usuarios con rol `invitado` ven el botón de "Reportes" en el menú, pero al hacer clic reciben un error **403 Forbidden**.
   - *Clasificación:* `REQUIERE VERIFICACIÓN` (Requiere confirmación sobre si el enlace debe ocultarse en el menú o si el rol invitado debería tener acceso de solo lectura a ciertos reportes).

2. **reCAPTCHA en el Formulario de Inicio de Sesión (RF-008):**
   - *Hallazgo:* `LoginController.php` (línea 23) valida `'g-recaptcha-response' => 'nullable'`. Sin embargo, en `resources/views/auth/login.blade.php` no existen scripts de Google reCAPTCHA ni claves configuradas en `.env.example`.
   - *Clasificación:* `REQUIERE VERIFICACIÓN` (Requiere confirmación si reCAPTCHA es una funcionalidad a implementar a futuro o código residual de una plantilla).

3. **Verificación de Bloqueo Concurrente Real en MySQL (RNF-005):**
   - *Hallazgo:* Aunque el código implementa `lockForUpdate()`, las pruebas automatizadas con PHPUnit se ejecutan en SQLite en memoria (`:memory:`), donde el bloqueo pesimista por fila opera de forma diferente a los motores InnoDB de MySQL.
   - *Clasificación:* `REQUIERE VERIFICACIÓN` (Requiere ejecución de pruebas concurrentes multiproceso sobre una instancia de MySQL real).

4. **Vistas Heredadas Huérfanas de Scaffold:**
   - *Hallazgo:* Los archivos `resources/views/home.blade.php`, `resources/views/welcome.blade.php` y el controlador `app/Http/Controllers/HomeController.php` existen en el árbol de archivos pero no están referenciados por ninguna ruta en `routes/web.php`.
   - *Clasificación:* `REQUIERE VERIFICACIÓN` (Se clasifica como código inactivo fuera del alcance de pruebas funcionales).

---

## 5. Resumen Cuantitativo de Requisitos Identificados

| Módulo / Dimensión | Requisitos Funcionales (RF) | Requisitos No Funcionales (RNF) | Total Requisitos | Alta Prioridad | Media Prioridad | Baja Prioridad |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| **Autenticación y Sesión** | 8 | 3 | 11 | 4 | 5 | 2 |
| **Seguridad y Control de Acceso (RBAC)** | 5 | 0 | 5 | 4 | 1 | 0 |
| **Dashboard (Panel de Control)** | 8 | 1 | 9 | 2 | 7 | 0 |
| **Categorías** | 5 | 0 | 5 | 2 | 3 | 0 |
| **Proveedores** | 5 | 0 | 5 | 2 | 3 | 0 |
| **Productos e Inventario** | 12 | 1 | 13 | 4 | 9 | 0 |
| **Clientes** | 5 | 0 | 5 | 2 | 3 | 0 |
| **Pedidos y Ventas** | 11 | 3 | 14 | 9 | 5 | 0 |
| **Reportes de Ventas** | 6 | 0 | 6 | 1 | 5 | 0 |
| **Gestión de Usuarios** | 5 | 1 | 6 | 3 | 3 | 0 |
| **Páginas de Error / Arquitectura** | 2 | 3 | 5 | 0 | 4 | 1 |
| **TOTALES CONSOLIDADOS** | **72** | **12** | **84** | **33** | **48** | **3** |

---

## 6. Estado y Trazabilidad

- **Base de Requisitos:** `VERIFICADO` (72 RF y 12 RNF derivados con trazabilidad a archivos y líneas de código).
- **Plan de Pruebas Asociado:** `PLANIFICADO` (Casos `CP-001` a `CP-084` proyectados para cobertura completa).
- **Ejecución de Pruebas:** `PENDIENTE` (Las pruebas no han sido ejecutadas ni evaluadas con veredictos en este documento).
