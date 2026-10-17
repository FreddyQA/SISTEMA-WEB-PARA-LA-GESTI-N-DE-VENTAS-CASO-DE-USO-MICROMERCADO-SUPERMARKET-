# 04 — Especificación Profesional de Casos de Prueba (SISMICRO / Supermarket)

> **Documento de Casos de Prueba de Software (Test Case Specification)**  
> **Proyecto:** Sistema Web de Ventas e Inventario para el Micromercado SuperMarket  
> **Ámbito:** Proyecto de Grado / Titulación Académica  
> **Fecha de Emisión:** Septiembre 2026  
> **Versión:** 1.0  
> **Fuente de Verdad:** `01_Analisis_Sistema.md`, `02_Requisitos_Verificables.md` y `03_Plan_Pruebas.md`  
> **Estado de los Casos:** PENDIENTE (especificados y listos para ejecución; sin ejecuciones ni resultados inventados)  

---

## 1. Convenciones y Reglas de la Especificación

1. **Estructura Estándar de cada Caso de Prueba:**
   - **ID:** Identificador correlativo único (`CP-001`, `CP-002`, ...).
   - **Requisito Asociado:** Código del requisito funcional (`RF-001` a `RF-072`) o no funcional (`RNF-001` a `RNF-012`).
   - **Módulo:** Subsistema o módulo al que pertenece.
   - **Nombre:** Denominación clara y representativa del escenario.
   - **Objetivo:** Propósito de la verificación (qué se intenta demostrar).
   - **Prioridad:** `Alta`, `Media` o `Baja`.
   - **Precondiciones:** Estado inicial del sistema y datos preexistentes requeridos.
   - **Datos de Prueba:** Entradas exactas, parámetros y valores utilizados.
   - **Pasos de Ejecución:** Secuencia numerada de acciones a ejecutar.
   - **Resultado Esperado:** Comportamiento estipulado por la regla de negocio y código fuente.
   - **Resultado Obtenido:** *(Vacío — conforme a la regla de no ejecutar ni inventar resultados antes de la fase de pruebas).*
   - **Estado:** `PENDIENTE` *(para todos los casos de prueba en esta fase).*
   - **Evidencia:** Registro documental proyectado (salida PHPUnit, log, captura o documento descargado).
   - **Observaciones:** Notas de diseño, particularidades de motor BD o dependencias técnicas.

2. **Tipologías de Prueba Cubiertas:**
   - Pruebas Positivas (caminos felices / happy path).
   - Pruebas Negativas (datos inválidos, rechazos de negocio).
   - Pruebas de Límites (BVA: stock exacto, stock insuficiente por 1 unidad, mínimos y máximos).
   - Pruebas de Campos Obligatorios (omisión de campos requeridos).
   - Pruebas de Formato e Invalidez (expresiones regulares, emails mal formados, tipos de archivo).
   - Pruebas de Duplicidad y Papelera (colisiones de unicidad y auto-restauración desde soft-delete).
   - Pruebas de Operaciones Incorrectas e Integridad Referencial (bloqueo de borrados en cascada destructivos).
   - Pruebas de Roles y Permisos (RBAC: Administrador vs. Invitado).
   - Pruebas de Concurrencia y Transaccionalidad (ACID, `lockForUpdate`, reversión completa).
   - Pruebas de Navegación, Interfaz y Páginas de Error HTTP.

---

## 2. Matriz General de Casos de Prueba (CP-001 a CP-100)

| ID | Módulo | Requisito | Tipo de Caso | Nombre del Caso de Prueba | Prioridad | Estado |
| :--- | :--- | :---: | :---: | :--- | :---: | :---: |
| **CP-001** | Autenticación | RF-001 | Positivo | Login exitoso con credenciales de Administrador | Alta | PENDIENTE |
| **CP-002** | Autenticación | RF-001 | Positivo | Login exitoso con credenciales de Invitado | Alta | PENDIENTE |
| **CP-003** | Autenticación | RF-002 | Negativo | Login fallido por contraseña incorrecta | Alta | PENDIENTE |
| **CP-004** | Autenticación | RF-002 | Negativo | Login fallido por correo inexistente | Alta | PENDIENTE |
| **CP-005** | Autenticación | RF-003 | Obligatorio | Validación de campos obligatorios vacíos en login | Media | PENDIENTE |
| **CP-006** | Autenticación | RF-003 | Inválido | Validación de formato inválido en campo email de login | Media | PENDIENTE |
| **CP-007** | Autenticación | RF-004 | Límite / Seguridad | Bloqueo temporal por fuerza bruta tras 5 intentos fallidos | Alta | PENDIENTE |
| **CP-008** | Autenticación | RF-005 | Positivo | Autenticación persistente marcando opción "Recordarme" | Media | PENDIENTE |
| **CP-009** | Autenticación | RF-006 | Seguridad | Cierre de sesión seguro con invalidación y regeneración CSRF | Alta | PENDIENTE |
| **CP-010** | Autenticación | RF-007 | Navegación | Redirección de ruta raíz (`/`) según estado de autenticación | Media | PENDIENTE |
| **CP-011** | Seguridad / RBAC | RF-009 | Seguridad | Intento de acceso a ruta protegida sin sesión activa | Alta | PENDIENTE |
| **CP-012** | Seguridad / RBAC | RF-010 | Permisos / Negativo | Invitado intenta acceder a formulario de creación de productos | Alta | PENDIENTE |
| **CP-013** | Seguridad / RBAC | RF-010 | Permisos / Negativo | Invitado intenta eliminar una categoría vía DELETE | Alta | PENDIENTE |
| **CP-014** | Seguridad / RBAC | RF-011 | Permisos / Positivo | Invitado accede a crear pedido y registra venta en mostrador | Alta | PENDIENTE |
| **CP-015** | Seguridad / RBAC | RF-012 | Permisos / Negativo | Invitado intenta editar un pedido registrado previamente | Alta | PENDIENTE |
| **CP-016** | Seguridad / RBAC | RF-012 | Permisos / Negativo | Invitado intenta eliminar un pedido del historial | Alta | PENDIENTE |
| **CP-017** | Seguridad / RBAC | RF-012 | Permisos / Negativo | Invitado intenta acceder a la pantalla de Reportes de Ventas | Alta | PENDIENTE |
| **CP-018** | Seguridad / RBAC | RF-012 | Permisos / Negativo | Invitado intenta acceder a la gestión de Usuarios | Alta | PENDIENTE |
| **CP-019** | Dashboard | RF-014 | Positivo | Carga correcta de contadores globales de entidades | Media | PENDIENTE |
| **CP-020** | Dashboard | RF-016 | Exactitud / Negocio | Exclusión sistemática de pedidos anulados en sumatoria de ventas | Alta | PENDIENTE |
| **CP-021** | Dashboard | RF-017 | Exactitud / Negocio | Cálculo exacto de valuación de inventario activo | Alta | PENDIENTE |
| **CP-022** | Dashboard | RF-018 | Límite | Detección cuantitativa de productos con stock bajo ($\le 5$) | Media | PENDIENTE |
| **CP-023** | Dashboard | RF-019 | Portabilidad | Agrupación de series temporales según driver de BD | Media | PENDIENTE |
| **CP-024** | Dashboard | RF-020 | Lógica / Negocio | Cálculo del Top 5 de productos con mayores ingresos | Media | PENDIENTE |
| **CP-025** | Dashboard | RF-021 | Lógica / Negocio | Cálculo del Top 5 de clientes con mayor volumen de compra | Media | PENDIENTE |
| **CP-026** | Categorías | RF-022 | Navegación | Paginación del catálogo de categorías a 15 registros | Media | PENDIENTE |
| **CP-027** | Categorías | RF-023 | Positivo | Registro exitoso de nueva categoría con datos válidos | Media | PENDIENTE |
| **CP-028** | Categorías | RF-023 | Obligatorio | Intento de registrar categoría con campo nombre vacío | Media | PENDIENTE |
| **CP-029** | Categorías | RF-023 | Inválido / Regex | Validación de caracteres especiales no permitidos en nombre | Media | PENDIENTE |
| **CP-030** | Categorías | RF-024 | Duplicado / Negativo| Intento de registrar categoría con nombre activo existente | Alta | PENDIENTE |
| **CP-031** | Categorías | RF-024 | Papelera / Positivo | Restauración automática de categoría existente en papelera | Alta | PENDIENTE |
| **CP-032** | Categorías | RF-025 | Positivo | Edición exitosa de categoría existente | Media | PENDIENTE |
| **CP-033** | Categorías | RF-026 | Integridad / Bloqueo| Bloqueo de eliminación de categoría con productos vinculados | Alta | PENDIENTE |
| **CP-034** | Categorías | RF-026 | Soft Delete | Eliminación lógica de categoría huérfana sin productos | Media | PENDIENTE |
| **CP-035** | Proveedores | RF-027 | Búsqueda / Filtro | Búsqueda dinámica de proveedores por término `q` | Media | PENDIENTE |
| **CP-036** | Proveedores | RF-028 | Positivo | Registro exitoso de proveedor con todos los campos válidos | Media | PENDIENTE |
| **CP-037** | Proveedores | RF-028 | Límite Mínimo | Registro de proveedor solo con nombre obligatorio | Media | PENDIENTE |
| **CP-038** | Proveedores | RF-028 | Inválido / Regex | Validación de formato de NIT con caracteres alfabéticos | Media | PENDIENTE |
| **CP-039** | Proveedores | RF-028 | Límite / Regex | Validación de longitud mínima de teléfono ($< 7$ caracteres) | Media | PENDIENTE |
| **CP-040** | Proveedores | RF-029 | Papelera / Positivo | Auto-restauración de proveedor en papelera al re-registrar | Alta | PENDIENTE |
| **CP-041** | Proveedores | RF-030 | Positivo | Edición de proveedor y modificación de estado activo/inactivo | Media | PENDIENTE |
| **CP-042** | Proveedores | RF-031 | Integridad / Bloqueo| Bloqueo de eliminación de proveedor con productos vinculados | Alta | PENDIENTE |
| **CP-043** | Proveedores | RF-031 | Soft Delete | Eliminación lógica exitosa de proveedor sin productos asociados | Media | PENDIENTE |
| **CP-044** | Productos | RF-032 | Navegación | Paginación de catálogo de productos a 15 filas con relaciones | Media | PENDIENTE |
| **CP-045** | Productos | RF-033 | Positivo | Registro exitoso de producto con datos válidos e imagen | Alta | PENDIENTE |
| **CP-046** | Productos | RF-033 | Límite / Negativo | Intento de registrar producto con precio negativo | Alta | PENDIENTE |
| **CP-047** | Productos | RF-033 | Límite / Negativo | Intento de registrar producto con stock negativo | Alta | PENDIENTE |
| **CP-048** | Productos | RF-033 | Inválido / Regex | Validación de precio con más de 2 decimales | Media | PENDIENTE |
| **CP-049** | Productos | RF-033 | Integridad | Intento de registrar producto con categoría inexistente | Alta | PENDIENTE |
| **CP-050** | Productos | RF-033 | Inválido / Formato| Carga de archivo no soportado en campo imagen | Media | PENDIENTE |
| **CP-051** | Productos | RF-033 | Límite Superior | Carga de imagen con tamaño superior a 2048 KB | Media | PENDIENTE |
| **CP-052** | Productos | RF-034 | Duplicado / Negativo| Intento de registrar producto con nombre activo existente | Alta | PENDIENTE |
| **CP-053** | Productos | RF-034 | Papelera / Positivo | Auto-restauración de producto en papelera al re-registrar | Alta | PENDIENTE |
| **CP-054** | Productos | RF-035 | Almacenamiento | Sustitución física de imagen en actualización de producto | Media | PENDIENTE |
| **CP-055** | Productos | RF-036 | Soft Delete | Baja lógica de producto (envío a papelera) | Alta | PENDIENTE |
| **CP-056** | Productos | RF-037 | Papelera / Positivo | Restauración manual de producto desde vista de papelera | Alta | PENDIENTE |
| **CP-057** | Productos | RF-038 | Integridad / Bloqueo| Bloqueo de eliminación definitiva de producto con ventas | Alta | PENDIENTE |
| **CP-058** | Productos | RF-038 | Borrado Físico | Eliminación definitiva exitosa de producto sin historial | Alta | PENDIENTE |
| **CP-059** | Productos | RF-039 | Descarga / Binario | Generación y descarga de código QR en formato PNG | Media | PENDIENTE |
| **CP-060** | Productos | RF-040 | Descarga / Binario | Generación y descarga de código de barras Code128 en PNG | Media | PENDIENTE |
| **CP-061** | Productos | RF-041 | Unitario / Helper | Formato determinista de SKU estándar (`PRD-XXXXXX`) | Media | PENDIENTE |
| **CP-062** | Productos | RF-042 | Documental | Exportación de catálogo de productos a PDF Landscape | Media | PENDIENTE |
| **CP-063** | Productos | RF-043 | Documental | Exportación de catálogo de productos a libro Excel | Media | PENDIENTE |
| **CP-064** | Clientes | RF-044 | Búsqueda / Filtro | Búsqueda dinámica de clientes por nombre o correo con `q` | Media | PENDIENTE |
| **CP-065** | Clientes | RF-045 | Positivo | Registro exitoso de nuevo cliente con datos válidos | Media | PENDIENTE |
| **CP-066** | Clientes | RF-045 | Obligatorio | Intento de registrar cliente con nombre o email vacío | Media | PENDIENTE |
| **CP-067** | Clientes | RF-045 | Inválido | Validación de formato sintáctico inválido en email de cliente | Media | PENDIENTE |
| **CP-068** | Clientes | RF-046 | Duplicado / Negativo| Intento de registrar cliente con correo ya activo | Alta | PENDIENTE |
| **CP-069** | Clientes | RF-046 | Papelera / Positivo | Auto-restauración de cliente en papelera al re-registrar email | Alta | PENDIENTE |
| **CP-070** | Clientes | RF-047 | Positivo | Edición de cliente validando email propio vs ajeno | Media | PENDIENTE |
| **CP-071** | Clientes | RF-048 | Integridad / Bloqueo| Bloqueo de eliminación de cliente con pedidos asociados | Alta | PENDIENTE |
| **CP-072** | Clientes | RF-048 | Soft Delete | Eliminación lógica de cliente sin pedidos registrados | Media | PENDIENTE |
| **CP-073** | Pedidos / Ventas | RF-049 | Navegación | Listado paginado de pedidos con cliente y usuario registrador| Media | PENDIENTE |
| **CP-074** | Pedidos / Ventas | RF-050 | Lógica / Catálogo | Formulario de venta muestra solo productos con $stock > 0$ | Media | PENDIENTE |
| **CP-075** | Pedidos / Ventas | RF-051 | Inválido / Fecha | Rechazo de venta con fecha futura (`before_or_equal:today`) | Alta | PENDIENTE |
| **CP-076** | Pedidos / Ventas | RF-051 | Obligatorio | Rechazo de pedido sin líneas de producto (`items` vacío) | Alta | PENDIENTE |
| **CP-077** | Pedidos / Ventas | RF-051 | Duplicado en Array | Rechazo de productos repetidos en líneas separadas | Alta | PENDIENTE |
| **CP-078** | Pedidos / Ventas | RF-051 | Límite / Cantidad | Validación de cantidad mínima ($< 1$) o excesiva ($> 9999$) | Media | PENDIENTE |
| **CP-079** | Pedidos / Ventas | RF-052 | Positivo / ACID | Registro de venta simple con descuento atómico de stock | Alta | PENDIENTE |
| **CP-080** | Pedidos / Ventas | RF-052 | Positivo / ACID | Registro de venta multi-línea con cómputo de subtotales | Alta | PENDIENTE |
| **CP-081** | Pedidos / Ventas | RF-052 | Límite Exacto | Venta en el límite exacto del stock ($stock = cantidad$) | Alta | PENDIENTE |
| **CP-082** | Pedidos / Ventas | RF-053 | Límite / Insuficiente| Rechazo de venta por insuficiencia de stock por 1 unidad | Alta | PENDIENTE |
| **CP-083** | Pedidos / Ventas | RF-053 | Transaccional / ACID| Rollback total en venta multi-línea ante falla de un ítem | Alta | PENDIENTE |
| **CP-084** | Pedidos / Ventas | RF-052 | Exactitud / bcmath | Preservación de snapshot de precio y cálculo con bcmath | Alta | PENDIENTE |
| **CP-085** | Pedidos / Ventas | RF-054 | Transición / Estado | Anulación de pedido y restitución exacta de stock | Alta | PENDIENTE |
| **CP-086** | Pedidos / Ventas | RF-055 | Transición / Estado | Reactivación de pedido anulado a completado | Alta | PENDIENTE |
| **CP-087** | Pedidos / Ventas | RF-056 | Idempotencia | Modificación de pedido anulado hacia anulado sin tocar stock | Alta | PENDIENTE |
| **CP-088** | Pedidos / Ventas | RF-057 | Resiliencia | Anular pedido completado cuyo stock general está en 0 | Alta | PENDIENTE |
| **CP-089** | Pedidos / Ventas | RF-058 | Persistencia | Eliminación de pedido completado restituye el stock | Alta | PENDIENTE |
| **CP-090** | Pedidos / Ventas | RF-059 | Regresión / Stock | Eliminación de pedido anulado NO genera stock fantasma | Alta | PENDIENTE |
| **CP-091** | Reportes | RF-060 | Coherencia / Fechas | Rechazo de filtro con fecha fin anterior a fecha inicio | Media | PENDIENTE |
| **CP-092** | Reportes | RF-061 | Filtro Combinado | Filtrado de ventas por cliente, estado y rango temporal | Media | PENDIENTE |
| **CP-093** | Reportes | RF-062 | Exactitud Contable | Métricas agregadas globales sobre el total filtrado | Alta | PENDIENTE |
| **CP-094** | Reportes | RF-063 | Límite / Vacío | Manejo de reportes sin coincidencias (HTTP 200 y ceros) | Media | PENDIENTE |
| **CP-095** | Reportes | RF-064 | Documental | Exportación de reporte a PDF aplicando filtros activos | Media | PENDIENTE |
| **CP-096** | Reportes | RF-065 | Documental | Exportación de reporte a Excel aplicando filtros activos | Media | PENDIENTE |
| **CP-097** | Usuarios | RF-067 | Positivo | Registro exitoso de nuevo usuario administrador o invitado | Alta | PENDIENTE |
| **CP-098** | Usuarios | RF-067 | Inválido / Password | Rechazo de contraseña con menos de 6 car. o no alfanumérica | Alta | PENDIENTE |
| **CP-099** | Usuarios | RF-069 | Seguridad / Bloqueo| Administrador intenta auto-degradar su propio rol | Alta | PENDIENTE |
| **CP-100** | Usuarios | RF-070 | Seguridad / Bloqueo| Administrador intenta eliminar su propia cuenta de usuario | Alta | PENDIENTE |

