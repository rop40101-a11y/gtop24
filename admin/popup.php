<?php 
include 'common/header.php'; 

// ====================================================
// SELF-HEALING DATABASE (Ensure Settings Keys Exist)
// ====================================================
// We use the 'settings' table (key-value store) instead of a separate table 
// to keep the database clean for single-row configurations.
$defaults = [
    'popup_image' => '',
    'popup_link' => '',
    'popup_btn_text' => '',
    'popup_text' => '' // The message body
];

foreach($defaults as $key => $val) {
    // Check if key exists
    $chk = $conn->query("SELECT id FROM settings WHERE name='$key'");
    if($chk->num_rows == 0) {
        // Insert if missing
        $conn->query("INSERT INTO settings (name, value) VALUES ('$key', '$val')");
    }
}

// ====================================================
// HELPER: GET SETTING
// ====================================================
function getAdminSetting($conn, $key) {
    $q = $conn->query("SELECT value FROM settings WHERE name='$key' LIMIT 1");
    return ($q && $q->num_rows > 0) ? $q->fetch_assoc()['value'] : '';
}

// ====================================================
// HANDLE SAVE
// ====================================================
if(isset($_POST['save_popup'])) {
    $link = $conn->real_escape_string($_POST['popup_link']);
    $btn = $conn->real_escape_string($_POST['popup_btn_text']);
    $text = $conn->real_escape_string($_POST['popup_text']); 
    
    // 1. UPDATE TEXT FIELDS
    $conn->query("UPDATE settings SET value='$link' WHERE name='popup_link'");
    $conn->query("UPDATE settings SET value='$btn' WHERE name='popup_btn_text'");
    $conn->query("UPDATE settings SET value='$text' WHERE name='popup_text'");

    // 2. HANDLE IMAGE UPLOAD
    if(isset($_FILES['popup_image']) && $_FILES['popup_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['popup_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $newFilename = "popup_" . time() . "." . $ext;
            $uploadDir = "../uploads/";
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            
            // Delete Old Image to save space
            $oldImg = getAdminSetting($conn, 'popup_image');
            if(!empty($oldImg) && file_exists("../" . $oldImg)) {
                unlink("../" . $oldImg);
            }

            if(move_uploaded_file($_FILES['popup_image']['tmp_name'], $uploadDir . $newFilename)) {
                $dbPath = "uploads/" . $newFilename;
                $conn->query("UPDATE settings SET value='$dbPath' WHERE name='popup_image'");
            }
        } else {
            echo "<script>alert('Invalid file format! Use JPG, PNG or WEBP.');</script>";
        }
    }

    // SUCCESS ALERT
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({ icon: 'success', title: 'Saved', text: 'Popup settings updated successfully.', timer: 1500, showConfirmButton: false });
        });
    </script>";
}

// ====================================================
// HANDLE REMOVE POPUP (Disable)
// ====================================================
if(isset($_POST['remove_popup'])) {
    $oldImg = getAdminSetting($conn, 'popup_image');
    if(!empty($oldImg) && file_exists("../" . $oldImg)) {
        unlink("../" . $oldImg);
    }
    // Clear all fields to disable popup
    $conn->query("UPDATE settings SET value='' WHERE name='popup_image'");
    $conn->query("UPDATE settings SET value='' WHERE name='popup_text'");
    $conn->query("UPDATE settings SET value='' WHERE name='popup_link'");
    $conn->query("UPDATE settings SET value='' WHERE name='popup_btn_text'");
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({ icon: 'success', title: 'Removed', text: 'Popup disabled.', timer: 1500, showConfirmButton: false });
        });
    </script>";
}

// ====================================================
// FETCH CURRENT VALUES
// ====================================================
$currImg = getAdminSetting($conn, 'popup_image');
$currLink = getAdminSetting($conn, 'popup_link');
$currBtn = getAdminSetting($conn, 'popup_btn_text');
$currText = getAdminSetting($conn, 'popup_text');
?>

