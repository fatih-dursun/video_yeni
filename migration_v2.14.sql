-- Migration v2.14: Categories status + Videos sort_order

-- 1. Categories tablosuna status ekle
ALTER TABLE categories 
ADD COLUMN status ENUM('active', 'passive') DEFAULT 'active' AFTER slug;

-- 2. Videos tablosuna sort_order ekle (kategori içinde sıralama)
ALTER TABLE videos 
ADD COLUMN sort_order INT DEFAULT NULL AFTER category_id,
ADD INDEX idx_sort_order (category_id, sort_order);

-- 3. Mevcut kategorileri aktif yap
UPDATE categories SET status = 'active' WHERE status IS NULL;

-- 4. Mevcut videoları sırasız bırak (NULL = sıraya girmemiş)
UPDATE videos SET sort_order = NULL WHERE sort_order IS NULL;