---

## 3. Especificación Detallada de Casos de Prueba

---

### Módulo 1: Autenticación y Control de Sesión

```
CASO DE PRUEBA: CP-001
Requisito: RF-001 / RNF-001
Módulo: Autenticación y Sesión
Nombre: Inicio de sesión exitoso con credenciales de Administrador
Objetivo: Verificar que un usuario con rol 'administrador' puede autenticarse con éxito y acceder al dashboard.
Prioridad: Alta
Precondiciones: Usuario administrador existente en base de datos ('dueno@supermarket.com' / 'password123').
Datos de Prueba: email: "dueno@supermarket.com", password: "password123", remember: false.
Pasos de Ejecución:
  1. Navegar a la ruta GET /login.
  2. Ingresar el correo electrónico "dueno@supermarket.com" en el campo 'email'.
  3. Ingresar la contraseña "password123" en el campo 'password'.
  4. Presionar el botón "Ingresar al Sistema" (POST /login).
Resultado Esperado: El sistema autentica al usuario, regenera la sesión, responde con HTTP 302 hacia /dashboard y muestra la insignia "👑 Administrador".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: Salida de prueba de integración en PHPUnit y captura de pantalla del Dashboard con rol administrador.
Observaciones: Verifica también la función Hash::check contra el hash bcrypt cost 12.
```

```
CASO DE PRUEBA: CP-002
Requisito: RF-001 / RNF-001
Módulo: Autenticación y Sesión
Nombre: Inicio de sesión exitoso con credenciales de Invitado
Objetivo: Verificar que un usuario con rol 'invitado' puede autenticarse con éxito y acceder al dashboard.
Prioridad: Alta
Precondiciones: Usuario invitado existente en base de datos ('empleado@supermarket.com' / 'password123').
Datos de Prueba: email: "empleado@supermarket.com", password: "password123", remember: false.
Pasos de Ejecución:
  1. Navegar a GET /login.
  2. Ingresar "empleado@supermarket.com" en el campo de correo.
  3. Ingresar "password123" en el campo de contraseña.
  4. Enviar el formulario vía POST /login.
Resultado Esperado: El sistema autentica al usuario, redirige a /dashboard y muestra la insignia "👤 Empleado".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: Salida de aserción assertAuthenticatedAs en PHPUnit.
Observaciones: Confirma la discriminación correcta de rol en la cabecera.
```

```
CASO DE PRUEBA: CP-003
Requisito: RF-002 / RNF-001
Módulo: Autenticación y Sesión
Nombre: Rechazo de inicio de sesión por contraseña incorrecta
Objetivo: Validar que el sistema rechace el acceso ante contraseña errónea sin comprometer la seguridad.
Prioridad: Alta
Precondiciones: Usuario existente con correo 'dueno@supermarket.com'.
Datos de Prueba: email: "dueno@supermarket.com", password: "passwordIncorrecta999".
Pasos de Ejecución:
  1. Navegar a GET /login.
  2. Ingresar "dueno@supermarket.com" en 'email'.
  3. Ingresar "passwordIncorrecta999" en 'password'.
  4. Enviar el formulario.
Resultado Esperado: Se deniega el acceso, se mantiene el usuario como no autenticado, se incrementa el throttler y se retorna a /login con el mensaje "Correo o contraseña incorrectos. Verifique sus credenciales.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['email']) en PHPUnit.
Observaciones: Mensaje genérico para prevenir enumeración de usuarios.
```

```
CASO DE PRUEBA: CP-004
Requisito: RF-002
Módulo: Autenticación y Sesión
Nombre: Rechazo de inicio de sesión por correo inexistente
Objetivo: Verificar el rechazo y manejo seguro cuando el correo no existe en la base de datos.
Prioridad: Alta
Precondiciones: La base de datos no contiene el usuario 'fantasma@supermarket.com'.
Datos de Prueba: email: "fantasma@supermarket.com", password: "password123".
Pasos de Ejecución:
  1. Navegar a GET /login.
  2. Ingresar "fantasma@supermarket.com" y contraseña cualquiera.
  3. Enviar el formulario.
Resultado Esperado: Acceso denegado, retorno con error en campo 'email' y persistencia del correo en el input.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertGuest() y assertSessionHasErrors en PHPUnit.
Observaciones: Mismo mensaje que CP-003.
```

```
CASO DE PRUEBA: CP-005
Requisito: RF-003
Módulo: Autenticación y Sesión
Nombre: Validación de campos obligatorios vacíos en formulario de login
Objetivo: Comprobar que no se procesen peticiones con campos vacíos.
Prioridad: Media
Precondiciones: Ninguna.
Datos de Prueba: email: "", password: "".
Pasos de Ejecución:
  1. Enviar una petición POST a /login con cuerpo vacío `{}`.
Resultado Esperado: Respuesta de validación HTTP 302 con errores en sesión: "El correo es obligatorio." y "La contraseña es obligatoria.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['email', 'password']).
Observaciones: Validación ejecutada por $request->validate en LoginController.
```

```
CASO DE PRUEBA: CP-006
Requisito: RF-003
Módulo: Autenticación y Sesión
Nombre: Validación de formato sintáctico inválido en campo email de login
Objetivo: Rechazar cadenas que no cumplan la estructura de una dirección de correo.
Prioridad: Media
Precondiciones: Ninguna.
Datos de Prueba: email: "usuario-sin-arroba-ni-dominio", password: "password123".
Pasos de Ejecución:
  1. Enviar POST a /login con email no válido.
Resultado Esperado: Retorno al formulario con mensaje "Ingrese un correo válido.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['email']).
Observaciones: Regla 'email' del validador de Laravel.
```

```
CASO DE PRUEBA: CP-007
Requisito: RF-004 / RNF-002
Módulo: Autenticación y Sesión
Nombre: Bloqueo temporal por fuerza bruta tras 5 intentos fallidos (Rate Limiting)
Objetivo: Verificar que tras 5 intentos fallidos consecutivos, el throttler bloquee temporalmente el acceso.
Prioridad: Alta
Precondiciones: Throttling activo en LoginController (maxAttempts = 5, decayMinutes = 2).
Datos de Prueba: email: "dueno@supermarket.com", password errónea x 5 veces.
Pasos de Ejecución:
  1. Enviar 5 peticiones POST consecutivas a /login con contraseña incorrecta para la misma IP/correo.
  2. Enviar un 6to intento de login (incluso con la contraseña correcta).
Resultado Esperado: El 6to intento es interceptado de inmediato sin validar credenciales, retornando error "Demasiados intentos de inicio de sesión. Por favor, inténtelo de nuevo en 120 segundos.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors con mensaje de throttle en PHPUnit.
Observaciones: Protege contra ataques automatizados de diccionario.
```

```
CASO DE PRUEBA: CP-008
Requisito: RF-005
Módulo: Autenticación y Sesión
Nombre: Autenticación persistente marcando opción "Recordarme"
Objetivo: Validar la emisión de la cookie remember_web al solicitar persistencia de sesión.
Prioridad: Media
Precondiciones: Usuario válido en base de datos.
Datos de Prueba: email: "dueno@supermarket.com", password: "password123", remember: "1".
Pasos de Ejecución:
  1. Enviar POST a /login con credenciales válidas y 'remember' => '1'.
Resultado Esperado: Autenticación exitosa y presencia de la cookie de sesión de larga duración emitida por Laravel.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertCookieNotExpired en PHPUnit o inspección de cabeceras Set-Cookie.
Observaciones: Utiliza la columna remember_token de la tabla users.
```

