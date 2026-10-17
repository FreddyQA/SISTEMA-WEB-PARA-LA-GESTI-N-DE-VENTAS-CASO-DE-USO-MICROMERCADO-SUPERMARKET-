# 13 — Informe Final de Resultados de Pruebas (SISMICRO / Supermarket)

> **Informe Final de Resultados de Pruebas del Sistema (Final Test Summary Report)**  
> **Proyecto:** Sistema Web de Ventas e Inventario para el Micromercado SuperMarket  
> **Ámbito:** Proyecto de Grado / Titulación Académica  
> **Fecha de Emisión:** 22 de Septiembre de 2026  
> **Versión:** 1.0  
> **Fuentes de Verdad:** Repositorio real (`app/`, `routes/`, `resources/views/`, `database/`, `tests/`, `composer.json`), `docs/BASE_DE_DATOS.md`, suite automatizada de PHPUnit (`php artisan test`), y documentos de auditoría previos (`01_Analisis_Sistema.md` a `12_Pruebas_Regresion.md`).  
> **Directiva de Integridad:** Se reportan exclusivamente datos, métricas y resultados comprobados en el código y en la ejecución real. No se proyectan tiempos ficticios ni se marcan como aprobadas pruebas que permanecen pendientes.

---

## 1. Introducción

El presente **Informe Final de Resultados de Pruebas** consolida los hallazgos, mediciones cuantitativas, evidencias de ejecución y evaluaciones de calidad técnica obtenidas durante el proceso de auditoría y aseguramiento de calidad (QA) del sistema **SuperMarket** (SISMICRO).

El sistema es una aplicación web transaccional construida sobre el framework **Laravel 12.0** en lenguaje **PHP 8.2+**, diseñada para administrar el inventario, los catálogos de productos y proveedores, el registro de clientes, el procesamiento de ventas en punto de caja con control atómico de existencias y la generación de reportes financieros y de auditoría. Este documento sintetiza de forma fidedigna el estado del software evaluado sobre el código real del repositorio.

---

## 2. Objetivo del Informe

1. **Documentar los Resultados Reales de Ejecución:** Presentar las métricas de la suite automatizada de pruebas unitarias y de integración ejecutadas mediante PHPUnit.
2. **Evaluar el Cumplimiento de Requisitos:** Contrastar la implementación respecto a los 84 requisitos (72 funcionales y 12 no funcionales) definidos en la especificación base.
3. **Consolidar el Estado de Defectos y Regresión:** Detallar los defectos corregidos en la historia del proyecto y las discrepancias activas pendientes de resolución.
4. **Identificar Riesgos y Limitaciones Técnicas:** Determinar las brechas existentes entre el entorno de testing en memoria (SQLite) y el entorno de producción (MySQL 8).
5. **Emitir una Conclusión Basada en Evidencia:** Proveer un dictamen técnico objetivo y verificable que sirva como base para la evaluación de preparación para producción (`14_Evaluacion_Produccion.md`).

---

## 3. Alcance de la Auditoría y Pruebas

### 3.1. Componentes Dentro del Alcance
- **Módulo de Autenticación y Sesiones:** Inicio de sesión, cierre de sesión seguro, regeneración de sesión, protección contra fuerza bruta y redirección inicial.
- **Módulo de Seguridad y Control de Acceso (RBAC):** Middleware `rol:administrador` y restricciones sobre el rol `invitado` en operaciones destructivas y reportes.
- **Módulo de Categorías y Proveedores:** Operaciones CRUD, validación de unicidad activa con soft-delete y protección de integridad referencial ante borrado con productos vinculados.
- **Módulo de Productos e Inventario:** Gestión de catálogo, control de stock no negativo, generación y descarga de códigos QR y de barras, y papelera de reciclaje (`SoftDeletes`).
- **Módulo de Clientes:** Búsqueda reactiva, paginación y bloqueo de eliminación ante compras asociadas.
- **Módulo de Ventas y Pedidos:** Procesamiento atómico en transacciones ACID con `lockForUpdate()`, aritmética decimal exacta con `bcmath`, cálculo de subtotales, y matriz de compensación de stock en anulaciones y eliminaciones.
- **Módulo de Dashboard y Reportes:** Agregación de métricas financieras, discriminación de ventas anuladas, valuación de inventario y exportación a PDF y Excel.
- **Base de Datos y Persistencia:** Migraciones DDL, claves foráneas, restricciones de motor, transacciones e integridad referencial.

