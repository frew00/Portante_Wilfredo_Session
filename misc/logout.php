<?php
require_once __DIR__ . '/security/SessionManager.php';

$sessionManager = SessionManager::getInstance();
$sessionManager->destroySession();

// return a plain success message for AJAX
echo "success";
exit;