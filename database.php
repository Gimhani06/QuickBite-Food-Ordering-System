<?php
// කිසිම ප්‍රශ්නයක් නොවෙන්න Port එකයි ඔක්කොම කෙලින්ම දැම්මා
$host = '127.0.0.1';
$port = '3307'; // ඔයාගේ සැබෑ MySQL Port එක (3307)
$db_name = 'quickbites_db'; // ඔයාගේ ඩේටාබේස් එකේ නම
$username = 'root';
$password = '';

// මාරු කරපු එක ෂුවර් වෙන්න අපි PDO ක්‍රමයට සම්බන්ධ කරමු
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db_name;charset=utf8";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // echo "Database Connected Successfully via New File!"; 
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>