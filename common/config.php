<?php
// common/config.php
ob_start(); // এই লাইনটি এরর ফিক্স করবে
session_start();

$host = "127.0.0.1";
$user = "root";
$pass = "root"; 
$db   = "primetopup_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    if (strpos($_SERVER['SCRIPT_NAME'], 'install.php') === false) {
        die("Connection failed: " . $conn->connect_error);
    }
}

// Helper: Get Setting
function getSetting($conn, $key) {
    $res = $conn->query("SELECT value FROM settings WHERE name='$key' LIMIT 1");
    if($res && $res->num_rows > 0) return $res->fetch_assoc()['value'];
    return "";
}
?>
