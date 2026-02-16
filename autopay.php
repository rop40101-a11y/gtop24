<?php 
include 'common/config.php';
// session_start(); // Config starts session

if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

// --- 1. SELF HEALING ---
$fb_cols = [
    'firebase_api_key', 'firebase_auth_domain', 'firebase_database_url', 
    'firebase_project_id', 'firebase_storage_bucket', 'firebase_messaging_sender_id', 'firebase_app_id',
    'admin_bkash_number', 'admin_nagad_number', 'admin_rocket_number'
];
foreach($fb_cols as $col) {
    $chk = $conn->query("SHOW COLUMNS FROM settings LIKE '$col'");
    if($chk->num_rows == 0) {
        $conn->query("ALTER TABLE settings ADD COLUMN $col TEXT DEFAULT NULL");
    }
}

// --- 2. AUTO-SAVE DEFAULTS ---
$chk_data = $conn->query("SELECT firebase_api_key FROM settings LIMIT 1")->fetch_assoc();
if(empty($chk_data['firebase_api_key'])) {
    $def_apikey = "AIzaSyB-OBIwkj_RWzE7aH1dX_sSMO3LTTzh_0U";
    $def_auth = "kapabazar-6215d.firebaseapp.com";
    $def_db = "https://kapabazar-6215d-default-rtdb.firebaseio.com";
    $def_proj = "kapabazar-6215d";
    $def_storage = "kapabazar-6215d.firebasestorage.app";
    $def_sender = "257446278700";
    $def_app = "1:257446278700:web:31e0a8895f5e4690e08389";
    $def_phone = "01797488769";

    $sql = "UPDATE settings SET 
            firebase_api_key='$def_apikey',
            firebase_auth_domain='$def_auth',
            firebase_database_url='$def_db',
            firebase_project_id='$def_proj',
            firebase_storage_bucket='$def_storage',
            firebase_messaging_sender_id='$def_sender',
            firebase_app_id='$def_app',
            admin_bkash_number='$def_phone',
            admin_nagad_number='$def_phone',
            admin_rocket_number='$def_phone'";
    $conn->query($sql);
}

// --- 3. RECEIVE DATA ---
$total = isset($_POST['total_amount']) ? $_POST['total_amount'] : 0;
$game_id = isset($_POST['game_id']) ? $_POST['game_id'] : 0;
$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : 0;
$player_id = isset($_POST['player_id']) ? $_POST['player_id'] : '';
$user_uid = $_SESSION['user_id']; 

// --- 4. FETCH SETTINGS ---
$bkash_no = getSetting($conn, 'admin_bkash_number');
$nagad_no = getSetting($conn, 'admin_nagad_number');
$rocket_no = getSetting($conn, 'admin_rocket_number');

