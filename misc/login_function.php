<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security/SessionManager.php';
require_once __DIR__ . '/security/Authentication.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verify CSRF token
    $sessionManager = SessionManager::getInstance();
    if (!isset($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Missing CSRF token']);
        exit;
    }
    if (!$sessionManager->validateCsrfToken($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }

    $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in both fields']);
        exit;
    }

    $auth = new Authentication($conn);
    $result = $auth->login($email, $password);
    
    echo json_encode($result);
    exit;
}
