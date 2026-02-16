<?php 
include 'common/header.php'; 

// ====================================================
// INITIALIZE VARIABLES
// ====================================================
$editMode = false;
$editId = 0;
$currLink = '';
$currImg = '';

// Handle "Edit" Request
if(isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM sliders WHERE id=$editId");
    if($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $editMode = true;
        $currLink = $row['link'];
        $currImg = $row['image'];
    }
}

// ====================================================
// HANDLE SAVE (ADD / UPDATE)
// ====================================================
if(isset($_POST['save_slider'])) {
    $link = $conn->real_escape_string($_POST['link']);
    $finalImage = $editMode ? $currImg : ''; 

    // Image Upload Logic
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $newFilename = "slider_" . time() . "." . $ext;
            $uploadDir = "../uploads/";
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newFilename)) {
                $finalImage = "uploads/" . $newFilename;
                
                // Delete Old Image if editing
                if($editMode && !empty($currImg) && file_exists("../" . $currImg)) {
                    unlink("../" . $currImg);
                }
            }
        } else {
            echo "<script>alert('Invalid format. Use JPG, PNG, or WEBP.');</script>";
        }
    }

    if(empty($finalImage)) {
        echo "<script>alert('Image is required!');</script>";
    } else {
        if($editMode) {
            // UPDATE
            $uid = (int)$_POST['update_id'];
            $conn->query("UPDATE sliders SET image='$finalImage', link='$link' WHERE id=$uid");
            $msg = "Slider updated successfully.";
        } else {
            // INSERT
            $conn->query("INSERT INTO sliders (image, link) VALUES ('$finalImage', '$link')");
            $msg = "New slider added.";
        }
        
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({ icon: 'success', title: 'Success', text: '$msg', timer: 1000, showConfirmButton: false })
                .then(() => { window.location='sliders.php'; });
            });
        </script>";
    }
}

// ====================================================
// HANDLE DELETE
// ====================================================
if(isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $res = $conn->query("SELECT image FROM sliders WHERE id=$id");
    if($res->num_rows > 0) {
        $img = $res->fetch_assoc()['image'];
        if(!empty($img) && file_exists("../" . $img)) unlink("../" . $img);
    }
    $conn->query("DELETE FROM sliders WHERE id=$id");
    
    // Redirect logic moved to JS for popup, but keeping this as backend fallback
    echo "<script>window.location='sliders.php';</script>";
}
?>

