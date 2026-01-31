# VideoController - Sort Order Eklemesi

## CREATE Metodunda:

```php
public function create() {
    $this->checkAuth();
    
    if ($this->isPost()) {
        // ... mevcut validation ...
        
        // Sort order al
        $sortOrder = null;
        if (!empty($_POST['sort_order']) && $_POST['sort_order'] !== 'auto') {
            $sortOrder = (int)$_POST['sort_order'];
        } else {
            // Otomatik: İlk boş sırayı al
            $sortOrder = $this->videoModel->getNextAvailableSortOrder($_POST['category_id']);
        }
        
        $videoId = $this->videoModel->create([
            // ... mevcut alanlar ...
            'sort_order' => $sortOrder, // EKLE
        ]);
        
        // ...
    }

    $categories = $this->categoryModel->all();
    
    // Seçili kategori için boş sıraları al (AJAX için)
    $availableOrders = [];
    
    $this->view('admin/videos/create', [
        'categories' => $categories,
        'availableOrders' => $availableOrders,
        'errors' => $errors ?? []
    ]);
}
```

## EDIT Metodunda:

```php
public function edit($id) {
    // ... mevcut kodlar ...
    
    if ($this->isPost()) {
        // Sort order güncelle
        $sortOrder = null;
        if (!empty($_POST['sort_order']) && $_POST['sort_order'] !== 'auto') {
            $sortOrder = (int)$_POST['sort_order'];
        } else if ($_POST['sort_order'] === 'auto') {
            $sortOrder = $this->videoModel->getNextAvailableSortOrder($_POST['category_id']);
        } else {
            $sortOrder = $video['sort_order']; // Mevcut sırayı koru
        }
        
        $data = [
            // ... mevcut alanlar ...
            'sort_order' => $sortOrder, // EKLE
        ];
        
        // ...
    }
    
    // Seçili kategori için boş sıraları al
    $availableOrders = $this->videoModel->getAvailableSortOrders(
        $video['category_id'], 
        $video['id']
    );
    
    $this->view('admin/videos/edit', [
        'video' => $video,
        'categories' => $categories,
        'availableOrders' => $availableOrders // EKLE
    ]);
}
```

## AJAX Endpoint (Opsiyonel - Dinamik Dropdown için):

```php
public function getAvailableOrders() {
    $this->checkAuth();
    
    $categoryId = $_GET['category_id'] ?? null;
    $videoId = $_GET['video_id'] ?? null;
    
    if (!$categoryId) {
        $this->json(['orders' => []]);
        return;
    }
    
    $orders = $this->videoModel->getAvailableSortOrders($categoryId, $videoId);
    $this->json(['orders' => $orders]);
}
```
