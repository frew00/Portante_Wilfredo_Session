<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security/SessionManager.php';

$sessionManager = SessionManager::getInstance();

// CSRF protection: require token
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || !$sessionManager->validateCsrfToken($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = filter_input(INPUT_POST, "firstName", FILTER_SANITIZE_SPECIAL_CHARS);
    $lastName  = filter_input(INPUT_POST, "lastName", FILTER_SANITIZE_SPECIAL_CHARS);
    $address   = filter_input(INPUT_POST, "address", FILTER_SANITIZE_SPECIAL_CHARS);
    $email     = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
    $contact   = filter_input(INPUT_POST, "number", FILTER_SANITIZE_SPECIAL_CHARS);
    $password  = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);

    // Validate required fields
    if (!$firstName || !$lastName || !$email || !$password) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Check for existing email/contact
    $checkQuery = "SELECT email, contact FROM users WHERE email = ? OR contact = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $checkQuery);
    if ($stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }
    mysqli_stmt_bind_param($stmt, "ss", $email, $contact);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if (!empty($row['email']) && $row['email'] === $email) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit;
        }
        if (!empty($row['contact']) && $row['contact'] === $contact) {
            echo json_encode(['success' => false, 'message' => 'Contact number already registered']);
            exit;
        }
    }

    // Hash password (use default algorithm)
    $hashedPass = password_hash($password, PASSWORD_DEFAULT);

    // Use prepared statements for insertion and explicit columns (first_name, last_name)
    $insertUser = "INSERT INTO users (first_name, last_name, email, password, address, contact) VALUES (?, ?, ?, ?, ?, ?)";
    $insertStmt = mysqli_prepare($conn, $insertUser);
    if ($insertStmt === false) {
        echo json_encode(['success' => false, 'message' => 'Database error (prepare)']);
        exit;
    }
    mysqli_stmt_bind_param($insertStmt, "ssssss", $firstName, $lastName, $email, $hashedPass, $address, $contact);

    if (mysqli_stmt_execute($insertStmt)) {
        echo json_encode(['success' => true, 'message' => 'Account created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating account']);
    }

    mysqli_stmt_close($insertStmt);
    mysqli_close($conn);
}
