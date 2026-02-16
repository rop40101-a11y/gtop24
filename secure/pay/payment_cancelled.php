<?php
// secure/pay/payment_cancelled.php
include '../../common/config.php';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Payment Cancelled</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Noto Sans Bengali', sans-serif;
            background-color: #f3f4f6;
            background-image: url('../../res/backgrounds/bg.png');
            background-repeat: repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .cancel-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 380px;
            overflow: hidden;
            text-align: center;
            border: 1px solid #e5e7eb;
        }
        .header-bar {
            background-color: #b91c1c; /* Red */
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 18px;
            justify-content: center;
        }
        .content-box {
            padding: 30px 20px;
        }
        .msg {
            color: #6b7280;
            font-size: 15px;
            margin-bottom: 25px;
        }
        .home-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            color: #374151;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.2s;
        }
        .home-btn:hover {
            background-color: #f9fafb;
        }
    </style>
</head>
<body>

    <div class="cancel-card">
        <div class="header-bar">
            <i class="fa-solid fa-circle-xmark"></i> পেমেন্ট বাতিল!
        </div>
        <div class="content-box">
            <p class="msg">আপনি পেমেন্ট বাতিল করেছেন।</p>
            
            <a href="../../index.php" class="home-btn">
                <i class="fa-solid fa-house mr-2"></i> ওয়েবসাইটে ফিরে যান!
            </a>
        </div>
    </div>

</body>
</html>
