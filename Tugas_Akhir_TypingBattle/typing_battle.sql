-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versi server:                 8.0.30 - MySQL Community Server - GPL
-- OS Server:                    Win64
-- HeidiSQL Versi:               12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Membuang struktur basisdata untuk typing_battle
DROP DATABASE IF EXISTS `typing_battle`;
CREATE DATABASE IF NOT EXISTS `typing_battle` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `typing_battle`;

-- membuang struktur untuk table typing_battle.game_results
DROP TABLE IF EXISTS `game_results`;
CREATE TABLE IF NOT EXISTS `game_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `session_id` int DEFAULT NULL,
  `wpm` decimal(6,2) DEFAULT NULL,
  `accuracy` decimal(5,2) DEFAULT NULL,
  `error_rate` decimal(5,2) DEFAULT NULL,
  `raw_wpm` decimal(6,2) DEFAULT NULL,
  `total_errors` int DEFAULT '0',
  `total_chars` int DEFAULT '0',
  `wpm_round1` decimal(6,2) DEFAULT '0.00',
  `wpm_round5` decimal(6,2) DEFAULT '0.00',
  `finished_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `game_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `game_results_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `game_sessions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel typing_battle.game_results: ~17 rows (lebih kurang)
INSERT INTO `game_results` (`id`, `user_id`, `session_id`, `wpm`, `accuracy`, `error_rate`, `raw_wpm`, `total_errors`, `total_chars`, `wpm_round1`, `wpm_round5`, `finished_at`) VALUES
	(1, 1, 1, 33.00, 93.00, 7.00, 33.00, 32, 464, 32.00, 33.00, '2026-07-10 16:00:13'),
	(2, 2, 1, 34.00, 90.00, 10.00, 34.00, 45, 459, 5.00, 34.00, '2026-07-10 16:03:10');

-- membuang struktur untuk table typing_battle.game_sessions
DROP TABLE IF EXISTS `game_sessions`;
CREATE TABLE IF NOT EXISTS `game_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `room_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `host_id` int DEFAULT NULL,
  `phrase_ids` text COLLATE utf8mb4_unicode_ci,
  `status` enum('waiting','playing','finished') COLLATE utf8mb4_unicode_ci DEFAULT 'waiting',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_code` (`room_code`),
  KEY `host_id` (`host_id`),
  CONSTRAINT `game_sessions_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel typing_battle.game_sessions: ~0 rows (lebih kurang)
INSERT INTO `game_sessions` (`id`, `room_code`, `host_id`, `phrase_ids`, `status`, `created_at`) VALUES
	(1, '654E83', 1, '[{"id":16,"content":"WPM rata-rata orang adalah empat puluh, tetapi profesional mampu mencapai angka di atas seratus"},{"id":14,"content":"Pemrograman berorientasi objek membantu dalam membangun aplikasi besar yang lebih mudah dikelola"},{"id":6,"content":"Olahraga secara rutin dapat membuat tubuh kita tetap bugar dan sehat"},{"id":7,"content":"Membaca buku adalah jendela dunia untuk menambah wawasan kita"},{"id":10,"content":"Garry Kasparov pernah bertanding melawan superkomputer IBM Deep Blue dalam sejarah catur dunia"}]', 'playing', '2026-07-10 15:56:32');

-- membuang struktur untuk table typing_battle.phrases
DROP TABLE IF EXISTS `phrases`;
CREATE TABLE IF NOT EXISTS `phrases` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `difficulty` enum('easy','medium','hard') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel typing_battle.phrases: ~21 rows (lebih kurang)
INSERT INTO `phrases` (`id`, `content`, `difficulty`) VALUES
	(1, 'Matahari terbit dari ufuk timur membawa harapan baru bagi semua orang', 'easy'),
	(2, 'Kucing peliharaan itu tertidur pulas di atas sofa yang sangat empuk', 'easy'),
	(3, 'Belajar mengetik itu menyenangkan jika kita rajin berlatih setiap hari', 'easy'),
	(4, 'Kecepatan mengetik adalah modal penting bagi seorang mahasiswa masa kini', 'easy'),
	(5, 'Kita harus selalu menjaga kebersihan lingkungan di sekitar rumah kita', 'easy'),
	(6, 'Olahraga secara rutin dapat membuat tubuh kita tetap bugar dan sehat', 'easy'),
	(7, 'Membaca buku adalah jendela dunia untuk menambah wawasan kita', 'easy'),
	(8, 'Protokol WebSocket memungkinkan server mengirimkan data secara langsung tanpa permintaan berulang', 'medium'),
	(9, 'Keamanan sistem berkas NTFS pada Windows didukung oleh fitur daftar kendali akses bagi pengguna', 'medium'),
	(10, 'Garry Kasparov pernah bertanding melawan superkomputer IBM Deep Blue dalam sejarah catur dunia', 'medium'),
	(11, 'Machine Learning adalah cabang kecerdasan buatan yang memungkinkan komputer belajar dari pola data', 'medium'),
	(12, 'Game multiplayer Typing Battle membutuhkan sinkronisasi data cepat agar posisi pemain terlihat akurat', 'medium'),
	(13, 'Mahasiswa seringkali harus menghadapi deadline tugas kuliah yang sangat padat di akhir semester', 'medium'),
	(14, 'Pemrograman berorientasi objek membantu dalam membangun aplikasi besar yang lebih mudah dikelola', 'medium'),
	(15, 'Dalam sistem NTFS, Master File Table menyimpan metadata tentang setiap file di dalam volume tersebut', 'hard'),
	(16, 'WPM rata-rata orang adalah empat puluh, tetapi profesional mampu mencapai angka di atas seratus', 'hard'),
	(17, 'Implementasi switch-case dan do-while pada bahasa C++ sangat berguna untuk membuat logika program', 'hard'),
	(18, 'Evaluasi performa sistem dilakukan dengan menghitung rata-rata skor, waktu respon, dan konsistensi', 'hard'),
	(19, 'JavaScript dan PHP adalah kombinasi kuat untuk membangun aplikasi web interaktif dengan basis MySQL', 'hard'),
	(20, 'Error rate dihitung dari jumlah karakter salah dibagi total karakter dikali seratus persen hasilnya', 'hard'),
	(21, 'Sinkronisasi akun game antara perangkat mobile dan PC membutuhkan sistem API yang handal dan aman', 'hard');

-- membuang struktur untuk table typing_battle.player_progress
DROP TABLE IF EXISTS `player_progress`;
CREATE TABLE IF NOT EXISTS `player_progress` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `user_id` int NOT NULL,
  `progress` decimal(5,2) DEFAULT '0.00',
  `current_round` int DEFAULT '1',
  `wpm` decimal(6,2) DEFAULT '0.00',
  `accuracy` decimal(5,2) DEFAULT '100.00',
  `error_rate` decimal(5,2) DEFAULT '0.00',
  `finished` tinyint(1) DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_prog` (`session_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `player_progress_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `game_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `player_progress_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel typing_battle.player_progress: ~25 rows (lebih kurang)
INSERT INTO `player_progress` (`id`, `session_id`, `user_id`, `progress`, `current_round`, `wpm`, `accuracy`, `error_rate`, `finished`, `updated_at`) VALUES
	(1, 1, 1, 100.00, 5, 33.00, 93.00, 7.00, 1, '2026-07-10 16:00:13'),
	(2, 1, 2, 100.00, 5, 34.00, 90.00, 10.00, 1, '2026-07-10 16:03:10');

-- membuang struktur untuk table typing_battle.room_players
DROP TABLE IF EXISTS `room_players`;
CREATE TABLE IF NOT EXISTS `room_players` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `user_id` int NOT NULL,
  `joined_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_session_user` (`session_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `room_players_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `game_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `room_players_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel typing_battle.room_players: ~2 rows (lebih kurang)

-- membuang struktur untuk table typing_battle.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nickname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel typing_battle.users: ~34 rows (lebih kurang)
INSERT INTO `users` (`id`, `nickname`, `created_at`) VALUES
	(1, 'Farid', '2026-07-10 15:56:32'),
	(2, 'Nisa', '2026-07-10 15:56:32');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
