<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.html');
    exit;
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');

$message = '';
$messageClass = 'success-box';
$tokenNumber = '';

if ($name === '' || $phone === '' || $email === '') {
    $message = 'Please fill in all fields before placing an order.';
    $messageClass = 'success-box error-box';
} else {
    if (!isset($_SESSION['qb_token_counter'])) {
        $_SESSION['qb_token_counter'] = 1000;
    }

    $_SESSION['qb_token_counter']++;
    $tokenNumber = 'QB' . $_SESSION['qb_token_counter'];
    $safeToken = htmlspecialchars($tokenNumber, ENT_QUOTES, 'UTF-8');
    $message = "Order Successful! Your Token Number: {$safeToken}";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - QuickBite</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="checkout-page">
<main class="checkout-shell">
    <div class="checkout-title">
        <h1>Checkout</h1>
        <p>Order status</p>
    </div>

    <section class="checkout-card">
        <div class="<?php echo $messageClass; ?>">
            <?php echo $message; ?>
        </div>
        <p class="register-note"><a href="checkout.html">Back to checkout</a> | <a href="myorders.html">My Orders</a></p>
    </section>
</main>
</body>
</html>