```
CASO DE PRUEBA: CP-009
Requisito: RF-006 / RNF-003
Módulo: Autenticación y Sesión
Nombre: Cierre de sesión seguro con invalidación de sesión y regeneración CSRF
Objetivo: Garantizar que la sesión del usuario sea completamente destruida al desautenticarse.
Prioridad: Alta
Precondiciones: Usuario autenticado con sesión activa.
Datos de Prueba: Token CSRF válido.
Pasos de Ejecución:
  1. Estando autenticado, emitir una petición POST a /logout con token CSRF válido.
Resultado Esperado: El usuario queda desautenticado (auth()->check() = false), la sesión previa queda invalidada, el token CSRF se regenera y se redirige con HTTP 302 a /login.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertGuest() y assertRedirect('/login') en PHPUnit.
Observaciones: Previene la reutilización de sesiones huérfanas.
```

```
CASO DE PRUEBA: CP-010
Requisito: RF-007
Módulo: Autenticación y Sesión
Nombre: Redirección condicional en ruta raíz ('/') según estado de sesión
Objetivo: Comprobar la lógica de enrutamiento inicial de la aplicación.
Prioridad: Media
Precondiciones: Casos con usuario sin sesión y con sesión activa.
Datos de Prueba: Petición GET a '/'.
Pasos de Ejecución:
  1. Como usuario no autenticado, solicitar GET /.
  2. Iniciar sesión y solicitar nuevamente GET /.
Resultado Esperado: En el paso 1 redirige a /login. En el paso 2 redirige a /dashboard.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: HomeRedirectTest existente en tests/Feature/HomeRedirectTest.php.
Observaciones: Regla definida en closure de routes/web.php:L21-27.
```

---

### Módulo 2: Seguridad y Control de Acceso (RBAC)

```
CASO DE PRUEBA: CP-011
Requisito: RF-009
Módulo: Seguridad / RBAC
Nombre: Bloqueo de acceso a rutas protegidas para usuarios no autenticados
Objetivo: Asegurar que ninguna sección privada sea accesible sin autenticación previa.
Prioridad: Alta
Precondiciones: Sesión no iniciada.
Datos de Prueba: URIs: /dashboard, /productos, /pedidos, /categorias, /clientes, /usuarios, /reportes/ventas.
Pasos de Ejecución:
  1. Solicitar vía GET cada una de las URIs indicadas sin enviar cookies de sesión.
Resultado Esperado: Todas las peticiones responden con HTTP 302 redirigiendo a /login.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertRedirect(route('login')) para cada ruta.
Observaciones: Verificado por middleware Authenticate estándar de Laravel.
```

```
CASO DE PRUEBA: CP-012
Requisito: RF-010 / RF-012
Módulo: Seguridad / RBAC
Nombre: Invitado intenta acceder al formulario de creación de productos
Objetivo: Verificar que el rol 'invitado' no puede ingresar a pantallas de administración de catálogo.
Prioridad: Alta
Precondiciones: Usuario autenticado con rol 'invitado' ('empleado@supermarket.com').
Datos de Prueba: GET /productos/create.
Pasos de Ejecución:
  1. Autenticar como invitado.
  2. Intentar acceder a GET /productos/create.
Resultado Esperado: El middleware 'rol:administrador' intercepta la petición y responde con código HTTP 403 Forbidden.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertForbidden() en PHPUnit y vista errors/403.blade.php renderizada.
Observaciones: Protegido a nivel de ruta y por método soloDueno().
```

```
CASO DE PRUEBA: CP-013
Requisito: RF-010 / RF-012
Módulo: Seguridad / RBAC
Nombre: Invitado intenta eliminar una categoría vía petición HTTP directa
Objetivo: Garantizar que peticiones maliciosas o directas de borrado desde rol invitado sean bloqueadas.
Prioridad: Alta
Precondiciones: Categoría existente con ID 1; usuario autenticado con rol 'invitado'.
Datos de Prueba: DELETE /categorias/1.
Pasos de Ejecución:
  1. Autenticado como invitado, enviar una petición DELETE a /categorias/1.
Resultado Esperado: Respuesta HTTP 403 Forbidden. La categoría permanece intacta en la base de datos.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertForbidden() y assertDatabaseHas('categorias', ['idCategoria' => 1]).
Observaciones: Impide ataques por inyección de verbos HTTP.
```

```
CASO DE PRUEBA: CP-014
Requisito: RF-011 / RF-050
Módulo: Seguridad / RBAC
Nombre: Invitado accede a crear pedido y registra venta en mostrador
Objetivo: Confirmar que el empleado/invitado sí posee autorización explícita para operar el punto de venta.
Prioridad: Alta
Precondiciones: Usuario 'invitado' autenticado; cliente activo y producto con stock suficiente.
Datos de Prueba: idCliente: 1, fecha: fecha_hoy, items: [[idProducto: 1, cantidad: 2]].
Pasos de Ejecución:
  1. Autenticado como invitado, navegar a GET /pedidos/create.
  2. Verificar que la vista carga con código HTTP 200.
  3. Enviar petición POST a /pedidos con los datos del pedido.
Resultado Esperado: La vista carga (HTTP 200), la venta se registra con éxito (HTTP 302 a /pedidos) y el pedido se almacena con idUsuario correspondiente al usuario invitado.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: PedidoInvitadoYDescargasTest existente en el repositorio.
Observaciones: Funcionalidad operativa de cajero para el empleado.
```

```
CASO DE PRUEBA: CP-015
Requisito: RF-012 / RF-054
Módulo: Seguridad / RBAC
Nombre: Invitado intenta editar un pedido registrado previamente
Objetivo: Asegurar que un empleado no pueda modificar ventas ya cerradas.
Prioridad: Alta
Precondiciones: Pedido existente con ID 1; usuario 'invitado' autenticado.
Datos de Prueba: GET /pedidos/1/edit y PUT /pedidos/1.
Pasos de Ejecución:
  1. Autenticado como invitado, solicitar GET /pedidos/1/edit.
  2. Enviar PUT /pedidos/1 intentando modificar cliente o estado.
Resultado Esperado: Ambas peticiones responden con HTTP 403 Forbidden.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertForbidden() verificado en PedidoInvitadoYDescargasTest.
Observaciones: Solo el Administrador/Dueño tiene permiso de corrección o anulación.
```

```
CASO DE PRUEBA: CP-016
Requisito: RF-012 / RF-058
Módulo: Seguridad / RBAC
Nombre: Invitado intenta eliminar un pedido del historial
Objetivo: Prohibir la destrucción o anulación de pedidos por parte de usuarios sin privilegio de administrador.
Prioridad: Alta
Precondiciones: Pedido existente; usuario 'invitado' autenticado.
Datos de Prueba: DELETE /pedidos/1.
Pasos de Ejecución:
  1. Autenticado como invitado, emitir DELETE /pedidos/1.
Resultado Esperado: Respuesta HTTP 403 Forbidden. El pedido no se elimina ni se modifica su estado.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertForbidden() y comprobación de persistencia en tabla pedidos.
Observaciones: Garantía de auditoría contable.
```

```
CASO DE PRUEBA: CP-017
Requisito: RF-012 / RF-060
Módulo: Seguridad / RBAC
Nombre: Invitado intenta acceder a la pantalla de Reportes de Ventas
Objetivo: Impedir la visualización de métricas contables y balances financieros a usuarios invitados.
Prioridad: Alta
Precondiciones: Usuario 'invitado' autenticado.
Datos de Prueba: GET /reportes/ventas, GET /reportes/ventas/pdf, GET /reportes/ventas/excel.
Pasos de Ejecución:
  1. Autenticado como invitado, solicitar GET /reportes/ventas.
  2. Solicitar GET /reportes/ventas/pdf.
  3. Solicitar GET /reportes/ventas/excel.
Resultado Esperado: Las tres rutas retornan de forma inmediata HTTP 403 Forbidden.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertForbidden() para las tres rutas en PHPUnit.
Observaciones: Vinculado al hallazgo de discrepancia en UI (RF-013).
```

```
CASO DE PRUEBA: CP-018
Requisito: RF-012 / RF-066
Módulo: Seguridad / RBAC
Nombre: Invitado intenta acceder al módulo de gestión de Usuarios
Objetivo: Bloquear la administración de cuentas y asignación de roles a usuarios no administradores.
Prioridad: Alta
Precondiciones: Usuario 'invitado' autenticado.
Datos de Prueba: GET /usuarios, GET /usuarios/create, POST /usuarios.
Pasos de Ejecución:
  1. Autenticado como invitado, solicitar GET /usuarios.
Resultado Esperado: Respuesta HTTP 403 Forbidden.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertForbidden() en PHPUnit.
Observaciones: Protegido por middleware rol:administrador y soloDueno().
```

---

### Módulo 3: Panel de Control (Dashboard)

```
CASO DE PRUEBA: CP-019
Requisito: RF-014 / RF-015
Módulo: Dashboard
Nombre: Carga correcta de contadores globales de entidades en Dashboard
Objetivo: Verificar que los contadores numéricos reflejen con exactitud las filas de base de datos.
Prioridad: Media
Precondiciones: Base de datos con 5 categorías, 3 proveedores, 20 productos, 10 clientes y 15 pedidos (10 completados, 5 anulados).
Datos de Prueba: Usuario autenticado accede a GET /dashboard.
Pasos de Ejecución:
  1. Navegar a /dashboard.
  2. Inspeccionar los datos inyectados en la vista: totalCategorias, totalProveedores, totalProductos, totalClientes, totalPedidos, totalPedidosCompletados, totalPedidosAnulados.
Resultado Esperado: La vista recibe exactamente los valores correspondientes (5, 3, 20, 10, 15, 10, 5) y responde con HTTP 200.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertViewHas('totalCategorias', 5) etc. en PHPUnit.
Observaciones: Consultas optimizadas con count().
```

```
CASO DE PRUEBA: CP-020
Requisito: RF-016
Módulo: Dashboard
Nombre: Exclusión sistemática de pedidos anulados en la sumatoria de ventas
Objetivo: Asegurar que el total monetario de ventas del Dashboard no sume pedidos anulados.
Prioridad: Alta
Precondiciones: Pedido A completado por valor de 100.00; Pedido B anulado por valor de 500.00.
Datos de Prueba: GET /dashboard.
Pasos de Ejecución:
  1. Cargar el dashboard.
  2. Comprobar el valor de la variable 'totalVentas'.
Resultado Esperado: 'totalVentas' equivale a 100.00. Los 500.00 del pedido anulado son omitidos.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: PaginacionYDashboardTest existente en tests/Feature/.
Observaciones: Regla Pedido::where('estado', '!=', 'anulado')->sum('total').
```

```
CASO DE PRUEBA: CP-021
Requisito: RF-017
Módulo: Dashboard
Nombre: Cálculo exacto de la valuación monetaria del inventario activo
Objetivo: Validar que 'inventarioValuado' calcule la sumatoria de stock * precio de todos los productos.
Prioridad: Alta
Precondiciones: Producto 1: stock 10, precio 20.00 (valor: 200.00); Producto 2: stock 5, precio 50.00 (valor: 250.00).
Datos de Prueba: GET /dashboard.
Pasos de Ejecución:
  1. Cargar el dashboard.
  2. Inspeccionar la variable 'inventarioValuado'.
Resultado Esperado: 'inventarioValuado' es exactamente 450.00.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertViewHas('inventarioValuado', 450.0) en PHPUnit.
Observaciones: Consulta Producto::sum(DB::raw('stock * precio')).
```

```
CASO DE PRUEBA: CP-022
Requisito: RF-018
Módulo: Dashboard
Nombre: Detección cuantitativa de productos con stock bajo en el umbral crítico
Objetivo: Verificar que se contabilicen los productos con stock menor o igual a 5 unidades.
Prioridad: Media
Precondiciones: Producto A con stock 5; Producto B con stock 2; Producto C con stock 6.
Datos de Prueba: GET /dashboard.
Pasos de Ejecución:
  1. Cargar el dashboard.
  2. Verificar 'stockBajoCant'.
Resultado Esperado: 'stockBajoCant' retorna 2 (incluye productos A y B; excluye producto C).
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertViewHas('stockBajoCant', 2).
Observaciones: Umbral límite <= 5 verificado en routes/web.php:L92.
```

```
CASO DE PRUEBA: CP-023
Requisito: RF-019 / RNF-012
Módulo: Dashboard
Nombre: Compatibilidad de agregaciones de fecha por motor de base de datos
Objetivo: Comprobar que las expresiones de fecha operen correctamente en SQLite y en MySQL.
Prioridad: Media
Precondiciones: Conexión SQLite activa en tests y conexión MySQL en staging.
Datos de Prueba: GET /dashboard con ventas registradas en meses pasados.
Pasos de Ejecución:
  1. Ejecutar la ruta dashboard bajo driver SQLite y comprobar generación de etiquetas mensuales.
  2. Ejecutar la ruta bajo driver MySQL.
Resultado Esperado: En ambos entornos la consulta se ejecuta sin error de sintaxis SQL y produce el array de labelsMes formateado en español (ej. "sep. 26").
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: Comprobación de labelsMes y dataVentasMes en viewData.
Observaciones: Resuelve strftime vs DATE_FORMAT dinámicamente.
```

