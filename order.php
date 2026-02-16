<?php 
include 'common/header.php';
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$uid = $_SESSION['user_id'];

// ====================================================
// SELF-HEALING DATABASE: Ensure API Columns Exist
// ====================================================
if(isset($conn)) {
    // Add api_key to games if missing
    $cols = $conn->query("SHOW COLUMNS FROM games LIKE 'api_key'");
    if($cols->num_rows == 0) {
        $conn->query("ALTER TABLE games ADD COLUMN api_key VARCHAR(255) DEFAULT NULL");
    }
    // Add unipin_code to products if missing
    $cols2 = $conn->query("SHOW COLUMNS FROM products LIKE 'unipin_code'");
    if($cols2->num_rows == 0) {
        $conn->query("ALTER TABLE products ADD COLUMN unipin_code VARCHAR(100) DEFAULT NULL");
    }
}

// --- HANDLE FORM SUBMISSION ---
if(isset($_POST['action']) && $_POST['action'] == 'create_order') {
    $gid = (int)$_POST['game_id'];
    $pid = (int)$_POST['product_id'];
    $ply = isset($_POST['player_id']) ? $conn->real_escape_string($_POST['player_id']) : '';
    $met = isset($_POST['payment_method']) ? $conn->real_escape_string($_POST['payment_method']) : '';
    
    // Generate unique ID
    $trx = !empty($_POST['order_txid']) ? $conn->real_escape_string($_POST['order_txid']) : substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 10); 

    $pQuery = $conn->query("SELECT price, unipin_code FROM products WHERE id=$pid");
    if($pQuery->num_rows == 0) { die("Invalid Product"); }
    $pData = $pQuery->fetch_assoc();
    $amt = $pData['price'];
    $uCode = $pData['unipin_code'];

    if($gid > 0) {
        if($met == 'wallet') {
            $uRes = $conn->query("SELECT balance FROM users WHERE id=$uid");
            $uData = $uRes->fetch_assoc();
            if($uData['balance'] < $amt) {
                echo "<script>alert('Insufficient Balance!'); window.location.href='game_detail.php?id=$gid';</script>";
                exit;
            }
            $newBalance = $uData['balance'] - $amt;
            $conn->query("UPDATE users SET balance = $newBalance WHERE id = $uid");
        }

        // Insert Order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, game_id, product_id, amount, player_id, transaction_id, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iiidsss", $uid, $gid, $pid, $amt, $ply, $trx, $met);
        
        if($stmt->execute()){
            // UNIPIN AUTO TOPUP LOGIC
            $gRes = $conn->query("SELECT api_key FROM games WHERE id=$gid");
            $gData = $gRes->fetch_assoc();
            
            $default_api = "TPBD-1B87AB7E6594F330"; 
            $apiKey = (!empty($gData['api_key'])) ? $gData['api_key'] : $default_api;

            if(!empty($uCode) && !empty($ply)) {
                $apiUrl = "https://androidartist.com/api/uctopup";
                $postData = [ "api" => $apiKey, "playerid" => $ply, "code" => $uCode, "orderid" => $trx, "url" => "https://user-site.com/callback" ];

                $ch = curl_init($apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_exec($ch);
                curl_close($ch);
            }
            header("Location: order.php");
            exit;
        }
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Bree+Serif&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
    /* BACKGROUND SCROLL SETUP */
    body {
        background-image: url('res/backgrounds/bg.png');
        background-repeat: repeat;
        background-size: 100% auto; 
        background-attachment: scroll; /* Ensures BG scrolls with user */
        background-position: top center;
        font-family: 'Inter', sans-serif; /* Cleaner font for details */
        -webkit-user-select: none; user-select: none;
    }

    /* MAIN CONTAINER CARD */
    .main-card {
        background: white;
        border-radius: 6px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .card-header {
        background: white;
        padding: 15px 20px;
        border-bottom: 1px solid #e5e7eb;
        font-family: 'Bree Serif', serif;
        font-size: 18px;
        font-weight: 700;
        display: flex; align-items: center; gap: 10px;
        color: #111827;
    }

    /* ORDER LIST ITEM */
    .order-item {
        padding: 15px 20px;
        border-bottom: 1px solid #f3f4f6;
    }
    .order-item:last-child { border-bottom: none; }

    /* DATA ROWS */
    .data-row {
        display: flex;
        margin-bottom: 8px;
        font-size: 14px;
        color: #1f2937;
        align-items: baseline;
    }

    .label {
        font-weight: 700; /* BOLD Labels */
        color: #000;
        width: 100px; /* Fixed width for alignment */
        flex-shrink: 0;
    }

    .value {
        font-weight: 500;
        color: #374151;
    }

    /* STATUS & BUTTON */
    .status-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 5px;
    }

    /* Status Colors */
    .st-pending { color: #eab308; font-weight: 700; } /* Yellow */
    .st-completed { color: #16a34a; font-weight: 700; } /* Green */
    .st-cancelled { color: #dc2626; font-weight: 700; } /* Red */

    /* Pay Now Button (Purple) */
    .pay-btn {
        background-color: #5b21b6; /* Deep Purple */
        color: white;
        padding: 6px 16px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    .pay-btn:hover { background-color: #4c1d95; }

</style>

<div class="container mx-auto px-4 py-8 mb-20 max-w-lg">
    
    <div class="main-card">
        
        <div class="card-header">
            <i class="fa-solid fa-list-ul"></i> My Orders
        </div>
        
        <div class="flex flex-col">
            <?php 
            $sql = "SELECT o.*, g.name as gname, p.name as pname 
                    FROM orders o 
                    JOIN games g ON o.game_id = g.id 
                    JOIN products p ON o.product_id = p.id 
                    WHERE o.user_id=$uid ORDER BY o.id DESC";
            $res = $conn->query($sql);
            
            if($res && $res->num_rows > 0):
                while($row = $res->fetch_assoc()): 
                    $st = strtolower($row['status']);
                    $stClass = 'st-pending';
                    if($st == 'completed') $stClass = 'st-completed';
                    if($st == 'cancelled') $stClass = 'st-cancelled';
                    
                    $displayAmount = (float)($row['amount'] ?? 0.00);
                    $date = date('d-m-Y h:i:s A', strtotime($row['created_at']));
            ?>
            
            <div class="order-item">
                <div class="data-row">
                    <span class="label">Order ID:</span>
                    <span class="value"><?php echo $row['id']; ?></span>
                </div>

                <div class="data-row">
                    <span class="label">Date:</span>
                    <span class="value"><?php echo $date; ?></span>
                </div>

                <div class="data-row">
                    <span class="label">Game:</span>
                    <span class="value font-bold"><?php echo $row['gname']; ?></span>
                </div>

                <div class="data-row">
                    <span class="label">Package:</span>
                    <span class="value uppercase"><?php echo $row['pname']; ?></span>
                </div>
                
                <?php if(!empty($row['player_id'])): ?>
                <div class="data-row">
                    <span class="label">Player ID:</span>
                    <span class="value"><?php echo $row['player_id']; ?></span>
                </div>
                <?php endif; ?>

                <div class="data-row">
                    <span class="label">Price:</span>
                    <span class="value">৳<?php echo number_format($displayAmount); ?></span>
                </div>

                <div class="status-row">
                    <div class="data-row mb-0">
                        <span class="label">Status:</span>
                        <span class="<?php echo $stClass; ?>"><?php echo $st; ?></span>
                    </div>

                    <?php if($st == 'pending'): ?>
                        <a href="#" class="pay-btn">Pay Now</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php 
                endwhile; 
            else: 
            ?>
                <div class="p-10 text-center">
                    <i class="fa-solid fa-cart-shopping text-gray-200 text-5xl mb-4"></i>
                    <p class="text-gray-500 font-bold">No orders found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
