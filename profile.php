<?php 
include 'common/header.php';
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$uid = $_SESSION['user_id'];

// 1. AUTO-FIX DATABASE (Add support_pin column if missing)
if(isset($conn)) {
    $check_col = $conn->query("SHOW COLUMNS FROM users LIKE 'support_pin'");
    if($check_col->num_rows == 0) {
        $conn->query("ALTER TABLE users ADD COLUMN support_pin INT DEFAULT 0");
    }
}

// 2. FETCH USER DATA
$u = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();

// 3. GENERATE SUPPORT PIN IF EMPTY
if(empty($u['support_pin']) || $u['support_pin'] == 0) {
    $new_pin = rand(10000, 99999);
    $conn->query("UPDATE users SET support_pin='$new_pin' WHERE id=$uid");
    $u['support_pin'] = $new_pin; 
}

// 4. FETCH STATS
// Total Spent
$total_spent_res = $conn->query("SELECT SUM(amount) as spent FROM orders WHERE user_id=$uid AND status='completed'");
$total_spent = $total_spent_res ? (float)$total_spent_res->fetch_assoc()['spent'] : 0;

// Total Orders
$total_order_res = $conn->query("SELECT COUNT(*) as cnt FROM orders WHERE user_id=$uid");
$total_orders = $total_order_res ? (int)$total_order_res->fetch_assoc()['cnt'] : 0;

// Weekly Spent
$weekly_spent_res = $conn->query("SELECT SUM(amount) as spent FROM orders WHERE user_id=$uid AND status='completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$weekly_spent = $weekly_spent_res ? (float)$weekly_spent_res->fetch_assoc()['spent'] : 0;

// 5. AVATAR LOGIC (Database -> Fallback)
$av_url = !empty($u['avatar']) ? $u['avatar'] : "https://ui-avatars.com/api/?name=".urlencode($u['name'])."&background=random&color=fff";
?>

<style>
    /* BODY BACKGROUND IMAGE SETTING */
    body { 
        background-color: #f0f5f9; /* Fallback color */
        background-image: url('res/backgrounds/bg.png'); 
        
        /* FIX: 100% Width = Fits mobile screen width exactly */
        background-size: 100%; 
        
        background-repeat: repeat-y; /* Repeat vertically as you scroll */
        background-position: top center; 
        background-attachment: scroll; /* Scrolls with content */
        
        min-height: 100vh;
        padding-bottom: 90px; 
    }
    
    .font-bree { font-family: 'Bree Serif', serif; }
    .font-bangla { font-family: 'Noto Serif Bengali', serif; }
    
    .profile-card {
        background: white;
        /* Main Color Border */
        border: 1px solid #2B71AD; 
        border-radius: 8px;
        text-align: center;
        padding: 15px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }
    
    .stat-val {
        color: #2B71AD;
        font-weight: 700;
        font-size: 16px;
        font-family: 'Bree Serif', serif;
    }
    
    .stat-label {
        color: #1f2937;
        font-size: 12px;
        font-weight: 700;
        margin-top: 4px;
        font-family: 'Bree Serif', serif;
    }

    .verified-badge {
        color: #3b82f6; 
        font-size: 40px;
    }
</style>

<div class="container mx-auto px-4 py-6">

    <div class="flex flex-col items-center mb-6">
        <div class="w-28 h-28 rounded-full p-1 shadow-lg mb-3 flex items-center justify-center bg-white" style="background: linear-gradient(to bottom, #ffffff, #2B71AD);">
            <div class="w-full h-full rounded-full p-[3px] bg-white flex items-center justify-center">
                <img src="<?php echo $av_url; ?>" class="w-full h-full rounded-full object-cover border-2 border-white">
            </div>
        </div>
        
        <h2 class="text-[#2B71AD] text-sm font-bold">Hi, <?php echo htmlspecialchars($u['name']); ?></h2>
        <p class="text-gray-600 text-xs font-bold mt-1 flex items-center gap-1">
            Available Balance : <?php echo (int)$u['balance']; ?> Tk <i class="fa-solid fa-rotate-right text-xs cursor-pointer" onclick="location.reload()"></i>
        </p>
    </div>

    <div class="grid grid-cols-2 gap-3 mb-6">
        <div class="profile-card">
            <div class="stat-val"><?php echo $u['support_pin']; ?></div>
            <div class="stat-label">Support Pin</div>
        </div>

        <div class="profile-card">
            <div class="stat-val"><?php echo (int)$weekly_spent; ?> ৳</div>
            <div class="stat-label">Weeklly Spent</div>
        </div>

        <div class="profile-card">
            <div class="stat-val"><?php echo (int)$total_spent; ?></div>
            <div class="stat-label">Total Spent</div>
        </div>

        <div class="profile-card">
            <div class="stat-val"><?php echo $total_orders; ?></div>
            <div class="stat-label">Total Order</div>
        </div>
    </div>

    <div class="bg-white rounded shadow-sm border border-gray-200 mb-6">
        <div class="border-b border-gray-100 px-4 py-3">
            <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                <i class="fa-regular fa-folder-open"></i> Account Information
            </h3>
        </div>
        
        <div class="p-6 text-center">
            <div class="border border-gray-200 rounded-lg p-4 mb-6 shadow-sm inline-block min-w-[250px]">
                <div class="flex justify-center items-center gap-2 text-gray-500 text-sm font-bold mb-2">
                    <i class="fa-solid fa-rotate-right border p-1 rounded"></i> ৳ <?php echo number_format($u['balance'], 2); ?>
                </div>
                <h2 class="font-bold text-black text-lg font-bree">Available Balance</h2>
            </div>

            <div class="border border-gray-200 rounded-lg p-4 shadow-sm">
                <div class="flex justify-center mb-2 relative">
                    <i class="fa-solid fa-certificate verified-badge"></i>
                    <i class="fa-solid fa-check text-white absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-sm"></i>
                </div>
                <h2 class="font-bold text-black text-lg font-bree">Account Verified!</h2>
            </div>
        </div>
    </div>

    <div class="bg-white rounded shadow-sm border border-gray-200 mb-6">
        <div class="border-b border-gray-100 px-4 py-3">
            <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-info"></i> User Information
            </h3>
        </div>
        
        <div class="p-4">
            <div class="text-xs font-bold text-gray-800 space-y-2">
                <p><span class="text-gray-900">email :</span> <?php echo htmlspecialchars($u['email']); ?></p>
                <p><span class="text-gray-900">Phone :</span> <?php echo htmlspecialchars($u['phone']); ?></p>
            </div>
        </div>
    </div>

</div>

<?php include 'common/footer.php'; ?>
<?php include 'common/bottom.php'; ?>