```
CASO DE PRUEBA: CP-024
Requisito: RF-020
Módulo: Dashboard
Nombre: Cálculo del Top 5 de productos con mayores ingresos excluyendo anulados
Objetivo: Comprobar el ordenamiento por ingresos reales de productos vendidos.
Prioridad: Media
Precondiciones: Producto A con ventas completadas por 800.00; Producto B con ventas completadas por 300.00 y venta anulada por 1000.00.
Datos de Prueba: GET /dashboard.
Pasos de Ejecución:
  1. Cargar el dashboard y examinar la colección 'topProductos'.
Resultado Esperado: Producto A se ubica en primera posición con 800.00 de ingresos; Producto B se ubica por debajo con 300.00.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSee / assertViewHas('topProductos') en PHPUnit.
Observaciones: Cruce de detalle_pedidos con pedidos excluyendo anulados.
```

```
CASO DE PRUEBA: CP-025
Requisito: RF-021
Módulo: Dashboard
Nombre: Cálculo del Top 5 de clientes con mayor volumen de compra
Objetivo: Comprobar el ordenamiento descendente de clientes por total comprado válido.
Prioridad: Media
Precondiciones: Cliente A: 2 pedidos completados sumando 400.00; Cliente B: 1 pedido completado por 200.00 y 1 anulado por 600.00.
Datos de Prueba: GET /dashboard.
Pasos de Ejecución:
  1. Cargar dashboard e inspeccionar 'topClientes'.
Resultado Esperado: Cliente A encabeza el listado con 400.00.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: Inspección de 'topClientes' en viewData.
Observaciones: Límite de 5 registros verificado con ->limit(5).
```

---

### Módulo 4: Gestión de Categorías

```
CASO DE PRUEBA: CP-026
Requisito: RF-022 / RNF-007
Módulo: Categorías
Nombre: Paginación del catálogo de categorías a 15 registros por página
Objetivo: Verificar que el listado divida los registros en páginas de 15 elementos y preserve el query string.
Prioridad: Media
Precondiciones: 20 categorías creadas en la base de datos; usuario autenticado.
Datos de Prueba: GET /categorias y GET /categorias?page=2.
Pasos de Ejecución:
  1. Solicitar GET /categorias.
  2. Verificar cantidad de ítems en la página 1.
  3. Solicitar GET /categorias?page=2 y verificar ítems restantes.
Resultado Esperado: Página 1 contiene exactamente 15 categorías. Página 2 contiene 5 categorías. La instancia es un LengthAwarePaginator.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertEquals(15, $categorias->count()) y assertEquals(20, $categorias->total()).
Observaciones: Ordenamiento alfabético por nombre.
```

```
CASO DE PRUEBA: CP-027
Requisito: RF-023
Módulo: Categorías
Nombre: Registro exitoso de nueva categoría con datos válidos
Objetivo: Crear una categoría proporcionando nombre y descripción permitidos.
Prioridad: Media
Precondiciones: Administrador autenticado; el nombre "Lácteos Frescos" no existe.
Datos de Prueba: nombre: "Lácteos Frescos", descripcion: "Productos derivados de la leche pasteurizada".
Pasos de Ejecución:
  1. Enviar POST /categorias con los datos de prueba.
Resultado Esperado: Redirección con HTTP 302 a /categorias, mensaje flash de éxito en sesión y registro persistido en la tabla 'categorias'.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertDatabaseHas('categorias', ['nombre' => 'Lácteos Frescos']).
Observaciones: Verifica el flujo CRUD de creación.
```

```
CASO DE PRUEBA: CP-028
Requisito: RF-023
Módulo: Categorías
Nombre: Intento de registrar categoría con campo nombre vacío
Objetivo: Validar que el nombre sea un campo estrictamente obligatorio.
Prioridad: Media
Precondiciones: Administrador autenticado.
Datos de Prueba: nombre: "", descripcion: "Sin nombre".
Pasos de Ejecución:
  1. Enviar POST /categorias con nombre vacío.
Resultado Esperado: Rechazo con HTTP 302, sin insertar registros, con error de validación "El nombre es obligatorio.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['nombre']).
Observaciones: Validación 'required' en CategoriaController:L30.
```

```
CASO DE PRUEBA: CP-029
Requisito: RF-023
Módulo: Categorías
Nombre: Validación de caracteres especiales no permitidos en nombre de categoría
Objetivo: Asegurar que el nombre cumpla con la expresión regular alfanumérica permitida.
Prioridad: Media
Precondiciones: Administrador autenticado.
Datos de Prueba: nombre: "Categoría <script>alert(1)</script> / $%", descripcion: "Inyección".
Pasos de Ejecución:
  1. Enviar POST /categorias con caracteres especiales no permitidos.
Resultado Esperado: Rechazo con error "El nombre solo puede contener letras, números, espacios y . , - _".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['nombre']).
Observaciones: Previene almacenamiento de scripts y nombres confusos.
```

```
CASO DE PRUEBA: CP-030
Requisito: RF-024
Módulo: Categorías
Nombre: Intento de registrar categoría con nombre activo ya existente
Objetivo: Impedir la duplicación de nombres entre categorías activas.
Prioridad: Alta
Precondiciones: Categoría "Limpieza" activa existente en base de datos; Administrador autenticado.
Datos de Prueba: nombre: "Limpieza", descripcion: "Segunda categoría de limpieza".
Pasos de Ejecución:
  1. Enviar POST /categorias con nombre "Limpieza".
Resultado Esperado: Rechazo de la operación con error "Ya existe una categoría activa con este nombre.". No se duplica el registro.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: CategoriaIntegrityTest existente en tests/Feature/.
Observaciones: Concurrencia controlada por controlador y restricción UNIQUE en BD.
```

```
CASO DE PRUEBA: CP-031
Requisito: RF-024
Módulo: Categorías
Nombre: Auto-restauración de categoría existente en papelera al re-registrar su nombre
Objetivo: Comprobar que registrar una categoría eliminada previamente restaure el registro previo en lugar de fallar o duplicar.
Prioridad: Alta
Precondiciones: Categoría "Bebidas" en soft-delete (deleted_at != null); Administrador autenticado.
Datos de Prueba: nombre: "Bebidas", descripcion: "Nueva descripción para bebidas".
Pasos de Ejecución:
  1. Enviar POST /categorias con nombre "Bebidas".
Resultado Esperado: El registro existente es restaurado (deleted_at = null), su descripción se actualiza, y redirige a /categorias con mensaje "La categoría \"Bebidas\" existía previamente en la papelera y ha sido restaurada con éxito.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertDatabaseHas('categorias', ['nombre' => 'Bebidas', 'deleted_at' => null]).
Observaciones: Regla inteligente de CategoriaController:L42-50.
```

```
CASO DE PRUEBA: CP-032
Requisito: RF-025
Módulo: Categorías
Nombre: Edición exitosa de categoría existente
Objetivo: Actualizar los datos de una categoría conservando su identificador.
Prioridad: Media
Precondiciones: Categoría con ID 5 existente; Administrador autenticado.
Datos de Prueba: PUT /categorias/5, nombre: "Panadería y Masas", descripcion: "Pan recién horneado".
Pasos de Ejecución:
  1. Enviar PUT a /categorias/5 con los datos actualizados.
Resultado Esperado: Redirección con mensaje de éxito y actualización de campos en base de datos.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertDatabaseHas('categorias', ['idCategoria' => 5, 'nombre' => 'Panadería y Masas']).
Observaciones: Valida que no choque consigo misma en la validación de unicidad.
```

```
CASO DE PRUEBA: CP-033
Requisito: RF-026
Módulo: Categorías
Nombre: Bloqueo de eliminación de categoría con productos asociados
Objetivo: Impedir la eliminación de una categoría que posee productos, protegiendo la integridad referencial.
Prioridad: Alta
Precondiciones: Categoría con ID 2 vinculada a al menos 1 producto en tabla 'productos'; Administrador autenticado.
Datos de Prueba: DELETE /categorias/2.
Pasos de Ejecución:
  1. Enviar DELETE /categorias/2.
Resultado Esperado: La eliminación se bloquea, no se marca soft delete (deleted_at = null) y redirige a /categorias con mensaje de error "No se puede eliminar la categoría... porque tiene X producto(s) asociado(s)...".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: CategoriaIntegrityTest verificado en tests/Feature/.
Observaciones: Respaldado por FK restrictiva en migración fix_database_consistency.
```

```
CASO DE PRUEBA: CP-034
Requisito: RF-026 / RNF-008
Módulo: Categorías
Nombre: Eliminación lógica exitosa de categoría huérfana sin productos
Objetivo: Permitir el soft delete de una categoría que no tiene productos asignados.
Prioridad: Media
Precondiciones: Categoría con ID 8 sin ningún producto vinculado; Administrador autenticado.
Datos de Prueba: DELETE /categorias/8.
Pasos de Ejecución:
  1. Enviar DELETE /categorias/8.
Resultado Esperado: La categoría recibe marca de soft delete (deleted_at != null), no se destruye físicamente de la base de datos y se muestra mensaje de éxito.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSoftDeleted('categorias', ['idCategoria' => 8]).
Observaciones: Comprueba el funcionamiento de SoftDeletes.
```

---

### Módulo 5: Gestión de Proveedores

```
CASO DE PRUEBA: CP-035
Requisito: RF-027
Módulo: Proveedores
Nombre: Búsqueda dinámica de proveedores por término 'q' en múltiples campos
Objetivo: Validar que el parámetro 'q' filtre por nombre, NIT, email o teléfono.
Prioridad: Media
Precondiciones: Proveedor 1: "Distribuidora Central", NIT: "10203040"; Proveedor 2: "Lácteos del Valle", NIT: "55667788".
Datos de Prueba: GET /proveedores?q=10203040 y GET /proveedores?q=Valle.
Pasos de Ejecución:
  1. Enviar GET /proveedores?q=10203040.
  2. Enviar GET /proveedores?q=Valle.
Resultado Esperado: La primera consulta retorna únicamente a "Distribuidora Central". La segunda consulta retorna únicamente a "Lácteos del Valle".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: ProveedorTest existente en tests/Feature/ProveedorTest.php.
Observaciones: Búsqueda con operador LIKE y agrupamiento OR anidado.
```

```
CASO DE PRUEBA: CP-036
Requisito: RF-028
Módulo: Proveedores
Nombre: Registro exitoso de proveedor con todos los campos válidos
Objetivo: Crear un proveedor completando información fiscal, de contacto y ubicación.
Prioridad: Media
Precondiciones: Administrador autenticado; nombre "Embutidos San Marcos" no registrado.
Datos de Prueba: nombre: "Embutidos San Marcos", nit: "9876543-1", telefono: "4455667", email: "ventas@sanmarcos.test", direccion: "Calle Junín 450", activo: "1".
Pasos de Ejecución:
  1. Enviar POST /proveedores con los datos indicados.
Resultado Esperado: Redirección con HTTP 302, mensaje de éxito en sesión y datos persistidos en tabla 'proveedores'.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertDatabaseHas('proveedores', ['nombre' => 'Embutidos San Marcos']).
Observaciones: ProveedorTest verificado.
```

```
CASO DE PRUEBA: CP-037
Requisito: RF-028
Módulo: Proveedores
Nombre: Registro exitoso de proveedor solo con nombre obligatorio
Objetivo: Confirmar que los campos de contacto y NIT sean opcionales.
Prioridad: Media
Precondiciones: Administrador autenticado; nombre "Proveedor Rápido" disponible.
Datos de Prueba: nombre: "Proveedor Rápido", nit: null, telefono: null, email: null, direccion: null.
Pasos de Ejecución:
  1. Enviar POST /proveedores con solo el nombre.
Resultado Esperado: El proveedor se registra con éxito; el campo 'activo' toma el valor por defecto true.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertDatabaseHas('proveedores', ['nombre' => 'Proveedor Rápido', 'activo' => 1]).
Observaciones: Campos marcados como 'nullable' en validación.
```

```
CASO DE PRUEBA: CP-038
Requisito: RF-028
Módulo: Proveedores
Nombre: Validación de formato de NIT con caracteres alfabéticos no permitidos
Objetivo: Rechazar entradas de NIT que contengan letras o caracteres fuera de números y guiones.
Prioridad: Media
Precondiciones: Administrador autenticado.
Datos de Prueba: nombre: "Proveedor Test", nit: "NIT-12345-ABC".
Pasos de Ejecución:
  1. Enviar POST /proveedores con NIT alfabético.
Resultado Esperado: Rechazo con error "El NIT solo puede contener números y guiones.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['nit']).
Observaciones: Regex ^[0-9\-]+$.
```

