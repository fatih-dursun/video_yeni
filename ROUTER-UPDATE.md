# Router Güncelleme Notu

public/index.php dosyasına şu route'u ekleyin:

```php
// Admin Category Routes (MEVCUT OLAN SATIRLARDAN SONRA)
$router->get('/admin/categories/toggle/{id}', 'CategoryController', 'toggleStatus');
```

Eklenecek yer:
```php
// Admin Category Routes
$router->get('/admin/categories', 'CategoryController', 'adminIndex');
$router->get('/admin/categories/create', 'CategoryController', 'create');
$router->post('/admin/categories/create', 'CategoryController', 'create');
$router->get('/admin/categories/edit/{id}', 'CategoryController', 'edit');
$router->post('/admin/categories/edit/{id}', 'CategoryController', 'edit');
$router->get('/admin/categories/toggle/{id}', 'CategoryController', 'toggleStatus'); // ← YENİ EKLE
$router->get('/admin/categories/delete/{id}', 'CategoryController', 'delete');
```
