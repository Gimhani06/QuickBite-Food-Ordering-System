<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'database.php'; // Defines $conn

// 1. Enforce Admin Authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$statusMessage = '';
$statusClass = '';

// 2. Handle Status Updates (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $orderId = intval($_POST['order_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? '');

    $allowedStatuses = ['Pending', 'Preparing', 'Ready', 'Completed'];
    if ($orderId > 0 && in_value($newStatus, $allowedStatuses)) {
        try {
            $updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
            $updateStmt->execute([$newStatus, $orderId]);
            $statusMessage = "Order #$orderId status updated to '$newStatus' successfully!";
            $statusClass = "success-toast";
        } catch (PDOException $e) {
            $statusMessage = "Error updating order status: " . $e->getMessage();
            $statusClass = "error-toast";
        }
    }
}

// Helper to check in array safely
function in_value($val, $arr) {
    return in_array($val, $arr, true);
}

// 3. Fetch All Orders
try {
    // Join orders with users and payments
    $ordersQuery = $conn->query("
        SELECT o.*, u.fullname as customer_name, u.email as customer_email, 
               p.payment_method, p.transaction_status
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        LEFT JOIN payments p ON o.order_id = p.order_id
        ORDER BY o.order_date DESC
    ");
    $orders = $ordersQuery->fetchAll(PDO::FETCH_ASSOC);

    // Fetch items for each order
    foreach ($orders as &$order) {
        $itemsQuery = $conn->prepare("
            SELECT oi.quantity, oi.price, f.food_name 
            FROM order_items oi
            JOIN foods f ON oi.food_item_id = f.food_id
            WHERE oi.order_id = ?
        ");
        $itemsQuery->execute([$order['order_id']]);
        $order['items'] = $itemsQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($order); // break reference
} catch (PDOException $e) {
    $orders = [];
    $statusMessage = "Failed to load orders: " . $e->getMessage();
    $statusClass = "error-toast";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - QuickBite</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --primary-color: #e67e22;
            --primary-hover: #d35400;
            --bg-dark: #1e272e;
            --card-bg: #ffffff;
            --border-color: #f1f2f6;
            --text-dark: #2f3640;
            --text-light: #718093;
            
            --status-pending: #ffeaa7;
            --status-pending-text: #d63031;
            --status-preparing: #dff9fb;
            --status-preparing-text: #0984e3;
            --status-ready: #e0dbff;
            --status-ready-text: #6c5ce7;
            --status-completed: #d4edda;
            --status-completed-text: #155724;
        }

        body {
            background-color: #f5f6fa;
            color: var(--text-dark);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: #fff;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .dashboard-header h1 {
            margin: 0;
            font-size: 1.8rem;
            color: var(--text-dark);
            border-left: 5px solid var(--primary-color);
            padding-left: 15px;
        }

        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 5px;
            color: #fff;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 1000;
            animation: slideIn 0.3s ease-out, fadeOut 0.5s ease-in 4s forwards;
        }

        .success-toast {
            background-color: #2ecc71;
        }

        .error-toast {
            background-color: #e74c3c;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes fadeOut {
            to { opacity: 0; visibility: hidden; }
        }

        .order-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .order-card {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid var(--border-color);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            background: #fafbfc;
            border-bottom: 1px solid var(--border-color);
        }

        .token-badge {
            background: var(--primary-color);
            color: #fff;
            font-size: 1.1rem;
            font-weight: bold;
            padding: 5px 15px;
            border-radius: 5px;
        }

        .order-date {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .card-body {
            padding: 25px;
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }

        .customer-info, .order-details, .payment-info, .status-update-section {
            flex: 1;
            min-width: 220px;
        }

        .section-title {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-light);
            margin-bottom: 12px;
            margin-top: 0;
            font-weight: bold;
        }

        .customer-info p {
            margin: 5px 0;
            font-size: 0.95rem;
        }

        .customer-name {
            font-weight: bold;
            color: var(--text-dark);
        }

        .order-items-list {
            margin: 0;
            padding-left: 20px;
            font-size: 0.95rem;
        }

        .order-items-list li {
            margin-bottom: 5px;
        }

        .payment-method {
            display: inline-block;
            background: #f1f2f6;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: bold;
            color: #57606f;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .status-Pending {
            background: var(--status-pending);
            color: var(--status-pending-text);
        }

        .status-Preparing {
            background: var(--status-preparing);
            color: var(--status-preparing-text);
        }

        .status-Ready {
            background: var(--status-ready);
            color: var(--status-ready-text);
        }

        .status-Completed {
            background: var(--status-completed);
            color: var(--status-completed-text);
        }

        .status-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .status-select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 0.95rem;
            background-color: #fff;
            cursor: pointer;
            outline: none;
            transition: border-color 0.2s;
        }

        .status-select:focus {
            border-color: var(--primary-color);
        }

        .update-btn {
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }

        .update-btn:hover {
            background-color: var(--primary-hover);
        }

        .no-orders {
            text-align: center;
            background: #fff;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            color: var(--text-light);
        }
    </style>
</head>
<body>

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
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <li><a href="admin.php" style="color: #e67e22; font-weight: bold;">Admin Panel</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="header-actions">
            <span class="user-welcome" style="margin-right: 15px; color: #fbf4f4; font-weight: bold;">
                Admin: <?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?>
            </span>
            <a href="logout.php" class="login-link">Logout</a>
        </div>
    </div>
</header>

<?php if ($statusMessage !== ''): ?>
    <div class="toast-notification <?php echo $statusClass; ?>">
        <?php echo htmlspecialchars($statusMessage); ?>
    </div>
<?php endif; ?>

<main class="dashboard-container">
    <div class="dashboard-header">
        <h1>Admin Order Dashboard</h1>
        <div style="font-size: 0.95rem; color: var(--text-light);">
            Logged in as <strong><?php echo htmlspecialchars($_SESSION['user_email']); ?></strong>
        </div>
    </div>

    <div class="order-grid">
        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
                <article class="order-card">
                    <div class="card-header">
                        <div>
                            <span class="token-badge"><?php echo htmlspecialchars($order['token_number']); ?></span>
                            <span style="margin-left: 10px; font-weight: bold; color: var(--text-light);">Order #<?php echo $order['order_id']; ?></span>
                        </div>
                        <div class="order-date">
                            Ordered: <?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="customer-info">
                            <h3 class="section-title">Customer Details</h3>
                            <p class="customer-name"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                            <p style="color: var(--text-light); font-size: 0.9rem;"><?php echo htmlspecialchars($order['customer_email']); ?></p>
                        </div>

                        <div class="order-details">
                            <h3 class="section-title">Items Ordered</h3>
                            <ul class="order-items-list">
                                <?php if (!empty($order['items'])): ?>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <li>
                                            <strong><?php echo $item['quantity']; ?>x</strong> 
                                            <?php echo htmlspecialchars($item['food_name']); ?> 
                                            <span style="color: var(--text-light); font-size: 0.85rem;">(LKR <?php echo number_format($item['price'], 2); ?> each)</span>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li style="color: var(--text-light); list-style: none; margin-left: -20px;">No items found.</li>
                                <?php endif; ?>
                            </ul>
                            <div style="margin-top: 15px; font-weight: bold; font-size: 1.1rem; color: var(--primary-color);">
                                Total: LKR <?php echo number_format($order['total_amount'], 2); ?>
                            </div>
                        </div>

                        <div class="payment-info">
                            <h3 class="section-title">Payment Info</h3>
                            <p><span class="payment-method"><?php echo htmlspecialchars($order['payment_method']); ?></span></p>
                            <p>Status: 
                                <span class="status-badge status-<?php echo htmlspecialchars($order['transaction_status'] ?? 'Pending'); ?>">
                                    <?php echo htmlspecialchars($order['transaction_status'] ?? 'Pending'); ?>
                                </span>
                            </p>
                        </div>

                        <div class="status-update-section">
                            <h3 class="section-title">Manage Status</h3>
                            <p>Current: 
                                <span class="status-badge status-<?php echo htmlspecialchars($order['status']); ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </p>
                            <form class="status-form" method="POST" action="admin.php">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                <select name="status" class="status-select">
                                    <option value="Pending" <?php echo ($order['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Preparing" <?php echo ($order['status'] === 'Preparing') ? 'selected' : ''; ?>>Preparing</option>
                                    <option value="Ready" <?php echo ($order['status'] === 'Ready') ? 'selected' : ''; ?>>Ready</option>
                                    <option value="Completed" <?php echo ($order['status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                </select>
                                <button type="submit" class="update-btn">Save</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-orders">
                <h2>No Orders Found</h2>
                <p>Incoming customer orders will appear here automatically.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
