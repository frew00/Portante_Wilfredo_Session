<?php
// Include key files to detect parse errors. This script is safe to run and only includes files.
$files = [
    __DIR__ . '/SessionManager.php',
    __DIR__ . '/Authentication.php',
    __DIR__ . '/../login_function.php',
    __DIR__ . '/../signup_function.php',
    __DIR__ . '/../logout.php',
    __DIR__ . '/../cart_functions.php',
    __DIR__ . '/../validate_user.php',
    // Do not require test scripts here (they may execute DB actions)
];

foreach ($files as $f) {
    if (file_exists($f)) {
        require_once $f;
        echo "Included: $f\n";
    } else {
        echo "Missing: $f\n";
    }
}

echo "OK\n";
