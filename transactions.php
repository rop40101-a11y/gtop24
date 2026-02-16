<?php
include 'common/config.php';

// Check Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$history = [];

if (isset($conn)) {
    // 1. Fetch from ORDERS
    $check_orders = $conn->query("SHOW TABLES LIKE 'orders'");
    if($check_orders->num_rows > 0) {
        // Fetch All Columns to ensure we get method/txid if they exist
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                // Map fields if they exist, or set defaults
                $row['desc']   = "Order #" . $row['id'];
                $row['method'] = isset($row['payment_method']) ? $row['payment_method'] : 'Wallet';
                $row['txid']   = isset($row['order_txid']) ? $row['order_txid'] : (isset($row['trx_id']) ? $row['trx_id'] : '-');
                $history[] = $row;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Transactions</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Bree+Serif&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Bree Serif', serif; /* UPDATED FONT */
            background-color: #f0f5f9;
            color: #1f2937;
            -webkit-tap-highlight-color: transparent;
        }
        
        /* Consistent Header */
        .app-header {
            background-color: #ffffff;
            height: 60px;
            display: flex;
            align-items: center;
            padding: 0 16px;
            position: sticky;
            top: 0;
            z-index: 50;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }
        
        /* Transaction Card */
        .trans-card {
            background: white;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: flex-start; /* Changed to top align for details */
            justify-content: space-between;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
            border: 1px solid #e5e7eb;
        }

        .icon-box {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        /* Detail Tags */
        .detail-tag {
            font-size: 10px;
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            color: #64748b;
            display: inline-block;
            margin-top: 4px;
            font-family: sans-serif; /* Keep details legible */
            font-weight: 600;
        }

        /* Status Badges */
        .status-badge {
            font-size: 10px;
            padding: 3px 10px;
            border-radius: 4px;
            text-transform: capitalize;
            font-family: sans-serif;
            font-weight: 700;
        }
        .st-completed, .st-success { background: #dcfce7; color: #166534; }
        .st-pending, .st-processing { background: #fef9c3; color: #854d0e; }
        .st-failed, .st-cancelled { background: #fee2e2; color: #991b1b; }
        
    </style>
</head>
<body class="pb-6">

    <div class="app-header">
        <a href="profile.php" class="w-8 h-8 flex items-center justify-center rounded-full active:bg-gray-100 transition-colors mr-3 text-gray-600">
            <i class="fa-solid fa-arrow-left text-lg"></i>
        </a>
        <h1 class="text-xl text-gray-800">Transactions</h1>
    </div>

    <div class="container mx-auto px-4 mt-5">
        
        <?php if (count($history) > 0): ?>
            <?php foreach ($history as $t): 
                // Fix Amount NULL Error
                $amountVal = (float)($t['amount'] ?? 0);
                
                // Colors
                $amountColor = 'text-red-500'; // Default debit
                $sign = '-';
                
                // Icon Settings
                $iconClass = 'fa-bag-shopping';
                $iconBg = 'bg-blue-50 text-[#2B71AD]';

                // Display Method formatting
                $methodDisplay = ucfirst($t['method']);
                $trxDisplay = $t['txid'] != '-' ? 'Trx: '.$t['txid'] : '';
            ?>
            <div class="trans-card">
                <div class="flex gap-3 overflow-hidden w-full">
                    
                    <div class="icon-box <?php echo $iconBg; ?>">
                        <i class="fa-solid <?php echo $iconClass; ?>"></i>
                    </div>

                    <div class="min-w-0 flex-1">
                        <h4 class="text-gray-800 text-sm truncate">
                            <?php echo htmlspecialchars($t['desc']); ?>
                        </h4>
                        
                        <div class="flex flex-wrap gap-1 mt-1">
                            <?php if(!empty($methodDisplay)): ?>
                                <span class="detail-tag text-xs border border-gray-200">
                                    <i class="fa-regular fa-credit-card mr-1"></i><?php echo $methodDisplay; ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if(!empty($trxDisplay)): ?>
                                <span class="detail-tag text-xs border border-gray-200">
                                    <i class="fa-solid fa-receipt mr-1"></i><?php echo $trxDisplay; ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <p class="text-xs text-gray-400 mt-1" style="font-family: sans-serif;">
                            <?php echo date('d M Y, h:i A', strtotime($t['created_at'])); ?>
                        </p>
                    </div>
                </div>

                <div class="text-right flex-shrink-0 ml-2">
                    <div class="text-base <?php echo $amountColor; ?>">
                        <?php echo $sign . number_format($amountVal, 2); ?> ৳
                    </div>
                    <div class="mt-1">
                        <span class="status-badge st-<?php echo strtolower($t['status']); ?>">
                            <?php echo $t['status']; ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        
        <?php else: ?>
            <div class="flex flex-col items-center justify-center mt-32 opacity-70">
                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mb-4 shadow-sm border border-gray-100">
                    <i class="fa-solid fa-receipt text-4xl text-gray-300"></i>
                </div>
                <h3 class="text-lg text-gray-600">No Transactions</h3>
                <p class="text-sm text-gray-400 mt-1" style="font-family: sans-serif;">You haven't made any orders yet.</p>
                <a href="index.php" class="mt-6 bg-[#2B71AD] text-white px-6 py-2 rounded-full text-sm shadow-sm hover:opacity-90 transition">
                    Shop Now
                </a>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
