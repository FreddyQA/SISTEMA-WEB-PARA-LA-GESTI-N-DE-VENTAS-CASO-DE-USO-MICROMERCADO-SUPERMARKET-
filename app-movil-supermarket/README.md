# Supermarket — App Móvil

App móvil (Android/iOS) para el sistema Supermarket, construida con **Expo +
React Native + TypeScript**. Consume la misma base de datos que el admin
web — está "vinculada" a través de una API REST que se agregó al proyecto
Laravel (`routes/api.php`).

## ¿Por qué Expo y no Android Studio puro?

Con Expo, **no necesitas instalar Android Studio ni el SDK de Android para
probar la app mientras la desarrollas** — eso es justamente lo que más
errores de configuración genera en un primer proyecto móvil (versiones de
Gradle, variables de entorno, licencias del SDK, etc.). En su lugar:

1. Instalas la app **Expo Go** desde Play Store en tu celular.
2. Corres `npx expo start` en tu PC.
3. Escaneas el código QR que aparece en la terminal con la cámara del
   celular (o con la propia app Expo Go).
4. La app se abre en tu celular en segundos, con hot-reload: cada vez que
   guardas un cambio de código, se actualiza sola.

Cuando el proyecto ya esté terminado y quieras el `.apk` instalable de
verdad (para entregar o instalar sin Expo Go), se genera con **EAS Build**
(`npx eas build -p android`), un servicio gratuito de Expo que compila el
APK en la nube — así tampoco para ese paso necesitas Android Studio
instalado localmente. Ese paso no se hizo en esta sesión porque requiere
crear una cuenta gratuita de Expo (expo.dev) y no es algo que se pueda
automatizar sin tu usuario.

## Instalación

```bash
cd app-movil-supermarket
npm install
npx expo start
```

Esto abre un panel en la terminal con un código QR. Escanéalo con Expo Go.

## Conectar con el Laravel

1. **Inicia el servidor para el celular**: en la carpeta del proyecto
   Laravel hay un archivo **`iniciar-para-celular.bat`** — dale doble clic.
   Muestra las direcciones IP de tu PC y deja el servidor encendido.

   > Importante: `php artisan serve` a secas (y el `SuperMarket.exe` actual)
   > solo aceptan conexiones desde la propia PC; el celular recibe "No se
   > pudo conectar". `iniciar-para-celular.bat` lo arranca con
   > `--host=0.0.0.0`, que es lo que permite que entre el celular.
2. **Emulador de Android Studio**: la app ya viene apuntando a
   `http://127.0.0.1:8000`; normalmente no hay que cambiar nada.
3. **Celular físico** (lo normal con Expo Go): PC y celular deben estar en
   la **misma red Wi-Fi**. En la app: pantalla de login → "Dirección del
   servidor" → escribe `http://TU-IP:8000` (la IP que muestra el `.bat`,
   por ejemplo `http://192.168.0.105:8000`) y toca **"Probar conexión"**:
   te dice al instante si conecta (y en cuántos ms) o qué revisar si no.
   Luego "Guardar"; queda guardada para la próxima vez.
4. Si aparece el aviso del Firewall de Windows la primera vez, elige
   "Permitir acceso" para PHP (si no, el celular tampoco puede entrar).

Credenciales de prueba (las mismas del seeder de Laravel):
- **Administrador:** dueno@supermarket.com / password123
- **Invitado:** empleado@supermarket.com / password123

## Qué hace la app

- **Login** con las mismas cuentas/roles del admin web (token propio, no
  usa cookies — ver `app/Http/Middleware/ApiTokenAuth.php` del lado
  Laravel).
- **Inicio**: resumen de ventas de hoy/del mes, productos y clientes, stock
  bajo — mismas reglas que el dashboard web (excluye ventas anuladas),
  con las mismas 4 tarjetas de degradado (índigo/esmeralda/ámbar/rosa) que
  el dashboard web.
- **Productos**: lista con búsqueda en tiempo real, precio, stock y la
  **foto** del producto; carga de a 20 con scroll infinito (los datos de
  demo tienen 30 productos: antes solo se veían los primeros 20).
