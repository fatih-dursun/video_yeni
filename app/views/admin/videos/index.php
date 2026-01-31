<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Yönetimi - Admin</title>
    <link rel="stylesheet" href="<?= asset('/css/admin.css') ?>">
</head>
<body>
    <?php include __DIR__ . '/../_header.php'; ?>
    
    <div class="admin-container">
        <div class="page-header">
            <h1>📹 Video Yönetimi</h1>
            <a href="<?= url('/admin/videos/create') ?>" class="btn btn-primary">+ Yeni Video Ekle</a>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Öne Çıkan Görsel</th>
                        <th>Başlık</th>
                        <th>Kategori</th>
                        <th>Durum</th>
                        <th>Sıra</th>
                        <th>Öne Çıkan</th>
                        <th>Görsel Kaynağı</th>
                        <th>Görüntülenme</th>
                        <th>Ekleyen</th>
                        <th>Tarih</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($videos as $video): ?>
                    <tr>
                        <td>
                            <img src="<?= upload_url($video['featured_image_path']) ?>" alt="" style="width: 80px; border-radius: 4px;">
                            <br>
                            <small style="color: #666; font-size: 11px;">
                                <?php if (($video['featured_source'] ?? 'thumbnail') === 'thumbnail'): ?>
                                    📸 Thumbnail
                                <?php else: ?>
                                    ✨ Otomatik
                                <?php endif; ?>
                            </small>
                        </td>
                        <td><?= htmlspecialchars($video['title']) ?></td>
                        <td><?= htmlspecialchars($video['category_name']) ?></td>
                        <td>
                            <span class="status-badge status-<?= $video['status'] ?>">
                                <?= $video['status'] === 'active' ? 'Aktif' : 'Pasif' ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($video['sort_order']): ?>
                                <span style="background: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 4px; font-weight: 600;">
                                    #<?= $video['sort_order'] ?>
                                </span>
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $video['is_featured'] ? '⭐ Evet' : 'Hayır' ?></td>
                        <td>
                            <?php if (($video['featured_source'] ?? 'thumbnail') === 'thumbnail'): ?>
                                <span style="background: #e3f2fd; color: #1976d2; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                    📸 Thumbnail
                                </span>
                            <?php else: ?>
                                <span style="background: #f3e5f5; color: #7b1fa2; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                    ✨ Otomatik
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?= number_format($video['view_count']) ?></td>
                        <td><?= htmlspecialchars($video['created_by_username'] ?? 'Bilinmiyor') ?></td>
                        <td><?= date('d.m.Y', strtotime($video['created_at'])) ?></td>
                        <td class="action-buttons">
                            <?php 
                            $canEdit = ($_SESSION['admin_role'] === 'admin') || ($video['created_by'] == $_SESSION['admin_id']);
                            $isLocked = ($video['status'] === 'passive' && $video['can_edit_after_admin'] == 0);
                            ?>
                            <?php if ($canEdit && !$isLocked): ?>
                            <a href="<?= url('/admin/videos/edit/' . $video['id']) ?>" class="btn btn-sm btn-edit">Düzenle</a>
                            <a href="<?= url('/admin/videos/toggle/' . $video['id']) ?>" class="btn btn-sm btn-warning">
                                <?= $video['status'] === 'active' ? 'Pasif Yap' : 'Aktif Yap' ?>
                            </a>
                            <a href="<?= url('/admin/videos/delete/' . $video['id']) ?>" class="btn btn-sm btn-danger" 
                               onclick="return confirm('Bu videoyu pasife almak istediğinize emin misiniz?')">Sil</a>
                            <?php elseif ($isLocked && $_SESSION['admin_role'] === 'editor'): ?>
                            <span style="color: #dc3545; font-size: 13px;">🔒 Admin Kilidi</span>
                            <?php elseif (!$canEdit): ?>
                            <span style="color: #888;">Yetkiniz yok</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
