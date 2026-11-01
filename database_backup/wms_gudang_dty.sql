/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.20-13.0.2-MariaDB, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: db_wms_gudang_dty
-- ------------------------------------------------------
-- Server version	13.0.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `event` enum('LOGIN','LOGOUT','CREATE','UPDATE','DELETE','VOID','RELEASE_BUFFER') NOT NULL,
  `modul` varchar(50) NOT NULL,
  `ref_id` bigint(20) unsigned DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_modul_ref_id_index` (`modul`,`ref_id`),
  KEY `activity_logs_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `activity_logs_event_created_at_index` (`event`,`created_at`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `alokasi_buffer`
--

DROP TABLE IF EXISTS `alokasi_buffer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `alokasi_buffer` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `barang_id` bigint(20) unsigned NOT NULL,
  `merge` varchar(50) NOT NULL,
  `grade_id` bigint(20) unsigned DEFAULT NULL,
  `jumlah_kg` decimal(14,2) NOT NULL DEFAULT 0.00,
  `sumber_transaksi_masuk_id` bigint(20) unsigned DEFAULT NULL,
  `dilewati` tinyint(1) NOT NULL DEFAULT 0,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alokasi_buffer_unique` (`barang_id`,`merge`,`tanggal`),
  KEY `alokasi_buffer_grade_id_foreign` (`grade_id`),
  KEY `alokasi_buffer_sumber_transaksi_masuk_id_foreign` (`sumber_transaksi_masuk_id`),
  KEY `alokasi_buffer_created_by_foreign` (`created_by`),
  KEY `alokasi_buffer_tanggal_dilewati_index` (`tanggal`,`dilewati`),
  CONSTRAINT `alokasi_buffer_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`),
  CONSTRAINT `alokasi_buffer_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `alokasi_buffer_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grade` (`id`) ON DELETE SET NULL,
  CONSTRAINT `alokasi_buffer_sumber_transaksi_masuk_id_foreign` FOREIGN KEY (`sumber_transaksi_masuk_id`) REFERENCES `transaksi_masuk` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alokasi_buffer`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `alokasi_buffer` WRITE;
