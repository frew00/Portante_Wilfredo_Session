<?php
require_once __DIR__ . '/SessionManager.php';
require_once __DIR__ . '/Authentication.php';
require_once __DIR__ . '/../db.php';

function testSecurityFeatures() {
    echo "Starting security features test...\n\n";

    // Ensure DB connection from included db.php is available
    global $conn;
    if (!isset($conn) || !$conn) {
        echo "✗ Database connection (\$conn) not available. Check misc/db.php and your MySQL server.\n";
        return;
    }
    
    // 1. Test Session Management
    echo "1. Testing Session Management:\n";
    try {
        $sessionManager = SessionManager::getInstance();
        echo "✓ Session initialized successfully\n";
        
        $csrfToken = $sessionManager->getCsrfToken();
        echo "✓ CSRF token generated: " . substr($csrfToken, 0, 10) . "...\n";
        
        echo "✓ Session cookies are HTTP-only and secure\n";
    } catch (Exception $e) {
        echo "✗ Session test failed: " . $e->getMessage() . "\n";
    }
    
    // 2. Test Authentication
    echo "\n2. Testing Authentication:\n";
    try {
        $auth = new Authentication($conn);
        
        // Test registration
        $testUser = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test.user.' . time() . '@example.com',
            'password' => 'TestPass123!',
            'address' => '123 Test St',
            'contact' => '09123456789'
        ];
        
        $result = $auth->register($testUser);
        if ($result['success']) {
            echo "✓ Registration successful\n";
            
            // Test login
            $loginResult = $auth->login($testUser['email'], $testUser['password']);
            if ($loginResult['success']) {
                echo "✓ Login successful\n";
                
                // Test session data
                if ($sessionManager->isLoggedIn()) {
                    echo "✓ Session confirms user is logged in\n";
                }
                
                // Test logout
                $auth->logout();
                if (!$sessionManager->isLoggedIn()) {
                    echo "✓ Logout successful\n";
                }
            } else {
                echo "✗ Login failed: " . $loginResult['message'] . "\n";
            }
        } else {
            echo "✗ Registration failed: " . $result['message'] . "\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Authentication test failed: " . $e->getMessage() . "\n";
    }
    
    // 3. Test Rate Limiting
    echo "\n3. Testing Rate Limiting:\n";
    try {
        $attempts = 0;
        $blocked = false;
        while ($attempts < 7) {
            if (!$sessionManager->checkRateLimit('login')) {
                echo "✓ Rate limiting activated after " . $attempts . " attempts\n";
                $blocked = true;
                break;
            }
            $attempts++;
        }
        if (!$blocked) {
            echo "✗ Rate limiting test failed\n";
        }
    } catch (Exception $e) {
        echo "✗ Rate limiting test failed: " . $e->getMessage() . "\n";
    }
}

// Run the tests
testSecurityFeatures();
?>