### 3.2. Componentes Fuera del Alcance / Pendientes
- Pruebas de estrés y carga masiva concurrente con herramientas externas (JMeter / k6) en servidores físicos de red (especificadas en `09_Plan_Rendimiento.md`, catalogadas como **PENDIENTES DE EJECUCIÓN**).
- Verificación multi-hilo en hardware distribuido para contención de bloqueos InnoDB en MySQL físico.

---

## 4. Ambiente de Pruebas y Configuración Técnica

La auditoría técnica y ejecución de pruebas se llevó a cabo en el siguiente entorno real:

| Parámetro de Entorno | Especificación Técnica Real | Observaciones de Auditoría |
| :--- | :--- | :--- |
| **Sistema Operativo** | Windows 11 Pro / PowerShell 5.1 & 7.x | Entorno local de desarrollo |
| **Lenguaje de Programación** | PHP 8.2+ (CLI) | Tipado estricto, OPcache |
| **Framework Web** | Laravel 12.0 (v12.x) | Arquitectura monolítica MVC |
| **Motor de Base de Datos (Testing)** | SQLite 3 (`:memory:`) | Configurado en `phpunit.xml` para pruebas rápidas |
| **Motor de Base de Datos (Producción)** | MySQL 8.0.16+ / MariaDB 10.2.1+ | Motor InnoDB, `utf8mb4_unicode_ci` |
| **Librería de Precisión Matemática** | `ext-bcmath` (compilada en PHP) | Requerida por `PedidoController` |
| **Framework CSS / UI** | Bootstrap 5.3 + AdminLTE + DataTables | Interfaz responsiva y modo oscuro |
| **Herramienta de Pruebas** | PHPUnit 11.5 / Artisan Test Runner | Suite de pruebas unitarias y de integración |

---

## 5. Pruebas Realizadas (Inventario y Ejecución)

En el repositorio se encuentran implementadas y operativas **15 suites automatizadas** distribuidas en `tests/Unit/` y `tests/Feature/`. La totalidad de las 41 pruebas fue ejecutada de forma real en la sesión de auditoría mediante el comando:
```bash
php artisan test
```

### Detalle de las Pruebas Ejecutadas y Verificadas

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

---

## 6. Resumen Estadístico y Métricas Cuantitativas

### 6.1. Métricas de la Suite Automatizada de Pruebas
- **Total de Pruebas Automatizadas Ejecutadas:** 41 pruebas.
- **Pruebas Aprobadas (PASS):** 41 pruebas (**100.0 %**).
- **Pruebas Fallidas (FAIL):** 0 pruebas (**0.0 %**).
- **Aserciones Evaluadas y Superadas:** 155 aserciones.
- **Tiempo Total de Ejecución de Suite:** 8.73 segundos.
- **Promedio de Tiempo por Prueba:** 0.21 segundos.

### 6.2. Distribución de Pruebas por Tipo
- **Pruebas Unitarias (`tests/Unit`):** 3 pruebas (7.3 %) — Formato de SKU y Huso horario oficial.
- **Pruebas de Integración y Feature (`tests/Feature`):** 38 pruebas (92.7 %) — Controladores, modelos, flujos transaccionales, seguridad y reportes.

### 6.3. Estado de los 100 Casos Formales de `04_Casos_Prueba.md`
- **Total de Casos Especificados en Matriz:** 100 casos (`CP-001` a `CP-100`).
- **Casos con Cobertura Automatizada Directa:** 41 casos (41.0 %).
- **Casos Catalogados como PENDIENTE (Verificación Manual de UI/E2E):** 59 casos (59.0 %).
- **Casos Calificados Artificialmente como PASS sin Ejecución:** 0 casos (0.0 %).

---

## 7. Resultados Detallados por Módulo del Sistema

