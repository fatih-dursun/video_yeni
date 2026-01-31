<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Video.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../helpers/ImageGenerator.php';

class VideoController extends Controller {
    private $videoModel;
    private $categoryModel;

    public function __construct() {
        session_start();
        $this->videoModel = new Video();
        $this->categoryModel = new Category();
    }

    public function show($slug) {
        $video = $this->videoModel->getBySlug($slug);
        
        if (!$video) {
            $this->redirect('/');
            return;
        }

        $this->videoModel->incrementView($video['id']);

        $relatedVideos = $this->videoModel->getByCategory($video['category_id'], 4);
        $categories = $this->categoryModel->getAllWithVideoCount();

        $this->view('public/video', [
            'video' => $video,
            'relatedVideos' => $relatedVideos,
            'categories' => $categories,
            'pageTitle' => $video['title']
        ]);
    }

    public function adminIndex() {
        $this->checkAuth();
        
        if ($this->isEditor()) {
            $videos = $this->videoModel->getByCreatorForAdmin($_SESSION['admin_id']);
        } else {
            $videos = $this->videoModel->getAllForAdmin();
        }
        
        $this->view('admin/videos/index', ['videos' => $videos]);
    }

    public function create() {
        $this->checkAuth();
        
        if ($this->isPost()) {
            $errors = $this->validateRequired([
                'title' => 'Video Başlığı',
                'category_id' => 'Kategori',
                'description' => 'Açıklama'
            ]);

            if (empty($errors)) {
                $videoPath = $this->uploadFile($_FILES['video'], 'videos', ['mp4', 'webm', 'ogg']);
                
                if (!$videoPath) {
                    $errors['file'] = 'Video yüklenemedi';
                } else {
                    $title = $_POST['title'];
                    $slug = $this->slugify($title);
                    $featuredText = !empty($_POST['featured_text']) ? $_POST['featured_text'] : $title;
                    $featuredSource = $_POST['featured_source'] ?? 'thumbnail';
                    
                    $category = $this->categoryModel->find($_POST['category_id']);
                    
                    $thumbnailPath = null;
                    if (!empty($_FILES['thumbnail']['name'])) {
                        $thumbnailPath = $this->uploadFile($_FILES['thumbnail'], 'thumbnails', ['jpg', 'jpeg', 'png']);
                    }
                    
                    if ($featuredSource === 'text' || !$thumbnailPath) {
                        $generator = new ImageGenerator();
                        $featuredImageName = uniqid() . '_' . time() . '.jpg';
                        $featuredImagePath = __DIR__ . '/../../public/uploads/featured/' . $featuredImageName;
                        
                        if (!is_dir(dirname($featuredImagePath))) {
                            mkdir(dirname($featuredImagePath), 0755, true);
                        }
                        
                        $generatedPath = $generator->generateFeaturedImage(
                            $featuredText,
                            $category['background_color'],
                            $category['text_color'],
                            $featuredImagePath
                        );
                        
                        if (!$thumbnailPath) {
                            $thumbnailPath = $generatedPath;
                        }
                        
                        $featuredImagePathFinal = $generatedPath;
                        $actualFeaturedSource = 'text';
                    } else {
                        $featuredImagePathFinal = $thumbnailPath;
                        $actualFeaturedSource = 'thumbnail';
                    }
                    
                    $sortOrder = null;
                    if (isset($_POST['sort_order']) && $_POST['sort_order'] !== 'auto') {
                        $sortOrder = (int)$_POST['sort_order'];
                    } else {
                        $sortOrder = $this->videoModel->getNextAvailableSortOrder($_POST['category_id']);
                    }

                    $videoId = $this->videoModel->create([
                        'title' => $title,
                        'slug' => $slug,
                        'description' => $_POST['description'],
                        'featured_text' => $featuredText,
                        'video_path' => $videoPath,
                        'thumbnail_path' => $thumbnailPath,
                        'featured_image_path' => $featuredImagePathFinal,
                        'featured_source' => $actualFeaturedSource,
                        'category_id' => $_POST['category_id'],
                        'created_by' => $_SESSION['admin_id'],
                        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                        'status' => $_POST['status'] ?? 'active',
                        'sort_order' => $sortOrder
                    ]);

                    $this->redirect('/admin/videos');
                    return;
                }
            }
        }

        $categories = $this->categoryModel->all();
        $this->view('admin/videos/create', ['categories' => $categories, 'errors' => $errors ?? []]);
    }

