<?php
require_once __DIR__ . '/../db.php';

$sql = "ALTER TABLE users
MODIFY COLUMN id INT AUTO_INCREMENT,
CHANGE COLUMN name first_name VARCHAR(255) NOT NULL,
ADD COLUMN last_name VARCHAR(255) NOT NULL AFTER first_name,
MODIFY COLUMN email VARCHAR(255) NOT NULL,
MODIFY COLUMN password VARCHAR(255) NOT NULL,
MODIFY COLUMN address TEXT NOT NULL,
MODIFY COLUMN contact VARCHAR(20) NOT NULL;";

try {
    if (mysqli_query($conn, $sql)) {
        echo "Users table structure updated successfully\n";
    }
} catch (Exception $e) {
    echo "Error updating users table: " . $e->getMessage() . "\n";
    // If column already exists, it's fine
    if (!strpos($e->getMessage(), "Duplicate column name")) {
        throw $e;
    }
}

mysqli_close($conn);
?>