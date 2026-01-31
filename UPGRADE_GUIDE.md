# 🚀 Video Portal - Geliştirme Güncellemesi v2.0

## 📋 Yapılan Geliştirmeler

### 1. ✅ Admin Status Sistemi
- Adminler artık "aktif" veya "pasif" olarak işaretlenebilir
- Pasif adminler sisteme giriş yapamaz
- Admin panelinde kullanıcı durum yönetimi

### 2. 👤 Kullanıcı Profil Sistemi
- Her kullanıcı kendi profilini görüntüleyebilir
- Ad, soyad ve e-posta bilgilerini güncelleyebilir
- **Şifre değiştirme özelliği** - Her kullanıcı kendi şifresini değiştirebilir

### 3. 🎬 Video Status Güncellemesi
- "deleted" status kaldırıldı
- Artık sadece "active" ve "passive" durumları var
- "Sil" butonu videoyu pasife alır (soft delete)
- Sadece adminler kalıcı silme yapabilir

### 4. 👥 Kullanıcı Yönetimi (Sadece Admin)
- Yeni kullanıcı ekleme
- Kullanıcı düzenleme
- Kullanıcı pasife alma / aktif etme
- Kalıcı silme (sadece admin)

### 5. 📝 İsim Soyisim Sistemi
- Tüm adminlerin ad ve soyad bilgisi
- Video listelerinde ekleyen kişinin adı görünür
- Kullanıcı profil sayfaları

---

## 🔧 Kurulum Adımları

### Adım 1: Veritabanı Güncellemesi

**Mevcut veritabanınızı güncellemek için `migration.sql` dosyasını çalıştırın:**

```sql
-- phpMyAdmin'de SQL sekmesinden çalıştırın
USE video_portal;

-- 1. Admins tablosuna 'status' kolonu ekle
ALTER TABLE `admins` 
ADD COLUMN `status` enum('active','passive') NOT NULL DEFAULT 'active' AFTER `role`;

-- 2. Admins tablosuna status indexi ekle
ALTER TABLE `admins`
ADD KEY `idx_status` (`status`);

-- 3. Videos tablosundaki 'deleted' durumunu kaldır
UPDATE `videos` SET `status` = 'passive' WHERE `status` = 'deleted';

ALTER TABLE `videos` 
MODIFY COLUMN `status` enum('active','passive') DEFAULT 'active';

-- 4. Tüm mevcut adminleri 'active' yap
UPDATE `admins` SET `status` = 'active';
```

### Adım 2: Dosyaları Güncelleme

Aşağıdaki dosyaları projenize kopyalayın:

#### 📁 Model Dosyaları
- ✅ `app/models/Admin.php` (güncellenmiş)
- ✅ `app/models/Video.php` (güncellenmiş)

#### 📁 Controller Dosyaları
- ✅ `app/controllers/AdminController.php` (YENİ - kullanıcı yönetimi)
- ✅ `app/controllers/VideoController.php` (güncellenmiş)

#### 📁 View Dosyaları

**Kullanıcı Yönetimi:**
- ✅ `app/views/admin/admins/index.php` (YENİ)
- ✅ `app/views/admin/admins/create.php` (YENİ)
- ✅ `app/views/admin/admins/edit.php` (YENİ)

**Profil Sayfaları:**
- ✅ `app/views/admin/profile/view.php` (YENİ)
- ✅ `app/views/admin/profile/edit.php` (YENİ)
- ✅ `app/views/admin/profile/change-password.php` (YENİ)

**Güncellenmiş Sayfalar:**
- ✅ `app/views/admin/_header.php` (güncellenmiş - profil dropdown eklendi)
- ✅ `app/views/admin/videos/index.php` (güncellenmiş - status gösterimi)

#### 📁 Routes
- ✅ `public/index.php` (güncellenmiş - yeni rotalar eklendi)

---

## 🎯 Yeni Özellikler Kullanımı

### 1. Kullanıcı Yönetimi (Admin)

**Erişim:** Admin Paneli → Kullanıcılar

- ➕ Yeni kullanıcı ekle
- ✏️ Kullanıcı bilgilerini düzenle
- 🔄 Aktif/Pasif durumunu değiştir
- 🗑️ Kalıcı sil

### 2. Profil Yönetimi (Tüm Kullanıcılar)

**Erişim:** Admin Header → Kullanıcı Adınız → Profilim

