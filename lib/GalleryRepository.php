<?php
namespace App\Lib;

class GalleryRepository
{
    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    // ======================================================================
    // === KATEGORİ FONKSİYONLARI (Doğru ve Tam Hali) ===
    // ======================================================================
    public function getAllCategories($lang = 'tr') {
        $stmt = $this->pdo->prepare("SELECT c.*, ct.name FROM gallery_categories c JOIN gallery_category_translations ct ON c.id = ct.category_id WHERE ct.language_code = ? ORDER BY c.sort_order ASC");
        $stmt->execute([$lang]);
        return $stmt->fetchAll();
    }
    
    public function getCategoryById($id, $lang = 'tr') {
        $stmt = $this->pdo->prepare("SELECT c.*, ct.name FROM gallery_categories c JOIN gallery_category_translations ct ON c.id = ct.category_id WHERE c.id = ? AND ct.language_code = ?");
        $stmt->execute([$id, $lang]);
        return $stmt->fetch();
    }

    public function createCategory(array $data) {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("INSERT INTO gallery_categories (sort_order, is_published) VALUES (?, ?)");
            $stmt->execute([$data['sort_order'], $data['is_published']]);
            $categoryId = $this->pdo->lastInsertId();
            $stmt_trans = $this->pdo->prepare("INSERT INTO gallery_category_translations (category_id, language_code, name) VALUES (?, 'tr', ?)");
            $stmt_trans->execute([$categoryId, $data['name']]);
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) { $this->pdo->rollBack(); return false; }
    }

    public function updateCategory($id, array $data) {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("UPDATE gallery_categories SET sort_order = ?, is_published = ? WHERE id = ?");
            $stmt->execute([$data['sort_order'], $data['is_published'], $id]);
            $stmt_trans = $this->pdo->prepare("UPDATE gallery_category_translations SET name = ? WHERE category_id = ? AND language_code = 'tr'");
            $stmt_trans->execute([$data['name'], $id]);
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) { $this->pdo->rollBack(); return false; }
    }

    public function deleteCategory($id) {
        $stmt = $this->pdo->prepare("DELETE FROM gallery_categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ======================================================================
    // === RESİM FONKSİYONLARI (Doğru ve Tam Hali) ===
    // ======================================================================

    public function getAllGalleryImages($onlyPublished = false) {
        $sql = "
            SELECT 
                gi.id, 
                gi.image_path, 
                gi.sort_order, 
                gi.is_published, 
                gi.category_id, -- // DÜZELTME: EKSİK OLAN SATIR BU. FİLTRELEME İÇİN GEREKLİ.
                git.title, 
                gct.name as category_name
            FROM gallery_images gi
            LEFT JOIN gallery_image_translations git ON gi.id = git.image_id AND git.language_code = 'tr'
            LEFT JOIN gallery_categories gc ON gi.category_id = gc.id
            LEFT JOIN gallery_category_translations gct ON gc.id = gct.category_id AND gct.language_code = 'tr'
        ";
        if ($onlyPublished) {
            $sql .= " WHERE gi.is_published = 1";
        }
        $sql .= " ORDER BY gi.sort_order ASC, gi.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getGalleryImageById($id) {
        $stmt = $this->pdo->prepare("
            SELECT gi.id, gi.image_path, gi.sort_order, gi.is_published, gi.category_id, git.title
            FROM gallery_images gi
            LEFT JOIN gallery_image_translations git ON gi.id = git.image_id AND git.language_code = 'tr'
            WHERE gi.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createGalleryImage(array $data) {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("INSERT INTO gallery_images (category_id, image_path, sort_order, is_published) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['category_id'], $data['image_path'], $data['sort_order'], $data['is_published']]);
            $imageId = $this->pdo->lastInsertId();
            $stmt_trans = $this->pdo->prepare("INSERT INTO gallery_image_translations (image_id, language_code, title) VALUES (?, 'tr', ?)");
            $stmt_trans->execute([$imageId, $data['title']]);
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) { $this->pdo->rollBack(); return false; }
    }

    public function updateGalleryImage($id, array $data) {
        $this->pdo->beginTransaction();
        try {
            $sql_parts = [];
            $params = [];
            if (isset($data['category_id'])) { $sql_parts[] = "category_id = ?"; $params[] = $data['category_id']; }
            if (isset($data['sort_order'])) { $sql_parts[] = "sort_order = ?"; $params[] = $data['sort_order']; }
            if (isset($data['is_published'])) { $sql_parts[] = "is_published = ?"; $params[] = $data['is_published']; }
            if (isset($data['image_path'])) { $sql_parts[] = "image_path = ?"; $params[] = $data['image_path']; }
            
            if (!empty($sql_parts)) {
                $params[] = $id;
                $stmt = $this->pdo->prepare("UPDATE gallery_images SET " . implode(", ", $sql_parts) . " WHERE id = ?");
                $stmt->execute($params);
            }

            $stmt_check = $this->pdo->prepare("SELECT image_id FROM gallery_image_translations WHERE image_id = ? AND language_code = 'tr'");
            $stmt_check->execute([$id]);
            if ($stmt_check->fetch()) {
                $stmt_trans = $this->pdo->prepare("UPDATE gallery_image_translations SET title = ? WHERE image_id = ? AND language_code = 'tr'");
                $stmt_trans->execute([$data['title'], $id]);
            } else {
                $stmt_trans = $this->pdo->prepare("INSERT INTO gallery_image_translations (image_id, language_code, title) VALUES (?, 'tr', ?)");
                $stmt_trans->execute([$id, $data['title']]);
            }

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) { $this->pdo->rollBack(); return false; }
    }

    public function deleteGalleryImage($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM gallery_images WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\Exception $e) { return false; }
    }
}