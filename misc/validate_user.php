<?php
require_once __DIR__ . '/db.php';

header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'method_not_allowed';
    exit;
}

if (!empty($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res && mysqli_num_rows($res) > 0) {
            echo 'email_exists';
        } else {
            echo 'ok';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo 'error';
    }
    exit;
}

if (!empty($_POST['contact'])) {
    $contact = preg_replace('/[^0-9]/', '', $_POST['contact']);
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE contact = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $contact);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res && mysqli_num_rows($res) > 0) {
            echo 'contact_exists';
        } else {
            echo 'ok';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo 'error';
    }
    exit;
}

echo 'ok';
