<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Video.php';
require_once __DIR__ . '/../models/Category.php';

class CategoryController extends Controller {
    private $videoModel;
    private $categoryModel;

    public function __construct() {
        session_start();
        $this->videoModel = new Video();
        $this->categoryModel = new Category();
    }

    public function show($slug) {
        $category = $this->categoryModel->getBySlug($slug);
        
        if (!$category) {
            $this->redirect('/');
            return;
        }

        $videos = $this->videoModel->getByCategory($category['id']);
        $categories = $this->categoryModel->getAllWithVideoCount();

        $this->view('public/category', [
            'category' => $category,
            'videos' => $videos,
            'categories' => $categories,
            'pageTitle' => $category['name']
        ]);
    }

    public function adminIndex() {
        $this->checkAuth();
        
        // Başarı/hata mesajları
        $success = $_SESSION['success_message'] ?? null;
        $error = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);
        
        $categories = $this->categoryModel->getAllWithVideoCount();
        
        $this->view('admin/categories/index', [
            'categories' => $categories,
            'success' => $success,
            'error' => $error
        ]);
    }

    public function create() {
        $this->checkAuth();
        $this->requireAdmin();
        
        if ($this->isPost()) {
            $errors = $this->validateRequired([
                'name' => 'Kategori Adı',
                'background_color' => 'Arka Plan Rengi',
                'text_color' => 'Yazı Rengi'
            ]);

            if (empty($errors)) {
                $data = [
                    'name' => $_POST['name'],
                    'slug' => $this->slugify($_POST['name']),
                    'background_color' => $_POST['background_color'],
                    'text_color' => $_POST['text_color'],
                    'created_by' => $_SESSION['admin_id'],
                    'status' => 'active'
                ];

                // Logo upload - TAM YOL
                if (!empty($_FILES['logo']['name'])) {
                    $logoPath = $this->uploadFile($_FILES['logo'], 'category-logos', ['png', 'jpg', 'jpeg']);
                    if ($logoPath) {
                        // BASE_PATH ekleyerek tam yol oluştur
                        $basePath = defined('BASE_PATH') ? BASE_PATH : '/video-portal/public';
                        $data['logo_path'] = $basePath . $logoPath;
                    } else {
                        $_SESSION['error_message'] = '❌ Logo yüklenemedi!';
                        $this->redirect('/admin/categories/create');
                        return;
                    }
                }

                $this->categoryModel->create($data);
                
                $_SESSION['success_message'] = '✅ Kategori başarıyla eklendi!';
                $this->redirect('/admin/categories');
                return;
            } else {
                $_SESSION['error_message'] = '❌ Lütfen tüm zorunlu alanları doldurun!';
            }
        }

        $this->view('admin/categories/create', [
            'errors' => $errors ?? [],
            'colorPresets' => Category::$colorPresets
        ]);
    }

    public function edit($id) {
        $this->checkAuth();
        $this->requireAdmin();
        
        $category = $this->categoryModel->find($id);
        
        if (!$category) {
            $_SESSION['error_message'] = '❌ Kategori bulunamadı!';
            $this->redirect('/admin/categories');
            return;
        }

        if ($this->isPost()) {
            $data = [
                'name' => $_POST['name'],
                'slug' => $this->slugify($_POST['name']),
                'background_color' => $_POST['background_color'],
                'text_color' => $_POST['text_color']
            ];

            // Yeni logo yüklendiyse - TAM YOL
            if (!empty($_FILES['logo']['name'])) {
                $logoPath = $this->uploadFile($_FILES['logo'], 'category-logos', ['png', 'jpg', 'jpeg']);
                if ($logoPath) {
                    $basePath = defined('BASE_PATH') ? BASE_PATH : '/video-portal/public';
                    $data['logo_path'] = $basePath . $logoPath;
                } else {
                    $_SESSION['error_message'] = '❌ Logo yüklenemedi!';
                    $this->redirect('/admin/categories/edit/' . $id);
                    return;
                }
            }

            // Logo silme checkbox'ı işaretliyse
            if (isset($_POST['remove_logo']) && $_POST['remove_logo'] == '1') {
                $data['logo_path'] = null;
            }

            $this->categoryModel->update($id, $data);
            
            $_SESSION['success_message'] = '✅ Kategori başarıyla güncellendi!';
            $this->redirect('/admin/categories');
            return;
        }

        $this->view('admin/categories/edit', [
            'category' => $category,
            'colorPresets' => Category::$colorPresets
        ]);
    }

    public function delete($id) {
        $this->checkAuth();
        $this->requireAdmin();
        
        $category = $this->categoryModel->find($id);
        
        if (!$category) {
            $_SESSION['error_message'] = '❌ Kategori bulunamadı!';
            $this->redirect('/admin/categories');
            return;
        }
        
        // Kategorideki video sayısını kontrol et
        $videos = $this->videoModel->getByCategory($id);
        if (!empty($videos)) {
            $_SESSION['error_message'] = '❌ Bu kategoride ' . count($videos) . ' video var! Önce videoları silin veya taşıyın.';
            $this->redirect('/admin/categories');
            return;
        }
        
        $this->categoryModel->delete($id);
        
        $_SESSION['success_message'] = '✅ Kategori başarıyla silindi!';
        $this->redirect('/admin/categories');
    }

    public function toggleStatus($id) {
        $this->checkAuth();
        $this->requireAdmin();
        
        $category = $this->categoryModel->find($id);
        
        if (!$category) {
            $_SESSION['error_message'] = '❌ Kategori bulunamadı!';
            $this->redirect('/admin/categories');
            return;
        }
        
        $newStatus = ($category['status'] ?? 'active') === 'active' ? 'passive' : 'active';
        $this->categoryModel->update($id, ['status' => $newStatus]);
        
        $statusText = $newStatus === 'active' ? 'aktif yapıldı' : 'pasife alındı';
        $_SESSION['success_message'] = "✅ Kategori {$statusText}!";
        
        $this->redirect('/admin/categories');
    }

    private function checkAuth() {
        if (!isset($_SESSION['admin_id'])) {
            $this->redirect('/admin/login');
            exit;
        }
    }
}
