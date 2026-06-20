<?php
session_start();
include 'database.php'; // 👈 මේක ඇතුළේ තියෙන්නේ $conn variable එකයි

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

$message = '';
$messageClass = 'success-box error-box';

if ($email === '' || $password === '') {
    $message = 'Please fill in all fields.';
} else {
    try {
        // 1. $pdo වෙනුවට $conn භාවිතා කළා
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // 2. Register වීමේදී password_hash භාවිතා කළ නිසා password_verify පමණක් සෑහේ
            if (password_verify($password, $user['password'])) {
                
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['fullname'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_email'] = $user['email'];

                // 3. index.html වෙනුවට index.php ලෙස වෙනස් කළා (Session කියවීමට හැකි වීමට)
                header('Location: index.php');
                exit;
            } else {
                $message = 'Invalid email or password.';
            }
        } else {
            $message = 'Invalid email or password.';
        }
    } catch (PDOException $e) {
        $message = "Database error. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Status - QuickBite</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
<main class="login-shell">
    <section class="login-hero">
        <div class="login-icon" aria-hidden="true">❌</div>
        <h1>Login Failed</h1>
        <p>Something went wrong</p>
    </section>

    <section class="form-container">
        <div class="<?php echo $messageClass; ?>">
            <?php echo $message; ?>
        </div>
        <p class="register-note">
            <a href="login.html">Try Again</a> | 
            <a href="register.html">Create an account</a>
        </p>
    </section>
</main>
</body>
</html>
