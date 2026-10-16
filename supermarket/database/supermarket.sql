-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
-- DEPRECADO: instantánea histórica; no importar en instalaciones actuales.
-- No incluye lotes de inventario/vencimientos ni la eliminación de proveedores.
-- Usa `php artisan migrate --seed` como fuente de verdad del esquema actual.
--
-- Host: localhost    Database: supermarket
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `supermarket`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `supermarket` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `supermarket`;

--
-- Table structure for table `api_tokens`
--

DROP TABLE IF EXISTS `api_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_tokens` (
  `idToken` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `idUsuario` bigint(20) unsigned NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `nombre_dispositivo` varchar(100) DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`idToken`),
  UNIQUE KEY `api_tokens_token_hash_unique` (`token_hash`),
  KEY `api_tokens_idusuario_foreign` (`idUsuario`),
  CONSTRAINT `api_tokens_idusuario_foreign` FOREIGN KEY (`idUsuario`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_tokens`
--

LOCK TABLES `api_tokens` WRITE;
/*!40000 ALTER TABLE `api_tokens` DISABLE KEYS */;
INSERT INTO `api_tokens` VALUES (1,1,'4faf6933181465a8e87ce6b3519d6bd221fa441c200cc32c5b108864896c9a08','app-movil','2026-10-06 10:18:28','2026-10-06 10:13:11','2026-10-06 10:18:28'),(9,1,'f33ca1b804c4266f4c1c32ff7c73db6546876b11cbb66cd63c8173aed43f3802','app-movil','2026-10-12 20:39:03','2026-10-12 20:27:09','2026-10-12 20:39:03'),(18,1,'9e71fd89708a986f13f417fe68a33e9078321d0530f7c7c78717d76d52848828',NULL,'2026-10-12 21:26:11','2026-10-12 21:26:11','2026-10-12 21:26:11'),(25,1,'29ebf59e2178f7ba8a5fc5822fcac074ba6d7d6b722c6fd2c0ab6ca7f84998d5','app-movil','2026-10-13 00:47:38','2026-10-12 22:05:19','2026-10-13 00:47:38'),(35,1,'07cbbc17f14dbba17faea7a3c7529c692b4a73b9e30350c735d3a307f0f3b0b8','app-movil','2026-10-13 03:13:52','2026-10-13 03:05:32','2026-10-13 03:13:52'),(48,1,'2372749d1d243a8ece87b65593f3b786bd67bb5d4eb2589d23d9c2737ea1b065','app-movil','2026-10-13 05:38:28','2026-10-13 05:38:20','2026-10-13 05:38:28'),(49,1,'b2a4f1c5224043fe8b7625a529e1c927b23bb7a58274ee2f314e8a01681b300c','app-movil','2026-10-13 08:55:36','2026-10-13 08:26:26','2026-10-13 08:55:36');
/*!40000 ALTER TABLE `api_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('supermarket-cache-api-login:dueno@supermakert.com|192.168.100.216','i:1;',1791845569),('supermarket-cache-api-login:dueno@supermakert.com|192.168.100.216:timer','i:1791845569;',1791845569),('supermarket-cache-api-login:dueno@supermarkert.com|192.168.100.216','i:1;',1791845584),('supermarket-cache-api-login:dueno@supermarkert.com|192.168.100.216:timer','i:1791845584;',1791845584);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categorias` (
  `idCategoria` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`idCategoria`),
  UNIQUE KEY `categorias_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (1,'Abarrotes','Arroz, azúcar, aceite, fideo y productos de despensa en general.','2026-07-31 12:00:00','2026-10-13 08:26:59',NULL),(2,'Lácteos','Yogurt, Quesos y derivados','2026-07-31 12:00:00','2026-09-03 22:45:04',NULL),(3,'Panadería','Pan, galletas y productos de repostería.','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(4,'Bebidas','Gaseosas, jugos, agua y bebidas en general.','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(5,'Limpieza','Detergentes, desinfectantes y artículos de limpieza del hogar.','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(6,'Higiene Personal','Jabón, shampoo, pasta dental y cuidado personal.','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(7,'Snacks','Papas fritas, chocolates y golosinas.','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(8,'Carnes y Fiambres','Embutidos y productos cárnicos procesados.','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(9,'Bebidas Alcoholicas','Cerveza, Vinos','2026-09-03 12:31:58','2026-09-03 22:48:01',NULL),(15,'Postres','Masitas en gral','2026-09-04 00:09:34','2026-09-04 00:09:34',NULL),(16,'Frituras','Papas Fritas Etc','2026-09-04 00:37:44','2026-09-04 00:37:44',NULL),(17,'Material Escolar','Lapices, cuadernos, etc','2026-10-12 21:10:19','2026-10-12 21:10:19',NULL),(18,'Dulces','Golosinas, dulces,etc','2026-10-13 00:41:27','2026-10-13 00:41:27',NULL);
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clientes` (
  `idCliente` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`idCliente`),
  UNIQUE KEY `clientes_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'Rosa Fernández Choque','rosa.fernandez@example.com','70011122','Av. Ayacucho #245, Cochabamba','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(2,'Marco Antonio Villca','marco.villca@example.com','70022233','Calle Bolívar #560, Cochabamba','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(3,'Elizabeth Mamani Rojas','eli.mamani@example.com','70033344','Zona Queru Queru, Cochabamba','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(4,'Juan Carlos Terrazas','jc.terrazas@example.com','70044455','Av. Heroínas #1120, Cochabamba','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(5,'Patricia Gutiérrez Soliz','patricia.gutierrez@example.com','70055566','Zona Cala Cala, Cochabamba','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(6,'David Quiroga Vargas','david.quiroga@example.com','70066677','Av. Circunvalación, Cochabamba','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(7,'a                                                 s','qwda@asda.com','60397508','asqdwqd','2026-09-03 12:34:19','2026-09-03 12:35:13','2026-09-03 12:35:13'),(8,'Juan','juan@supermarket.com','63037855','Heroinas','2026-09-03 22:50:36','2026-09-03 22:50:36',NULL),(9,'Ana lopez','ana@supermarket.com','61600923','Heroinas Y Lanza','2026-09-04 00:10:17','2026-10-13 08:27:24',NULL),(10,'Pepe','pepe@supermarket','15652346','Lanza','2026-09-04 00:38:27','2026-09-04 00:38:27',NULL),(11,'Anabel','anabel@gmail.com',NULL,NULL,'2026-10-13 00:42:05','2026-10-13 00:42:17',NULL),(12,'Cliente Comun','client@supermarket.com',NULL,NULL,'2026-10-13 03:27:08','2026-10-13 03:27:08',NULL);
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_ventas`
--

DROP TABLE IF EXISTS `detalle_ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalle_ventas` (
  `idDetalle` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `idVenta` bigint(20) unsigned NOT NULL,
  `idProducto` bigint(20) unsigned NOT NULL,
  `cantidad` int(10) unsigned NOT NULL,
  `precio_unitario` decimal(8,2) NOT NULL,
  `subtotal` decimal(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`idDetalle`),
  UNIQUE KEY `detalle_ventas_venta_producto_unique` (`idVenta`,`idProducto`),
  KEY `detalle_ventas_idventa_foreign` (`idVenta`),
  KEY `detalle_ventas_idproducto_foreign` (`idProducto`),
  CONSTRAINT `detalle_ventas_idventa_foreign` FOREIGN KEY (`idVenta`) REFERENCES `ventas` (`idVenta`) ON DELETE CASCADE,
  CONSTRAINT `detalle_ventas_idproducto_foreign` FOREIGN KEY (`idProducto`) REFERENCES `productos` (`idProducto`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_ventas`
--

LOCK TABLES `detalle_ventas` WRITE;
/*!40000 ALTER TABLE `detalle_ventas` DISABLE KEYS */;
INSERT INTO `detalle_ventas` VALUES (1,1,1,3,9.50,28.50,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(2,1,3,2,14.00,28.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(3,1,2,2,7.80,15.60,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(4,2,5,6,8.50,51.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(5,2,8,10,0.50,5.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(6,3,10,4,13.00,52.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(7,3,18,5,6.00,30.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(8,3,19,8,2.50,20.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(9,4,13,2,15.50,31.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(10,4,14,3,6.00,18.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(11,5,7,2,22.00,44.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(12,5,6,4,12.00,48.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(13,6,15,6,3.50,21.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(14,6,16,2,18.00,36.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(15,6,17,3,9.00,27.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(16,7,4,4,6.50,26.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(17,7,9,3,5.00,15.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(18,8,11,5,7.00,35.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(19,8,12,2,10.50,21.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(20,9,20,2,16.00,32.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(21,9,8,6,0.50,3.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(22,10,10,3,13.00,39.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(23,10,1,2,9.50,19.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(24,10,5,4,8.50,34.00,'2026-07-31 12:00:00','2026-07-31 12:00:00'),(25,11,11,1,7.00,7.00,'2026-09-03 12:35:37','2026-09-03 12:35:37'),(26,11,15,1,3.50,3.50,'2026-09-03 12:35:37','2026-09-03 12:35:37'),(27,12,2,1,7.80,7.80,'2026-09-03 22:55:06','2026-09-03 22:55:06'),(28,12,10,1,13.00,13.00,'2026-09-03 22:55:06','2026-09-03 22:55:06'),(29,12,23,1,75.00,75.00,'2026-09-03 22:55:06','2026-09-03 22:55:06'),(30,12,16,1,18.00,18.00,'2026-09-03 22:55:06','2026-09-03 22:55:06'),(31,13,30,1,20.00,20.00,'2026-09-04 00:11:05','2026-09-04 00:11:05'),(32,13,29,1,20.00,20.00,'2026-09-04 00:11:05','2026-09-04 00:11:05'),(33,14,29,1,20.00,20.00,'2026-09-04 00:39:13','2026-09-04 00:39:13'),(34,14,16,1,18.00,18.00,'2026-09-04 00:39:13','2026-09-04 00:39:13'),(35,14,10,1,13.00,13.00,'2026-09-04 00:39:13','2026-09-04 00:39:13'),(37,15,11,1,7.00,7.00,'2026-09-15 17:14:27','2026-09-15 17:14:27'),(38,15,10,1,13.00,13.00,'2026-09-15 17:14:27','2026-09-15 17:14:27'),(39,16,5,1,8.50,8.50,'2026-10-06 10:14:14','2026-10-06 10:14:14'),(40,16,10,1,13.00,13.00,'2026-10-06 10:14:14','2026-10-06 10:14:14'),(45,17,11,1,7.00,7.00,'2026-10-11 16:01:56','2026-10-11 16:01:56'),(46,17,29,1,20.00,20.00,'2026-10-11 16:01:56','2026-10-11 16:01:56'),(47,17,8,1,0.50,0.50,'2026-10-11 16:01:56','2026-10-11 16:01:56'),(48,17,7,1,22.00,22.00,'2026-10-11 16:01:56','2026-10-11 16:01:56'),(49,18,3,1,14.00,14.00,'2026-10-11 17:29:31','2026-10-11 17:29:31'),(50,18,19,1,2.50,2.50,'2026-10-11 17:29:31','2026-10-11 17:29:31'),(51,18,13,1,15.50,15.50,'2026-10-11 17:29:31','2026-10-11 17:29:31'),(52,19,11,1,7.00,7.00,'2026-10-12 20:22:56','2026-10-12 20:22:56'),(53,19,15,1,3.50,3.50,'2026-10-12 20:22:56','2026-10-12 20:22:56'),(54,19,13,1,15.50,15.50,'2026-10-12 20:22:56','2026-10-12 20:22:56'),(55,20,10,1,13.00,13.00,'2026-10-12 20:27:52','2026-10-12 20:27:52'),(56,20,18,1,6.00,6.00,'2026-10-12 20:27:52','2026-10-12 20:27:52'),(57,20,7,1,22.00,22.00,'2026-10-12 20:27:52','2026-10-12 20:27:52'),(59,21,7,3,22.00,66.00,'2026-10-12 20:30:55','2026-10-12 20:30:55'),(60,22,7,4,22.00,88.00,'2026-10-12 22:49:32','2026-10-12 22:49:32'),(61,23,13,7,15.50,108.50,'2026-10-12 22:53:25','2026-10-12 22:53:25'),(62,24,7,8,22.00,176.00,'2026-10-12 22:57:43','2026-10-12 22:57:43'),(64,26,17,5,9.00,45.00,'2026-10-13 00:50:48','2026-10-13 00:50:48'),(65,27,3,8,14.00,112.00,'2026-10-13 00:56:28','2026-10-13 00:56:28'),(66,28,7,5,22.00,110.00,'2026-10-13 01:03:40','2026-10-13 01:03:40'),(67,29,7,4,22.00,88.00,'2026-10-13 01:06:08','2026-10-13 01:06:08'),(68,29,10,3,13.00,39.00,'2026-10-13 01:06:08','2026-10-13 01:06:08'),(69,29,14,2,6.00,12.00,'2026-10-13 01:06:08','2026-10-13 01:06:08'),(70,30,7,4,22.00,88.00,'2026-10-13 01:17:39','2026-10-13 01:17:39'),(71,31,17,5,9.00,45.00,'2026-10-13 03:23:58','2026-10-13 03:23:58'),(72,31,8,3,0.50,1.50,'2026-10-13 03:23:58','2026-10-13 03:23:58'),(73,31,5,3,8.50,25.50,'2026-10-13 03:23:58','2026-10-13 03:23:58'),(74,31,18,4,6.00,24.00,'2026-10-13 03:23:58','2026-10-13 03:23:58'),(75,32,3,5,14.00,70.00,'2026-10-13 03:27:24','2026-10-13 03:27:24'),(76,32,7,3,22.00,66.00,'2026-10-13 03:27:24','2026-10-13 03:27:24'),(77,33,3,4,14.00,56.00,'2026-10-13 04:58:42','2026-10-13 04:58:42'),(78,33,12,2,10.50,21.00,'2026-10-13 04:58:42','2026-10-13 04:58:42'),(79,33,9,1,5.00,5.00,'2026-10-13 04:58:42','2026-10-13 04:58:42'),(80,33,13,2,15.50,31.00,'2026-10-13 04:58:42','2026-10-13 04:58:42'),(81,33,18,2,6.00,12.00,'2026-10-13 04:58:42','2026-10-13 04:58:42'),(82,34,18,5,6.00,30.00,'2026-10-13 05:05:59','2026-10-13 05:05:59'),(83,34,29,5,20.00,100.00,'2026-10-13 05:05:59','2026-10-13 05:05:59'),(84,35,7,6,22.00,132.00,'2026-10-13 05:22:54','2026-10-13 05:22:54'),(85,35,29,4,20.00,80.00,'2026-10-13 05:22:54','2026-10-13 05:22:54'),(86,36,8,1,0.50,0.50,'2026-10-13 08:43:58','2026-10-13 08:43:58'),(87,36,29,6,20.00,120.00,'2026-10-13 08:43:58','2026-10-13 08:43:58');
/*!40000 ALTER TABLE `detalle_ventas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2014_10_12_100000_create_password_resets_table',1),(5,'2026_04_23_041225_create_categorias_table',1),(6,'2026_04_23_043059_create_productos_table',1),(7,'2026_04_23_045515_create_clientes_table',1),(8,'2026_04_23_050508_create_pedidos_table',1),(9,'2026_05_20_210258_add_soft_deletes_to_productos_table',1),(10,'2026_05_20_212811_add_imagen_to_productos_table',1),(11,'2026_07_30_010000_create_proveedores_table',1),(12,'2026_07_30_020000_add_id_proveedor_to_productos_table',1),(13,'2026_07_30_030000_create_detalle_pedidos_table',1),(14,'2026_07_30_040000_restructure_pedidos_table',1),(15,'2026_07_30_050000_add_soft_deletes_to_clientes_table',1),(16,'2026_07_30_060000_add_soft_deletes_to_pedidos_table',1),(17,'2026_07_30_070000_fix_database_consistency',1),(18,'2026_07_31_010000_add_unique_nombre_to_productos_table',1),(19,'2026_07_31_070110_add_rol_and_foto_to_users_table',1),(20,'2026_05_20_204612_create_users_table',2),(21,'2026_10_06_060242_create_api_tokens_table',3),(22,'2026_09_11_054459_add_unique_constraint_to_productos_nombre',4),(23,'2026_09_13_193245_add_estado_fecha_index_to_pedidos',4),(24,'2026_09_14_044922_make_stock_and_cantidad_unsigned',4),(25,'2026_09_25_010000_add_unique_constraint_to_proveedores_nombre',4),(26,'2026_09_25_020000_add_check_precio_no_negativo_to_productos',4),(27,'2026_09_25_030000_add_unique_producto_por_pedido_to_detalle_pedidos',4),(28,'2026_09_25_040000_create_api_tokens_table',5),(29,'2026_10_13_000000_add_numero_to_pedidos_table',5);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ventas`
--

DROP TABLE IF EXISTS `ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ventas` (
  `idVenta` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `numero` int(10) unsigned NOT NULL DEFAULT 0,
  `idCliente` bigint(20) unsigned NOT NULL,
  `idUsuario` bigint(20) unsigned DEFAULT NULL,
  `total` decimal(8,2) NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'completado',
  `fecha` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`idVenta`),
  KEY `ventas_idcliente_foreign` (`idCliente`),
  KEY `ventas_idusuario_foreign` (`idUsuario`),
  KEY `ventas_fecha_index` (`fecha`),
  KEY `ventas_estado_fecha_index` (`estado`,`fecha`),
  CONSTRAINT `ventas_idcliente_foreign` FOREIGN KEY (`idCliente`) REFERENCES `clientes` (`idCliente`),
  CONSTRAINT `ventas_idusuario_foreign` FOREIGN KEY (`idUsuario`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas`
--

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
INSERT INTO `ventas` VALUES (1,1,1,1,72.10,'completado','2026-07-13','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(2,2,2,2,56.00,'completado','2026-07-15','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(3,3,3,1,102.00,'completado','2026-07-17','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(4,4,4,2,49.00,'completado','2026-07-19','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(5,5,5,1,92.00,'anulado','2026-07-21','2026-07-31 12:00:00','2026-10-13 01:02:36','2026-10-13 01:02:36'),(6,5,6,2,84.00,'completado','2026-07-22','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(7,6,1,1,41.00,'completado','2026-07-24','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(8,7,2,1,56.00,'completado','2026-07-26','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(9,8,3,2,35.00,'completado','2026-07-28','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(10,9,4,1,92.00,'completado','2026-07-30','2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(11,10,3,1,10.50,'completado','2026-09-03','2026-09-03 12:35:37','2026-09-03 12:35:37',NULL),(12,11,8,NULL,113.80,'completado','2026-09-03','2026-09-03 22:55:06','2026-09-03 22:55:06',NULL),(13,0,9,NULL,40.00,'completado','2026-09-03','2026-09-04 00:11:05','2026-09-04 00:16:39','2026-09-04 00:16:39'),(14,0,10,NULL,51.00,'completado','2026-09-03','2026-09-04 00:39:13','2026-09-04 00:40:18','2026-09-04 00:40:18'),(15,0,9,1,20.00,'completado','2026-09-15','2026-09-15 17:14:15','2026-09-15 17:14:36','2026-09-15 17:14:36'),(16,12,9,1,21.50,'completado','2026-10-06','2026-10-06 10:14:14','2026-10-13 01:03:12','2026-10-13 01:03:12'),(17,0,9,1,49.50,'anulado','2026-10-11','2026-10-11 16:01:29','2026-10-11 16:01:59','2026-10-11 16:01:59'),(18,0,9,1,32.00,'completado','2026-10-11','2026-10-11 17:29:31','2026-10-11 17:29:39','2026-10-11 17:29:39'),(19,0,9,1,26.00,'completado','2026-10-12','2026-10-12 20:22:56','2026-10-12 22:54:41','2026-10-12 22:54:41'),(20,0,6,1,41.00,'completado','2026-10-12','2026-10-12 20:27:52','2026-10-12 22:52:17','2026-10-12 22:52:17'),(21,0,6,1,66.00,'anulado','2026-10-12','2026-10-12 20:30:40','2026-10-12 20:31:02','2026-10-12 20:31:02'),(22,0,10,1,88.00,'completado','2026-10-12','2026-10-12 22:49:32','2026-10-12 22:50:01','2026-10-12 22:50:01'),(23,0,8,1,108.50,'completado','2026-10-12','2026-10-12 22:53:25','2026-10-12 22:53:39','2026-10-12 22:53:39'),(24,0,10,1,176.00,'completado','2026-10-12','2026-10-12 22:57:43','2026-10-12 22:57:57','2026-10-12 22:57:57'),(26,12,11,1,45.00,'completado','2026-10-12','2026-10-13 00:50:48','2026-10-13 01:03:16','2026-10-13 01:03:16'),(27,15,10,1,112.00,'completado','2026-10-12','2026-10-13 00:56:28','2026-10-13 00:56:43','2026-10-13 00:56:43'),(28,12,10,1,110.00,'completado','2026-10-12','2026-10-13 01:03:40','2026-10-13 01:03:40',NULL),(29,13,1,2,139.00,'completado','2026-10-12','2026-10-13 01:06:08','2026-10-13 01:06:08',NULL),(30,14,5,1,88.00,'completado','2026-10-12','2026-10-13 01:17:39','2026-10-13 01:18:01','2026-10-13 01:18:01'),(31,14,10,1,96.00,'completado','2026-10-12','2026-10-13 03:23:58','2026-10-13 03:23:58',NULL),(32,15,12,1,136.00,'completado','2026-10-12','2026-10-13 03:27:24','2026-10-13 03:27:24',NULL),(33,16,12,1,125.00,'completado','2026-10-13','2026-10-13 04:58:42','2026-10-13 04:58:42',NULL),(34,17,12,6,130.00,'completado','2026-10-13','2026-10-13 05:05:59','2026-10-13 05:05:59',NULL),(35,18,12,1,212.00,'completado','2026-10-13','2026-10-13 05:22:54','2026-10-13 05:22:54',NULL),(36,19,12,1,120.50,'completado','2026-10-13','2026-10-13 08:43:58','2026-10-13 08:43:58',NULL);
/*!40000 ALTER TABLE `ventas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productos` (
  `idProducto` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `precio` decimal(8,2) NOT NULL,
  `stock` int(10) unsigned NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `idCategoria` bigint(20) unsigned NOT NULL,
  `idProveedor` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`idProducto`),
  UNIQUE KEY `productos_nombre_unique` (`nombre`),
  KEY `productos_idcategoria_foreign` (`idCategoria`),
  KEY `productos_idproveedor_foreign` (`idProveedor`),
  CONSTRAINT `productos_idcategoria_foreign` FOREIGN KEY (`idCategoria`) REFERENCES `categorias` (`idCategoria`),
  CONSTRAINT `productos_idproveedor_foreign` FOREIGN KEY (`idProveedor`) REFERENCES `proveedores` (`idProveedor`) ON DELETE SET NULL,
  CONSTRAINT `chk_productos_stock_no_negativo` CHECK (`stock` >= 0),
  CONSTRAINT `chk_productos_precio_no_negativo` CHECK (`precio` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,'Arroz Extra 1kg',9.50,145,'productos/prd_6acd4c8d44441.jpeg',1,1,'2026-07-31 12:00:00','2026-10-12 21:09:33',NULL),(2,'Azúcar Blanca 1kg',7.80,127,'productos/prd_6acdebd440d89.jpeg',1,1,'2026-07-31 12:00:00','2026-10-13 08:29:08',NULL),(3,'Aceite Fino 900ml',14.00,69,'productos/prd_6acd4c4d3feb3.png',1,1,'2026-07-31 12:00:00','2026-10-13 04:58:42',NULL),(4,'Fideo Espagueti 400g',6.50,106,'productos/prd_6acd4d9d6728f.jpeg',1,1,'2026-07-31 12:00:00','2026-10-12 21:14:05',NULL),(5,'Leche PIL Entera 1L',8.50,87,'productos/prd_6acda3dc5f890.jpeg',2,2,'2026-07-31 12:00:00','2026-10-13 03:23:58',NULL),(6,'Yogurt PIL Frutilla 1L',12.00,65,'productos/prd_6acdbd10ee894.jpeg',2,2,'2026-07-31 12:00:00','2026-10-13 05:09:37',NULL),(7,'Queso Criollo 500g',22.00,17,'productos/prd_6acdee31d8faa.jpeg',2,2,'2026-07-31 12:00:00','2026-10-13 08:39:13',NULL),(8,'Pan de Batalla (unidad)',0.50,330,'productos/prd_6acdee0922ba1.jpeg',3,3,'2026-07-31 12:00:00','2026-10-13 08:43:58',NULL),(9,'Galletas María 200g',5.00,86,'productos/prd_6acd4c1c9d97f.jpeg',3,3,'2026-07-31 12:00:00','2026-10-13 04:58:42',NULL),(10,'Coca-Cola 2L',13.00,74,'productos/prd_6acd48c65267b.jpeg',4,4,'2026-07-31 12:00:00','2026-10-13 01:06:08',NULL),(11,'Agua Vital 2L',7.00,104,'productos/prd_6acd4c65acb1b.jpeg',4,4,'2026-07-31 12:00:00','2026-10-12 22:54:41',NULL),(12,'Jugo del Valle 1L',10.50,51,'productos/prd_6acda393344ab.jpeg',4,4,'2026-07-31 12:00:00','2026-10-13 04:58:42',NULL),(13,'Detergente Ace 1kg',15.50,56,'productos/prd_6acda03c9330e.jpeg',5,5,'2026-07-31 12:00:00','2026-10-13 04:58:42',NULL),(14,'Lavandina 1L',6.00,65,'productos/prd_6acda3bd6deb7.jpeg',5,5,'2026-07-31 12:00:00','2026-10-13 03:21:33',NULL),(15,'Jabón de Tocador (unidad)',3.50,113,'productos/prd_6acdec086998f.jpeg',6,5,'2026-07-31 12:00:00','2026-10-13 08:30:00',NULL),(16,'Shampoo Sedal 350ml',18.00,37,'productos/prd_6acdbcc1a660f.jpeg',6,NULL,'2026-07-31 12:00:00','2026-10-13 05:08:17',NULL),(17,'Pasta Dental Colgate 90g',9.00,47,'productos/prd_6acda40e5422c.jpeg',6,NULL,'2026-07-31 12:00:00','2026-10-13 03:23:58',NULL),(18,'Papas Lays 45g',6.00,79,'productos/prd_6acd4be6a9825.jpeg',7,NULL,'2026-07-31 12:00:00','2026-10-13 05:05:59',NULL),(19,'Chocolate Cofler BLOCK (unidad)',2.50,172,'productos/prd_6acdb84f980a7.jpeg',7,NULL,'2026-07-31 12:00:00','2026-10-13 04:49:19',NULL),(20,'Salchicha Sofía 500g',16.00,6,'productos/prd_6acdbc911b249.jpeg',8,NULL,'2026-07-31 12:00:00','2026-10-13 05:07:29',NULL),(21,'Jamón de Pierna 250g',14.50,25,'productos/1yBqNW9Q3O6g5dsbBm42zmQxy0vRaQYGPV1HoSs0.jpg',8,NULL,'2026-07-31 12:00:00','2026-10-13 08:31:15',NULL),(23,'Singani',75.00,14,'productos/prd_6acdbce7000df.jpeg',9,NULL,'2026-09-03 22:50:09','2026-10-13 05:08:55',NULL),(29,'Fanta',20.00,10,'productos/IQKdCncoeoNR6mG3nenH6Jgk5qTw91D4vr7wKf4R.jpg',4,NULL,'2026-09-04 00:02:18','2026-10-13 08:43:58',NULL),(30,'Sprite',20.00,29,'productos/iRa1quncYRo6kCHKb4vSoBmGTZtNIGTRCiMZygAm.webp',4,NULL,'2026-09-04 00:07:21','2026-09-04 00:16:15','2026-09-04 00:16:15');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proveedores` (
  `idProveedor` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `nit` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`idProveedor`),
  UNIQUE KEY `proveedores_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'Distribuidora La Cosecha','1023456011','70123456','ventas@lacosecha.bo','Av. Blanco Galindo km 5, Cochabamba',1,'2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(2,'PIL Andina S.A.','1004521018','4258900','contacto@pil.com.bo','Parque Industrial, Cochabamba',1,'2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(3,'Panificadora El Trigal','5567234015','70988712',NULL,'Av. América, Cochabamba',1,'2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(4,'Embol S.A.','1009887013','4223344','ventas@embol.com.bo','Av. Villazón, Cochabamba',1,'2026-07-31 12:00:00','2026-07-31 12:00:00',NULL),(5,'Distribuidora Aseo Total','3321456019','70654321',NULL,'Zona Sud, Cochabamba',1,'2026-07-31 12:00:00','2026-07-31 12:00:00',NULL);
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('FaJveUH6njAQvwFdR6WGF9c7MqL3Tn6hCvRnVXrT',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:153.0) Gecko/20100101 Firefox/153.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoid3VKbndMTllhN2cxdXRCY2FsVVFTWE0waEFDOUhwWnU1aEQxaTVuNiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=',1786092533),('Mwr1FJ8PiPYwX2f0XNVIY3C2twLs9Kkdz9IQ9aGW',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:153.0) Gecko/20100101 Firefox/153.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRXFpWjExb2huc0RXUjJBTXVqYVlwc1JuVnNPSjZoWnFJaDRiVDI1ZCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1786128195);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `rol` varchar(255) NOT NULL DEFAULT 'invitado',
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Freddy Ascarraga','dueno@supermarket.com',NULL,'$2y$12$vFsTvCs6jEjkniIAvB6o1OUfWIRbQ7YHVOsrtDuvGE0jQx3ULf2E6',NULL,'administrador','usuarios/usr_6acd4902eed31.jpeg','2026-07-31 12:00:00','2026-10-13 00:47:37'),(2,'Empleado','empleado@supermarket.com',NULL,'$2y$12$vFsTvCs6jEjkniIAvB6o1OUfWIRbQ7YHVOsrtDuvGE0jQx3ULf2E6',NULL,'invitado','usuarios/usr_6acd4f3177fb6.png','2026-07-31 12:00:00','2026-10-13 03:44:04'),(6,'Juan perez','juan@supermarket.com',NULL,'$2y$12$lF5w3JOoQfdIFwdKrdq8keAKXNpWuvPoVoO.19fs5jaiQVCiWqfHi',NULL,'invitado','usuarios/usr_6acdadafed02c.jpeg','2026-10-13 04:03:13','2026-10-13 04:04:00');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'supermarket'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-13  4:55:38
