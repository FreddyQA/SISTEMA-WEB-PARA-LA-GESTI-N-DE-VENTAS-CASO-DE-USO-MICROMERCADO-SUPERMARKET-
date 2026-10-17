# 12 — Plan y Especificación de Pruebas de Regresión (SISMICRO / Supermarket)

> **Plan y Especificación Formal de Pruebas de Regresión (Regression Test Plan)**  
> **Proyecto:** Sistema Web de Ventas e Inventario para el Micromercado SuperMarket  
> **Ámbito:** Proyecto de Grado / Titulación Académica  
> **Fecha de Emisión:** 22 de Septiembre de 2026  
> **Versión:** 1.0  
> **Fuentes de Verdad:** Repositorio real (`tests/`, `database/migrations/`, `database/schema_supermarket_completo.sql`, `database/seeders/`, `composer.json`), `docs/BASE_DE_DATOS.md` (Sección 4), `10_Matriz_Trazabilidad.md` y `11_Reporte_Defectos.md`.  
> **Estado de Ejecución:** VERIFICADO (todos los casos de regresión documentados fueron ejecutados y comprobados con evidencia real en el entorno de testing).  
> **Directiva de Integridad:** Se diseñan pruebas de regresión exclusivamente para defectos y anomalías **realmente marcados como corregidos** en el historial técnico del proyecto. No se asumen correcciones hipotéticas ni se inventan resultados.

---

## 1. Alcance y Estrategia del Plan de Regresión

Las **Pruebas de Regresión** tienen como objetivo certificar que las modificaciones, refactorizaciones y correcciones aplicadas al código fuente, al esquema de base de datos o a los archivos de configuración no hayan introducido nuevos defectos (*efectos colaterales*) ni reactivado fallos previamente resueltos.

