<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'database.php'; // Defines $conn (PDO connection)

// 1. Enforce authentication: check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';
$messageClass = 'success-box';
$tokenNumber = '';
$isSuccess = false;
$grandTotal = 0.00;

// 2. Handle POST Request (Form Submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? '';
    $cartDataJson = $_POST['cart_data'] ?? '[]';
    
    $cartItems = json_decode($cartDataJson, true);

    if ($name === '' || $phone === '' || $email === '' || $paymentMethod === '' || empty($cartItems)) {
        $message = 'Invalid form submission. Please make sure you have items in your cart and all fields are filled.';
        $messageClass = 'success-box error-box';
    } else {
        try {
            // Start Transaction
            $conn->beginTransaction();

            // 1. Calculate dynamic grand total
            foreach ($cartItems as $item) {
                $itemPrice = floatval($item['price']);
                $itemQuantity = intval($item['quantity']);
                $grandTotal += ($itemPrice * $itemQuantity);
            }

            // 2. Generate a unique, sequential token number
            $stmt = $conn->query("SELECT MAX(order_id) FROM orders");
            $maxId = $stmt->fetchColumn();
            $tokenNumber = 'QB' . (1000 + ($maxId ? $maxId + 1 : 1));

            // 3. Insert into orders table
            $orderStmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, token_number, status, order_date) VALUES (?, ?, ?, 'Pending', NOW())");
            $orderStmt->execute([$userId, $grandTotal, $tokenNumber]);
            $orderId = $conn->lastInsertId();

            // 4. Insert into order_items table
            foreach ($cartItems as $item) {
                $itemName = trim($item['name'] ?? '');
                $itemPrice = floatval($item['price'] ?? 0);
                $itemQuantity = intval($item['quantity'] ?? 0);
                $itemImage = trim($item['image'] ?? '');
                $itemDescription = trim($item['description'] ?? '');

                if ($itemName === '' || $itemQuantity <= 0) {
                    continue;
                }

                // Check if the food item exists in foods table by name
                $foodQuery = $conn->prepare("SELECT food_id FROM foods WHERE food_name = ?");
                $foodQuery->execute([$itemName]);
                $foodId = $foodQuery->fetchColumn();

                if (!$foodId) {
                    // Create the food dynamically
                    $insertFood = $conn->prepare("INSERT INTO foods (food_name, category, description, price, image) VALUES (?, 'General', ?, ?, ?)");
                    $insertFood->execute([$itemName, $itemDescription, $itemPrice, $itemImage]);
                    $foodId = $conn->lastInsertId();
                }

                // Insert into order_items
                $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, food_item_id, quantity, price) VALUES (?, ?, ?, ?)");
                $itemStmt->execute([$orderId, $foodId, $itemQuantity, $itemPrice]);
            }

            // 5. Insert into payments table
            $paymentStatus = ($paymentMethod === 'Cash') ? 'Pending' : 'Completed';
            $payStmt = $conn->prepare("INSERT INTO payments (order_id, payment_method, amount, transaction_status) VALUES (?, ?, ?, ?)");
            $payStmt->execute([$orderId, $paymentMethod, $grandTotal, $paymentStatus]);

            // Commit Transaction
            $conn->commit();

            $safeToken = htmlspecialchars($tokenNumber, ENT_QUOTES, 'UTF-8');
            $message = "Order Successful! Your Token Number is: <strong id='php-token'>{$safeToken}</strong>";
            $messageClass = 'success-box';
            $isSuccess = true;

        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Order Processing Failed: " . $e->getMessage();
            $messageClass = 'success-box error-box';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - QuickBite</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/cart.css">
</head>
<body class="checkout-page" data-user-id="<?php echo $_SESSION['user_id']; ?>">

