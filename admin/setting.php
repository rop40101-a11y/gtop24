<?php 
include 'common/header.php'; 

// ====================================================
// 1. HANDLE FORM SUBMISSION
// ====================================================
if(isset($_POST['update'])) {
    // A. Handle Text Settings
    foreach($_POST as $key => $val) {
        if($key == 'update' || $key == 'new_pass') continue;
        
        $val = $conn->real_escape_string($val);
        
        // Update or Insert
        $check = $conn->query("SELECT id FROM settings WHERE name='$key'");
        if($check->num_rows > 0) {
            $conn->query("UPDATE settings SET value='$val' WHERE name='$key'");
        } else {
            $conn->query("INSERT INTO settings (name, value) VALUES ('$key', '$val')");
        }
    }
    
    // B. Handle App Logo
    if(!empty($_FILES['app_logo']['name'])) {
        $target_dir = "../res/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $target_file = $target_dir . "logo.png"; 
        
        if(move_uploaded_file($_FILES["app_logo"]["tmp_name"], $target_file)) {
            $db_path = "res/logo.png";
            $chk = $conn->query("SELECT id FROM settings WHERE name='site_logo'");
            if($chk->num_rows > 0){
                $conn->query("UPDATE settings SET value='$db_path' WHERE name='site_logo'");
            } else {
                $conn->query("INSERT INTO settings (name, value) VALUES ('site_logo', '$db_path')");
            }
        }
    }
    
    // C. Handle Admin Password
    if(!empty($_POST['new_pass'])) {
        $np = password_hash($_POST['new_pass'], PASSWORD_DEFAULT);
        $aid = $_SESSION['admin_id'];
        $conn->query("UPDATE admins SET password='$np' WHERE id=$aid");
    }
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Saved',
                text: 'Configuration updated successfully.',
                timer: 1500,
                showConfirmButton: false
            });
        });
    </script>";
}

// Helper
function getVal($conn, $key) {
    return htmlspecialchars(getSetting($conn, $key)); 
}
?>

