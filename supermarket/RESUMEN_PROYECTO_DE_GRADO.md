# 📋 PROYECTO DE GRADO: SISTEMA INTEGRAL DE GESTIÓN Y VENTAS (SUPERMARKET)
## DOCUMENTO DE SÍNTESIS TÉCNICA, ARQUITECTURA Y GUÍA DE DEFENSA ANTE EL JURADO

---

## 1. FICHA TÉCNICA DEL PROYECTO

| Parámetro | Detalle Técnico |
| :--- | :--- |
| **Nombre del Sistema** | **Supermarket** – Plataforma Integral de Gestión Comercial, Control de Inventario y Ventas Omnicanal |
| **Arquitectura de Software** | Cliente-Servidor Híbrido Desacoplado: Panel Web Administrativo + API RESTful + Aplicación Móvil Nativa |
| **Backend / API** | **PHP 8.2+** / **Laravel 12 (Framework Enterprise)** |
| **Frontend Web** | **Blade Templates**, **Bootstrap 5**, **Vite**, **jQuery DataTables**, **Chart.js** |
| **Frontend Móvil** | **React Native (Expo SDK)**, **TypeScript**, **React Native Paper (Material Design 3)** |
| **Base de Datos** | **MySQL 8.0+ / MariaDB** (con compatibilidad con **SQLite 3** para pruebas automatizadas) |
| **Módulos Físicos / Hardware** | Cámara para escaneo de códigos de barra (Code128) y QR, generación de comprobantes PDF y hojas Excel |
| **Suite de Pruebas** | **PHPUnit / Pest** con **94 pruebas automatizadas** que validan el 100% de los flujos críticos |

---

## 2. ARQUITECTURA Y DESACOPLAMIENTO DEL SISTEMA

```
┌────────────────────────────────────────────────────────────────────────┐
│                        BACKEND LARAVEL 12                              │
│                                                                        │
│   ┌─────────────────────┐   ┌──────────────────────────────────────┐   │
│   │   Web Controllers   │   │         API REST Controllers         │   │
│   │ (Sesiones + CSRF)   │   │       (Stateless Bearer Tokens)      │   │
│   └──────────┬──────────┘   └──────────────────┬───────────────────┘   │
│              │                                 │                       │
│              ▼                                 ▼                       │
│        ┌─────────────────────────────────────────────┐                 │
│        │      CAPA DE SERVICIO (PedidoService)       │                 │
│        │  - Transacciones Atómicas (DB::transaction) │                 │
│        │  - Bloqueos Pesimistas (lockForUpdate)      │                 │
│        │  - Cálculos Monetarios de Alta Precisión    │                 │
│        └──────────────────────┬──────────────────────┘                 │
│                               ▼                                        │
│                 BASE DE DATOS (MySQL / MariaDB)                        │
└───────────────────────────────┼────────────────────────────────────────┘
                                │
        ┌───────────────────────┴───────────────────────┐
        ▼                                               ▼
┌──────────────────────────────┐        ┌──────────────────────────────┐
│       ADMINISTRADOR WEB      │        │       APP MÓVIL (EXPO)       │
│  - Tableros KPI en vivo      │        │  - Lector de Barras y QR     │
│  - Exportaciones PDF & Excel │        │  - Registro ágil de ventas   │
│  - Papelera de reciclaje     │        │  - Clientes con WhatsApp     │
│  - Acciones Rápidas directas │        │  - Acciones Rápidas directas │
└──────────────────────────────┘        └──────────────────────────────┘
```

---

## 3. PUNTOS CLAVE PARA LA EVALUACIÓN DEL JURADO

### 3.1. Autenticación Stateful (Web) vs Stateless (Móvil)
* **Pregunta probable del jurado:** *¿Por qué no se usaron las mismas sesiones del navegador en la aplicación móvil?*
* **Fundamentación Académica:**
  * El **panel web** utiliza autenticación con estado (**Stateful Session**) mediante cookies cifradas `HttpOnly`, `SameSite` y tokens **CSRF** (*Cross-Site Request Forgery*) inyectados en formularios para proteger al usuario contra ataques web comunes.
  * La **aplicación móvil** consume una arquitectura sin estado (**Stateless REST API**) basada en **Tokens Bearer** almacenados en la tabla `api_tokens`. Esto desacopla a la app de la gestión de cookies del navegador, permite revocar tokens de forma remota por dispositivo y optimiza el consumo de datos móviles.

