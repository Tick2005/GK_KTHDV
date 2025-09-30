<?php
require_once __DIR__ . '/../database/db.php';

class UserRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findByUsername($username) {
        $stmt = $this->pdo->prepare('SELECT * FROM customer WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

     public function findById($userId) {
        $stmt = $this->pdo->prepare(
            'SELECT user_id, username, full_name, email, phone, balance, user_icon 
             FROM customer WHERE user_id = ?'
        );
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function updateProfile($userId, $username, $fullName, $email, $phone) {
        $stmt = $this->pdo->prepare(
            'UPDATE customer 
             SET username = ?, full_name = ?, email = ?, phone = ? 
             WHERE user_id = ?'
        );
        return $stmt->execute([$username, $fullName, $email, $phone, $userId]);
    }

    public function getPassword($userId) {
        $stmt = $this->pdo->prepare('SELECT password_hash FROM customer WHERE user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

   public function updatePassword($userId, $newHash) {
        $stmt = $this->pdo->prepare("UPDATE customer SET password_hash = :hash WHERE user_id = :id");
        $stmt->execute(['hash' => $newHash, 'id' => $userId]);
    }


    public function updateIcon($userId, $icon) {
        $stmt = $this->pdo->prepare('UPDATE customer SET user_icon = ? WHERE user_id = ?');
        return $stmt->execute([$icon, $userId]);
    }

    public function updateBalance($userId, $amount, $operation = '-') {
        $operator = $operation === '+' ? '+' : '-';
        $stmt = $this->pdo->prepare("UPDATE customer SET balance = balance $operator ? WHERE user_id = ?");
        return $stmt->execute([$amount, $userId]);
    }

    public function getBalance($userId) {
        $stmt = $this->pdo->prepare('SELECT balance FROM customer WHERE user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
}
?>