# 10 — Matriz de Trazabilidad de Requisitos y Pruebas (RTM) (SISMICRO / Supermarket)

> **Matriz de Trazabilidad de Requisitos (Requirements Traceability Matrix - RTM)**  
> **Proyecto:** Sistema Web de Ventas e Inventario para el Micromercado SuperMarket  
> **Ámbito:** Proyecto de Grado / Titulación Académica  
> **Fecha de Emisión:** Septiembre 2026  
> **Versión:** 1.0  
> **Fuentes de Verdad Exclusivas:** `02_Requisitos_Verificables.md` (84 requisitos) y `04_Casos_Prueba.md` (100 casos de prueba)  
> **Estado del Análisis:** VERIFICADO (mapeo estricto y exhaustivo de correspondencia bidireccional entre requisitos y casos)  
> **Estado de las Pruebas:** PENDIENTE DE EJECUCIÓN (ningún caso ha sido ejecutado ni marcado como PASS/FAIL)  

---

## 1. Convenciones y Reglas de Trazabilidad

1. **Estructura de la Matriz:**
   - **Requisito:** Código unívoco del requisito funcional (`RF-001` a `RF-072`) o no funcional (`RNF-001` a `RNF-012`) según `02_Requisitos_Verificables.md`.
   - **Descripción:** Síntesis técnica del comportamiento o restricción exigida.
   - **Módulo:** Subsistema al que pertenece la especificación.
   - **Caso de prueba:** Identificador del caso de prueba asignado (`CP-001` a `CP-100`) en `04_Casos_Prueba.md`. Si un requisito no cuenta con caso en dicho documento, se declara explícitamente como `Sin caso en 04_Casos_Prueba.md`.
   - **Tipo:** Tipología formal de la prueba (Positivo, Negativo, Obligatorio, Inválido, Límite, Integridad, Seguridad, etc.).
   - **Prioridad:** Nivel de criticidad (`Alta`, `Media`, `Baja`) definido en las especificaciones base.
   - **Estado:**
     - `PENDIENTE`: El caso de prueba está formalmente diseñado en `04_Casos_Prueba.md` pero no ha sido ejecutado todavía.
     - `REQUIERE VERIFICACIÓN`: El requisito carece de caso de prueba en `04_Casos_Prueba.md` o presenta discrepancias técnicas documentadas en `02_Requisitos_Verificables.md`.

2. **Principio de No Invención:**
   No se inventan asociaciones artificiales. La matriz refleja exclusivamente las relaciones demostrables entre `02_Requisitos_Verificables.md` y `04_Casos_Prueba.md`.

---

## 2. Matriz de Trazabilidad: Requisitos Funcionales (`RF-001` a `RF-072`)

