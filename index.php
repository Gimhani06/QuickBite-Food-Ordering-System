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
    <link rel="stylesheet" href="css/style.css">
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

    <section class="hero">
	    <h2>Delicious Food Delivered Fast</h2>
        <p>Order your favorite meals online.</p>
        <a href="menu.php" class="btn">Order Now</a>
    </section>

    <section class="features">
      <div class="card-wrap">
        <div class="card">
          <h3>Fresh Food</h3>
          <p>Healthy and tasty meals.</p>
        </div>

        <div class="card">
          <h3>Fast Delivery</h3>
          <p>Quick delivery to your doorstep.</p>
        </div>

        <div class="card">
          <h3>Easy Payment</h3>
          <p>Simple and secure payment methods.</p>
        </div>
      </div>
    </section>

<section class="featured-dishes">
    <h2 class="featured-title">Featured Dishes</h2>
    <p class="featured-subtitle">Try our chef's special recommendations</p>
    <div class="dishes-container">
        <div class="dish-card">
            <div class="dish-image">
                <img src="images/grilled_Salmon.jpg" alt="Grilled Salmon">
                <span class="dish-price">RS.200</span>
            </div>
            <div class="dish-info">
                <h3>Grilled Salmon</h3>
                <p>Fresh Atlantic salmon with seasonal vegetables and lemon butter sauce</p>
                <button class="add-to-cart-btn">+ Add to Cart</button>
            </div>
        </div>
        <div class="dish-card">
            <div class="dish-image">
                <img src="images/Artisan_Sandwich.jpg" alt="Artisan Sandwich">
                <span class="dish-price">RS.300</span>
            </div>
            <div class="dish-info">
                <h3>Artisan Sandwich</h3>
                <p>Freshly baked bread with premium deli meats and aged cheese</p>
                <button class="add-to-cart-btn">+ Add to Cart</button>
            </div>
        </div>
        <div class="dish-card">
            <div class="dish-image">
                <img src="images/Pan_Seared_Fish.jpg" alt="Pan-Seared Fish">
                <span class="dish-price">Rs.200</span>
            </div>
            <div class="dish-info">
                <h3>Pan-Seared Fish</h3>
                <p>Delicate white fish with garden vegetables and herb butter</p>
                <button class="add-to-cart-btn">+ Add to Cart</button>
            </div>
        </div>
    </div>
    <div class="view-menu-container">
        <a href="menu.php" class="view-menu-btn">View Full Menu &rarr;</a>
    </div>
</section>

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
