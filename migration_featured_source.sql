-- Video tablosuna "featured_source" kolonu ekle
-- Bu kolon öne çıkan görselin nereden geldiğini belirler

USE video_portal;

ALTER TABLE `videos` 
ADD COLUMN `featured_source` ENUM('thumbnail', 'text') DEFAULT 'thumbnail' 
AFTER `featured_image_path`;

-- Mevcut videoları güncelle: thumbnail ve featured aynıysa 'thumbnail', değilse 'text'
UPDATE `videos` 
SET `featured_source` = 
    CASE 
        WHEN `thumbnail_path` = `featured_image_path` THEN 'thumbnail'
        ELSE 'text'
    END
WHERE `featured_source` IS NULL;

SELECT 'Migration başarıyla tamamlandı!' as status;