| Requisito | Descripción | Módulo | Caso de prueba | Tipo | Prioridad | Estado |
| :---: | :--- | :--- | :---: | :---: | :---: | :---: |
| **RF-001** | Inicio de sesión con credenciales válidas (Administrador) | Autenticación | CP-001 | Positivo | Alta | PENDIENTE |
| **RF-001** | Inicio de sesión con credenciales válidas (Invitado) | Autenticación | CP-002 | Positivo | Alta | PENDIENTE |
| **RF-002** | Rechazo de login por contraseña incorrecta | Autenticación | CP-003 | Negativo | Alta | PENDIENTE |
| **RF-002** | Rechazo de login por correo inexistente | Autenticación | CP-004 | Negativo | Alta | PENDIENTE |
| **RF-003** | Validación de campos obligatorios vacíos en login | Autenticación | CP-005 | Obligatorio | Media | PENDIENTE |
| **RF-003** | Validación de formato sintáctico inválido en email | Autenticación | CP-006 | Inválido | Media | PENDIENTE |
| **RF-004** | Bloqueo temporal por fuerza bruta tras 5 intentos fallidos | Autenticación | CP-007 | Límite / Seguridad | Alta | PENDIENTE |
| **RF-005** | Persistencia de sesión con casilla "Recordarme" | Autenticación | CP-008 | Positivo | Media | PENDIENTE |
| **RF-006** | Cierre de sesión seguro con invalidación y token CSRF | Autenticación | CP-009 | Seguridad | Alta | PENDIENTE |
| **RF-007** | Redirección de ruta raíz (`/`) según autenticación | Autenticación | CP-010 | Navegación | Media | PENDIENTE |
| **RF-008** | Campo reCAPTCHA nullable en formulario de login | Autenticación | *Sin caso en 04_Casos_Prueba.md* | N/A | Baja | REQUIERE VERIFICACIÓN |
| **RF-009** | Protección de rutas ante accesos no autenticados | Seguridad / RBAC | CP-011 | Seguridad | Alta | PENDIENTE |
| **RF-010** | Restricción de acceso a creación de productos para Invitado | Seguridad / RBAC | CP-012 | Permisos / Negativo | Alta | PENDIENTE |
| **RF-010** | Restricción de eliminación de categorías para Invitado | Seguridad / RBAC | CP-013 | Permisos / Negativo | Alta | PENDIENTE |
| **RF-011** | Invitado accede a crear pedido y registra venta en caja | Seguridad / RBAC | CP-014 | Permisos / Positivo | Alta | PENDIENTE |
| **RF-012** | Invitado intenta editar un pedido registrado previamente | Seguridad / RBAC | CP-015 | Permisos / Negativo | Alta | PENDIENTE |
| **RF-012** | Invitado intenta eliminar un pedido del historial | Seguridad / RBAC | CP-016 | Permisos / Negativo | Alta | PENDIENTE |
| **RF-012** | Invitado intenta acceder a pantalla de Reportes de Ventas | Seguridad / RBAC | CP-017 | Permisos / Negativo | Alta | PENDIENTE |
| **RF-012** | Invitado intenta acceder al módulo de Gestión de Usuarios | Seguridad / RBAC | CP-018 | Permisos / Negativo | Alta | PENDIENTE |
| **RF-013** | Visibilidad de enlace a Reportes en menú lateral vs 403 | Seguridad / UI | *Sin caso en 04_Casos_Prueba.md* | N/A | Media | REQUIERE VERIFICACIÓN |
| **RF-014** | Despliegue de contadores cuantitativos globales en panel | Dashboard | CP-019 | Positivo | Media | PENDIENTE |
| **RF-015** | Discriminación de pedidos completados vs anulados | Dashboard | *Sin caso en 04_Casos_Prueba.md* | N/A | Media | REQUIERE VERIFICACIÓN |
| **RF-016** | Sumatoria de ventas netas excluyendo pedidos anulados | Dashboard | CP-020 | Exactitud / Negocio | Alta | PENDIENTE |
| **RF-017** | Valuación total del inventario activo (`stock * precio`) | Dashboard | CP-021 | Exactitud / Negocio | Alta | PENDIENTE |
| **RF-018** | Alerta cuantitativa de productos con stock bajo ($\le 5$) | Dashboard | CP-022 | Límite | Media | PENDIENTE |
| **RF-019** | Series temporales adaptativas por motor (`DATE_FORMAT`/`strftime`) | Dashboard | CP-023 | Portabilidad | Media | PENDIENTE |
| **RF-020** | Cálculo de Top 5 productos con mayores ingresos | Dashboard | CP-024 | Lógica / Negocio | Media | PENDIENTE |
| **RF-021** | Cálculo de Top 5 clientes con mayor volumen de compra | Dashboard | CP-025 | Lógica / Negocio | Media | PENDIENTE |
| **RF-022** | Paginación y ordenamiento del catálogo de categorías | Categorías | CP-026 | Navegación | Media | PENDIENTE |
| **RF-023** | Registro exitoso de categoría con datos válidos | Categorías | CP-027 | Positivo | Media | PENDIENTE |
| **RF-023** | Validación de campo nombre obligatorio vacío en categoría | Categorías | CP-028 | Obligatorio | Media | PENDIENTE |
| **RF-023** | Validación de caracteres especiales no permitidos en nombre | Categorías | CP-029 | Inválido / Regex | Media | PENDIENTE |
| **RF-024** | Rechazo de duplicado con categoría activa existente | Categorías | CP-030 | Duplicado / Negativo | Alta | PENDIENTE |
| **RF-024** | Auto-restauración de categoría archivada en papelera | Categorías | CP-031 | Papelera / Positivo | Alta | PENDIENTE |
| **RF-025** | Edición exitosa de nombre y descripción de categoría | Categorías | CP-032 | Positivo | Media | PENDIENTE |
| **RF-026** | Bloqueo de eliminación de categoría con productos vinculados | Categorías | CP-033 | Integridad / Bloqueo | Alta | PENDIENTE |
| **RF-026** | Eliminación lógica (Soft Delete) de categoría huérfana | Categorías | CP-034 | Soft Delete | Media | PENDIENTE |
| **RF-027** | Búsqueda dinámica y paginación de proveedores por `q` | Proveedores | CP-035 | Búsqueda / Filtro | Media | PENDIENTE |
| **RF-028** | Registro de proveedor con todos los campos válidos | Proveedores | CP-036 | Positivo | Media | PENDIENTE |
| **RF-028** | Registro de proveedor omitiendo campos opcionales | Proveedores | CP-037 | Límite Mínimo | Media | PENDIENTE |
| **RF-028** | Validación de formato de NIT con letras no permitidas | Proveedores | CP-038 | Inválido / Regex | Media | PENDIENTE |
| **RF-028** | Validación de longitud mínima de teléfono ($< 7$ dígitos) | Proveedores | CP-039 | Límite / Regex | Media | PENDIENTE |
| **RF-029** | Auto-restauración de proveedor archivado en papelera | Proveedores | CP-040 | Papelera / Positivo | Alta | PENDIENTE |
| **RF-030** | Edición de proveedor y cambio de estado activo/inactivo | Proveedores | CP-041 | Positivo | Media | PENDIENTE |
| **RF-031** | Bloqueo de eliminación de proveedor con productos vinculados | Proveedores | CP-042 | Integridad / Bloqueo | Alta | PENDIENTE |
| **RF-031** | Eliminación lógica de proveedor huérfano sin productos | Proveedores | CP-043 | Soft Delete | Media | PENDIENTE |
| **RF-032** | Paginación de catálogo de productos a 15 filas con relaciones | Productos | CP-044 | Navegación | Media | PENDIENTE |
| **RF-033** | Registro exitoso de producto con datos válidos e imagen | Productos | CP-045 | Positivo | Alta | PENDIENTE |
| **RF-033** | Rechazo de registro de producto con precio negativo | Productos | CP-046 | Límite / Negativo | Alta | PENDIENTE |
| **RF-033** | Rechazo de registro de producto con stock negativo | Productos | CP-047 | Límite / Negativo | Alta | PENDIENTE |
| **RF-033** | Validación de precio con más de 2 decimales | Productos | CP-048 | Inválido / Regex | Media | PENDIENTE |
| **RF-033** | Rechazo de producto vinculado a categoría inexistente | Productos | CP-049 | Integridad | Alta | PENDIENTE |
| **RF-033** | Validación de formato no soportado en carga de imagen | Productos | CP-050 | Inválido / Formato | Media | PENDIENTE |
| **RF-033** | Rechazo de archivo de imagen que excede 2048 KB | Productos | CP-051 | Límite Superior | Media | PENDIENTE |
| **RF-034** | Rechazo de registro con nombre de producto activo existente | Productos | CP-052 | Duplicado / Negativo | Alta | PENDIENTE |
| **RF-034** | Auto-restauración de producto en papelera al re-registrar | Productos | CP-053 | Papelera / Positivo | Alta | PENDIENTE |
| **RF-035** | Sustitución física y purga de imagen anterior en disco | Productos | CP-054 | Almacenamiento | Media | PENDIENTE |
| **RF-036** | Baja lógica de producto hacia la papelera de reciclaje | Productos | CP-055 | Soft Delete | Alta | PENDIENTE |
| **RF-037** | Restauración manual de producto desde vista de papelera | Productos | CP-056 | Papelera / Positivo | Alta | PENDIENTE |
| **RF-038** | Bloqueo de eliminación definitiva de producto con ventas | Productos | CP-057 | Integridad / Bloqueo | Alta | PENDIENTE |
| **RF-038** | Eliminación permanente física de producto sin ventas | Productos | CP-058 | Borrado Físico | Alta | PENDIENTE |
| **RF-039** | Generación y descarga de código QR en formato PNG | Productos | CP-059 | Descarga / Binario | Media | PENDIENTE |
| **RF-040** | Generación y descarga de código de barras Code128 en PNG | Productos | CP-060 | Descarga / Binario | Media | PENDIENTE |
| **RF-041** | Formato determinista de SKU estándar (`PRD-XXXXXX`) | Productos | CP-061 | Unitario / Helper | Media | PENDIENTE |
| **RF-042** | Exportación de catálogo completo de productos a PDF A4 | Productos | CP-062 | Documental | Media | PENDIENTE |
| **RF-043** | Exportación de catálogo de productos a libro Excel XLSX | Productos | CP-063 | Documental | Media | PENDIENTE |
| **RF-044** | Búsqueda dinámica de clientes por nombre o correo con `q` | Clientes | CP-064 | Búsqueda / Filtro | Media | PENDIENTE |
| **RF-045** | Registro exitoso de cliente con datos válidos | Clientes | CP-065 | Positivo | Media | PENDIENTE |
| **RF-045** | Validación de campos obligatorios nombre y correo vacíos | Clientes | CP-066 | Obligatorio | Media | PENDIENTE |
| **RF-045** | Validación de formato sintáctico de correo de cliente | Clientes | CP-067 | Inválido | Media | PENDIENTE |
| **RF-046** | Rechazo de cliente con correo ya activo en base de datos | Clientes | CP-068 | Duplicado / Negativo | Alta | PENDIENTE |
| **RF-046** | Auto-restauración de cliente en papelera al re-registrar email | Clientes | CP-069 | Papelera / Positivo | Alta | PENDIENTE |
| **RF-047** | Edición de cliente validando colisión de correo | Clientes | CP-070 | Positivo | Media | PENDIENTE |
| **RF-048** | Bloqueo de eliminación de cliente con pedidos asociados | Clientes | CP-071 | Integridad / Bloqueo | Alta | PENDIENTE |
| **RF-048** | Eliminación lógica de cliente sin historial de pedidos | Clientes | CP-072 | Soft Delete | Media | PENDIENTE |
| **RF-049** | Listado paginado de pedidos con cliente y cajero | Pedidos / Ventas | CP-073 | Navegación | Media | PENDIENTE |
| **RF-050** | Selector de productos en venta muestra solo stock $> 0$ | Pedidos / Ventas | CP-074 | Lógica / Catálogo | Media | PENDIENTE |
| **RF-051** | Rechazo de venta con fecha futura (`before_or_equal:today`) | Pedidos / Ventas | CP-075 | Inválido / Fecha | Alta | PENDIENTE |
| **RF-051** | Rechazo de pedido sin líneas de productos (`items` vacío) | Pedidos / Ventas | CP-076 | Obligatorio | Alta | PENDIENTE |
| **RF-051** | Rechazo de productos repetidos en líneas separadas | Pedidos / Ventas | CP-077 | Duplicado en Array | Alta | PENDIENTE |
| **RF-051** | Validación de cantidad mínima ($< 1$) o excesiva ($> 9999$) | Pedidos / Ventas | CP-078 | Límite / Cantidad | Media | PENDIENTE |
| **RF-052** | Venta simple con descuento atómico de stock | Pedidos / Ventas | CP-079 | Positivo / ACID | Alta | PENDIENTE |
| **RF-052** | Venta multi-línea con cómputo de subtotales y total | Pedidos / Ventas | CP-080 | Positivo / ACID | Alta | PENDIENTE |
| **RF-052** | Venta en el límite exacto de existencias ($stock = cantidad$) | Pedidos / Ventas | CP-081 | Límite Exacto | Alta | PENDIENTE |
| **RF-052** | Preservación de snapshot de precio y cálculo con bcmath | Pedidos / Ventas | CP-084 | Exactitud / bcmath | Alta | PENDIENTE |
| **RF-053** | Rechazo de venta por insuficiencia de stock por 1 unidad | Pedidos / Ventas | CP-082 | Límite / Insuficiente | Alta | PENDIENTE |
| **RF-053** | Rollback total en venta multi-línea ante falla de un ítem | Pedidos / Ventas | CP-083 | Transaccional / ACID | Alta | PENDIENTE |
| **RF-054** | Anulación de pedido y restitución exacta de stock | Pedidos / Ventas | CP-085 | Transición / Estado | Alta | PENDIENTE |
| **RF-055** | Reactivación de pedido anulado hacia completado | Pedidos / Ventas | CP-086 | Transición / Estado | Alta | PENDIENTE |
| **RF-056** | Modificación idempotente de anulado hacia anulado sin tocar stock | Pedidos / Ventas | CP-087 | Idempotencia | Alta | PENDIENTE |
| **RF-057** | Anular pedido completado cuando el stock general está en 0 | Pedidos / Ventas | CP-088 | Resiliencia | Alta | PENDIENTE |
| **RF-058** | Eliminación de pedido completado restituye el stock físico | Pedidos / Ventas | CP-089 | Persistencia | Alta | PENDIENTE |
| **RF-059** | Eliminación de pedido anulado NO genera stock fantasma | Pedidos / Ventas | CP-090 | Regresión / Stock | Alta | PENDIENTE |
| **RF-060** | Rechazo de filtro con fecha fin anterior a fecha inicio | Reportes | CP-091 | Coherencia / Fechas | Media | PENDIENTE |
| **RF-061** | Filtrado multicriterio de ventas (fechas, cliente, estado) | Reportes | CP-092 | Filtro Combinado | Media | PENDIENTE |
| **RF-062** | Cálculo de métricas globales sobre el total filtrado | Reportes | CP-093 | Exactitud Contable | Alta | PENDIENTE |
| **RF-063** | Respuesta amigable ante reportes sin coincidencias (HTTP 200) | Reportes | CP-094 | Límite / Vacío | Media | PENDIENTE |
| **RF-064** | Exportación de reporte a PDF aplicando filtros activos | Reportes | CP-095 | Documental | Media | PENDIENTE |
| **RF-065** | Exportación de reporte a Excel aplicando filtros activos | Reportes | CP-096 | Documental | Media | PENDIENTE |
| **RF-066** | Paginación y listado de usuarios con avatar y rol | Usuarios | *Sin caso en 04_Casos_Prueba.md* | N/A | Media | REQUIERE VERIFICACIÓN |
| **RF-067** | Registro exitoso de usuario con rol y datos válidos | Usuarios | CP-097 | Positivo | Alta | PENDIENTE |
| **RF-067** | Rechazo de clave con menos de 6 caracteres o no alfanumérica | Usuarios | CP-098 | Inválido / Password | Alta | PENDIENTE |
| **RF-068** | Sustitución física de foto de perfil en actualización | Usuarios | *Sin caso en 04_Casos_Prueba.md* | N/A | Media | REQUIERE VERIFICACIÓN |
| **RF-069** | Protección contra auto-degradación de rol del Administrador | Usuarios | CP-099 | Seguridad / Bloqueo | Alta | PENDIENTE |
| **RF-070** | Protección contra auto-eliminación de cuenta del Administrador | Usuarios | CP-100 | Seguridad / Bloqueo | Alta | PENDIENTE |
| **RF-071** | Vistas de error personalizadas (403, 404, 419, 429, 500, 503) | Errores / UI | *Sin caso en 04_Casos_Prueba.md* | N/A | Media | REQUIERE VERIFICACIÓN |
| **RF-072** | Respuesta estándar HTTP 404 ante rutas inexistentes | Errores / Kernel | *Sin caso en 04_Casos_Prueba.md* | N/A | Media | REQUIERE VERIFICACIÓN |