- **Escanear**: usa la cámara para leer el código QR o de barras que el
  admin web ya genera para cada producto, y muestra el producto al
  instante — esto es algo que el admin web, al ser una página en el
  navegador, no puede hacer (no tiene acceso a una cámara física para leer
  un código ya impreso en una etiqueta).
- **Nueva Venta**: elegir cliente, agregar productos con cantidad, y
  registrar la venta — descuenta stock con la misma lógica transaccional
  que usa el admin web (`App\Services\VentaService`, compartida por
  ambos canales para que nunca puedan divergir).
- **Más** → **Clientes**: lista con búsqueda y scroll infinito; crear,
  editar y eliminar (editar/eliminar solo administrador; no deja eliminar
  a un cliente con ventas); y con un toque **llamar** o escribir por
  **WhatsApp** al número del cliente.
- **Más** → **Categorías**: listar, crear, editar y eliminar (solo
  administrador; el invitado solo puede ver), con la misma validación y
  el mismo bloqueo "no se puede eliminar si tiene productos" que el admin
  web.
- **Más** → **Proveedores**: listar con búsqueda, crear (solo
  administrador).
- **Más** → **Reportes de ventas** (solo administrador): mismos filtros
  que el admin web (rango de fechas con selector nativo, cliente, estado)
  y las mismas 4 métricas (cantidad e importe de ventas, unidades vendidas,
  promedio por venta), excluyendo ventas anuladas igual que la web.
- **Productos**: ahora también **crear y editar** (toca un producto o el
  botón "+"), solo administrador — el invitado sigue viendo en modo
  lectura. Incluye **foto del producto**: elegir de la galería o tomarla
  con la cámara (recorte cuadrado, comprimida para el límite de 2 MB), o
  quitarla.
- **Más** → **Historial**: toca cualquier venta para ver su detalle
  completo. Ahí, el administrador puede **editar** los productos y
  cantidades, **anular** la venta (restituye el stock), **reactivar**
  uno anulado, o **eliminarlo** definitivamente — el invitado solo
  consulta el detalle.
- **Más** → **Usuarios** (solo administrador): crear, editar y eliminar
  cuentas y asignar el rol (administrador/invitado) — con las mismas dos
  protecciones que la web: nadie puede eliminar su propia cuenta ni
  cambiarse su propio rol.
- **Más** → **Apariencia**: modo Claro / Oscuro / Según el sistema —
  queda guardado en el celular.

La aplicación utiliza `/api/ventas` como endpoint canónico. Los endpoints
antiguos `/api/pedidos` siguen disponibles temporalmente para versiones ya
instaladas y las tablas antiguas se renombran mediante una migración que
conserva los registros existentes.
- **Más** → cerrar sesión, cambiar la dirección del servidor.

## Acabado profesional

- **Ícono, ícono adaptable de Android y pantalla de carga** propios de la
  marca (carrito sobre el degradado navy del sidebar), en vez de los de Expo.
- **Carga con "esqueletos"** (filas fantasma con pulso suave) en vez de un
  spinner suelto, y **estados vacíos / de error** con ícono, mensaje claro y
  botón "Reintentar".
- **Vibración sutil** al registrar una venta, guardar, agregar un producto
  al carrito, o cuando algo falla.
- **Resguardo de errores**: si algo inesperado rompe una pantalla, se
  muestra un mensaje con "Reintentar" en vez de dejar la app en blanco.
- **Modo oscuro automático**: `app.json` ahora usa
  `userInterfaceStyle: "automatic"` (antes estaba fijo en "light", lo que
  impedía que la opción "Según el sistema" detectara el modo oscuro).
- El APK final permite conectar por HTTP a la red local
  (`usesCleartextTraffic`); Android lo bloquea por defecto desde la
  versión 9 y sin esto la app instalada (no Expo Go) no podría hablar con
  tu Laravel.

## Modo oscuro y paleta de colores

