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
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES
(1,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',1,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1044) dari penerimaan DEMO-POY-IN-01.','127.0.0.1','Symfony','2026-11-04 05:05:39'),
(2,2,'UPDATE','TRANSAKSI_MASUK_POY',1,'Posting transaksi DEMO-POY-IN-01 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:39'),
(3,2,'UPDATE','TRANSAKSI_MASUK_DTY',2,'Posting transaksi DEMO-DTY-IN-01 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:39'),
(4,2,'UPDATE','TRANSAKSI_KELUAR_POY',1,'Posting transaksi DEMO-POY-OUT-01 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:39'),
(5,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',3,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1044) dari penerimaan DEMO-POY-IN-02.','127.0.0.1','Symfony','2026-11-04 05:05:39'),
(6,2,'UPDATE','TRANSAKSI_MASUK_POY',3,'Posting transaksi DEMO-POY-IN-02 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:39'),
(7,2,'UPDATE','TRANSAKSI_MASUK_DTY',4,'Posting transaksi DEMO-DTY-IN-02 (3.333,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:39'),
(8,2,'UPDATE','TRANSAKSI_KELUAR_POY',2,'Posting transaksi DEMO-POY-OUT-02 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:39'),
(9,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',5,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1044) dari penerimaan DEMO-POY-IN-03.','127.0.0.1','Symfony','2026-11-04 05:05:39'),
(10,2,'UPDATE','TRANSAKSI_MASUK_POY',5,'Posting transaksi DEMO-POY-IN-03 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:39'),
(11,2,'UPDATE','TRANSAKSI_MASUK_DTY',6,'Posting transaksi DEMO-DTY-IN-03 (3.366,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:39'),
(12,2,'UPDATE','TRANSAKSI_MASUK_PENDUKUNG',7,'Issue dokumen DEMO-PENDUKUNG-IN-01 (3.856,00 kg, 2 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(13,2,'UPDATE','TRANSAKSI_KELUAR_POY',3,'Posting transaksi DEMO-POY-OUT-03 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(14,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',8,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1044) dari penerimaan DEMO-POY-IN-04.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(15,2,'UPDATE','TRANSAKSI_MASUK_POY',8,'Posting transaksi DEMO-POY-IN-04 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(16,2,'UPDATE','TRANSAKSI_MASUK_DTY',9,'Posting transaksi DEMO-DTY-IN-04 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(17,2,'UPDATE','TRANSAKSI_KELUAR_POY',4,'Posting transaksi DEMO-POY-OUT-04 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(18,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',10,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1044) dari penerimaan DEMO-POY-IN-05.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(19,2,'UPDATE','TRANSAKSI_MASUK_POY',10,'Posting transaksi DEMO-POY-IN-05 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(20,2,'UPDATE','TRANSAKSI_MASUK_DTY',11,'Posting transaksi DEMO-DTY-IN-05 (3.333,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(21,2,'UPDATE','TRANSAKSI_KELUAR_POY',5,'Posting transaksi DEMO-POY-OUT-05 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(22,2,'UPDATE','TRANSAKSI_KELUAR_SJ',6,'Issue dokumen DEMO-DTY-SJ-01 (825,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(23,2,'UPDATE','TRANSAKSI_KELUAR_SJ',7,'Issue dokumen DEMO-DTY-SJ-02 (990,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(24,2,'UPDATE','TRANSAKSI_MASUK_DTY',12,'Posting transaksi DEMO-DTY-IN-06 (3.366,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(25,2,'RELEASE_BUFFER','RILIS_BUFFER',NULL,'Rilis buffer Minggu 2.880,00 kg (1 baris) ke stok aktif (RILIS-BUFFER-2026-10-11).','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(26,2,'UPDATE','TRANSAKSI_KELUAR_POY',8,'Posting transaksi DEMO-POY-OUT-06 (2.880,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(27,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',13,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1065) dari penerimaan DEMO-POY-IN-06.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(28,2,'UPDATE','TRANSAKSI_MASUK_POY',13,'Posting transaksi DEMO-POY-IN-06 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(29,2,'UPDATE','TRANSAKSI_MASUK_DTY',14,'Posting transaksi DEMO-DTY-IN-07 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(30,2,'UPDATE','TRANSAKSI_KELUAR_POY',9,'Posting transaksi DEMO-POY-OUT-07 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(31,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',15,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1065) dari penerimaan DEMO-POY-IN-07.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(32,2,'UPDATE','TRANSAKSI_MASUK_POY',15,'Posting transaksi DEMO-POY-IN-07 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(33,2,'UPDATE','TRANSAKSI_MASUK_DTY',16,'Posting transaksi DEMO-DTY-IN-08 (3.333,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(34,2,'UPDATE','TRANSAKSI_KELUAR_POY',10,'Posting transaksi DEMO-POY-OUT-08 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(35,2,'UPDATE','TRANSAKSI_MASUK_POY',17,'Posting transaksi DEMO-POY-IN-08 (2.880,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(36,2,'UPDATE','TRANSAKSI_MASUK_DTY',18,'Posting transaksi DEMO-DTY-IN-09 (3.366,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(37,2,'UPDATE','TRANSAKSI_KELUAR_POY',11,'Posting transaksi DEMO-POY-OUT-09 (2.880,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(38,2,'UPDATE','TRANSAKSI_KELUAR_SJ',12,'Issue dokumen DEMO-DTY-SJ-03 (660,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(39,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',19,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1065) dari penerimaan DEMO-POY-IN-09.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(40,2,'UPDATE','TRANSAKSI_MASUK_POY',19,'Posting transaksi DEMO-POY-IN-09 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(41,2,'UPDATE','TRANSAKSI_MASUK_DTY',20,'Posting transaksi DEMO-DTY-IN-10 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(42,2,'UPDATE','TRANSAKSI_MASUK_PENDUKUNG',21,'Issue dokumen DEMO-PENDUKUNG-IN-02 (200,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(43,2,'UPDATE','TRANSAKSI_KELUAR_POY',13,'Posting transaksi DEMO-POY-OUT-10 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(44,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',22,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1065) dari penerimaan DEMO-POY-IN-10.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(45,2,'UPDATE','TRANSAKSI_MASUK_POY',22,'Posting transaksi DEMO-POY-IN-10 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(46,2,'UPDATE','TRANSAKSI_MASUK_DTY',23,'Posting transaksi DEMO-DTY-IN-11 (3.333,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(47,2,'UPDATE','TRANSAKSI_KELUAR_POY',14,'Posting transaksi DEMO-POY-OUT-11 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(48,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',24,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1065) dari penerimaan DEMO-POY-IN-11.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(49,2,'UPDATE','TRANSAKSI_MASUK_POY',24,'Posting transaksi DEMO-POY-IN-11 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(50,2,'UPDATE','TRANSAKSI_MASUK_DTY',25,'Posting transaksi DEMO-DTY-IN-12 (3.366,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(51,2,'UPDATE','TRANSAKSI_KELUAR_POY',15,'Posting transaksi DEMO-POY-OUT-12 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(52,2,'UPDATE','TRANSAKSI_KELUAR_SJ',16,'Issue dokumen DEMO-DTY-SJ-04 (924,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(53,2,'UPDATE','TRANSAKSI_MASUK_DTY',26,'Posting transaksi DEMO-DTY-IN-13 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(54,2,'RELEASE_BUFFER','RILIS_BUFFER',NULL,'Rilis buffer Minggu 2.880,00 kg (1 baris) ke stok aktif (RILIS-BUFFER-2026-10-18).','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(55,2,'UPDATE','TRANSAKSI_KELUAR_POY',17,'Posting transaksi DEMO-POY-OUT-13 (2.880,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(56,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',27,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1044) dari penerimaan DEMO-POY-IN-12.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(57,2,'UPDATE','TRANSAKSI_MASUK_POY',27,'Posting transaksi DEMO-POY-IN-12 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(58,2,'UPDATE','TRANSAKSI_MASUK_DTY',28,'Posting transaksi DEMO-DTY-IN-14 (330,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(59,2,'UPDATE','TRANSAKSI_KELUAR_POY',18,'Posting transaksi DEMO-POY-OUT-14 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(60,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',29,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1044) dari penerimaan DEMO-POY-IN-13.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(61,2,'UPDATE','TRANSAKSI_MASUK_POY',29,'Posting transaksi DEMO-POY-IN-13 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(62,2,'UPDATE','TRANSAKSI_MASUK_DTY',30,'Posting transaksi DEMO-DTY-IN-15 (3.366,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(63,2,'UPDATE','TRANSAKSI_KELUAR_POY',19,'Posting transaksi DEMO-POY-OUT-15 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(64,2,'UPDATE','TRANSAKSI_MASUK_POY',31,'Posting transaksi DEMO-POY-IN-14 (2.880,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(65,2,'UPDATE','TRANSAKSI_MASUK_DTY',32,'Posting transaksi DEMO-DTY-IN-16 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(66,2,'UPDATE','TRANSAKSI_KELUAR_POY',20,'Posting transaksi DEMO-POY-OUT-16 (2.880,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(67,2,'UPDATE','TRANSAKSI_KELUAR_SJ',21,'Issue dokumen DEMO-DTY-SJ-05 (726,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(68,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',33,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1044) dari penerimaan DEMO-POY-IN-15.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(69,2,'UPDATE','TRANSAKSI_MASUK_POY',33,'Posting transaksi DEMO-POY-IN-15 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(70,2,'UPDATE','TRANSAKSI_MASUK_DTY',34,'Posting transaksi DEMO-DTY-IN-17 (3.333,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(71,2,'UPDATE','TRANSAKSI_MASUK_PENDUKUNG',35,'Issue dokumen DEMO-PENDUKUNG-IN-03 (2.500,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(72,2,'UPDATE','TRANSAKSI_KELUAR_POY',22,'Posting transaksi DEMO-POY-OUT-17 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:40'),
(73,2,'UPDATE','TRANSAKSI_KELUAR_SCRAP',23,'Posting transaksi DEMO-DTY-SCRAP-01 (165,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(74,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',36,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1044) dari penerimaan DEMO-POY-IN-16.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(75,2,'UPDATE','TRANSAKSI_MASUK_POY',36,'Posting transaksi DEMO-POY-IN-16 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(76,2,'UPDATE','TRANSAKSI_MASUK_DTY',37,'Posting transaksi DEMO-DTY-IN-18 (3.366,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(77,2,'UPDATE','TRANSAKSI_KELUAR_POY',24,'Posting transaksi DEMO-POY-OUT-18 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(78,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',38,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1044) dari penerimaan DEMO-POY-IN-17.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(79,2,'UPDATE','TRANSAKSI_MASUK_POY',38,'Posting transaksi DEMO-POY-IN-17 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(80,2,'UPDATE','TRANSAKSI_MASUK_DTY',39,'Posting transaksi DEMO-DTY-IN-19 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(81,2,'UPDATE','TRANSAKSI_KELUAR_POY',25,'Posting transaksi DEMO-POY-OUT-19 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(82,2,'UPDATE','TRANSAKSI_KELUAR_SJ',26,'Issue dokumen DEMO-DTY-SJ-06 (990,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(83,2,'UPDATE','TRANSAKSI_MASUK_DTY',40,'Posting transaksi DEMO-DTY-IN-20 (3.333,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(84,2,'RELEASE_BUFFER','RILIS_BUFFER',NULL,'Rilis buffer Minggu 2.880,00 kg (1 baris) ke stok aktif (RILIS-BUFFER-2026-10-25).','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(85,2,'UPDATE','TRANSAKSI_KELUAR_POY',27,'Posting transaksi DEMO-POY-OUT-20 (2.880,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(86,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',41,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1065) dari penerimaan DEMO-POY-IN-18.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(87,2,'UPDATE','TRANSAKSI_MASUK_POY',41,'Posting transaksi DEMO-POY-IN-18 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(88,2,'UPDATE','TRANSAKSI_MASUK_DTY',42,'Posting transaksi DEMO-DTY-IN-21 (330,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(89,2,'UPDATE','TRANSAKSI_KELUAR_POY',28,'Posting transaksi DEMO-POY-OUT-21 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(90,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',43,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1065) dari penerimaan DEMO-POY-IN-19.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(91,2,'UPDATE','TRANSAKSI_MASUK_POY',43,'Posting transaksi DEMO-POY-IN-19 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(92,2,'UPDATE','TRANSAKSI_MASUK_DTY',44,'Posting transaksi DEMO-DTY-IN-22 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(93,2,'UPDATE','TRANSAKSI_KELUAR_POY',29,'Posting transaksi DEMO-POY-OUT-22 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(94,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',45,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1065) dari penerimaan DEMO-POY-IN-20.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(95,2,'UPDATE','TRANSAKSI_MASUK_POY',45,'Posting transaksi DEMO-POY-IN-20 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(96,2,'UPDATE','TRANSAKSI_MASUK_DTY',46,'Posting transaksi DEMO-DTY-IN-23 (3.333,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(97,2,'UPDATE','TRANSAKSI_KELUAR_POY',30,'Posting transaksi DEMO-POY-OUT-23 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(98,2,'UPDATE','TRANSAKSI_KELUAR_SJ',31,'Issue dokumen DEMO-DTY-SJ-07 (825,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(99,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',47,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1065) dari penerimaan DEMO-POY-IN-21.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(100,2,'UPDATE','TRANSAKSI_MASUK_POY',47,'Posting transaksi DEMO-POY-IN-21 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(101,2,'UPDATE','TRANSAKSI_MASUK_DTY',48,'Posting transaksi DEMO-DTY-IN-24 (3.366,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(102,2,'UPDATE','TRANSAKSI_MASUK_PENDUKUNG',49,'Issue dokumen DEMO-PENDUKUNG-IN-04 (450,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(103,2,'UPDATE','TRANSAKSI_KELUAR_POY',32,'Posting transaksi DEMO-POY-OUT-24 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(104,2,'UPDATE','TRANSAKSI_KELUAR_SCRAP',33,'Posting transaksi DEMO-DTY-SCRAP-02 (165,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(105,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',50,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1065) dari penerimaan DEMO-POY-IN-22.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(106,2,'UPDATE','TRANSAKSI_MASUK_POY',50,'Posting transaksi DEMO-POY-IN-22 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(107,2,'UPDATE','TRANSAKSI_MASUK_DTY',51,'Posting transaksi DEMO-DTY-IN-25 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(108,2,'UPDATE','TRANSAKSI_KELUAR_POY',34,'Posting transaksi DEMO-POY-OUT-25 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(109,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',52,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1065) dari penerimaan DEMO-POY-IN-23.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(110,2,'UPDATE','TRANSAKSI_MASUK_POY',52,'Posting transaksi DEMO-POY-IN-23 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(111,2,'UPDATE','TRANSAKSI_MASUK_DTY',53,'Posting transaksi DEMO-DTY-IN-26 (3.333,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(112,2,'UPDATE','TRANSAKSI_KELUAR_POY',35,'Posting transaksi DEMO-POY-OUT-26 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(113,2,'UPDATE','TRANSAKSI_KELUAR_SJ',36,'Issue dokumen DEMO-DTY-SJ-08 (660,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(114,2,'UPDATE','TRANSAKSI_MASUK_DTY',54,'Posting transaksi DEMO-DTY-IN-27 (3.366,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(115,2,'RELEASE_BUFFER','RILIS_BUFFER',NULL,'Rilis buffer Minggu 3.456,00 kg (1 baris) ke stok aktif (RILIS-BUFFER-2026-11-01).','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(116,2,'UPDATE','TRANSAKSI_KELUAR_POY',37,'Posting transaksi DEMO-POY-OUT-27 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(117,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',55,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1044) dari penerimaan DEMO-POY-IN-24.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(118,2,'UPDATE','TRANSAKSI_MASUK_POY',55,'Posting transaksi DEMO-POY-IN-24 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(119,2,'UPDATE','TRANSAKSI_MASUK_DTY',56,'Posting transaksi DEMO-DTY-IN-28 (3.300,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(120,2,'UPDATE','TRANSAKSI_KELUAR_POY',38,'Posting transaksi DEMO-POY-OUT-28 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(121,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',57,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1044) dari penerimaan DEMO-POY-IN-25.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(122,2,'UPDATE','TRANSAKSI_MASUK_POY',57,'Posting transaksi DEMO-POY-IN-25 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(123,2,'UPDATE','TRANSAKSI_MASUK_DTY',58,'Posting transaksi DEMO-DTY-IN-29 (3.333,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(124,2,'UPDATE','TRANSAKSI_KELUAR_POY',39,'Posting transaksi DEMO-POY-OUT-29 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(125,NULL,'ALOKASI_BUFFER','ALOKASI_BUFFER',59,'Alokasi buffer 576,00 kg (barang POY-250-48, merge 4P1044) dari penerimaan DEMO-POY-IN-26.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(126,2,'UPDATE','TRANSAKSI_MASUK_POY',59,'Posting transaksi DEMO-POY-IN-26 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(127,2,'UPDATE','TRANSAKSI_MASUK_DTY',60,'Posting transaksi DEMO-DTY-IN-30 (3.366,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(128,2,'UPDATE','TRANSAKSI_KELUAR_POY',40,'Posting transaksi DEMO-POY-OUT-30 (3.456,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(129,2,'UPDATE','TRANSAKSI_MASUK_POY',61,'Posting transaksi DEMO-POY-IN-27 (4.032,00 kg, 1 baris) oleh user #2.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(130,1,'CREATE','STOK_OPNAME',1,'Opname OPN-IN-2026-11-1 (415,00 kg, selisih 15,00 kg) oleh user #1.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(131,1,'CREATE','STOK_OPNAME',2,'Opname OPN-OUT-2026-11-1 (195,00 kg, selisih -5,00 kg) oleh user #1.','127.0.0.1','Symfony','2026-11-04 05:05:41'),
(132,1,'CREATE','STOK_OPNAME',3,'Opname OPN-20261104-3-8-GRNVELVET-c5e659 (3.456,00 kg, selisih 0,00 kg) oleh user #1.','127.0.0.1','Symfony','2026-11-04 05:05:41');
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
(1,'2026-10-06',1,'4P1044',2,576.00,1,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(2,'2026-10-07',1,'4P1044',2,576.00,3,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(3,'2026-10-08',1,'4P1044',2,576.00,5,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(4,'2026-10-09',1,'4P1044',2,576.00,8,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(5,'2026-10-10',1,'4P1044',2,576.00,10,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(6,'2026-10-12',1,'4P1065',1,576.00,13,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(7,'2026-10-13',1,'4P1065',1,576.00,15,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(8,'2026-10-14',1,'4P1065',1,0.00,17,1,'Buffer dilewati: Pasokan 5,00 pallet (2.880,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(9,'2026-10-15',1,'4P1065',1,576.00,19,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(10,'2026-10-16',1,'4P1065',1,576.00,22,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(11,'2026-10-17',1,'4P1065',1,576.00,24,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(12,'2026-10-19',1,'4P1044',2,576.00,27,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(13,'2026-10-20',1,'4P1044',2,576.00,29,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(14,'2026-10-21',1,'4P1044',2,0.00,31,1,'Buffer dilewati: Pasokan 5,00 pallet (2.880,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(15,'2026-10-22',1,'4P1044',2,576.00,33,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(16,'2026-10-23',1,'4P1044',2,576.00,36,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(17,'2026-10-24',1,'4P1044',2,576.00,38,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(18,'2026-10-26',1,'4P1065',1,576.00,41,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(19,'2026-10-27',1,'4P1065',1,576.00,43,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(20,'2026-10-28',1,'4P1065',1,576.00,45,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(21,'2026-10-29',1,'4P1065',1,576.00,47,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(22,'2026-10-30',1,'4P1065',1,576.00,50,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(23,'2026-10-31',1,'4P1065',1,576.00,52,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(24,'2026-11-02',1,'4P1044',2,576.00,55,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(25,'2026-11-03',1,'4P1044',2,576.00,57,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(26,'2026-11-04',1,'4P1044',2,576.00,59,0,'576,00 kg disisihkan ke buffer. Pasokan 7,00 pallet (4.032,00 kg) — ambang 7 pallet.',2,'2026-11-04 05:05:41','2026-11-04 05:05:41');
/*!40000 ALTER TABLE `alokasi_buffer` ENABLE KEYS */;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `barang` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kategori_id` bigint(20) unsigned NOT NULL,
  `kode_barang` varchar(50) NOT NULL,
  `kode_material` varchar(30) DEFAULT NULL,
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
  UNIQUE KEY `barang_kode_material_unique` (`kode_material`),
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
(1,1,'POY-250-48',NULL,'POY 250/48','250','48',1,NULL,1,1,'2026-11-04 05:05:39','2026-11-04 05:05:39',NULL),
(2,2,'DTY-150-48-1',NULL,'DTY 150/48','150','48',1,NULL,0,1,'2026-11-04 05:05:39','2026-11-04 05:05:39',NULL),
(3,3,'PPR TUB DTY, BULLNOSE','5003301227','PPR TUB DTY,BULLNOSE,212-222GM',NULL,NULL,4,1000.00,0,1,'2026-11-04 05:05:39','2026-11-04 05:05:39',NULL),
(4,3,'CARTON BOX DTY 675x444x278MM, KRAFT 200','5000002512','CARTON BOX DTY 675X444X278MM,KRAFT 200',NULL,NULL,4,200.00,0,1,'2026-11-04 05:05:39','2026-11-04 05:05:39',NULL),
(5,3,'STRETCH FILM 12MC','5003301185','STRETCH FILM 12MC',NULL,NULL,4,NULL,0,1,'2026-11-04 05:05:39','2026-11-04 05:05:39',NULL),
(6,3,'PALLET DTY SMALL BOX 1370x1150x140MM','5003301241','PALLET DTY SMALL BOX,1370X1150X140MM',NULL,NULL,4,NULL,0,1,'2026-11-04 05:05:39','2026-11-04 05:05:39',NULL),
(7,3,'TAPE 250MX3IN, VISTAC TRANSPARENT','5003301180','TAPE,250MX3IN,VISTAC,TRANSPARENT',NULL,NULL,4,NULL,0,1,'2026-11-04 05:05:39','2026-11-04 05:05:39',NULL),
(8,3,'DTY CONING OIL, TM368 BN','1003000201','DTY CONING OIL,TM368 BN',NULL,NULL,1,200.00,0,1,'2026-11-04 05:05:39','2026-11-04 05:05:39',NULL),
(9,3,'DTY PLASTIC BAG HDPE VRGN (4%A) 36/42x55 CM','5000001861','DTY PLASTIC BAG-HDPE VRGN (4%A) 36/42X55 CM',NULL,NULL,1,NULL,0,1,'2026-11-04 05:05:39','2026-11-04 05:05:39',NULL);
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
('wms-gudang-dty-pei-cache-wms:notifikasi-stok-menipis:barang:8:user:1','b:1;',1793855142),
('wms-gudang-dty-pei-cache-wms:notifikasi-stok-menipis:barang:8:user:3','b:1;',1793855142);
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `grade` DISABLE KEYS */;
INSERT INTO `grade` VALUES
(1,'AL',1,'POY grade AL (lolos)',1,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(2,'Am',1,'POY grade Am (minor)',1,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(3,'AX',2,'DTY ekspor / super',1,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(4,'AE',2,'DTY grade AE',1,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(5,'A',2,'DTY standar 1',1,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(6,'B',2,'DTY segregasi / turun grade',1,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(7,'C',2,'DTY reject / knitting (scrap)',1,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(8,'STD',3,'Grade netral item pendukung (tanpa grade pada Gate Pass)',1,'2026-11-04 05:05:39','2026-11-04 05:05:39');
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
(1,'POY','POY (Bahan Baku Utama)',NULL,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(2,'DTY','DTY (Produk Jadi)',NULL,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(3,'PENDUKUNG','Item Pendukung (Packaging & Consumables)',NULL,'2026-11-04 05:05:39','2026-11-04 05:05:39');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `konversi_satuan` DISABLE KEYS */;
INSERT INTO `konversi_satuan` VALUES
(1,2,2,6,6.0000,1,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(2,2,6,1,5.5000,1,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(3,2,2,1,33.0000,1,'2026-11-04 05:05:39','2026-11-04 05:05:39');
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
(18,'2026_11_04_110707_add_kode_material_to_barang_table',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `mutasi_stok` DISABLE KEYS */;
INSERT INTO `mutasi_stok` VALUES
(1,1,'4P1044',2,'2026-10-06','MASUK','AKTIF','DEMO-POY-IN-01',4032.00,0.00,4032.00,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(2,1,'4P1044',2,'2026-10-06','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-01',0.00,576.00,3456.00,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(3,1,'4P1044',2,'2026-10-06','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-01',576.00,0.00,576.00,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(4,2,'4D9163',3,'2026-10-06','MASUK','AKTIF','DEMO-DTY-IN-01',3300.00,0.00,3300.00,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(5,1,'4P1044',2,'2026-10-06','KELUAR','AKTIF','DEMO-POY-OUT-01',0.00,3456.00,0.00,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(6,1,'4P1044',2,'2026-10-07','MASUK','AKTIF','DEMO-POY-IN-02',4032.00,0.00,4032.00,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(7,1,'4P1044',2,'2026-10-07','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-02',0.00,576.00,3456.00,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(8,1,'4P1044',2,'2026-10-07','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-02',576.00,0.00,1152.00,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(9,2,'4D9163',5,'2026-10-07','MASUK','AKTIF','DEMO-DTY-IN-02',3333.00,0.00,3333.00,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(10,1,'4P1044',2,'2026-10-07','KELUAR','AKTIF','DEMO-POY-OUT-02',0.00,3456.00,0.00,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(11,1,'4P1044',2,'2026-10-08','MASUK','AKTIF','DEMO-POY-IN-03',4032.00,0.00,4032.00,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(12,1,'4P1044',2,'2026-10-08','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-03',0.00,576.00,3456.00,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(13,1,'4P1044',2,'2026-10-08','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-03',576.00,0.00,1728.00,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(14,2,'4D9163',3,'2026-10-08','MASUK','AKTIF','DEMO-DTY-IN-03',3366.00,0.00,6666.00,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(15,3,'GRN-VELVET',8,'2026-10-08','MASUK','AKTIF','DEMO-PENDUKUNG-IN-01',3456.00,0.00,3456.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(16,4,'GRN-VELVET',8,'2026-10-08','MASUK','AKTIF','DEMO-PENDUKUNG-IN-01',400.00,0.00,400.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(17,1,'4P1044',2,'2026-10-08','KELUAR','AKTIF','DEMO-POY-OUT-03',0.00,3456.00,0.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(18,1,'4P1044',2,'2026-10-09','MASUK','AKTIF','DEMO-POY-IN-04',4032.00,0.00,4032.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(19,1,'4P1044',2,'2026-10-09','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-04',0.00,576.00,3456.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(20,1,'4P1044',2,'2026-10-09','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-04',576.00,0.00,2304.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(21,2,'4D9163',6,'2026-10-09','MASUK','AKTIF','DEMO-DTY-IN-04',3300.00,0.00,3300.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(22,1,'4P1044',2,'2026-10-09','KELUAR','AKTIF','DEMO-POY-OUT-04',0.00,3456.00,0.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(23,1,'4P1044',2,'2026-10-10','MASUK','AKTIF','DEMO-POY-IN-05',4032.00,0.00,4032.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(24,1,'4P1044',2,'2026-10-10','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-05',0.00,576.00,3456.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(25,1,'4P1044',2,'2026-10-10','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-05',576.00,0.00,2880.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(26,2,'4D9163',3,'2026-10-10','MASUK','AKTIF','DEMO-DTY-IN-05',3333.00,0.00,9999.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(27,1,'4P1044',2,'2026-10-10','KELUAR','AKTIF','DEMO-POY-OUT-05',0.00,3456.00,0.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(28,2,'4D9163',3,'2026-10-10','KELUAR','AKTIF','DEMO-DTY-SJ-01',0.00,825.00,9174.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(29,2,'4D9163',3,'2026-10-10','KELUAR','AKTIF','DEMO-DTY-SJ-02',0.00,990.00,8184.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(30,2,'4D9163',3,'2026-10-11','MASUK','AKTIF','DEMO-DTY-IN-06',3366.00,0.00,11550.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(31,1,'4P1044',2,'2026-10-11','RILIS_BUFFER','BUFFER','RILIS-BUFFER-2026-10-11',0.00,2880.00,0.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(32,1,'4P1044',2,'2026-10-11','RILIS_BUFFER','AKTIF','RILIS-BUFFER-2026-10-11',2880.00,0.00,2880.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(33,1,'4P1044',2,'2026-10-11','KELUAR','AKTIF','DEMO-POY-OUT-06',0.00,2880.00,0.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(34,1,'4P1065',1,'2026-10-12','MASUK','AKTIF','DEMO-POY-IN-06',4032.00,0.00,4032.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(35,1,'4P1065',1,'2026-10-12','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-06',0.00,576.00,3456.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(36,1,'4P1065',1,'2026-10-12','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-06',576.00,0.00,576.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(37,2,'4D9160',5,'2026-10-12','MASUK','AKTIF','DEMO-DTY-IN-07',3300.00,0.00,3300.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(38,1,'4P1065',1,'2026-10-12','KELUAR','AKTIF','DEMO-POY-OUT-07',0.00,3456.00,0.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(39,1,'4P1065',1,'2026-10-13','MASUK','AKTIF','DEMO-POY-IN-07',4032.00,0.00,4032.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(40,1,'4P1065',1,'2026-10-13','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-07',0.00,576.00,3456.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(41,1,'4P1065',1,'2026-10-13','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-07',576.00,0.00,1152.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(42,2,'4D9160',3,'2026-10-13','MASUK','AKTIF','DEMO-DTY-IN-08',3333.00,0.00,3333.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(43,1,'4P1065',1,'2026-10-13','KELUAR','AKTIF','DEMO-POY-OUT-08',0.00,3456.00,0.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(44,1,'4P1065',1,'2026-10-14','MASUK','AKTIF','DEMO-POY-IN-08',2880.00,0.00,2880.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(45,2,'4D9160',6,'2026-10-14','MASUK','AKTIF','DEMO-DTY-IN-09',3366.00,0.00,3366.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(46,1,'4P1065',1,'2026-10-14','KELUAR','AKTIF','DEMO-POY-OUT-09',0.00,2880.00,0.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(47,2,'4D9163',3,'2026-10-14','KELUAR','AKTIF','DEMO-DTY-SJ-03',0.00,660.00,10890.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(48,1,'4P1065',1,'2026-10-15','MASUK','AKTIF','DEMO-POY-IN-09',4032.00,0.00,4032.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(49,1,'4P1065',1,'2026-10-15','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-09',0.00,576.00,3456.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(50,1,'4P1065',1,'2026-10-15','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-09',576.00,0.00,1728.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(51,2,'4D9160',3,'2026-10-15','MASUK','AKTIF','DEMO-DTY-IN-10',3300.00,0.00,6633.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(52,8,'GRN-PEI-01',8,'2026-10-15','MASUK','AKTIF','DEMO-PENDUKUNG-IN-02',200.00,0.00,200.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(53,1,'4P1065',1,'2026-10-15','KELUAR','AKTIF','DEMO-POY-OUT-10',0.00,3456.00,0.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(54,1,'4P1065',1,'2026-10-16','MASUK','AKTIF','DEMO-POY-IN-10',4032.00,0.00,4032.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(55,1,'4P1065',1,'2026-10-16','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-10',0.00,576.00,3456.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(56,1,'4P1065',1,'2026-10-16','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-10',576.00,0.00,2304.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(57,2,'4D9160',3,'2026-10-16','MASUK','AKTIF','DEMO-DTY-IN-11',3333.00,0.00,9966.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(58,1,'4P1065',1,'2026-10-16','KELUAR','AKTIF','DEMO-POY-OUT-11',0.00,3456.00,0.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(59,1,'4P1065',1,'2026-10-17','MASUK','AKTIF','DEMO-POY-IN-11',4032.00,0.00,4032.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(60,1,'4P1065',1,'2026-10-17','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-11',0.00,576.00,3456.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(61,1,'4P1065',1,'2026-10-17','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-11',576.00,0.00,2880.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(62,2,'4D9160',5,'2026-10-17','MASUK','AKTIF','DEMO-DTY-IN-12',3366.00,0.00,6666.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(63,1,'4P1065',1,'2026-10-17','KELUAR','AKTIF','DEMO-POY-OUT-12',0.00,3456.00,0.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(64,2,'4D9163',3,'2026-10-17','KELUAR','AKTIF','DEMO-DTY-SJ-04',0.00,924.00,9966.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(65,2,'4D9160',3,'2026-10-18','MASUK','AKTIF','DEMO-DTY-IN-13',3300.00,0.00,13266.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(66,1,'4P1065',1,'2026-10-18','RILIS_BUFFER','BUFFER','RILIS-BUFFER-2026-10-18',0.00,2880.00,0.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(67,1,'4P1065',1,'2026-10-18','RILIS_BUFFER','AKTIF','RILIS-BUFFER-2026-10-18',2880.00,0.00,2880.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(68,1,'4P1065',1,'2026-10-18','KELUAR','AKTIF','DEMO-POY-OUT-13',0.00,2880.00,0.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(69,1,'4P1044',2,'2026-10-19','MASUK','AKTIF','DEMO-POY-IN-12',4032.00,0.00,4032.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(70,1,'4P1044',2,'2026-10-19','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-12',0.00,576.00,3456.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(71,1,'4P1044',2,'2026-10-19','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-12',576.00,0.00,576.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(72,2,'4D9162',7,'2026-10-19','MASUK','AKTIF','DEMO-DTY-IN-14',330.00,0.00,330.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(73,1,'4P1044',2,'2026-10-19','KELUAR','AKTIF','DEMO-POY-OUT-14',0.00,3456.00,0.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(74,1,'4P1044',2,'2026-10-20','MASUK','AKTIF','DEMO-POY-IN-13',4032.00,0.00,4032.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(75,1,'4P1044',2,'2026-10-20','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-13',0.00,576.00,3456.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(76,1,'4P1044',2,'2026-10-20','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-13',576.00,0.00,1152.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(77,2,'4D9162',3,'2026-10-20','MASUK','AKTIF','DEMO-DTY-IN-15',3366.00,0.00,3366.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(78,1,'4P1044',2,'2026-10-20','KELUAR','AKTIF','DEMO-POY-OUT-15',0.00,3456.00,0.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(79,1,'4P1044',2,'2026-10-21','MASUK','AKTIF','DEMO-POY-IN-14',2880.00,0.00,2880.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(80,2,'4D9162',3,'2026-10-21','MASUK','AKTIF','DEMO-DTY-IN-16',3300.00,0.00,6666.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(81,1,'4P1044',2,'2026-10-21','KELUAR','AKTIF','DEMO-POY-OUT-16',0.00,2880.00,0.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(82,2,'4D9160',3,'2026-10-21','KELUAR','AKTIF','DEMO-DTY-SJ-05',0.00,726.00,12540.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(83,1,'4P1044',2,'2026-10-22','MASUK','AKTIF','DEMO-POY-IN-15',4032.00,0.00,4032.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(84,1,'4P1044',2,'2026-10-22','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-15',0.00,576.00,3456.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(85,1,'4P1044',2,'2026-10-22','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-15',576.00,0.00,1728.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(86,2,'4D9162',5,'2026-10-22','MASUK','AKTIF','DEMO-DTY-IN-17',3333.00,0.00,3333.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(87,3,'GRN-PEI-02',8,'2026-10-22','MASUK','AKTIF','DEMO-PENDUKUNG-IN-03',2500.00,0.00,2500.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(88,1,'4P1044',2,'2026-10-22','KELUAR','AKTIF','DEMO-POY-OUT-17',0.00,3456.00,0.00,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(89,2,'4D9162',7,'2026-10-22','KELUAR','AKTIF','DEMO-DTY-SCRAP-01',0.00,165.00,165.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(90,1,'4P1044',2,'2026-10-23','MASUK','AKTIF','DEMO-POY-IN-16',4032.00,0.00,4032.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(91,1,'4P1044',2,'2026-10-23','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-16',0.00,576.00,3456.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(92,1,'4P1044',2,'2026-10-23','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-16',576.00,0.00,2304.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(93,2,'4D9162',3,'2026-10-23','MASUK','AKTIF','DEMO-DTY-IN-18',3366.00,0.00,10032.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(94,1,'4P1044',2,'2026-10-23','KELUAR','AKTIF','DEMO-POY-OUT-18',0.00,3456.00,0.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(95,1,'4P1044',2,'2026-10-24','MASUK','AKTIF','DEMO-POY-IN-17',4032.00,0.00,4032.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(96,1,'4P1044',2,'2026-10-24','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-17',0.00,576.00,3456.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(97,1,'4P1044',2,'2026-10-24','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-17',576.00,0.00,2880.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(98,2,'4D9162',6,'2026-10-24','MASUK','AKTIF','DEMO-DTY-IN-19',3300.00,0.00,3300.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(99,1,'4P1044',2,'2026-10-24','KELUAR','AKTIF','DEMO-POY-OUT-19',0.00,3456.00,0.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(100,2,'4D9160',3,'2026-10-24','KELUAR','AKTIF','DEMO-DTY-SJ-06',0.00,990.00,11550.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(101,2,'4D9162',3,'2026-10-25','MASUK','AKTIF','DEMO-DTY-IN-20',3333.00,0.00,13365.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(102,1,'4P1044',2,'2026-10-25','RILIS_BUFFER','BUFFER','RILIS-BUFFER-2026-10-25',0.00,2880.00,0.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(103,1,'4P1044',2,'2026-10-25','RILIS_BUFFER','AKTIF','RILIS-BUFFER-2026-10-25',2880.00,0.00,2880.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(104,1,'4P1044',2,'2026-10-25','KELUAR','AKTIF','DEMO-POY-OUT-20',0.00,2880.00,0.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(105,1,'4P1065',1,'2026-10-26','MASUK','AKTIF','DEMO-POY-IN-18',4032.00,0.00,4032.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(106,1,'4P1065',1,'2026-10-26','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-18',0.00,576.00,3456.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(107,1,'4P1065',1,'2026-10-26','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-18',576.00,0.00,576.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(108,2,'4D9163',7,'2026-10-26','MASUK','AKTIF','DEMO-DTY-IN-21',330.00,0.00,330.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(109,1,'4P1065',1,'2026-10-26','KELUAR','AKTIF','DEMO-POY-OUT-21',0.00,3456.00,0.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(110,1,'4P1065',1,'2026-10-27','MASUK','AKTIF','DEMO-POY-IN-19',4032.00,0.00,4032.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(111,1,'4P1065',1,'2026-10-27','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-19',0.00,576.00,3456.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(112,1,'4P1065',1,'2026-10-27','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-19',576.00,0.00,1152.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(113,2,'4D9163',5,'2026-10-27','MASUK','AKTIF','DEMO-DTY-IN-22',3300.00,0.00,6633.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(114,1,'4P1065',1,'2026-10-27','KELUAR','AKTIF','DEMO-POY-OUT-22',0.00,3456.00,0.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(115,1,'4P1065',1,'2026-10-28','MASUK','AKTIF','DEMO-POY-IN-20',4032.00,0.00,4032.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(116,1,'4P1065',1,'2026-10-28','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-20',0.00,576.00,3456.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(117,1,'4P1065',1,'2026-10-28','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-20',576.00,0.00,1728.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(118,2,'4D9163',3,'2026-10-28','MASUK','AKTIF','DEMO-DTY-IN-23',3333.00,0.00,13299.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(119,1,'4P1065',1,'2026-10-28','KELUAR','AKTIF','DEMO-POY-OUT-23',0.00,3456.00,0.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(120,2,'4D9162',3,'2026-10-28','KELUAR','AKTIF','DEMO-DTY-SJ-07',0.00,825.00,12540.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(121,1,'4P1065',1,'2026-10-29','MASUK','AKTIF','DEMO-POY-IN-21',4032.00,0.00,4032.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(122,1,'4P1065',1,'2026-10-29','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-21',0.00,576.00,3456.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(123,1,'4P1065',1,'2026-10-29','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-21',576.00,0.00,2304.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(124,2,'4D9163',6,'2026-10-29','MASUK','AKTIF','DEMO-DTY-IN-24',3366.00,0.00,6666.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(125,4,'GRN-PEI-03',8,'2026-10-29','MASUK','AKTIF','DEMO-PENDUKUNG-IN-04',450.00,0.00,450.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(126,1,'4P1065',1,'2026-10-29','KELUAR','AKTIF','DEMO-POY-OUT-24',0.00,3456.00,0.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(127,2,'4D9163',7,'2026-10-29','KELUAR','AKTIF','DEMO-DTY-SCRAP-02',0.00,165.00,165.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(128,1,'4P1065',1,'2026-10-30','MASUK','AKTIF','DEMO-POY-IN-22',4032.00,0.00,4032.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(129,1,'4P1065',1,'2026-10-30','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-22',0.00,576.00,3456.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(130,1,'4P1065',1,'2026-10-30','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-22',576.00,0.00,2880.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(131,2,'4D9163',3,'2026-10-30','MASUK','AKTIF','DEMO-DTY-IN-25',3300.00,0.00,16599.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(132,1,'4P1065',1,'2026-10-30','KELUAR','AKTIF','DEMO-POY-OUT-25',0.00,3456.00,0.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(133,1,'4P1065',1,'2026-10-31','MASUK','AKTIF','DEMO-POY-IN-23',4032.00,0.00,4032.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(134,1,'4P1065',1,'2026-10-31','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-23',0.00,576.00,3456.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(135,1,'4P1065',1,'2026-10-31','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-23',576.00,0.00,3456.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(136,2,'4D9163',3,'2026-10-31','MASUK','AKTIF','DEMO-DTY-IN-26',3333.00,0.00,19932.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(137,1,'4P1065',1,'2026-10-31','KELUAR','AKTIF','DEMO-POY-OUT-26',0.00,3456.00,0.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(138,2,'4D9163',3,'2026-10-31','KELUAR','AKTIF','DEMO-DTY-SJ-08',0.00,660.00,19272.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(139,2,'4D9163',5,'2026-11-01','MASUK','AKTIF','DEMO-DTY-IN-27',3366.00,0.00,9999.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(140,1,'4P1065',1,'2026-11-01','RILIS_BUFFER','BUFFER','RILIS-BUFFER-2026-11-01',0.00,3456.00,0.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(141,1,'4P1065',1,'2026-11-01','RILIS_BUFFER','AKTIF','RILIS-BUFFER-2026-11-01',3456.00,0.00,3456.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(142,1,'4P1065',1,'2026-11-01','KELUAR','AKTIF','DEMO-POY-OUT-27',0.00,3456.00,0.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(143,1,'4P1044',2,'2026-11-02','MASUK','AKTIF','DEMO-POY-IN-24',4032.00,0.00,4032.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(144,1,'4P1044',2,'2026-11-02','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-24',0.00,576.00,3456.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(145,1,'4P1044',2,'2026-11-02','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-24',576.00,0.00,576.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(146,2,'4D9160',3,'2026-11-02','MASUK','AKTIF','DEMO-DTY-IN-28',3300.00,0.00,14850.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(147,1,'4P1044',2,'2026-11-02','KELUAR','AKTIF','DEMO-POY-OUT-28',0.00,3456.00,0.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(148,1,'4P1044',2,'2026-11-03','MASUK','AKTIF','DEMO-POY-IN-25',4032.00,0.00,4032.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(149,1,'4P1044',2,'2026-11-03','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-25',0.00,576.00,3456.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(150,1,'4P1044',2,'2026-11-03','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-25',576.00,0.00,1152.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(151,2,'4D9160',6,'2026-11-03','MASUK','AKTIF','DEMO-DTY-IN-29',3333.00,0.00,6699.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(152,1,'4P1044',2,'2026-11-03','KELUAR','AKTIF','DEMO-POY-OUT-29',0.00,3456.00,0.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(153,1,'4P1044',2,'2026-11-04','MASUK','AKTIF','DEMO-POY-IN-26',4032.00,0.00,4032.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(154,1,'4P1044',2,'2026-11-04','ALOKASI_BUFFER','AKTIF','DEMO-POY-IN-26',0.00,576.00,3456.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(155,1,'4P1044',2,'2026-11-04','ALOKASI_BUFFER','BUFFER','DEMO-POY-IN-26',576.00,0.00,1728.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(156,2,'4D9160',3,'2026-11-04','MASUK','AKTIF','DEMO-DTY-IN-30',3366.00,0.00,18216.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(157,1,'4P1044',2,'2026-11-04','KELUAR','AKTIF','DEMO-POY-OUT-30',0.00,3456.00,0.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(158,1,'4P1044',2,'2026-11-04','MASUK','AKTIF','DEMO-POY-IN-27',4032.00,0.00,4032.00,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(159,4,'GRN-VELVET',8,'2026-11-02','ADJUSTMENT_IN','AKTIF','IN-2026-11-1',15.00,0.00,415.00,1,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(160,8,'GRN-PEI-01',8,'2026-11-03','ADJUSTMENT_OUT','AKTIF','OUT-2026-11-1',0.00,5.00,195.00,1,'2026-11-04 05:05:41','2026-11-04 05:05:41');
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
(1,'SURAT_JALAN_DTY','2026-11',0,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(2,'GATE_PASS','2026-11',0,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(3,'TRX_MASUK','2026-11',1,'2026-11-04 05:05:39','2026-11-04 05:05:41'),
(4,'TRX_KELUAR','2026-11',1,'2026-11-04 05:05:39','2026-11-04 05:05:41'),
(5,'SURAT_JALAN_DTY','2026-10',8,'2026-11-04 05:05:40','2026-11-04 05:05:41');
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
('34d6f506-9a59-43a4-a731-9606dc833542','Filament\\Notifications\\DatabaseNotification','App\\Models\\User',3,'{\"actions\":[],\"body\":\"Sisa 195,00 KG dari minimum 200,00 KG.\",\"color\":null,\"duration\":\"persistent\",\"icon\":\"heroicon-o-exclamation-triangle\",\"iconColor\":\"warning\",\"iconSize\":null,\"status\":\"warning\",\"title\":\"Stok DTY CONING OIL, TM368 BN menipis\",\"view\":null,\"viewData\":[],\"format\":\"filament\"}',NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
('36b13bd8-c92a-4f5b-b707-34bfb52f90e2','Filament\\Notifications\\DatabaseNotification','App\\Models\\User',1,'{\"actions\":[],\"body\":\"Sisa 195,00 KG dari minimum 200,00 KG.\",\"color\":null,\"duration\":\"persistent\",\"icon\":\"heroicon-o-exclamation-triangle\",\"iconColor\":\"warning\",\"iconSize\":null,\"status\":\"warning\",\"title\":\"Stok DTY CONING OIL, TM368 BN menipis\",\"view\":null,\"viewData\":[],\"format\":\"filament\"}',NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41');
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `satuan` DISABLE KEYS */;
INSERT INTO `satuan` VALUES
(1,'KG','Kilogram','2026-11-04 05:05:39','2026-11-04 05:05:39'),
(2,'BOX','Box / Carton','2026-11-04 05:05:39','2026-11-04 05:05:39'),
(3,'PALLET','Pallet','2026-11-04 05:05:39','2026-11-04 05:05:39'),
(4,'PCS','Pieces','2026-11-04 05:05:39','2026-11-04 05:05:39'),
(5,'ROLL','Roll','2026-11-04 05:05:39','2026-11-04 05:05:39'),
(6,'BOBBIN','Bobbin / Gulungan','2026-11-04 05:05:39','2026-11-04 05:05:39');
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `stok_barang` DISABLE KEYS */;
INSERT INTO `stok_barang` VALUES
(1,1,'4P1044',2,4032.00,1728.00,3708,0,'2026-11-04 05:05:39','2026-11-04 05:05:41'),
(2,2,'4D9163',3,19272.00,0.00,4242,707,'2026-11-04 05:05:39','2026-11-04 05:05:41'),
(3,2,'4D9163',5,9999.00,0.00,1818,303,'2026-11-04 05:05:39','2026-11-04 05:05:41'),
(4,3,'GRN-VELVET',8,3456.00,0.00,0,0,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(5,4,'GRN-VELVET',8,415.00,0.00,0,0,'2026-11-04 05:05:40','2026-11-04 05:05:41'),
(6,2,'4D9163',6,6666.00,0.00,1212,202,'2026-11-04 05:05:40','2026-11-04 05:05:41'),
(7,1,'4P1065',1,0.00,0.00,2952,0,'2026-11-04 05:05:40','2026-11-04 05:05:41'),
(8,2,'4D9160',5,6666.00,0.00,1212,202,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(9,2,'4D9160',3,18216.00,0.00,3624,604,'2026-11-04 05:05:40','2026-11-04 05:05:41'),
(10,2,'4D9160',6,6699.00,0.00,1218,203,'2026-11-04 05:05:40','2026-11-04 05:05:41'),
(11,8,'GRN-PEI-01',8,195.00,0.00,0,0,'2026-11-04 05:05:40','2026-11-04 05:05:41'),
(12,2,'4D9162',7,165.00,0.00,60,10,'2026-11-04 05:05:40','2026-11-04 05:05:41'),
(13,2,'4D9162',3,12540.00,0.00,2430,405,'2026-11-04 05:05:40','2026-11-04 05:05:41'),
(14,2,'4D9162',5,3333.00,0.00,606,101,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(15,3,'GRN-PEI-02',8,2500.00,0.00,0,0,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(16,2,'4D9162',6,3300.00,0.00,600,100,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(17,2,'4D9163',7,165.00,0.00,60,10,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(18,4,'GRN-PEI-03',8,450.00,0.00,0,0,'2026-11-04 05:05:41','2026-11-04 05:05:41');
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
(1,'OPN-IN-2026-11-1','2026-11-02',4,'GRN-VELVET',8,400.00,415.00,'Opname demo selisih positif.',1,64,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41',15.00),
(2,'OPN-OUT-2026-11-1','2026-11-03',8,'GRN-PEI-01',8,200.00,195.00,'Opname demo selisih negatif kecil.',1,NULL,42,'2026-11-04 05:05:41','2026-11-04 05:05:41',-5.00),
(3,'OPN-20261104-3-8-GRNVELVET-c5e659','2026-11-04',3,'GRN-VELVET',8,3456.00,3456.00,'Opname demo selisih nol.',1,NULL,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41',0.00);
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
(1,'IPCI','IPCI - 300097905','BOTH',NULL,'PT. Indorama Polychem Indonesia','2026-11-04 05:05:39','2026-11-04 05:05:39',NULL),
(2,'STORE-IPCI','Store IPCI','SUPPLIER',NULL,'PT. Indorama Polychem Indonesia — Dept. STORE','2026-11-04 05:05:39','2026-11-04 05:05:39',NULL),
(3,'AREA PRODUKSI','Area Produksi DTY PEI','INTERNAL',NULL,'Area Doffing / Transit Produksi','2026-11-04 05:05:39','2026-11-04 05:05:39',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `transaksi_keluar` DISABLE KEYS */;
INSERT INTO `transaksi_keluar` VALUES
(1,'DEMO-POY-OUT-01','POY_PRODUKSI',NULL,'2026-10-06',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(2,'DEMO-POY-OUT-02','POY_PRODUKSI',NULL,'2026-10-07',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(3,'DEMO-POY-OUT-03','POY_PRODUKSI',NULL,'2026-10-08',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(4,'DEMO-POY-OUT-04','POY_PRODUKSI',NULL,'2026-10-09',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(5,'DEMO-POY-OUT-05','POY_PRODUKSI',NULL,'2026-10-10',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(6,'DEMO-DTY-SJ-01','DTY_SURAT_JALAN','1/DTY/PEI/X/2026','2026-10-10',1,'B 1234 DEMO','Joko Demo','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(7,'DEMO-DTY-SJ-02','DTY_SURAT_JALAN','2/DTY/PEI/X/2026','2026-10-10',1,'B 5678 KIRIM','Slamet Kirim','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(8,'DEMO-POY-OUT-06','POY_PRODUKSI',NULL,'2026-10-11',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(9,'DEMO-POY-OUT-07','POY_PRODUKSI',NULL,'2026-10-12',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(10,'DEMO-POY-OUT-08','POY_PRODUKSI',NULL,'2026-10-13',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(11,'DEMO-POY-OUT-09','POY_PRODUKSI',NULL,'2026-10-14',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(12,'DEMO-DTY-SJ-03','DTY_SURAT_JALAN','3/DTY/PEI/X/2026','2026-10-14',1,'B 9012 ANTAR','Andi Antar','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(13,'DEMO-POY-OUT-10','POY_PRODUKSI',NULL,'2026-10-15',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(14,'DEMO-POY-OUT-11','POY_PRODUKSI',NULL,'2026-10-16',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(15,'DEMO-POY-OUT-12','POY_PRODUKSI',NULL,'2026-10-17',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(16,'DEMO-DTY-SJ-04','DTY_SURAT_JALAN','4/DTY/PEI/X/2026','2026-10-17',1,'B 3456 ANGKUT','Budi Angkut','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(17,'DEMO-POY-OUT-13','POY_PRODUKSI',NULL,'2026-10-18',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(18,'DEMO-POY-OUT-14','POY_PRODUKSI',NULL,'2026-10-19',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(19,'DEMO-POY-OUT-15','POY_PRODUKSI',NULL,'2026-10-20',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(20,'DEMO-POY-OUT-16','POY_PRODUKSI',NULL,'2026-10-21',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(21,'DEMO-DTY-SJ-05','DTY_SURAT_JALAN','5/DTY/PEI/X/2026','2026-10-21',1,'B 1234 DEMO','Joko Demo','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(22,'DEMO-POY-OUT-17','POY_PRODUKSI',NULL,'2026-10-22',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(23,'DEMO-DTY-SCRAP-01','DTY_SCRAP_REJECT',NULL,'2026-10-22',NULL,NULL,NULL,NULL,NULL,NULL,'Budi Santoso','Scrap knitting grade C batch 4D9162 dijual ke pengepul.','POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(24,'DEMO-POY-OUT-18','POY_PRODUKSI',NULL,'2026-10-23',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(25,'DEMO-POY-OUT-19','POY_PRODUKSI',NULL,'2026-10-24',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(26,'DEMO-DTY-SJ-06','DTY_SURAT_JALAN','6/DTY/PEI/X/2026','2026-10-24',1,'B 5678 KIRIM','Slamet Kirim','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(27,'DEMO-POY-OUT-20','POY_PRODUKSI',NULL,'2026-10-25',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(28,'DEMO-POY-OUT-21','POY_PRODUKSI',NULL,'2026-10-26',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(29,'DEMO-POY-OUT-22','POY_PRODUKSI',NULL,'2026-10-27',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(30,'DEMO-POY-OUT-23','POY_PRODUKSI',NULL,'2026-10-28',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(31,'DEMO-DTY-SJ-07','DTY_SURAT_JALAN','7/DTY/PEI/X/2026','2026-10-28',1,'B 9012 ANTAR','Andi Antar','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(32,'DEMO-POY-OUT-24','POY_PRODUKSI',NULL,'2026-10-29',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(33,'DEMO-DTY-SCRAP-02','DTY_SCRAP_REJECT',NULL,'2026-10-29',NULL,NULL,NULL,NULL,NULL,NULL,'Budi Santoso','Scrap knitting grade C batch 4D9163 dijual ke pengepul.','POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(34,'DEMO-POY-OUT-25','POY_PRODUKSI',NULL,'2026-10-30',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(35,'DEMO-POY-OUT-26','POY_PRODUKSI',NULL,'2026-10-31',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(36,'DEMO-DTY-SJ-08','DTY_SURAT_JALAN','8/DTY/PEI/X/2026','2026-10-31',1,'B 3456 ANGKUT','Budi Angkut','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(37,'DEMO-POY-OUT-27','POY_PRODUKSI',NULL,'2026-11-01',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(38,'DEMO-POY-OUT-28','POY_PRODUKSI',NULL,'2026-11-02',NULL,NULL,NULL,NULL,NULL,1,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(39,'DEMO-POY-OUT-29','POY_PRODUKSI',NULL,'2026-11-03',NULL,NULL,NULL,NULL,NULL,2,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(40,'DEMO-POY-OUT-30','POY_PRODUKSI',NULL,'2026-11-04',NULL,NULL,NULL,NULL,NULL,3,'Budi Santoso',NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(41,'DEMO-DTY-DRAFT-01','DTY_SURAT_JALAN',NULL,'2026-11-04',1,'B 9999 DRAF','Sopir Draf','Budi Santoso',NULL,NULL,'Budi Santoso',NULL,'DRAFT',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(42,'OUT-2026-11-1','ADJUSTMENT_OUT',NULL,'2026-11-03',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Opname demo selisih negatif kecil.','POSTED',NULL,NULL,NULL,1,'2026-11-04 05:05:41','2026-11-04 05:05:41');
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
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `transaksi_keluar_detail` DISABLE KEYS */;
INSERT INTO `transaksi_keluar_detail` VALUES
(1,1,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(2,2,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(3,3,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(4,4,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(5,5,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(6,6,2,'4D9163',3,25.00,NULL,150,825.00,893.50,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(7,7,2,'4D9163',3,30.00,NULL,180,990.00,1072.20,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(8,8,1,'4P1044',2,NULL,NULL,NULL,2880.00,NULL,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(9,9,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(10,10,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(11,11,1,'4P1065',1,NULL,NULL,NULL,2880.00,NULL,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(12,12,2,'4D9163',3,20.00,NULL,120,660.00,714.80,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(13,13,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(14,14,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(15,15,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(16,16,2,'4D9163',3,28.00,NULL,168,924.00,1000.72,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(17,17,1,'4P1065',1,NULL,NULL,NULL,2880.00,NULL,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(18,18,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(19,19,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(20,20,1,'4P1044',2,NULL,NULL,NULL,2880.00,NULL,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(21,21,2,'4D9160',3,22.00,NULL,132,726.00,786.28,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(22,22,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(23,23,2,'4D9162',7,NULL,NULL,NULL,165.00,NULL,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(24,24,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(25,25,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(26,26,2,'4D9160',3,30.00,NULL,180,990.00,1072.20,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(27,27,1,'4P1044',2,NULL,NULL,NULL,2880.00,NULL,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(28,28,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(29,29,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(30,30,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(31,31,2,'4D9162',3,25.00,NULL,150,825.00,893.50,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(32,32,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(33,33,2,'4D9163',7,NULL,NULL,NULL,165.00,NULL,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(34,34,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(35,35,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(36,36,2,'4D9163',3,20.00,NULL,120,660.00,714.80,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(37,37,1,'4P1065',1,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(38,38,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(39,39,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(40,40,1,'4P1044',2,NULL,NULL,NULL,3456.00,NULL,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(41,41,2,'4D9169',3,10.00,NULL,60,330.00,357.40,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(42,42,8,'GRN-PEI-01',8,NULL,NULL,NULL,5.00,NULL,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41');
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
(1,'DEMO-POY-IN-01','POY_IPCI',NULL,'2026-10-06',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(2,'DEMO-DTY-IN-01','DTY_PRODUKSI',NULL,'2026-10-06',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(3,'DEMO-POY-IN-02','POY_IPCI',NULL,'2026-10-07',1,NULL,NULL,2,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(4,'DEMO-DTY-IN-02','DTY_PRODUKSI',NULL,'2026-10-07',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(5,'DEMO-POY-IN-03','POY_IPCI',NULL,'2026-10-08',1,NULL,NULL,3,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(6,'DEMO-DTY-IN-03','DTY_PRODUKSI',NULL,'2026-10-08',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(7,'DEMO-PENDUKUNG-IN-01','PENDUKUNG_GATEPASS','DEMO-GP-001','2026-10-08',2,NULL,NULL,NULL,'Budi Santoso',NULL,NULL,NULL,NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-04 05:05:39','2026-11-04 05:05:40'),
(8,'DEMO-POY-IN-04','POY_IPCI',NULL,'2026-10-09',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(9,'DEMO-DTY-IN-04','DTY_PRODUKSI',NULL,'2026-10-09',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(10,'DEMO-POY-IN-05','POY_IPCI',NULL,'2026-10-10',1,NULL,NULL,2,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(11,'DEMO-DTY-IN-05','DTY_PRODUKSI',NULL,'2026-10-10',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(12,'DEMO-DTY-IN-06','DTY_PRODUKSI',NULL,'2026-10-11',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(13,'DEMO-POY-IN-06','POY_IPCI',NULL,'2026-10-12',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(14,'DEMO-DTY-IN-07','DTY_PRODUKSI',NULL,'2026-10-12',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(15,'DEMO-POY-IN-07','POY_IPCI',NULL,'2026-10-13',1,NULL,NULL,2,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(16,'DEMO-DTY-IN-08','DTY_PRODUKSI',NULL,'2026-10-13',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(17,'DEMO-POY-IN-08','POY_IPCI',NULL,'2026-10-14',1,NULL,NULL,3,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(18,'DEMO-DTY-IN-09','DTY_PRODUKSI',NULL,'2026-10-14',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(19,'DEMO-POY-IN-09','POY_IPCI',NULL,'2026-10-15',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(20,'DEMO-DTY-IN-10','DTY_PRODUKSI',NULL,'2026-10-15',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(21,'DEMO-PENDUKUNG-IN-02','PENDUKUNG_GATEPASS','DEMO-GP-002','2026-10-15',2,NULL,NULL,NULL,'Budi Santoso',NULL,NULL,NULL,NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(22,'DEMO-POY-IN-10','POY_IPCI',NULL,'2026-10-16',1,NULL,NULL,2,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(23,'DEMO-DTY-IN-11','DTY_PRODUKSI',NULL,'2026-10-16',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(24,'DEMO-POY-IN-11','POY_IPCI',NULL,'2026-10-17',1,NULL,NULL,3,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(25,'DEMO-DTY-IN-12','DTY_PRODUKSI',NULL,'2026-10-17',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(26,'DEMO-DTY-IN-13','DTY_PRODUKSI',NULL,'2026-10-18',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(27,'DEMO-POY-IN-12','POY_IPCI',NULL,'2026-10-19',1,NULL,NULL,2,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(28,'DEMO-DTY-IN-14','DTY_PRODUKSI',NULL,'2026-10-19',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(29,'DEMO-POY-IN-13','POY_IPCI',NULL,'2026-10-20',1,NULL,NULL,3,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(30,'DEMO-DTY-IN-15','DTY_PRODUKSI',NULL,'2026-10-20',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(31,'DEMO-POY-IN-14','POY_IPCI',NULL,'2026-10-21',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(32,'DEMO-DTY-IN-16','DTY_PRODUKSI',NULL,'2026-10-21',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(33,'DEMO-POY-IN-15','POY_IPCI',NULL,'2026-10-22',1,NULL,NULL,2,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(34,'DEMO-DTY-IN-17','DTY_PRODUKSI',NULL,'2026-10-22',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(35,'DEMO-PENDUKUNG-IN-03','PENDUKUNG_GATEPASS','DEMO-GP-003','2026-10-22',2,NULL,NULL,NULL,'Budi Santoso',NULL,NULL,NULL,NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(36,'DEMO-POY-IN-16','POY_IPCI',NULL,'2026-10-23',1,NULL,NULL,3,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(37,'DEMO-DTY-IN-18','DTY_PRODUKSI',NULL,'2026-10-23',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(38,'DEMO-POY-IN-17','POY_IPCI',NULL,'2026-10-24',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(39,'DEMO-DTY-IN-19','DTY_PRODUKSI',NULL,'2026-10-24',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(40,'DEMO-DTY-IN-20','DTY_PRODUKSI',NULL,'2026-10-25',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(41,'DEMO-POY-IN-18','POY_IPCI',NULL,'2026-10-26',1,NULL,NULL,3,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(42,'DEMO-DTY-IN-21','DTY_PRODUKSI',NULL,'2026-10-26',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(43,'DEMO-POY-IN-19','POY_IPCI',NULL,'2026-10-27',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(44,'DEMO-DTY-IN-22','DTY_PRODUKSI',NULL,'2026-10-27',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(45,'DEMO-POY-IN-20','POY_IPCI',NULL,'2026-10-28',1,NULL,NULL,2,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(46,'DEMO-DTY-IN-23','DTY_PRODUKSI',NULL,'2026-10-28',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(47,'DEMO-POY-IN-21','POY_IPCI',NULL,'2026-10-29',1,NULL,NULL,3,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(48,'DEMO-DTY-IN-24','DTY_PRODUKSI',NULL,'2026-10-29',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(49,'DEMO-PENDUKUNG-IN-04','PENDUKUNG_GATEPASS','DEMO-GP-004','2026-10-29',2,NULL,NULL,NULL,'Budi Santoso',NULL,NULL,NULL,NULL,'ISSUED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(50,'DEMO-POY-IN-22','POY_IPCI',NULL,'2026-10-30',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(51,'DEMO-DTY-IN-25','DTY_PRODUKSI',NULL,'2026-10-30',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(52,'DEMO-POY-IN-23','POY_IPCI',NULL,'2026-10-31',1,NULL,NULL,2,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(53,'DEMO-DTY-IN-26','DTY_PRODUKSI',NULL,'2026-10-31',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(54,'DEMO-DTY-IN-27','DTY_PRODUKSI',NULL,'2026-11-01',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1065',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(55,'DEMO-POY-IN-24','POY_IPCI',NULL,'2026-11-02',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(56,'DEMO-DTY-IN-28','DTY_PRODUKSI',NULL,'2026-11-02',NULL,'AREA PRODUKSI',NULL,1,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(57,'DEMO-POY-IN-25','POY_IPCI',NULL,'2026-11-03',1,NULL,NULL,2,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(58,'DEMO-DTY-IN-29','DTY_PRODUKSI',NULL,'2026-11-03',NULL,'AREA PRODUKSI',NULL,2,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(59,'DEMO-POY-IN-26','POY_IPCI',NULL,'2026-11-04',1,NULL,NULL,3,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(60,'DEMO-DTY-IN-30','DTY_PRODUKSI',NULL,'2026-11-04',NULL,'AREA PRODUKSI',NULL,3,'Budi Santoso','4P1044',NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(61,'DEMO-POY-IN-27','POY_IPCI',NULL,'2026-11-04',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'POSTED',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(62,'DEMO-POY-DRAFT-01','POY_IPCI',NULL,'2026-11-04',1,NULL,NULL,1,'Budi Santoso',NULL,NULL,NULL,NULL,'DRAFT',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(63,'DEMO-POY-DRAFT-02','POY_IPCI',NULL,'2026-11-04',1,NULL,NULL,3,'Budi Santoso',NULL,NULL,NULL,NULL,'DRAFT',NULL,NULL,NULL,2,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(64,'IN-2026-11-1','ADJUSTMENT_IN',NULL,'2026-11-02',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Opname demo selisih positif.','POSTED',NULL,NULL,NULL,1,'2026-11-04 05:05:41','2026-11-04 05:05:41');
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
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
/*!40000 ALTER TABLE `transaksi_masuk_detail` DISABLE KEYS */;
INSERT INTO `transaksi_masuk_detail` VALUES
(1,1,1,'4P1044',2,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(2,2,2,'4D9163',3,100.00,2,600,3300.00,'[GROSS=3574.00]','2026-11-04 05:05:39','2026-11-04 05:05:39'),
(3,3,1,'4P1044',2,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(4,4,2,'4D9163',5,101.00,2,606,3333.00,'[GROSS=3609.74]','2026-11-04 05:05:39','2026-11-04 05:05:39'),
(5,5,1,'4P1044',2,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(6,6,2,'4D9163',3,102.00,2,612,3366.00,'[GROSS=3645.48]','2026-11-04 05:05:39','2026-11-04 05:05:39'),
(7,7,3,'GRN-VELVET',8,3456.00,4,NULL,3456.00,'GRN-VELVET','2026-11-04 05:05:39','2026-11-04 05:05:39'),
(8,7,4,'GRN-VELVET',8,400.00,4,NULL,400.00,'GRN-VELVET','2026-11-04 05:05:39','2026-11-04 05:05:39'),
(9,8,1,'4P1044',2,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(10,9,2,'4D9163',6,100.00,2,600,3300.00,'[GROSS=3574.00]','2026-11-04 05:05:40','2026-11-04 05:05:40'),
(11,10,1,'4P1044',2,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(12,11,2,'4D9163',3,101.00,2,606,3333.00,'[GROSS=3609.74]','2026-11-04 05:05:40','2026-11-04 05:05:40'),
(13,12,2,'4D9163',3,102.00,2,612,3366.00,'[GROSS=3645.48]','2026-11-04 05:05:40','2026-11-04 05:05:40'),
(14,13,1,'4P1065',1,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(15,14,2,'4D9160',5,100.00,2,600,3300.00,'[GROSS=3574.00]','2026-11-04 05:05:40','2026-11-04 05:05:40'),
(16,15,1,'4P1065',1,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(17,16,2,'4D9160',3,101.00,2,606,3333.00,'[GROSS=3609.74]','2026-11-04 05:05:40','2026-11-04 05:05:40'),
(18,17,1,'4P1065',1,NULL,NULL,180,2880.00,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(19,18,2,'4D9160',6,102.00,2,612,3366.00,'[GROSS=3645.48]','2026-11-04 05:05:40','2026-11-04 05:05:40'),
(20,19,1,'4P1065',1,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(21,20,2,'4D9160',3,100.00,2,600,3300.00,'[GROSS=3574.00]','2026-11-04 05:05:40','2026-11-04 05:05:40'),
(22,21,8,'GRN-PEI-01',8,200.00,1,NULL,200.00,'GRN-PEI-01','2026-11-04 05:05:40','2026-11-04 05:05:40'),
(23,22,1,'4P1065',1,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(24,23,2,'4D9160',3,101.00,2,606,3333.00,'[GROSS=3609.74]','2026-11-04 05:05:40','2026-11-04 05:05:40'),
(25,24,1,'4P1065',1,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(26,25,2,'4D9160',5,102.00,2,612,3366.00,'[GROSS=3645.48]','2026-11-04 05:05:40','2026-11-04 05:05:40'),
(27,26,2,'4D9160',3,100.00,2,600,3300.00,'[GROSS=3574.00]','2026-11-04 05:05:40','2026-11-04 05:05:40'),
(28,27,1,'4P1044',2,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(29,28,2,'4D9162',7,10.00,2,60,330.00,'[GROSS=357.40]','2026-11-04 05:05:40','2026-11-04 05:05:40'),
(30,29,1,'4P1044',2,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(31,30,2,'4D9162',3,102.00,2,612,3366.00,'[GROSS=3645.48]','2026-11-04 05:05:40','2026-11-04 05:05:40'),
(32,31,1,'4P1044',2,NULL,NULL,180,2880.00,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(33,32,2,'4D9162',3,100.00,2,600,3300.00,'[GROSS=3574.00]','2026-11-04 05:05:40','2026-11-04 05:05:40'),
(34,33,1,'4P1044',2,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:40','2026-11-04 05:05:40'),
(35,34,2,'4D9162',5,101.00,2,606,3333.00,'[GROSS=3609.74]','2026-11-04 05:05:40','2026-11-04 05:05:40'),
(36,35,3,'GRN-PEI-02',8,2500.00,4,NULL,2500.00,'GRN-PEI-02','2026-11-04 05:05:40','2026-11-04 05:05:40'),
(37,36,1,'4P1044',2,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(38,37,2,'4D9162',3,102.00,2,612,3366.00,'[GROSS=3645.48]','2026-11-04 05:05:41','2026-11-04 05:05:41'),
(39,38,1,'4P1044',2,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(40,39,2,'4D9162',6,100.00,2,600,3300.00,'[GROSS=3574.00]','2026-11-04 05:05:41','2026-11-04 05:05:41'),
(41,40,2,'4D9162',3,101.00,2,606,3333.00,'[GROSS=3609.74]','2026-11-04 05:05:41','2026-11-04 05:05:41'),
(42,41,1,'4P1065',1,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(43,42,2,'4D9163',7,10.00,2,60,330.00,'[GROSS=357.40]','2026-11-04 05:05:41','2026-11-04 05:05:41'),
(44,43,1,'4P1065',1,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(45,44,2,'4D9163',5,100.00,2,600,3300.00,'[GROSS=3574.00]','2026-11-04 05:05:41','2026-11-04 05:05:41'),
(46,45,1,'4P1065',1,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(47,46,2,'4D9163',3,101.00,2,606,3333.00,'[GROSS=3609.74]','2026-11-04 05:05:41','2026-11-04 05:05:41'),
(48,47,1,'4P1065',1,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(49,48,2,'4D9163',6,102.00,2,612,3366.00,'[GROSS=3645.48]','2026-11-04 05:05:41','2026-11-04 05:05:41'),
(50,49,4,'GRN-PEI-03',8,450.00,4,NULL,450.00,'GRN-PEI-03','2026-11-04 05:05:41','2026-11-04 05:05:41'),
(51,50,1,'4P1065',1,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(52,51,2,'4D9163',3,100.00,2,600,3300.00,'[GROSS=3574.00]','2026-11-04 05:05:41','2026-11-04 05:05:41'),
(53,52,1,'4P1065',1,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(54,53,2,'4D9163',3,101.00,2,606,3333.00,'[GROSS=3609.74]','2026-11-04 05:05:41','2026-11-04 05:05:41'),
(55,54,2,'4D9163',5,102.00,2,612,3366.00,'[GROSS=3645.48]','2026-11-04 05:05:41','2026-11-04 05:05:41'),
(56,55,1,'4P1044',2,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(57,56,2,'4D9160',3,100.00,2,600,3300.00,'[GROSS=3574.00]','2026-11-04 05:05:41','2026-11-04 05:05:41'),
(58,57,1,'4P1044',2,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(59,58,2,'4D9160',6,101.00,2,606,3333.00,'[GROSS=3609.74]','2026-11-04 05:05:41','2026-11-04 05:05:41'),
(60,59,1,'4P1044',2,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(61,60,2,'4D9160',3,102.00,2,612,3366.00,'[GROSS=3645.48]','2026-11-04 05:05:41','2026-11-04 05:05:41'),
(62,61,1,'4P1044',2,NULL,NULL,252,4032.00,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(63,62,1,'4P1066',1,NULL,NULL,NULL,576.00,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(64,63,1,'4P1067',2,NULL,NULL,NULL,576.00,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41'),
(65,64,4,'GRN-VELVET',8,NULL,NULL,NULL,15.00,NULL,'2026-11-04 05:05:41','2026-11-04 05:05:41');
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
(1,'Administrator Gudang','admin','admin@wms.local','2026-11-04 05:05:39','$2y$12$sui7XcdfNHh1PQ8SWwK7a.qTj.FHuNTvhvAhmv4nXlXUsGe6lu2Ou','admin',1,NULL,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(2,'Operator Gudang','operator','operator@wms.local','2026-11-04 05:05:39','$2y$12$nBnvHoblsqkQBKGrIqrVj.ypky9LDMxjNNCleiRG.R3KFhHyKr/c2','operator',1,NULL,'2026-11-04 05:05:39','2026-11-04 05:05:39'),
(3,'Manajer DTY PEI','manajer','manajer@wms.local','2026-11-04 05:05:39','$2y$12$7KN.LB59o4xRCjAdI39ml.MPwNg74ww8HrSW3GLqld6K5zltR3mse','manajer',1,NULL,'2026-11-04 05:05:39','2026-11-04 05:05:39');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
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