<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&family=Noto+Serif+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* GLOBAL THEME */
    body { font-family: 'Lato', 'Noto Serif Bengali', sans-serif; background-color: #f3f4f6; color: #1f2937; }
    
    /* CARDS */
    .flat-card {
        background: white; border: 1px solid #e5e7eb; border-radius: 6px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 24px;
    }
    
    .flat-header {
        background: #f9fafb; padding: 14px 18px; border-bottom: 1px solid #e5e7eb;
        font-weight: 700; color: #374151; font-size: 13px; text-transform: uppercase;
        letter-spacing: 0.05em; display: flex; justify-content: space-between; align-items: center;
    }

    .flat-body { padding: 20px; }

    /* FORMS */
    .input-label { display: block; font-size: 11px; font-weight: 700; color: #6b7280; margin-bottom: 6px; text-transform: uppercase; }
    
    .input-field {
        width: 100%; border: 1px solid #d1d5db; border-radius: 4px; padding: 10px 12px;
        font-size: 14px; color: #111827; background: #fff; transition: all 0.2s;
    }
    .input-field:focus { border-color: #eab308; outline: none; }

    /* BUTTONS */
    .btn-submit {
        background: #eab308; color: white; padding: 10px; border-radius: 6px; font-weight: 700;
        border: none; cursor: pointer; width: 100%; font-size: 14px; transition: background 0.2s;
    }
    .btn-submit:hover { background: #ca8a04; }

    .btn-cancel {
        background: #f3f4f6; color: #4b5563; padding: 10px; border-radius: 6px; font-weight: 700;
        border: 1px solid #d1d5db; cursor: pointer; text-align: center;
        display: block; text-decoration: none; width: 100%; font-size: 14px;
    }

    /* SLIDER ITEM */
    .slider-item {
        position: relative; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb;
        background: #000; height: 160px;
    }
    .slider-img {
        width: 100%; height: 100%; object-fit: cover; opacity: 0.9;
        transition: transform 0.3s ease;
    }
    .slider-item:hover .slider-img { transform: scale(1.05); opacity: 0.6; }
    
    .slider-actions {
        position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; gap: 10px;
        opacity: 0; transition: opacity 0.2s;
    }
    .slider-item:hover .slider-actions { opacity: 1; }

    .action-circle {
        width: 36px; height: 36px; border-radius: 50%; background: white; 
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; transition: transform 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        cursor: pointer;
    }
    .action-circle:hover { transform: scale(1.1); }
    .btn-edit { color: #2563eb; }
    .btn-del { color: #ef4444; }
    .btn-link { color: #4b5563; }
</style>

<div class="container mx-auto px-4 py-6 max-w-full">
    
    <div class="mb-5">
        <h1 class="text-2xl font-bold text-gray-800">Banner Sliders</h1>
        <p class="text-xs text-gray-500 font-medium mt-0.5">Manage home page promotional banners.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-1">
            <div class="flat-card border-t-4 <?php echo $editMode ? 'border-t-orange-500' : 'border-t-yellow-500'; ?> sticky top-4">
                <div class="flat-header">
                    <span>
                        <i class="fa-solid <?php echo $editMode ? 'fa-pen' : 'fa-plus-circle'; ?> mr-2 text-yellow-600"></i> 
                        <?php echo $editMode ? 'Edit Slider' : 'Add New Banner'; ?>
                    </span>
                </div>
                <div class="flat-body">
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        
                        <?php if($editMode): ?>
                            <input type="hidden" name="update_id" value="<?php echo $editId; ?>">
                        <?php endif; ?>

                        <?php if($editMode && !empty($currImg)): ?>
                        <div>
                            <label class="input-label">Current Image</label>
                            <img src="../<?php echo $currImg; ?>" class="w-full h-32 object-cover rounded border">
                        </div>
                        <?php endif; ?>

                        <div>
                            <label class="input-label"><?php echo $editMode ? 'Replace Image (Optional)' : 'Slider Image'; ?></label>
                            <input type="file" name="image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100 cursor-pointer border border-gray-300 rounded-md p-1" <?php echo $editMode ? '' : 'required'; ?>>
                            <p class="text-[10px] text-gray-400 mt-1">Recommended size: 1200x600px (2:1 Ratio)</p>
                        </div>

                        <div>
                            <label class="input-label">Redirect Link (Optional)</label>
                            <input type="text" name="link" value="<?php echo htmlspecialchars($currLink); ?>" placeholder="https://..." class="input-field">
                        </div>

                        <div class="pt-2 flex gap-2">
                            <button type="submit" name="save_slider" class="btn-submit flex-1">
                                <?php echo $editMode ? 'Update Slider' : 'Add Slider'; ?>
                            </button>
                            <?php if($editMode): ?>
                                <a href="sliders.php" class="btn-cancel flex-1">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="flat-card">
                <div class="flat-header">
                    <span><i class="fa-solid fa-images mr-2 text-gray-400"></i> Active Banners</span>
                    <span class="text-xs font-bold text-gray-400 bg-gray-100 px-2 py-1 rounded">
                        <?php 
                        $cnt = $conn->query("SELECT COUNT(*) as c FROM sliders")->fetch_assoc()['c'];
                        echo $cnt; 
                        ?>
                    </span>
                </div>
                
                <div class="flat-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php 
                        $sliders = $conn->query("SELECT * FROM sliders ORDER BY id DESC");
                        if($sliders->num_rows > 0):
                            while($s = $sliders->fetch_assoc()): 
                        ?>
                        <div class="slider-item group">
                            <img src="../<?php echo $s['image']; ?>" class="slider-img" alt="Slider">
                            
                            <div class="slider-actions">
                                <?php if(!empty($s['link'])): ?>
                                    <a href="<?php echo $s['link']; ?>" target="_blank" class="action-circle btn-link" title="Open Link">
                                        <i class="fa-solid fa-link"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <a href="?edit=<?php echo $s['id']; ?>" class="action-circle btn-edit" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                                <button onclick="confirmDelete(<?php echo $s['id']; ?>)" class="action-circle btn-del" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>

                            <div class="absolute top-2 left-2 bg-black/50 text-white text-[10px] px-2 py-0.5 rounded backdrop-blur-sm">
                                #<?php echo $s['id']; ?>
                            </div>
                        </div>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <div class="col-span-2 text-center py-10 text-gray-400 italic bg-gray-50 rounded border border-dashed border-gray-300">
                                <i class="fa-regular fa-image text-4xl mb-2 opacity-30"></i>
                                <p class="text-xs">No banners found. Add one from the left.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete this banner?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Yes, Delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to delete
                window.location.href = '?del=' + id;
            }
        });
    }
</script>
