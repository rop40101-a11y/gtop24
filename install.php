<?php
// install.php
// Database Connection Configuration
$host = "127.0.0.1";
$user = "root";
$pass = "root"; // For AWebServer user is 'root', pass is 'root'. For XAMPP pass is usually empty.

// Create Connection
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "<br>Please check your server settings in install.php");
}

// Create Database
$sql = "CREATE DATABASE IF NOT EXISTS primetopup_db";
if ($conn->query($sql) === TRUE) {
    $conn->select_db("primetopup_db");
    
    // SQL Queries for All Tables (Including new 'deposits' table)
    $queries = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            phone VARCHAR(20),
            email VARCHAR(100) UNIQUE,
            password VARCHAR(255),
            balance DECIMAL(10,2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50),
            password VARCHAR(255)
        )",
        "CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) UNIQUE,
            value TEXT
        )",
        "CREATE TABLE IF NOT EXISTS sliders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            image VARCHAR(255),
            link VARCHAR(255)
        )",
        "CREATE TABLE IF NOT EXISTS games (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            type ENUM('uid','voucher') DEFAULT 'uid',
            description TEXT,
            image VARCHAR(255)
        )",
        "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            game_id INT,
            name VARCHAR(100),
            price DECIMAL(10,2),
            FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS payment_methods (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50),
            logo VARCHAR(255),
            qr_image VARCHAR(255),
            number VARCHAR(50),
            description TEXT,
            short_desc VARCHAR(255)
        )",
        "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            game_id INT,
            product_id INT,
            amount DECIMAL(10,2),
            status ENUM('pending','completed','cancelled') DEFAULT 'pending',
            player_id VARCHAR(100),
            transaction_id VARCHAR(100),
            payment_method VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS redeem_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            game_id INT,
            product_id INT,
            code VARCHAR(100),
            status ENUM('active','used','expired') DEFAULT 'active',
            order_id INT DEFAULT 0
        )",
        // New Table for Add Money Requests
        "CREATE TABLE IF NOT EXISTS deposits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            amount DECIMAL(10,2),
            method VARCHAR(50),
            wallet_number VARCHAR(50),
            trx_id VARCHAR(100),
            status ENUM('pending','approved','rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];

    // Execute Table Queries
    echo "<div style='font-family: sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background: #f9f9f9;'>";
    echo "<h2 style='color: #333; text-align: center;'>System Installation</h2>";
    echo "<hr>";
    
    foreach ($queries as $q) {
        if (!$conn->query($q)) {
            echo "<p style='color:red'>Error creating table: " . $conn->error . "</p>";
        }
    }
    echo "<p style='color:green'>✔ Database Tables Checked/Created.</p>";

    // Default Admin (mraiprime / khfmhf2007)
    $adminPass = password_hash("khfmhf2007", PASSWORD_DEFAULT);
    $conn->query("INSERT IGNORE INTO admins (id, username, password) VALUES (1, 'mraiprime', '$adminPass')");
    echo "<p style='color:green'>✔ Default Admin Account Ready.</p>";
    
    // Default Settings
    $conn->query("INSERT IGNORE INTO settings (name, value) VALUES 
        ('site_name', 'Prime Top Up'),
        ('site_desc', 'Best Gaming Top Up Shop'),
        ('currency', '৳'),
        ('marquee_text', 'Welcome to Prime Top Up! Best prices for games.'),
        ('marquee_active', '1'),
        ('fab_link', 'https://t.me/mraiprime'),
        ('add_money_video', 'https://www.youtube.com/watch?v=VIDEO_ID')");
    echo "<p style='color:green'>✔ Default Settings Inserted.</p>";

    // Create Upload Folder
    if (!file_exists('uploads')) {
        if (mkdir('uploads', 0777, true)) {
            echo "<p style='color:green'>✔ Uploads folder created successfully.</p>";
        } else {
            echo "<p style='color:red'>✘ Failed to create uploads folder. Please create a folder named 'uploads' manually in the root directory.</p>";
        }
    } else {
        echo "<p style='color:orange'>✔ Uploads folder already exists.</p>";
    }

    echo "<hr>";
    echo "<div style='text-align:center; margin-top:20px;'>
            <h1 style='color:blue;'>Installation Complete!</h1>
            <p>Your system is ready to use.</p>
            <div style='display:flex; gap:10px; justify-content:center; margin-top:20px;'>
                <a href='login.php' style='padding:12px 25px; background:blue; color:white; text-decoration:none; border-radius:5px; font-weight:bold;'>Go to User Login</a>
                <a href='mraiprime/login.php' style='padding:12px 25px; background:black; color:white; text-decoration:none; border-radius:5px; font-weight:bold;'>Go to Admin Panel</a>
            </div>
            <p style='color:red; font-size: 12px; margin-top: 20px;'>Warning: Please delete or rename 'install.php' after installation for security.</p>
          </div>";
    echo "</div>";

} else {
    echo "Error creating database: " . $conn->error;
}

$conn->close();
?>
