<?php 
// Adjust path to config based on folder structure
include '../../common/config.php';

if(!isset($_SESSION['user_id'])) { 
    header("Location: ../../login.php"); 
    exit; 
}

// ==========================================
// 1. AJAX HANDLER FOR ADD MONEY (Background)
// ==========================================
if(isset($_POST['action_type']) && $_POST['action_type'] == 'ajax_add_money') {
    $uid = $_SESSION['user_id'];
    $amt = floatval($_POST['amount']);
    
    // Update User Balance
    $update = $conn->query("UPDATE users SET balance = balance + $amt WHERE id = $uid");
    
    if($update) { echo "success"; } else { echo "error"; }
    exit; 
}

// =========================================================
// 2. PRG PATTERN (Fix for Confirm Form Resubmission)
// =========================================================

// A. Check if data is coming via POST (First Load)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['method'])) {
    // Save data to session
    $_SESSION['payment_data'] = [
        'method'     => $_POST['method'],
        'amount'     => $_POST['amount'],
        'game_id'    => isset($_POST['game_id']) ? $_POST['game_id'] : 0,
        'product_id' => isset($_POST['product_id']) ? $_POST['product_id'] : 0,
        'player_id'  => isset($_POST['player_id']) ? $_POST['player_id'] : ''
    ];

    // Redirect to self to clear POST buffer
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// B. Check if session data exists (Subsequent Loads / Refreshes)
if (!isset($_SESSION['payment_data'])) {
    // No data found, redirect to instantpay
    header("Location: ../../instantpay.php");
    exit;
}

// C. Retrieve variables from Session
$pData = $_SESSION['payment_data'];

$method     = $pData['method'];
$amount     = $pData['amount'];
$game_id    = $pData['game_id'];
$product_id = $pData['product_id'];
$player_id  = $pData['player_id'];

// Detect if this is "Add Money" or "Product Purchase"
$is_add_money = ($game_id == 0 && $product_id == 0) ? true : false;

// --- FETCH SETTINGS ---
$site_name = getSetting($conn, 'site_name');

// Static Paths (Relative to secure/pay/)
$logo_path = "../../res/logo.png"; 
$bg_path = "../../res/backgrounds/bg.png";

// Fetch Admin Numbers
$bkash_no = getSetting($conn, 'admin_bkash_number');
$nagad_no = getSetting($conn, 'admin_nagad_number');
$rocket_no = getSetting($conn, 'admin_rocket_number');

// Firebase Config
$fb_config = [
    'apiKey' => getSetting($conn, 'firebase_api_key'),
    'authDomain' => getSetting($conn, 'firebase_auth_domain'),
    'databaseURL' => getSetting($conn, 'firebase_database_url'),
    'projectId' => getSetting($conn, 'firebase_project_id'),
    'storageBucket' => getSetting($conn, 'firebase_storage_bucket'),
    'messagingSenderId' => getSetting($conn, 'firebase_messaging_sender_id'),
    'appId' => getSetting($conn, 'firebase_app_id')
];

// --- METHOD CONFIGURATION ---
$configs = [
    'bkash' => [
        'name' => 'bKash',
        'color' => '#e2136e', 
        'number' => $bkash_no,
        'dial' => '*247#'
    ],
    'nagad' => [
        'name' => 'Nagad',
        'color' => '#ec1c24', 
        'number' => $nagad_no,
        'dial' => '*167#'
    ],
    'rocket' => [
        'name' => 'Rocket',
        'color' => '#8c3494', 
        'number' => $rocket_no,
        'dial' => '*322#'
    ]
];

