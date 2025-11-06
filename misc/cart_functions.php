<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security/SessionManager.php';

$sessionManager = SessionManager::getInstance();
$usr_id = $_SESSION['user_id'] ?? 0;

// Add to cart function
if (isset($_GET['action']) && $_GET['action'] === 'add' && isset($_GET['id'])) {
    $id   = intval($_GET['id']);
    $size = $_GET['size'] ?? '';
    $qty  = intval($_GET['qty'] ?? 1);

    $query = "SELECT * FROM products WHERE id = $id LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        $prod_name = mysqli_real_escape_string($conn, $row['prod_name']);
        $unit_price = $row['price'];

        // Check if already in cart
        $checkQuery = "SELECT * FROM cart WHERE prod_name = '$prod_name' AND usr_id = $usr_id LIMIT 1";
        $checkResult = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($checkResult) > 0) {
            // Product already in cart → update quantity & price
            $cartItem = mysqli_fetch_assoc($checkResult);
            $newQty = $cartItem['quantity'] + $qty;
            $newPrice = $unit_price * $newQty;

            $updateQuery = "UPDATE cart 
                            SET quantity = $newQty, price = $newPrice 
                            WHERE prod_name = '$prod_name'";
            mysqli_query($conn, $updateQuery);
        } else {
            // Add new item
            $total_price = $unit_price * $qty;
            $insertQuery = "INSERT INTO cart (usr_id, prod_name, quantity, price, frame_size) 
                            VALUES ($usr_id, '$prod_name', $qty, '$total_price', '$size')";
            mysqli_query($conn, $insertQuery);
        }

        echo "<script>alert('{$prod_name} added to cart!');</script>";
    } else {
        echo "<script>alert('Product not found.');</script>";
    }
}

function getCartCount()
{
    global $conn;
    $usr_id = $_SESSION['user_id'] ?? 0;
    $query = "SELECT COUNT(*) AS counted FROM cart WHERE usr_id = $usr_id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return (int)$row['counted'];
}

function showCartItems()
{
    $usr_id = $_SESSION['user_id'] ?? 0;
    global $conn;
    $counted = getCartCount();

    //Fallback when cart is Empty.
    if ($counted == 0) {
        $html = <<<HTML
            <h1 class="h1 text-center">You have no items in your cart!</h1>
            <br>
            <img class="mx-auto d-block" src="img/pageimg/empty-cart.png" alt="no items" height=100>
        HTML;
        echo $html;
    } else {
        $query = "SELECT * FROM cart WHERE usr_id = $usr_id";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $prod_name = $row['prod_name'];
            $quantity = $row['quantity'];
            $price = $row['price'];
            $frame_size = $row['frame_size'];

            //GET IMG & PER PRICE
            $imageQuery = "SELECT * FROM products WHERE prod_name = '$prod_name'";
            $imageRes = mysqli_query($conn, $imageQuery);
            $imageRow = mysqli_fetch_assoc($imageRes);
            $image = $imageRow['img'];
            $category = $imageRow['category'];
            $perPrice = $imageRow['price'];

            $link = "img/products/$category/$image";

            $html = <<<HTML
                <div class="list-group-item d-flex align-items-center" data-unit="$perPrice">
                    <img src="$link.jpg" alt="$prod_name" style="width:64px;height:64px;object-fit:cover;border-radius:6px;margin-right:12px">
                    <div class="flex-fill">
                        <div class="fw-bold">$prod_name</div>
                        <div>Qty: <span class="qty">$quantity</span> &middot; ₱$perPrice</div>
                    </div>
                    <div>
                        <div class="text-end">₱<span class="item-total">$price</span></div>
                        <div class="btn-group mt-2 d-flex justify-content-end gap-2" role="group">
                            <button class="btn btn-sm btn-outline-secondary decrease">−</button>
                            <button class="btn btn-sm btn-outline-secondary increase">+</button>
                            <form action="cart.php" method="post" class="d-inline m-0 p-0">
                                <input type="hidden" name="cart_item" value="$prod_name">
                                <button type="submit" class="btn btn-sm btn-outline-danger remove">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
                HTML;


            echo $html;
        }
    }
}

function cartUtils()
{
    $html = <<<HTML
            <div id="cart-" class="d-flex justify-content-between align-items-center">
                <div class="h4">Total: ₱<span id="cart-page-total">0.00</span></div>
                    <div class="d-flex justify-content-end gap-2">
                        <form action="cart.php" method="post" class="d-inline">
                            <input type="hidden" name="clear-cart" value="1">
                            <button type="submit" class="btn btn-secondary">Clear Cart</button>
                        </form>

                        <form action="cart.php" method="post" class="d-inline">
                            <input type="hidden" name="check-out" value="1">
                            <button type="submit" class="btn btn-primary">Checkout</button>
                        </form>
                    </div>
            </div>
    HTML;

    echo $html;
}

//Clear Cart
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['clear-cart'])) {
    $cart_item = filter_input(INPUT_POST, 'clear-cart', FILTER_SANITIZE_SPECIAL_CHARS);
    $delete_query = "DELETE FROM cart WHERE usr_id = $usr_id"; //temporary
    if (mysqli_query($conn, $delete_query)) {
        echo "<script>alert('Your Cart has been cleared!');</script>";
        // Optional: refresh the page to update the cart
        echo "<script>window.location.href = 'cart.php';</script>";
    } else {
        echo "<script>alert('Failed to clear cart.');</script>";
    }
}

//Checkout
//Clear Cart
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['check-out'])) {
    $cart_item = filter_input(INPUT_POST, 'check-out', FILTER_SANITIZE_SPECIAL_CHARS);
    $delete_query = "DELETE FROM cart WHERE usr_id = $usr_id"; //temporary
    if (mysqli_query($conn, $delete_query)) {
        echo "<script>alert('Thank you for shopping with us!');</script>";
        // Optional: refresh the page to update the cart
        echo "<script>window.location.href = 'index.php';</script>";
    } else {
        echo "<script>alert('Failed to Checkout.');</script>";
    }
}

//Remove from Cart
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cart_item'])) {
    $cart_item = filter_input(INPUT_POST, 'cart_item', FILTER_SANITIZE_SPECIAL_CHARS);
    $delete_query = "DELETE FROM cart WHERE prod_name = '$cart_item' and usr_id = $usr_id LIMIT 1";
    if (mysqli_query($conn, $delete_query)) {
        echo "<script>alert('{$cart_item} removed from cart!');</script>";
        // Optional: refresh the page to update the cart
        echo "<script>window.location.href = 'cart.php';</script>";
    } else {
        echo "<script>alert('Failed to remove item.');</script>";
    }
}