---

## 3. Matriz de Trazabilidad: Requisitos No Funcionales (`RNF-001` a `RNF-012`)

A continuación se detalla la trazabilidad de los requisitos no funcionales frente a las verificaciones especializadas definidas en las fichas técnicas de `04_Casos_Prueba.md`:

| Requisito | Descripción | Módulo | Caso de prueba | Tipo | Prioridad | Estado |
| :---: | :--- | :--- | :---: | :---: | :---: | :---: |
| **RNF-001** | Cifrado robusto de contraseñas con bcrypt (costo 12) | Seguridad / Auth | CP-001, CP-002, CP-097 | Seguridad / Criptografía | Alta | PENDIENTE |
| **RNF-002** | Mitigación de ataques de fuerza bruta (Rate Limiter) | Seguridad / Auth | CP-007 | Límite / Seguridad | Alta | PENDIENTE |
| **RNF-003** | Protección contra Session Fixation y CSRF en sesiones | Seguridad / Auth | CP-009 | Seguridad / Sesión | Alta | PENDIENTE |
| **RNF-004** | Aritmética decimal de precisión arbitraria (`bcmath`) | Ventas / Pedidos | CP-084 | Exactitud / bcmath | Alta | PENDIENTE |
| **RNF-005** | Atomicidad y bloqueo pesimista (`lockForUpdate`) | Ventas / Inventario | CP-079, CP-080, CP-083 | Concurrencia / ACID | Alta | PENDIENTE |
| **RNF-006** | Restricción física de stock no negativo en motor BD | Base de Datos | CP-047, CP-081 | Integridad de Motor | Alta | PENDIENTE |
| **RNF-007** | Paginación del lado del servidor (15 registros por página) | Datos / UI | CP-026, CP-044, CP-073 | Rendimiento / UI | Media | PENDIENTE |
| **RNF-008** | Preservación histórica mediante SoftDeletes (`deleted_at`) | Base de Datos | CP-034, CP-043, CP-055, CP-072, CP-089 | Trazabilidad / Auditoría | Alta | PENDIENTE |
| **RNF-009** | Consistencia de zona horaria oficial (`America/La_Paz`) | Sistema / Kernel | *Sin caso en 04_Casos_Prueba.md* | N/A | Media | REQUIERE VERIFICACIÓN |
| **RNF-010** | Almacenamiento multimedia desacoplado de symlinks | Archivos / Storage | CP-054, CP-097 | Compatibilidad / SO | Media | PENDIENTE |
| **RNF-011** | Prevención de parpadeo de modo oscuro (Anti-FOUC) | Frontend / UI | *Sin caso en 04_Casos_Prueba.md* | N/A | Baja | REQUIERE VERIFICACIÓN |
| **RNF-012** | Compatibilidad multi-motor SQL (`DATE_FORMAT`/`strftime`)| Base de Datos | CP-023 | Portabilidad | Media | PENDIENTE |