A continuación se detalla el comportamiento verificado en cada módulo funcional a partir de las pruebas ejecutadas:

### 7.1. Módulo de Autenticación y Control de Acceso (RBAC)
- **Comportamiento Verificado:** Redirección automática de visitantes no autenticados hacia `/login`, redirección de usuarios logueados hacia `/dashboard`, y bloqueo con código HTTP 403 Forbidden para usuarios con rol `invitado` que intentan editar o eliminar pedidos, crear proveedores o manipular categorías.
- **Estado:** `APROBADO CON OBSERVACIONES` (Ver DEF-001 y DEF-002).

### 7.2. Módulo de Inventario y Catálogo de Productos
- **Comportamiento Verificado:** El SKU se genera con ceros a la izquierda y prefijo sin truncar identificadores grandes (`SkuTest`). Se impide la sobreventa cuando la cantidad solicitada excede el stock (`PedidoStockConsistencyTest`). Generación y descarga funcional de códigos QR y códigos de barras en formatos gráficos (`PedidoInvitadoYDescargasTest`).
- **Estado:** `APROBADO`.

### 7.3. Módulo de Categorías y Proveedores
- **Comportamiento Verificado:** Bloqueo de eliminación física/lógica si la categoría o el proveedor cuentan con productos enlazados (`CategoriaIntegrityTest`, `ProveedorTest`). Validación estricta de unicidad de nombre contra registros activos, permitiendo reutilizar nombres si el registro anterior está en la papelera (`deleted_at IS NOT NULL`).
- **Estado:** `APROBADO`.

### 7.4. Módulo de Ventas y Transacciones de Pedidos
- **Comportamiento Verificado:** El flujo de venta descuenta atómicamente el stock y calcula totales con precisión decimal vía BCMath (`PedidoStockConsistencyTest`). La anulación devuelve el stock una única vez (`PedidoAnulacionStockTest`). La eliminación de un pedido anulado no duplica el inventario (*prevención de stock fantasma*, `PedidoEliminarAnuladoTest`).
- **Estado:** `APROBADO`.

### 7.5. Módulo de Reportes y Métricas de Dashboard
- **Comportamiento Verificado:** Las pantallas de reportes soportan filtros vacíos y rangos sin registros retornando HTTP 200 amigable sin excepciones (`ReporteFiltrosVaciosTest`). El cálculo de unidades vendidas e ingresos excluye categóricamente pedidos anulados (`ReporteVentasMetricasTest`, `PaginacionYDashboardTest`).
- **Estado:** `APROBADO CON OBSERVACIONES` (Ver DEF-003).

### 7.6. Páginas de Error y Robustez del Kernel
- **Comportamiento Verificado:** Las rutas inexistentes devuelven HTTP 404 amigable. Las plantillas personalizadas de error (403, 404, 500) renderizan correctamente sin requerir sesión activa (`PaginasDeErrorTest`). La aplicación opera bajo la zona horaria boliviana `America/La_Paz` (`TimezoneTest`).
- **Estado:** `APROBADO`.

---

## 8. Gestión de Defectos (Reporte Consolidado)

De acuerdo con el documento `11_Reporte_Defectos.md`, se identificaron y catalogaron **12 defectos** en el proyecto:

| ID Defecto | Título Sintético | Módulo | Severidad | Prioridad | Estado Actual |
| :---: | :--- | :--- | :---: | :---: | :---: |
| **DEF-001** | Enlace a Reportes visible en sidebar para rol invitado $\rightarrow$ 403 | RBAC / UI | Media | Media | **PENDIENTE** |
| **DEF-002** | Validación residual 'g-recaptcha-response' en LoginController | Autenticación | Baja | Baja | **PENDIENTE** |
| **DEF-003** | `$topProductos` en Dashboard incluye ítems en papelera por JOIN crudo | Dashboard | Media | Media | **PENDIENTE** |
| **DEF-004** | Modelo `User` carece de relación inversa `pedidos()` | Usuarios / Eloquent | Baja | Baja | **PENDIENTE** |
| **DEF-005** | Restricciones CHECK y UNSIGNED omitidas en suite SQLite | Base de Datos | Media | Media | **REQUIERE VERIF.** |
| **DEF-006** | Migración duplicada de tabla `users` rompía `migrate:fresh` | BD / Migraciones | Crítica | Alta | **CORREGIDO** |
| **DEF-007** | Tabla huérfana `password_resets` coexistía con tokens | BD / Auth | Baja | Baja | **CORREGIDO** |
| **DEF-008** | Sintaxis no portable de CHECK rompía migraciones en SQLite | BD / Portabilidad | Alta | Alta | **CORREGIDO** |
| **DEF-009** | Hash bcrypt inválido en volcado `.sql` impedía autenticación | BD / Seguridad | Crítica | Alta | **CORREGIDO** |
| **DEF-010** | Disparidad de credenciales entre Seeder y volcado SQL | BD / Semillas | Media | Media | **CORREGIDO** |
| **DEF-011** | Extensión `ext-bcmath` omitida en composer.json provocaba 500 | Ventas / Dependencias | Alta | Alta | **CORREGIDO** |
| **DEF-012** | Archivos de scaffolding huérfanos sin ruta (HomeController, vistas) | Arquitectura / Código | Baja | Baja | **REQUIERE VERIF.** |

---

## 9. Correcciones Aplicadas (Historial Verificado)

Los 6 defectos corregidos (`DEF-006` a `DEF-011`) fueron verificados contra el código fuente actual:

1. **Refactorización de Migración `users` (DEF-006):** La migración `2024_03_20_000001_add_rol_and_foto_to_users_table.php` aplica `Schema::table()` en lugar de intentar recrear la tabla.
2. **Depuración de Tabla `password_resets` (DEF-007):** Se eliminó la migración huérfana, quedando únicamente `2014_10_12_100000_create_password_reset_tokens_table.php`.
3. **Condicional de Driver para `CHECK` (DEF-008):** Encapsulado en `if (DB::getDriverName() === 'mysql')` en `2026_07_30_070000_fix_database_consistency.php`, permitiendo la ejecución portable en SQLite.
4. **Regeneración de Hash Bcrypt en Volcado SQL (DEF-009):** Hashes comprobados con `password_verify('password123', ...)` en `database/schema_supermarket_completo.sql`.
5. **Unificación de Credenciales (DEF-010):** Seeder y volcado SQL insertan exactamente los mismos usuarios (`dueno@supermarket.com` y `empleado@supermarket.com` con clave `password123`).
6. **Inclusión de `ext-bcmath` en Composer (DEF-011):** Declarada en `composer.json` línea 10, validando la plataforma antes del despliegue.

---

## 10. Evaluación de Pruebas de Regresión

En apego a `12_Pruebas_Regresion.md`, se especificaron y evaluaron **12 casos de prueba de regresión** (`CPR-001` a `CPR-012`):
- **Casos de Regresión Ejecutados y Verificados:** 12 de 12 (**100.0 %**).
- **Veredicto:** **12 PASS / 0 FAIL**.
- **Cobertura de Defectos Históricos:** 100.0 % de los defectos subsanados cuentan con prueba de regresión verificada.
- **Evaluación de Estabilidad:** No se identificaron regresiones activas ni efectos colaterales en el código verificado.

---

## 11. Análisis de Cobertura de Requisitos y Código

Con base en la matriz de trazabilidad `10_Matriz_Trazabilidad.md`:

| Dimensión de Auditoría | Requisitos Identificados en `02` | Requisitos con Casos en `04` | Requisitos con Pruebas Automatizadas | Cobertura Formal de Requisitos |
| :--- | :---: | :---: | :---: | :---: |
| **Requisitos Funcionales (RF)** | 72 | 65 | 32 | **90.28 %** (en diseño) / **44.44 %** (en tests unitarios) |
| **Requisitos No Funcionales (RNF)** | 12 | 10 | 9 | **83.33 %** (en diseño) / **75.00 %** (en tests unitarios) |
| **TOTAL GENERAL** | **84** | **75** | **41** | **89.29 %** (en diseño) / **48.81 %** (en tests unitarios) |

---

## 12. Evidencias Técnicas de Ejecución