```
CASO DE PRUEBA: CP-039
Requisito: RF-028
Módulo: Proveedores
Nombre: Validación de longitud mínima de teléfono en proveedor
Objetivo: Exigir al menos 7 dígitos en el campo teléfono si es proporcionado.
Prioridad: Media
Precondiciones: Administrador autenticado.
Datos de Prueba: nombre: "Proveedor Test", telefono: "12345".
Pasos de Ejecución:
  1. Enviar POST /proveedores con teléfono de 5 dígitos.
Resultado Esperado: Rechazo con error de validación en campo 'telefono' por no alcanzar el mínimo de 7 caracteres.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['telefono']).
Observaciones: Regla 'min:7' en ProveedorController:L46.
```

```
CASO DE PRUEBA: CP-040
Requisito: RF-029
Módulo: Proveedores
Nombre: Auto-restauración de proveedor en papelera al intentar re-registrar su nombre
Objetivo: Restaurar el proveedor archivado cuando se ingresa su nombre exacto en el formulario de alta.
Prioridad: Alta
Precondiciones: Proveedor "Lácteos del Valle" en soft delete; Administrador autenticado.
Datos de Prueba: nombre: "Lácteos del Valle", telefono: "77889900".
Pasos de Ejecución:
  1. Enviar POST /proveedores con nombre coincidente con el archivado.
Resultado Esperado: Se restaura el registro (deleted_at = null), se actualiza su teléfono y redirige con mensaje de éxito indicando la restauración.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: ProveedorTest:test_validacion_duplicado_y_restauracion_desde_papelera.
Observaciones: Mantiene el mismo idProveedor previo.
```

```
CASO DE PRUEBA: CP-041
Requisito: RF-030
Módulo: Proveedores
Nombre: Edición de proveedor y modificación de estado activo/inactivo
Objetivo: Actualizar datos de un proveedor y desactivarlo operativamente.
Prioridad: Media
Precondiciones: Proveedor con ID 3 existente; Administrador autenticado.
Datos de Prueba: PUT /proveedores/3 con 'activo' no enviado (false) y nueva dirección.
Pasos de Ejecución:
  1. Enviar PUT a /proveedores/3 sin marcar el checkbox de activo.
Resultado Esperado: Se actualizan los datos y la columna 'activo' cambia a 0 (false).
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertDatabaseHas('proveedores', ['idProveedor' => 3, 'activo' => 0]).
Observaciones: Cast booleano verificado en modelo Proveedor.
```

```
CASO DE PRUEBA: CP-042
Requisito: RF-031
Módulo: Proveedores
Nombre: Bloqueo de eliminación de proveedor con productos vinculados en catálogo
Objetivo: Proteger la relación proveedor-producto impidiendo la eliminación si hay catálogo asociado.
Prioridad: Alta
Precondiciones: Proveedor vinculado a al menos 1 producto activo; Administrador autenticado.
Datos de Prueba: DELETE /proveedores/{id}.
Pasos de Ejecución:
  1. Enviar DELETE al proveedor con productos vinculados.
Resultado Esperado: Operación rechazada con mensaje de error explicativo en sesión; el proveedor permanece activo (deleted_at = null).
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: ProveedorTest:test_no_se_puede_eliminar_proveedor_con_productos_asociados.
Observaciones: Validación manual en controlador antes de ejecutar delete().
```

```
CASO DE PRUEBA: CP-043
Requisito: RF-031 / RNF-008
Módulo: Proveedores
Nombre: Eliminación lógica exitosa de proveedor sin productos asociados
Objetivo: Aplicar soft delete sobre proveedor sin dependencias activas.
Prioridad: Media
Precondiciones: Proveedor sin productos asignados; Administrador autenticado.
Datos de Prueba: DELETE /proveedores/{id}.
Pasos de Ejecución:
  1. Enviar DELETE al proveedor huérfano.
Resultado Esperado: Soft delete aplicado con éxito (deleted_at != null) y mensaje de confirmación.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSoftDeleted('proveedores', ['idProveedor' => $id]).
Observaciones: ProveedorTest:test_proveedor_sin_productos_se_elimina_correctamente.
```

---

### Módulo 6: Catálogo de Productos e Inventario

```
CASO DE PRUEBA: CP-044
Requisito: RF-032 / RNF-007
Módulo: Productos
Nombre: Paginación de catálogo de productos a 15 filas con relaciones precargadas
Objetivo: Verificar la visualización dividida en páginas y la carga ansiosa de categoría y proveedor.
Prioridad: Media
Precondiciones: 20 productos registrados; usuario autenticado.
Datos de Prueba: GET /productos.
Pasos de Ejecución:
  1. Navegar a /productos.
  2. Verificar los elementos de la tabla.
Resultado Esperado: La primera página contiene 15 productos, muestra su categoría, proveedor y stock, e incluye enlaces de paginación.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: PaginacionYDashboardTest verificado.
Observaciones: Utiliza with(['categoria', 'proveedor'])->paginate(15).
```

```
CASO DE PRUEBA: CP-045
Requisito: RF-033 / RNF-010
Módulo: Productos
Nombre: Registro exitoso de producto con datos válidos y archivo de imagen
Objetivo: Crear un producto completo y verificar la persistencia y copia de la imagen en public/storage.
Prioridad: Alta
Precondiciones: Categoría válida ID 1; Proveedor válido ID 1; Administrador autenticado.
Datos de Prueba: nombre: "Aceite de Girasol 1L", precio: 18.50, stock: 40, idCategoria: 1, idProveedor: 1, imagen: archivo PNG válido de 300 KB.
Pasos de Ejecución:
  1. Enviar POST /productos con los datos indicados y el archivo subido.
Resultado Esperado: Producto persistido con éxito, ruta guardada en columna 'imagen', archivo copiado a public/storage/productos/ y redirección a /productos con mensaje de éxito.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertDatabaseHas('productos', ['nombre' => 'Aceite de Girasol 1L']) y verificación de existencia en disco.
Observaciones: Valida trait SincronizaImagenPublica.
```

```
CASO DE PRUEBA: CP-046
Requisito: RF-033
Módulo: Productos
Nombre: Intento de registrar producto con precio negativo
Objetivo: Asegurar que la capa de validación rechace precios menores a cero.
Prioridad: Alta
Precondiciones: Administrador autenticado.
Datos de Prueba: nombre: "Producto Error Precio", precio: -15.00, stock: 10, idCategoria: 1.
Pasos de Ejecución:
  1. Enviar POST /productos con precio negativo.
Resultado Esperado: Rechazo con error "El precio no puede ser negativo.". No se inserta el registro.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['precio']).
Observaciones: Regla 'min:0' en ProductoController:L48.
```

```
CASO DE PRUEBA: CP-047
Requisito: RF-033 / RNF-006
Módulo: Productos
Nombre: Intento de registrar producto con stock negativo
Objetivo: Prohibir la creación de productos con inventario negativo desde la aplicación.
Prioridad: Alta
Precondiciones: Administrador autenticado.
Datos de Prueba: nombre: "Producto Error Stock", precio: 10.00, stock: -5, idCategoria: 1.
Pasos de Ejecución:
  1. Enviar POST /productos con stock negativo.
Resultado Esperado: Rechazo con error "El stock no puede ser negativo.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['stock']).
Observaciones: Primera línea de defensa previa al CHECK de motor de base de datos.
```

```
CASO DE PRUEBA: CP-048
Requisito: RF-033
Módulo: Productos
Nombre: Validación de precisión de precio con más de dos decimales
Objetivo: Garantizar que los precios no excedan dos cifras decimales.
Prioridad: Media
Precondiciones: Administrador autenticado.
Datos de Prueba: nombre: "Producto 3 Decimales", precio: "12.345", stock: 10, idCategoria: 1.
Pasos de Ejecución:
  1. Enviar POST /productos con precio con 3 decimales.
Resultado Esperado: Rechazo con error "El precio solo puede tener hasta 2 decimales.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['precio']).
Observaciones: Regex ^\d+(\.\d{1,2})?$.
```

```
CASO DE PRUEBA: CP-049
Requisito: RF-033
Módulo: Productos
Nombre: Intento de registrar producto con categoría inexistente en base de datos
Objetivo: Proteger la clave foránea idCategoria verificando su existencia previa.
Prioridad: Alta
Precondiciones: Categoría con ID 999999 no existe en base de datos; Administrador autenticado.
Datos de Prueba: nombre: "Producto Sin Categoría", precio: 10.00, stock: 5, idCategoria: 999999.
Pasos de Ejecución:
  1. Enviar POST /productos con idCategoria inexistente.
Resultado Esperado: Rechazo con error "La categoría seleccionada no existe o no es válida.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['idCategoria']).
Observaciones: Regla exists:categorias,idCategoria.
```

```
CASO DE PRUEBA: CP-050
Requisito: RF-033
Módulo: Productos
Nombre: Carga de archivo no admitido en campo imagen de producto
Objetivo: Rechazar archivos ejecutables, scripts o documentos que no correspondan a imágenes permitidas.
Prioridad: Media
Precondiciones: Administrador autenticado.
Datos de Prueba: Archivo 'documento.pdf' o 'script.exe' cargado en campo 'imagen'.
Pasos de Ejecución:
  1. Enviar POST /productos con archivo PDF adjunto en 'imagen'.
Resultado Esperado: Rechazo con errores: "El archivo debe ser una imagen." y "La imagen debe ser de tipo JPG, JPEG, PNG o WEBP.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['imagen']).
Observaciones: Regla 'image|mimes:jpg,jpeg,png,webp'.
```

```
CASO DE PRUEBA: CP-051
Requisito: RF-033
Módulo: Productos
Nombre: Carga de imagen con tamaño superior al límite máximo de 2048 KB
Objetivo: Impedir la saturación de almacenamiento mediante imágenes excesivamente pesadas.
Prioridad: Media
Precondiciones: Archivo de imagen válido de 3.5 MB; Administrador autenticado.
Datos de Prueba: imagen: archivo JPG de 3500 KB.
Pasos de Ejecución:
  1. Enviar POST /productos con la imagen de 3.5 MB.
Resultado Esperado: Rechazo con mensaje de validación "La imagen no debe superar 2MB.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['imagen']).
Observaciones: Regla 'max:2048'.
```

```
CASO DE PRUEBA: CP-052
Requisito: RF-034
Módulo: Productos
Nombre: Intento de registrar producto con nombre activo ya existente
Objetivo: Prevenir duplicaciones de catálogo activo por error humano.
Prioridad: Alta
Precondiciones: Producto "Coca Cola 2L" activo en base de datos; Administrador autenticado.
Datos de Prueba: nombre: "Coca Cola 2L", precio: 15.00, stock: 20, idCategoria: 1.
Pasos de Ejecución:
  1. Enviar POST /productos con nombre duplicado.
Resultado Esperado: Rechazo con error "Ya existe un producto activo con este nombre.". No se duplica el producto.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['nombre']).
Observaciones: Verificado además por constraint UNIQUE en migración add_unique_constraint_to_productos_nombre.
```

```
CASO DE PRUEBA: CP-053
Requisito: RF-034
Módulo: Productos
Nombre: Auto-restauración y actualización de producto desde papelera
Objetivo: Recuperar el producto archivado si se vuelve a crear con el mismo nombre, actualizando sus valores.
Prioridad: Alta
Precondiciones: Producto "Galletas Soda" en soft delete; Administrador autenticado.
Datos de Prueba: nombre: "Galletas Soda", precio: 20.00, stock: 50, idCategoria: 1.
Pasos de Ejecución:
  1. Enviar POST /productos con nombre "Galletas Soda".
Resultado Esperado: El producto es restaurado (deleted_at = null), su precio y stock se actualizan a 20.00 y 50, y redirige con mensaje indicando la restauración exitosa.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertDatabaseHas('productos', ['nombre' => 'Galletas Soda', 'deleted_at' => null, 'precio' => 20.00]).
Observaciones: ProductoController:L82-100.
```

```
CASO DE PRUEBA: CP-054
Requisito: RF-035 / RNF-010
Módulo: Productos
Nombre: Sustitución física de imagen al actualizar producto
Objetivo: Verificar que al subir una nueva imagen se elimine la anterior de los dos directorios locales.
Prioridad: Media
Precondiciones: Producto con imagen previa guardada; Administrador autenticado.
Datos de Prueba: PUT /productos/{id} con archivo de imagen nuevo.
Pasos de Ejecución:
  1. Enviar PUT al producto adjuntando una nueva imagen.
Resultado Esperado: El archivo anterior desaparece de storage/app/public y public/storage; el nuevo archivo queda almacenado y sincronizado.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: Verificación de no existencia del archivo antiguo en disco con Storage::disk('public')->exists().
Observaciones: Evita acumulación de archivos basura.
```

```
CASO DE PRUEBA: CP-055
Requisito: RF-036 / RNF-008
Módulo: Productos
Nombre: Baja lógica de producto (envío a papelera)
Objetivo: Comprobar que el borrado inicial de producto sea un soft delete y no una destrucción física.
Prioridad: Alta
Precondiciones: Producto activo con ID 10; Administrador autenticado.
Datos de Prueba: DELETE /productos/10.
Pasos de Ejecución:
  1. Enviar DELETE a /productos/10.
Resultado Esperado: El producto recibe deleted_at con la fecha actual, desaparece de la vista /productos y se muestra mensaje "Producto enviado a la papelera.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSoftDeleted('productos', ['idProducto' => 10]).
Observaciones: ProductoController::destroy.
```

