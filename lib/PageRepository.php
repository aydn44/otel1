<?php
namespace App\Lib;

class PageRepository
{
    private $pdo;
    public function __construct(\PDO $pdo) { $this->pdo = $pdo; }

    public function findPublishedBySlug($slug, $langCode = 'tr')
    {
        $sql = "SELECT pt.title, pt.content, p.background_type, p.background_value FROM pages p JOIN page_translations pt ON p.id = pt.page_id WHERE p.slug = ? AND pt.language_code = ? AND p.is_published = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$slug, $langCode]);
        return $stmt->fetch();
    }

    // getMenuPages() fonksiyonu güncellendi: sort_order eklendi
    public function getMenuPages($langCode = 'tr')
    {
        return $this->pdo->query("
            SELECT p.id, p.slug, p.is_published, p.sort_order, pt.title 
            FROM pages p 
            JOIN page_translations pt ON p.id = pt.page_id 
            WHERE p.is_published = 1 AND pt.language_code = 'tr' 
            ORDER BY p.sort_order ASC, p.id ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getHomepageSettings()
    {
        return $this->pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'homepage_%'")->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    public function getPageById($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.slug, p.is_published, p.sort_order, p.background_type, p.background_value, pt.title, pt.content 
            FROM pages p 
            LEFT JOIN page_translations pt ON p.id = pt.page_id AND pt.language_code = 'tr' 
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updatePage($id, array $data)
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                UPDATE pages 
                SET slug = ?, is_published = ?, sort_order = ?, background_type = ?, background_value = ? 
                WHERE id = ?
            ");
            $stmt->execute([
                $data['slug'], 
                $data['is_published'], 
                $data['sort_order'] ?? 0, 
                $data['background_type'], 
                $data['background_value'], 
                $id
            ]);
            
            $stmt_check = $this->pdo->prepare("SELECT page_id FROM page_translations WHERE page_id = ? AND language_code = 'tr'");
            $stmt_check->execute([$id]);
            if ($stmt_check->fetch()) {
                $stmt_trans = $this->pdo->prepare("UPDATE page_translations SET title = ?, content = ? WHERE page_id = ? AND language_code = 'tr'");
                $stmt_trans->execute([$data['title'], $data['content'], $id]);
            } else {
                $stmt_trans = $this->pdo->prepare("INSERT INTO page_translations (page_id, language_code, title, content) VALUES (?, 'tr', ?, ?)");
                $stmt_trans->execute([$id, $data['title'], $data['content']]);
            }
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) { $this->pdo->rollBack(); return false; }
    }
    
    public function createPage(array $data) {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO pages (slug, is_published, sort_order, background_type, background_value) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['slug'], 
                $data['is_published'], 
                $data['sort_order'] ?? 0, 
                $data['background_type'], 
                $data['background_value']
            ]);
            $pageId = $this->pdo->lastInsertId();

            $stmt_trans = $this->pdo->prepare("INSERT INTO page_translations (page_id, language_code, title, content) VALUES (?, 'tr', ?, ?)");
            $stmt_trans->execute([$pageId, $data['title'], $data['content']]);

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) { $this->pdo->rollBack(); return false; }
    }

    // Sayfa silme metodu eklendi
    public function deletePage($id)
    {
        $this->pdo->beginTransaction();
        try {
            // Önce sayfa çevirilerini sil
            $stmt = $this->pdo->prepare("DELETE FROM page_translations WHERE page_id = ?");
            $stmt->execute([$id]);
            
            // Sonra sayfayı sil
            $stmt = $this->pdo->prepare("DELETE FROM pages WHERE id = ?");
            $stmt->execute([$id]);
            
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}