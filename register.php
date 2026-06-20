<?php
session_start();
require 'database.php'; // 👈 දැන් මේක ඇතුළේ තියෙන්නේ PDO ($conn) එක

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
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $message = 'Please enter a valid email address.';
    $messageClass = 'success-box error-box';
} elseif ($password !== $confirmPassword) {
    $message = 'Passwords do not match.';
    $messageClass = 'success-box error-box';
} elseif (strlen($password) < 6) {
    $message = 'Password must be at least 6 characters long.';
    $messageClass = 'success-box error-box';
} else {
    try {
        // 1. Email එක දැනටමත් තියෙනවාදැයි බැලීම (PDO ක්‍රමයට)
        $checkQuery = "SELECT user_id FROM users WHERE email = :email";
        $stmt = $conn->prepare($checkQuery);
        $stmt->execute([':email' => $email]);
        
        if ($stmt->rowCount() > 0) {
            $message = 'This email is already registered.';
            $messageClass = 'success-box error-box';
        } else {
            // 2. Password එක ආරක්ෂිතව Hash කිරීම
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            // 3. දත්ත ඇතුළත් කිරීම (PDO ක්‍රමයට)
            $insertQuery = "INSERT INTO users (fullname, email, password, role) VALUES (:fullname, :email, :password, 'customer')";
            $stmt = $conn->prepare($insertQuery);
            
            $result = $stmt->execute([
                ':fullname' => $fullName,
                ':email'    => $email,
                ':password' => $hashedPassword
            ]);
            
            if ($result) {
                $safeName = htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8');
                $message = "Account successfully created for {$safeName}! You can now Sign in.";
                $messageClass = 'success-box';
            } else {
                $message = "Something went wrong. Please try again.";
                $messageClass = 'success-box error-box';
            }
        }
    } catch (PDOException $e) {
        // Database Error එකක් ආවොත් බලාගන්න
        $message = "Database error: " . $e->getMessage();
        $messageClass = 'success-box error-box';
    }
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
