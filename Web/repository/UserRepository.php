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

    public function updateBalance(int $userId, float $amount): bool {
        // ĐÃ SỬA: Thay 'users' bằng 'customer' và 'id' bằng 'user_id'
        $sql = "UPDATE customer SET balance = balance - :amount WHERE user_id = :userId AND balance >= :amount"; 
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':amount', $amount);
        $stmt->bindValue(':userId', $userId);
        
        $stmt->execute();
        
        return $stmt->rowCount() > 0; 
    }

    public function getBalance($userId) {
        $stmt = $this->pdo->prepare('SELECT balance FROM customer WHERE user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    // Khóa row tài khoản người nộp tiền để tránh conflict khi xác nhận giao dịch
    public function lockForUpdate($userId) {
        $stmt = $this->pdo->prepare('SELECT user_id FROM customer WHERE user_id = ? FOR UPDATE');
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
}
?>