$fb_config = [
    'apiKey' => getSetting($conn, 'firebase_api_key'),
    'authDomain' => getSetting($conn, 'firebase_auth_domain'),
    'databaseURL' => getSetting($conn, 'firebase_database_url'),
    'projectId' => getSetting($conn, 'firebase_project_id'),
    'storageBucket' => getSetting($conn, 'firebase_storage_bucket'),
    'messagingSenderId' => getSetting($conn, 'firebase_messaging_sender_id'),
    'appId' => getSetting($conn, 'firebase_app_id')
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Make Payment</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&display=swap" rel="stylesheet">

    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-firestore-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-database-compat.js"></script>

    <style>
        body { 
            background-color: #f8f9fa; 
            font-family: 'Lato', sans-serif; 
            overflow-x: hidden;
            -webkit-user-select: none; user-select: none;
        }
        input, textarea { -webkit-user-select: text; user-select: text; }

        .sharp-popup {
            border-radius: 0px !important;
            box-shadow: 0 0 0 1000px rgba(0,0,0,0.8);
            aspect-ratio: 3/2;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            width: 90%; max-width: 340px;
        }
        .sharp-btn { border-radius: 0px !important; text-transform: uppercase; letter-spacing: 1px; font-weight: 900; }

        .toolbar {
            background-color: #fff; height: 60px;
            display: flex; align-items: center; padding: 0 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            position: fixed; top: 0; left: 0; right: 0; z-index: 50;
        }

        .method-card {
            background: white; border: 1px solid #eee; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            gap: 8px; cursor: pointer; position: relative; transition: all 0.2s;
            height: 60px; padding: 5px;
        }
        .method-logo { height: auto; max-height: 35px; width: auto; max-width: 50%; object-fit: contain; flex-shrink: 0; }
        .method-name { font-weight: 700; color: #555; font-size: 14px; white-space: nowrap; }
        
        .check-badge {
            position: absolute; top: -6px; right: -6px;
            background: #6200ea; color: white; border-radius: 50%;
            width: 20px; height: 20px; font-size: 10px;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: opacity 0.2s; border: 2px solid white; z-index: 10;
        }

        input[value="bkash"]:checked ~ .grid .card-bkash { border: 1.5px solid #e2136e; background: #fff5f8; }
        input[value="bkash"]:checked ~ .grid .card-bkash .check-badge { opacity: 1; background: #e2136e; }
        input[value="rocket"]:checked ~ .grid .card-rocket { border: 1.5px solid #8c3494; background: #fbf5fc; }
        input[value="rocket"]:checked ~ .grid .card-rocket .check-badge { opacity: 1; background: #8c3494; }
        input[value="nagad"]:checked ~ .grid .card-nagad { border: 1.5px solid #ec1c24; background: #fff5f5; }
        input[value="nagad"]:checked ~ .grid .card-nagad .check-badge { opacity: 1; background: #ec1c24; }

        .pay-container {
            border-radius: 12px; padding: 24px 20px; color: white;
            transition: background-color 0.3s ease;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); margin-top: 24px;
        }
        .bg-bkash { background-color: #e2136e; }
        .bg-rocket { background-color: #8c3494; }
        .bg-nagad { background-color: #f15a29; }

        .trx-input {
            width: 100%; background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3); color: white;
            padding: 14px; border-radius: 8px; outline: none;
            font-size: 15px; margin-top: 10px; margin-bottom: 18px;
            text-align: center; letter-spacing: 1px; font-family: 'Lato', sans-serif;
        }
        .trx-input::placeholder { color: rgba(255,255,255,0.8); }
        
        .instruction-list { list-style: none; padding: 0; margin: 0; font-size: 13px; line-height: 1.7; }
        .instruction-list li { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 8px; }
        .dot { min-width: 8px; height: 8px; background: white; border-radius: 50%; margin-top: 7px; display: inline-block; }
        
        .copy-btn {
            background: rgba(255,255,255,0.25); border: 1px solid rgba(255,255,255,0.4);
            color: white; padding: 3px 10px; border-radius: 6px; font-size: 11px;
            cursor: pointer; margin-left: 8px; display: inline-flex; align-items: center; gap: 5px; font-family: 'Lato', sans-serif;
        }
        
        .verify-btn {
            width: 100%; background: white; font-weight: 900; text-transform: uppercase;
            padding: 14px; border-radius: 8px; border: none; margin-top: 20px;
            cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 16px; font-family: 'Lato', sans-serif;
        }
        .number-highlight { font-weight: bold; color: #fff; background: rgba(0,0,0,0.1); padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 15px; }
    </style>
</head>
<body>

<div class="toolbar">
    <a href="javascript:history.back()" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition">
        <i class="fa-solid fa-arrow-left text-gray-700 text-lg"></i>
    </a>
    <h1 class="font-bold text-lg text-gray-800 ml-2">Add Money</h1>
</div>

<div class="pt-20 px-4 pb-10 max-w-lg mx-auto">
    <input type="radio" name="pay_method" id="pm_bkash" value="bkash" class="hidden" checked onchange="updateUI('bkash')">
    <input type="radio" name="pay_method" id="pm_rocket" value="rocket" class="hidden" onchange="updateUI('rocket')">
    <input type="radio" name="pay_method" id="pm_nagad" value="nagad" class="hidden" onchange="updateUI('nagad')">

    <div class="grid grid-cols-3 gap-3 mb-6">
        <label for="pm_bkash" class="method-card card-bkash">
            <img src="res/images/bkash.png" class="method-logo"><span class="method-name">bKash</span><div class="check-badge"><i class="fa-solid fa-check"></i></div>
        </label>
        <label for="pm_rocket" class="method-card card-rocket">
            <img src="res/images/Rocket.png" class="method-logo"><span class="method-name">Rocket</span><div class="check-badge"><i class="fa-solid fa-check"></i></div>
        </label>
        <label for="pm_nagad" class="method-card card-nagad">
            <img src="res/images/Nagad.png" class="method-logo"><span class="method-name">Nagad</span><div class="check-badge"><i class="fa-solid fa-check"></i></div>
        </label>
    </div>

    <div id="dynamicBox" class="pay-container bg-bkash">
        <h3 class="text-center text-white font-bold text-lg mb-1">ট্রানজেকশন আইডি দিন</h3>
        <input type="text" id="trxID" class="trx-input" placeholder="ট্রানজেকশন আইডি দিন">
        <ul class="instruction-list" id="instructList"></ul>
        <button id="btnVerify" onclick="verifyTransaction()" class="verify-btn text-[#e2136e]">VERIFY</button>
    </div>
</div>

<div id="customPopup" class="fixed inset-0 hidden z-[999] flex items-center justify-center">
    <div class="bg-white p-6 shadow-2xl relative sharp-popup">
        <div class="w-full flex flex-col items-center justify-center text-center">
            <div id="popupIcon" class="text-5xl mb-4"></div>
            <h2 id="popupTitle" class="text-xl font-black mb-2 uppercase tracking-wide text-gray-900"></h2>
            <p id="popupMsg" class="text-gray-600 text-xs mb-6 font-bold leading-relaxed px-4"></p>
            <button onclick="closePopup()" id="popupBtn" class="w-full py-3 text-white sharp-btn">OK</button>
        </div>
    </div>
</div>

<form action="order.php" method="POST" id="legacyForm" class="hidden">
    <input type="hidden" name="action" value="create_order">
    <input type="hidden" name="game_id" value="<?php echo $game_id; ?>">
    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
    <input type="hidden" name="amount" value="<?php echo $total; ?>">
    <input type="hidden" name="player_id" value="<?php echo $player_id; ?>">
    <input type="hidden" name="trx_id" id="legacyTrx">
</form>

<script>
    const paymentData = {
        bkash: { color: 'bg-bkash', textColor: 'text-[#e2136e]', number: '<?php echo $bkash_no; ?>', dial: '*247#', name: 'BKASH' },
        rocket: { color: 'bg-rocket', textColor: 'text-[#8c3494]', number: '<?php echo $rocket_no; ?>', dial: '*322#', name: 'Rocket' },
        nagad: { color: 'bg-nagad', textColor: 'text-[#f15a29]', number: '<?php echo $nagad_no; ?>', dial: '*167#', name: 'NAGAD' }
    };

    function updateUI(method) {
        const data = paymentData[method];
        const box = document.getElementById('dynamicBox');
        const list = document.getElementById('instructList');
        const btn = document.getElementById('btnVerify');

        if(!box || !list || !btn) return;

        box.className = `pay-container ${data.color}`;
        btn.className = `verify-btn ${data.textColor}`;

        list.innerHTML = `
            <li><span class="dot"></span><span>${data.dial} ডায়াল করে আপনার ${data.name} মোবাইল মেনুতে যান অথবা ${data.name} অ্যাপে যান।</span></li>
            <li><span class="dot"></span><span><span class="font-bold text-yellow-300">Send Money</span> - এ ক্লিক করুন।</span></li>
            <li><span class="dot"></span><span>প্রাপক নম্বর হিসেবে নিচের এই নম্বরটি লিখুন</span></li>
            <li class="pl-5 mb-2"><span class="number-highlight">${data.number}</span> <button type="button" class="copy-btn" onclick="copyToClip('${data.number}')"><i class="fa-regular fa-copy"></i> Copy</button></li>
            <li><span class="dot"></span><span>নিশ্চিত করতে এখন আপনার ${data.name} মোবাইল মেনু পিন লিখুন।</span></li>
            <li><span class="dot"></span><span>এখন উপরের বক্সে আপনার Transaction ID এবং Amount দিন আর নিচের VERIFY বাটনে ক্লিক করুন।</span></li>
        `;
    }

    // INITIALIZE ON LOAD
    window.onload = function() {
        updateUI('bkash');
    };

    const firebaseConfig = <?php echo json_encode($fb_config); ?>;
    if (firebaseConfig.apiKey) { firebase.initializeApp(firebaseConfig); }
    const db = firebase.database();
    const firestore = firebase.firestore();
    const currentUserId = "<?php echo $user_uid; ?>";
    const amountExpected = parseFloat("<?php echo $total; ?>");

    function showPopup(type, title, msg) {
        const modal = document.getElementById('customPopup');
        const icon = document.getElementById('popupIcon');
        const tDiv = document.getElementById('popupTitle');
        const mDiv = document.getElementById('popupMsg');
        const btn = document.getElementById('popupBtn');

        modal.classList.remove('hidden');

        if(type === 'success') {
            icon.innerHTML = '<i class="fa-solid fa-circle-check text-green-500"></i>';
            tDiv.innerText = title; mDiv.innerText = msg;
            btn.className = "w-full py-3 text-white sharp-btn bg-green-600";
            btn.onclick = function() { document.getElementById('legacyForm').submit(); };
        } else {
            icon.innerHTML = '<i class="fa-solid fa-circle-xmark text-red-500"></i>';
            tDiv.innerText = title; mDiv.innerText = msg;
            btn.className = "w-full py-3 text-white sharp-btn bg-red-600";
            btn.onclick = closePopup;
        }
    }

    function closePopup() { document.getElementById('customPopup').classList.add('hidden'); }

    async function verifyTransaction() {
        const trxInput = document.getElementById('trxID');
        const btn = document.getElementById('btnVerify');
        const trxId = trxInput.value.trim().toUpperCase();

        if (trxId.length < 4) {
            showPopup('error', 'INVALID FORMAT', 'Please enter a valid Transaction ID.');
            return;
        }

        btn.disabled = true; btn.innerText = "VERIFYING...";

        try {
            const rdbRef = db.ref('XNXANIKPAY/' + trxId);
            const snapshot = await rdbRef.once('value');

            if (snapshot.exists()) {
                const data = snapshot.val();
                const receivedAmount = parseFloat(data.amount || amountExpected);

                const batch = firestore.batch();
                const userRef = firestore.collection('users').doc(currentUserId);
                batch.update(userRef, { walletBalance: firebase.firestore.FieldValue.increment(receivedAmount) });

                const newTrxRef = firestore.collection('transactions').doc();
                batch.set(newTrxRef, {
                    uid: currentUserId, trxId: trxId, amount: receivedAmount,
                    method: 'Auto SMS', status: 'success',
                    timestamp: firebase.firestore.FieldValue.serverTimestamp()
                });

                await batch.commit();
                await rdbRef.remove();

                document.getElementById('legacyTrx').value = trxId;
                showPopup('success', 'PAYMENT SUCCESS', `Verified! ${receivedAmount} Tk Added.`);
            } else {
                showPopup('error', 'NOT FOUND', 'Transaction ID Not Found or Already Used.');
                btn.disabled = false; btn.innerText = "VERIFY";
            }
        } catch (error) {
            showPopup('error', 'SYSTEM ERROR', 'Network error. Please try again.');
            btn.disabled = false; btn.innerText = "VERIFY";
        }
    }

    function copyToClip(text) {
        navigator.clipboard.writeText(text);
        const icon = event.currentTarget.querySelector('i');
        icon.className = "fa-solid fa-check text-green-300";
        setTimeout(() => icon.className = "fa-regular fa-copy", 1500);
    }
</script>

</body>
</html>
