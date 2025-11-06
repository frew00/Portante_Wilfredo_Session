<?php
require_once __DIR__ . '/SessionManager.php';

class Authentication {
    private $conn;
    private $sessionManager;
    private const HASH_ALGO = PASSWORD_ARGON2ID;
    private const HASH_OPTIONS = [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 2
    ];

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->sessionManager = SessionManager::getInstance();
    }

    public function login(string $email, string $password): array {
        if (!$this->sessionManager->checkRateLimit('login')) {
            return ['success' => false, 'message' => 'Too many login attempts. Please try again later.'];
        }

        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
    // include first_name/last_name so we can set a display name in session
    $stmt = $this->conn->prepare("SELECT id, email, password, role, first_name, last_name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

    $user = $result->fetch_assoc();
        
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        // Check if password needs rehashing
        if (password_needs_rehash($user['password'], self::HASH_ALGO, self::HASH_OPTIONS)) {
            $newHash = password_hash($password, self::HASH_ALGO, self::HASH_OPTIONS);
            $updateStmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateStmt->bind_param("si", $newHash, $user['id']);
            $updateStmt->execute();
        }

        $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $this->sessionManager->setUserData([
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'name' => $displayName
        ]);
        // Also set display name explicitly (backwards-compatible)
        $this->sessionManager->setUserDisplayName($displayName);
        
        $this->sessionManager->regenerateSession();

        return ['success' => true, 'message' => 'Login successful'];
    }

    public function register(array $userData): array {
        if (!$this->sessionManager->checkRateLimit('register')) {
            return ['success' => false, 'message' => 'Too many registration attempts. Please try again later.'];
        }

        // Validate input
        $email = filter_var($userData['email'], FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        // Check if email already exists
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        // Hash password (use ARGON2ID if available, otherwise default)
        if (defined('PASSWORD_ARGON2ID')) {
            $hashedPassword = password_hash($userData['password'], PASSWORD_ARGON2ID, self::HASH_OPTIONS);
        } else {
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
        }

        // Insert new user (explicit columns)
        $stmt = $this->conn->prepare("INSERT INTO users (first_name, last_name, email, password, address, contact, role) VALUES (?, ?, ?, ?, ?, ?, 'user')");
        if ($stmt === false) {
            return ['success' => false, 'message' => 'Registration failed (DB prepare)'];
        }

        $first = $userData['first_name'] ?? '';
        $last = $userData['last_name'] ?? '';
        $addr = $userData['address'] ?? '';
        $contact = $userData['contact'] ?? '';

        $stmt->bind_param("ssssss", $first, $last, $email, $hashedPassword, $addr, $contact);

        if (!$stmt->execute()) {
            return ['success' => false, 'message' => 'Registration failed (DB execute)'];
        }

        return ['success' => true, 'message' => 'Registration successful'];
    }

    public function logout(): void {
        $this->sessionManager->destroySession();
    }

    public function isLoggedIn(): bool {
        return $this->sessionManager->isLoggedIn();
    }
}