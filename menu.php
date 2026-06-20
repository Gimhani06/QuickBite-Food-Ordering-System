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
    <link rel="stylesheet" href="css/menu.css">
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

<div class="menu-subnav">
  <div class="subnav-inner">
    <h2 class="subnav-title">Quick Bites Menu</h2>
    <nav class="category-nav" aria-label="Menu categories">
      <ul>
        <li><a href="maincourse.php">Main Course</a></li>
        <li><a href="pasta.php">Pasta</a></li>
        <li><a href="salads.php">Salads</a></li>
        <li><a href="burgers.php">Burgers</a></li>
        <li><a href="sandwiches.php">Sandwiches</a></li>
        <li><a href="featured dishes.php">Featured Dishes</a></li>
      </ul>
    </nav>
  </div>
</div>


<div class="container">

<div class="page-title">
    <h2>Our Menu</h2>
    <p>Select a category</p>
</div>

<div class="menu-grid">

<a href="maincourse.php" class="card">
    <img src="images/fried rice.jpg.jpeg">
    <div class="card-content">
        <h3>Main Courses</h3>
        <p>Fried Rice, Koththu</p>
    </div>
</a>

<a href="sandwiches.php" class="card">
    <img src="images/sandwiches menu.png">
    <div class="card-content">
        <h3>Sandwiches</h3>
        <p>Egg Sandwich, Chicken Sandwich</p>
    </div>
</a>

<a href="pasta.php" class="card">
    <img src="images/pasta_menu.jpg.jpeg">
    <div class="card-content">
        <h3>Pasta</h3>
        <p>Cheese Pasta</p>
    </div>
</a>

<a href="salads.php" class="card">
    <img src="images/salad menu.jpg.jpeg">
    <div class="card-content">
        <h3>Salads</h3>
        <p>Chicken Salad, Vegetable Salad</p>
    </div>
</a>

<a href="burgers.php" class="card">
    <img src="images/burgers menu.png">
    <div class="card-content">
        <h3>Burgers</h3>
        <p>Chicken Burger, Fish Burger</p>
    </div>
</a>

<a href="featured dishes.php" class="card">
    <img src="images/special food menu.jpg.jpeg">
    <div class="card-content">
        <h3>Featured Dishes</h3>
        <p>Grilled Salmon,Artisan Sandwich,Pan-Seared Fish</p>
   </div>
</a>


</div>

</div>
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