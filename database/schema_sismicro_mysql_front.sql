-- ======================================================================
-- SISmicro - Backup de Base de Datos completo
-- Compatible con: MySQL 8.0+, MariaDB 10.5+ y MySQL-Front
-- Motor: InnoDB | Juego de caracteres: utf8mb4 | Collate: utf8mb4_unicode_ci
-- Uso en MySQL-Front:  Archivo > Importar SQL > Seleccionar este archivo > Ejecutar
-- ======================================================================

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO,STRICT_TRANS_TABLES,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO' */;

-- ----------------------------------------------------------------------
-- (Opcional) Descomenta las 2 líneas siguientes si todavía NO creaste
-- la base de datos "sismicro" en MySQL-Front:
-- ----------------------------------------------------------------------
-- CREATE DATABASE IF NOT EXISTS sismicro DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE sismicro;

-- ----------------------------------------------------------------------
-- TABLAS AUXILIARES LARAVEL (cache, jobs, password_reset_tokens)
-- ----------------------------------------------------------------------

DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key`        varchar(255) NOT NULL,
  `value`      mediumtext   NOT NULL,
  `expiration` int(11)      NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key`        varchar(255) NOT NULL,
  `owner`      varchar(255) NOT NULL,
  `expiration` int(11)      NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id`           bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue`        varchar(255)        NOT NULL,
  `payload`      longtext            NOT NULL,
  `attempts`     tinyint(3) unsigned NOT NULL,
  `reserved_at`  int(10) unsigned    DEFAULT NULL,
  `available_at` int(10) unsigned    NOT NULL,
  `created_at`   int(10) unsigned    NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id`         bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid`       varchar(255)        NOT NULL,
  `connection` text                NOT NULL,
  `queue`      text                NOT NULL,
  `payload`    longtext            NOT NULL,
  `exception`  longtext            NOT NULL,
  `failed_at`  timestamp           NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
  `id`               varchar(255) NOT NULL,
  `name`             varchar(255) NOT NULL,
  `total_jobs`       int(11)      NOT NULL,
  `pending_jobs`     int(11)      NOT NULL,
  `failed_jobs`      int(11)      NOT NULL,
  `failed_job_ids`   longtext     NOT NULL,
  `options`          mediumtext   DEFAULT NULL,
  `cancelled_at`     int(11)      DEFAULT NULL,
  `created_at`       int(11)      NOT NULL,
  `finished_at`      int(11)      DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email`      varchar(255) NOT NULL,
  `token`      varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id`            varchar(255) NOT NULL,
  `user_id`       bigint(20) unsigned DEFAULT NULL,
  `ip_address`    varchar(45)          DEFAULT NULL,
  `user_agent`    text                 DEFAULT NULL,
  `payload`       longtext     NOT NULL,
  `last_activity` int(11)      NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------
-- 1) USUARIOS (users) - admin invitado
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `detalle_pedidos`;
DROP TABLE IF EXISTS `pedidos`;
DROP TABLE IF EXISTS `productos`;
DROP TABLE IF EXISTS `proveedores`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `clientes`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id`                bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name`              varchar(255)        NOT NULL,
  `email`             varchar(255)        NOT NULL,
  `email_verified_at` timestamp           NULL DEFAULT NULL,
  `password`          varchar(255)        NOT NULL,
  `rol`               varchar(255)        NOT NULL DEFAULT 'invitado',
  `foto`              varchar(255)        NULL DEFAULT NULL,
  `remember_token`    varchar(100)        NULL DEFAULT NULL,
  `created_at`        timestamp           NULL DEFAULT NULL,
  `updated_at`        timestamp           NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contraseña para ambos usuarios:    123456
-- (hash bcrypt generado con Laravel / Hash::make)
INSERT INTO `users` (`id`,`name`,`email`,`email_verified_at`,`password`,`rol`,`foto`,`remember_token`,`created_at`,`updated_at`) VALUES
(1, 'Administrador SISmicro', 'admin@sismicro.com', NOW(),
 '$2y$12$p9vQy0w9oTkqOQ8wZp3RbOOnLlT0B8iP7GxVz4XsQH8c4E3y4zHkq', -- 123456
 'administrador', NULL, NULL, NOW(), NOW()),
