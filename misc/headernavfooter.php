<?php
require_once __DIR__ . '/security/SessionManager.php';
$sessionManager = SessionManager::getInstance();


function navbarcall()
{
    //Checks if user is logged in:
    if (!isset($_SESSION['user_id']))
    {
        $logSignHTML = <<<HTML
            <li><a class="dropdown-item" href="#" id="loginButton">Login</a></li>
            <li><a class="dropdown-item" href="#" id="registerButton">Register</a></li>
        HTML;
        $cartHTML = <<<HTML
        HTML;
    }
    else
    {
        // Use stored user_name if available, otherwise fallback to email or a generic label
        $name = $_SESSION['user_name'] ?? ($_SESSION['user_email'] ?? 'User');
        $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $logSignHTML = <<<HTML
            <li><p class=" dropdown-item fw-bold">Hi, $safeName!</p></li>
            <hr>
            <li><a class="dropdown-item" href="#" id="logoutButton">Logout</a></li>
        HTML;
        $cartHTML = <<<HTML
            <li class="nav-item"><a class="nav-link" href="cart.php">🛒 Cart</a></li>
        HTML;
    }

    $html = <<<HTML
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="index.php">WEyewear</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <form class="search-nav d-flex ms-3 me-3" action="search.php" method="GET">
                    <input 
                        class="form-control me-2" 
                        type="search" 
                        name="search_query" 
                        placeholder="Search products..." 
                        aria-label="Search"
                        required
                    >
                    <button class="btn btn-outline-light" type="submit">Search</button>
                </form>

                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                        <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
                        <li class="nav-item"><a class="nav-link" href="contact-us.php">Contact Us</a></li>
                        $cartHTML

                        <!-- Account Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                                $logSignHTML
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    HTML;

    echo $html;
}


function footer()
{
    $html = <<<HTML
            <footer class="mt-auto bg-black text-center py-2 text-secondary small">
                © 2025 Weyewear | Designed for demo purposes
            </footer>
    HTML;

    echo $html;
}