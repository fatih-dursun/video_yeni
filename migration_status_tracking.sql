-- Video tablosuna "last_status_changed_by" kolonu ekle
-- Bu kolon, durumu son değiştiren admin'i takip eder

USE video_portal;

ALTER TABLE `videos` 
ADD COLUMN `last_status_changed_by` int(11) DEFAULT NULL AFTER `created_by`,
ADD KEY `idx_last_status_changed_by` (`last_status_changed_by`);

-- Foreign key ekle
ALTER TABLE `videos`
ADD CONSTRAINT `videos_ibfk_3` FOREIGN KEY (`last_status_changed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

-- Mevcut videoların durumunu değiştiren kişiyi oluşturan kişi olarak ayarla
UPDATE `videos` SET `last_status_changed_by` = `created_by` WHERE `last_status_changed_by` IS NULL;

SELECT 'Migration başarıyla tamamlandı!' as status;
