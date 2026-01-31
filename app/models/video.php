<?php
require_once __DIR__ . '/../../core/Model.php';

class Video extends Model {
    protected $table = 'videos';

    public function getAllActive($limit = null) {
        $sql = "
            SELECT v.*, c.name as category_name, c.slug as category_slug,
                   c.background_color, c.text_color
            FROM videos v
            INNER JOIN categories c ON v.category_id = c.id
            WHERE v.status = 'active' AND c.status = 'active'
            ORDER BY v.created_at DESC
        ";
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        return $this->db->fetchAll($sql);
    }

    public function getFeatured($limit = 3) {
        $sql = "
            SELECT v.*, c.name as category_name, c.slug as category_slug,
                   c.background_color, c.text_color
            FROM videos v
            INNER JOIN categories c ON v.category_id = c.id
            WHERE v.status = 'active' AND v.is_featured = 1 AND c.status = 'active'
            ORDER BY v.created_at DESC
            LIMIT {$limit}
        ";
        return $this->db->fetchAll($sql);
    }

    public function getBySlug($slug) {
        $sql = "
            SELECT v.*, c.name as category_name, c.slug as category_slug,
                   c.background_color, c.text_color
            FROM videos v
            INNER JOIN categories c ON v.category_id = c.id
            WHERE v.slug = ? AND v.status = 'active' AND c.status = 'active'
            LIMIT 1
        ";
        return $this->db->fetch($sql, [$slug]);
    }

    public function getByCategory($categoryId, $limit = null) {
        $sql = "
            SELECT v.*, c.name as category_name, c.slug as category_slug,
                   c.background_color, c.text_color
            FROM videos v
            INNER JOIN categories c ON v.category_id = c.id
            WHERE v.category_id = ? AND v.status = 'active' AND c.status = 'active'
            ORDER BY 
                CASE WHEN v.sort_order IS NULL THEN 1 ELSE 0 END,
                v.sort_order ASC,
                v.created_at DESC
        ";
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        return $this->db->fetchAll($sql, [$categoryId]);
    }

    public function search($query, $limit = 5) {
        $searchTerm = "%{$query}%";
        $sql = "
            SELECT v.*, c.name as category_name, c.slug as category_slug,
                   c.background_color, c.text_color
            FROM videos v
            INNER JOIN categories c ON v.category_id = c.id
            WHERE v.status = 'active' AND c.status = 'active'
            AND (v.title LIKE ? OR v.description LIKE ?)
            ORDER BY v.created_at DESC
            LIMIT {$limit}
        ";
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm]);
    }

    public function incrementView($id) {
        $sql = "UPDATE videos SET view_count = view_count + 1 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function getAllForAdmin() {
        $sql = "
            SELECT v.*, 
                   c.name as category_name, 
                   creator.username as created_by_username,
                   creator.first_name as creator_first_name, 
                   creator.last_name as creator_last_name,
                   status_changer.role as status_changer_role,
                   status_changer.username as status_changer_username
            FROM videos v
            INNER JOIN categories c ON v.category_id = c.id
            LEFT JOIN admins creator ON v.created_by = creator.id
            LEFT JOIN admins status_changer ON v.last_status_changed_by = status_changer.id
            ORDER BY v.created_at DESC
        ";
        return $this->db->fetchAll($sql);
    }

    public function getByCreatorForAdmin($creatorId) {
        $sql = "
            SELECT v.*, 
                   c.name as category_name, 
                   creator.username as created_by_username,
                   creator.first_name as creator_first_name, 
                   creator.last_name as creator_last_name,
                   status_changer.role as status_changer_role,
                   status_changer.username as status_changer_username
            FROM videos v
            INNER JOIN categories c ON v.category_id = c.id
            LEFT JOIN admins creator ON v.created_by = creator.id
            LEFT JOIN admins status_changer ON v.last_status_changed_by = status_changer.id
            WHERE v.created_by = ?
            ORDER BY v.created_at DESC
        ";
        return $this->db->fetchAll($sql, [$creatorId]);
    }

    public function getAvailableSortOrders($categoryId, $excludeVideoId = null) {
        // Kategorideki kullanılmış sıraları bul (mevcut video HARİÇ)
        $sql = "SELECT sort_order FROM videos WHERE category_id = ? AND sort_order IS NOT NULL";
        $params = [$categoryId];
        
        if ($excludeVideoId) {
            $sql .= " AND id != ?";
            $params[] = $excludeVideoId;
        }
        
        $used = $this->db->fetchAll($sql, $params);
        $usedOrders = array_column($used, 'sort_order');
        
        // Mevcut videonun sırasını al (varsa dahil et)
        $currentOrder = null;
        if ($excludeVideoId) {
            $currentVideo = $this->find($excludeVideoId);
            $currentOrder = $currentVideo['sort_order'] ?? null;
        }
        
        // 1-50 arası TÜM BOŞ sıraları döndür + mevcut sırayı dahil et
        $available = [];
        for ($i = 1; $i <= 50; $i++) {
            if (!in_array($i, $usedOrders) || $i == $currentOrder) {
                $available[] = $i;
            }
        }
        
        return $available;
    }

    public function getNextAvailableSortOrder($categoryId) {
        $available = $this->getAvailableSortOrders($categoryId);
        return !empty($available) ? $available[0] : null;
    }

    public function deactivate($id) {
        return $this->update($id, ['status' => 'passive']);
    }

    public function activate($id) {
        return $this->update($id, ['status' => 'active']);
    }

    public function toggleStatus($id) {
        $video = $this->find($id);
        if (!$video) {
            return false;
        }
        
        $newStatus = $video['status'] === 'active' ? 'passive' : 'active';
        return $this->update($id, ['status' => $newStatus]);
    }

    public function permanentDelete($id) {
        return $this->delete($id);
    }

    public function getByCreator($creatorId) {
        $sql = "
            SELECT v.*, c.name as category_name, c.slug as category_slug
            FROM videos v
            INNER JOIN categories c ON v.category_id = c.id
            WHERE v.created_by = ?
            ORDER BY v.created_at DESC
        ";
        return $this->db->fetchAll($sql, [$creatorId]);
    }

    public function getStatistics() {
        $sql = "
            SELECT 
                COUNT(*) as total_videos,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_videos,
                SUM(CASE WHEN status = 'passive' THEN 1 ELSE 0 END) as passive_videos,
                SUM(view_count) as total_views,
                SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured_videos
            FROM videos
        ";
        return $this->db->fetch($sql);
    }
}
