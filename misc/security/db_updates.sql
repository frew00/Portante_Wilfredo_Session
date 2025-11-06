ALTER TABLE users
ADD COLUMN password_reset_token VARCHAR(255) NULL,
ADD COLUMN password_reset_expires DATETIME NULL,
ADD COLUMN last_login DATETIME NULL,
ADD COLUMN failed_login_attempts INT DEFAULT 0,
ADD COLUMN account_locked DATETIME NULL,
ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user',
ADD INDEX idx_email (email),
ADD INDEX idx_password_reset_token (password_reset_token);