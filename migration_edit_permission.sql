-- Video tablosuna "edited_by" kolonu ekle
-- Admin aktif edince düzenleme izni verir

USE video_portal;

ALTER TABLE `videos` 
ADD COLUMN `can_edit_after_admin` tinyint(1) DEFAULT 1 AFTER `last_status_changed_by`;

-- Varsayılan olarak hepsi düzenlenebilir
UPDATE `videos` SET `can_edit_after_admin` = 1 WHERE `can_edit_after_admin` IS NULL;

SELECT 'Migration başarıyla tamamlandı!' as status;