---

## 4. Análisis de Cobertura, Casos Huérfanos y Brechas Identificadas

### 4.1. Requisitos sin Pruebas en `04_Casos_Prueba.md` (Brechas Detectadas)
Al realizar el cotejo estricto contra `04_Casos_Prueba.md`, se identifican **7 Requisitos Funcionales** y **2 Requisitos No Funcionales** que carecen de caso asignado en la suite de 100 pruebas:

1. **`RF-008` (Campo reCAPTCHA nullable en Login):** No tiene caso en `04_Casos_Prueba.md`. En `02_Requisitos_Verificables.md` ya estaba catalogado como `REQUIERE VERIFICACIÓN` al no poseer scripts ni claves en la vista `login.blade.php`.
2. **`RF-013` (Visibilidad del menú lateral vs 403 en Reportes):** No tiene caso en `04_Casos_Prueba.md`. Discrepancia UI documentada en `02_Requisitos_Verificables.md` (el botón de menú se muestra a invitados, pero la ruta backend da 403).
3. **`RF-015` (Desglose de estado de pedidos en Dashboard):** `04_Casos_Prueba.md` salta de CP-019 (`RF-014`) a CP-020 (`RF-016`), omitiendo una prueba directa para la discriminación cuantitativa entre pedidos completados y anulados.
4. **`RF-066` (Paginación y visualización de usuarios):** El módulo de usuarios en `04_Casos_Prueba.md` solo abarca del CP-097 al CP-100, omitiendo la prueba de listado paginado `GET /usuarios`.
5. **`RF-068` (Sustitución física de foto de perfil):** La prueba de reemplazo de imagen solo fue formulada para productos (CP-054), omitiendo el flujo análogo en `UsuarioController::update`.
6. **`RF-071` (Vistas de error HTTP personalizadas 403, 404, 419, 429, 500, 503):** Omitido en `04_Casos_Prueba.md` (cubierto en el archivo de código `PaginasDeErrorTest.php`, pero sin ficha asignada en el documento 04).
7. **`RF-072` (Respuesta estándar HTTP 404 ante rutas inexistentes):** Omitido formalmente en `04_Casos_Prueba.md`.
8. **`RNF-009` (Consistencia de zona horaria `America/La_Paz`):** Posee prueba en código (`TimezoneTest.php`), pero no fue incluida en las fichas de `04_Casos_Prueba.md`.
9. **`RNF-011` (Prevención de parpadeo de modo oscuro Anti-FOUC):** Regla de interfaz que no fue formalizada como caso en `04_Casos_Prueba.md`.