```
CASO DE PRUEBA: CP-056
Requisito: RF-037
Módulo: Productos
Nombre: Restauración manual de producto desde vista de papelera
Objetivo: Reactivar un producto eliminado desde la interfaz de papelera.
Prioridad: Alta
Precondiciones: Producto con ID 12 en papelera; Administrador autenticado.
Datos de Prueba: PUT /productos/12/restore.
Pasos de Ejecución:
  1. Navegar a /productos/papelera.
  2. Emitir PUT a /productos/12/restore.
Resultado Esperado: El producto recupera deleted_at = null, desaparece de la papelera, reaparece en el catálogo activo y redirige con mensaje de éxito.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertDatabaseHas('productos', ['idProducto' => 12, 'deleted_at' => null]).
Observaciones: ProductoController::restore.
```

```
CASO DE PRUEBA: CP-057
Requisito: RF-038
Módulo: Productos
Nombre: Bloqueo de eliminación definitiva de producto con ventas históricas
Objetivo: Proteger el historial de ventas impidiendo el borrado físico de productos ya vendidos.
Prioridad: Alta
Precondiciones: Producto en papelera que posee al menos un registro vinculado en tabla 'detalle_pedidos'; Administrador autenticado.
Datos de Prueba: DELETE /productos/{id}/force-delete.
Pasos de Ejecución:
  1. Enviar DELETE a /productos/{id}/force-delete.
Resultado Esperado: La eliminación definitiva se bloquea, el registro permanece en papelera y redirige con error "No se puede eliminar permanentemente... porque tiene ventas registradas en el historial.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertDatabaseHas('productos', ['idProducto' => $id]) y comprobación de error flash en sesión.
Observaciones: Regla de auditoría crítica en ProductoController:L234-237.
```

```
CASO DE PRUEBA: CP-058
Requisito: RF-038
Módulo: Productos
Nombre: Eliminación definitiva exitosa de producto sin historial de ventas
Objetivo: Permitir el borrado permanente físico de un producto en papelera que nunca fue vendido.
Prioridad: Alta
Precondiciones: Producto en papelera con 0 registros en 'detalle_pedidos'; Administrador autenticado.
Datos de Prueba: DELETE /productos/{id}/force-delete.
Pasos de Ejecución:
  1. Enviar DELETE de fuerza a producto sin ventas.
Resultado Esperado: El registro es completamente eliminado de la tabla 'productos' (forceDelete), su imagen es destruida del storage y se muestra mensaje de éxito.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertDatabaseMissing('productos', ['idProducto' => $id]).
Observaciones: Limpieza permanente controlada.
```

```
CASO DE PRUEBA: CP-059
Requisito: RF-039 / RF-041
Módulo: Productos
Nombre: Generación y descarga de código QR en formato PNG
Objetivo: Validar la descarga del QR con JSON estructurado conteniendo SKU formateado, nombre y precio.
Prioridad: Media
Precondiciones: Producto existente con ID 42 ("Chocolate"); usuario autenticado.
Datos de Prueba: GET /productos/42/qr.
Pasos de Ejecución:
  1. Solicitar GET /productos/42/qr.
Resultado Esperado: Respuesta HTTP 200, cabecera Content-Type 'image/png', cabecera Content-Disposition con nombre 'qr_PRD-000042.png' y contenido binario de imagen PNG válido.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: SmokeVistasModificadasTest y PedidoInvitadoYDescargasTest verificados.
Observaciones: Librería endroid/qr-code.
```

```
CASO DE PRUEBA: CP-060
Requisito: RF-040 / RF-041
Módulo: Productos
Nombre: Generación y descarga de código de barras Code128 en formato PNG
Objetivo: Validar la descarga de código de barras generado sobre la cadena estándar del SKU.
Prioridad: Media
Precondiciones: Producto existente con ID 7; usuario autenticado.
Datos de Prueba: GET /productos/7/barcode.
Pasos de Ejecución:
  1. Solicitar GET /productos/7/barcode.
Resultado Esperado: Respuesta HTTP 200, Content-Type 'image/png', nombre de archivo 'barcode_PRD-000007.png' y contenido binario representativo del código de barras.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: Verificado en suite existente tests/Feature/PedidoInvitadoYDescargasTest.php.
Observaciones: Librería picqer/php-barcode-generator.
```

```
CASO DE PRUEBA: CP-061
Requisito: RF-041
Módulo: Productos
Nombre: Verificación del formateador determinista de SKU (Helper Sku)
Objetivo: Probar que Sku::format rellene a 6 dígitos con prefijo 'PRD-' y no trunque IDs mayores.
Prioridad: Media
Precondiciones: Clase App\Support\Sku disponible.
Datos de Prueba: IDs: 1, 42, 1234567.
Pasos de Ejecución:
  1. Ejecutar Sku::format(1).
  2. Ejecutar Sku::format(42).
  3. Ejecutar Sku::format(1234567).
Resultado Esperado: Retorna exactamente: 'PRD-000001', 'PRD-000042', 'PRD-1234567'.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: SkuTest existente en tests/Unit/SkuTest.php.
Observaciones: Prueba Unitaria aislada.
```

```
CASO DE PRUEBA: CP-062
Requisito: RF-042
Módulo: Productos
Nombre: Exportación de catálogo de productos a PDF en orientación apaisada
Objetivo: Comprobar la generación del documento PDF con métricas y agrupación por categoría.
Prioridad: Media
Precondiciones: Catálogo con productos en diferentes estados de stock; usuario autenticado.
Datos de Prueba: GET /productos-pdf.
Pasos de Ejecución:
  1. Solicitar GET /productos-pdf.
Resultado Esperado: Respuesta HTTP 200, Content-Type 'application/pdf', archivo nombrado 'productos_YYYYMMDD.pdf' y vista productos.pdf renderizada con resumen de valuación y agrupación por categoría.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertOk() y validación de cabeceras en PHPUnit.
Observaciones: Librería barryvdh/laravel-dompdf.
```

```
CASO DE PRUEBA: CP-063
Requisito: RF-043
Módulo: Productos
Nombre: Exportación de catálogo de productos a hoja de cálculo Excel
Objetivo: Validar la descarga del archivo Excel estructurado con datos del catálogo.
Prioridad: Media
Precondiciones: Productos registrados; usuario autenticado.
Datos de Prueba: GET /productos-excel.
Pasos de Ejecución:
  1. Solicitar GET /productos-excel.
Resultado Esperado: Descarga de archivo binario con extensión '.xlsx' generado mediante ProductosExport.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: Excel::fake() o descarga binaria en test de integración.
Observaciones: Librería maatwebsite/excel.
```

---

### Módulo 7: Gestión de Clientes

```
CASO DE PRUEBA: CP-064
Requisito: RF-044
Módulo: Clientes
Nombre: Búsqueda dinámica de clientes por nombre o correo electrónico
Objetivo: Verificar el filtrado correcto con parámetro 'q' sobre campos nombre y email.
Prioridad: Media
Precondiciones: Cliente A: "María Pérez" / "maria@test.com"; Cliente B: "Juan Gómez" / "juan@test.com".
Datos de Prueba: GET /clientes?q=maria y GET /clientes?q=juan@test.com.
Pasos de Ejecución:
  1. Enviar GET /clientes?q=maria.
  2. Enviar GET /clientes?q=juan@test.com.
Resultado Esperado: Ambas búsquedas retornan exactamente 1 cliente coincidente. Sin parámetro 'q' retorna ambos clientes.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: ClienteBusquedaTest en tests/Feature/ClienteBusquedaTest.php.
Observaciones: Paginado a 15 clientes.
```

```
CASO DE PRUEBA: CP-065
Requisito: RF-045
Módulo: Clientes
Nombre: Registro exitoso de nuevo cliente con datos válidos
Objetivo: Dar de alta un cliente con nombre, email, teléfono y dirección conformes a las reglas.
Prioridad: Media
Precondiciones: Administrador autenticado; correo 'carlos@empresa.test' disponible.
Datos de Prueba: nombre: "Carlos Mendoza", email: "carlos@empresa.test", telefono: "71234567", direccion: "Av. Heroínas 320".
Pasos de Ejecución:
  1. Enviar POST /clientes con los datos de prueba.
Resultado Esperado: Redirección con HTTP 302, mensaje de éxito en sesión y registro persistido en tabla 'clientes'.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertDatabaseHas('clientes', ['email' => 'carlos@empresa.test']).
Observaciones: ClienteController::store.
```

```
CASO DE PRUEBA: CP-066
Requisito: RF-045
Módulo: Clientes
Nombre: Validación de campos obligatorios vacíos al registrar cliente
Objetivo: Impedir el registro de clientes sin nombre o sin correo electrónico.
Prioridad: Media
Precondiciones: Administrador autenticado.
Datos de Prueba: nombre: "", email: "".
Pasos de Ejecución:
  1. Enviar POST /clientes con campos vacíos.
Resultado Esperado: Rechazo con HTTP 302 y errores: "El nombre es obligatorio." y "El correo es obligatorio.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['nombre', 'email']).
Observaciones: Reglas required en ClienteController:L42,46.
```

```
CASO DE PRUEBA: CP-067
Requisito: RF-045
Módulo: Clientes
Nombre: Validación de formato sintáctico inválido en correo de cliente
Objetivo: Rechazar direcciones de correo sin estructura estándar de email.
Prioridad: Media
Precondiciones: Administrador autenticado.
Datos de Prueba: nombre: "Cliente Inválido", email: "correo_sin_dominio".
Pasos de Ejecución:
  1. Enviar POST /clientes con email no válido.
Resultado Esperado: Rechazo con error "Ingrese un correo válido.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['email']).
Observaciones: Regla 'email' en validación.
```

```
CASO DE PRUEBA: CP-068
Requisito: RF-046
Módulo: Clientes
Nombre: Intento de registrar cliente con correo ya activo en el sistema
Objetivo: Prevenir duplicaciones de cuentas de clientes con el mismo email.
Prioridad: Alta
Precondiciones: Cliente existente con email 'juan@test.com' activo; Administrador autenticado.
Datos de Prueba: nombre: "Juan Duplicado", email: "juan@test.com".
Pasos de Ejecución:
  1. Enviar POST /clientes con el correo ya utilizado.
Resultado Esperado: Rechazo con error "Este correo ya está registrado por un cliente activo.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['email']).
Observaciones: ClienteController:L73.
```

```
CASO DE PRUEBA: CP-069
Requisito: RF-046
Módulo: Clientes
Nombre: Auto-restauración de cliente en papelera al registrar su correo electrónico
Objetivo: Recuperar automáticamente el cliente previo archivado en lugar de duplicar o arrojar error.
Prioridad: Alta
Precondiciones: Cliente con email 'archivado@test.com' en soft delete; Administrador autenticado.
Datos de Prueba: nombre: "Cliente Recuperado", email: "archivado@test.com", telefono: "77665544".
Pasos de Ejecución:
  1. Enviar POST /clientes con el correo archivado.
Resultado Esperado: El cliente es restaurado (deleted_at = null), su nombre y teléfono se actualizan y se muestra mensaje indicando que se encontraba en la papelera y fue restaurado.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertDatabaseHas('clientes', ['email' => 'archivado@test.com', 'deleted_at' => null]).
Observaciones: ClienteController:L63-71.
```

```
CASO DE PRUEBA: CP-070
Requisito: RF-047
Módulo: Clientes
Nombre: Edición de cliente validando correo propio vs ajeno
Objetivo: Permitir actualizar el cliente manteniendo su propio correo sin generar conflicto de duplicado.
Prioridad: Media
Precondiciones: Cliente ID 4 existente; Administrador autenticado.
Datos de Prueba: PUT /clientes/4 con el mismo email y nuevo teléfono.
Pasos de Ejecución:
  1. Enviar PUT a /clientes/4.
Resultado Esperado: Actualización exitosa en base de datos.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertDatabaseHas('clientes', ['idCliente' => 4, 'telefono' => '77665544']).
Observaciones: Valida 'idCliente != cliente->idCliente' en ClienteController:L119.
```

```
CASO DE PRUEBA: CP-071
Requisito: RF-048
Módulo: Clientes
Nombre: Bloqueo de eliminación de cliente con pedidos asociados en el historial
Objetivo: Proteger el historial de transacciones impidiendo borrar clientes con compras previas.
Prioridad: Alta
Precondiciones: Cliente con al menos 1 pedido registrado en tabla 'pedidos'; Administrador autenticado.
Datos de Prueba: DELETE /clientes/{id}.
Pasos de Ejecución:
  1. Enviar DELETE al cliente con pedidos.
Resultado Esperado: La eliminación se bloquea, no se marca soft delete y redirige con mensaje de error "No se puede eliminar el cliente... porque tiene X pedido(s) asociado(s).".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertDatabaseHas('clientes', ['idCliente' => $id, 'deleted_at' => null]).
Observaciones: Reforzado por FK restrictiva pedidos.idCliente -> clientes.idCliente.
```