<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&family=Noto+Serif+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* GLOBAL THEME */
    body { font-family: 'Lato', 'Noto Serif Bengali', sans-serif; background-color: #f3f4f6; color: #1f2937; }

    /* CARD */
    .flat-card {
        background: white; border: 1px solid #d1d5db; border-radius: 6px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 20px;
    }
    
    .flat-header {
        background: #f9fafb; padding: 14px 18px; border-bottom: 1px solid #d1d5db;
        font-weight: 800; color: #374151; font-size: 13px; text-transform: uppercase;
        letter-spacing: 0.05em; display: flex; align-items: center; gap: 8px;
    }

    .flat-body { padding: 20px; }

    /* FORM ELEMENTS */
    .input-label { display: block; font-size: 11px; font-weight: 700; color: #6b7280; margin-bottom: 6px; text-transform: uppercase; }
    
    .input-field, .textarea-field {
        width: 100%; border: 1px solid #9ca3af; border-radius: 4px; padding: 10px 12px;
        font-size: 14px; color: #111827; background: #fff; transition: all 0.2s;
    }
    .input-field:focus, .textarea-field:focus { border-color: #eab308; outline: none; }

    /* PREVIEW BOX */
    .preview-box {
        border: 2px dashed #d1d5db; border-radius: 6px; padding: 20px;
        text-align: center; background: #f8fafc; min-height: 150px;
        display: flex; flex-direction: column; justify-content: center; align-items: center;
    }
    .preview-img {
        max-width: 100%; max-height: 250px; border-radius: 4px; 
        box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: block;
    }

    /* BUTTONS */
    .btn-save {
        background: #eab308; color: white; padding: 12px; border-radius: 4px; font-weight: 800;
        border: none; cursor: pointer; width: 100%; font-size: 14px; text-transform: uppercase;
        transition: background 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-save:hover { background: #ca8a04; }

    .btn-remove {
        background: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 4px; font-weight: 800;
        border: 1px solid #fecaca; cursor: pointer; width: 100%; font-size: 14px; text-transform: uppercase;
        margin-top: 10px; transition: background 0.2s;
    }
    .btn-remove:hover { background: #fecaca; }
</style>

<div class="container mx-auto px-4 py-6 max-w-2xl">
    
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Popup Management</h1>
        <p class="text-xs text-gray-500 font-medium mt-0.5">Configure the promotional modal shown on app open.</p>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="flat-card border-t-4 border-t-yellow-500">
            <div class="flat-header">
                <i class="fa-solid fa-bullhorn text-yellow-600"></i> Popup Configuration
            </div>
            
            <div class="flat-body space-y-5">
                
                <div>
                    <label class="input-label">Current Popup Image</label>
                    <div class="preview-box">
                        <?php if(!empty($currImg)): ?>
                            <img src="../<?php echo $currImg; ?>" class="preview-img">
                            <span class="text-xs font-bold text-green-600 mt-2 bg-green-50 px-2 py-1 rounded border border-green-100">
                                <i class="fa-solid fa-circle-check"></i> Active
                            </span>
                        <?php else: ?>
                            <i class="fa-regular fa-image text-4xl text-gray-300 mb-2"></i>
                            <p class="text-xs text-gray-400 italic">No image uploaded. Popup is disabled.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label class="input-label">Upload Image</label>
                    <input type="file" name="popup_image" accept="image/*" class="input-field text-xs p-1">
                    <p class="text-[10px] text-gray-400 mt-1">Supported: JPG, PNG, WEBP.</p>
                </div>

                <div>
                    <label class="input-label">Popup Message (Optional)</label>
                    <textarea name="popup_text" rows="3" class="textarea-field" placeholder="Write a short message to display below the image..."><?php echo htmlspecialchars($currText); ?></textarea>
                </div>

                <div>
                    <label class="input-label">Button Link (URL)</label>
                    <input type="text" name="popup_link" value="<?php echo htmlspecialchars($currLink); ?>" class="input-field" placeholder="https://t.me/yourchannel">
                </div>

                <div>
                    <label class="input-label">Button Text</label>
                    <input type="text" name="popup_btn_text" value="<?php echo htmlspecialchars($currBtn); ?>" class="input-field" placeholder="e.g. Join Now">
                </div>

                <div class="pt-2">
                    <button type="submit" name="save_popup" class="btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>

                    <?php if(!empty($currImg)): ?>
                        <button type="submit" name="remove_popup" class="btn-remove" onclick="return confirm('Disable popup? This will delete the image.');">
                            <i class="fa-solid fa-trash"></i> Disable Popup
                        </button>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </form>

    <div class="bg-blue-50 border border-blue-100 rounded p-4 flex gap-3 items-start">
        <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
        <div class="text-xs text-blue-800 leading-relaxed">
            <strong>Note:</strong> The popup will appear automatically when users visit the home page. To disable it entirely, click the "Disable Popup" button above.
        </div>
    </div>

</div>