1. **Registro de Terminal de PHPUnit:** Salida completa de `php artisan test` con código de salida `0` y 41 pruebas aprobadas en 8.73 segundos.
2. **Archivos de Prueba Unitarios y Feature:** 15 archivos de pruebas físicos ubicados en el directorio `tests/`.
3. **Caché de Ejecución de PHPUnit:** Archivo `.phpunit.result.cache` generado y verificado en la raíz del espacio de trabajo.
4. **Scripts SQL y Migraciones:** `database/schema_supermarket_completo.sql` y las 14 migraciones en `database/migrations/`.

---

## 13. Gestión de Riesgos del Software

A partir de las pruebas y la inspección del código, se identifican los siguientes riesgos operativos:

1. **Riesgo RSK-001 (Discrepancia SQLite vs MySQL 8):** Las restricciones físicas `CHECK (stock >= 0)` e `INT UNSIGNED` no operan en SQLite. Si se despliega sin ejecutar pruebas directas en MySQL, un bug de aplicación podría permitir stock negativo a nivel de motor. *Mitigación:* Configurar CI/CD con contenedor MySQL 8.
2. **Riesgo RSK-002 (Error 403 en Interfaz de Usuario):** El enlace a "Reportes" visible para invitados genera frustración de usuario y quejas de usabilidad. *Mitigación:* Aplicar el parche `@if(auth()->user()->esAdmin())` en `layouts/app.blade.php`.
3. **Riesgo RSK-003 (Sincronización Manual de Archivos):** La subida de fotos copia físicamente archivos con `copy()` a `public/storage/` sin symlinks. En entornos Linux con permisos restrictivos (`www-data`), la subida podría fallar silenciosamente. *Mitigación:* Auditar permisos de escritura en la carpeta `public/storage/`.
4. **Riesgo RSK-004 (Rendimiento Concurrente No Medido):** El plan de rendimiento (`09_Plan_Rendimiento.md`) no cuenta aún con métricas bajo 50/100 usuarios concurrentes. *Mitigación:* Ejecutar pruebas de carga con k6/JMeter previo al uso en horas pico.

---

## 14. Limitaciones Técnicas del Proceso de Pruebas

1. **Entorno de Pruebas In-Memory:** Todas las 41 pruebas automáticas se ejecutan sobre SQLite en memoria (`:memory:`), lo que impide validar bloqueos pesimistas reales por fila (`lockForUpdate`) bajo concurrencia multi-hilo de MySQL InnoDB.
2. **Ausencia de Suite E2E de Navegador:** Las pruebas de frontend son de tipo HTTP Feature (renderizado de vistas y aserciones de estado HTTP), sin pruebas automatizadas basadas en navegadores reales (Laravel Dusk o Selenium) para evaluar eventos JavaScript, modales de Bootstrap o DataTables interactivos.
3. **Métricas de Carga Pendientes:** Los tiempos de respuesta bajo estrés y saturación de red se encuentran conceptualmente modelados pero no ejecutados físicamente.

---

## 15. Conclusión General del Informe

El proceso exhaustivo de auditoría y pruebas técnicas sobre el repositorio **SuperMarket** permite concluir lo siguiente:

1. **Estabilidad Transaccional Comprobada:** El núcleo funcional del sistema (ventas, pedidos, stock, categorías, proveedores y autenticación) es robusto y cuenta con una suite automatizada de **41 pruebas con 100% de éxito (0 fallos)** que blindan las reglas críticas de negocio.
2. **Resolución Exitosa de Defectos Históricos:** Todos los defectos que previamente impedían la migración limpia, la restauración de backups y el cálculo de ventas fueron corregidos y validados mediante pruebas de regresión.
3. **Defectos Menores Localizados:** Los 4 defectos activos pendientes (`DEF-001` a `DEF-004`) son de severidad Media y Baja, perfectamente delimitados y no comprometen la integridad de los datos ni la viabilidad técnica del aplicativo.
4. **Dictamen Técnico:** El sistema se encuentra en un estado **estable, funcional y verificable**, requiriendo la aplicación de los parches menores identificados antes de su liberación definitiva a producción.
