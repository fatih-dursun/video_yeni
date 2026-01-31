-- MİGRATION SCRIPT
-- Mevcut video_portal veritabanını güncellemek için bu SQL'i çalıştırın

USE video_portal;

-- 1. Admins tablosuna 'status' kolonu ekle
ALTER TABLE `admins` 
ADD COLUMN `status` enum('active','passive') NOT NULL DEFAULT 'active' AFTER `role`;

-- 2. Admins tablosuna status indexi ekle
ALTER TABLE `admins`
ADD KEY `idx_status` (`status`);

-- 3. Videos tablosundaki 'deleted' durumunu kaldır, sadece 'active' ve 'passive' kalsın
-- Önce mevcut 'deleted' videoları 'passive' yap
UPDATE `videos` SET `status` = 'passive' WHERE `status` = 'deleted';

-- 4. Videos status enum'unu güncelle
ALTER TABLE `videos` 
MODIFY COLUMN `status` enum('active','passive') DEFAULT 'active';

-- 5. Tüm mevcut adminleri 'active' yap
UPDATE `admins` SET `status` = 'active';

-- Migration tamamlandı!
SELECT 'Migration başarıyla tamamlandı!' as status;
