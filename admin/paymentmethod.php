<?php 
include 'common/header.php'; 

// ====================================================
// HANDLE SETTINGS UPDATE
// ====================================================
if(isset($_POST['update_payment'])) {
    foreach($_POST as $key => $val) {
        if($key == 'update_payment') continue;
        
        $val = $conn->real_escape_string($val);
        
        // Update or Insert into 'settings' table
        $check = $conn->query("SELECT id FROM settings WHERE name='$key'");
        if($check->num_rows > 0) {
            $conn->query("UPDATE settings SET value='$val' WHERE name='$key'");
        } else {
            $conn->query("INSERT INTO settings (name, value) VALUES ('$key', '$val')");
        }
    }
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Saved',
                text: 'Payment details updated successfully.',
                timer: 1500,
                showConfirmButton: false
            });
        });
    </script>";
}

// Helper Function
function getVal($conn, $key) {
    return htmlspecialchars(getSetting($conn, $key)); 
}
?>

<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&family=Noto+Serif+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* CLASSIC FLAT THEME */
    body { font-family: 'Lato', 'Noto Serif Bengali', sans-serif; background-color: #f3f4f6; color: #1f2937; }
    
    /* CARD STYLE */
    .flat-card {
        background: white; border: 1px solid #d1d5db; border-radius: 4px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05); overflow: hidden; max-width: 600px; margin: 0 auto;
    }
    
    .flat-header {
        background: #f9fafb; padding: 15px 20px; border-bottom: 1px solid #d1d5db;
        font-weight: 800; color: #374151; font-size: 14px; text-transform: uppercase;
        letter-spacing: 0.05em; display: flex; align-items: center; gap: 8px;
    }

    .flat-body { padding: 25px; }

    /* FORM ELEMENTS */
    .input-group { margin-bottom: 20px; }
    .input-label { display: block; font-size: 12px; font-weight: 700; color: #4b5563; margin-bottom: 8px; text-transform: uppercase; }
    
    .input-field {
        width: 100%; border: 1px solid #9ca3af; border-radius: 4px; padding: 12px 14px;
        font-size: 15px; color: #111827; background: #fff; transition: all 0.2s; font-family: 'Lato', monospace;
    }
    .input-field:focus { border-color: #eab308; outline: none; box-shadow: 0 0 0 2px rgba(234, 179, 8, 0.1); }

    /* WALLET ICONS */
    .wallet-icon { width: 24px; display: inline-block; text-align: center; margin-right: 8px; }
    .bkash-color { color: #e2136e; }
    .nagad-color { color: #f7931a; }
    .rocket-color { color: #8c3494; }
    .video-color { color: #ef4444; }

    /* BUTTON */
    .btn-save {
        background: #eab308; color: white; font-weight: 800; padding: 14px 30px;
        border-radius: 4px; border: none; cursor: pointer; font-size: 14px; text-transform: uppercase;
        width: 100%; transition: background 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-save:hover { background: #ca8a04; }
</style>

<div class="container mx-auto px-4 py-8 max-w-full">
    
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Payment Configuration</h1>
        <p class="text-xs text-gray-500 font-medium mt-1">Manage wallet numbers and instruction videos.</p>
    </div>

    <form method="POST">
        <div class="flat-card">
            <div class="flat-header border-l-4 border-l-yellow-500">
                <i class="fa-solid fa-wallet text-yellow-600"></i> Wallet Numbers & Video
            </div>
            
            <div class="flat-body">
                
                <div class="input-group">
                    <label class="input-label"><span class="wallet-icon bkash-color"><i class="fa-solid fa-b"></i></span> bKash Personal/Agent</label>
                    <input type="text" name="admin_bkash_number" value="<?php echo getVal($conn, 'admin_bkash_number'); ?>" class="input-field" placeholder="017xxxxxxxx">
                </div>

                <div class="input-group">
                    <label class="input-label"><span class="wallet-icon nagad-color"><i class="fa-solid fa-n"></i></span> Nagad Personal/Agent</label>
                    <input type="text" name="admin_nagad_number" value="<?php echo getVal($conn, 'admin_nagad_number'); ?>" class="input-field" placeholder="017xxxxxxxx">
                </div>

                <div class="input-group">
                    <label class="input-label"><span class="wallet-icon rocket-color"><i class="fa-solid fa-r"></i></span> Rocket Personal/Agent</label>
                    <input type="text" name="admin_rocket_number" value="<?php echo getVal($conn, 'admin_rocket_number'); ?>" class="input-field" placeholder="017xxxxxxxx">
                </div>

                <hr class="border-gray-200 my-6">

                <div class="input-group">
                    <label class="input-label"><span class="wallet-icon text-blue-600"><i class="fa-solid fa-u"></i></span> Upay Number (Optional)</label>
                    <input type="text" name="admin_upay_number" value="<?php echo getVal($conn, 'admin_upay_number'); ?>" class="input-field" placeholder="017xxxxxxxx">
                </div>

                <div class="input-group">
                    <label class="input-label"><span class="wallet-icon video-color"><i class="fa-brands fa-youtube"></i></span> "How to Add Money" Video URL</label>
                    <input type="text" name="add_money_video" value="<?php echo getVal($conn, 'add_money_video'); ?>" class="input-field text-blue-600 underline" placeholder="https://youtube.com/watch?v=...">
                    <p class="text-[10px] text-gray-400 mt-1">This video will be shown to users in the Add Money section.</p>
                </div>

                <button type="submit" name="update_payment" class="btn-save">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>

            </div>
        </div>
    </form>

</div>
