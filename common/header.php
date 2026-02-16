<?php 
// 1. Include Config
include_once __DIR__ . '/config.php'; 

// ====================================================
// SELF-HEALING: Fix 'avatar' column missing error
// ====================================================
if(isset($conn) && $conn) {
    $colCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'avatar'");
    if($colCheck->num_rows == 0) {
        $conn->query("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL");
    }
}

// ====================================================
// SETTINGS FETCH LOGIC
// ====================================================
if (!function_exists('getSetting')) { 
    function getSetting($conn, $key) { 
        if(!$conn) return ''; 
        $q = $conn->query("SELECT value FROM settings WHERE name='$key' LIMIT 1");
        return ($q && $q->num_rows > 0) ? $q->fetch_assoc()['value'] : '';
    } 
}

// Default Values
$site_name = "TopupBD";
$site_logo_path = "res/logo.png";

if(isset($conn) && $conn) {
    $db_name = getSetting($conn, 'site_name');
    $db_logo = getSetting($conn, 'site_logo');

    if(!empty($db_name)) $site_name = $db_name;
    if(!empty($db_logo)) $site_logo_path = $db_logo;
}

$logo_url = $site_logo_path . "?v=" . time(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <meta name="theme-color" content="#2B71AD">
    <meta name="msapplication-navbutton-color" content="#2B71AD">
    <meta name="apple-mobile-web-app-status-bar-style" content="#2B71AD">
    
    <title><?php echo htmlspecialchars($site_name); ?></title>
    
    <link rel="icon" type="image/png" href="<?php echo $logo_url; ?>">
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Lato:wght@400;700&family=Noto+Serif+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* GLOBAL SCROLLBAR */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* HEADER: High Transparency Glassmorphism */
        #main-header-secure {
            background-color: rgba(255, 255, 255, 0.1) !important; 
            backdrop-filter: blur(12px) !important; 
            -webkit-backdrop-filter: blur(12px) !important;
            border-bottom: 1px solid rgba(203, 213, 225, 0.4) !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 50 !important; 
            width: 100% !important;
            height: 70px !important;
        }

        /* SIDEBAR Z-Index */
        #user-sidebar, .sidebar, .offcanvas, .drawer, .mobile-menu { z-index: 9999999 !important; }
        .sidebar-backdrop, .drawer-overlay, .offcanvas-backdrop { z-index: 9999998 !important; }

        /* BIGGER LOGO */
        #secure-logo-img {
            height: 70px !important; 
            width: auto !important;
            object-fit: contain !important;
            display: block !important;
        }

        /* BALANCE PILL STYLES */
        .secure-balance-box {
            background-color: #2B71AD !important;
            color: #ffffff !important;
            border: 1px solid #ffffff !important;
            border-radius: 50px !important; 
            height: 38px !important; 
            padding: 0 8px !important; 
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important; 
            min-width: 60px !important;
            text-decoration: none !important;
            box-shadow: 0 2px 4px rgba(43, 113, 173, 0.2) !important;
        }

        .secure-balance-text {
            font-family: 'Noto Serif Bengali', serif !important;
            font-weight: 600 !important;
            line-height: 1 !important;
            white-space: nowrap !important;
            padding-top: 2px; 
        }

        /* --- MINI AUTH BUTTONS --- */
        .btn-auth-login {
            background: transparent;
            color: #2B71AD; 
            
            /* Border 2px */
            border: 2px solid #2B71AD; 
            
            padding: 4px 12px;
            font-size: 12px;
            border-radius: 6px; 
            
            /* Font Lato, Weight Normal */
            font-weight: 400;
            font-family: 'Lato', sans-serif;
            
            text-transform: uppercase;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-auth-login:hover { background-color: rgba(43, 113, 173, 0.1); }

        .btn-auth-register {
            background: #2B71AD; 
            color: white;
            border: 1px solid #2B71AD;
            
            padding: 4px 12px; 
            font-size: 12px;
            border-radius: 6px; 
            
            /* Font Lato, Weight Normal */
            font-weight: 400;
            font-family: 'Lato', sans-serif;
            
            text-transform: uppercase;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-auth-register:hover { opacity: 0.9; }

        body { font-family: 'Outfit', sans-serif; background: #f8fafc; }
    </style>
</head>
<body class="bg-[#f0f5f9] text-gray-800 pb-24">

<header id="main-header-secure">
    <div class="container mx-auto px-4 flex justify-between items-center h-full gap-5">
        
        <div class="flex items-center">
            <a href="index.php" class="flex items-center">
                <img id="secure-logo-img" src="<?php echo $logo_url; ?>" alt="<?php echo htmlspecialchars($site_name); ?>">
            </a>
        </div>

        <div class="flex items-center gap-3">
            <?php if(isset($_SESSION['user_id'])): 
                $u_data = ['balance' => 0, 'name' => 'User', 'avatar' => ''];
                $uid = $_SESSION['user_id'];

                if(isset($conn) && $conn) {
                    $u_res = $conn->query("SELECT balance, name, avatar FROM users WHERE id=$uid");
                    if($u_res && $u_res->num_rows > 0) {
                        $u_data = $u_res->fetch_assoc();
                    }
                }
                
                $balStr = number_format($u_data['balance'], 0);
                $digits = strlen(str_replace(',', '', $balStr));
                
                $fSize = '15px'; 
                if($digits > 4) $fSize = '13px'; 
                if($digits > 6) $fSize = '11px'; 

                $avatarUrl = !empty($u_data['avatar']) ? $u_data['avatar'] : "https://ui-avatars.com/api/?name=" . urlencode($u_data['name']) . "&background=f3f4f6&color=333&length=1&font-size=0.5&bold=true";
            ?>
                <div class="secure-balance-box" style="font-size: <?php echo $fSize; ?> !important;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="opacity-90">
                        <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4" />
                        <path d="M3 5v14a2 2 0 0 0 2 2h16v-5" />
                        <path d="M18 12a2 2 0 0 0 0 4h4v-4Z" />
                    </svg>
                    <span class="secure-balance-text"><?php echo $balStr; ?>&#2547;</span>
                </div>

                <button onclick="toggleUserSidebar()" class="w-10 h-10 rounded-full bg-gray-100 overflow-hidden relative focus:outline-none border border-gray-100" style="z-index: 10001;">
                    <img src="<?php echo $avatarUrl; ?>" class="w-full h-full object-cover block">
                </button>

            <?php else: ?>
                <div class="flex items-center gap-2">
                    <a href="login.php" class="btn-auth-login">
                        Login
                    </a>
                    <a href="login.php?action=signup" class="btn-auth-register">
                        Register
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>
<?php include 'sidebar.php'; ?>
