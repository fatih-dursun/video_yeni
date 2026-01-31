-- Kategorilere logo ekleme
-- Logo Sol Üst + Yazı Orta tasarımı için

ALTER TABLE categories 
ADD COLUMN logo_path VARCHAR(255) NULL 
AFTER text_color;

-- Not: Logo boyutu 150x150px olmalı (PNG, şeffaf arka plan önerili)
