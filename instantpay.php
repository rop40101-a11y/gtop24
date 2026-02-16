<?php 
include 'common/config.php';

// Check Login
if(!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit; 
}

// --- RECEIVE DATA ---
$total = isset($_POST['amount']) ? $_POST['amount'] : (isset($_POST['total_amount']) ? $_POST['total_amount'] : 0);
$game_id = isset($_POST['game_id']) ? $_POST['game_id'] : 0;
$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : 0;
$player_id = isset($_POST['player_id']) ? $_POST['player_id'] : '';

// --- VALIDATION ---
if($total <= 0) {
    header("Location: index.php");
    exit;
}

// Fetch Settings
$site_name = getSetting($conn, 'site_name');
$site_logo = getSetting($conn, 'site_logo'); 
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <title>Secure Checkout | <?php echo htmlspecialchars($site_name); ?></title>
    
    <link rel="shortcut icon" href="<?php echo $site_logo; ?>" type="image/x-icon">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* FULL PAGE LAYOUT */
        body { 
            font-family: 'Lato', 'Noto Sans Bengali', sans-serif; 
            background-color: #f4f7f9;
            background-image: url('res/backgrounds/bg.png'); 
            background-repeat: no-repeat;
            background-position: center;
            background-size: 100% 100%; 
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
        }

        /* Top Navigation Bar */
        .top-bar-card {
            background: white;
            margin: 5px 15px 0 15px; 
            padding: 10px 15px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #e5e7eb; 
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
            flex-shrink: 0;
        }
        .nav-icon { color: #64748b; font-size: 18px; cursor: pointer; transition: color 0.2s; }
        .nav-icon:hover { color: #1e293b; }

        /* Content Container */
        .content-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start; /* Start from top */
            padding-top: 40px; 
            padding-left: 20px;
            padding-right: 20px;
            margin-bottom: 50px; 
        }

        /* Circular Logo Ring */
        .logo-ring {
            width: 110px; height: 110px;
            border-radius: 50%;
            border: 1px solid #bfdbfe; 
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            padding: 15px;
        }
        .logo-ring img {
            width: 100%; height: 100%; 
            object-fit: contain; 
            border-radius: 50%;
        }

        /* Title Style */
        .page-title {
            font-size: 20px;
            font-weight: 700;
            color: #556070; 
            margin-bottom: 30px;
            text-align: center;
            font-family: 'Lato', sans-serif;
        }

        /* Blue Section Header */
        .blue-header {
            background-color: #0047ab; /* Main Blue Color */
            color: white;
            width: 100%; max-width: 400px;
            padding: 12px;
            text-align: center;
            font-weight: 500;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 25px;
            font-family: 'Noto Sans Bengali', sans-serif;
        }

        /* Methods Grid */
        .methods-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            width: 100%; max-width: 400px;
        }

        /* Payment Card */
        .pay-card {
            background: transparent;
            
            /* CHANGED: Border color matches Header Background (#0047ab) */
            border: 1px solid #0047ab; 
            
            border-radius: 8px;
            height: 75px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .pay-card:active {
            background-color: rgba(0, 71, 171, 0.1); /* Light blue tint on click */
            transform: scale(0.98);
        }
        .pay-logo {
            height: 35px;
            width: auto;
            object-fit: contain;
        }

        /* Bottom Sticky Bar */
        .pay-btn-area {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: #dbeafe; 
            color: #0047ab; 
            font-weight: 700;
            font-size: 17px;
            text-align: center;
            
            /* CHANGED: Reduced Height (Padding) and Corner Radius */
            padding: 12px; 
            border-top-left-radius: 8px; 
            border-top-right-radius: 8px;
            
            box-shadow: 0 -2px 10px rgba(0,0,0,0.03);
            z-index: 50;
            font-family: 'Lato', sans-serif;
        }
    </style>
</head>
<body>

    <div class="top-bar-card">
        <a href="index.php" class="nav-icon"><i class="fa-solid fa-house"></i></a>
        <div class="flex gap-4">
            <button class="nav-icon"><i class="fa-solid fa-language"></i></button>
            <a href="secure/pay/payment_cancelled.php" class="nav-icon"><i class="fa-solid fa-xmark"></i></a>
        </div>
    </div>

    <div class="content-container">
        
        <div class="logo-ring">
            <img src="res/logo.png" alt="Logo">
        </div>

        <h1 class="page-title"><?php echo htmlspecialchars($site_name); ?> Pay</h1>

        <div class="blue-header">মোবাইল ব্যাংকিং</div>

        <div class="methods-grid">
            
            <form action="secure/pay/verify.php" method="POST" class="w-full">
                <input type="hidden" name="method" value="bkash">
                <input type="hidden" name="amount" value="<?php echo $total; ?>">
                <input type="hidden" name="game_id" value="<?php echo $game_id; ?>">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <input type="hidden" name="player_id" value="<?php echo htmlspecialchars($player_id); ?>">
                <button type="submit" class="pay-card w-full">
                    <img src="res/images/bkash.png" class="pay-logo" alt="bKash">
                </button>
            </form>

            <form action="secure/pay/verify.php" method="POST" class="w-full">
                <input type="hidden" name="method" value="nagad">
                <input type="hidden" name="amount" value="<?php echo $total; ?>">
                <input type="hidden" name="game_id" value="<?php echo $game_id; ?>">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <input type="hidden" name="player_id" value="<?php echo htmlspecialchars($player_id); ?>">
                <button type="submit" class="pay-card w-full">
                    <img src="res/images/Nagad.png" class="pay-logo" alt="Nagad">
                </button>
            </form>

            <form action="secure/pay/verify.php" method="POST" class="w-full">
                <input type="hidden" name="method" value="rocket">
                <input type="hidden" name="amount" value="<?php echo $total; ?>">
                <input type="hidden" name="game_id" value="<?php echo $game_id; ?>">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <input type="hidden" name="player_id" value="<?php echo htmlspecialchars($player_id); ?>">
                <button type="submit" class="pay-card w-full">
                    <img src="res/images/Rocket.png" class="pay-logo" alt="Rocket">
                </button>
            </form>

        </div>
    </div>

    <div class="pay-btn-area">
        Pay <?php echo number_format($total, 2); ?> BDT
    </div>

</body>
</html>