$curr = $configs[$method];
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pay with <?php echo $curr['name']; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-database-compat.js"></script>

    <style>
        body { 
            font-family: 'Lato', 'Noto Sans Bengali', sans-serif; 
            background-color: #f4f7f9;
            background-image: url('<?php echo $bg_path; ?>'); 
            background-repeat: no-repeat;
            background-size: 100% 100%; 
            background-attachment: scroll;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            margin: 0; padding: 0;
        }

        /* Top Bar */
        .top-bar {
            background: white; margin: 15px; padding: 12px 15px;
            border-radius: 12px; display: flex; justify-content: space-between;
            align-items: center; border: 1px solid #e5e7eb; box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }
        .nav-icon { color: #64748b; font-size: 18px; cursor: pointer; transition: color 0.2s; }
        .nav-icon:hover { color: #ef4444; }

        /* Method Logo Banner */
        .method-banner {
            display: flex; justify-content: center; margin: 10px 0 20px 0;
        }
        .method-banner img { height: 60px; object-fit: contain; }

        /* Info Card */
        .info-card {
            background: white; border-radius: 10px; padding: 15px;
            display: flex; align-items: center; gap: 15px;
            margin: 0 20px 15px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .site-thumb { width: 40px; height: 40px; border-radius: 50%; border: 1px solid #eee; object-fit: contain; padding: 2px; }
        .site-text { font-weight: 700; color: #556070; font-size: 16px; }

        /* Amount Card */
        .amount-card {
            background: white; border-radius: 10px; padding: 15px;
            margin: 0 20px 20px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            font-size: 20px; font-weight: 700; color: #374151;
            font-family: 'Lato', sans-serif;
        }

        /* Main Instruction Card */
        .instruct-card {
            background: <?php echo $curr['color']; ?>;
            color: white; border-radius: 12px; padding: 25px 20px;
            margin: 0 20px 100px 20px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .card-header { text-align: center; font-weight: 700; font-size: 16px; margin-bottom: 20px; font-family: 'Noto Sans Bengali', sans-serif; }

        /* Input Field */
        .trx-input {
            width: 100%; padding: 12px; border-radius: 6px; border: none;
            font-size: 14px; color: #333; outline: none; margin-bottom: 20px;
            text-align: center; font-family: 'Lato', sans-serif;
        }
        .trx-input::placeholder { color: #9ca3af; font-family: 'Noto Sans Bengali', sans-serif; }

        /* List Items */
        .steps { list-style: none; padding: 0; font-size: 13px; line-height: 1.8; font-family: 'Noto Sans Bengali', sans-serif; }
        .steps li { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 8px; }
        .steps li:last-child { border-bottom: none; }
        .dot { min-width: 6px; height: 6px; background: white; border-radius: 50%; margin-top: 8px; }

        .highlight-yellow { color: #fde047; font-weight: 700; font-family: 'Lato', sans-serif; }
        
        /* Copy Button */
        .copy-btn {
            background: #374151; color: white; border-radius: 4px; 
            padding: 2px 8px; font-size: 11px; margin-left: 8px; 
            cursor: pointer; display: inline-flex; align-items: center; gap: 4px;
            text-transform: uppercase; font-weight: 600; font-family: 'Lato', sans-serif;
        }

        /* Verify Button (Fixed Bottom) */
        .verify-btn {
            position: fixed; bottom: 20px; left: 20px; right: 20px;
            background: #b91c1c; color: white;
            padding: 15px; border-radius: 8px; text-align: center;
            font-weight: 800; text-transform: uppercase; font-size: 16px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15); border: none; cursor: pointer;
            z-index: 50; font-family: 'Lato', sans-serif;
        }
    </style>
</head>
<body>

    <div class="top-bar">
        <button onclick="confirmBack()" class="nav-icon"><i class="fa-solid fa-circle-left"></i></button>
        
        <div class="flex gap-4">
            <button class="nav-icon"><i class="fa-solid fa-language"></i></button>
            
            <button onclick="confirmCancel()" class="nav-icon"><i class="fa-solid fa-xmark"></i></button>
        </div>
    </div>

    <div class="method-banner">
        <img src="../../res/images/<?php echo $method; ?>.png" alt="<?php echo $curr['name']; ?>">
    </div>

    <div class="info-card">
        <img src="<?php echo $logo_path; ?>" class="site-thumb">
        <div class="site-text"><?php echo $site_name; ?> Pay</div>
    </div>

    <div class="amount-card">
        ৳ <?php echo $amount; ?>
    </div>

    <div class="instruct-card">
        <div class="card-header">ট্রানজেকশন আইডি দিন</div>
        
        <input type="text" id="trxID" class="trx-input" placeholder="ট্রানজেকশন আইডি দিন">

        <ul class="steps">
            <li>
                <div class="dot"></div>
                <div>
                    <span class="highlight-yellow"><?php echo $curr['dial']; ?></span> ডায়াল করে আপনার <span class="highlight-yellow"><?php echo $curr['name']; ?></span> মোবাইল মেনুতে যান অথবা <span class="highlight-yellow"><?php echo $curr['name']; ?></span> অ্যাপে যান।
                </div>
            </li>
            <li>
                <div class="dot"></div>
                <div>
                    "<span class="highlight-yellow">Send Money</span>" -এ ক্লিক করুন।
                </div>
            </li>
            <li>
                <div class="dot"></div>
                <div>
                    প্রাপক নম্বর হিসেবে এই নম্বরটি লিখুন:
                    <div class="mt-1 flex items-center">
                        <span class="highlight-yellow text-lg"><?php echo $curr['number']; ?></span>
                        <button class="copy-btn" onclick="copyToClip('<?php echo $curr['number']; ?>')">
                            <i class="fa-regular fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
            </li>
            <li>
                <div class="dot"></div>
                <div>
                    টাকার পরিমাণ: <span class="highlight-yellow"><?php echo $amount; ?></span>
                </div>
            </li>
            <li>
                <div class="dot"></div>
                <div>
                    নিশ্চিত করতে এখন আপনার <span class="highlight-yellow"><?php echo $curr['name']; ?></span> মোবাইল মেনু পিন লিখুন।
                </div>
            </li>
            <li>
                <div class="dot"></div>
                <div>
                    সবকিছু ঠিক থাকলে, আপনি <span class="highlight-yellow"><?php echo $curr['name']; ?></span> থেকে একটি নিশ্চিতকরণ বার্তা পাবেন।
                </div>
            </li>
            <li>
                <div class="dot"></div>
                <div>
                    এখন উপরের বক্সে আপনার <span class="highlight-yellow">Transaction ID</span> দিন এবং নিচের <span class="highlight-yellow">VERIFY</span> বাটনে ক্লিক করুন।
                </div>
            </li>
        </ul>
    </div>

    <button id="btnVerify" onclick="verifyTransaction()" class="verify-btn">VERIFY</button>

    <form action="../../order.php" method="POST" id="finalForm" class="hidden">
        <input type="hidden" name="action" value="create_order">
        <input type="hidden" name="game_id" value="<?php echo $game_id; ?>">
        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        <input type="hidden" name="amount" value="<?php echo $amount; ?>">
        <input type="hidden" name="player_id" value="<?php echo $player_id; ?>">
        <input type="hidden" name="order_txid" id="finalTrx">
        <input type="hidden" name="payment_method" value="<?php echo $method; ?>">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize Firebase
        const firebaseConfig = <?php echo json_encode($fb_config); ?>;
        if (firebaseConfig.apiKey) { firebase.initializeApp(firebaseConfig); }
        const db = firebase.database();

        function copyToClip(text) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(showToast).catch(() => fallbackCopy(text));
            } else { fallbackCopy(text); }
        }

        function fallbackCopy(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed"; textArea.style.left = "-9999px";
            document.body.appendChild(textArea);
            textArea.focus(); textArea.select();
            try { document.execCommand('copy'); showToast(); } catch (err) {}
            document.body.removeChild(textArea);
        }

        function showToast() {
            const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 1500,
                timerProgressBar: true,
                didOpen: (toast) => { toast.addEventListener('mouseenter', Swal.stopTimer); toast.addEventListener('mouseleave', Swal.resumeTimer); }
            });
            Toast.fire({ icon: 'success', title: 'Number Copied!' });
        }

        // --- BACK CONFIRMATION ---
        function confirmBack() {
            Swal.fire({
                title: 'Are you sure?',
                text: "Going back will cancel this transaction!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, go back',
                cancelButtonText: 'Stay'
            }).then((result) => {
                if (result.isConfirmed) {
                    // CHANGED: Redirect to Instantpay instead of home
                    window.location.href = "../../instantpay.php";
                }
            })
        }

        // --- CANCEL CONFIRMATION ---
        function confirmCancel() {
            Swal.fire({
                title: 'Cancel Payment?',
                text: "Are you sure you want to cancel the payment?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Cancel',
                cancelButtonText: 'No, Continue'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "payment_cancelled.php";
                }
            })
        }

        async function verifyTransaction() {
            const trxInput = document.getElementById('trxID');
            const btn = document.getElementById('btnVerify');
            const trxId = trxInput.value.trim().toUpperCase();
            const expectedAmount = parseFloat("<?php echo $amount; ?>");
            const isAddMoney = <?php echo $is_add_money ? 'true' : 'false'; ?>;

            if (trxId.length < 5) {
                Swal.fire({ icon: 'warning', title: 'Invalid ID', text: 'অনুগ্রহ করে সঠিক ট্রানজেকশন আইডি দিন।', confirmButtonColor: '#d33' });
                return;
            }

            btn.disabled = true; 
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Checking...';
            btn.style.opacity = "0.7";

            try {
                const rdbRef = db.ref('XNXANIKPAY/' + trxId);
                const snapshot = await rdbRef.once('value');

                if (snapshot.exists()) {
                    const data = snapshot.val();
                    const receivedAmount = parseFloat(data.amount);

                    if (receivedAmount >= expectedAmount) {
                        await rdbRef.remove();
                        // Clear session on success
                        <?php unset($_SESSION['payment_data']); ?>

                        if (isAddMoney) {
                            handleWalletUpdate(expectedAmount);
                        } else {
                            document.getElementById('finalTrx').value = trxId;
                            document.getElementById('finalForm').submit();
                        }
                    } else {
                        Swal.fire({ icon: 'error', title: 'Amount Mismatch', text: `পেমেন্ট পাওয়া গেছে কিন্তু টাকার পরিমাণ কম। \nপ্রাপ্ত: ${receivedAmount} Tk \nপ্রয়োজন: ${expectedAmount} Tk`, confirmButtonColor: '#d33' });
                        resetBtn();
                    }
                } else {
                    Swal.fire({ icon: 'error', title: 'Not Found', text: 'ট্রানজেকশন আইডি খুঁজে পাওয়া যায়নি। অনুগ্রহ করে আগে পেমেন্ট করুন।', confirmButtonColor: '#d33' });
                    resetBtn();
                }
            } catch (error) {
                console.error(error);
                Swal.fire({ icon: 'error', title: 'Network Error', text: 'সার্ভারে সংযোগ করা যাচ্ছে না। আবার চেষ্টা করুন।', confirmButtonColor: '#d33' });
                resetBtn();
            }
        }

        function handleWalletUpdate(amount) {
            const formData = new FormData();
            formData.append('action_type', 'ajax_add_money');
            formData.append('amount', amount);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if(data.trim() === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Successful!',
                        text: 'Money has been added to your wallet.',
                        confirmButtonColor: '#28a745'
                    }).then((result) => {
                        window.location.href = '../../profile.php';
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Database Error', text: 'Payment verified but failed to add balance. Contact admin.', confirmButtonColor: '#d33' });
                    resetBtn();
                }
            })
            .catch(error => {
                Swal.fire({ icon: 'error', title: 'Connection Error', text: 'Failed to update balance.', confirmButtonColor: '#d33' });
                resetBtn();
            });
        }

        function resetBtn() {
            const btn = document.getElementById('btnVerify');
            btn.disabled = false;
            btn.innerHTML = 'VERIFY';
            btn.style.opacity = "1";
        }
    </script>
</body>
</html>