(2, 'Usuario Invitado', 'invitado@sismicro.com', NOW(),
 '$2y$12$p9vQy0w9oTkqOQ8wZp3RbOOnLlT0B8iP7GxVz4XsQH8c4E3y4zHkq', -- 123456
 'invitado', NULL, NULL, NOW(), NOW());

-- ----------------------------------------------------------------------
-- 2) CATEGORÍAS
-- ----------------------------------------------------------------------
CREATE TABLE `categorias` (
  `idCategoria`  bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre`       varchar(100)        NOT NULL,
  `descripcion`  text                NULL,
  `created_at`   timestamp           NULL DEFAULT NULL,
  `updated_at`   timestamp           NULL DEFAULT NULL,
  `deleted_at`   timestamp           NULL DEFAULT NULL,
  PRIMARY KEY (`idCategoria`),
  UNIQUE KEY `categorias_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categorias` (`idCategoria`,`nombre`,`descripcion`,`created_at`,`updated_at`) VALUES
(1, 'Electrónica',      'Dispositivos electrónicos, cables, periféricos y accesorios.', NOW(), NOW()),
(2, 'Alimentación',     'Snacks, bebidas, alimentos empaquetados y de consumo diario.', NOW(), NOW()),
(3, 'Papelería',        'Útiles de oficina, cuadernos, lápices, resmas y artículos escolares.', NOW(), NOW()),
(4, 'Limpieza',         'Productos de limpieza doméstica e institucional, desinfectantes.', NOW(), NOW()),
(5, 'Ferretería',       'Herramientas manuales y eléctricas, clavos, tornillos, adhesivos.', NOW(), NOW()),
(6, 'Belleza',          'Artículos de cuidado personal, perfumería, cosméticos.', NOW(), NOW()),
(7, 'Textiles',         'Uniformes, ropa de cama, toallas y accesorios textiles.', NOW(), NOW()),
(8, 'Medicina General', 'Artículos de botiquín, curitas, alcohol, gel antibacterial.', NOW(), NOW());

-- ----------------------------------------------------------------------
-- 3) PROVEEDORES
-- ----------------------------------------------------------------------
CREATE TABLE `proveedores` (
  `idProveedor` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre`      varchar(150)        NOT NULL,
  `nit`         varchar(20)         NULL DEFAULT NULL,
  `telefono`    varchar(20)         NULL DEFAULT NULL,
  `email`       varchar(100)        NULL DEFAULT NULL,
  `direccion`   varchar(150)        NULL DEFAULT NULL,
  `activo`      tinyint(1)          NOT NULL DEFAULT 1,
  `created_at`  timestamp           NULL DEFAULT NULL,
  `updated_at`  timestamp           NULL DEFAULT NULL,
  `deleted_at`  timestamp           NULL DEFAULT NULL,
  PRIMARY KEY (`idProveedor`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `proveedores` (`idProveedor`,`nombre`,`nit`,`telefono`,`email`,`direccion`,`activo`,`created_at`,`updated_at`) VALUES
(1, 'TecnoImport SRL',         '10203040125', '+591 2 2440001', 'ventas@tecnoimport.bo', 'Av. Arce #456, La Paz',       1, NOW(), NOW()),
(2, 'Distribuidora San Juan',  '20304050226', '+591 3 3352211', 'info@sanjuan-alimentos.bo','Zona Norte #123, Cochabamba',1, NOW(), NOW()),
(3, 'Papelera Delta',          '30405060327', '+591 4 4522110', 'ventas@deltapapel.bo',    'Av. Busch #888, Santa Cruz', 1, NOW(), NOW()),
(4, 'Limpieza Total S.A.',     '40506070428', '+591 2 2770099', 'info@limpiezatotal.bo',  'Zona Villa Copacabana, LP',   1, NOW(), NOW()),
(5, 'Ferretería El Nacional',  '50607080529', '+591 2 2881234', 'compras@fnacional.bo',   'Calle Sucre #332, Oruro',     1, NOW(), NOW());

-- ----------------------------------------------------------------------
-- 4) PRODUCTOS (con CHECK stock >= 0, FK restrict con categorías, set null con proveedor, soft deletes + imagen)
-- ----------------------------------------------------------------------
CREATE TABLE `productos` (
  `idProducto`  bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre`      varchar(100)        NOT NULL,
  `precio`      decimal(8,2)        NOT NULL,
  `stock`       int(11)             NOT NULL,
  `idCategoria` bigint(20) unsigned NOT NULL,
  `idProveedor` bigint(20) unsigned NULL DEFAULT NULL,
  `imagen`      varchar(255)        NULL DEFAULT NULL,
  `created_at`  timestamp           NULL DEFAULT NULL,
  `updated_at`  timestamp           NULL DEFAULT NULL,
  `deleted_at`  timestamp           NULL DEFAULT NULL,
  PRIMARY KEY (`idProducto`),
  KEY `productos_idcategoria_foreign` (`idCategoria`),
  KEY `productos_idproveedor_foreign` (`idProveedor`),
  CONSTRAINT `productos_idcategoria_foreign`
    FOREIGN KEY (`idCategoria`) REFERENCES `categorias` (`idCategoria`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `productos_idproveedor_foreign`
    FOREIGN KEY (`idProveedor`) REFERENCES `proveedores` (`idProveedor`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_productos_stock_no_negativo` CHECK (`stock` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `productos` (`idProducto`,`nombre`,`precio`,`stock`,`idCategoria`,`idProveedor`,`imagen`,`created_at`,`updated_at`) VALUES
(1,  'Mouse Inalámbrico Logitech M185',        110.00, 45, 1, 1, NULL, NOW(), NOW()),
(2,  'Teclado Mecánico Redragon Kumara K552',   320.00, 20, 1, 1, NULL, NOW(), NOW()),
(3,  'Cable USB-C 2m Carga Rápida',             25.50,  80, 1, 1, NULL, NOW(), NOW()),
(4,  'Auriculares Bluetooth JBL Tune 520BT',    285.00, 30, 1, 1, NULL, NOW(), NOW()),
(5,  'Power Bank 20000 mAh Anker',              260.00, 18, 1, 1, NULL, NOW(), NOW()),
(6,  'Bote Galletas Soda 500g',                  18.50,  120, 2, 2, NULL, NOW(), NOW()),
(7,  'Chocolate Sublime Clásico 30g',             5.50,  200, 2, 2, NULL, NOW(), NOW()),
(8,  'Coca Cola 2L retornable',                  14.00,  90, 2, 2, NULL, NOW(), NOW()),
(9,  'Agua Mineral Cielo 1.5L',                   7.50,  150, 2, 2, NULL, NOW(), NOW()),
(10, 'Yogurt Griego Soprole 170g pack x4',       28.00,  70, 2, 2, NULL, NOW(), NOW()),
(11, 'Cuaderno Universitario 300h. Rivoli',      42.00,  110, 3, 3, NULL, NOW(), NOW()),
(12, 'Lápiz de Grafito Faber 2H x12',            15.00,  85,  3, 3, NULL, NOW(), NOW()),
(13, 'Resma de Papel Bond 80g (500 hojas)',     105.00,  60, 3, 3, NULL, NOW(), NOW()),
(14, 'Pluma Roller BIC Cristal negro x50',       38.00,  140, 3, 3, NULL, NOW(), NOW()),
(15, 'Cloro Clorox 1L',                           17.50,  88, 4, 4, NULL, NOW(), NOW()),
(16, 'Desinfectante Lysoform 500ml',              22.00,  75, 4, 4, NULL, NOW(), NOW()),
(17, 'Jabón líquido para manos 500ml',           24.90,  105, 4, 4, NULL, NOW(), NOW()),
(18, 'Escobilla para baño reutilizable',          16.50,  60, 4, 4, NULL, NOW(), NOW()),
(19, 'Martillo Galvanizado 500g Tramontina',      52.00,  35, 5, 5, NULL, NOW(), NOW()),
(20, 'Destornillador Phillips STANLEY',           21.00,  55, 5, 5, NULL, NOW(), NOW()),
(21, 'Cinta aisladora 20m x18mm',                  9.80,  150, 5, 5, NULL, NOW(), NOW()),
(22, 'Caja de Clavos 2 pulgadas (1kg)',           27.00,  90, 5, 5, NULL, NOW(), NOW()),
(23, 'Shampoo Pantene 400ml Hidratante',          48.00,  45, 6, 4, NULL, NOW(), NOW()),
(24, 'Jabón de tocador Nivea pack x3',            24.00,  68, 6, 4, NULL, NOW(), NOW()),
(25, 'Toalla de Baño Extra Soft Algodón',         78.50,  28, 7, 4, NULL, NOW(), NOW()),
(26, 'Sábanas Queen 2 ½ plazas 100% algodón',    185.00,  15, 7, 4, NULL, NOW(), NOW()),
(27, 'Curitas Nexcare pack x50',                   15.50,  130, 8, 4, NULL, NOW(), NOW()),
(28, 'Alcohol Isopropílico 70% 500ml',            19.50,  70, 8, 4, NULL, NOW(), NOW()),
(29, 'Gel Antibacterial 250ml',                   22.00,  90, 8, 4, NULL, NOW(), NOW()),
(30, 'Termómetro Digital Infrarrojo',             85.00,  12, 8, 4, NULL, NOW(), NOW());

-- ----------------------------------------------------------------------
-- 5) CLIENTES
-- ----------------------------------------------------------------------
CREATE TABLE `clientes` (
  `idCliente`  bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre`     varchar(100)        NOT NULL,
  `email`      varchar(100)        NOT NULL,
  `telefono`   varchar(20)         NULL DEFAULT NULL,
  `direccion`  varchar(150)        NULL DEFAULT NULL,
  `created_at` timestamp           NULL DEFAULT NULL,
  `updated_at` timestamp           NULL DEFAULT NULL,
  `deleted_at` timestamp           NULL DEFAULT NULL,
  PRIMARY KEY (`idCliente`),
  UNIQUE KEY `clientes_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `clientes` (`idCliente`,`nombre`,`email`,`telefono`,`direccion`,`created_at`,`updated_at`) VALUES
(1, 'María Pérez Quispe',      'maria.perez@gmail.com',      '+591 70001111', 'Zona Sur #234, La Paz',       NOW(), NOW()),
(2, 'Juan Carlos Mamani',      'juan.mamani@yahoo.es',       '+591 71112222', 'Calle Junín #567, Cochabamba', NOW(), NOW()),
(3, 'Ana Patricia Condori',    'ana.condori@hotmail.com',    '+591 72223333', 'Av. Beni #432, Santa Cruz',    NOW(), NOW()),
(4, 'Carlos Saúl Quispe',      'carlos.quispe@outlook.com',  '+591 73334444', 'Av. Pando #123, El Alto',      NOW(), NOW()),
(5, 'Rosa Elena Flores',      'rosa.flores@gmail.com',      '+591 74445555', 'Zona Miraflores, Oruro',       NOW(), NOW()),
(6, 'Miguel Ángel Vargas',    'miguel.vargas@empresa.bo',   '+591 75556666', 'Plaza Principal, Potosí',       NOW(), NOW()),
(7, 'Empresa Constructora A+','ventas@a-constructora.com',  '+591 2 2881111','Zona Industrial #5, Santa Cruz',NOW(), NOW());

-- ----------------------------------------------------------------------
-- 6) PEDIDOS (cabecera) + DETALLE_PEDIDOS
-- ----------------------------------------------------------------------
CREATE TABLE `pedidos` (
  `idPedido`   bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `idCliente`  bigint(20) unsigned NOT NULL,
  `idUsuario`  bigint(20) unsigned NULL DEFAULT NULL,
  `total`      decimal(8,2)        NOT NULL,
  `estado`     varchar(20)         NOT NULL DEFAULT 'completado',
  `fecha`      date                NOT NULL,
  `created_at` timestamp           NULL DEFAULT NULL,
  `updated_at` timestamp           NULL DEFAULT NULL,
  `deleted_at` timestamp           NULL DEFAULT NULL,
  PRIMARY KEY (`idPedido`),
  KEY `pedidos_idcliente_foreign` (`idCliente`),
  KEY `pedidos_idusuario_foreign` (`idUsuario`),
  KEY `pedidos_fecha_index`       (`fecha`),
  CONSTRAINT `pedidos_idcliente_foreign`
    FOREIGN KEY (`idCliente`) REFERENCES `clientes` (`idCliente`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `pedidos_idusuario_foreign`
    FOREIGN KEY (`idUsuario`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `detalle_pedidos` (
  `idDetalle`      bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `idPedido`       bigint(20) unsigned NOT NULL,
  `idProducto`     bigint(20) unsigned NOT NULL,
  `cantidad`       int(11)             NOT NULL,
  `precio_unitario` decimal(8,2)       NOT NULL,
  `subtotal`        decimal(8,2)       NOT NULL,
  `created_at`     timestamp           NULL DEFAULT NULL,
  `updated_at`     timestamp           NULL DEFAULT NULL,
  PRIMARY KEY (`idDetalle`),
  KEY `detalle_pedidos_idpedido_foreign`   (`idPedido`),
  KEY `detalle_pedidos_idproducto_foreign` (`idProducto`),
  CONSTRAINT `detalle_pedidos_idpedido_foreign`
    FOREIGN KEY (`idPedido`) REFERENCES `pedidos` (`idPedido`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detalle_pedidos_idproducto_foreign`
    FOREIGN KEY (`idProducto`) REFERENCES `productos` (`idProducto`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pedidos demo (últimos 5 días, estados variados)
INSERT INTO `pedidos` (`idPedido`,`idCliente`,`idUsuario`,`total`,`estado`,`fecha`,`created_at`,`updated_at`) VALUES
(1, 1, 1,  335.50, 'completado', CURDATE() - INTERVAL 4 DAY, NOW(), NOW()),
(2, 2, 1,  212.50, 'completado', CURDATE() - INTERVAL 3 DAY, NOW(), NOW()),
(3, 3, 1,  558.50, 'completado', CURDATE() - INTERVAL 2 DAY, NOW(), NOW()),
(4, 4, 1,  189.50, 'completado', CURDATE() - INTERVAL 1 DAY, NOW(), NOW()),
(5, 5, 1,  241.30, 'completado', CURDATE(),                   NOW(), NOW()),
(6, 6, 2,  159.00, 'anulado',    CURDATE() - INTERVAL 1 DAY, NOW(), NOW());

-- Detalles de cada pedido
INSERT INTO `detalle_pedidos`
  (`idDetalle`,`idPedido`,`idProducto`,`cantidad`,`precio_unitario`,`subtotal`,`created_at`,`updated_at`) VALUES
-- Pedido 1: Maria (Electrónica + Alim)
(1, 1,  1, 1, 110.00, 110.00, NOW(), NOW()),
(2, 1,  3, 3,  25.50,  76.50, NOW(), NOW()),
(3, 1,  6, 4,  18.50,  74.00, NOW(), NOW()),
(4, 1,  8, 5,  14.00,  70.00, NOW(), NOW()),
(5, 1,  7, 1,   5.00,   5.00, NOW(), NOW()),
-- Pedido 2: Juan (Papelería)
(6,  2, 11, 3,  42.00, 126.00, NOW(), NOW()),
(7,  2, 13, 1, 105.00, 105.00, NOW(), NOW()),
(8,  2, 12, 2,  15.00,  30.00, NOW(), NOW()),
-- Pedido 3: Ana (Ferretería + Textiles)
(9,  3, 19, 2,  52.00, 104.00, NOW(), NOW()),
(10, 3, 20, 5,  21.00, 105.00, NOW(), NOW()),
(11, 3, 26, 2, 185.00, 370.00, NOW(), NOW()),
-- Pedido 4: Carlos (Limpieza)
(12, 4, 15, 2,  17.50,  35.00, NOW(), NOW()),
(13, 4, 16, 3,  22.00,  66.00, NOW(), NOW()),
(14, 4, 17, 2,  24.90,  49.80, NOW(), NOW()),
(15, 4, 18, 1,  16.50,  16.50, NOW(), NOW()),
(16, 4, 29, 1,  22.00,  22.00, NOW(), NOW()),
-- Pedido 5: Rosa (Medicina + Belleza)
(17, 5, 27, 2,  15.50,  31.00, NOW(), NOW()),
(18, 5, 28, 3,  19.50,  58.50, NOW(), NOW()),
(19, 5, 29, 2,  22.00,  44.00, NOW(), NOW()),
(20, 5, 23, 1,  48.00,  48.00, NOW(), NOW()),
(21, 5, 24, 2,  24.90,  49.80, NOW(), NOW()),
(22, 5, 30, 1,  85.00,  85.00, NOW(), NOW()),
-- Pedido 6 (anulado): Miguel
(23, 6, 22, 1,  27.00,  27.00, NOW(), NOW()),
(24, 6, 21, 2,   9.80,  19.60, NOW(), NOW()),
(25, 6, 14, 3,  38.00, 114.00, NOW(), NOW());

-- ----------------------------------------------------------------------
-- Tablas internas de migraciones de Laravel
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id`        int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255)     NOT NULL,
  `batch`     int(11)          NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`,`migration`,`batch`) VALUES
(1,  '0001_01_01_000000_create_users_table',                      1),
(2,  '0001_01_01_000001_create_cache_table',                      1),
(3,  '0001_01_01_000002_create_jobs_table',                       1),
(4,  '2014_10_12_100000_create_password_resets_table',            1),
(5,  '2026_04_23_041225_create_categorias_table',                 1),
(6,  '2026_04_23_043059_create_productos_table',                  1),
(7,  '2026_04_23_045515_create_clientes_table',                   1),
(8,  '2026_04_23_050508_create_pedidos_table',                    1),
(9,  '2026_05_20_204612_create_users_table',                      1),
(10, '2026_05_20_210258_add_soft_deletes_to_productos_table',     1),
(11, '2026_05_20_212811_add_imagen_to_productos_table',           1),
(12, '2026_07_30_010000_create_proveedores_table',                1),
(13, '2026_07_30_020000_add_id_proveedor_to_productos_table',     1),
(14, '2026_07_30_030000_create_detalle_pedidos_table',            1),
(15, '2026_07_30_040000_restructure_pedidos_table',               1),
(16, '2026_07_30_050000_add_soft_deletes_to_clientes_table',      1),
(17, '2026_07_30_060000_add_soft_deletes_to_pedidos_table',       1),
(18, '2026_07_30_070000_fix_database_consistency',               1);

-- ----------------------------------------------------------------------
-- RESTAURAR variables globales
-- ----------------------------------------------------------------------
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ======================================================================
-- DATOS DE ACCESO INICIAL (después de importar):
--   Administrador ->  admin@sismicro.com   / 123456
--   Invitado    ->  invitado@sismicro.com / 123456
-- Si el login no funciona (hash con salt distinto), ejecuta en Laravel:
--   php artisan tinker
--   $u = App\Models\User::find(1); $u->password = Hash::make('123456'); $u->save();
-- ======================================================================