<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&family=Noto+Serif+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* CLASSIC THEME */
    body { font-family: 'Lato', 'Noto Serif Bengali', sans-serif; background-color: #f3f4f6; color: #1f2937; }
    
    /* CARDS */
    .flat-card {
        background: white; border: 1px solid #d1d5db; border-radius: 4px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05); overflow: hidden; height: 100%;
    }
    
    .flat-header {
        background: #f9fafb; padding: 12px 18px; border-bottom: 1px solid #d1d5db;
        font-weight: 800; color: #374151; font-size: 13px; text-transform: uppercase;
        letter-spacing: 0.05em; display: flex; align-items: center; gap: 8px;
    }

    .flat-body { padding: 20px; }

    /* FORM ELEMENTS */
    .input-group { margin-bottom: 15px; }
    .input-label { display: block; font-size: 11px; font-weight: 700; color: #4b5563; margin-bottom: 6px; text-transform: uppercase; }
    
    .input-field, .textarea-field {
        width: 100%; border: 1px solid #9ca3af; border-radius: 4px; padding: 10px 12px;
        font-size: 14px; color: #111827; background: #fff; transition: all 0.2s;
    }
    .input-field:focus, .textarea-field:focus { border-color: #ca8a04; outline: none; }
    
    .input-field.mono { font-family: monospace; font-size: 13px; background: #f8fafc; color: #334155; }

    /* LOGO PREVIEW */
    .logo-preview {
        display: flex; align-items: center; gap: 15px; background: #f8fafc;
        border: 1px dashed #9ca3af; padding: 10px; border-radius: 4px;
    }
    .logo-box {
        width: 50px; height: 50px; background: white; border: 1px solid #e5e7eb;
        display: flex; align-items: center; justify-content: center; border-radius: 4px; padding: 2px;
    }

    /* STICKY FOOTER */
    .sticky-footer {
        position: fixed; bottom: 0; left: 0; width: 100%;
        background: white; border-top: 1px solid #d1d5db; padding: 15px;
        z-index: 50; display: flex; justify-content: flex-end;
        box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    @media (min-width: 768px) {
        .sticky-footer { position: static; background: transparent; border: none; box-shadow: none; padding: 0; margin-top: 20px; }
    }

    /* BUTTONS */
    .btn-save {
        background: #eab308; color: white; font-weight: 800; padding: 12px 30px;
        border-radius: 4px; border: none; cursor: pointer; font-size: 14px; text-transform: uppercase;
        transition: background 0.2s; display: inline-flex; align-items: center; gap: 8px;
    }
    .btn-save:hover { background: #ca8a04; }
</style>

<div class="container mx-auto px-4 py-6 max-w-7xl">
    
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Settings</h1>
        <p class="text-xs text-gray-500 font-medium mt-0.5">Configure system variables.</p>
    </div>

    <form method="POST" enctype="multipart/form-data">
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-20">

            <div class="flat-card">
                <div class="flat-header border-l-4 border-l-gray-600">
                    <i class="fa-solid fa-layer-group text-gray-600"></i> Branding & SEO
                </div>
                <div class="flat-body">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="input-group">
                            <label class="input-label">Site Name</label>
                            <input type="text" name="site_name" value="<?php echo getVal($conn, 'site_name'); ?>" class="input-field">
                        </div>
                        <div class="input-group">
                            <label class="input-label">Currency Symbol</label>
                            <input type="text" name="currency" value="<?php echo getVal($conn, 'currency'); ?>" class="input-field text-center font-bold">
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">Meta Description (SEO)</label>
                        <input type="text" name="site_desc" value="<?php echo getVal($conn, 'site_desc'); ?>" class="input-field" placeholder="Best Gaming Shop...">
                    </div>

                    <div class="input-group">
                        <label class="input-label">Keywords</label>
                        <textarea name="seo_keywords" class="textarea-field h-20 resize-none"><?php echo getVal($conn, 'seo_keywords'); ?></textarea>
                    </div>

                    <div class="input-group">
                        <label class="input-label">App Logo</label>
                        <div class="logo-preview">
                            <div class="logo-box">
                                <img src="../res/logo.png?v=<?php echo time(); ?>" class="max-w-full max-h-full object-contain">
                            </div>
                            <div class="flex-1">
                                <input type="file" name="app_logo" class="input-field text-xs p-1">
                                <p class="text-[10px] text-gray-400 mt-1">Upload PNG to replace current logo.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flat-card">
                <div class="flat-header border-l-4 border-l-yellow-600">
                    <i class="fa-solid fa-address-book text-yellow-600"></i> Social Media & Support
                </div>
                <div class="flat-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="input-group">
                            <label class="input-label">WhatsApp Number</label>
                            <input type="text" name="whatsapp_number" value="<?php echo getVal($conn, 'whatsapp_number'); ?>" class="input-field">
                        </div>
                        <div class="input-group">
                            <label class="input-label">Telegram Link</label>
                            <input type="text" name="telegram_link" value="<?php echo getVal($conn, 'telegram_link'); ?>" class="input-field">
                        </div>
                        <div class="input-group">
                            <label class="input-label">Facebook Page</label>
                            <input type="text" name="facebook" value="<?php echo getVal($conn, 'facebook'); ?>" class="input-field">
                        </div>
                        <div class="input-group">
                            <label class="input-label">Instagram</label>
                            <input type="text" name="instagram" value="<?php echo getVal($conn, 'instagram'); ?>" class="input-field">
                        </div>
                        
                        <div class="input-group md:col-span-2">
                            <label class="input-label">Floating Button (FAB) Link</label>
                            <input type="text" name="fab_link" value="<?php echo getVal($conn, 'fab_link'); ?>" class="input-field" placeholder="https://...">
                            <p class="text-[10px] text-gray-400 mt-1">Link for the floating support button on homepage.</p>
                        </div>

                        <div class="input-group md:col-span-2">
                            <label class="input-label">YouTube Channel</label>
                            <input type="text" name="youtube" value="<?php echo getVal($conn, 'youtube'); ?>" class="input-field">
                        </div>
                        <div class="input-group md:col-span-2">
                            <label class="input-label">Support Email</label>
                            <input type="text" name="contact_email" value="<?php echo getVal($conn, 'contact_email'); ?>" class="input-field">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flat-card">
                <div class="flat-header border-l-4 border-l-gray-800">
                    <i class="fa-solid fa-bullhorn text-gray-800"></i> Notice Board
                </div>
                <div class="flat-body">
                    <div class="input-group">
                        <label class="input-label">Home Page Notice</label>
                        <textarea name="home_notice" class="textarea-field h-32 resize-none"><?php echo getVal($conn, 'home_notice'); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="flat-card">
                <div class="flat-header border-l-4 border-l-red-600">
                    <i class="fa-brands fa-google text-red-600"></i> Google Authentication
                </div>
                <div class="flat-body">
                    <div class="input-group">
                        <label class="input-label">Client ID</label>
                        <input type="text" name="google_client_id" value="<?php echo getVal($conn, 'google_client_id'); ?>" class="input-field mono">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Client Secret</label>
                        <input type="text" name="google_client_secret" value="<?php echo getVal($conn, 'google_client_secret'); ?>" class="input-field mono">
                    </div>
                    <div class="input-group">
                        <label class="input-label">Redirect URL</label>
                        <input type="text" name="google_redirect_url" value="<?php echo getVal($conn, 'google_redirect_url'); ?>" class="input-field mono">
                    </div>
                </div>
            </div>

            <div class="flat-card lg:col-span-2">
                <div class="flat-header border-l-4 border-l-orange-500">
                    <i class="fa-solid fa-fire text-orange-500"></i> Firebase Realtime Database
                </div>
                <div class="flat-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="lg:col-span-3 input-group">
                            <label class="input-label">Database URL</label>
                            <input type="text" name="firebase_database_url" value="<?php echo getVal($conn, 'firebase_database_url'); ?>" class="input-field mono">
                        </div>
                        <div class="input-group">
                            <label class="input-label">API Key</label>
                            <input type="text" name="firebase_api_key" value="<?php echo getVal($conn, 'firebase_api_key'); ?>" class="input-field mono">
                        </div>
                        <div class="input-group">
                            <label class="input-label">Auth Domain</label>
                            <input type="text" name="firebase_auth_domain" value="<?php echo getVal($conn, 'firebase_auth_domain'); ?>" class="input-field mono">
                        </div>
                        <div class="input-group">
                            <label class="input-label">Project ID</label>
                            <input type="text" name="firebase_project_id" value="<?php echo getVal($conn, 'firebase_project_id'); ?>" class="input-field mono">
                        </div>
                        <div class="input-group">
                            <label class="input-label">Storage Bucket</label>
                            <input type="text" name="firebase_storage_bucket" value="<?php echo getVal($conn, 'firebase_storage_bucket'); ?>" class="input-field mono">
                        </div>
                        <div class="input-group">
                            <label class="input-label">Sender ID</label>
                            <input type="text" name="firebase_messaging_sender_id" value="<?php echo getVal($conn, 'firebase_messaging_sender_id'); ?>" class="input-field mono">
                        </div>
                        <div class="input-group">
                            <label class="input-label">App ID</label>
                            <input type="text" name="firebase_app_id" value="<?php echo getVal($conn, 'firebase_app_id'); ?>" class="input-field mono">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flat-card lg:col-span-2" style="border-left: 4px solid #ef4444;">
                <div class="flat-header">
                    <i class="fa-solid fa-lock text-gray-700"></i> Admin Security
                </div>
                <div class="flat-body">
                    <div class="input-group">
                        <label class="input-label">New Admin Password</label>
                        <input type="password" name="new_pass" class="input-field" style="background: #fef2f2; border-color: #fecaca;" placeholder="Enter new password to update...">
                    </div>
                </div>
            </div>

        </div>

        <div class="sticky-footer">
            <div class="max-w-7xl mx-auto w-full flex justify-end">
                <button type="submit" name="update" class="btn-save w-full md:w-auto shadow-lg">
                    <i class="fa-solid fa-floppy-disk"></i> Save Configuration
                </button>
            </div>
        </div>

    </form>
</div>
