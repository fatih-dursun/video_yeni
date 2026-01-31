# 📦 Video Portal v2.14 - Kurulum Talimatları

## 🚀 ADIM ADIM KURULUM

### 1️⃣ Veritabanı Migration
```bash
mysql -u root -p video_portal < migration_v2.14.sql
```

**ÖNEMLİ:** Şifre isterse MySQL şifrenizi girin

---

### 2️⃣ Dosyaları Kopyala

```
✅ app/models/Category.php
✅ app/models/Video.php
✅ app/controllers/CategoryController.php
✅ app/views/admin/categories/index.php
✅ app/views/admin/categories/edit.php
✅ app/views/admin/videos/index.php
```

---

### 3️⃣ CSS Güncellemesi

**Seçenek A:** style.css içinde `.featured-grid` bölümünü bul ve değiştir:

```css
/* ESKI */
.featured-grid {
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
}

/* YENİ */
.featured-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

@media (max-width: 1200px) {
    .featured-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .featured-grid {
        grid-template-columns: 1fr;
    }
}
```

**Seçenek B:** FEATURED-GRID-PATCH.css içeriğini kopyala-yapıştır

---

### 4️⃣ Router Güncellemesi

`public/index.php` dosyasını aç:

```php
// Admin Category Routes bölümünde EKLE:
$router->get('/admin/categories/toggle/{id}', 'CategoryController', 'toggleStatus');
```

**Tam Konum:**
```php
// Admin Category Routes
$router->get('/admin/categories', 'CategoryController', 'adminIndex');
$router->get('/admin/categories/create', 'CategoryController', 'create');
$router->post('/admin/categories/create', 'CategoryController', 'create');
$router->get('/admin/categories/edit/{id}', 'CategoryController', 'edit');
$router->post('/admin/categories/edit/{id}', 'CategoryController', 'edit');
$router->get('/admin/categories/toggle/{id}', 'CategoryController', 'toggleStatus'); // ← BURAYI EKLE
$router->get('/admin/categories/delete/{id}', 'CategoryController', 'delete');
```

---

### 5️⃣ Test

#### Kategori Durumu:
1. Admin → Kategoriler
2. Herhangi bir kategoride "Pasif Yap"
3. Ana sayfaya git → O kategori görünmüyor mu? ✅

#### Video Sıralama:
1. Admin → Yeni Video Ekle
2. "Sıra Numarası" dropdown görünüyor mu? ✅
3. Sıra seç veya otomatik bırak
4. Kaydet
5. Kategori sayfasına git → Sıralama doğru mu? ✅

#### Featured Grid:
1. Ana sayfa
2. Öne çıkan videolar 3 tane yan yana mı? ✅

---

## 🐛 Sorun Giderme

### Migration Hatası
```
Error: Table 'categories' already has column 'status'
```
**Çözüm:** Daha önce çalıştırılmış, sorun yok.

### Router 404
```
/admin/categories/toggle/1 → 404
```
**Çözüm:** Router'ı doğru güncelledin mi? public/index.php kontrol et.

### Featured Grid Çalışmıyor
**Çözüm:** 
1. CSS güncellemesini yaptın mı?
2. Cache temizle (Ctrl+F5)
3. Tarayıcı geliştirici araçları → Elements → .featured-grid CSS'ini kontrol et

---

## ✅ Kurulum Tamamlandı!

Tüm adımları tamamladıysan:
- ✅ Migration çalıştı
- ✅ Dosyalar kopyalandı
- ✅ CSS güncellendi
- ✅ Router güncellendi

**Test et ve keyfini çıkar! 🎉**