### 4.2. Casos de Prueba sin Requisito (Casos Huérfanos)
- **Total de casos evaluados:** 100 casos (`CP-001` a `CP-100`).
- **Casos sin requisito identificado:** **0 casos (0%)**.
- **Evaluación:** La totalidad de los 100 casos especificados en `04_Casos_Prueba.md` declaran explícitamente su requisito funcional padre (y en las fichas detalladas su requisito no funcional complementario), garantizando una justificación de negocio para cada prueba formulada.

### 4.3. Requisitos con Cobertura Múltiple
Varios requisitos de alta criticidad cuentan con múltiples casos de prueba para cubrir escenarios positivos, negativos, límites y de concurrencia:
- **`RF-033` (Validación de productos):** 7 casos de prueba asociados (`CP-045` a `CP-051`).
- **`RF-051` (Validación de pedidos):** 4 casos de prueba asociados (`CP-075` a `CP-078`).
- **`RF-052` (Descuento atómico de stock y total):** 4 casos de prueba asociados (`CP-079`, `CP-080`, `CP-081`, `CP-084`).
- **`RF-028` (Validación de proveedores):** 4 casos de prueba asociados (`CP-036` a `CP-039`).
- **`RF-012` (Restricciones a rol invitado):** 4 casos de prueba asociados (`CP-015` a `CP-018`).