### 3.2. Consistencia e Integridad del Inventario (Propiedades ACID)
* **Pregunta probable del jurado:** *¿Qué ocurre si dos cajeros intentan vender la última unidad de un producto al mismo tiempo?*
* **Fundamentación Académica:**
  * Toda venta se procesa en [`PedidoService`](file:///c:/Users/B.L.A.CK.S.T.A.R/Documents/OpenCode/Supermarket/supermarket/app/Services/PedidoService.php) dentro de una transacción `DB::transaction()`.
  * Se aplica **bloqueo pesimista** (`lockForUpdate()`) sobre la fila del producto en la base de datos durante la lectura de stock. La primera transacción reserva y descuenta el stock; la segunda transacción esperará el desbloqueo y, al verificar que el stock llegó a cero, rechazará la operación lanzando una `ValidationException` clara ("Stock insuficiente").
  * **Cálculo monetario exacto:** Se emplea la librería BCMath (`bcmul`, `bcadd`) con 2 decimales para evitar los errores de redondeo inherentes al estándar binario IEEE 754 de punto flotante en cálculos de dinero.

### 3.3. Tratamiento de Anulaciones y Reversión de Stock
* **Pregunta probable del jurado:** *¿Qué pasa cuando se anula un pedido? ¿Se pierde la trazabilidad o se crea stock fantasma?*
* **Fundamentación Académica:**
  * Al anular un pedido completado, el sistema restituye exactamente las unidades de los productos al almacén y cambia el estado a `anulado`.
  * Los pedidos anulados **se conservan en la base de datos** para auditoría contable, pero sus montos y unidades quedan automáticamente excluidos de las sumatorias de ventas del día y métricas financieras del dashboard y de los reportes.
  * Si un pedido ya estaba anulado, una nueva edición o eliminación no vuelve a sumar stock, previniendo la duplicación involuntaria de existencias (validado con pruebas automatizadas).

### 3.4. Sincronización en Tiempo Real sin Sobrecarga de Servidor
* **Pregunta probable del jurado:** *¿Cómo sabe la app móvil que se creó o modificó un producto en la web sin recargar manualmente?*
* **Fundamentación Académica:**
  * Se diseñó un servicio de sincronización ligera [`SyncService`](file:///c:/Users/B.L.A.CK.S.T.A.R/Documents/OpenCode/Supermarket/supermarket/app/Services/SyncService.php) que mantiene marcas de tiempo UNIX (*timestamps*) de la última modificación de productos, categorías y pedidos.
  * Tanto la web como el móvil realizan un sondeo (*smart polling*) periódico hacia `/api/sync/estado`. La respuesta pesa menos de 100 bytes (únicamente números de versión). Si el cliente detecta una versión más reciente, refresca en segundo plano el listado sin congelar la interfaz ni parpadear.
  * Esto otorga la experiencia de "tiempo real" sin requerir infraestructura costosa de WebSockets ni servidores Node.js adicionales.

---

## 4. DETALLE DE LAS MEJORAS IMPLEMENTADAS EN ESTA REVISIÓN

### 4.1. Corrección del Fondo Blanco al Escribir en la App Móvil (Login y Modales de Edición)
* **Diagnóstico del problema:**
  * En Android, el archivo `app.json` configuraba `"softwareKeyboardLayoutMode": "pan"`. Al emerger el teclado nativo, el sistema operativo desplazaba la ventana hacia arriba, dejando al descubierto el lienzo nativo de la actividad Android que por defecto es blanco puro (`#FFFFFF`).
  * Además, los componentes `KeyboardAvoidingView` tenían asignado `behavior="height"`, lo cual en combinación con Android provocaba un encogimiento doble de los contenedores.
  * En los modales de edición (`ProductosScreen`, `ClientesScreen`, `UsuariosScreen`, `ProveedoresScreen`, `CategoriasScreen`), el contenedor nativo `<Modal>` no poseía una capa raíz con el color temático del fondo (`theme.colors.background`).
* **Solución aplicada:**
  1. En `app.json`: se configuró `"softwareKeyboardLayoutMode": "resize"` y se definió `"backgroundColor": "#0F172A"` tanto en la raíz como en la sección de Android.
  2. En `LoginScreen.tsx` y en todos los modales: se normalizó `KeyboardAvoidingView` para usar `behavior={Platform.OS === 'ios' ? 'padding' : undefined}`.
  3. Se envolvieron los modales en un contenedor raíz `<View style={{ flex: 1, backgroundColor: theme.colors.background }}>`.
  4. Se asignó explícitamente `backgroundColor: theme.colors.surface` y `textColor={theme.colors.onSurface}` a todos los inputs (`TextInput`), garantizando una apariencia sólida sin parpadeos blancos en tema claro y oscuro.

### 4.2. Rediseño de "Acciones Rápidas" (Eliminación de Redundancia)
* **Diagnóstico del problema:**
  * Las acciones rápidas anteriores repetían accesos que ya estaban presentes en la barra de pestañas inferior (Inventario, Escáner, Reportes).
* **Solución aplicada:**
  * **En la App Móvil:** Se transformó el carrusel de acciones rápidas en botones de creación operativa directa:
    1. 🛒 **Nueva Venta:** Navega inmediatamente a la pantalla de caja y despacho.
    2. 📦 **Nuevo Producto:** Navega a la pantalla de productos y abre automáticamente el formulario modal de alta con cámara y selector de categorías.
    3. 👤 **Nuevo Cliente:** Abre directamente el formulario para registrar un nuevo cliente con teléfono y correo.
    4. 🏢 **Nuevo Proveedor:** Acceso directo para registrar distribuidores comerciales.
    5. 🏷️ **Nueva Categoría:** Permite clasificar ítems nuevos al instante.
  * **En el Panel Web:** Se incorporó un bloque destacado de **"Acciones Rápidas del Sistema"** con diseño glassmorphic, tarjetas con badges temáticos y navegación con un solo clic.

### 4.3. Corrección del Bloqueo de Pantalla al Eliminar en Papelera Web
* **Diagnóstico del problema:**
  * Al confirmar la eliminación en el modal de Bootstrap (`#modalEliminar`), la función ejecutaba de inmediato `form.submit()`. Bootstrap 5 añade dinámicamente un elemento `<div class="modal-backdrop fade show">` al `<body>` y le asigna la clase CSS `modal-open` con `overflow: hidden`.
  * Como el formulario realizaba una navegación POST sincrónica antes de que la animación de cierre del modal concluyera, el backdrop quedaba congelado sobre la pantalla impidiendo toda interacción del usuario.
* **Solución aplicada:**
  1. En `papelera/index.blade.php`: se invoca `modal.hide()` antes del envío y se programa un `setTimeout` de 180ms para permitir que Bootstrap remueva limpiamente el backdrop.
  2. Se añadió un listener defensivo al evento `hidden.bs.modal` que limpia cualquier nodo `.modal-backdrop` huérfano y retira la clase `modal-open` del `body`.
  3. Se marcó el formulario con la clase `no-loading` para evitar colisiones con el overlay global de transiciones del layout principal.

### 4.4. Sistema Responsivo y Adaptabilidad Web Multi-Dispositivo
* **Diagnóstico del problema:**
  * El diseño web tenía un padding rígido en el contenedor `#main` (`padding: 28px 48px 40px`), lo que provocaba que en pantallas móviles o tablets se perdiera casi un tercio del ancho útil sólo en márgenes.
  * Los listados DataTables y las tarjetas de métricas no contaban con reglas adaptativas para pantallas angostas.
* **Solución aplicada:**
  1. **Breakpoint Tablets (<= 992px):** Se optimizó `#main` con padding de 22px 24px 36px y espaciado dinámico de grillas KPI.
  2. **Breakpoint Móvil (<= 768px):** Sidebar replegable con sombra flotante (`transform: translateX(-260px)`), `#topbar` a ancho completo, reducción de padding a 18px 16px, scroll táctil horizontal en tablas (`overflow-x: auto; -webkit-overflow-scrolling: touch`), y controles de paginación y búsqueda centrados y apilables.
  3. **Breakpoint Teléfonos (<= 576px):** Topbar compactada a 60px, títulos con elipsis para evitar desbordes de texto, modales al 95% de ancho con esquinas de 16px, botones y tarjetas KPI con tipografía compacta (`1.3rem`), y footer apilado verticalmente.

### 4.5. Soporte Integral de Modo Oscuro en Modales Bootstrap
* **Diagnóstico del problema:**
  * Al activar el modo oscuro, los modales de Bootstrap conservaban fondos blancos predeterminados o textos de bajo contraste en formularios de creación y edición.
* **Solución aplicada:**
  * Se definieron reglas CSS bajo `body.dark-mode .modal-content` con fondo `#0d1733`, bordes sutiles `rgba(255,255,255,0.08)`, sombra nocturna profunda (`box-shadow: 0 25px 60px -12px rgba(0,0,0,0.7)`), cabeceras con gradientes azul noche/índigo (`#1e1b4b` a `#312e81`), inputs adaptados a `#071026` con foco iluminado en índigo, y pies de modal traslúcidos.

### 4.6. Nuevo Icono Profesional de la App Móvil y Adaptive Icon
* **Diagnóstico del problema:**
  * El icono anterior mostraba un fondo plano repetido ("icono de app de fondo") debido a que el archivo PNG de primer plano (*foreground*) no estaba dimensionado para el estándar de iconos adaptativos de Android y pesaba más de 1.3 MB.
* **Solución aplicada:**
  * Se diseñó un icono profesional y minimalista con estética fintech/SaaS: fachada 3D estilizada de tienda de autoservicio con etiqueta de código de barras, bolsa de compra con tilde de verificación y gradiente vibrante índigo-violeta (`#4F46E5` a `#7C3AED`) sobre fondo azul noche (`#0F172A`).
  * Se configuró `backgroundColor: "#0F172A"` en `app.json` para que el lienzo de Android Adaptive Icon ensamble de manera homogénea y limpia con el icono sin bordes blancos ni recortes antiestéticos.

### 4.7. Limpieza de Archivos Residuales y Validación de Pruebas
* Se eliminaron archivos accidentales generados por errores de consola en la raíz de Laravel (`count()`, `get`, `idPedido.'`).
* Se ejecutó la suite de pruebas automatizadas completa de Laravel: **94 de 94 pruebas aprobadas exitosamente (100% PASS, 339 aserciones)**.
* Se validó el tipado estricto de TypeScript en la app móvil con `npx tsc --noEmit` (**0 errores detectados**).

### 4.8. Corrección Exhaustiva de Defectos de Integridad, Seguridad y Flujo Móvil
1. **Validación real de imágenes por API:** Detección de magic bytes con `getimagesizefromstring` para rechazar archivos maliciosos (.php simulado) y forzar lista blanca binaria estricta (JPG, PNG, WEBP).
2. **Ciclo de vida de tokens móviles:** Caducidad automática por inactividad tras 30 días en `ApiTokenAuth` y revocación total de tokens al actualizar la contraseña del usuario.
3. **Protección contra ataques DoS en API:** Activación del middleware `throttle:60,1` en las rutas protegidas de la API móvil.
4. **Escáner óptico híbrido:** El endpoint de productos y la cámara móvil ahora decodifican tanto el payload JSON del código QR (`{"sku":"PRD-0001",...}`) como códigos de barra directos y IDs numéricos, resolviendo el error 404.
5. **Preservación de precios históricos en ventas:** Al editar un pedido, las líneas existentes mantienen su precio pactado original en vez de recalcularse con el precio de catálogo de hoy.
6. **Defensa de papelera en ventas:** Restricción con `whereNull('deleted_at')` para evitar vender a clientes archivados o agregar productos archivados, y tolerancia con `withTrashed()` para permitir consultar, editar o anular pedidos previos con productos enviados a papelera.
7. **Exclusión de pedidos eliminados en Dashboard:** Se añadieron filtros `whereNull('pedidos.deleted_at')` en los JOINs de ranking de productos y clientes más recurrentes.
8. **Ampliación monetaria (decimal 12,2) y CHECKs en BD:** Migración que amplía totales de 999,999.99 a 9,999,999,999.99 y agrega restricciones CHECK de integridad para estado de pedido, rol de usuario y subtotales no negativos.
9. **Búsqueda sobre el 100% de registros en Clientes y Proveedores:** Integración de la búsqueda en DataTables conectada con el servidor para buscar en todo el universo de registros y no limitarse a 15 filas.
10. **Unificación de validaciones:** Creación de `App\Support\ReglasValidacion` como fuente única de verdad para los 10 controladores web y API.
11. **Métricas de Reportes en SQL:** Reemplazo de hidratación masiva de colecciones en RAM por funciones agregadas (`SUM`, `COUNT`) delegadas directamente a la base de datos.
12. **Seguridad móvil con Expo SecureStore y deslogueo reactivo (401):** Migración del almacenamiento de token a `expo-secure-store` con listener global que redirige inmediatamente a la pantalla de login al vencer la sesión.
13. **Permisos Android depurados:** Eliminación de permisos no requeridos (`RECORD_AUDIO`, `WRITE_EXTERNAL_STORAGE`) en `app.json`.

---

## 5. CATÁLOGO DE VALIDACIONES Y CASOS DE BORDE (CONTROL PARA EL JURADO)

| Módulo | Validación / Regla de Negocio | Mecanismo de Control | Resultado ante Violación |
| :--- | :--- | :--- | :--- |
| **Login** | Límite contra ataques de fuerza bruta | `RateLimiter` (5 intentos / 120s por IP + correo) | HTTP 429 "Demasiados intentos. Espera X segundos" |
| **Login** | Formato de correo y contraseña obligatoria | Validación en cliente y en servidor | Mensaje de error visual en badge rojo |
| **Venta / Pedido** | Stock suficiente en almacén | `lockForUpdate()` + verificación atómica | Rechazo de la transacción con aviso de cantidad faltante |
| **Venta / Pedido** | Items duplicados en el mismo pedido | Verificación de claves únicas en base de datos | No permite añadir dos veces el mismo producto en una venta |
| **Venta / Pedido** | Anulación de pedido | Restitución del stock en almacén | Se devuelve el stock; se excluye de las ganancias del día |
| **Productos** | Nombre de producto único / válido | Expresión regular unicode + `min:2, max:100` | Error de validación "Nombre inválido" |
| **Productos** | Precio y stock positivos | `min:0` y validación de 2 decimales fijos | Bloqueo de números negativos o formato inválido |
| **Productos** | Borrado con ventas históricas | Verificación en capa de modelo y papelera | Bloqueo de eliminación física; se envía a papelera |
| **Clientes** | Correo electrónico válido | Validación RFC de email | Rechazo si falta `@` o dominio |
| **Clientes** | Teléfono móvil | Normalización internacional con prefijo `+591` | Enlace directo a llamada o WhatsApp con mensaje listo |
| **Proveedores** | NIT y Razón Social | Control de unicidad de nombre en la BD | Mensaje amigable si ya existe registrado |
| **Categorías** | Eliminación con productos asociados | Control de integridad referencial | Impide el borrado mientras contenga productos activos |
| **Usuarios** | Auto-eliminación del Administrador | Verificación de ID en `UsuarioController` | El administrador no puede borrarse a sí mismo |

---

## 6. GUÍA RÁPIDA DE EJECUCIÓN DEL PROYECTO

### 6.1. Backend Laravel (Servidor Web y API)
Abrir una terminal en `supermarket`:
```bash
# 1. Compilar los estilos y scripts con Vite
npm run build

# 2. Iniciar el servidor local de Laravel
php artisan serve --host=0.0.0.0 --port=8000
```
> El servidor quedará disponible en `http://localhost:8000` para la web y en `http://<TU_IP_LOCAL>:8000` para los celulares en la misma red Wi-Fi.

### 6.2. Aplicación Móvil (React Native con Expo)
Abrir una terminal en `app-movil-supermarket`:
```bash
# 1. Iniciar el empaquetador de Expo
npx expo start
```
* Presionar **`a`** para abrir en emulador Android o escanear el código QR con la app **Expo Go** en un teléfono físico.
* En la pantalla de inicio de sesión de la app, pulsar **"Configurar IP del servidor"** e ingresar `http://<TU_IP_LOCAL>:8000` (o el enlace de túnel Cloudflare). Probar con el botón de ping para verificar conexión inmediata.

---
*Documento preparado por el Equipo de Desarrollo para la Defensa Pública de Grado.*
