<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.html');
    exit;
}

$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirmPassword = trim($_POST['confirm_password'] ?? '');

$message = '';
$messageClass = 'success-box';

if ($fullName === '' || $email === '' || $password === '' || $confirmPassword === '') {
    $message = 'Please fill in all fields.';
    $messageClass = 'success-box error-box';
} elseif ($password !== $confirmPassword) {
    $message = 'Passwords do not match.';
    $messageClass = 'success-box error-box';
} else {
    $safeName = htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8');
    $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $message = "Account created for {$safeName} ({$safeEmail}). Connect this form to MySQL later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - QuickBite</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page register-page">
<main class="login-shell">
    <section class="login-hero">
        <div class="login-icon" aria-hidden="true">↦</div>
        <h1>Register</h1>
        <p>Account registration status</p>
    </section>

    <section class="form-container">
        <div class="<?php echo $messageClass; ?>">
            <?php echo $message; ?>
        </div>
        <p class="register-note"><a href="register.html">Back to register form</a> | <a href="login.html">Sign in</a></p>
    </section>
</main>
</body>
</html>