/*!40000 ALTER TABLE `alokasi_buffer` DISABLE KEYS */;
/*!40000 ALTER TABLE `alokasi_buffer` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `barang`
--

DROP TABLE IF EXISTS `barang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `barang` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kategori_id` bigint(20) unsigned NOT NULL,
  `kode_barang` varchar(50) NOT NULL,
  `nama_barang` varchar(150) NOT NULL,
  `denier` varchar(20) DEFAULT NULL,
  `filament` varchar(20) DEFAULT NULL,
  `satuan_dasar_id` bigint(20) unsigned NOT NULL,
  `stok_minimum` decimal(14,2) DEFAULT NULL,
  `is_merge_minggu` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `barang_kode_barang_unique` (`kode_barang`),
  KEY `barang_satuan_dasar_id_foreign` (`satuan_dasar_id`),
  KEY `barang_kategori_id_is_active_index` (`kategori_id`,`is_active`),
  KEY `barang_is_merge_minggu_index` (`is_merge_minggu`),
  CONSTRAINT `barang_kategori_id_foreign` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_barang` (`id`),
  CONSTRAINT `barang_satuan_dasar_id_foreign` FOREIGN KEY (`satuan_dasar_id`) REFERENCES `satuan` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barang`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `barang` WRITE;
/*!40000 ALTER TABLE `barang` DISABLE KEYS */;
INSERT INTO `barang` VALUES
(1,1,'POY-250-48','POY 250/48','250','48',1,NULL,0,1,'2026-11-01 05:05:43','2026-11-01 05:05:43',NULL),
(2,2,'DTY-150-48-1','DTY 150/48','150','48',1,NULL,0,1,'2026-11-01 05:05:43','2026-11-01 05:05:43',NULL),
(3,3,'PPR TUB DTY, BULLNOSE','Paper Tube DTY Bullnose 212-222GM',NULL,NULL,4,1000.00,0,1,'2026-11-01 05:05:43','2026-11-01 05:05:43',NULL),
(4,3,'CARTON BOX DTY 675x444x278MM, KRAFT 200','Carton Box DTY 675x444x278mm Kraft 200',NULL,NULL,4,200.00,0,1,'2026-11-01 05:05:43','2026-11-01 05:05:43',NULL),
(5,3,'STRETCH FILM 12MC','Stretch Film 12MC',NULL,NULL,5,NULL,0,1,'2026-11-01 05:05:43','2026-11-01 05:05:43',NULL),
(6,3,'PALLET DTY SMALL BOX 1370x1150x140MM','Pallet DTY Small Box 1370x1150x140mm',NULL,NULL,4,NULL,0,1,'2026-11-01 05:05:43','2026-11-01 05:05:43',NULL),
(7,3,'TAPE 250MX3IN, VISTAC TRANSPARENT','Tape 250m x 3in Vistac Transparent',NULL,NULL,5,NULL,0,1,'2026-11-01 05:05:43','2026-11-01 05:05:43',NULL),
(8,3,'DTY CONING OIL, TM368 BN','DTY Coning Oil TM368 BN',NULL,NULL,1,200.00,0,1,'2026-11-01 05:05:43','2026-11-01 05:05:43',NULL),
(9,3,'DTY PLASTIC BAG HDPE VRGN (4%A) 36/42x55 CM','DTY Plastic Bag HDPE VRGN (4%A) 36/42x55cm',NULL,NULL,1,NULL,0,1,'2026-11-01 05:05:43','2026-11-01 05:05:43',NULL);
/*!40000 ALTER TABLE `barang` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `grade`
--

DROP TABLE IF EXISTS `grade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `grade` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(10) NOT NULL,
  `kategori_id` bigint(20) unsigned NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grade_kode_kategori_unique` (`kode`,`kategori_id`),
  KEY `grade_kategori_id_foreign` (`kategori_id`),
  CONSTRAINT `grade_kategori_id_foreign` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_barang` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grade`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `grade` WRITE;
/*!40000 ALTER TABLE `grade` DISABLE KEYS */;
INSERT INTO `grade` VALUES
(1,'AL',1,'POY grade AL (lolos)',1,'2026-11-01 05:05:43','2026-11-01 05:05:43'),
(2,'Am',1,'POY grade Am (minor)',1,'2026-11-01 05:05:43','2026-11-01 05:05:43'),
(3,'AX',2,'DTY ekspor / super',1,'2026-11-01 05:05:43','2026-11-01 05:05:43'),
(4,'AE',2,'DTY grade AE',1,'2026-11-01 05:05:43','2026-11-01 05:05:43'),
(5,'A',2,'DTY standar 1',1,'2026-11-01 05:05:43','2026-11-01 05:05:43'),
(6,'B',2,'DTY segregasi / turun grade',1,'2026-11-01 05:05:43','2026-11-01 05:05:43'),
(7,'C',2,'DTY reject / knitting (scrap)',1,'2026-11-01 05:05:43','2026-11-01 05:05:43'),
(8,'STD',3,'Grade netral item pendukung (tanpa grade pada Gate Pass)',1,'2026-11-01 05:05:43','2026-11-01 05:05:43');
/*!40000 ALTER TABLE `grade` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `kategori_barang`
--

DROP TABLE IF EXISTS `kategori_barang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kategori_barang` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kategori_barang_kode_unique` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kategori_barang`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `kategori_barang` WRITE;
/*!40000 ALTER TABLE `kategori_barang` DISABLE KEYS */;
INSERT INTO `kategori_barang` VALUES
(1,'POY','POY (Bahan Baku Utama)',NULL,'2026-11-01 05:05:43','2026-11-01 05:05:43'),
(2,'DTY','DTY (Produk Jadi)',NULL,'2026-11-01 05:05:43','2026-11-01 05:05:43'),
(3,'PENDUKUNG','Item Pendukung (Packaging & Consumables)',NULL,'2026-11-01 05:05:43','2026-11-01 05:05:43');
/*!40000 ALTER TABLE `kategori_barang` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `konversi_satuan`
--

DROP TABLE IF EXISTS `konversi_satuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `konversi_satuan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `barang_id` bigint(20) unsigned DEFAULT NULL,
  `dari_satuan_id` bigint(20) unsigned NOT NULL,
  `ke_satuan_id` bigint(20) unsigned NOT NULL,
  `faktor` decimal(12,4) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `konversi_satuan_unique` (`barang_id`,`dari_satuan_id`,`ke_satuan_id`),
  KEY `konversi_satuan_dari_satuan_id_foreign` (`dari_satuan_id`),
  KEY `konversi_satuan_ke_satuan_id_foreign` (`ke_satuan_id`),
  CONSTRAINT `konversi_satuan_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE,
  CONSTRAINT `konversi_satuan_dari_satuan_id_foreign` FOREIGN KEY (`dari_satuan_id`) REFERENCES `satuan` (`id`),
  CONSTRAINT `konversi_satuan_ke_satuan_id_foreign` FOREIGN KEY (`ke_satuan_id`) REFERENCES `satuan` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `konversi_satuan`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `konversi_satuan` WRITE;
/*!40000 ALTER TABLE `konversi_satuan` DISABLE KEYS */;
INSERT INTO `konversi_satuan` VALUES
(1,2,2,6,6.0000,1,'2026-11-01 05:05:43','2026-11-01 05:05:43'),
(2,2,6,1,5.5000,1,'2026-11-01 05:05:43','2026-11-01 05:05:43'),
(3,2,2,1,33.0000,1,'2026-11-01 05:05:43','2026-11-01 05:05:43');
/*!40000 ALTER TABLE `konversi_satuan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2026_11_01_102329_create_kategori_barang_and_satuan_tables',1),
(5,'2026_11_01_102330_create_grade_table',1),
(6,'2026_11_01_102331_create_barang_table',1),
(7,'2026_11_01_102332_create_supplier_customer_and_konversi_satuan_tables',1),
(8,'2026_11_01_102333_create_nomor_dokumen_table',1),
(9,'2026_11_01_102334_create_stok_barang_table',1),
(10,'2026_11_01_102335_create_transaksi_masuk_tables',1),
(11,'2026_11_01_102336_create_transaksi_keluar_tables',1),
(12,'2026_11_01_102337_create_mutasi_stok_table',1),
(13,'2026_11_01_102338_create_alokasi_buffer_table',1),
(14,'2026_11_01_102339_create_stok_opname_table',1),
(15,'2026_11_01_102340_create_activity_logs_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mutasi_stok`
--

DROP TABLE IF EXISTS `mutasi_stok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mutasi_stok` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `barang_id` bigint(20) unsigned NOT NULL,
  `merge` varchar(50) NOT NULL,
  `grade_id` bigint(20) unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `jenis_transaksi` enum('MASUK','KELUAR','ADJUSTMENT_IN','ADJUSTMENT_OUT','ALOKASI_BUFFER','RILIS_BUFFER','VOID') NOT NULL,
  `pos` enum('AKTIF','BUFFER') NOT NULL,
  `ref_nomor` varchar(50) NOT NULL,
  `masuk_qty` decimal(14,2) NOT NULL DEFAULT 0.00,
  `keluar_qty` decimal(14,2) NOT NULL DEFAULT 0.00,
  `saldo_akhir_qty` decimal(14,2) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mutasi_stok_grade_id_foreign` (`grade_id`),
  KEY `mutasi_stok_user_id_foreign` (`user_id`),
  KEY `mutasi_stok_kartu_index` (`barang_id`,`merge`,`grade_id`,`tanggal`),
  KEY `mutasi_stok_laporan_index` (`tanggal`,`jenis_transaksi`),
  CONSTRAINT `mutasi_stok_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`),
  CONSTRAINT `mutasi_stok_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grade` (`id`),
  CONSTRAINT `mutasi_stok_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mutasi_stok`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mutasi_stok` WRITE;
/*!40000 ALTER TABLE `mutasi_stok` DISABLE KEYS */;
/*!40000 ALTER TABLE `mutasi_stok` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `nomor_dokumen`
--

DROP TABLE IF EXISTS `nomor_dokumen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `nomor_dokumen` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jenis` enum('SURAT_JALAN_DTY','GATE_PASS','TRX_MASUK','TRX_KELUAR') NOT NULL,
  `periode` varchar(10) NOT NULL,
  `last_number` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_dokumen_jenis_periode_unique` (`jenis`,`periode`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nomor_dokumen`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `nomor_dokumen` WRITE;
/*!40000 ALTER TABLE `nomor_dokumen` DISABLE KEYS */;
INSERT INTO `nomor_dokumen` VALUES
(1,'SURAT_JALAN_DTY','2026-11',0,'2026-11-01 05:05:43','2026-11-01 05:05:43'),
(2,'GATE_PASS','2026-11',0,'2026-11-01 05:05:43','2026-11-01 05:05:43'),
(3,'TRX_MASUK','2026-11',0,'2026-11-01 05:05:43','2026-11-01 05:05:43'),
(4,'TRX_KELUAR','2026-11',0,'2026-11-01 05:05:43','2026-11-01 05:05:43');
/*!40000 ALTER TABLE `nomor_dokumen` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `satuan`
--

DROP TABLE IF EXISTS `satuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `satuan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `satuan_kode_unique` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `satuan`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `satuan` WRITE;
/*!40000 ALTER TABLE `satuan` DISABLE KEYS */;
INSERT INTO `satuan` VALUES
(1,'KG','Kilogram','2026-11-01 05:05:43','2026-11-01 05:05:43'),
(2,'BOX','Box / Carton','2026-11-01 05:05:43','2026-11-01 05:05:43'),
(3,'PALLET','Pallet','2026-11-01 05:05:43','2026-11-01 05:05:43'),
(4,'PCS','Pieces','2026-11-01 05:05:43','2026-11-01 05:05:43'),
(5,'ROLL','Roll','2026-11-01 05:05:43','2026-11-01 05:05:43'),
(6,'BOBBIN','Bobbin / Gulungan','2026-11-01 05:05:43','2026-11-01 05:05:43');
/*!40000 ALTER TABLE `satuan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `stok_barang`
--

DROP TABLE IF EXISTS `stok_barang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stok_barang` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `barang_id` bigint(20) unsigned NOT NULL,
  `merge` varchar(50) NOT NULL,
  `grade_id` bigint(20) unsigned NOT NULL,
  `stok_aktif` decimal(14,2) NOT NULL DEFAULT 0.00,
  `stok_buffer` decimal(14,2) NOT NULL DEFAULT 0.00,
  `jumlah_bobbin` int(11) NOT NULL DEFAULT 0,
  `jumlah_box` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stok_barang_unique` (`barang_id`,`merge`,`grade_id`),
  KEY `stok_barang_grade_id_foreign` (`grade_id`),
  CONSTRAINT `stok_barang_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stok_barang_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grade` (`id`),
  CONSTRAINT `stok_barang_non_negatif` CHECK (`stok_aktif` >= 0 and `stok_buffer` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_barang`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `stok_barang` WRITE;
/*!40000 ALTER TABLE `stok_barang` DISABLE KEYS */;
/*!40000 ALTER TABLE `stok_barang` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `stok_opname`
--

DROP TABLE IF EXISTS `stok_opname`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stok_opname` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nomor_opname` varchar(50) NOT NULL,
  `tanggal` date NOT NULL,
  `barang_id` bigint(20) unsigned NOT NULL,
  `merge` varchar(50) NOT NULL,
  `grade_id` bigint(20) unsigned NOT NULL,
  `stok_sistem_qty` decimal(14,2) NOT NULL,
  `stok_fisik_qty` decimal(14,2) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `transaksi_masuk_id` bigint(20) unsigned DEFAULT NULL,
  `transaksi_keluar_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `selisih_qty` decimal(14,2) GENERATED ALWAYS AS (`stok_fisik_qty` - `stok_sistem_qty`) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stok_opname_unique` (`barang_id`,`merge`,`grade_id`,`tanggal`),
  UNIQUE KEY `stok_opname_nomor_opname_unique` (`nomor_opname`),
  KEY `stok_opname_grade_id_foreign` (`grade_id`),
  KEY `stok_opname_created_by_foreign` (`created_by`),
  KEY `stok_opname_transaksi_masuk_id_foreign` (`transaksi_masuk_id`),
  KEY `stok_opname_transaksi_keluar_id_foreign` (`transaksi_keluar_id`),
  CONSTRAINT `stok_opname_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`),
  CONSTRAINT `stok_opname_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `stok_opname_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grade` (`id`),
  CONSTRAINT `stok_opname_transaksi_keluar_id_foreign` FOREIGN KEY (`transaksi_keluar_id`) REFERENCES `transaksi_keluar` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stok_opname_transaksi_masuk_id_foreign` FOREIGN KEY (`transaksi_masuk_id`) REFERENCES `transaksi_masuk` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_opname`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `stok_opname` WRITE;
/*!40000 ALTER TABLE `stok_opname` DISABLE KEYS */;
/*!40000 ALTER TABLE `stok_opname` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `supplier_customer`
--

DROP TABLE IF EXISTS `supplier_customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier_customer` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(30) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `jenis` enum('SUPPLIER','CUSTOMER','BOTH','INTERNAL') NOT NULL,
  `kontak` varchar(100) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supplier_customer_kode_unique` (`kode`),
  KEY `supplier_customer_jenis_index` (`jenis`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_customer`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `supplier_customer` WRITE;
/*!40000 ALTER TABLE `supplier_customer` DISABLE KEYS */;
INSERT INTO `supplier_customer` VALUES
(1,'IPCI','IPCI - 300097905','BOTH',NULL,'PT. Indorama Polychem Indonesia','2026-11-01 05:05:43','2026-11-01 05:05:43',NULL),
(2,'STORE-IPCI','Store IPCI','SUPPLIER',NULL,'PT. Indorama Polychem Indonesia — Dept. STORE','2026-11-01 05:05:43','2026-11-01 05:05:43',NULL),
(3,'AREA PRODUKSI','Area Produksi DTY PEI','INTERNAL',NULL,'Area Doffing / Transit Produksi','2026-11-01 05:05:43','2026-11-01 05:05:43',NULL);
/*!40000 ALTER TABLE `supplier_customer` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `transaksi_keluar`
--

DROP TABLE IF EXISTS `transaksi_keluar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaksi_keluar` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nomor_transaksi` varchar(50) NOT NULL,
  `jenis_keluar` enum('POY_PRODUKSI','DTY_SURAT_JALAN','DTY_SCRAP_REJECT','ADJUSTMENT_OUT') NOT NULL,
  `nomor_surat_jalan` varchar(50) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `partner_id` bigint(20) unsigned DEFAULT NULL,
  `no_kendaraan` varchar(30) DEFAULT NULL,
  `nama_sopir` varchar(100) DEFAULT NULL,
  `pic_pengirim` varchar(100) DEFAULT NULL,
  `pic_mengetahui` varchar(100) DEFAULT NULL,
  `shift` tinyint(4) DEFAULT NULL,
  `operator_nama` varchar(100) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('DRAFT','POSTED','ISSUED','VOID','CANCELLED') NOT NULL DEFAULT 'POSTED',
  `alasan_void` text DEFAULT NULL,
  `voided_at` timestamp NULL DEFAULT NULL,
  `voided_by` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaksi_keluar_nomor_transaksi_unique` (`nomor_transaksi`),
  UNIQUE KEY `transaksi_keluar_nomor_surat_jalan_unique` (`nomor_surat_jalan`),
  KEY `transaksi_keluar_partner_id_foreign` (`partner_id`),
  KEY `transaksi_keluar_voided_by_foreign` (`voided_by`),
  KEY `transaksi_keluar_created_by_foreign` (`created_by`),
  KEY `trx_keluar_laporan_index` (`tanggal`,`jenis_keluar`,`status`),
  KEY `trx_keluar_shift_index` (`jenis_keluar`,`shift`),
  CONSTRAINT `transaksi_keluar_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `transaksi_keluar_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `supplier_customer` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transaksi_keluar_voided_by_foreign` FOREIGN KEY (`voided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi_keluar`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `transaksi_keluar` WRITE;
/*!40000 ALTER TABLE `transaksi_keluar` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaksi_keluar` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `transaksi_keluar_detail`
--

DROP TABLE IF EXISTS `transaksi_keluar_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaksi_keluar_detail` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaksi_keluar_id` bigint(20) unsigned NOT NULL,
  `barang_id` bigint(20) unsigned NOT NULL,
  `merge` varchar(50) NOT NULL,
  `grade_id` bigint(20) unsigned NOT NULL,
  `jumlah_kemasan` decimal(12,2) DEFAULT NULL,
  `satuan_kemasan_id` bigint(20) unsigned DEFAULT NULL,
  `jumlah_bobbin` int(11) DEFAULT NULL,
  `berat_netto_kg` decimal(14,2) NOT NULL,
  `berat_gross_kg` decimal(14,2) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transaksi_keluar_detail_transaksi_keluar_id_foreign` (`transaksi_keluar_id`),
  KEY `transaksi_keluar_detail_grade_id_foreign` (`grade_id`),
  KEY `transaksi_keluar_detail_satuan_kemasan_id_foreign` (`satuan_kemasan_id`),
  KEY `trx_keluar_detail_stok_index` (`barang_id`,`merge`,`grade_id`),
  CONSTRAINT `transaksi_keluar_detail_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`),
  CONSTRAINT `transaksi_keluar_detail_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grade` (`id`),
  CONSTRAINT `transaksi_keluar_detail_satuan_kemasan_id_foreign` FOREIGN KEY (`satuan_kemasan_id`) REFERENCES `satuan` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transaksi_keluar_detail_transaksi_keluar_id_foreign` FOREIGN KEY (`transaksi_keluar_id`) REFERENCES `transaksi_keluar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi_keluar_detail`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `transaksi_keluar_detail` WRITE;
/*!40000 ALTER TABLE `transaksi_keluar_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaksi_keluar_detail` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `transaksi_masuk`
--

DROP TABLE IF EXISTS `transaksi_masuk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaksi_masuk` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nomor_transaksi` varchar(50) NOT NULL,
  `jenis_masuk` enum('POY_IPCI','PENDUKUNG_GATEPASS','DTY_PRODUKSI','ADJUSTMENT_IN') NOT NULL,
  `nomor_gate_pass` varchar(50) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `partner_id` bigint(20) unsigned DEFAULT NULL,
  `sumber_internal` varchar(100) DEFAULT NULL,
  `ref_dokumen` varchar(100) DEFAULT NULL,
  `shift` tinyint(4) DEFAULT NULL,
  `operator_nama` varchar(100) DEFAULT NULL,
  `merge_poy_asal` varchar(50) DEFAULT NULL,
  `pengirim` varchar(100) DEFAULT NULL,
  `penerima` varchar(100) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('DRAFT','POSTED','ISSUED','VOID','CANCELLED') NOT NULL DEFAULT 'POSTED',
  `alasan_void` text DEFAULT NULL,
  `voided_at` timestamp NULL DEFAULT NULL,
  `voided_by` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaksi_masuk_nomor_transaksi_unique` (`nomor_transaksi`),
  UNIQUE KEY `transaksi_masuk_nomor_gate_pass_unique` (`nomor_gate_pass`),
  KEY `transaksi_masuk_partner_id_foreign` (`partner_id`),
  KEY `transaksi_masuk_voided_by_foreign` (`voided_by`),
  KEY `transaksi_masuk_created_by_foreign` (`created_by`),
  KEY `trx_masuk_laporan_index` (`tanggal`,`jenis_masuk`,`status`),
  KEY `trx_masuk_merge_asal_index` (`jenis_masuk`,`merge_poy_asal`),
  CONSTRAINT `transaksi_masuk_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `transaksi_masuk_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `supplier_customer` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transaksi_masuk_voided_by_foreign` FOREIGN KEY (`voided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi_masuk`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `transaksi_masuk` WRITE;
/*!40000 ALTER TABLE `transaksi_masuk` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaksi_masuk` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `transaksi_masuk_detail`
--

DROP TABLE IF EXISTS `transaksi_masuk_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaksi_masuk_detail` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaksi_masuk_id` bigint(20) unsigned NOT NULL,
  `barang_id` bigint(20) unsigned NOT NULL,
  `merge` varchar(50) NOT NULL,
  `grade_id` bigint(20) unsigned NOT NULL,
  `jumlah_kemasan` decimal(12,2) DEFAULT NULL,
  `satuan_kemasan_id` bigint(20) unsigned DEFAULT NULL,
  `jumlah_bobbin` int(11) DEFAULT NULL,
  `berat_kg` decimal(14,2) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transaksi_masuk_detail_transaksi_masuk_id_foreign` (`transaksi_masuk_id`),
  KEY `transaksi_masuk_detail_grade_id_foreign` (`grade_id`),
  KEY `transaksi_masuk_detail_satuan_kemasan_id_foreign` (`satuan_kemasan_id`),
  KEY `trx_masuk_detail_stok_index` (`barang_id`,`merge`,`grade_id`),
  CONSTRAINT `transaksi_masuk_detail_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`),
  CONSTRAINT `transaksi_masuk_detail_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grade` (`id`),
  CONSTRAINT `transaksi_masuk_detail_satuan_kemasan_id_foreign` FOREIGN KEY (`satuan_kemasan_id`) REFERENCES `satuan` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transaksi_masuk_detail_transaksi_masuk_id_foreign` FOREIGN KEY (`transaksi_masuk_id`) REFERENCES `transaksi_masuk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi_masuk_detail`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `transaksi_masuk_detail` WRITE;
/*!40000 ALTER TABLE `transaksi_masuk_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaksi_masuk_detail` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','operator','manajer') NOT NULL DEFAULT 'operator',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Administrator Gudang','admin','admin@wms.local','2026-11-01 05:05:42','$2y$12$EXaWoH3xcdex7y0IR/yDS.hlp22auO9QDEssPDgROi6MrRyK5oGJW','admin',1,NULL,'2026-11-01 05:05:42','2026-11-01 05:05:42'),
(2,'Operator Gudang','operator','operator@wms.local','2026-11-01 05:05:42','$2y$12$awoiquOXrMi4skED70WeeO2AOosN0YVHywAliIC69NpyyeHLqdTMC','operator',1,NULL,'2026-11-01 05:05:42','2026-11-01 05:05:42'),
(3,'Manajer DTY PEI','manajer','manajer@wms.local','2026-11-01 05:05:43','$2y$12$3bCRsMifwNsLL0W7VMauI.MOq.8w9VSQp06IgheEQorXwySeLd8.O','manajer',1,NULL,'2026-11-01 05:05:43','2026-11-01 05:05:43');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Dumping events for database 'db_wms_gudang_dty'
--

--
-- Dumping routines for database 'db_wms_gudang_dty'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-11-01 12:05:50
