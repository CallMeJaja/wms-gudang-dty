/*M!999999\- enable the sandbox mode */

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
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `event` enum('LOGIN','LOGOUT','CREATE','UPDATE','DELETE','VOID','ALOKASI_BUFFER','RELEASE_BUFFER') NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES
(1,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',1,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1044) dari penerimaan DEMO-POY-IN-01.','127.0.0.1','Symfony','2026-11-05 22:49:13'),
(2,2,'UPDATE','TRANSAKSI_MASUK_POY',1,'Posting transaksi DEMO-POY-IN-01 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:13'),
(3,2,'UPDATE','TRANSAKSI_MASUK_DTY',2,'Posting transaksi DEMO-DTY-IN-01 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:13'),
(4,2,'UPDATE','TRANSAKSI_MASUK_PENDUKUNG',3,'Issue dokumen DEMO-PENDUKUNG-IN-01 (3.856,00 kg, 2 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:13'),
(5,2,'UPDATE','TRANSAKSI_KELUAR_POY',1,'Posting transaksi DEMO-POY-OUT-01 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:13'),
(6,2,'UPDATE','TRANSAKSI_KELUAR_SJ',2,'Issue dokumen DEMO-DTY-SJ-01 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:13'),
(7,2,'UPDATE','TRANSAKSI_MASUK_POY',4,'Posting transaksi DEMO-POY-IN-02 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:13'),
(8,2,'UPDATE','TRANSAKSI_MASUK_DTY',5,'Posting transaksi DEMO-DTY-IN-02 (3.333,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(9,2,'UPDATE','TRANSAKSI_KELUAR_POY',3,'Posting transaksi DEMO-POY-OUT-02 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(10,2,'UPDATE','TRANSAKSI_KELUAR_SJ',4,'Issue dokumen DEMO-DTY-SJ-02 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(11,2,'UPDATE','TRANSAKSI_MASUK_POY',6,'Posting transaksi DEMO-POY-IN-03 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(12,2,'UPDATE','TRANSAKSI_MASUK_DTY',7,'Posting transaksi DEMO-DTY-IN-03 (3.366,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(13,2,'UPDATE','TRANSAKSI_KELUAR_POY',5,'Posting transaksi DEMO-POY-OUT-03 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(14,2,'UPDATE','TRANSAKSI_KELUAR_SJ',6,'Issue dokumen DEMO-DTY-SJ-03 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(15,2,'UPDATE','TRANSAKSI_MASUK_DTY',8,'Posting transaksi DEMO-DTY-IN-04 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(16,2,'RELEASE_BUFFER','RILIS_BUFFER',NULL,'Rilis buffer Minggu 576,00 kg (1 baris) ke stok aktif (RILIS-BUFFER-2026-10-11).','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(17,2,'UPDATE','TRANSAKSI_KELUAR_POY',7,'Posting transaksi DEMO-POY-OUT-04 (1.728,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(18,2,'UPDATE','TRANSAKSI_KELUAR_SJ',8,'Issue dokumen DEMO-DTY-SJ-04 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(19,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',9,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1065) dari penerimaan DEMO-POY-IN-04.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(20,2,'UPDATE','TRANSAKSI_MASUK_POY',9,'Posting transaksi DEMO-POY-IN-04 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(21,2,'UPDATE','TRANSAKSI_MASUK_DTY',10,'Posting transaksi DEMO-DTY-IN-05 (3.333,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(22,2,'UPDATE','TRANSAKSI_KELUAR_POY',9,'Posting transaksi DEMO-POY-OUT-05 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(23,2,'UPDATE','TRANSAKSI_KELUAR_SJ',10,'Issue dokumen DEMO-DTY-SJ-05 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(24,2,'UPDATE','TRANSAKSI_MASUK_POY',11,'Posting transaksi DEMO-POY-IN-05 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(25,2,'UPDATE','TRANSAKSI_MASUK_DTY',12,'Posting transaksi DEMO-DTY-IN-06 (3.366,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(26,2,'UPDATE','TRANSAKSI_KELUAR_POY',11,'Posting transaksi DEMO-POY-OUT-06 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(27,2,'UPDATE','TRANSAKSI_KELUAR_SJ',12,'Issue dokumen DEMO-DTY-SJ-06 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(28,2,'UPDATE','TRANSAKSI_MASUK_POY',13,'Posting transaksi DEMO-POY-IN-06 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(29,2,'UPDATE','TRANSAKSI_MASUK_DTY',14,'Posting transaksi DEMO-DTY-IN-07 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(30,2,'UPDATE','TRANSAKSI_KELUAR_POY',13,'Posting transaksi DEMO-POY-OUT-07 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(31,2,'UPDATE','TRANSAKSI_KELUAR_SJ',14,'Issue dokumen DEMO-DTY-SJ-07 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(32,2,'UPDATE','TRANSAKSI_MASUK_POY',15,'Posting transaksi DEMO-POY-IN-07 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(33,2,'UPDATE','TRANSAKSI_MASUK_DTY',16,'Posting transaksi DEMO-DTY-IN-08 (3.333,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(34,2,'UPDATE','TRANSAKSI_MASUK_PENDUKUNG',17,'Issue dokumen DEMO-PENDUKUNG-IN-02 (200,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(35,2,'UPDATE','TRANSAKSI_KELUAR_POY',15,'Posting transaksi DEMO-POY-OUT-08 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(36,2,'UPDATE','TRANSAKSI_KELUAR_SJ',16,'Issue dokumen DEMO-DTY-SJ-08 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(37,2,'UPDATE','TRANSAKSI_MASUK_POY',18,'Posting transaksi DEMO-POY-IN-08 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(38,2,'UPDATE','TRANSAKSI_MASUK_DTY',19,'Posting transaksi DEMO-DTY-IN-09 (3.366,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(39,2,'UPDATE','TRANSAKSI_KELUAR_POY',17,'Posting transaksi DEMO-POY-OUT-09 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(40,2,'UPDATE','TRANSAKSI_KELUAR_SJ',18,'Issue dokumen DEMO-DTY-SJ-09 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(41,2,'UPDATE','TRANSAKSI_MASUK_POY',20,'Posting transaksi DEMO-POY-IN-09 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(42,2,'UPDATE','TRANSAKSI_MASUK_DTY',21,'Posting transaksi DEMO-DTY-IN-10 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(43,2,'UPDATE','TRANSAKSI_KELUAR_POY',19,'Posting transaksi DEMO-POY-OUT-10 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(44,2,'UPDATE','TRANSAKSI_KELUAR_SJ',20,'Issue dokumen DEMO-DTY-SJ-10 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(45,2,'UPDATE','TRANSAKSI_MASUK_DTY',22,'Posting transaksi DEMO-DTY-IN-11 (3.333,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(46,2,'RELEASE_BUFFER','RILIS_BUFFER',NULL,'Rilis buffer Minggu 576,00 kg (1 baris) ke stok aktif (RILIS-BUFFER-2026-10-18).','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(47,2,'UPDATE','TRANSAKSI_KELUAR_POY',21,'Posting transaksi DEMO-POY-OUT-11 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(48,2,'UPDATE','TRANSAKSI_KELUAR_SJ',22,'Issue dokumen DEMO-DTY-SJ-11 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(49,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',23,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1044) dari penerimaan DEMO-POY-IN-10.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(50,2,'UPDATE','TRANSAKSI_MASUK_POY',23,'Posting transaksi DEMO-POY-IN-10 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(51,2,'UPDATE','TRANSAKSI_MASUK_DTY',24,'Posting transaksi DEMO-DTY-IN-12 (3.366,00 kg, 2 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(52,2,'UPDATE','TRANSAKSI_KELUAR_POY',23,'Posting transaksi DEMO-POY-OUT-12 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(53,2,'UPDATE','TRANSAKSI_KELUAR_SJ',24,'Issue dokumen DEMO-DTY-SJ-12 (3.300,00 kg, 2 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(54,2,'UPDATE','TRANSAKSI_MASUK_POY',25,'Posting transaksi DEMO-POY-IN-11 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(55,2,'UPDATE','TRANSAKSI_MASUK_DTY',26,'Posting transaksi DEMO-DTY-IN-13 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(56,2,'UPDATE','TRANSAKSI_KELUAR_POY',25,'Posting transaksi DEMO-POY-OUT-13 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(57,2,'UPDATE','TRANSAKSI_KELUAR_SJ',26,'Issue dokumen DEMO-DTY-SJ-13 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(58,2,'UPDATE','TRANSAKSI_MASUK_POY',27,'Posting transaksi DEMO-POY-IN-12 (2.880,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(59,2,'UPDATE','TRANSAKSI_MASUK_DTY',28,'Posting transaksi DEMO-DTY-IN-14 (3.333,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(60,2,'UPDATE','TRANSAKSI_KELUAR_POY',27,'Posting transaksi DEMO-POY-OUT-14 (2.880,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(61,2,'UPDATE','TRANSAKSI_KELUAR_SJ',28,'Issue dokumen DEMO-DTY-SJ-14 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(62,2,'UPDATE','TRANSAKSI_MASUK_POY',29,'Posting transaksi DEMO-POY-IN-13 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(63,2,'UPDATE','TRANSAKSI_MASUK_DTY',30,'Posting transaksi DEMO-DTY-IN-15 (3.366,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(64,2,'UPDATE','TRANSAKSI_MASUK_PENDUKUNG',31,'Issue dokumen DEMO-PENDUKUNG-IN-03 (2.500,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(65,2,'UPDATE','TRANSAKSI_KELUAR_POY',29,'Posting transaksi DEMO-POY-OUT-15 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(66,2,'UPDATE','TRANSAKSI_KELUAR_SJ',30,'Issue dokumen DEMO-DTY-SJ-15 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(67,2,'UPDATE','TRANSAKSI_MASUK_POY',32,'Posting transaksi DEMO-POY-IN-14 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(68,2,'UPDATE','TRANSAKSI_MASUK_DTY',33,'Posting transaksi DEMO-DTY-IN-16 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:14'),
(69,2,'UPDATE','TRANSAKSI_KELUAR_POY',31,'Posting transaksi DEMO-POY-OUT-16 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(70,2,'UPDATE','TRANSAKSI_KELUAR_SJ',32,'Issue dokumen DEMO-DTY-SJ-16 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(71,2,'UPDATE','TRANSAKSI_MASUK_POY',34,'Posting transaksi DEMO-POY-IN-15 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(72,2,'UPDATE','TRANSAKSI_MASUK_DTY',35,'Posting transaksi DEMO-DTY-IN-17 (3.333,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(73,2,'UPDATE','TRANSAKSI_KELUAR_POY',33,'Posting transaksi DEMO-POY-OUT-17 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(74,2,'UPDATE','TRANSAKSI_KELUAR_SJ',34,'Issue dokumen DEMO-DTY-SJ-17 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(75,2,'UPDATE','TRANSAKSI_MASUK_DTY',36,'Posting transaksi DEMO-DTY-IN-18 (3.366,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(76,2,'RELEASE_BUFFER','RILIS_BUFFER',NULL,'Rilis buffer Minggu 576,00 kg (1 baris) ke stok aktif (RILIS-BUFFER-2026-10-25).','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(77,2,'UPDATE','TRANSAKSI_KELUAR_POY',35,'Posting transaksi DEMO-POY-OUT-18 (2.880,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(78,2,'UPDATE','TRANSAKSI_KELUAR_SJ',36,'Issue dokumen DEMO-DTY-SJ-18 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(79,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',37,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1065) dari penerimaan DEMO-POY-IN-16.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(80,2,'UPDATE','TRANSAKSI_MASUK_POY',37,'Posting transaksi DEMO-POY-IN-16 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(81,2,'UPDATE','TRANSAKSI_MASUK_DTY',38,'Posting transaksi DEMO-DTY-IN-19 (3.300,00 kg, 2 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(82,2,'UPDATE','TRANSAKSI_KELUAR_POY',37,'Posting transaksi DEMO-POY-OUT-19 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(83,2,'UPDATE','TRANSAKSI_KELUAR_SJ',38,'Issue dokumen DEMO-DTY-SJ-19 (3.300,00 kg, 2 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(84,2,'UPDATE','TRANSAKSI_MASUK_POY',39,'Posting transaksi DEMO-POY-IN-17 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(85,2,'UPDATE','TRANSAKSI_MASUK_DTY',40,'Posting transaksi DEMO-DTY-IN-20 (3.333,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(86,2,'UPDATE','TRANSAKSI_KELUAR_POY',39,'Posting transaksi DEMO-POY-OUT-20 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(87,2,'UPDATE','TRANSAKSI_KELUAR_SJ',40,'Issue dokumen DEMO-DTY-SJ-20 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(88,2,'UPDATE','TRANSAKSI_MASUK_POY',41,'Posting transaksi DEMO-POY-IN-18 (2.880,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(89,2,'UPDATE','TRANSAKSI_MASUK_DTY',42,'Posting transaksi DEMO-DTY-IN-21 (3.366,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(90,2,'UPDATE','TRANSAKSI_KELUAR_POY',41,'Posting transaksi DEMO-POY-OUT-21 (2.880,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(91,2,'UPDATE','TRANSAKSI_KELUAR_SJ',42,'Issue dokumen DEMO-DTY-SJ-21 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(92,2,'UPDATE','TRANSAKSI_MASUK_POY',43,'Posting transaksi DEMO-POY-IN-19 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(93,2,'UPDATE','TRANSAKSI_MASUK_DTY',44,'Posting transaksi DEMO-DTY-IN-22 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(94,2,'UPDATE','TRANSAKSI_MASUK_PENDUKUNG',45,'Issue dokumen DEMO-PENDUKUNG-IN-04 (450,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(95,2,'UPDATE','TRANSAKSI_KELUAR_POY',43,'Posting transaksi DEMO-POY-OUT-22 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(96,2,'UPDATE','TRANSAKSI_KELUAR_SJ',44,'Issue dokumen DEMO-DTY-SJ-22 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(97,2,'UPDATE','TRANSAKSI_MASUK_POY',46,'Posting transaksi DEMO-POY-IN-20 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(98,2,'UPDATE','TRANSAKSI_MASUK_DTY',47,'Posting transaksi DEMO-DTY-IN-23 (3.333,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(99,2,'UPDATE','TRANSAKSI_KELUAR_POY',45,'Posting transaksi DEMO-POY-OUT-23 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(100,2,'UPDATE','TRANSAKSI_KELUAR_SJ',46,'Issue dokumen DEMO-DTY-SJ-23 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(101,2,'UPDATE','TRANSAKSI_MASUK_POY',48,'Posting transaksi DEMO-POY-IN-21 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(102,2,'UPDATE','TRANSAKSI_MASUK_DTY',49,'Posting transaksi DEMO-DTY-IN-24 (3.366,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(103,2,'UPDATE','TRANSAKSI_KELUAR_POY',47,'Posting transaksi DEMO-POY-OUT-24 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(104,2,'UPDATE','TRANSAKSI_KELUAR_SJ',48,'Issue dokumen DEMO-DTY-SJ-24 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(105,2,'UPDATE','TRANSAKSI_MASUK_DTY',50,'Posting transaksi DEMO-DTY-IN-25 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(106,2,'RELEASE_BUFFER','RILIS_BUFFER',NULL,'Rilis buffer Minggu 576,00 kg (1 baris) ke stok aktif (RILIS-BUFFER-2026-11-01).','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(107,2,'UPDATE','TRANSAKSI_KELUAR_POY',49,'Posting transaksi DEMO-POY-OUT-25 (2.880,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(108,2,'UPDATE','TRANSAKSI_KELUAR_SJ',50,'Issue dokumen DEMO-DTY-SJ-25 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(109,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',51,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1044) dari penerimaan DEMO-POY-IN-22.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(110,2,'UPDATE','TRANSAKSI_MASUK_POY',51,'Posting transaksi DEMO-POY-IN-22 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(111,2,'UPDATE','TRANSAKSI_MASUK_DTY',52,'Posting transaksi DEMO-DTY-IN-26 (3.333,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(112,2,'UPDATE','TRANSAKSI_KELUAR_POY',51,'Posting transaksi DEMO-POY-OUT-26 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(113,2,'UPDATE','TRANSAKSI_KELUAR_SJ',52,'Issue dokumen DEMO-DTY-SJ-26 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(114,2,'UPDATE','TRANSAKSI_MASUK_POY',53,'Posting transaksi DEMO-POY-IN-23 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(115,2,'UPDATE','TRANSAKSI_MASUK_DTY',54,'Posting transaksi DEMO-DTY-IN-27 (3.366,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(116,2,'UPDATE','TRANSAKSI_KELUAR_POY',53,'Posting transaksi DEMO-POY-OUT-27 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(117,2,'UPDATE','TRANSAKSI_KELUAR_SJ',54,'Issue dokumen DEMO-DTY-SJ-27 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(118,2,'UPDATE','TRANSAKSI_MASUK_POY',55,'Posting transaksi DEMO-POY-IN-24 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(119,2,'UPDATE','TRANSAKSI_MASUK_DTY',56,'Posting transaksi DEMO-DTY-IN-28 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(120,2,'UPDATE','TRANSAKSI_KELUAR_POY',55,'Posting transaksi DEMO-POY-OUT-28 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(121,2,'UPDATE','TRANSAKSI_KELUAR_SJ',56,'Issue dokumen DEMO-DTY-SJ-28 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(122,2,'UPDATE','TRANSAKSI_MASUK_POY',57,'Posting transaksi DEMO-POY-IN-25 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(123,2,'UPDATE','TRANSAKSI_MASUK_DTY',58,'Posting transaksi DEMO-DTY-IN-29 (3.333,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(124,2,'UPDATE','TRANSAKSI_KELUAR_POY',57,'Posting transaksi DEMO-POY-OUT-29 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(125,2,'UPDATE','TRANSAKSI_KELUAR_SJ',58,'Issue dokumen DEMO-DTY-SJ-29 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:15'),
(126,2,'UPDATE','TRANSAKSI_MASUK_POY',59,'Posting transaksi DEMO-POY-IN-26 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:16'),
(127,2,'UPDATE','TRANSAKSI_MASUK_DTY',60,'Posting transaksi DEMO-DTY-IN-30 (3.366,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:16'),
(128,2,'UPDATE','TRANSAKSI_KELUAR_POY',59,'Posting transaksi DEMO-POY-OUT-30 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:16'),
(129,2,'UPDATE','TRANSAKSI_KELUAR_SJ',60,'Issue dokumen DEMO-DTY-SJ-30 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:16'),
(130,2,'UPDATE','TRANSAKSI_MASUK_POY',61,'Posting transaksi DEMO-POY-IN-27 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-05 22:49:16'),
(131,1,'CREATE','STOK_OPNAME',1,'Opname OPN-IN-2026-11-1 (865,00 kg, selisih 15,00 kg) oleh user #1.','127.0.0.1','Symfony','2026-11-05 22:49:16'),
(132,1,'CREATE','STOK_OPNAME',2,'Opname OPN-OUT-2026-11-1 (195,00 kg, selisih -5,00 kg) oleh user #1.','127.0.0.1','Symfony','2026-11-05 22:49:16'),
(133,1,'CREATE','STOK_OPNAME',3,'Opname OPN-20261130-3-8-X-0e208d (5.956,00 kg, selisih 0,00 kg) oleh user #1.','127.0.0.1','Symfony','2026-11-05 22:49:16');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `alokasi_buffer` DISABLE KEYS */;
INSERT INTO `alokasi_buffer` VALUES
(1,'2026-10-08',1,'4P1044',2,576.00,1,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(2,'2026-10-09',1,'4P1044',2,0.00,4,0,'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(3,'2026-10-10',1,'4P1044',2,0.00,6,0,'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(4,'2026-10-12',1,'4P1065',1,576.00,9,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(5,'2026-10-13',1,'4P1065',1,0.00,11,0,'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(6,'2026-10-14',1,'4P1065',1,0.00,13,0,'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(7,'2026-10-15',1,'4P1065',1,0.00,15,0,'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(8,'2026-10-16',1,'4P1065',1,0.00,18,0,'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(9,'2026-10-17',1,'4P1065',1,0.00,20,0,'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(10,'2026-10-19',1,'4P1044',2,576.00,23,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(11,'2026-10-20',1,'4P1044',2,0.00,25,0,'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(12,'2026-10-21',1,'4P1044',2,0.00,27,1,'Buffer dilewati: Pasokan 5,00 pallet (2.880,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(13,'2026-10-22',1,'4P1044',2,0.00,29,0,'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(14,'2026-10-23',1,'4P1044',2,0.00,32,0,'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(15,'2026-10-24',1,'4P1044',2,0.00,34,0,'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(16,'2026-10-26',1,'4P1065',1,576.00,37,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(17,'2026-10-27',1,'4P1065',1,0.00,39,0,'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(18,'2026-10-28',1,'4P1065',1,0.00,41,1,'Buffer dilewati: Pasokan 5,00 pallet (2.880,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(19,'2026-10-29',1,'4P1065',1,0.00,43,0,'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(20,'2026-10-30',1,'4P1065',1,0.00,46,0,'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(21,'2026-10-31',1,'4P1065',1,0.00,48,0,'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(22,'2026-11-02',1,'4P1044',2,576.00,51,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(23,'2026-11-03',1,'4P1044',2,0.00,53,0,'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(24,'2026-11-04',1,'4P1044',2,0.00,55,0,'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(25,'2026-11-05',1,'4P1044',2,0.00,57,0,'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(26,'2026-11-06',1,'4P1044',2,0.00,59,0,'Buffer sudah penuh (target gudang tercapai) — seluruh kg masuk stok aktif. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-05 22:49:16','2026-11-05 22:49:16');
/*!40000 ALTER TABLE `alokasi_buffer` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `barang` DISABLE KEYS */;
INSERT INTO `barang` VALUES
(1,1,'POY-250-48','POY 250/48','250','48',1,NULL,1,1,'2026-11-05 22:49:13','2026-11-05 22:49:13',NULL),
(2,2,'DTY-150-48-1','DTY 150/48','150','48',1,NULL,0,1,'2026-11-05 22:49:13','2026-11-05 22:49:13',NULL),
(3,3,'5003301227','PPR TUB DTY,BULLNOSE,212-222GM',NULL,NULL,4,1000.00,0,1,'2026-11-05 22:49:13','2026-11-05 22:49:13',NULL),
(4,3,'5000002512','CARTON BOX DTY 675X444X278MM,KRAFT 200',NULL,NULL,4,200.00,0,1,'2026-11-05 22:49:13','2026-11-05 22:49:13',NULL),
(5,3,'5003301185','STRETCH FILM 12MC',NULL,NULL,4,NULL,0,1,'2026-11-05 22:49:13','2026-11-05 22:49:13',NULL),
(6,3,'5003301241','PALLET DTY SMALL BOX,1370X1150X140MM',NULL,NULL,4,NULL,0,1,'2026-11-05 22:49:13','2026-11-05 22:49:13',NULL),
(7,3,'5003301180','TAPE,250MX3IN,VISTAC,TRANSPARENT',NULL,NULL,4,NULL,0,1,'2026-11-05 22:49:13','2026-11-05 22:49:13',NULL),
(8,3,'1003000201','DTY CONING OIL,TM368 BN',NULL,NULL,1,200.00,0,1,'2026-11-05 22:49:13','2026-11-05 22:49:13',NULL),
(9,3,'5000001861','DTY PLASTIC BAG-HDPE VRGN (4%A) 36/42X55 CM',NULL,NULL,1,NULL,0,1,'2026-11-05 22:49:13','2026-11-05 22:49:13',NULL);
/*!40000 ALTER TABLE `barang` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES
('wms-gudang-dty-pei-cache-wms:notifikasi-stok-menipis:barang:8:user:1','b:1;',1794005357),
('wms-gudang-dty-pei-cache-wms:notifikasi-stok-menipis:barang:8:user:3','b:1;',1794005357);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `grade` DISABLE KEYS */;
INSERT INTO `grade` VALUES
(1,'AL',1,'POY grade AL (lolos)',1,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(2,'Am',1,'POY grade Am (minor)',1,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(3,'AX',2,'DTY ekspor / super',1,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(4,'AE',2,'DTY grade AE',1,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(5,'A',2,'DTY standar 1',1,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(6,'B',2,'DTY segregasi / turun grade',1,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(7,'C',2,'DTY reject / knitting (scrap)',1,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(8,'STD',3,'Grade netral item pendukung (tanpa grade pada Gate Pass)',1,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(9,'WE',2,'DTY grade WE',1,'2026-11-05 22:49:13','2026-11-05 22:49:13');
/*!40000 ALTER TABLE `grade` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `kategori_barang` DISABLE KEYS */;
INSERT INTO `kategori_barang` VALUES
(1,'POY','POY (Bahan Baku Utama)',NULL,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(2,'DTY','DTY (Produk Jadi)',NULL,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(3,'PENDUKUNG','Item Pendukung (Packaging & Consumables)',NULL,'2026-11-05 22:49:13','2026-11-05 22:49:13');
/*!40000 ALTER TABLE `kategori_barang` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `konversi_satuan` DISABLE KEYS */;
INSERT INTO `konversi_satuan` VALUES
(1,2,2,5,6.0000,1,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(2,2,5,1,5.5000,1,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(3,2,2,1,33.0000,1,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(4,2,3,2,30.0000,1,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(5,1,3,2,1.0000,1,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(6,1,2,5,36.0000,1,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(7,1,5,1,16.0000,1,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(8,1,2,1,576.0000,1,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(9,1,3,1,576.0000,1,'2026-11-05 22:49:13','2026-11-05 22:49:13');
/*!40000 ALTER TABLE `konversi_satuan` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
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
(15,'2026_11_01_102340_create_activity_logs_table',1),
(16,'2026_11_02_013312_create_notifications_table',1),
(17,'2026_11_04_075257_update_alokasi_buffer_activity_event',1),
(18,'2026_11_05_225413_create_waste_logs_table',1),
(19,'2026_11_05_230805_add_batch_to_transaksi_masuk_detail_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `mutasi_stok` DISABLE KEYS */;
INSERT INTO `mutasi_stok` VALUES
(1,1,'4P1044',2,'2026-10-08','MASUK','AKTIF','DEMO-POY-IN-01',4032.00,0.00,4032.00,2,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(2,1,'4P1044',2,'2026-10-08','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-01',0.00,576.00,3456.00,2,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(3,1,'4P1044',2,'2026-10-08','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-01',576.00,0.00,576.00,2,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(4,2,'4D9163',3,'2026-10-08','MASUK','AKTIF','DEMO-DTY-IN-01',3300.00,0.00,3300.00,2,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(5,3,'-',8,'2026-10-08','MASUK','AKTIF','DEMO-PENDUKUNG-IN-01',3456.00,0.00,3456.00,2,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(6,4,'-',8,'2026-10-08','MASUK','AKTIF','DEMO-PENDUKUNG-IN-01',400.00,0.00,400.00,2,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(7,1,'4P1044',2,'2026-10-08','KELUAR','AKTIF','DEMO-POY-OUT-01',0.00,3456.00,0.00,2,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(8,2,'4D9163',3,'2026-10-08','KELUAR','AKTIF','DEMO-DTY-SJ-01',0.00,3300.00,0.00,2,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(9,1,'4P1044',2,'2026-10-09','MASUK','AKTIF','DEMO-POY-IN-02',4032.00,0.00,4032.00,2,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(10,2,'4D9163',5,'2026-10-09','MASUK','AKTIF','DEMO-DTY-IN-02',3333.00,0.00,3333.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(11,1,'4P1044',2,'2026-10-09','KELUAR','AKTIF','DEMO-POY-OUT-02',0.00,3456.00,576.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(12,2,'4D9163',5,'2026-10-09','KELUAR','AKTIF','DEMO-DTY-SJ-02',0.00,3300.00,33.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(13,1,'4P1044',2,'2026-10-10','MASUK','AKTIF','DEMO-POY-IN-03',4032.00,0.00,4608.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(14,2,'4D9163',3,'2026-10-10','MASUK','AKTIF','DEMO-DTY-IN-03',3366.00,0.00,3366.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(15,1,'4P1044',2,'2026-10-10','KELUAR','AKTIF','DEMO-POY-OUT-03',0.00,3456.00,1152.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(16,2,'4D9163',3,'2026-10-10','KELUAR','AKTIF','DEMO-DTY-SJ-03',0.00,3300.00,66.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(17,2,'4D9163',6,'2026-10-11','MASUK','AKTIF','DEMO-DTY-IN-04',3300.00,0.00,3300.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(18,1,'4P1044',2,'2026-10-11','RILIS_BUFFER','BUFFER','RILIS-BUFFER-2026-10-11',0.00,576.00,0.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(19,1,'4P1044',2,'2026-10-11','RILIS_BUFFER','AKTIF','RILIS-BUFFER-2026-10-11',576.00,0.00,1728.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(20,1,'4P1044',2,'2026-10-11','KELUAR','AKTIF','DEMO-POY-OUT-04',0.00,1728.00,0.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(21,2,'4D9163',6,'2026-10-11','KELUAR','AKTIF','DEMO-DTY-SJ-04',0.00,3300.00,0.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(22,1,'4P1065',1,'2026-10-12','MASUK','AKTIF','DEMO-POY-IN-04',4032.00,0.00,4032.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(23,1,'4P1065',1,'2026-10-12','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-04',0.00,576.00,3456.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(24,1,'4P1065',1,'2026-10-12','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-04',576.00,0.00,576.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(25,2,'4D9160',3,'2026-10-12','MASUK','AKTIF','DEMO-DTY-IN-05',3333.00,0.00,3333.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(26,1,'4P1065',1,'2026-10-12','KELUAR','AKTIF','DEMO-POY-OUT-05',0.00,3456.00,0.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(27,2,'4D9160',3,'2026-10-12','KELUAR','AKTIF','DEMO-DTY-SJ-05',0.00,3300.00,33.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(28,1,'4P1065',1,'2026-10-13','MASUK','AKTIF','DEMO-POY-IN-05',4032.00,0.00,4032.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(29,2,'4D9160',3,'2026-10-13','MASUK','AKTIF','DEMO-DTY-IN-06',3366.00,0.00,3399.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(30,1,'4P1065',1,'2026-10-13','KELUAR','AKTIF','DEMO-POY-OUT-06',0.00,3456.00,576.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(31,2,'4D9160',3,'2026-10-13','KELUAR','AKTIF','DEMO-DTY-SJ-06',0.00,3300.00,99.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(32,1,'4P1065',1,'2026-10-14','MASUK','AKTIF','DEMO-POY-IN-06',4032.00,0.00,4608.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(33,2,'4D9160',5,'2026-10-14','MASUK','AKTIF','DEMO-DTY-IN-07',3300.00,0.00,3300.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(34,1,'4P1065',1,'2026-10-14','KELUAR','AKTIF','DEMO-POY-OUT-07',0.00,3456.00,1152.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(35,2,'4D9160',5,'2026-10-14','KELUAR','AKTIF','DEMO-DTY-SJ-07',0.00,3300.00,0.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(36,1,'4P1065',1,'2026-10-15','MASUK','AKTIF','DEMO-POY-IN-07',4032.00,0.00,5184.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(37,2,'4D9160',3,'2026-10-15','MASUK','AKTIF','DEMO-DTY-IN-08',3333.00,0.00,3432.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(38,8,'-',8,'2026-10-15','MASUK','AKTIF','DEMO-PENDUKUNG-IN-02',200.00,0.00,200.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(39,1,'4P1065',1,'2026-10-15','KELUAR','AKTIF','DEMO-POY-OUT-08',0.00,3456.00,1728.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(40,2,'4D9160',3,'2026-10-15','KELUAR','AKTIF','DEMO-DTY-SJ-08',0.00,3300.00,132.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(41,1,'4P1065',1,'2026-10-16','MASUK','AKTIF','DEMO-POY-IN-08',4032.00,0.00,5760.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(42,2,'4D9160',6,'2026-10-16','MASUK','AKTIF','DEMO-DTY-IN-09',3366.00,0.00,3366.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(43,1,'4P1065',1,'2026-10-16','KELUAR','AKTIF','DEMO-POY-OUT-09',0.00,3456.00,2304.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(44,2,'4D9160',6,'2026-10-16','KELUAR','AKTIF','DEMO-DTY-SJ-09',0.00,3300.00,66.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(45,1,'4P1065',1,'2026-10-17','MASUK','AKTIF','DEMO-POY-IN-09',4032.00,0.00,6336.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(46,2,'4D9160',3,'2026-10-17','MASUK','AKTIF','DEMO-DTY-IN-10',3300.00,0.00,3432.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(47,1,'4P1065',1,'2026-10-17','KELUAR','AKTIF','DEMO-POY-OUT-10',0.00,3456.00,2880.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(48,2,'4D9160',3,'2026-10-17','KELUAR','AKTIF','DEMO-DTY-SJ-10',0.00,3300.00,132.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(49,2,'4D9160',3,'2026-10-18','MASUK','AKTIF','DEMO-DTY-IN-11',3333.00,0.00,3465.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(50,1,'4P1065',1,'2026-10-18','RILIS_BUFFER','BUFFER','RILIS-BUFFER-2026-10-18',0.00,576.00,0.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(51,1,'4P1065',1,'2026-10-18','RILIS_BUFFER','AKTIF','RILIS-BUFFER-2026-10-18',576.00,0.00,3456.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(52,1,'4P1065',1,'2026-10-18','KELUAR','AKTIF','DEMO-POY-OUT-11',0.00,3456.00,0.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(53,2,'4D9160',3,'2026-10-18','KELUAR','AKTIF','DEMO-DTY-SJ-11',0.00,3300.00,165.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(54,1,'4P1044',2,'2026-10-19','MASUK','AKTIF','DEMO-POY-IN-10',4032.00,0.00,4032.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(55,1,'4P1044',2,'2026-10-19','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-10',0.00,576.00,3456.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(56,1,'4P1044',2,'2026-10-19','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-10',576.00,0.00,576.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(57,2,'4D9162',7,'2026-10-19','MASUK','AKTIF','DEMO-DTY-IN-12',330.00,0.00,330.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(58,2,'4D9162',5,'2026-10-19','MASUK','AKTIF','DEMO-DTY-IN-12',3036.00,0.00,3036.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(59,1,'4P1044',2,'2026-10-19','KELUAR','AKTIF','DEMO-POY-OUT-12',0.00,3456.00,0.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(60,2,'4D9162',5,'2026-10-19','KELUAR','AKTIF','DEMO-DTY-SJ-12',0.00,3036.00,0.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(61,2,'4D9162',7,'2026-10-19','KELUAR','AKTIF','DEMO-DTY-SJ-12',0.00,264.00,66.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(62,1,'4P1044',2,'2026-10-20','MASUK','AKTIF','DEMO-POY-IN-11',4032.00,0.00,4032.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(63,2,'4D9162',3,'2026-10-20','MASUK','AKTIF','DEMO-DTY-IN-13',3300.00,0.00,3300.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(64,1,'4P1044',2,'2026-10-20','KELUAR','AKTIF','DEMO-POY-OUT-13',0.00,3456.00,576.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(65,2,'4D9162',3,'2026-10-20','KELUAR','AKTIF','DEMO-DTY-SJ-13',0.00,3300.00,0.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(66,1,'4P1044',2,'2026-10-21','MASUK','AKTIF','DEMO-POY-IN-12',2880.00,0.00,3456.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(67,2,'4D9162',6,'2026-10-21','MASUK','AKTIF','DEMO-DTY-IN-14',3333.00,0.00,3333.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(68,1,'4P1044',2,'2026-10-21','KELUAR','AKTIF','DEMO-POY-OUT-14',0.00,2880.00,576.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(69,2,'4D9162',6,'2026-10-21','KELUAR','AKTIF','DEMO-DTY-SJ-14',0.00,3300.00,33.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(70,1,'4P1044',2,'2026-10-22','MASUK','AKTIF','DEMO-POY-IN-13',4032.00,0.00,4608.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(71,2,'4D9162',3,'2026-10-22','MASUK','AKTIF','DEMO-DTY-IN-15',3366.00,0.00,3366.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(72,3,'-',8,'2026-10-22','MASUK','AKTIF','DEMO-PENDUKUNG-IN-03',2500.00,0.00,5956.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(73,1,'4P1044',2,'2026-10-22','KELUAR','AKTIF','DEMO-POY-OUT-15',0.00,3456.00,1152.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(74,2,'4D9162',3,'2026-10-22','KELUAR','AKTIF','DEMO-DTY-SJ-15',0.00,3300.00,66.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(75,1,'4P1044',2,'2026-10-23','MASUK','AKTIF','DEMO-POY-IN-14',4032.00,0.00,5184.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(76,2,'4D9162',3,'2026-10-23','MASUK','AKTIF','DEMO-DTY-IN-16',3300.00,0.00,3366.00,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(77,1,'4P1044',2,'2026-10-23','KELUAR','AKTIF','DEMO-POY-OUT-16',0.00,3456.00,1728.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(78,2,'4D9162',3,'2026-10-23','KELUAR','AKTIF','DEMO-DTY-SJ-16',0.00,3300.00,66.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(79,1,'4P1044',2,'2026-10-24','MASUK','AKTIF','DEMO-POY-IN-15',4032.00,0.00,5760.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(80,2,'4D9162',5,'2026-10-24','MASUK','AKTIF','DEMO-DTY-IN-17',3333.00,0.00,3333.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(81,1,'4P1044',2,'2026-10-24','KELUAR','AKTIF','DEMO-POY-OUT-17',0.00,3456.00,2304.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(82,2,'4D9162',5,'2026-10-24','KELUAR','AKTIF','DEMO-DTY-SJ-17',0.00,3300.00,33.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(83,2,'4D9162',3,'2026-10-25','MASUK','AKTIF','DEMO-DTY-IN-18',3366.00,0.00,3432.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(84,1,'4P1044',2,'2026-10-25','RILIS_BUFFER','BUFFER','RILIS-BUFFER-2026-10-25',0.00,576.00,0.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(85,1,'4P1044',2,'2026-10-25','RILIS_BUFFER','AKTIF','RILIS-BUFFER-2026-10-25',576.00,0.00,2880.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(86,1,'4P1044',2,'2026-10-25','KELUAR','AKTIF','DEMO-POY-OUT-18',0.00,2880.00,0.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(87,2,'4D9162',3,'2026-10-25','KELUAR','AKTIF','DEMO-DTY-SJ-18',0.00,3300.00,132.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(88,1,'4P1065',1,'2026-10-26','MASUK','AKTIF','DEMO-POY-IN-16',4032.00,0.00,4032.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(89,1,'4P1065',1,'2026-10-26','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-16',0.00,576.00,3456.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(90,1,'4P1065',1,'2026-10-26','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-16',576.00,0.00,576.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(91,2,'4D9163',7,'2026-10-26','MASUK','AKTIF','DEMO-DTY-IN-19',330.00,0.00,330.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(92,2,'4D9163',6,'2026-10-26','MASUK','AKTIF','DEMO-DTY-IN-19',2970.00,0.00,2970.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(93,1,'4P1065',1,'2026-10-26','KELUAR','AKTIF','DEMO-POY-OUT-19',0.00,3456.00,0.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(94,2,'4D9163',6,'2026-10-26','KELUAR','AKTIF','DEMO-DTY-SJ-19',0.00,2970.00,0.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(95,2,'4D9163',7,'2026-10-26','KELUAR','AKTIF','DEMO-DTY-SJ-19',0.00,330.00,0.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(96,1,'4P1065',1,'2026-10-27','MASUK','AKTIF','DEMO-POY-IN-17',4032.00,0.00,4032.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(97,2,'4D9163',3,'2026-10-27','MASUK','AKTIF','DEMO-DTY-IN-20',3333.00,0.00,3399.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(98,1,'4P1065',1,'2026-10-27','KELUAR','AKTIF','DEMO-POY-OUT-20',0.00,3456.00,576.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(99,2,'4D9163',3,'2026-10-27','KELUAR','AKTIF','DEMO-DTY-SJ-20',0.00,3300.00,99.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(100,1,'4P1065',1,'2026-10-28','MASUK','AKTIF','DEMO-POY-IN-18',2880.00,0.00,3456.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(101,2,'4D9163',3,'2026-10-28','MASUK','AKTIF','DEMO-DTY-IN-21',3366.00,0.00,3465.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(102,1,'4P1065',1,'2026-10-28','KELUAR','AKTIF','DEMO-POY-OUT-21',0.00,2880.00,576.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(103,2,'4D9163',3,'2026-10-28','KELUAR','AKTIF','DEMO-DTY-SJ-21',0.00,3300.00,165.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(104,1,'4P1065',1,'2026-10-29','MASUK','AKTIF','DEMO-POY-IN-19',4032.00,0.00,4608.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(105,2,'4D9163',5,'2026-10-29','MASUK','AKTIF','DEMO-DTY-IN-22',3300.00,0.00,3333.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(106,4,'-',8,'2026-10-29','MASUK','AKTIF','DEMO-PENDUKUNG-IN-04',450.00,0.00,850.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(107,1,'4P1065',1,'2026-10-29','KELUAR','AKTIF','DEMO-POY-OUT-22',0.00,3456.00,1152.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(108,2,'4D9163',5,'2026-10-29','KELUAR','AKTIF','DEMO-DTY-SJ-22',0.00,3300.00,33.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(109,1,'4P1065',1,'2026-10-30','MASUK','AKTIF','DEMO-POY-IN-20',4032.00,0.00,5184.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(110,2,'4D9163',3,'2026-10-30','MASUK','AKTIF','DEMO-DTY-IN-23',3333.00,0.00,3498.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(111,1,'4P1065',1,'2026-10-30','KELUAR','AKTIF','DEMO-POY-OUT-23',0.00,3456.00,1728.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(112,2,'4D9163',3,'2026-10-30','KELUAR','AKTIF','DEMO-DTY-SJ-23',0.00,3300.00,198.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(113,1,'4P1065',1,'2026-10-31','MASUK','AKTIF','DEMO-POY-IN-21',4032.00,0.00,5760.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(114,2,'4D9163',6,'2026-10-31','MASUK','AKTIF','DEMO-DTY-IN-24',3366.00,0.00,3366.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(115,1,'4P1065',1,'2026-10-31','KELUAR','AKTIF','DEMO-POY-OUT-24',0.00,3456.00,2304.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(116,2,'4D9163',6,'2026-10-31','KELUAR','AKTIF','DEMO-DTY-SJ-24',0.00,3300.00,66.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(117,2,'4D9163',3,'2026-11-01','MASUK','AKTIF','DEMO-DTY-IN-25',3300.00,0.00,3498.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(118,1,'4P1065',1,'2026-11-01','RILIS_BUFFER','BUFFER','RILIS-BUFFER-2026-11-01',0.00,576.00,0.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(119,1,'4P1065',1,'2026-11-01','RILIS_BUFFER','AKTIF','RILIS-BUFFER-2026-11-01',576.00,0.00,2880.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(120,1,'4P1065',1,'2026-11-01','KELUAR','AKTIF','DEMO-POY-OUT-25',0.00,2880.00,0.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(121,2,'4D9163',3,'2026-11-01','KELUAR','AKTIF','DEMO-DTY-SJ-25',0.00,3300.00,198.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(122,1,'4P1044',2,'2026-11-02','MASUK','AKTIF','DEMO-POY-IN-22',4032.00,0.00,4032.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(123,1,'4P1044',2,'2026-11-02','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-22',0.00,576.00,3456.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(124,1,'4P1044',2,'2026-11-02','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-22',576.00,0.00,576.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(125,2,'4D9160',3,'2026-11-02','MASUK','AKTIF','DEMO-DTY-IN-26',3333.00,0.00,3498.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(126,1,'4P1044',2,'2026-11-02','KELUAR','AKTIF','DEMO-POY-OUT-26',0.00,3456.00,0.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(127,2,'4D9160',3,'2026-11-02','KELUAR','AKTIF','DEMO-DTY-SJ-26',0.00,3300.00,198.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(128,1,'4P1044',2,'2026-11-03','MASUK','AKTIF','DEMO-POY-IN-23',4032.00,0.00,4032.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(129,2,'4D9160',5,'2026-11-03','MASUK','AKTIF','DEMO-DTY-IN-27',3366.00,0.00,3366.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(130,1,'4P1044',2,'2026-11-03','KELUAR','AKTIF','DEMO-POY-OUT-27',0.00,3456.00,576.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(131,2,'4D9160',5,'2026-11-03','KELUAR','AKTIF','DEMO-DTY-SJ-27',0.00,3300.00,66.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(132,1,'4P1044',2,'2026-11-04','MASUK','AKTIF','DEMO-POY-IN-24',4032.00,0.00,4608.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(133,2,'4D9160',3,'2026-11-04','MASUK','AKTIF','DEMO-DTY-IN-28',3300.00,0.00,3498.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(134,1,'4P1044',2,'2026-11-04','KELUAR','AKTIF','DEMO-POY-OUT-28',0.00,3456.00,1152.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(135,2,'4D9160',3,'2026-11-04','KELUAR','AKTIF','DEMO-DTY-SJ-28',0.00,3300.00,198.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(136,1,'4P1044',2,'2026-11-05','MASUK','AKTIF','DEMO-POY-IN-25',4032.00,0.00,5184.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(137,2,'4D9160',6,'2026-11-05','MASUK','AKTIF','DEMO-DTY-IN-29',3333.00,0.00,3399.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(138,1,'4P1044',2,'2026-11-05','KELUAR','AKTIF','DEMO-POY-OUT-29',0.00,3456.00,1728.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(139,2,'4D9160',6,'2026-11-05','KELUAR','AKTIF','DEMO-DTY-SJ-29',0.00,3300.00,99.00,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(140,1,'4P1044',2,'2026-11-06','MASUK','AKTIF','DEMO-POY-IN-26',4032.00,0.00,5760.00,2,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(141,2,'4D9160',3,'2026-11-06','MASUK','AKTIF','DEMO-DTY-IN-30',3366.00,0.00,3564.00,2,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(142,1,'4P1044',2,'2026-11-06','KELUAR','AKTIF','DEMO-POY-OUT-30',0.00,3456.00,2304.00,2,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(143,2,'4D9160',3,'2026-11-06','KELUAR','AKTIF','DEMO-DTY-SJ-30',0.00,3300.00,264.00,2,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(144,1,'4P1044',2,'2026-11-06','MASUK','AKTIF','DEMO-POY-IN-27',4032.00,0.00,6336.00,2,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(145,4,'-',8,'2026-11-30','ADJUSTMENT_IN','AKTIF','IN-2026-11-1',15.00,0.00,865.00,1,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(146,8,'-',8,'2026-11-30','ADJUSTMENT_OUT','AKTIF','OUT-2026-11-1',0.00,5.00,195.00,1,'2026-11-05 22:49:16','2026-11-05 22:49:16');
/*!40000 ALTER TABLE `mutasi_stok` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `nomor_dokumen` DISABLE KEYS */;
INSERT INTO `nomor_dokumen` VALUES
(1,'SURAT_JALAN_DTY','2026-11',6,'2026-11-05 22:49:13','2026-11-05 22:49:16'),
(2,'GATE_PASS','2026-11',0,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(3,'TRX_MASUK','2026-11',1,'2026-11-05 22:49:13','2026-11-05 22:49:16'),
(4,'TRX_KELUAR','2026-11',1,'2026-11-05 22:49:13','2026-11-05 22:49:16'),
(5,'SURAT_JALAN_DTY','2026-10',24,'2026-11-05 22:49:13','2026-11-05 22:49:15');
/*!40000 ALTER TABLE `nomor_dokumen` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES
('35fe2a0f-1314-471e-b99a-71e4d1354eef','Filament\\Notifications\\DatabaseNotification','App\\Models\\User',3,'{\"actions\":[],\"body\":\"Sisa 195,00 KG dari minimum 200,00 KG.\",\"color\":null,\"duration\":\"persistent\",\"icon\":\"heroicon-o-exclamation-triangle\",\"iconColor\":\"warning\",\"iconSize\":null,\"status\":\"warning\",\"title\":\"Stok DTY CONING OIL,TM368 BN menipis\",\"view\":null,\"viewData\":[],\"format\":\"filament\"}',NULL,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
('ad4eaa62-d21e-4521-97ee-5e6e2d55c389','Filament\\Notifications\\DatabaseNotification','App\\Models\\User',1,'{\"actions\":[],\"body\":\"Sisa 195,00 KG dari minimum 200,00 KG.\",\"color\":null,\"duration\":\"persistent\",\"icon\":\"heroicon-o-exclamation-triangle\",\"iconColor\":\"warning\",\"iconSize\":null,\"status\":\"warning\",\"title\":\"Stok DTY CONING OIL,TM368 BN menipis\",\"view\":null,\"viewData\":[],\"format\":\"filament\"}',NULL,'2026-11-05 22:49:16','2026-11-05 22:49:16');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `satuan` DISABLE KEYS */;
INSERT INTO `satuan` VALUES
(1,'KG','Kilogram','2026-11-05 22:49:13','2026-11-05 22:49:13'),
(2,'BOX','Box / Carton','2026-11-05 22:49:13','2026-11-05 22:49:13'),
(3,'PALLET','Pallet','2026-11-05 22:49:13','2026-11-05 22:49:13'),
(4,'PCS','Pieces','2026-11-05 22:49:13','2026-11-05 22:49:13'),
(5,'BOBBIN','Bobbin / Gulungan','2026-11-05 22:49:13','2026-11-05 22:49:13');
/*!40000 ALTER TABLE `satuan` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES
('MPmWINuvM7w2Nm9V7KbmdJGnCaqpakcNLGPpxpYr',NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/156.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJabmhxejJ4a2lqYm5xTXhENUlTa0lHOHNVU29hOEVWcHh0MGFMMHBLIiwiZmlsYW1lbnQiOnsibm90aWZpY2F0aW9ucyI6eyJtYWlsYm94IjoiMDBiZTQ5Zjk4NTBlMmQ5YzJhOTIzZTUzMTBhMGU3YjBkYzE3N2IzZDcxMDBmMTIxZmFhNDRlNTQ3NWE3MzRhMiJ9fSwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL2FkbWluXC9wZW5lcmltYWFuLXBveSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYWRtaW5cL2xvZ2luIiwicm91dGUiOiJmaWxhbWVudC5hZG1pbi5hdXRoLmxvZ2luIn19',1793918956);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `stok_barang` DISABLE KEYS */;
INSERT INTO `stok_barang` VALUES
(1,1,'4P1044',2,6336.00,576.00,3708,0,'2026-11-05 22:49:13','2026-11-05 22:49:16'),
(2,2,'4D9163',3,198.00,0.00,3636,606,'2026-11-05 22:49:13','2026-11-05 22:49:15'),
(3,3,'-',8,5956.00,0.00,0,0,'2026-11-05 22:49:13','2026-11-05 22:49:14'),
(4,4,'-',8,865.00,0.00,0,0,'2026-11-05 22:49:13','2026-11-05 22:49:16'),
(5,2,'4D9163',5,33.00,0.00,1206,201,'2026-11-05 22:49:14','2026-11-05 22:49:15'),
(6,2,'4D9163',6,66.00,0.00,1752,292,'2026-11-05 22:49:14','2026-11-05 22:49:15'),
(7,1,'4P1065',1,0.00,0.00,2952,0,'2026-11-05 22:49:14','2026-11-05 22:49:15'),
(8,2,'4D9160',3,264.00,0.00,4848,808,'2026-11-05 22:49:14','2026-11-05 22:49:16'),
(9,2,'4D9160',5,66.00,0.00,1212,202,'2026-11-05 22:49:14','2026-11-05 22:49:15'),
(10,8,'-',8,195.00,0.00,0,0,'2026-11-05 22:49:14','2026-11-05 22:49:16'),
(11,2,'4D9160',6,99.00,0.00,1218,203,'2026-11-05 22:49:14','2026-11-05 22:49:15'),
(12,2,'4D9162',7,66.00,0.00,60,10,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(13,2,'4D9162',5,33.00,0.00,1158,193,'2026-11-05 22:49:14','2026-11-05 22:49:15'),
(14,2,'4D9162',3,132.00,0.00,2424,404,'2026-11-05 22:49:14','2026-11-05 22:49:15'),
(15,2,'4D9162',6,33.00,0.00,606,101,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(16,2,'4D9163',7,0.00,0.00,60,10,'2026-11-05 22:49:15','2026-11-05 22:49:15');
/*!40000 ALTER TABLE `stok_barang` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `stok_opname` DISABLE KEYS */;
INSERT INTO `stok_opname` VALUES
(1,'OPN-IN-2026-11-1','2026-11-30',4,'-',8,850.00,865.00,'Opname demo selisih positif.',1,64,NULL,'2026-11-05 22:49:16','2026-11-05 22:49:16',15.00),
(2,'OPN-OUT-2026-11-1','2026-11-30',8,'-',8,200.00,195.00,'Opname demo selisih negatif kecil.',1,NULL,62,'2026-11-05 22:49:16','2026-11-05 22:49:16',-5.00),
(3,'OPN-20261130-3-8-X-0e208d','2026-11-30',3,'-',8,5956.00,5956.00,'Opname demo selisih nol.',1,NULL,NULL,'2026-11-05 22:49:16','2026-11-05 22:49:16',0.00);
/*!40000 ALTER TABLE `stok_opname` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `supplier_customer` DISABLE KEYS */;
INSERT INTO `supplier_customer` VALUES
(1,'IPCI','IPCI - 300097905','BOTH',NULL,'PT. Indorama Polychem Indonesia','2026-11-05 22:49:13','2026-11-05 22:49:13',NULL),
(2,'STORE-IPCI','Store IPCI','SUPPLIER',NULL,'PT. Indorama Polychem Indonesia — Dept. STORE','2026-11-05 22:49:13','2026-11-05 22:49:13',NULL),
(3,'AREA PRODUKSI','Area Produksi DTY PEI','INTERNAL',NULL,'Area Doffing / Transit Produksi','2026-11-05 22:49:13','2026-11-05 22:49:13',NULL);
/*!40000 ALTER TABLE `supplier_customer` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `transaksi_keluar` DISABLE KEYS */;
INSERT INTO `transaksi_keluar` VALUES
(1,'DEMO-POY-OUT-01','POY_PRODUKSI',NULL,'2026-10-08',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(2,'DEMO-DTY-SJ-01','DTY_SURAT_JALAN','1/DTY/PEI/X/2026','2026-10-08',1,'B 1234 DEMO','Joko Demo','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(3,'DEMO-POY-OUT-02','POY_PRODUKSI',NULL,'2026-10-09',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(4,'DEMO-DTY-SJ-02','DTY_SURAT_JALAN','2/DTY/PEI/X/2026','2026-10-09',1,'B 5678 KIRIM','Slamet Kirim','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(5,'DEMO-POY-OUT-03','POY_PRODUKSI',NULL,'2026-10-10',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(6,'DEMO-DTY-SJ-03','DTY_SURAT_JALAN','3/DTY/PEI/X/2026','2026-10-10',1,'B 9012 ANTAR','Andi Antar','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(7,'DEMO-POY-OUT-04','POY_PRODUKSI',NULL,'2026-10-11',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(8,'DEMO-DTY-SJ-04','DTY_SURAT_JALAN','4/DTY/PEI/X/2026','2026-10-11',1,'B 3456 ANGKUT','Budi Angkut','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(9,'DEMO-POY-OUT-05','POY_PRODUKSI',NULL,'2026-10-12',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(10,'DEMO-DTY-SJ-05','DTY_SURAT_JALAN','5/DTY/PEI/X/2026','2026-10-12',1,'B 1234 DEMO','Joko Demo','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(11,'DEMO-POY-OUT-06','POY_PRODUKSI',NULL,'2026-10-13',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(12,'DEMO-DTY-SJ-06','DTY_SURAT_JALAN','6/DTY/PEI/X/2026','2026-10-13',1,'B 5678 KIRIM','Slamet Kirim','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(13,'DEMO-POY-OUT-07','POY_PRODUKSI',NULL,'2026-10-14',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(14,'DEMO-DTY-SJ-07','DTY_SURAT_JALAN','7/DTY/PEI/X/2026','2026-10-14',1,'B 9012 ANTAR','Andi Antar','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(15,'DEMO-POY-OUT-08','POY_PRODUKSI',NULL,'2026-10-15',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(16,'DEMO-DTY-SJ-08','DTY_SURAT_JALAN','8/DTY/PEI/X/2026','2026-10-15',1,'B 3456 ANGKUT','Budi Angkut','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(17,'DEMO-POY-OUT-09','POY_PRODUKSI',NULL,'2026-10-16',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(18,'DEMO-DTY-SJ-09','DTY_SURAT_JALAN','9/DTY/PEI/X/2026','2026-10-16',1,'B 1234 DEMO','Joko Demo','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(19,'DEMO-POY-OUT-10','POY_PRODUKSI',NULL,'2026-10-17',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(20,'DEMO-DTY-SJ-10','DTY_SURAT_JALAN','10/DTY/PEI/X/2026','2026-10-17',1,'B 5678 KIRIM','Slamet Kirim','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(21,'DEMO-POY-OUT-11','POY_PRODUKSI',NULL,'2026-10-18',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(22,'DEMO-DTY-SJ-11','DTY_SURAT_JALAN','11/DTY/PEI/X/2026','2026-10-18',1,'B 9012 ANTAR','Andi Antar','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(23,'DEMO-POY-OUT-12','POY_PRODUKSI',NULL,'2026-10-19',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(24,'DEMO-DTY-SJ-12','DTY_SURAT_JALAN','12/DTY/PEI/X/2026','2026-10-19',1,'B 3456 ANGKUT','Budi Angkut','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(25,'DEMO-POY-OUT-13','POY_PRODUKSI',NULL,'2026-10-20',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(26,'DEMO-DTY-SJ-13','DTY_SURAT_JALAN','13/DTY/PEI/X/2026','2026-10-20',1,'B 1234 DEMO','Joko Demo','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(27,'DEMO-POY-OUT-14','POY_PRODUKSI',NULL,'2026-10-21',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(28,'DEMO-DTY-SJ-14','DTY_SURAT_JALAN','14/DTY/PEI/X/2026','2026-10-21',1,'B 5678 KIRIM','Slamet Kirim','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(29,'DEMO-POY-OUT-15','POY_PRODUKSI',NULL,'2026-10-22',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(30,'DEMO-DTY-SJ-15','DTY_SURAT_JALAN','15/DTY/PEI/X/2026','2026-10-22',1,'B 9012 ANTAR','Andi Antar','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(31,'DEMO-POY-OUT-16','POY_PRODUKSI',NULL,'2026-10-23',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:15'),
(32,'DEMO-DTY-SJ-16','DTY_SURAT_JALAN','16/DTY/PEI/X/2026','2026-10-23',1,'B 3456 ANGKUT','Budi Angkut','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(33,'DEMO-POY-OUT-17','POY_PRODUKSI',NULL,'2026-10-24',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(34,'DEMO-DTY-SJ-17','DTY_SURAT_JALAN','17/DTY/PEI/X/2026','2026-10-24',1,'B 1234 DEMO','Joko Demo','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(35,'DEMO-POY-OUT-18','POY_PRODUKSI',NULL,'2026-10-25',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(36,'DEMO-DTY-SJ-18','DTY_SURAT_JALAN','18/DTY/PEI/X/2026','2026-10-25',1,'B 5678 KIRIM','Slamet Kirim','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(37,'DEMO-POY-OUT-19','POY_PRODUKSI',NULL,'2026-10-26',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(38,'DEMO-DTY-SJ-19','DTY_SURAT_JALAN','19/DTY/PEI/X/2026','2026-10-26',1,'B 9012 ANTAR','Andi Antar','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(39,'DEMO-POY-OUT-20','POY_PRODUKSI',NULL,'2026-10-27',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(40,'DEMO-DTY-SJ-20','DTY_SURAT_JALAN','20/DTY/PEI/X/2026','2026-10-27',1,'B 3456 ANGKUT','Budi Angkut','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(41,'DEMO-POY-OUT-21','POY_PRODUKSI',NULL,'2026-10-28',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(42,'DEMO-DTY-SJ-21','DTY_SURAT_JALAN','21/DTY/PEI/X/2026','2026-10-28',1,'B 1234 DEMO','Joko Demo','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(43,'DEMO-POY-OUT-22','POY_PRODUKSI',NULL,'2026-10-29',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(44,'DEMO-DTY-SJ-22','DTY_SURAT_JALAN','22/DTY/PEI/X/2026','2026-10-29',1,'B 5678 KIRIM','Slamet Kirim','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(45,'DEMO-POY-OUT-23','POY_PRODUKSI',NULL,'2026-10-30',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(46,'DEMO-DTY-SJ-23','DTY_SURAT_JALAN','23/DTY/PEI/X/2026','2026-10-30',1,'B 9012 ANTAR','Andi Antar','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(47,'DEMO-POY-OUT-24','POY_PRODUKSI',NULL,'2026-10-31',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(48,'DEMO-DTY-SJ-24','DTY_SURAT_JALAN','24/DTY/PEI/X/2026','2026-10-31',1,'B 3456 ANGKUT','Budi Angkut','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(49,'DEMO-POY-OUT-25','POY_PRODUKSI',NULL,'2026-11-01',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(50,'DEMO-DTY-SJ-25','DTY_SURAT_JALAN','1/DTY/PEI/XI/2026','2026-11-01',1,'B 1234 DEMO','Joko Demo','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(51,'DEMO-POY-OUT-26','POY_PRODUKSI',NULL,'2026-11-02',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(52,'DEMO-DTY-SJ-26','DTY_SURAT_JALAN','2/DTY/PEI/XI/2026','2026-11-02',1,'B 5678 KIRIM','Slamet Kirim','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(53,'DEMO-POY-OUT-27','POY_PRODUKSI',NULL,'2026-11-03',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(54,'DEMO-DTY-SJ-27','DTY_SURAT_JALAN','3/DTY/PEI/XI/2026','2026-11-03',1,'B 9012 ANTAR','Andi Antar','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(55,'DEMO-POY-OUT-28','POY_PRODUKSI',NULL,'2026-11-04',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(56,'DEMO-DTY-SJ-28','DTY_SURAT_JALAN','4/DTY/PEI/XI/2026','2026-11-04',1,'B 3456 ANGKUT','Budi Angkut','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(57,'DEMO-POY-OUT-29','POY_PRODUKSI',NULL,'2026-11-05',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(58,'DEMO-DTY-SJ-29','DTY_SURAT_JALAN','5/DTY/PEI/XI/2026','2026-11-05',1,'B 1234 DEMO','Joko Demo','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(59,'DEMO-POY-OUT-30','POY_PRODUKSI',NULL,'2026-11-06',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(60,'DEMO-DTY-SJ-30','DTY_SURAT_JALAN','6/DTY/PEI/XI/2026','2026-11-06',1,'B 5678 KIRIM','Slamet Kirim','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(61,'DEMO-DTY-DRAFT-01','DTY_SURAT_JALAN',NULL,'2026-11-06',1,'B 9999 DRAF','Sopir Draf','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'DRAFT',NULL,NULL,NULL,2,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(62,'OUT-2026-11-1','ADJUSTMENT_OUT',NULL,'2026-11-30',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Opname demo selisih negatif kecil.','POSTED',NULL,NULL,NULL,1,'2026-11-05 22:49:16','2026-11-05 22:49:16');
/*!40000 ALTER TABLE `transaksi_keluar` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `transaksi_keluar_detail` DISABLE KEYS */;
INSERT INTO `transaksi_keluar_detail` VALUES
(1,1,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(2,2,2,'4D9163',3,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(3,3,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(4,4,2,'4D9163',5,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(5,5,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(6,6,2,'4D9163',3,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(7,7,1,'4P1044',2,NULL,NULL,NULL,1728.00,NULL,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(8,8,2,'4D9163',6,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(9,9,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(10,10,2,'4D9160',3,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(11,11,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(12,12,2,'4D9160',3,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(13,13,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(14,14,2,'4D9160',5,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(15,15,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(16,16,2,'4D9160',3,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(17,17,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(18,18,2,'4D9160',6,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(19,19,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(20,20,2,'4D9160',3,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(21,21,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(22,22,2,'4D9160',3,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(23,23,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(24,24,2,'4D9162',5,92.00,NULL,552,3036.00,3288.08,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(25,24,2,'4D9162',7,8.00,NULL,48,264.00,285.92,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(26,25,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(27,26,2,'4D9162',3,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(28,27,1,'4P1044',2,NULL,NULL,NULL,2880.00,NULL,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(29,28,2,'4D9162',6,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(30,29,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(31,30,2,'4D9162',3,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(32,31,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(33,32,2,'4D9162',3,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(34,33,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(35,34,2,'4D9162',5,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(36,35,1,'4P1044',2,NULL,NULL,NULL,2880.00,NULL,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(37,36,2,'4D9162',3,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(38,37,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(39,38,2,'4D9163',6,90.00,NULL,540,2970.00,3216.60,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(40,38,2,'4D9163',7,10.00,NULL,60,330.00,357.40,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(41,39,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(42,40,2,'4D9163',3,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(43,41,1,'4P1065',1,NULL,NULL,NULL,2880.00,NULL,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(44,42,2,'4D9163',3,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(45,43,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(46,44,2,'4D9163',5,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(47,45,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(48,46,2,'4D9163',3,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(49,47,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(50,48,2,'4D9163',6,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(51,49,1,'4P1065',1,NULL,NULL,NULL,2880.00,NULL,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(52,50,2,'4D9163',3,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(53,51,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(54,52,2,'4D9160',3,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(55,53,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(56,54,2,'4D9160',5,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(57,55,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(58,56,2,'4D9160',3,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(59,57,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(60,58,2,'4D9160',6,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(61,59,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(62,60,2,'4D9160',3,100.00,NULL,600,3300.00,3574.00,NULL,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(63,61,2,'4D9169',3,10.00,NULL,60,330.00,357.40,NULL,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(64,62,8,'-',8,NULL,NULL,NULL,5.00,NULL,NULL,'2026-11-05 22:49:16','2026-11-05 22:49:16');
/*!40000 ALTER TABLE `transaksi_keluar_detail` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `transaksi_masuk` DISABLE KEYS */;
INSERT INTO `transaksi_masuk` VALUES
(1,'DEMO-POY-IN-01','POY_IPCI',NULL,'2026-10-08',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(2,'DEMO-DTY-IN-01','DTY_PRODUKSI',NULL,'2026-10-08',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(3,'DEMO-PENDUKUNG-IN-01','PENDUKUNG_GATEPASS','DEMO-GP-001','2026-10-08',2,NULL,NULL,NULL,'Budi Santoso',NULL,NULL,NULL,NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(4,'DEMO-POY-IN-02','POY_IPCI',NULL,'2026-10-09',1,NULL,NULL,2,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(5,'DEMO-DTY-IN-02','DTY_PRODUKSI',NULL,'2026-10-09',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:13','2026-11-05 22:49:14'),
(6,'DEMO-POY-IN-03','POY_IPCI',NULL,'2026-10-10',1,NULL,NULL,3,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(7,'DEMO-DTY-IN-03','DTY_PRODUKSI',NULL,'2026-10-10',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(8,'DEMO-DTY-IN-04','DTY_PRODUKSI',NULL,'2026-10-11',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(9,'DEMO-POY-IN-04','POY_IPCI',NULL,'2026-10-12',1,NULL,NULL,2,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(10,'DEMO-DTY-IN-05','DTY_PRODUKSI',NULL,'2026-10-12',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(11,'DEMO-POY-IN-05','POY_IPCI',NULL,'2026-10-13',1,NULL,NULL,3,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(12,'DEMO-DTY-IN-06','DTY_PRODUKSI',NULL,'2026-10-13',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(13,'DEMO-POY-IN-06','POY_IPCI',NULL,'2026-10-14',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(14,'DEMO-DTY-IN-07','DTY_PRODUKSI',NULL,'2026-10-14',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(15,'DEMO-POY-IN-07','POY_IPCI',NULL,'2026-10-15',1,NULL,NULL,2,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(16,'DEMO-DTY-IN-08','DTY_PRODUKSI',NULL,'2026-10-15',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(17,'DEMO-PENDUKUNG-IN-02','PENDUKUNG_GATEPASS','DEMO-GP-002','2026-10-15',2,NULL,NULL,NULL,'Budi Santoso',NULL,NULL,NULL,NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(18,'DEMO-POY-IN-08','POY_IPCI',NULL,'2026-10-16',1,NULL,NULL,3,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(19,'DEMO-DTY-IN-09','DTY_PRODUKSI',NULL,'2026-10-16',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(20,'DEMO-POY-IN-09','POY_IPCI',NULL,'2026-10-17',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(21,'DEMO-DTY-IN-10','DTY_PRODUKSI',NULL,'2026-10-17',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(22,'DEMO-DTY-IN-11','DTY_PRODUKSI',NULL,'2026-10-18',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(23,'DEMO-POY-IN-10','POY_IPCI',NULL,'2026-10-19',1,NULL,NULL,3,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(24,'DEMO-DTY-IN-12','DTY_PRODUKSI',NULL,'2026-10-19',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(25,'DEMO-POY-IN-11','POY_IPCI',NULL,'2026-10-20',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(26,'DEMO-DTY-IN-13','DTY_PRODUKSI',NULL,'2026-10-20',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(27,'DEMO-POY-IN-12','POY_IPCI',NULL,'2026-10-21',1,NULL,NULL,2,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(28,'DEMO-DTY-IN-14','DTY_PRODUKSI',NULL,'2026-10-21',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(29,'DEMO-POY-IN-13','POY_IPCI',NULL,'2026-10-22',1,NULL,NULL,3,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(30,'DEMO-DTY-IN-15','DTY_PRODUKSI',NULL,'2026-10-22',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(31,'DEMO-PENDUKUNG-IN-03','PENDUKUNG_GATEPASS','DEMO-GP-003','2026-10-22',2,NULL,NULL,NULL,'Budi Santoso',NULL,NULL,NULL,NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(32,'DEMO-POY-IN-14','POY_IPCI',NULL,'2026-10-23',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(33,'DEMO-DTY-IN-16','DTY_PRODUKSI',NULL,'2026-10-23',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(34,'DEMO-POY-IN-15','POY_IPCI',NULL,'2026-10-24',1,NULL,NULL,2,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(35,'DEMO-DTY-IN-17','DTY_PRODUKSI',NULL,'2026-10-24',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(36,'DEMO-DTY-IN-18','DTY_PRODUKSI',NULL,'2026-10-25',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(37,'DEMO-POY-IN-16','POY_IPCI',NULL,'2026-10-26',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(38,'DEMO-DTY-IN-19','DTY_PRODUKSI',NULL,'2026-10-26',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(39,'DEMO-POY-IN-17','POY_IPCI',NULL,'2026-10-27',1,NULL,NULL,2,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(40,'DEMO-DTY-IN-20','DTY_PRODUKSI',NULL,'2026-10-27',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(41,'DEMO-POY-IN-18','POY_IPCI',NULL,'2026-10-28',1,NULL,NULL,3,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(42,'DEMO-DTY-IN-21','DTY_PRODUKSI',NULL,'2026-10-28',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(43,'DEMO-POY-IN-19','POY_IPCI',NULL,'2026-10-29',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(44,'DEMO-DTY-IN-22','DTY_PRODUKSI',NULL,'2026-10-29',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(45,'DEMO-PENDUKUNG-IN-04','PENDUKUNG_GATEPASS','DEMO-GP-004','2026-10-29',2,NULL,NULL,NULL,'Budi Santoso',NULL,NULL,NULL,NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(46,'DEMO-POY-IN-20','POY_IPCI',NULL,'2026-10-30',1,NULL,NULL,2,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(47,'DEMO-DTY-IN-23','DTY_PRODUKSI',NULL,'2026-10-30',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(48,'DEMO-POY-IN-21','POY_IPCI',NULL,'2026-10-31',1,NULL,NULL,3,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(49,'DEMO-DTY-IN-24','DTY_PRODUKSI',NULL,'2026-10-31',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(50,'DEMO-DTY-IN-25','DTY_PRODUKSI',NULL,'2026-11-01',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(51,'DEMO-POY-IN-22','POY_IPCI',NULL,'2026-11-02',1,NULL,NULL,2,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(52,'DEMO-DTY-IN-26','DTY_PRODUKSI',NULL,'2026-11-02',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(53,'DEMO-POY-IN-23','POY_IPCI',NULL,'2026-11-03',1,NULL,NULL,3,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(54,'DEMO-DTY-IN-27','DTY_PRODUKSI',NULL,'2026-11-03',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(55,'DEMO-POY-IN-24','POY_IPCI',NULL,'2026-11-04',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(56,'DEMO-DTY-IN-28','DTY_PRODUKSI',NULL,'2026-11-04',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(57,'DEMO-POY-IN-25','POY_IPCI',NULL,'2026-11-05',1,NULL,NULL,2,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(58,'DEMO-DTY-IN-29','DTY_PRODUKSI',NULL,'2026-11-05',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(59,'DEMO-POY-IN-26','POY_IPCI',NULL,'2026-11-06',1,NULL,NULL,3,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:15','2026-11-05 22:49:16'),
(60,'DEMO-DTY-IN-30','DTY_PRODUKSI',NULL,'2026-11-06',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(61,'DEMO-POY-IN-27','POY_IPCI',NULL,'2026-11-06',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(62,'DEMO-POY-DRAFT-01','POY_IPCI',NULL,'2026-11-06',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'DRAFT',NULL,NULL,NULL,2,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(63,'DEMO-POY-DRAFT-02','POY_IPCI',NULL,'2026-11-06',1,NULL,NULL,3,'Budi Santoso',NULL,NULL,NULL,NULL,'DRAFT',NULL,NULL,NULL,2,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(64,'IN-2026-11-1','ADJUSTMENT_IN',NULL,'2026-11-30',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Opname demo selisih positif.','POSTED',NULL,NULL,NULL,1,'2026-11-05 22:49:16','2026-11-05 22:49:16');
/*!40000 ALTER TABLE `transaksi_masuk` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaksi_masuk_detail` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaksi_masuk_id` bigint(20) unsigned NOT NULL,
  `barang_id` bigint(20) unsigned NOT NULL,
  `merge` varchar(50) NOT NULL,
  `batch` varchar(50) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `transaksi_masuk_detail` DISABLE KEYS */;
INSERT INTO `transaksi_masuk_detail` VALUES
(1,1,1,'4P1044',NULL,2,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(2,2,2,'4D9163',NULL,3,100.00,2,600,3300.00,'[GROSS=3574.00]','2026-11-05 22:49:13','2026-11-05 22:49:13'),
(3,3,3,'-','GRN-VELVET',8,3456.00,4,NULL,3456.00,NULL,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(4,3,4,'-','GRN-VELVET',8,400.00,4,NULL,400.00,NULL,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(5,4,1,'4P1044',NULL,2,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(6,5,2,'4D9163',NULL,5,101.00,2,606,3333.00,'[GROSS=3609.74]','2026-11-05 22:49:13','2026-11-05 22:49:13'),
(7,6,1,'4P1044',NULL,2,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(8,7,2,'4D9163',NULL,3,102.00,2,612,3366.00,'[GROSS=3645.48]','2026-11-05 22:49:14','2026-11-05 22:49:14'),
(9,8,2,'4D9163',NULL,6,100.00,2,600,3300.00,'[GROSS=3574.00]','2026-11-05 22:49:14','2026-11-05 22:49:14'),
(10,9,1,'4P1065',NULL,1,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(11,10,2,'4D9160',NULL,3,101.00,2,606,3333.00,'[GROSS=3609.74]','2026-11-05 22:49:14','2026-11-05 22:49:14'),
(12,11,1,'4P1065',NULL,1,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(13,12,2,'4D9160',NULL,3,102.00,2,612,3366.00,'[GROSS=3645.48]','2026-11-05 22:49:14','2026-11-05 22:49:14'),
(14,13,1,'4P1065',NULL,1,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(15,14,2,'4D9160',NULL,5,100.00,2,600,3300.00,'[GROSS=3574.00]','2026-11-05 22:49:14','2026-11-05 22:49:14'),
(16,15,1,'4P1065',NULL,1,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(17,16,2,'4D9160',NULL,3,101.00,2,606,3333.00,'[GROSS=3609.74]','2026-11-05 22:49:14','2026-11-05 22:49:14'),
(18,17,8,'-','GRN-PEI-01',8,200.00,1,NULL,200.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(19,18,1,'4P1065',NULL,1,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(20,19,2,'4D9160',NULL,6,102.00,2,612,3366.00,'[GROSS=3645.48]','2026-11-05 22:49:14','2026-11-05 22:49:14'),
(21,20,1,'4P1065',NULL,1,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(22,21,2,'4D9160',NULL,3,100.00,2,600,3300.00,'[GROSS=3574.00]','2026-11-05 22:49:14','2026-11-05 22:49:14'),
(23,22,2,'4D9160',NULL,3,101.00,2,606,3333.00,'[GROSS=3609.74]','2026-11-05 22:49:14','2026-11-05 22:49:14'),
(24,23,1,'4P1044',NULL,2,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(25,24,2,'4D9162',NULL,7,10.00,2,60,330.00,'[GROSS=357.40]','2026-11-05 22:49:14','2026-11-05 22:49:14'),
(26,24,2,'4D9162',NULL,5,92.00,2,552,3036.00,'[GROSS=3288.08]','2026-11-05 22:49:14','2026-11-05 22:49:14'),
(27,25,1,'4P1044',NULL,2,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(28,26,2,'4D9162',NULL,3,100.00,2,600,3300.00,'[GROSS=3574.00]','2026-11-05 22:49:14','2026-11-05 22:49:14'),
(29,27,1,'4P1044',NULL,2,NULL,NULL,180,2880.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(30,28,2,'4D9162',NULL,6,101.00,2,606,3333.00,'[GROSS=3609.74]','2026-11-05 22:49:14','2026-11-05 22:49:14'),
(31,29,1,'4P1044',NULL,2,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(32,30,2,'4D9162',NULL,3,102.00,2,612,3366.00,'[GROSS=3645.48]','2026-11-05 22:49:14','2026-11-05 22:49:14'),
(33,31,3,'-','GRN-PEI-02',8,2500.00,4,NULL,2500.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(34,32,1,'4P1044',NULL,2,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:14','2026-11-05 22:49:14'),
(35,33,2,'4D9162',NULL,3,100.00,2,600,3300.00,'[GROSS=3574.00]','2026-11-05 22:49:14','2026-11-05 22:49:14'),
(36,34,1,'4P1044',NULL,2,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(37,35,2,'4D9162',NULL,5,101.00,2,606,3333.00,'[GROSS=3609.74]','2026-11-05 22:49:15','2026-11-05 22:49:15'),
(38,36,2,'4D9162',NULL,3,102.00,2,612,3366.00,'[GROSS=3645.48]','2026-11-05 22:49:15','2026-11-05 22:49:15'),
(39,37,1,'4P1065',NULL,1,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(40,38,2,'4D9163',NULL,7,10.00,2,60,330.00,'[GROSS=357.40]','2026-11-05 22:49:15','2026-11-05 22:49:15'),
(41,38,2,'4D9163',NULL,6,90.00,2,540,2970.00,'[GROSS=3216.60]','2026-11-05 22:49:15','2026-11-05 22:49:15'),
(42,39,1,'4P1065',NULL,1,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(43,40,2,'4D9163',NULL,3,101.00,2,606,3333.00,'[GROSS=3609.74]','2026-11-05 22:49:15','2026-11-05 22:49:15'),
(44,41,1,'4P1065',NULL,1,NULL,NULL,180,2880.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(45,42,2,'4D9163',NULL,3,102.00,2,612,3366.00,'[GROSS=3645.48]','2026-11-05 22:49:15','2026-11-05 22:49:15'),
(46,43,1,'4P1065',NULL,1,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(47,44,2,'4D9163',NULL,5,100.00,2,600,3300.00,'[GROSS=3574.00]','2026-11-05 22:49:15','2026-11-05 22:49:15'),
(48,45,4,'-','GRN-PEI-03',8,450.00,4,NULL,450.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(49,46,1,'4P1065',NULL,1,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(50,47,2,'4D9163',NULL,3,101.00,2,606,3333.00,'[GROSS=3609.74]','2026-11-05 22:49:15','2026-11-05 22:49:15'),
(51,48,1,'4P1065',NULL,1,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(52,49,2,'4D9163',NULL,6,102.00,2,612,3366.00,'[GROSS=3645.48]','2026-11-05 22:49:15','2026-11-05 22:49:15'),
(53,50,2,'4D9163',NULL,3,100.00,2,600,3300.00,'[GROSS=3574.00]','2026-11-05 22:49:15','2026-11-05 22:49:15'),
(54,51,1,'4P1044',NULL,2,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(55,52,2,'4D9160',NULL,3,101.00,2,606,3333.00,'[GROSS=3609.74]','2026-11-05 22:49:15','2026-11-05 22:49:15'),
(56,53,1,'4P1044',NULL,2,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(57,54,2,'4D9160',NULL,5,102.00,2,612,3366.00,'[GROSS=3645.48]','2026-11-05 22:49:15','2026-11-05 22:49:15'),
(58,55,1,'4P1044',NULL,2,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(59,56,2,'4D9160',NULL,3,100.00,2,600,3300.00,'[GROSS=3574.00]','2026-11-05 22:49:15','2026-11-05 22:49:15'),
(60,57,1,'4P1044',NULL,2,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(61,58,2,'4D9160',NULL,6,101.00,2,606,3333.00,'[GROSS=3609.74]','2026-11-05 22:49:15','2026-11-05 22:49:15'),
(62,59,1,'4P1044',NULL,2,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:15','2026-11-05 22:49:15'),
(63,60,2,'4D9160',NULL,3,102.00,2,612,3366.00,'[GROSS=3645.48]','2026-11-05 22:49:16','2026-11-05 22:49:16'),
(64,61,1,'4P1044',NULL,2,NULL,NULL,252,4032.00,NULL,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(65,62,1,'4P1066',NULL,1,NULL,NULL,NULL,576.00,NULL,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(66,63,1,'4P1067',NULL,2,NULL,NULL,NULL,576.00,NULL,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(67,64,4,'-',NULL,8,NULL,NULL,NULL,15.00,NULL,'2026-11-05 22:49:16','2026-11-05 22:49:16');
/*!40000 ALTER TABLE `transaksi_masuk_detail` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
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

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Administrator Gudang','admin','admin@wms.local','2026-11-05 22:49:13','$2y$12$I/.fMOFuodXEWiVl/JOg0uv9Na7FUGT8Eh5biyKPFxQbI64OmgtEu','admin',1,NULL,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(2,'Operator Gudang','operator','operator@wms.local','2026-11-05 22:49:13','$2y$12$ai6q9Z7ceXZWhwN4H9RKo.tGvfrWDx4C/O.LQi1fcJDwl8quy2q0K','operator',1,NULL,'2026-11-05 22:49:13','2026-11-05 22:49:13'),
(3,'Manajer DTY PEI','manajer','manajer@wms.local','2026-11-05 22:49:13','$2y$12$Ju4.pbtcjDKO4sJuh5qoqOFRMVXD6sxhaXz0RDMIz6/OmAnbFxdBe','manajer',1,NULL,'2026-11-05 22:49:13','2026-11-05 22:49:13');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `waste_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `jenis` varchar(30) NOT NULL,
  `tujuan` varchar(50) DEFAULT NULL,
  `berat_kg` decimal(12,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `waste_logs_created_by_foreign` (`created_by`),
  KEY `waste_logs_tanggal_index` (`tanggal`),
  CONSTRAINT `waste_logs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `waste_logs` DISABLE KEYS */;
INSERT INTO `waste_logs` VALUES
(1,'2026-11-01','WASTE','RCL',145.50,'Waste benang dari area produksi.',1,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(2,'2026-11-03','REJECT','EYD',82.25,'Reject benang, dikirim ke EYD.',1,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(3,'2026-11-05','SCRAP_PACKAGING','Pengepul',36.80,'Scrap kemasan carton.',1,'2026-11-05 22:49:16','2026-11-05 22:49:16'),
(4,'2026-11-06','REJECT','RCL',55.00,'Reject hasil sortir akhir.',1,'2026-11-05 22:49:16','2026-11-05 22:49:16');
/*!40000 ALTER TABLE `waste_logs` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;
