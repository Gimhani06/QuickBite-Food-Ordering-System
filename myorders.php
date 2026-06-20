<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'database.php'; // 👈 ඔයාගේ පරණ database.php එක එහෙම්මම තිබුනදෙන්

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
$userId = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $orders = [];
    $error_msg = $e->getMessage();
}

function get_status_styles($status) {
    switch ($status) {
        case 'Preparing':
            return 'background: #dff9fb; color: #0984e3;';
        case 'Ready':
            return 'background: #e0dbff; color: #6c5ce7;';
        case 'Completed':
            return 'background: #d4edda; color: #155724;';
        case 'Pending':
        default:
            return 'background: #ffeaa7; color: #d63031;';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QuickBite - My Orders</title>
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

<main class="orders-shell">
    <div class="orders-title">
        <h1>My Orders</h1>
        <p>Track your token and order status</p>
    </div>
    <section class="orders-card">
        <div class="orders-list" id="orders-list">
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-item" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius: 5px; background: #fff;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h3 style="margin: 0; color: #e67e22;">Token: <?php echo htmlspecialchars($order['token_number']); ?></h3>
                                <p style="margin: 5px 0 0 0; font-size: 14px; color: #666;">Date: <?php echo $order['order_date']; ?></p>
                            </div>
                            <div style="text-align: right;">
                                <span style="font-weight: bold; display: block;">LKR <?php echo number_format($order['total_amount'], 2); ?></span>
                                <span class="status-badge" style="<?php echo get_status_styles($order['status']); ?> padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; color: #7f8c8d; padding: 20px;">No orders found in your account.</p>
                <?php if (isset($error_msg)) echo "<p style='color:red;'>Error: $error_msg</p>"; ?>
            <?php endif; ?>
        </div>
    </section>
</main>
</body>
</html>