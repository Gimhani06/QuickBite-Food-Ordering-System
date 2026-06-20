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
<div class="container">

<a href="menu.php" class="back-btn">← Back to Menu</a>

<div class="page-title">
	<h2>Pasta</h2>
	<p>Choose a pasta dish and add it to your cart</p>
</div>

<div class="menu-grid">

	<div class="card">
		<div class="card-image-wrap">
			<img src="images\cheese pasta.jpg.jpeg" alt="Cheese Pasta">
			<span class="price-badge">Rs.200.00</span>
		</div>
		<div class="card-content">
			<h3>Cheese Pasta</h3>
			<p>Delicious pasta with melted cheese and herbs.</p>
			<button class="add-to-cart-btn">+ Add to Cart</button>
		</div>
	</div>

	<div class="card">
		<div class="card-image-wrap">
			<img src="images\chickenpasta.jpg" alt="Chicken Pasta">
			<span class="price-badge">Rs.160.00</span>
		</div>
		<div class="card-content">
			<h3>Chicken Pasta</h3>
			<p>Delicious pasta with grilled chicken and herbs.</p>
			<button class="add-to-cart-btn">+ Add to Cart</button>
		</div>
	</div>
	
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