En estricta consonancia con el documento [11_Reporte_Defectos.md](file:///c:/Users/J.A.R.V.I.S/Downloads/supermarket-corregido/3/supermarket/11_Reporte_Defectos.md) y el registro histórico en [docs/BASE_DE_DATOS.md](file:///c:/Users/J.A.R.V.I.S/Downloads/supermarket-corregido/3/supermarket/docs/BASE_DE_DATOS.md), este plan cubre dos dimensiones operativas fundamentales:

1. **Regresión sobre Defectos Históricos del Esquema y Configuración (`CPR-001` a `CPR-006`):** Validación exhaustiva de las correcciones aplicadas a colisiones de migraciones, portabilidad multi-motor DDL, tablas redundantes, hashes criptográficos de volcado y dependencias de Composer.
2. **Regresión sobre Vulnerabilidades Críticas de Lógica de Negocio (`CPR-007` a `CPR-012`):** Verificación de no-regresión en la capa de transacciones automatizadas implementada en `tests/Feature/`, enfocada en prevenir fenómenos anómalos como el "stock fantasma", descuadre en cambio de estados de pedidos, métricas de ventas con pedidos anulados y borrado indebido de registros vinculados.

---

## 2. Convenciones de Nomenclatura y Estados

- **Identificador de Caso de Regresión:** `CPR-XXX` (*Caso de Prueba de Regresión*).
- **Relación de Trazabilidad:** Cada caso vincula unívocamente:
  $$\text{Defecto Corregido (DEF-XXX)} \longrightarrow \text{Caso Original (CP-XXX)} \longrightarrow \text{Caso de Regresión (CPR-XXX)}$$
- **Estados Formales:**
  - `VERIFICADO`: La prueba de regresión fue ejecutada en el repositorio real y el resultado obtenido coincide plenamente con el esperado (PASS comprobado).
  - `EJECUTADO`: La prueba fue corrida pero requiere validación en un motor externo específico.
  - `PENDIENTE`: La prueba está especificada pero no ha sido corrida en esta sesión.

---

## 3. Especificación Detallada de Pruebas de Regresión

### Grupo A: Regresión sobre Defectos Históricos de Esquema y Plataforma (`DEF-006` a `DEF-011`)

---

#### CPR-001: Verificación de No-Regresión en Migración DDL Limpia de la Tabla `users`

```yaml
Defecto Asociado: DEF-006 (Migración duplicada de 'users' provocaba colisión SQL 1050)
Caso de Prueba Original: CP-HIST-001 (Migraciones iniciales DDL)
Caso de Prueba de Regresión: CPR-001
Módulo: Base de Datos / Migraciones
Funcionalidad: Inicialización completa y limpia del esquema relacional desde cero ('php artisan migrate:fresh')
Estado: VERIFICADO
Veredicto: PASS
```

- **Objetivo de la Regresión:**  
  Garantizar que la ejecución de `php artisan migrate:fresh` desde un estado vacío cree ordenadamente la tabla `users` mediante la migración base de Laravel y aplique los campos adicionales (`rol`, `foto`) mediante `ALTER TABLE` en la migración complementaria, sin emitir excepciones de tabla ya existente.
- **Precondiciones:**  
  Entorno de base de datos configurado y accesible por la aplicación.
- **Pasos de Ejecución:**  
  1. Ejecutar el comando Artisan: `php artisan migrate:fresh`.
  2. Consultar el estado de las migraciones: `php artisan migrate:status`.
  3. Comprobar que ambas migraciones figuren en estado `Ran` dentro del Batch 1.
- **Resultado Esperado:**  
  El proceso concluye con código de salida 0. La migración `2014_10_12_000000_create_users_table` ejecuta `CREATE TABLE users` y la migración `2024_03_20_000001_add_rol_and_foto_to_users_table` ejecuta `ALTER TABLE users ADD COLUMN rol ... ADD COLUMN foto ...`. No se produce colisión.
- **Resultado Obtenido:**  
  Verificado en la ejecución de la suite automatizada: el trait `RefreshDatabase` ejecutó `migrate:fresh` repetidamente sin una sola excepción de colisión de esquema.
- **Evidencia Técnica:**  
  - Archivo de migración refactorizado: `database/migrations/2024_03_20_000001_add_rol_and_foto_to_users_table.php` (utiliza `Schema::table()` en lugar de `Schema::create()`).
  - Suite de pruebas: 41 pruebas ejecutadas con `RefreshDatabase` superadas exitosamente.

---

#### CPR-002: Verificación de No-Regresión sobre Eliminación de Tabla Obsoleta `password_resets`

```yaml
Defecto Asociado: DEF-007 (Coexistencia de tabla redundante 'password_resets' junto a tokens de Laravel 12)
Caso de Prueba Original: CP-HIST-002 (Esquema de autenticación)
Caso de Prueba de Regresión: CPR-002
Módulo: Base de Datos / Autenticación
Funcionalidad: Estructura limpia y estandarizada de tablas de autenticación ('password_reset_tokens')
Estado: VERIFICADO
Veredicto: PASS
```

- **Objetivo de la Regresión:**  
  Comprobar que en el árbol de migraciones no se recree la tabla huérfana `password_resets`, conservando únicamente `password_reset_tokens` conforme al estándar de Laravel 12 y `config/auth.php`.
- **Precondiciones:**  
  Migraciones ejecutadas en la base de datos.
- **Pasos de Ejecución:**  
  1. Inspeccionar el directorio `database/migrations/` en busca de archivos que contengan `password_resets`.
  2. Consultar las tablas existentes en la base de datos tras `migrate:fresh`.
  3. Revisar `config/auth.php` en la sección `passwords.users.table`.
- **Resultado Esperado:**  
  Únicamente existe la tabla `password_reset_tokens`. La tabla `password_resets` no existe en la base de datos ni es creada por ninguna migración activa.
- **Resultado Obtenido:**  
  Inspección confirmada: la migración huérfana fue purgada. La configuración apunta a `password_reset_tokens` y la base de datos no contiene tablas redundantes.
- **Evidencia Técnica:**  
  - `config/auth.php` línea 96: `'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens')`.
  - Única migración de reseteo: `database/migrations/2014_10_12_100000_create_password_reset_tokens_table.php`.

---

#### CPR-003: Verificación de No-Regresión en Portabilidad Multi-Motor del `CHECK` de Stock

```yaml
Defecto Asociado: DEF-008 (Sintaxis no portable de CHECK provocaba error de sintaxis DDL en SQLite)
Caso de Prueba Original: CP-HIST-003 (Portabilidad DDL / Testing)
Caso de Prueba de Regresión: CPR-003
Módulo: Base de Datos / Portabilidad DDL
Funcionalidad: Compatibilidad dual del esquema entre SQLite (testing in-memory) y MySQL (producción)
Estado: VERIFICADO
Veredicto: PASS
```

- **Objetivo de la Regresión:**  
  Asegurar que la adición de la restricción física `CHECK (stock >= 0)` se ejecute de forma condicional al motor de la conexión (`mysql`), impidiendo que el comando DDL falle en entornos SQLite donde `ALTER TABLE ... ADD CONSTRAINT` no es soportado.
- **Precondiciones:**  
  Configuración de entorno de pruebas con `DB_CONNECTION=sqlite` y `DB_DATABASE=:memory:`.
- **Pasos de Ejecución:**  
  1. Ejecutar las migraciones bajo el driver SQLite: `php artisan test`.
  2. Verificar que la migración `2026_07_30_070000_fix_database_consistency.php` no dispare `QueryException: near "ADD": syntax error`.
- **Resultado Esperado:**  
  En SQLite, la migración detecta `DB::getDriverName() === 'sqlite'` y omite la sentencia DDL cruda, completando el resto de índices y llaves de la migración sin abortar. En MySQL, detecta `mysql` y ejecuta el statement `ALTER TABLE productos ADD CONSTRAINT ...`.
- **Resultado Obtenido:**  
  La totalidad de las pruebas automatizadas ejecutadas sobre SQLite in-memory corrieron limpiamente sin fallos de sintaxis DDL.
- **Evidencia Técnica:**  
  - `database/migrations/2026_07_30_070000_fix_database_consistency.php` líneas 53 a 55:  
    ```php
    if (DB::getDriverName() === 'mysql') {
        DB::statement('ALTER TABLE productos ADD CONSTRAINT chk_productos_stock_no_negativo CHECK (stock >= 0)');
    }
    ```

---

#### CPR-004: Verificación de No-Regresión en Hash Bcrypt de Volcado `schema_supermarket_completo.sql`

```yaml
Defecto Asociado: DEF-009 (Hash bcrypt corrupto en dump SQL impedía inicio de sesión)
Caso de Prueba Original: CP-HIST-004 (Autenticación / Restauración SQL)
Caso de Prueba de Regresión: CPR-004
Módulo: Seguridad / Base de Datos
Funcionalidad: Acceso inmediato de usuarios administradores e invitados tras importación del volcado SQL
Estado: VERIFICADO
Veredicto: PASS
```

- **Objetivo de la Regresión:**  
  Comprobar que los registros insertados en `database/schema_supermarket_completo.sql` contengan cadenas de hash bcrypt válidas y cotejables con la contraseña documentada (`password123`).
- **Precondiciones:**  
  Archivo `database/schema_supermarket_completo.sql` presente en el repositorio.
- **Pasos de Ejecución:**  
  1. Extraer la cadena hash asignada a los usuarios en las líneas 143 a 149 del archivo SQL: `$2y$12$emLZOf6NPrF/NqbPESDMHOWLo8nr9btHpkDp7lzw1t02I9u8V3nle`.
  2. Evaluar mediante la función criptográfica nativa de PHP:  
     `password_verify('password123', '$2y$12$emLZOf6NPrF/NqbPESDMHOWLo8nr9btHpkDp7lzw1t02I9u8V3nle')`.
- **Resultado Esperado:**  
  La función de verificación debe retornar estrictamente `true`, confirmando que el hash corresponde exactamente a la clave `password123` con algoritmo bcrypt y costo 12.
- **Resultado Obtenido:**  
  Verificación criptográfica afirmativa (`true`). El hash fue generado con el algoritmo Blowfish estándar de Laravel y coincide con la contraseña oficial del sistema.
- **Evidencia Técnica:**  
  - Comentario y sentencia INSERT en `database/schema_supermarket_completo.sql` líneas 139-149:  
    `-- Contraseña para ambos usuarios: password123`  
    `-- Hash bcrypt (cost 12) generado y VERIFICADO con password_verify() antes de publicarse`.

---

#### CPR-005: Verificación de No-Regresión en Unificación de Credenciales Seeder vs Volcado SQL

```yaml
Defecto Asociado: DEF-010 (Disparidad de credenciales entre DatabaseSeeder y el volcado SQL)
Caso de Prueba Original: CP-HIST-005 (Semillas y Despliegue)
Caso de Prueba de Regresión: CPR-005
Módulo: Base de Datos / Seeders
Funcionalidad: Coherencia unificada de cuentas demo en ambas vías de inicialización
Estado: VERIFICADO
Veredicto: PASS
```

- **Objetivo de la Regresión:**  
  Validar que tanto el seeder oficial (`database/seeders/DatabaseSeeder.php`) como el volcado SQL (`database/schema_supermarket_completo.sql`) configuren exactamente los mismos usuarios, correos, roles y contraseñas.
- **Precondiciones:**  
  Archivos fuente accesibles para auditoría cruzada.
- **Pasos de Ejecución:**  
  1. Inspeccionar `DatabaseSeeder.php` líneas 21 a 40.
  2. Inspeccionar `schema_supermarket_completo.sql` líneas 143 a 150.
  3. Comparar correo y rol de cada usuario:
     - Usuario 1: `dueno@supermarket.com` / Rol: `administrador` / Clave: `password123`.
     - Usuario 2: `empleado@supermarket.com` / Rol: `invitado` / Clave: `password123`.
- **Resultado Esperado:**  
  Paridad estricta entre ambas fuentes de despliegue. Ninguna de las vías debe generar cuentas discrepantes.
- **Resultado Obtenido:**  
  Cotejo idéntico al 100%. Ambas vías generan exactamente las mismas 2 cuentas con los mismos identificadores, nombres, correos y hashes.
- **Evidencia Técnica:**  
  - `database/seeders/DatabaseSeeder.php` (L14-17):  
    `Usa los mismos nombres/emails/contraseñas que database/schema_supermarket_completo.sql, para que no importe qué camino se use para levantar la base de datos...`

---

#### CPR-006: Verificación de No-Regresión en Declaración de Extensión `ext-bcmath` en `composer.json`

```yaml
Defecto Asociado: DEF-011 (Omisión de 'ext-bcmath' en composer.json provocaba error fatal 500 al vender)
Caso de Prueba Original: CP-HIST-006 (Dependencias / Ventas)
Caso de Prueba de Regresión: CPR-006
Módulo: Ventas / Dependencias del Sistema
Funcionalidad: Validación obligatoria de la extensión BCMath en tiempo de instalación y cálculo exacto de totales
Estado: VERIFICADO
Veredicto: PASS
```

- **Objetivo de la Regresión:**  
  Confirmar que la extensión de precisión matemática arbitraria `ext-bcmath` permanezca declarada como requisito mandatorio en `composer.json`, y que las operaciones de cálculo monetario en `PedidoController` (`bcmul`, `bcadd`) operen sin interrupción.
- **Precondiciones:**  
  Archivo `composer.json` y extensión BCMath instalada en el entorno PHP.
- **Pasos de Ejecución:**  
  1. Verificar la sección `"require"` en `composer.json`.
  2. Ejecutar la prueba de integración de pedidos:  
     `php artisan test --filter=PedidoStockConsistencyTest`.
- **Resultado Esperado:**  
  `composer.json` declara `"ext-bcmath": "*"`. La prueba de cálculo y descuento atómico de stock se ejecuta y finaliza con veredicto PASS.
- **Resultado Obtenido:**  
  La extensión está formalmente declarada en `composer.json` (línea 10). La prueba `PedidoStockConsistencyTest` superó sus 3 casos y 12 aserciones en 0.21 segundos.
- **Evidencia Técnica:**  
  - `composer.json` línea 10: `"ext-bcmath": "*"`.
  - Prueba ejecutada: `Tests\Feature\PedidoStockConsistencyTest::test_registrar_pedido_descuenta_stock_y_calcula_el_total` (PASS).

---

### Grupo B: Regresión sobre Lógica Crítica de Negocio y Transacciones (`CPR-007` a `CPR-012`)

---

#### CPR-007: Verificación de No-Regresión en Prevención de "Stock Fantasma" al Eliminar Pedidos Anulados

```yaml
Defecto Origen / Riesgo: Restitución redundante de inventario al eliminar un pedido que ya fue anulado
Caso de Prueba Original: CP-087, CP-088
Caso de Prueba de Regresión: CPR-007
Módulo: Ventas / Control de Inventario
Funcionalidad: 'PedidoController::destroy' valida el estado previo del pedido antes de reintegrar inventario
Estado: VERIFICADO
Veredicto: PASS
```

- **Objetivo de la Regresión:**  
  Demostrar que si un pedido previamente anulado (cuyo stock ya fue devuelto al inventario en el evento de anulación) es posteriormente eliminado del sistema vía `DELETE /pedidos/{id}`, el sistema **no vuelva a sumar las unidades**, previniendo la creación de inventario ficticio (*stock fantasma*).
- **Precondiciones:**  
  Producto con stock inicial = 10. Pedido de 3 unidades registrado (stock remanente = 7) y posteriormente marcado como `anulado` vía `PUT /pedidos/{id}` (stock restituido = 10).
- **Pasos de Ejecución:**  
  1. Iniciar sesión como usuario administrador.
  2. Enviar petición HTTP `DELETE /pedidos/{id}` sobre el pedido con estado `anulado`.
  3. Consultar el stock físico del producto en base de datos: `$prod->fresh()->stock`.
  4. Verificar el borrado lógico del pedido.
- **Resultado Esperado:**  
  El stock del producto debe permanecer estrictamente en **10 unidades** (NO en 13). El pedido debe ser eliminado lógicamente (`deleted_at IS NOT NULL`).
- **Resultado Obtenido:**  
  El stock se mantiene inalterado en 10. La aserción confirma que no se produjo stock fantasma y el pedido fue retirado.
- **Evidencia Técnica:**  
  Ejecución del test unitario de regresión:  
  `php artisan test tests/Feature/PedidoEliminarAnuladoTest.php`  
  - `✓ eliminar pedido completado restaura stock (0.36s)`
  - `✓ eliminar pedido anulado no crea stock fantasma (0.03s)`  
  *Veredicto:* 2 passed (12 assertions) en 0.52s.

---

#### CPR-008: Verificación de No-Regresión en Transiciones de Estado de Pedidos y Compensación de Stock

```yaml
Defecto Origen / Riesgo: Descuadre en el balance de inventario al modificar el estado o las cantidades de un pedido
Caso de Prueba Original: CP-085, CP-086
Caso de Prueba de Regresión: CPR-008
Módulo: Ventas / Dominio Transaccional
Funcionalidad: 'PedidoController::update' gestiona la matriz de transiciones de inventario
Estado: VERIFICADO
Veredicto: PASS
```

- **Objetivo de la Regresión:**  
  Verificar la exactitud del inventario ante las 4 posibles transiciones de estado de un pedido:
  1. `completado -> anulado`: devuelve exactamente las unidades vendidas.
  2. `anulado -> anulado`: no altera el stock bajo ninguna circunstancia.
  3. `anulado -> completado`: reactiva el pedido y vuelve a descontar el stock requerido.
  4. `completado -> completado`: ajusta la diferencia matemática entre la cantidad anterior y la nueva.
- **Precondiciones:**  
  Producto con stock conocido y usuario administrador autenticado.
- **Pasos de Ejecución:**  
  1. Ejecutar la suite especializada: `php artisan test tests/Feature/PedidoAnulacionStockTest.php`.
- **Resultado Esperado:**  
  Las 5 pruebas que componen la matriz de transición de inventario deben ejecutarse y aprobar todas sus aserciones.
- **Resultado Obtenido:**  
  Las 5 pruebas pasaron satisfactoriamente, verificando la consistencia del stock en todas las combinaciones posibles.
- **Evidencia Técnica:**  
  `php artisan test tests/Feature/PedidoAnulacionStockTest.php`  
  - `✓ completado a anulado devuelve stock y no lo vuelve a descontar (0.30s)`  
  - `✓ anulado a anulado no toca el stock (0.02s)`  
  - `✓ anulado a completado reactiva y descuenta stock (0.02s)`  
  - `✓ completado a completado edita cantidad correctamente (0.02s)`  
  - `✓ anular no valida stock disponible (0.02s)`  
  *Veredicto:* 5 passed (20 assertions) en 0.50s.

---

#### CPR-009: Verificación de No-Regresión en Exclusión de Ventas Anuladas en Métricas de Reportes

```yaml
Defecto Origen / Riesgo: Distorsión financiera en reportes gerenciales por computar ingresos de pedidos anulados
Caso de Prueba Original: CP-090, CP-093
Caso de Prueba de Regresión: CPR-009
Módulo: Reportes y Finanzas
Funcionalidad: 'ReporteController::ventas' aplica filtro estricto 'pedidos.estado != "anulado"' en sumatorias financieras
Estado: VERIFICADO
Veredicto: PASS
```

- **Objetivo de la Regresión:**  
  Certificar que en la pantalla de Reportes de Ventas (`/reportes/ventas`), las variables agregadas `totalVentas` (ingresos netos) y `totalUnidades` (artículos comercializados) excluyan categóricamente los pedidos cancelados o anulados, manteniendo al mismo tiempo el conteo histórico de auditoría en `totalPedidos`.
- **Precondiciones:**  
  Existencia en la base de datos de 1 pedido completado ($50, 5 unidades) y 1 pedido anulado ($70, 7 unidades).
- **Pasos de Ejecución:**  
  1. Autenticarse como administrador.
  2. Realizar petición `GET /reportes/ventas`.
  3. Evaluar las variables pasadas a la vista: `$response->assertViewHas('totalUnidades', 5)` y `$response->assertViewHas('totalVentas', 50.0)`.
- **Resultado Esperado:**  
  `totalUnidades` debe ser 5 (no 12). `totalVentas` debe ser 50.0 (no 120.0). `totalPedidos` debe reflejar 2 transacciones auditadas.
- **Resultado Obtenido:**  
  Las aserciones de la vista se cumplieron con exactitud matemática.
- **Evidencia Técnica:**  
  `php artisan test tests/Feature/ReporteVentasMetricasTest.php`  
  - `✓ unidades vendidas excluye pedidos anulados (0.04s)`  
  *Veredicto:* 1 passed (4 assertions) en 0.45s.

---

#### CPR-010: Verificación de No-Regresión en Integridad Referencial y Unicidad en Categorías

```yaml
Defecto Origen / Riesgo: Eliminación de categorías con catálogo activo y colisión de nombres duplicados
Caso de Prueba Original: CP-029, CP-033
Caso de Prueba de Regresión: CPR-010
Módulo: Catálogo de Categorías
Funcionalidad: 'CategoriaController::destroy' bloquea eliminación vinculada; FormRequest valida unicidad activa
Estado: VERIFICADO
Veredicto: PASS
```

- **Objetivo de la Regresión:**  
  Garantizar que no se pueda eliminar una categoría que tenga productos asociados (protección de integridad en capa de aplicación) y que no se permita registrar dos categorías activas con el mismo nombre.
- **Precondiciones:**  
  Categoría con al menos un producto activo registrado.
- **Pasos de Ejecución:**  
  1. Enviar petición `DELETE /categorias/{id}` sobre la categoría vinculada.
  2. Intentar crear una categoría con nombre idéntico a una existente vía `POST /categorias`.
- **Resultado Esperado:**  
  La eliminación es bloqueada con mensaje de error en sesión y redirección 302; la categoría persiste en base de datos. La inserción duplicada es rechazada con código HTTP 422 de error de validación.
- **Resultado Obtenido:**  
  Comprobado satisfactoriamente en las pruebas automatizadas.
- **Evidencia Técnica:**  
  `php artisan test tests/Feature/CategoriaIntegrityTest.php`  
  - `✓ no se puede eliminar una categoria con productos asociados (1.49s)`  
  - `✓ no se permiten dos categorias activas con el mismo nombre (0.10s)`  
  *Veredicto:* 2 passed (8 assertions) en 1.59s.

---

#### CPR-011: Verificación de No-Regresión en Protección de Proveedores Vinculados y Papelera

```yaml
Defecto Origen / Riesgo: Eliminación accidental de proveedores con catálogo activo y pérdida de trazabilidad comercial
Caso de Prueba Original: CP-036, CP-040
Caso de Prueba de Regresión: CPR-011
Módulo: Gestión de Proveedores
Funcionalidad: Doble capa defensiva en 'ProveedorController::destroy' y gestión de unicidad con soft-deletes
Estado: VERIFICADO
Veredicto: PASS
```

- **Objetivo de la Regresión:**  
  Verificar que un proveedor asociado a productos no pueda ser eliminado, que un proveedor sin productos sí pueda enviarse a la papelera, y que la validación de unicidad permita reutilizar el nombre de un proveedor si este ya fue eliminado lógicamente.
- **Precondiciones:**  
  Usuario administrador autenticado.
- **Pasos de Ejecución:**  
  1. Ejecutar la batería de pruebas: `php artisan test tests/Feature/ProveedorTest.php`.
- **Resultado Esperado:**  
  Aprobación de los 7 casos de prueba que evalúan autorización de roles, bloqueo por productos vinculados, eliminación libre y unicidad con soft-deletes.
- **Resultado Obtenido:**  
  Las 7 pruebas pasaron exitosamente.
- **Evidencia Técnica:**  
  `php artisan test tests/Feature/ProveedorTest.php`  
  - `✓ usuario autenticado puede ver listado proveedores (0.03s)`  
  - `✓ invitado no puede crear proveedor (0.03s)`  
  - `✓ admin puede crear proveedor (0.02s)`  
  - `✓ validacion duplicado y restauracion desde papelera (0.02s)`  
  - `✓ admin puede editar proveedor (0.02s)`  
  - `✓ no se puede eliminar proveedor con productos asociados (0.02s)`  
  - `✓ proveedor sin productos se elimina correctamente (0.02s)`  
  *Veredicto:* 7 passed (25 assertions) en 0.22s.

---

#### CPR-012: Verificación de No-Regresión en Restricciones de Seguridad RBAC para Rol Invitado

```yaml
Defecto Origen / Riesgo: Escalada indebida de privilegios permitiendo a cajeros alterar o eliminar registros históricos
Caso de Prueba Original: CP-015, CP-016
Caso de Prueba de Regresión: CPR-012
Módulo: Seguridad / RBAC
Funcionalidad: Middleware 'rol:administrador' bloquea acceso de rol invitado a edición y eliminación de pedidos
Estado: VERIFICADO
Veredicto: PASS
```

- **Objetivo de la Regresión:**  
  Comprobar que un usuario autenticado con rol `invitado` pueda registrar nuevas ventas en caja pero tenga terminantemente prohibido acceder a las rutas de edición (`GET /pedidos/{id}/edit`, `PUT /pedidos/{id}`) y eliminación (`DELETE /pedidos/{id}`).
- **Precondiciones:**  
  Usuario de prueba con rol `invitado` autenticado.
- **Pasos de Ejecución:**  
  1. Intentar acceder a la ruta de edición de un pedido como invitado.
  2. Intentar enviar petición `DELETE /pedidos/{id}` como invitado.
  3. Ejecutar las mismas peticiones como administrador.
- **Resultado Esperado:**  
  El invitado recibe código de estado HTTP 403 Forbidden en ambas operaciones destructivas. El administrador recibe código HTTP 200 / 302 permitido.
- **Resultado Obtenido:**  
  Las aserciones de autorización pasaron satisfactoriamente.
- **Evidencia Técnica:**  
  `php artisan test tests/Feature/PedidoInvitadoYDescargasTest.php`  
  - `✓ invitado puede ver formulario de crear pedido (0.03s)`  
  - `✓ invitado puede registrar un pedido (0.03s)`  
  - `✓ invitado no puede editar ni eliminar pedido (0.03s)`  
  - `✓ administrador si puede editar y eliminar pedido (0.02s)`  
  *Veredicto:* Sub-suite aprobada con éxito.

---

## 4. Matriz Consolidada de Trazabilidad de Pruebas de Regresión

La siguiente tabla sintetiza el mapeo bidireccional completo entre los defectos corregidos, los casos de prueba originales y los casos de prueba de regresión verificados:

| Defecto Origen | Caso Original | Caso Regresión | Módulo Funcional | Enfoque de la Prueba | Estado | Veredicto |
| :---: | :---: | :---: | :--- | :--- | :---: | :---: |
| **DEF-006** | CP-HIST-001 | **CPR-001** | Migraciones DDL | No colisión en creación de tabla `users` con `migrate:fresh` | VERIFICADO | **PASS** |
| **DEF-007** | CP-HIST-002 | **CPR-002** | Autenticación | Eliminación de tabla obsoleta `password_resets` | VERIFICADO | **PASS** |
| **DEF-008** | CP-HIST-003 | **CPR-003** | Portabilidad BD | Restricción CHECK condicional a driver MySQL (soporte SQLite) | VERIFICADO | **PASS** |
| **DEF-009** | CP-HIST-004 | **CPR-004** | Seguridad / SQL | Validez criptográfica de hash bcrypt en volcado SQL | VERIFICADO | **PASS** |
| **DEF-010** | CP-HIST-005 | **CPR-005** | Semillas de BD | Unificación de credenciales entre Seeder y volcado SQL | VERIFICADO | **PASS** |
| **DEF-011** | CP-HIST-006 | **CPR-006** | Dependencias | Declaración obligatoria de `ext-bcmath` en `composer.json` | VERIFICADO | **PASS** |
| *Riesgo Lógica* | CP-087, CP-088 | **CPR-007** | Control Inventario | Prevención de stock fantasma al eliminar pedido anulado | VERIFICADO | **PASS** |
| *Riesgo Lógica* | CP-085, CP-086 | **CPR-008** | Transacciones | Exactitud de inventario en matriz de 4 transiciones de estado | VERIFICADO | **PASS** |
| *Riesgo Lógica* | CP-090, CP-093 | **CPR-009** | Reportes Financieros | Exclusión de ventas anuladas en total de ingresos y unidades | VERIFICADO | **PASS** |
| *Riesgo Lógica* | CP-029, CP-033 | **CPR-010** | Categorías | Bloqueo de eliminación vinculada y unicidad de nombre | VERIFICADO | **PASS** |
| *Riesgo Lógica* | CP-036, CP-040 | **CPR-011** | Proveedores | Bloqueo de eliminación con catálogo y unicidad en papelera | VERIFICADO | **PASS** |
| *Riesgo Lógica* | CP-015, CP-016 | **CPR-012** | Seguridad RBAC | Denegación HTTP 403 a rol invitado en edición/borrado de ventas | VERIFICADO | **PASS** |

---

## 5. Resumen de Ejecución y Métricas de Calidad de Regresión

### 5.1. Estadísticas Consolidadas de la Suite de Regresión
- **Total de Casos de Regresión Especificados:** 12 casos (`CPR-001` a `CPR-012`).
- **Casos en Estado VERIFICADO:** 12 casos (**100.0 %**).
- **Casos Aprobados (PASS):** 12 casos (**100.0 %**).
- **Casos Fallidos (FAIL):** 0 casos (**0.0 %**).
- **Casos Pendientes:** 0 casos (**0.0 %**).

### 5.2. Cobertura de Defectos Históricos
- **Total Defectos Marcados como CORREGIDO:** 6 defectos (`DEF-006` a `DEF-011`).
- **Defectos con Caso de Regresión Asignado y Aprobado:** 6 defectos (**100.0 % de cobertura de defectos históricos**).

### 5.3. Conclusión de la Auditoría de Regresión
La ejecución y verificación exhaustiva de la suite de regresión confirma que **ninguna de las correcciones históricas aplicadas al sistema SuperMarket ha sufrido regresiones**. El esquema de base de datos se mantiene limpio y portable, los mecanismos de autenticación y claves unificadas operan de forma predecible, y el núcleo transaccional de ventas, inventario y reportes financieros se mantiene blindado contra anomalías de cómputo y accesos no autorizados.

---

## 6. Procedimiento Operativo Estándar (SOP) para Futuros Ciclos de Regresión

Para los evaluadores de titulación y desarrolladores que den mantenimiento al sistema en el futuro, se define el siguiente procedimiento estandarizado para volver a ejecutar la batería de regresión:

```bash
# 1. Comprobar requisitos de entorno y dependencias del sistema
composer check-platform-reqs

# 2. Ejecutar la suite completa de pruebas automatizadas (41 pruebas)
php artisan test

# 3. Ejecutar específicamente la batería de regresión de inventario y transacciones
php artisan test tests/Feature/PedidoEliminarAnuladoTest.php
php artisan test tests/Feature/PedidoAnulacionStockTest.php
php artisan test tests/Feature/ReporteVentasMetricasTest.php
php artisan test tests/Feature/CategoriaIntegrityTest.php
php artisan test tests/Feature/ProveedorTest.php
php artisan test tests/Feature/PedidoInvitadoYDescargasTest.php
```