- 📝 Ad, soyad, e-posta güncelleme
- 🔒 Şifre değiştirme
- 👤 Profil bilgilerini görüntüleme

### 3. Video Durum Yönetimi

**Artık daha basit:**
- "Sil" butonu → Videoyu pasife alır
- "Pasif Yap / Aktif Yap" → Durumu değiştirir
- "Kalıcı Sil" → Sadece admin (veritabanından siler)

---

## 📊 Veritabanı Değişiklikleri Özet

### `admins` Tablosu
```sql
-- YENİ KOLON
`status` enum('active','passive') NOT NULL DEFAULT 'active'

-- YENİ INDEX
KEY `idx_status` (`status`)
```

### `videos` Tablosu
```sql
-- DEĞİŞTİRİLEN KOLON
`status` enum('active','passive') DEFAULT 'active'
-- (Önceden: enum('active','passive','deleted'))
```

---

## 🔐 Güvenlik İyileştirmeleri

1. ✅ Pasif kullanıcılar giriş yapamaz
2. ✅ Şifre değiştirme için mevcut şifre kontrolü
3. ✅ E-posta benzersizlik kontrolü
4. ✅ Kullanıcı kendi kendini silemez/pasife alamaz
5. ✅ Editor sadece kendi videolarını yönetebilir

---

## 🎨 Yeni URL'ler

### Profil İşlemleri (Herkes)
- `/admin/profile` - Profili görüntüle
- `/admin/profile/edit` - Profili düzenle
- `/admin/profile/change-password` - Şifre değiştir

### Kullanıcı Yönetimi (Sadece Admin)
- `/admin/users` - Kullanıcı listesi
- `/admin/users/create` - Yeni kullanıcı ekle
- `/admin/users/edit/{id}` - Kullanıcı düzenle
- `/admin/users/toggle/{id}` - Aktif/Pasif değiştir
- `/admin/users/permanent-delete/{id}` - Kalıcı sil

### Video İşlemleri (Güncellendi)
- `/admin/videos/delete/{id}` - Pasife al
- `/admin/videos/toggle/{id}` - Aktif/Pasif değiştir
- `/admin/videos/permanent-delete/{id}` - Kalıcı sil (sadece admin)

---

## 🧪 Test Senaryoları

### Test 1: Şifre Değiştirme
1. Admin paneline giriş yapın
2. Sağ üstteki kullanıcı adınıza tıklayın
3. "Şifre Değiştir" seçeneğini seçin
4. Eski şifre, yeni şifre ve tekrar girin
5. Çıkış yapıp yeni şifre ile giriş yapın

### Test 2: Kullanıcı Pasife Alma
1. Admin olarak giriş yapın
2. Kullanıcılar sayfasına gidin
3. Bir editörü "Pasif Yap" ile pasife alın
4. Çıkış yapın
5. Pasif kullanıcı ile giriş yapmayı deneyin (BAŞARISIZ olmalı)

### Test 3: Video Pasife Alma
1. Bir video ekleyin
2. Video listesinde "Sil" butonuna tıklayın
3. Video listesinde "Pasif" olarak görünmeli
4. Public sitede video görünmemeli
5. "Aktif Yap" ile tekrar aktif edin

---

## ⚠️ Önemli Notlar

1. **Yedekleme:** Güncelleme öncesi mutlaka veritabanı yedeği alın!
2. **Migration:** `migration.sql` dosyasını sadece bir kez çalıştırın
3. **Şifre Hash:** Yeni kullanıcılar eklerken şifreler otomatik hash'lenir
4. **Admin Koruma:** Kendi hesabını pasife alamaz veya silemez
5. **Rol Kontrolü:** "Kullanıcılar" menüsü sadece admin rolünde görünür

---

## 📞 Destek ve Sorular

Herhangi bir sorun yaşarsanız:
1. `migration.sql` dosyasının doğru çalıştığından emin olun
2. PHP hata loglarını kontrol edin
3. Tarayıcı konsolunda JavaScript hataları olup olmadığına bakın

---

## ✨ Başarılı Güncelleme!

Artık sisteminiz:
- ✅ Kullanıcı profil yönetimi
- ✅ Şifre değiştirme
- ✅ Admin status kontrolü
- ✅ Gelişmiş video durum yönetimi
- ✅ İsim-soyisim sistemi

özelliklerine sahip! 🎉
