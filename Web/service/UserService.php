<?php
require_once __DIR__ . '/../repository/UserRepository.php';

class UserService {
    private $userRepo;

    public function __construct($pdo) {
        $this->userRepo = new UserRepository($pdo);
    }

    public function login($username, $password) {
        $user = $this->userRepo->findByUsername($username);
        if (!$user || (!password_verify($password, $user['password_hash']))) {
            throw new Exception('Username or password is incorrect');
        }
        return $user;
    }


    public function getUserById($userId) {
        return $this->userRepo->findById($userId);
    }

    public function updateIcon($userId, $icon) {
        $allowedIcons = ['default.png', 'ava_a.png', 'ava_b.png', 'ava_c.png', 'ava_d.png', 'ava_e.png', 'ava_f.png', 'ava_g.png', 'ava_h.png'];
        if (!in_array($icon, $allowedIcons)) {
            throw new Exception('Invalid icon');
        }
        return $this->userRepo->updateIcon($userId, $icon);
    }

    public function updateProfile($userId, $username, $fullName, $email, $phone) {
        if (!preg_match('/^[a-zA-Z0-9_]{3,}$/', $username)) {
            throw new Exception('Username must be at least 3 characters and contain only letters, numbers, and underscores');
        }

        if (strlen(trim($fullName)) < 3) {
            throw new Exception('Full name must be at least 3 characters');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        if (!preg_match('/^[0-9]{10}$/', $phone)) {
            throw new Exception('Invalid phone number format');
        }


        // Thực hiện update
        return $this->userRepo->updateProfile($userId, $username, $fullName, $email, $phone);
    }

     public function updatePassword($userId, $oldPassword, $newPassword) {
        $currentHash = $this->userRepo->getPassword($userId);
        if (!$currentHash || !password_verify($oldPassword, $currentHash)) {
            throw new Exception("Old password is incorrect");
        }
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->userRepo->updatePassword($userId, $newHash);
    }
    public function getBalance($userId) {
        return $this->userRepo->getBalance($userId);
    }


}
?>