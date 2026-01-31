-- phpMyAdmin SQL Dump
-- UPDATED VERSION - Video Portal Geliştirmeler
-- Değişiklikler:
-- 1. admins tablosuna 'status' kolonu eklendi (active/passive)
-- 2. videos tablosundaki 'deleted' status kaldırıldı, sadece 'active/passive'

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `video_portal`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','editor') NOT NULL DEFAULT 'editor',
  `status` enum('active','passive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `admins`
--

INSERT INTO `admins` (`id`, `username`, `first_name`, `last_name`, `password`, `email`, `role`, `status`, `created_at`, `updated_at`) VALUES
(2, 'admin', 'Admin', 'Kullanıcı', '$2y$10$g5UZoNRgKyUiNwrrnIMqsueKGvQ7NnCJwRw1ojMyuSj6mBjDQXgKS', 'admin@example.com', 'admin', 'active', '2026-01-09 20:50:14', '2026-01-11 18:51:11'),
(3, 'editor', 'Editör', 'Kullanıcı', '$2y$10$g5UZoNRgKyUiNwrrnIMqsueKGvQ7NnCJwRw1ojMyuSj6mBjDQXgKS', 'editor@example.com', 'editor', 'active', '2026-01-09 21:47:21', '2026-01-11 18:51:11');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `background_color` varchar(7) NOT NULL DEFAULT '#3B82F6',
  `text_color` varchar(7) NOT NULL DEFAULT '#FFFFFF',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `background_color`, `text_color`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '📚 Eğitim', 'egitim', '#3B82F6', '#FFFFFF', NULL, '2026-01-09 20:08:58', '2026-01-09 20:08:58'),
(2, '🎭 Eğlence', 'eglence', '#EF4444', '#FFFFFF', NULL, '2026-01-09 20:08:58', '2026-01-09 20:08:58'),
(3, '🤖 Teknoloji', 'teknoloji', '#8B5CF6', '#FFFFFF', NULL, '2026-01-09 20:08:58', '2026-01-09 20:08:58'),
(4, '⚽ Spor', 'spor', '#10B981', '#FFFFFF', NULL, '2026-01-09 20:08:58', '2026-01-09 20:08:58'),
(5, '🎵 Müzik', 'muzik', '#F59E0B', '#1F2937', NULL, '2026-01-09 20:08:58', '2026-01-09 20:08:58'),
(6, '✈️ Seyahat', 'seyahat', '#06B6D4', '#FFFFFF', NULL, '2026-01-09 20:08:58', '2026-01-09 20:08:58');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `videos`
--

CREATE TABLE `videos` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(300) NOT NULL,
  `description` text DEFAULT NULL,
  `featured_text` varchar(150) DEFAULT NULL,
  `video_path` varchar(255) NOT NULL,
  `thumbnail_path` varchar(255) NOT NULL,
  `featured_image_path` varchar(255) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `status` enum('active','passive') DEFAULT 'active',
  `view_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_status` (`status`);

--
-- Tablo için indeksler `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `created_by` (`created_by`);

--
-- Tablo için indeksler `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `created_by` (`created_by`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Tablo için AUTO_INCREMENT değeri `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Tablo kısıtlamaları `videos`
--
ALTER TABLE `videos`
  ADD CONSTRAINT `videos_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `videos_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
