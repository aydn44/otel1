<?php
namespace App\Lib;

class InquiryRepository
{
    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function createInquiry(array $data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO inquiries (name, email, country_code, phone, subject, message, type, source_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['name'],
            $data['email'],
            $data['country_code'],
            $data['phone'],
            $data['subject'],
            $data['message'],
            $data['type'],
            $data['source_id'] ?? null
        ]);
    }

    public function getAllInquiries() {
        $stmt = $this->pdo->query("SELECT * FROM inquiries ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function getInquiryById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM inquiries WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function markAsRead($id) {
        $stmt = $this->pdo->prepare("UPDATE inquiries SET is_read = TRUE WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function deleteInquiry($id) {
        $stmt = $this->pdo->prepare("DELETE FROM inquiries WHERE id = ?");
        return $stmt->execute([$id]);
    }
}