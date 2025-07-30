<?php
namespace App\Lib;

class EventRepository
{
    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAllEvents() {
        $stmt = $this->pdo->prepare("
            SELECT e.id, e.featured_image, e.is_published, et.title, et.description
            FROM events e
            JOIN event_translations et ON e.id = et.event_id
            WHERE et.language_code = 'tr'
            ORDER BY e.id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getAllPublishedEvents($lang = 'tr') {
        $stmt = $this->pdo->prepare("
            SELECT e.id, e.featured_image, et.title, et.description
            FROM events e
            JOIN event_translations et ON e.id = et.event_id
            WHERE e.is_published = 1 AND et.language_code = ?
            ORDER BY e.id DESC
        ");
        $stmt->execute([$lang]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getEventById($id) {
        $stmt = $this->pdo->prepare("
            SELECT e.id, e.featured_image, e.is_published, et.title, et.description
            FROM events e
            LEFT JOIN event_translations et ON e.id = et.event_id AND et.language_code = 'tr'
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createEvent(array $data) {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("INSERT INTO events (featured_image, is_published) VALUES (?, ?)");
            $stmt->execute([$data['featured_image'], $data['is_published']]);
            $eventId = $this->pdo->lastInsertId();

            $stmt_trans = $this->pdo->prepare("INSERT INTO event_translations (event_id, language_code, title, description) VALUES (?, 'tr', ?, ?)");
            $stmt_trans->execute([$eventId, $data['title'], $data['description']]);

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) { $this->pdo->rollBack(); return false; }
    }

    public function updateEvent($id, array $data) {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("UPDATE events SET featured_image = ?, is_published = ? WHERE id = ?");
            $stmt->execute([$data['featured_image'], $data['is_published'], $id]);

            $stmt_check = $this->pdo->prepare("SELECT event_id FROM event_translations WHERE event_id = ? AND language_code = 'tr'");
            $stmt_check->execute([$id]);
            if ($stmt_check->fetch()) {
                $stmt_trans = $this->pdo->prepare("UPDATE event_translations SET title = ?, description = ? WHERE event_id = ? AND language_code = 'tr'");
                $stmt_trans->execute([$data['title'], $data['description'], $id]);
            } else {
                $stmt_trans = $this->pdo->prepare("INSERT INTO event_translations (event_id, language_code, title, description) VALUES (?, 'tr', ?, ?)");
                $stmt_trans->execute([$id, $data['title'], $data['description']]);
            }

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) { $this->pdo->rollBack(); return false; }
    }

    public function deleteEvent($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM events WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\Exception $e) { return false; }
    }
}