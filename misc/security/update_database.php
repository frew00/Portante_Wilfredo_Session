<?php
require_once __DIR__ . '/../db.php';

$sql = "ALTER TABLE users
ADD COLUMN IF NOT EXISTS password_reset_token VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS password_reset_expires DATETIME NULL,
ADD COLUMN IF NOT EXISTS last_login DATETIME NULL,
ADD COLUMN IF NOT EXISTS failed_login_attempts INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS account_locked DATETIME NULL,
ADD COLUMN IF NOT EXISTS role ENUM('user', 'admin') DEFAULT 'user';

CREATE INDEX IF NOT EXISTS idx_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_password_reset_token ON users(password_reset_token);";

try {
    if (mysqli_multi_query($conn, $sql)) {
        do {
            if ($result = mysqli_store_result($conn)) {
                mysqli_free_result($result);
            }
        } while (mysqli_next_result($conn));
        echo "Database updated successfully\n";
    }
} catch (Exception $e) {
    echo "Error updating database: " . $e->getMessage() . "\n";
}

mysqli_close($conn);
?>