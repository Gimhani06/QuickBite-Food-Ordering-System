<?php
// 1. Session එක සහ Database එක මුලින්ම ඇතුළත් කරන්න
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'database.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickBite</title>
    <link rel="stylesheet" href="css/cart.css">
</head>
<body data-user-id="<?php echo $_SESSION['user_id'] ?? ''; ?>">

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
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="myorders.php">My Orders</a></li>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <li><a href="admin.php" style="color: #e67e22; font-weight: bold;">Admin Panel</a></li>
            <?php endif; ?>
          </ul>
        </nav>

        <div class="header-actions">
          <a href="cart.php" class="cart-btn">Cart</a>
          
          <!-- 2. Session එක ඇත්දැයි නිවැරදිවම පරීක්ෂා කිරීම -->
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

<main class="cart-page">
    <a href="menu.php" class="back-link">Continue Shopping</a>

    <div class="cart-layout">
        <section class="cart-panel">
            <div class="panel-header">
                <div>
                    <h2>Shopping Cart</h2>
                    <p id="cart-total-items">0 items</p>
                </div>
            </div>

            <div id="cart-empty-state" class="empty-state" hidden>
                Your cart is empty. Add items from the menu to see them here.
            </div>

            <div id="cart-list" class="cart-list"></div>
        </section>

        <aside class="summary-panel">
            <h2>Order Summary</h2>

            <div class="summary-row">
                <span>Subtotal</span>
                <strong id="summary-subtotal">Rs.0</strong>
            </div>
            <hr>
            <div class="summary-total">
                <span>Total</span>
                <strong id="summary-total">Rs.0</strong>
            </div>

            <a href="checkout.php" style="text-decoration: none; display: block;">
                <button type="button" class="checkout-btn" style="width: 100%;">Proceed to Checkout</button>
            </a>
            <p class="summary-note">Secure checkout powered by our system</p>
        </aside>
    </div>
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
        <li><a href="#">Menu</a></li>
        <li><a href="#">Cart</a></li>
        <li><a href="#">My Orders</a></li>
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