<header class="site-header">
    <div class="header-inner">
        <div class="brand">
            <img src="logo1.png" class="logo" alt="QuickBite logo">
            <h1 class="site-title">QuickBite</h1>
        </div>

        <nav class="main-nav" aria-label="Main navigation">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="myorders.php">My Orders</a></li>
            </ul>
        </nav>

        <div class="header-actions">
            <a href="cart.php" class="cart-btn">Cart</a>
            <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
                <span class="user-welcome" style="margin-right: 15px; color: #fbf4f4; font-weight: bold;">
                    Hi, <?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?>
                </span>
                <a href="logout.php" class="login-link">Logout</a>
            <?php else: ?>
                <a href="login.html" class="login-link">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="checkout-shell" style="padding: 40px 20px; max-width: 1200px; margin: 0 auto;">
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <!-- Checkout Result Display -->
        <div class="checkout-title" style="text-align: center; margin-bottom: 30px;">
            <h1>Checkout Status</h1>
            <p>Your order details below</p>
        </div>

        <section class="checkout-card" id="checkout-status-card" 
                 data-success="<?php echo $isSuccess ? 'true' : 'false'; ?>"
                 data-token="<?php echo $tokenNumber; ?>"
                 style="background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; text-align: center;">
            
            <div class="<?php echo $messageClass; ?>" style="font-size: 1.2rem; margin-bottom: 20px; padding: 15px; border-radius: 5px;">
                <?php echo $message; ?>
            </div>
            
            <?php if ($isSuccess): ?>
                <p style="margin-top: 20px; color: #555;">
                    Please present this token at the counter to collect your order. You can track this order in your order history.
                </p>
            <?php endif; ?>

            <p class="register-note" style="margin-top: 30px; font-size: 1rem;">
                <a href="menu.php" style="color: #e67e22; text-decoration: none; font-weight: bold;">Back to Menu</a> | 
                <a href="myorders.php" style="color: #e67e22; text-decoration: none; font-weight: bold;">My Orders</a>
            </p>
        </section>

        <script>
        document.addEventListener("DOMContentLoaded", () => {
            const card = document.getElementById("checkout-status-card");
            if (!card) return;

            const isSuccess = card.dataset.success === "true";
            
            if (isSuccess) {
                // Clear the correct scoped cart
                const userId = document.body.dataset.userId;
                if (userId) {
                    localStorage.setItem("quickbite-cart-" + userId, JSON.stringify([]));
                }
                localStorage.setItem("quickbite-cart", JSON.stringify([]));
                localStorage.setItem("cart", JSON.stringify([])); 
            }
        });
        </script>

    <?php else: ?>
        <!-- Checkout Form Display -->
        <div class="checkout-title" style="margin-bottom: 30px;">
            <h1>Checkout</h1>
            <p>Place your order and receive a token number</p>
        </div>

        <div class="checkout-layout" style="display: flex; gap: 30px; flex-wrap: wrap;">
            <section class="checkout-card" style="flex: 2; min-width: 300px; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <form id="checkout-form" action="checkout.php" method="POST">
                    <!-- Cart data hidden input -->
                    <input type="hidden" name="cart_data" id="checkout-cart-data">

                    <div style="margin-bottom: 20px;">
                        <label for="checkout-name" style="display: block; font-weight: bold; margin-bottom: 8px;">Name</label>
                        <input id="checkout-name" name="name" type="text" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 1rem;">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label for="checkout-phone" style="display: block; font-weight: bold; margin-bottom: 8px;">Phone Number</label>
                        <input id="checkout-phone" name="phone" type="tel" required style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 1rem;">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label for="checkout-email" style="display: block; font-weight: bold; margin-bottom: 8px;">Email</label>
                        <input id="checkout-email" name="email" type="email" value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 1rem;">
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label style="display: block; font-weight: bold; margin-bottom: 12px;">Payment Method</label>
                        
                        <label style="display: flex; align-items: center; margin-bottom: 10px; font-size: 1rem; cursor: pointer;">
                            <input type="radio" name="payment_method" value="Cash" required style="margin-right: 10px; transform: scale(1.2);"> Cash on Counter
                        </label>
                        
                        <label style="display: flex; align-items: center; margin-bottom: 10px; font-size: 1rem; cursor: pointer;">
                            <input type="radio" name="payment_method" value="Card" style="margin-right: 10px; transform: scale(1.2);"> Card Payment
                        </label>
                        
                        <label style="display: flex; align-items: center; margin-bottom: 10px; font-size: 1rem; cursor: pointer;">
                            <input type="radio" name="payment_method" value="Online Banking" style="margin-right: 10px; transform: scale(1.2);"> Online Banking
                        </label>
                    </div>

                    <button type="submit" style="background: #e67e22; color: #fff; border: none; padding: 14px 28px; font-size: 1.1rem; font-weight: bold; border-radius: 4px; cursor: pointer; width: 100%; transition: background 0.2s;">Pay Now</button>
                </form>

                <div id="checkout-success" class="success-box" hidden style="margin-top: 20px; padding: 15px; border-radius: 5px;"></div>
            </section>

            <aside class="checkout-summary-card" style="flex: 1; min-width: 280px; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); height: fit-content;">
                <h2 style="margin-top: 0; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px;">Order Summary</h2>
                <div id="checkout-summary"></div>
            </aside>
        </div>
    <?php endif; ?>
</main>

<footer class="footer">
    <div class="footer-container">
        <div class="footer-section">
            <h2>Quick Bites</h2>
            <p>Order delicious food from the comfort of your home.</p>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="myorders.php">My Orders</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Contact</h3>
            <p>Phone: (555) 123-4567</p>
            <p>Email: info@quickbites.com</p>
            <p>Hours: 9 AM - 10 PM</p>
        </div>
        <div class="footer-section">
            <h3>Follow Us</h3>
            <ul class="footer-social">
                <li><a href="#">Facebook</a></li>
                <li><a href="#">Twitter</a></li>
                <li><a href="#">Instagram</a></li>
            </ul>
        </div>
    </div>
    <hr>
    <div class="footer-bottom">
        <p>© 2026 Quick Bites. All rights reserved.</p>
    </div>
</footer>

<script src="js/script.js"></script>
</body>
</html>