```
CASO DE PRUEBA: CP-072
Requisito: RF-048 / RNF-008
Módulo: Clientes
Nombre: Eliminación lógica exitosa de cliente sin pedidos registrados
Objetivo: Comprobar el soft delete de clientes que no poseen historial de compra.
Prioridad: Media
Precondiciones: Cliente sin pedidos vinculados; Administrador autenticado.
Datos de Prueba: DELETE /clientes/{id}.
Pasos de Ejecución:
  1. Enviar DELETE al cliente sin pedidos.
Resultado Esperado: El cliente es marcado con deleted_at != null, desaparece del listado normal y se muestra mensaje de éxito.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSoftDeleted('clientes', ['idCliente' => $id]).
Observaciones: ClienteController::destroy.
```

---

### Módulo 8: Registro de Pedidos y Ventas (POS)

```
CASO DE PRUEBA: CP-073
Requisito: RF-049
Módulo: Pedidos y Ventas
Nombre: Listado histórico de pedidos ordenados por fecha con carga ansiosa
Objetivo: Verificar la vista principal de pedidos con datos de cliente, usuario registrador y detalles.
Prioridad: Media
Precondiciones: Pedidos registrados en base de datos; usuario autenticado.
Datos de Prueba: GET /pedidos.
Pasos de Ejecución:
  1. Navegar a /pedidos.
Resultado Esperado: La vista lista los pedidos paginados a 15 filas, ordenados descendentemente por fecha, mostrando total, estado y usuario que atendió la venta.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: PedidoController::index verificado en PHPUnit.
Observaciones: with(['cliente', 'usuario', 'detalles.producto']).
```

```
CASO DE PRUEBA: CP-074
Requisito: RF-050
Módulo: Pedidos y Ventas
Nombre: Formulario de venta muestra exclusivamente productos con stock disponible
Objetivo: Evitar que el cajero intente vender productos cuyo stock actual sea 0.
Prioridad: Media
Precondiciones: Producto A con stock 10; Producto B con stock 0; usuario autenticado.
Datos de Prueba: GET /pedidos/create.
Pasos de Ejecución:
  1. Acceder a GET /pedidos/create.
  2. Inspeccionar la colección $productos enviada a la vista.
Resultado Esperado: Producto A está presente en la lista de selección; Producto B está ausente.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertViewHas('productos') comprobando Producto::where('stock', '>', 0).
Observaciones: PedidoController:L31.
```

```
CASO DE PRUEBA: CP-075
Requisito: RF-051
Módulo: Pedidos y Ventas
Nombre: Rechazo de pedido con fecha futura en validación de formulario
Objetivo: Garantizar que no se registren ventas con fecha posterior a la actual.
Prioridad: Alta
Precondiciones: Usuario autenticado; cliente existente; producto disponible.
Datos de Prueba: fecha: "2099-12-31" (fecha futura).
Pasos de Ejecución:
  1. Enviar POST /pedidos con fecha futura.
Resultado Esperado: Rechazo con error "La fecha no puede ser futura.". No se crea el pedido.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['fecha']).
Observaciones: Regla 'before_or_equal:today' en StorePedidoRequest:L18.
```

```
CASO DE PRUEBA: CP-076
Requisito: RF-051
Módulo: Pedidos y Ventas
Nombre: Rechazo de pedido sin líneas de producto (array items vacío)
Objetivo: Prohibir la creación de ventas vacías con total cero y sin ítems.
Prioridad: Alta
Precondiciones: Usuario autenticado.
Datos de Prueba: idCliente: 1, fecha: fecha_hoy, items: [].
Pasos de Ejecución:
  1. Enviar POST /pedidos sin elementos en el array 'items'.
Resultado Esperado: Rechazo de validación con mensaje "Agregue al menos un producto al pedido.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['items']).
Observaciones: Regla 'required|array|min:1' en StorePedidoRequest:L19.
```

```
CASO DE PRUEBA: CP-077
Requisito: RF-051
Módulo: Pedidos y Ventas
Nombre: Rechazo de productos repetidos en líneas separadas del mismo pedido
Objetivo: Forzar que si un producto se repite, se sume en una sola línea en vez de generar múltiples filas del mismo ID.
Prioridad: Alta
Precondiciones: Producto 1 disponible; usuario autenticado.
Datos de Prueba: items: [[idProducto: 1, cantidad: 2], [idProducto: 1, cantidad: 3]].
Pasos de Ejecución:
  1. Enviar POST /pedidos repitiendo idProducto: 1.
Resultado Esperado: Rechazo de validación con mensaje "No repita el mismo producto en dos líneas; sume la cantidad en una sola.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors('items.1.idProducto').
Observaciones: Regla 'distinct' en StorePedidoRequest:L20.
```

```
CASO DE PRUEBA: CP-078
Requisito: RF-051
Módulo: Pedidos y Ventas
Nombre: Validación de cantidad fuera del rango permitido en pedido
Objetivo: Rechazar cantidades iguales a 0 o superiores a 9999 unidades por línea.
Prioridad: Media
Precondiciones: Usuario autenticado.
Datos de Prueba: cantidad: 0 y cantidad: 10000.
Pasos de Ejecución:
  1. Enviar POST /pedidos con cantidad 0.
  2. Enviar POST /pedidos con cantidad 10000.
Resultado Esperado: En el primer caso error "La cantidad debe ser al menos 1.". En el segundo caso "La cantidad no debe superar 9,999.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors('items.0.cantidad').
Observaciones: Reglas 'min:1|max:9999'.
```

```
CASO DE PRUEBA: CP-079
Requisito: RF-052 / RNF-004 / RNF-005
Módulo: Pedidos y Ventas
Nombre: Registro de venta simple con descuento atómico de stock y cálculo de total
Objetivo: Validar que un pedido descuente el stock exactamente y calcule bien el total en moneda fija.
Prioridad: Alta
Precondiciones: Producto con stock 10 y precio 25.00; Cliente activo; Usuario autenticado.
Datos de Prueba: idCliente: 1, items: [[idProducto: 1, cantidad: 3]].
Pasos de Ejecución:
  1. Enviar POST /pedidos con los datos indicados.
Resultado Esperado: Pedido creado con total '75.00', estado 'completado', 1 registro en detalle_pedidos (precio_unitario: 25.00, subtotal: 75.00) y stock del producto decrementado a 7 (10 - 3).
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: PedidoStockConsistencyTest:test_registrar_pedido_descuenta_stock_y_calcula_el_total.
Observaciones: Cálculo con bcmul y bcadd.
```

```
CASO DE PRUEBA: CP-080
Requisito: RF-052 / RNF-005
Módulo: Pedidos y Ventas
Nombre: Registro de venta multi-línea con múltiples productos y descuento sincronizado
Objetivo: Comprobar el descuento atómico de inventario en ventas con 3 o más productos distintos.
Prioridad: Alta
Precondiciones: Prod 1: stock 20, precio 10.00; Prod 2: stock 15, precio 5.50; Prod 3: stock 8, precio 50.00.
Datos de Prueba: items: [[idProd: 1, cant: 5], [idProd: 2, cant: 10], [idProd: 3, cant: 2]].
Pasos de Ejecución:
  1. Enviar POST /pedidos con las tres líneas.
Resultado Esperado: Pedido creado con total = 5*10 + 10*5.50 + 2*50 = 50 + 55 + 100 = '205.00'. Stock resultante: Prod 1 = 15, Prod 2 = 5, Prod 3 = 6.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: Aserciones de stock y totales en PHPUnit.
Observaciones: Ejecución atómica dentro de DB::transaction.
```

```
CASO DE PRUEBA: CP-081
Requisito: RF-052 / RNF-005
Módulo: Pedidos y Ventas
Nombre: Venta en el límite exacto del stock disponible (stock resultante = 0)
Objetivo: Validar que se permita vender hasta la última unidad disponible sin arrojar error.
Prioridad: Alta
Precondiciones: Producto con stock 5 y precio 12.00; Cliente activo.
Datos de Prueba: items: [[idProducto: 1, cantidad: 5]].
Pasos de Ejecución:
  1. Enviar POST /pedidos pidiendo exactamente las 5 unidades.
Resultado Esperado: Venta exitosa, total '60.00', stock resultante = 0 (producto queda agotado pero no en negativo).
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSame(0, $producto->fresh()->stock) en PHPUnit.
Observaciones: Cumple con la restricción CHECK (stock >= 0).
```

```
CASO DE PRUEBA: CP-082
Requisito: RF-053
Módulo: Pedidos y Ventas
Nombre: Rechazo de venta por insuficiencia de stock por 1 unidad (análisis de valor límite)
Objetivo: Probar que si el stock disponible es N, pedir N+1 sea rechazado de forma segura.
Prioridad: Alta
Precondiciones: Producto con stock 4; Cliente activo.
Datos de Prueba: items: [[idProducto: 1, cantidad: 5]].
Pasos de Ejecución:
  1. Enviar POST /pedidos solicitando 5 unidades (1 más de lo existente).
Resultado Esperado: Transacción rechazada con ValidationException en 'items': "Stock insuficiente para \"...\" (disponible: 4).". El stock se mantiene intacto en 4 y no se crea ningún pedido.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: PedidoStockConsistencyTest:test_no_se_puede_pedir_mas_stock_del_disponible.
Observaciones: BVA en frontera superior de stock.
```

```
CASO DE PRUEBA: CP-083
Requisito: RF-053 / RNF-005
Módulo: Pedidos y Ventas
Nombre: Atomicidad y Rollback completo en venta multi-línea ante fallo de un ítem
Objetivo: Verificar que si el tercer ítem de un pedido falla por stock, los dos primeros no sufran descuento.
Prioridad: Alta
Precondiciones: Prod 1: stock 10 (pide 2); Prod 2: stock 8 (pide 3); Prod 3: stock 1 (pide 5 -> falla).
Datos de Prueba: items con Prod 1, Prod 2 y Prod 3 en la misma solicitud.
Pasos de Ejecución:
  1. Enviar POST /pedidos con los tres productos.
Resultado Esperado: Excepción por falta de stock en Prod 3. Rollback absoluto: Prod 1 conserva 10 unidades, Prod 2 conserva 8 unidades, Prod 3 conserva 1 unidad. Cero pedidos creados.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSame(10, $p1->stock), assertSame(8, $p2->stock), assertSame(0, Pedido::count()).
Observaciones: Principio de atomicidad en bases de datos relacionales.
```

```
CASO DE PRUEBA: CP-084
Requisito: RF-052 / RNF-004
Módulo: Pedidos y Ventas
Nombre: Preservación histórica de precios y cálculo monetario con bcmath
Objetivo: Comprobar que cambios posteriores de precio en el catálogo no alteren ventas pasadas.
Prioridad: Alta
Precondiciones: Venta registrada con producto a precio 30.00.
Datos de Prueba: Modificación del precio del producto en catálogo a 45.00.
Pasos de Ejecución:
  1. Registrar venta con precio original 30.00.
  2. Como administrador, actualizar el precio del producto en /productos/{id} a 45.00.
  3. Consultar el pedido previo en base de datos.
Resultado Esperado: El detalle del pedido previo mantiene 'precio_unitario' = 30.00 y su subtotal no sufre ninguna modificación.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: Consulta directa a detalle_pedidos y comparación con productos.precio.
Observaciones: Decisión de diseño documentada en BASE_DE_DATOS.md sección 2.2.
```

```
CASO DE PRUEBA: CP-085
Requisito: RF-054
Módulo: Pedidos y Ventas
Nombre: Transición de pedido de 'completado' a 'anulado' y restitución de stock
Objetivo: Validar que anular un pedido devuelva las unidades vendidas al inventario y cambie el estado.
Prioridad: Alta
Precondiciones: Pedido completado de 5 unidades; stock del producto quedó en 15 (inicial 20); Administrador autenticado.
Datos de Prueba: PUT /pedidos/{id} con estado: 'anulado'.
Pasos de Ejecución:
  1. Enviar PUT a /pedidos/{id} cambiando estado a 'anulado'.
Resultado Esperado: Redirección con éxito, estado del pedido cambia a 'anulado' y el stock del producto se restituye a 20 (15 + 5).
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: PedidoAnulacionStockTest:test_completado_a_anulado_devuelve_stock_y_no_lo_vuelve_a_descontar.
Observaciones: PedidoController:L83-88.
```

```
CASO DE PRUEBA: CP-086
Requisito: RF-055
Módulo: Pedidos y Ventas
Nombre: Transición de pedido de 'anulado' a 'completado' con nuevo descuento de inventario
Objetivo: Reactivar un pedido anulado verificando disponibilidad y descontando stock nuevamente.
Prioridad: Alta
Precondiciones: Pedido en estado 'anulado' con 5 unidades; stock actual en 20; Administrador autenticado.
Datos de Prueba: PUT /pedidos/{id} con estado: 'completado'.
Pasos de Ejecución:
  1. Enviar PUT modificando estado a 'completado'.
Resultado Esperado: Se valida el stock disponible, se descuentan las 5 unidades (stock pasa de 20 a 15) y el estado cambia a 'completado'.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: PedidoAnulacionStockTest:test_anulado_a_completado_reactiva_y_descuenta_stock.
Observaciones: Transición reactiva validada.
```

