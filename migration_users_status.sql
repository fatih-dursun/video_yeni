-- admins tablosuna status sütunu ekle
-- Pasif kullanıcılar giriş yapamaz

ALTER TABLE admins 
ADD COLUMN status ENUM('active', 'passive') DEFAULT 'active' 
AFTER role;

-- Mevcut tüm kullanıcıları aktif yap
UPDATE admins SET status = 'active' WHERE status IS NULL;
