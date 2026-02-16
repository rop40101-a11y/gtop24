<?php 
include 'common/header.php';
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$uid = $_SESSION['user_id'];
?>

<link href="https://fonts.googleapis.com/css2?family=Bree+Serif&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* BACKGROUND SCROLL SETUP */
    body {
        background-image: url('res/backgrounds/bg.png');
        background-repeat: repeat;
        background-size: 100% auto; 
        background-attachment: scroll;
        background-position: top center;
        font-family: 'Inter', sans-serif;
        -webkit-user-select: none; user-select: none;
    }

    /* MAIN CONTAINER CARD (White Background covers everything) */
    .main-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 6px; 
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        overflow: hidden;
        padding-bottom: 10px;
    }

    /* HEADER SECTION inside White Card */
    .card-header-row {
        padding: 15px 20px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-title-box {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .custom-icon {
        width: 28px;
        height: 28px;
        object-fit: contain;
    }

    .page-title {
        font-family: 'Bree Serif', serif;
        font-size: 19px;
        font-weight: 700; /* BOLD TEXT ADDED HERE */
        color: #1e293b;
        margin-top: 2px;
    }

    /* REDEEM BUTTON */
    .btn-redeem {
        background-color: #2B71AD;
        color: white;
        padding: 6px 14px;
        font-weight: 600;
        text-transform: uppercase;
        border-radius: 4px;
        font-size: 11px;
        text-decoration: none;
        display: inline-block;
        transition: opacity 0.2s;
    }
    .btn-redeem:hover { opacity: 0.9; }

    /* TOTAL SPENT BAR */
    .total-bar {
        background: #f8fafc;
        padding: 10px 20px;
        border-bottom: 1px solid #e2e8f0;
        text-align: center;
        font-size: 13px;
        color: #64748b;
        font-weight: 500;
    }
    
    /* VOUCHER ITEM */
    .voucher-item {
        padding: 15px 20px;
        border-bottom: 1px solid #f1f5f9;
    }
    .voucher-item:last-child { border-bottom: none; }

    .voucher-img {
        width: 48px;
        height: 48px;
        border-radius: 4px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
    }

    /* CODE BOX */
    .code-container {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 8px 12px;
        margin-top: 10px;
        border-radius: 4px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    .code-text {
        font-family: monospace;
        font-weight: 600;
        color: #334155;
        font-size: 14px;
        letter-spacing: 0.5px;
    }

    .copy-btn {
        background: white;
        border: 1px solid #cbd5e1;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
        border-radius: 4px;
        font-size: 12px;
    }
    
    .copy-btn:active {
        border-color: #2B71AD;
        color: #2B71AD;
    }
    
    .btn-buy-now {
        background-color: #ef4444;
        color: white;
        padding: 8px 20px;
        font-weight: 600;
        border-radius: 4px;
        font-size: 12px;
        display: inline-block;
        margin-top: 10px;
    }
</style>

<div class="container mx-auto px-4 py-6 mb-20 max-w-lg">
    
    <div class="main-card">
        
        <div class="card-header-row">
            <div class="header-title-box">
                <img src="https://img.icons8.com/?size=100&id=aVHe2jHuORcA&format=png&color=000000" class="custom-icon" alt="Icon">
                <h2 class="page-title">My Codes</h2>
            </div>
            <a href="https://shop.garena.my/app" target="_blank" class="btn-redeem">
                 Redeem Site
            </a>
        </div>

        <?php 
        $total_sql = "SELECT SUM(amount) as total FROM orders WHERE user_id=$uid AND status='completed' AND game_id IN (SELECT id FROM games WHERE type='voucher')";
        $total_res = $conn->query($total_sql);
        $total_val = ($total_res) ? (float)$total_res->fetch_assoc()['total'] : 0;
        ?>
        <div class="total-bar">
            Total Spent : <span class="text-[#2B71AD] font-bold">৳ <?php echo number_format($total_val, 2); ?></span>
        </div>

        <div class="voucher-list">
            <?php 
            $sql = "SELECT rc.code, p.name as pname, g.name as gname, g.image, o.id as order_id, o.amount, o.transaction_id, o.created_at 
                    FROM redeem_codes rc 
                    JOIN orders o ON rc.order_id = o.id 
                    JOIN products p ON o.product_id = p.id
                    JOIN games g ON o.game_id = g.id
                    WHERE o.user_id = $uid AND o.status='completed'
                    ORDER BY o.id DESC";
            
            $res = $conn->query($sql);
            if($res && $res->num_rows > 0):
            while($row = $res->fetch_assoc()): 
            ?>
                <div class="voucher-item">
                    <div class="flex gap-3 items-center mb-1">
                        <img src="<?php echo $row['image']; ?>" class="voucher-img">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-800 text-sm leading-tight"><?php echo $row['gname']; ?></h3>
                            <p class="text-[11px] text-gray-500 font-normal"><?php echo $row['pname']; ?></p>
                            <p class="text-[10px] text-gray-400 mt-0.5">Order #<?php echo $row['order_id']; ?></p>
                        </div>
                        <div class="text-right">
                             <span class="font-bold text-[#2B71AD] text-sm">৳ <?php echo number_format($row['amount'], 2); ?></span>
                             <div class="text-[10px] text-gray-400 mt-0.5"><?php echo date('d M', strtotime($row['created_at'])); ?></div>
                        </div>
                    </div>

                    <div class="code-container">
                        <div class="flex-1 overflow-hidden">
                            <p class="text-[9px] text-gray-400 uppercase font-semibold mb-0.5">REDEEM CODE</p>
                            <code class="code-text select-all break-all"><?php echo $row['code']; ?></code>
                        </div>
                        <button type="button" onclick="copyToClipboard('<?php echo $row['code']; ?>', this)" class="copy-btn">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                </div>
            <?php endwhile; else: ?>
                <div class="p-10 text-center">
                    <i class="fa-solid fa-ticket text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500 font-medium text-sm mb-3">No codes found yet!</p>
                    <a href="index.php" class="btn-buy-now">BUY NOW</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const icon = btn.querySelector('i');
            icon.className = "fa-solid fa-check";
            btn.style.borderColor = "#2B71AD";
            btn.style.color = "#2B71AD";
            
            setTimeout(() => {
                icon.className = "fa-regular fa-copy";
                btn.style.borderColor = "#cbd5e1";
                btn.style.color = "#64748b";
            }, 1500);
        });
    }
</script>

<?php include 'common/bottom.php'; ?>