    public function edit($id) {
        $this->checkAuth();
        $video = $this->videoModel->find($id);
        
        if (!$video) {
            $this->redirect('/admin/videos');
            return;
        }
        
        if ($this->isEditor() && $video['created_by'] != $_SESSION['admin_id']) {
            die('Bu videoyu düzenleme yetkiniz yok!');
        }

        if ($this->isPost()) {
            $data = [
                'title' => $_POST['title'],
                'slug' => $this->slugify($_POST['title']),
                'description' => $_POST['description'],
                'category_id' => $_POST['category_id'],
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'status' => $_POST['status']
            ];
            
            $sortOrder = null;
            if (isset($_POST['sort_order']) && $_POST['sort_order'] !== 'auto') {
                $sortOrder = (int)$_POST['sort_order'];
            } else {
                $sortOrder = $this->videoModel->getNextAvailableSortOrder($_POST['category_id']);
            }
            $data['sort_order'] = $sortOrder;

            if (!empty($_POST['featured_text']) && $_POST['featured_text'] !== $video['featured_text']) {
                $data['featured_text'] = $_POST['featured_text'];
            }
            
            $featuredSource = $_POST['featured_source'] ?? 'thumbnail';
            $data['featured_source'] = $featuredSource;
            
            $regenerateFeatured = false;
            if ($featuredSource === 'text' && $featuredSource !== ($video['featured_source'] ?? 'thumbnail')) {
                $regenerateFeatured = true;
            }
            
            if (!empty($_POST['featured_text']) && $_POST['featured_text'] !== $video['featured_text'] && $featuredSource === 'text') {
                $regenerateFeatured = true;
            }

            if (!empty($_FILES['video']['name'])) {
                $videoPath = $this->uploadFile($_FILES['video'], 'videos', ['mp4', 'webm', 'ogg']);
                if ($videoPath) $data['video_path'] = $videoPath;
            }

            $newThumbnail = false;
            if (!empty($_FILES['thumbnail']['name'])) {
                $thumbnailPath = $this->uploadFile($_FILES['thumbnail'], 'thumbnails', ['jpg', 'jpeg', 'png']);
                if ($thumbnailPath) {
                    $data['thumbnail_path'] = $thumbnailPath;
                    $newThumbnail = true;
                    
                    if ($featuredSource === 'thumbnail') {
                        $data['featured_image_path'] = $thumbnailPath;
                    }
                }
            }
            
            if ($regenerateFeatured) {
                $category = $this->categoryModel->find($_POST['category_id']);
                $generator = new ImageGenerator();
                $featuredImageName = uniqid() . '_' . time() . '.jpg';
                $featuredImagePath = __DIR__ . '/../../public/uploads/featured/' . $featuredImageName;
                
                $generatedPath = $generator->generateFeaturedImage(
                    $_POST['featured_text'] ?: $video['title'],
                    $category['background_color'],
                    $category['text_color'],
                    $featuredImagePath
                );
                
                $data['featured_image_path'] = $generatedPath;
            } elseif ($featuredSource === 'thumbnail' && !$newThumbnail) {
                $data['featured_image_path'] = $video['thumbnail_path'];
            }

            $this->videoModel->update($id, $data);
            $this->redirect('/admin/videos');
            return;
        }

        $categories = $this->categoryModel->all();
        
        // BURADA AVAILABLE ORDERS GÖNDERİLİYOR - SAYFA YÜKLENIRKEN
        $availableOrders = $this->videoModel->getAvailableSortOrders($video['category_id'], $video['id']);
        
        $this->view('admin/videos/edit', [
            'video' => $video,
            'categories' => $categories,
            'availableOrders' => $availableOrders  // ← ÖNEMLİ!
        ]);
    }

    public function delete($id) {
        $this->checkAuth();
        
        if ($this->isEditor()) {
            $video = $this->videoModel->find($id);
            if ($video['created_by'] != $_SESSION['admin_id']) {
                die('Bu videoyu silme yetkiniz yok!');
            }
        }
        
        $this->videoModel->update($id, ['status' => 'deleted']);
        $this->redirect('/admin/videos');
    }

    public function permanentDelete($id) {
        $this->checkAuth();
        $this->requireAdmin();
        $this->videoModel->delete($id);
        $this->redirect('/admin/videos');
    }

    public function toggleStatus($id) {
        $this->checkAuth();
        
        if ($this->isEditor()) {
            $video = $this->videoModel->find($id);
            if ($video['created_by'] != $_SESSION['admin_id']) {
                die('Bu videoyu düzenleme yetkiniz yok!');
            }
        }
        
        $video = $this->videoModel->find($id);
        
        $newStatus = $video['status'] === 'active' ? 'passive' : 'active';
        $this->videoModel->update($id, ['status' => $newStatus]);
        
        $this->redirect('/admin/videos');
    }

    private function checkAuth() {
        if (!isset($_SESSION['admin_id'])) {
            $this->redirect('/admin/login');
            exit;
        }
    }
}