Los colores (claro y oscuro) se tomaron literalmente del CSS real del
admin web (`resources/views/layouts/app.blade.php`: las variables
`:root` y el bloque `.dark-mode`), no son un modo oscuro genérico de
Material Design — mismo índigo `#4F46E5`, mismo degradado del sidebar
(`#1A1A2E → #16213E → #0F3460`, visible en el logo del login), y los
mismos 4 degradados de las tarjetas del dashboard
(`.card-indigo/.card-emerald/.card-amber/.card-rose`). En Más → Apariencia
se puede fijar Claro, Oscuro, o seguir el modo del sistema operativo del
celular (la app web usa un botón sol/luna; acá se ofrecen las tres
opciones, incluida la automática, pero el resultado visual es el mismo
par de paletas).

## Qué NO incluye esta versión (alcance futuro)

La **papelera** (restaurar lo eliminado) sigue siendo exclusiva del admin
web, donde ahora es una sola pantalla con pestañas por tipo. Y Reportes
sin exportación a PDF/Excel (esa parte tiene más sentido en la web).

## Estructura del proyecto

```
src/
  api/          cliente HTTP (JSON y subida de archivos), tipos, endpoints
  components/   EstadoVacio, SkeletonLista, ErrorBoundary
  context/      AuthContext (sesión, token), ThemeContext (modo oscuro)
  hooks/        usePaginado (scroll infinito con búsqueda sin carreras)
  utils/        haptico (vibración)
  screens/      una pantalla por archivo
  navigation/   pestañas inferiores (bottom tabs)
  theme/        colores Material Design 3 a juego con el admin web
```

## Validación hecha

- `npx tsc --noEmit` (TypeScript en modo estricto) sobre toda la app:
  **0 errores**.
- La API de Laravel que consume la app se probó con peticiones HTTP reales
  contra un servidor vivo: login, paginación (`meta.current_page/last_page`,
  que usa el scroll infinito), la ruta `/up` del botón "Probar conexión",
  subida de una imagen PNG real a un producto y carga de su `imagen_url`
  (responde 200 `image/png`), crear y anular una venta confirmando que el
  stock sube y baja bien, etc. Tiene 28 tests propios
  (`tests/Feature/ApiTest.php`) y el proyecto Laravel completo pasa la suite
  de pruebas (SQLite).

---

## Cómo generar el archivo APK instalable para Android (.apk)

La aplicación ya tiene configurado:
- `eas.json` con los perfiles `preview` y `production` configurados en modo `"buildType": "apk"`.
- `app.json` con los permisos necesarios (`CAMERA`, `STORAGE`, `INTERNET`, `RECORD_AUDIO`), `usesCleartextTraffic: true` para permitir peticiones HTTP a Laravel por red local, paquete `com.supermarket.app` y versión 1.
- Íconos y Splash Screen optimizados.

### Opción 1: Generar el APK en la nube con EAS Build (Recomendado, no requiere Android Studio)

1. **Abrir la terminal en la carpeta de la app móvil**:
   ```bash
   cd c:\Users\J.A.R.V.I.S\Documents\OpenCode\PF\app-movil-supermarket
   ```

2. **Instalar EAS CLI globalmente** (si no lo tienes instalado):
   ```bash
   npm install -g eas-cli
   ```

3. **Iniciar sesión con tu cuenta gratuita de Expo**:
   ```bash
   eas login
   ```
   *(Si no tienes una cuenta de Expo, créala gratis en https://expo.dev/signup)*

4. **Compilar el APK para Android**:
   ```bash
   eas build -p android --profile preview
   ```
   - Si te pregunta: *"Generate a new Android Keystore?"*, presiona `Y` (Enter). Expo generará y guardará la clave de firma de forma segura automáticamente.
   - EAS compilará el `.apk` en los servidores de Expo en la nube (toma entre 5 y 10 minutos).
   - Al finalizar, la terminal te dará un enlace directo de descarga para bajar el archivo `Supermarket.apk` e instalarlo directamente en cualquier celular Android.

---

### Opción 2: Compilación local con Android Studio (si tienes el SDK de Android instalado)

1. Precompilar el proyecto nativo de Android:
   ```bash
   npx expo prebuild
   ```
2. Generar el APK con Gradle:
   ```bash
   cd android
   .\gradlew assembleRelease
   ```
   El archivo generado se ubicará en:
   `android/app/build/outputs/apk/release/app-release.apk`