---

## 5. Resumen Consolidado de Cobertura

En apego estricto a las directivas de auditoría, las cifras reflejan el estado de **diseño y planificación** de pruebas sin presentar resultados de ejecución ficticios:

| Dimensión de Auditoría | Requisitos Identificados en `02` | Requisitos con Casos en `04` | Requisitos sin Casos (`REQUIERE VERIFICACIÓN`) | Porcentaje de Cobertura de Requisitos |
| :--- | :---: | :---: | :---: | :---: |
| **Requisitos Funcionales (RF)** | 72 | 65 | 7 | **90.28 %** |
| **Requisitos No Funcionales (RNF)** | 12 | 10 | 2 | **83.33 %** |
| **TOTAL GENERAL DE REQUISITOS** | **84** | **75** | **9** | **89.29 %** |

### Estado Formal de Ejecución de la Suite
- **Total Casos Especificados en `04_Casos_Prueba.md`:** 100 casos (`CP-001` a `CP-100`).
- **Casos en Estado PENDIENTE:** 100 casos (**100.0 %**).
- **Casos en Estado PASS:** 0 casos (**0.0 %** — Ninguna prueba ha sido calificada sin ejecución real en esta fase).
- **Casos en Estado FAIL:** 0 casos (**0.0 %**).
- **Casos Huérfanos (sin requisito):** 0 casos (**0.0 %**).

---

## 6. Recomendaciones Metodológicas para Completar la Cobertura

Para alcanzar una cobertura del 100% en las fases posteriores del proyecto, se recomienda:
1. **Incorporar Casos `CP-101` a `CP-107`:** Expandir formalmente `04_Casos_Prueba.md` con las especificaciones de prueba para los 7 requisitos funcionales faltantes (`RF-008`, `RF-013`, `RF-015`, `RF-066`, `RF-068`, `RF-071`, `RF-072`).
2. **Formalizar Casos `CP-108` y `CP-109`:** Añadir las pruebas unitarias y de front para los dos requisitos no funcionales pendientes (`RNF-009` sobre huso horario y `RNF-011` sobre script Anti-FOUC).
3. **Resolver Discrepancia RF-013 en Blade:** Decidir si en `resources/views/layouts/app.blade.php` se envuelve el enlace a `reportes.ventas` con `@if(auth()->user()->rol === 'administrador')` para evitar el error 403 a usuarios con rol invitado.