```
CASO DE PRUEBA: CP-087
Requisito: RF-056
Módulo: Pedidos y Ventas
Nombre: Idempotencia de stock en modificación de pedido anulado hacia anulado
Objetivo: Asegurar que guardar cambios en un pedido que ya está anulado no toque el inventario.
Prioridad: Alta
Precondiciones: Pedido nacido en estado 'anulado'; stock del producto en 20; Administrador autenticado.
Datos de Prueba: PUT /pedidos/{id} manteniendo estado: 'anulado' con cambio de fecha.
Pasos de Ejecución:
  1. Enviar PUT guardando el pedido como anulado.
Resultado Esperado: El stock permanece exactamente en 20 (ni se suma ni se resta).
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: PedidoAnulacionStockTest:test_anulado_a_anulado_no_toca_el_stock.
Observaciones: Previene manipulación indebida de inventario.
```

```
CASO DE PRUEBA: CP-088
Requisito: RF-057
Módulo: Pedidos y Ventas
Nombre: Anulación exitosa de pedido completado cuyo stock general llegó a cero
Objetivo: Permitir anular un pedido aunque el inventario general se haya agotado por otras ventas.
Prioridad: Alta
Precondiciones: Pedido completado de 5 unidades; producto ve reducido su stock a 0 por otras ventas posteriores; Administrador autenticado.
Datos de Prueba: PUT /pedidos/{id} con estado: 'anulado'.
Pasos de Ejecución:
  1. Anular el pedido que tenía reservadas 5 unidades.
Resultado Esperado: El pedido se anula con éxito sin arrojar error de "stock insuficiente", y devuelve las 5 unidades que tenía reservadas (stock pasa de 0 a 5).
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: PedidoAnulacionStockTest:test_anular_no_valida_stock_disponible.
Observaciones: PedidoController usa $descontarStock = false al anular.
```

```
CASO DE PRUEBA: CP-089
Requisito: RF-058
Módulo: Pedidos y Ventas
Nombre: Eliminación de pedido completado restituye el stock al inventario
Objetivo: Verificar que borrar un pedido activo devuelva las unidades vendidas antes de marcar soft delete.
Prioridad: Alta
Precondiciones: Pedido completado de 3 unidades; stock actual en 7; Administrador autenticado.
Datos de Prueba: DELETE /pedidos/{id}.
Pasos de Ejecución:
  1. Enviar DELETE a /pedidos/{id}.
Resultado Esperado: El pedido recibe soft delete, y el stock del producto incrementa a 10 (7 + 3).
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: PedidoEliminarAnuladoTest:test_eliminar_pedido_completado_restaura_stock.
Observaciones: PedidoController:L119-124.
```

```
CASO DE PRUEBA: CP-090
Requisito: RF-059
Módulo: Pedidos y Ventas
Nombre: Prevención de stock fantasma al eliminar un pedido previamente anulado
Objetivo: Garantizar que eliminar un pedido que ya estaba anulado NO vuelva a incrementar el inventario.
Prioridad: Alta
Precondiciones: Pedido en estado 'anulado' (cuyo stock ya fue devuelto previamente); stock en 10; Administrador autenticado.
Datos de Prueba: DELETE /pedidos/{id}.
Pasos de Ejecución:
  1. Enviar DELETE al pedido anulado.
Resultado Esperado: El pedido se elimina con soft delete, pero el stock del producto permanece en 10 (NO aumenta a 13).
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: PedidoEliminarAnuladoTest:test_eliminar_pedido_anulado_no_crea_stock_fantasma.
Observaciones: Regresión crítica verificada en tests/Feature/.
```

---

### Módulo 9: Reportes de Ventas

```
CASO DE PRUEBA: CP-091
Requisito: RF-060
Módulo: Reportes de Ventas
Nombre: Validación de coherencia de fechas en filtros de reporte (fecha_fin >= fecha_inicio)
Objetivo: Rechazar intervalos temporales invertidos donde la fecha fin sea anterior a la fecha inicio.
Prioridad: Media
Precondiciones: Administrador autenticado.
Datos de Prueba: fecha_inicio: "2026-09-20", fecha_fin: "2026-09-10".
Pasos de Ejecución:
  1. Enviar GET /reportes/ventas con fecha_fin anterior a fecha_inicio.
Resultado Esperado: Rechazo con error "La fecha final debe ser igual o posterior a la fecha inicial.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['fecha_fin']).
Observaciones: Regla 'after_or_equal:fecha_inicio' en ReporteController:L18.
```

```
CASO DE PRUEBA: CP-092
Requisito: RF-061
Módulo: Reportes de Ventas
Nombre: Filtrado múltiple combinado de ventas por fechas, cliente y estado
Objetivo: Validar la aplicación conjunta de filtros de consulta en la tabla de reportes.
Prioridad: Media
Precondiciones: Pedidos registrados con diferentes clientes y fechas; Administrador autenticado.
Datos de Prueba: fecha_inicio: "2026-01-01", fecha_fin: "2026-12-31", id_cliente: 2, estado: "completado".
Pasos de Ejecución:
  1. Solicitar GET /reportes/ventas con los parámetros indicados.
Resultado Esperado: La tabla y las métricas muestran exclusivamente los pedidos que cumplen simultáneamente todos los criterios.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: ReporteVentasMetricasTest en tests/Feature/.
Observaciones: Query dinámico encadenado con whereBetween, where('idCliente') y where('estado').
```

```
CASO DE PRUEBA: CP-093
Requisito: RF-062
Módulo: Reportes de Ventas
Nombre: Cálculo exacto de métricas agregadas globales sobre el universo total filtrado
Objetivo: Confirmar que las tarjetas de métricas (total ventas, unidades, pedidos) no se recorten por la paginación a 15 filas.
Prioridad: Alta
Precondiciones: 18 pedidos completados de 10.00 cada uno (total ventas real: 180.00); Administrador autenticado.
Datos de Prueba: GET /reportes/ventas.
Pasos de Ejecución:
  1. Cargar /reportes/ventas.
  2. Verificar la variable 'totalVentas' en la vista.
Resultado Esperado: La tabla visual pagina a 15 filas, pero la tarjeta de métrica 'totalVentas' refleja 180.00 (el total completo de los 18 pedidos).
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: PaginacionYDashboardTest:test_reportes_ventas_pagina_la_tabla_pero_las_metricas_usan_el_total_filtrado.
Observaciones: Resuelve defecto común de paginación antes de agregación.
```

```
CASO DE PRUEBA: CP-094
Requisito: RF-063
Módulo: Reportes de Ventas
Nombre: Manejo de reportes sin coincidencias o tabla de ventas vacía
Objetivo: Verificar que filtros sin resultados respondan con HTTP 200 y métricas en 0.0 sin arrojar error 500.
Prioridad: Media
Precondiciones: Administrador autenticado.
Datos de Prueba: fecha_inicio: "1990-01-01", fecha_fin: "1990-01-02" (rango sin registros).
Pasos de Ejecución:
  1. Solicitar GET /reportes/ventas con el rango histórico vacío.
Resultado Esperado: Respuesta HTTP 200, totalPedidos = 0, totalVentas = 0.0, totalUnidades = 0 y visualización del mensaje "No hay ventas registradas en el período seleccionado".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: ReporteFiltrosVaciosTest en tests/Feature/ReporteFiltrosVaciosTest.php.
Observaciones: Previene división por cero en cálculo de promedioVenta.
```

```
CASO DE PRUEBA: CP-095
Requisito: RF-064
Módulo: Reportes de Ventas
Nombre: Exportación de reporte a PDF Landscape aplicando filtros activos
Objetivo: Generar el archivo PDF de ventas reflejando con fidelidad los parámetros de búsqueda aplicados.
Prioridad: Media
Precondiciones: Administrador autenticado; filtros aplicados en sesión o URL.
Datos de Prueba: GET /reportes/ventas/pdf?fecha_inicio=2026-09-01&fecha_fin=2026-09-30&estado=completado.
Pasos de Ejecución:
  1. Solicitar la exportación PDF con los parámetros de prueba.
Resultado Esperado: Descarga de archivo 'ventas_2026-09-01_2026-09-30.pdf' en formato A4 apaisado (landscape) conteniendo únicamente los pedidos filtrados.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: ReporteController::exportPDF verificado en PHPUnit.
Observaciones: Librería barryvdh/laravel-dompdf.
```

```
CASO DE PRUEBA: CP-096
Requisito: RF-065
Módulo: Reportes de Ventas
Nombre: Exportación de reporte a Excel aplicando filtros activos
Objetivo: Descargar el libro Excel de ventas conservando los filtros de fecha, cliente y estado.
Prioridad: Media
Precondiciones: Administrador autenticado.
Datos de Prueba: GET /reportes/ventas/excel?id_cliente=1&estado=completado.
Pasos de Ejecución:
  1. Solicitar exportación Excel.
Resultado Esperado: Descarga de archivo '.xlsx' estructurado mediante la clase VentasExport.
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: ReporteController::exportExcel verificado en PHPUnit.
Observaciones: Librería maatwebsite/excel.
```

---

### Módulo 10: Gestión de Usuarios del Sistema

```
CASO DE PRUEBA: CP-097
Requisito: RF-067 / RNF-001
Módulo: Usuarios
Nombre: Registro exitoso de nuevo usuario administrador o invitado
Objetivo: Dar de alta un usuario asignándole credenciales seguras y rol determinado.
Prioridad: Alta
Precondiciones: Administrador autenticado; correo 'cajero1@supermarket.com' disponible.
Datos de Prueba: name: "Cajero Turno Mañana", email: "cajero1@supermarket.com", password: "Password123", password_confirmation: "Password123", rol: "invitado".
Pasos de Ejecución:
  1. Enviar POST /usuarios con los datos indicados.
Resultado Esperado: Redirección con HTTP 302 a /usuarios, mensaje de éxito en sesión y usuario persistido con contraseña encriptada con bcrypt (cost 12).
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertDatabaseHas('users', ['email' => 'cajero1@supermarket.com', 'rol' => 'invitado']).
Observaciones: UsuarioController::store.
```

```
CASO DE PRUEBA: CP-098
Requisito: RF-067 / RNF-001
Módulo: Usuarios
Nombre: Rechazo de contraseña con menos de 6 caracteres o sin combinación alfanumérica
Objetivo: Exigir contraseñas robustas que incluyan al menos una letra y un número con mínimo 6 caracteres.
Prioridad: Alta
Precondiciones: Administrador autenticado.
Datos de Prueba: password: "12345" (corta) y password: "sololetras" (sin números).
Pasos de Ejecución:
  1. Enviar POST /usuarios con contraseña corta.
  2. Enviar POST /usuarios con contraseña solo alfabética.
Resultado Esperado: En el primer caso error "La contraseña debe tener al menos 6 caracteres.". En el segundo caso error "La contraseña debe contener al menos una letra y un número.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertSessionHasErrors(['password']).
Observaciones: Regex ^(?=.*[A-Za-z])(?=.*\d).+$.
```

```
CASO DE PRUEBA: CP-099
Requisito: RF-069
Módulo: Usuarios
Nombre: Protección contra auto-degradación de rol del Administrador en sesión
Objetivo: Impedir que el administrador autenticado cambie su propio rol a invitado y pierda el acceso al panel.
Prioridad: Alta
Precondiciones: Usuario administrador autenticado con ID 1.
Datos de Prueba: PUT /usuarios/1 con rol: 'invitado'.
Pasos de Ejecución:
  1. Estando en la cuenta ID 1, enviar PUT a /usuarios/1 intentando cambiar el rol a 'invitado'.
Resultado Esperado: La operación es cancelada sin actualizar el rol y redirige con mensaje de error "No puedes cambiar tu propio rol. Pide a otro administrador que lo realice.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertDatabaseHas('users', ['id' => 1, 'rol' => 'administrador']).
Observaciones: Regla de seguridad crítica en UsuarioController:L124-128.
```

```
CASO DE PRUEBA: CP-100
Requisito: RF-070
Módulo: Usuarios
Nombre: Protección contra auto-eliminación de la cuenta de Administrador en sesión
Objetivo: Impedir que un administrador elimine su propio usuario del sistema.
Prioridad: Alta
Precondiciones: Administrador autenticado con ID 1.
Datos de Prueba: DELETE /usuarios/1.
Pasos de Ejecución:
  1. Estando en la cuenta ID 1, emitir DELETE /usuarios/1.
Resultado Esperado: La eliminación se bloquea de inmediato, el registro no se elimina y se muestra error "No puedes eliminar tu propio usuario.".
Resultado Obtenido: 
Estado: PENDIENTE
Evidencia: assertDatabaseHas('users', ['id' => 1]) y verificación de mensaje flash en sesión.
Observaciones: Regla de seguridad en UsuarioController:L172-177.
```

---

## 4. Estado y Trazabilidad del Documento

- **Total de Casos Especificados:** 100 casos (`CP-001` a `CP-100`).
- **Cobertura de Requisitos:** Mapeo formal contra `RF-001` a `RF-072` y `RNF-001` a `RNF-012`.
- **Estado de Ejecución:** `PENDIENTE` para todos los casos (cumpliendo la directiva de no adelantar veredictos sin ejecución física en el entorno de pruebas).
- **Próximo Paso Metodológico:** Elaboración de la Matriz de Trazabilidad y Ejecución formal de la suite en PHPUnit y entorno de staging.
