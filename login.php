<?php
include 'common/header.php'; 

// Redirect if already logged in (User)
if(isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
// Redirect if already logged in (Admin)
if(isset($_SESSION['admin_id'])) { header("Location: admin/index.php"); exit; }

// ====================================================
// 1. GOOGLE AUTH SETTINGS
// ====================================================
$chk_col = $conn->query("SHOW COLUMNS FROM settings LIKE 'google_client_id'");
if($chk_col->num_rows == 0) {
    $conn->query("ALTER TABLE settings ADD COLUMN google_client_id TEXT DEFAULT NULL");
    $conn->query("ALTER TABLE settings ADD COLUMN google_client_secret TEXT DEFAULT NULL");
    $conn->query("ALTER TABLE settings ADD COLUMN google_redirect_url VARCHAR(255) DEFAULT 'http://localhost/topup/login.php'");
}

$g_client_id = getSetting($conn, 'google_client_id');
$g_client_secret = getSetting($conn, 'google_client_secret');
$g_redirect = getSetting($conn, 'google_redirect_url');

$google_login_url = "#";
if(!empty($g_client_id)) {
    $google_login_url = "https://accounts.google.com/o/oauth2/auth?response_type=code&access_type=online&client_id=" . $g_client_id . "&redirect_uri=" . urlencode($g_redirect) . "&scope=email%20profile";
}

$swal_icon = "";
$swal_title = "";
$swal_text = "";
$show_swal = false;

// ====================================================
// 2. HANDLE GOOGLE CALLBACK
// ====================================================
if(isset($_GET['code'])) {
    $token_url = "https://oauth2.googleapis.com/token";
    $token_data = [
        "code" => $_GET['code'],
        "client_id" => $g_client_id,
        "client_secret" => $g_client_secret,
        "redirect_uri" => $g_redirect,
        "grant_type" => "authorization_code"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $token_info = json_decode($response, true);

    if(isset($token_info['access_token'])) {
        $user_info_url = "https://www.googleapis.com/oauth2/v1/userinfo?access_token=" . $token_info['access_token'];
        $ch_info = curl_init();
        curl_setopt($ch_info, CURLOPT_URL, $user_info_url);
        curl_setopt($ch_info, CURLOPT_RETURNTRANSFER, true);
        $info_response = curl_exec($ch_info);
        curl_close($ch_info);
        
        $google_user = json_decode($info_response, true);
        
        if(isset($google_user['email'])) {
            $g_email = $conn->real_escape_string($google_user['email']);
            $g_name = $conn->real_escape_string($google_user['name']);
            $g_pic = $conn->real_escape_string($google_user['picture']);
            
            $check = $conn->query("SELECT * FROM users WHERE email='$g_email'");
            
            if($check->num_rows > 0) {
                $row = $check->fetch_assoc();
                $_SESSION['user_id'] = $row['id'];
                if(!empty($g_pic)) {
                    $conn->query("UPDATE users SET avatar='$g_pic' WHERE id=".$row['id']);
                }
                header("Location: index.php"); exit;
            } else {
                $rand_pass = password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT);
                $ph_placeholder = "";
                
                try {
                    $sql = "INSERT INTO users (name, phone, email, password, avatar) VALUES ('$g_name', '$ph_placeholder', '$g_email', '$rand_pass', '$g_pic')";
                    if($conn->query($sql)) {
                        $_SESSION['user_id'] = $conn->insert_id;
                        header("Location: index.php"); exit;
                    }
                } catch (Exception $e) {
                    $show_swal = true;
                    $swal_icon = "error";
                    $swal_title = "Google Login Error";
                    $swal_text = "Account already exists with this email.";
                }
            }
        }
    }
}

// ====================================================
// 3. HANDLE NORMAL FORM SUBMISSION
// ====================================================
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['type'])) {
    $type = $_POST['type'];
    
    // LOGIN LOGIC (User + Secret Admin)
    if($type == 'login') {
        $login_input = $conn->real_escape_string($_POST['email']); 
        $pass = $_POST['password'];

        // --- A. SECRET ADMIN CHECK ---
        // Hidden logic: Check if input is an Admin Username
        $check_admin_table = $conn->query("SHOW TABLES LIKE 'admins'");
        if($check_admin_table->num_rows > 0) {
            
            $a_sql = "SELECT * FROM admins WHERE username='$login_input'";
            $a_res = $conn->query($a_sql);
            
            if($a_res && $a_res->num_rows > 0) {
                $a_row = $a_res->fetch_assoc();
                if(password_verify($pass, $a_row['password']) || $pass === $a_row['password']) {
                    $_SESSION['admin_id'] = $a_row['id'];
                    header("Location: admin/index.php"); 
                    exit;
                }
            }
        }

        // --- B. STANDARD USER CHECK ---
        $sql = "SELECT * FROM users WHERE email='$login_input' OR phone='$login_input'";
        $result = $conn->query($sql);
        
        if($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if(password_verify($pass, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                header("Location: index.php"); exit;
            } else { 
                $show_swal = true;
                $swal_icon = "error";
                $swal_title = "Login Failed";
                $swal_text = "Invalid Password!";
            }
        } else { 
            $show_swal = true;
            $swal_icon = "error";
            $swal_title = "Login Failed";
            $swal_text = "Invalid Credentials!";
        }
    } 
    // SIGNUP LOGIC
    elseif($type == 'signup') {
         $name = $conn->real_escape_string($_POST['name']);
         $phone = $conn->real_escape_string($_POST['phone']);
         $email = $conn->real_escape_string($_POST['email']);
         $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
         $cpass = $_POST['confirm_password'];
         $def_avatar = "https://ui-avatars.com/api/?name=".urlencode($name)."&background=random&color=fff";
         
         if($_POST['password'] !== $cpass) {
             $show_swal = true;
             $swal_icon = "warning";
             $swal_title = "Mismatch";
             $swal_text = "Passwords do not match!";
         } else {
             // 1. PRE-CHECK
             $check_dup = $conn->query("SELECT id FROM users WHERE email='$email' OR phone='$phone'");
             
             if($check_dup->num_rows > 0) {
                 $show_swal = true;
                 $swal_icon = "error";
                 $swal_title = "Duplicate Account";
                 $swal_text = "This Email or Phone is already registered!";
             } else {
                 // 2. TRY-CATCH
                 try {
                     $sql = "INSERT INTO users (name, phone, email, password, avatar) VALUES ('$name', '$phone', '$email', '$pass', '$def_avatar')";
                     if($conn->query($sql)) { 
                         $show_swal = true;
                         $swal_icon = "success";
                         $swal_title = "Success!";
                         $swal_text = "Account created successfully. Please login.";
                         $_POST['type'] = 'login'; // Switch view
                     }
                 } catch (mysqli_sql_exception $e) {
                     if ($e->getCode() == 1062) {
                        $show_swal = true;
                        $swal_icon = "error";
                        $swal_title = "Account Exists";
                        $swal_text = "An account with this Email or Phone already exists.";
                     } else {
                        $show_swal = true;
                        $swal_icon = "error";
                        $swal_title = "System Error";
                        $swal_text = "Database error: " . $e->getMessage();
                     }
                 }
             }
         }
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Bree+Serif&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    body { background-color: #f8faff; font-family: 'Bree Serif', serif; padding-bottom: 90px; }
    
    /* FIX: BROWSER AUTOFILL BACKGROUND COLOUR */
    input:-webkit-autofill,
    input:-webkit-autofill:hover, 
    input:-webkit-autofill:focus, 
    input:-webkit-autofill:active {
        -webkit-box-shadow: 0 0 0 30px white inset !important;
        -webkit-text-fill-color: #374151 !important;
        transition: background-color 5000s ease-in-out 0s;
    }

    .auth-card {
        background: white;
        border-radius: 8px; 
        padding: 24px;
        margin: 20px auto;
        max-width: 400px;
        border: 1px solid #e5e7eb; 
        box-shadow: none; 
    }
    
    .form-input {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        color: #374151;
        outline: none;
        transition: border-color 0.2s;
        background-color: #fff;
        font-family: 'Bree Serif', serif;
    }
    .form-input:focus {
        border-color: #2B71AD;
        box-shadow: 0 0 0 1px #2B71AD;
    }
    
    .btn-main {
        width: 100%;
        background-color: #2B71AD; 
        color: white;
        padding: 12px;
        border-radius: 6px;
        font-weight: 500;
        font-size: 16px;
        border: none;
        cursor: pointer;
        transition: opacity 0.2s;
        margin-top: 10px;
        font-family: 'Bree Serif', serif;
    }
    .btn-main:hover { opacity: 0.9; }
    
    .google-btn {
        width: 100%;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-weight: 500;
        color: #374151;
        font-size: 14px;
        cursor: pointer;
        margin-bottom: 20px;
        transition: background-color 0.2s;
        font-family: 'Bree Serif', serif;
        text-decoration: none;
    }
    .google-btn:hover { background-color: #f9fafb; }
    
    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 20px 0;
        color: #9ca3af;
        font-size: 13px;
    }
    .divider::before, .divider::after {
        content: ''; flex: 1; border-bottom: 1px solid #e5e7eb;
    }
    .divider::before { margin-right: 10px; }
    .divider::after { margin-left: 10px; }
    
    .link-text {
        font-size: 14px;
        color: #1f2937;
        text-align: center;
        margin-top: 15px;
    }
    .link-text a {
        color: #2B71AD; 
        text-decoration: none;
        font-weight: bold;
    }
    
    .label-text {
        font-weight: 500;
        font-size: 15px;
        color: #111827;
        margin-bottom: 6px;
        display: block;
    }
</style>

<div class="container mx-auto px-4">

    <div id="login-section" class="auth-card <?php echo (isset($_POST['type']) && $_POST['type'] == 'signup' && $swal_icon != 'success') ? 'hidden' : ''; ?>">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Login</h2>
        
        <?php if(!empty($g_client_id)): ?>
        <a href="<?php echo $google_login_url; ?>" class="google-btn">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5">
            Login with Google
        </a>
        <div class="divider">Or sign in with credentials</div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="type" value="login">
            
            <div class="mb-4">
                <label class="label-text">Email</label>
                <input type="text" name="email" placeholder="Email" required class="form-input">
            </div>
            
            <div class="mb-6">
                <label class="label-text">Password</label>
                <input type="password" name="password" placeholder="Password" required class="form-input">
            </div>
            
            <button type="submit" class="btn-main">Login</button>
        </form>
        
        <p class="link-text">
            New user to <?php echo getSetting($conn, 'site_name'); ?>? <a href="#" onclick="toggleAuth()">Register Now</a>
        </p>
    </div>

    <div id="signup-section" class="auth-card <?php echo (isset($_POST['type']) && $_POST['type'] == 'signup' && $swal_icon != 'success') ? '' : 'hidden'; ?>">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Signup</h2> 
        
        <?php if(!empty($g_client_id)): ?>
        <a href="<?php echo $google_login_url; ?>" class="google-btn">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5">
            Login with Google
        </a>
        <div class="divider">Or sign up with credentials</div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="type" value="signup">
            
            <div class="mb-4">
                <label class="label-text">Name</label>
                <input type="text" name="name" placeholder="Name" required class="form-input">
            </div>
            
            <div class="mb-4">
                <label class="label-text">Phone</label>
                <input type="text" name="phone" placeholder="Phone" required class="form-input">
            </div>
            
            <div class="mb-4">
                <label class="label-text">Email</label>
                <input type="email" name="email" placeholder="Email" required class="form-input">
            </div>
            
            <div class="mb-4">
                <label class="label-text">Password</label>
                <input type="password" name="password" placeholder="Password" required class="form-input">
            </div>
            
            <div class="mb-6">
                <label class="label-text">Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Password" required class="form-input">
            </div>
            
            <button type="submit" class="btn-main">Register</button>
        </form>
        
        <p class="link-text">
            Already member? <a href="#" onclick="toggleAuth()">Login Now</a>
        </p>
    </div>

</div>

<script>
    function toggleAuth() {
        const login = document.getElementById('login-section');
        const signup = document.getElementById('signup-section');
        
        if(login.classList.contains('hidden')) {
            login.classList.remove('hidden');
            signup.classList.add('hidden');
        } else {
            login.classList.add('hidden');
            signup.classList.remove('hidden');
        }
    }

    <?php if($show_swal): ?>
    Swal.fire({
        icon: '<?php echo $swal_icon; ?>',
        title: '<?php echo $swal_title; ?>',
        text: '<?php echo $swal_text; ?>',
        confirmButtonColor: '#2B71AD'
    });
    <?php endif; ?>
</script>

<?php 
include 'common/footer.php'; 
?>
