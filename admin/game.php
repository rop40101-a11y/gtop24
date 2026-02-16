<?php 
include 'common/header.php'; 

// ====================================================
// SELF-HEALING DATABASE
// ====================================================
$checkCol = $conn->query("SHOW COLUMNS FROM games LIKE 'api_key'");
if($checkCol->num_rows == 0) $conn->query("ALTER TABLE games ADD COLUMN api_key VARCHAR(255) DEFAULT NULL");

$checkCat = $conn->query("SHOW COLUMNS FROM games LIKE 'category_id'");
if($checkCat->num_rows == 0) $conn->query("ALTER TABLE games ADD COLUMN category_id INT DEFAULT 0");

// ====================================================
// INITIALIZE VARIABLES
// ====================================================
$editMode = false;
$editId = 0;
$name = '';
$type = 'uid';
$desc = '';
$apiKey = '';
$currImg = '';
$catId = 0;

// Edit Mode
if(isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM games WHERE id=$editId");
    if($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $editMode = true;
        $name = $row['name'];
        $type = $row['type'];
        $desc = $row['description'];
        $apiKey = $row['api_key'];
        $currImg = $row['image'];
        $catId = $row['category_id'];
    }
}

// ====================================================
// HANDLE SAVE (ADD/UPDATE)
// ====================================================
if(isset($_POST['save_game'])) {
    $n = $conn->real_escape_string($_POST['name']);
    $t = $_POST['type'];
    $c = (int)$_POST['category_id'];
    $d = $conn->real_escape_string($_POST['description']);
    $k = $conn->real_escape_string($_POST['api_key']);
    
    $finalImage = $editMode ? $currImg : ''; 
    
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $newFilename = "game_" . time() . "." . $ext;
            $uploadDir = "../uploads/";
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newFilename)) {
                $finalImage = "uploads/" . $newFilename;
                if($editMode && !empty($currImg) && file_exists("../" . $currImg)) unlink("../" . $currImg);
            }
        }
    }

    if(empty($finalImage)) {
        echo "<script>alert('Image is required!');</script>";
    } else {
        if($editMode) {
            $uid = (int)$_POST['update_id'];
            $stmt = $conn->prepare("UPDATE games SET name=?, type=?, category_id=?, description=?, image=?, api_key=? WHERE id=?");
            $stmt->bind_param("ssisssi", $n, $t, $c, $d, $finalImage, $k, $uid);
            $msg = "Game updated successfully!";
        } else {
            $stmt = $conn->prepare("INSERT INTO games (name, type, category_id, description, image, api_key) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisss", $n, $t, $c, $d, $finalImage, $k);
            $msg = "New game added successfully!";
        }
        
        if($stmt->execute()) {
            // Success Toast Logic
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });
                    Toast.fire({
                        icon: 'success',
                        title: '$msg'
                    }).then(() => {
                        window.location='game.php';
                    });
                });
            </script>";
        }
    }
}

// ====================================================
// HANDLE DELETE
// ====================================================
if(isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $res = $conn->query("SELECT image FROM games WHERE id=$id");
    if($res->num_rows > 0) {
        $img = $res->fetch_assoc()['image'];
        if(!empty($img) && file_exists("../" . $img)) unlink("../" . $img);
    }
    $conn->query("DELETE FROM games WHERE id=$id");
    echo "<script>window.location='game.php';</script>";
}
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* BASE STYLES */
    body { font-family: 'Inter', 'Noto Sans Bengali', sans-serif; background-color: #f9fafb; color: #111827; }

    /* FLAT CARD */
    .flat-card {
        background: white; border: 1px solid #e5e7eb; border-radius: 8px;
        overflow: hidden; margin-bottom: 24px;
    }
    
    .flat-header {
        background: #fdfdfd; padding: 14px 18px; border-bottom: 1px solid #f3f4f6;
        font-weight: 700; color: #374151; font-size: 14px;
        display: flex; justify-content: space-between; align-items: center;
    }

    .flat-body { padding: 20px; }

    /* INPUTS */
    .flat-label { display: block; font-size: 12px; font-weight: 600; color: #4b5563; margin-bottom: 6px; }
    
    .flat-input, .flat-select, .flat-textarea {
        width: 100%; border: 1px solid #d1d5db; border-radius: 6px;
        padding: 10px 12px; font-size: 14px; color: #1f2937; background: #fff;
        transition: border 0.2s; font-family: inherit;
    }
    .flat-input:focus, .flat-select:focus, .flat-textarea:focus { 
        border-color: #ca8a04; outline: none; background: #fff;
    }

    /* BUTTONS */
    .btn-primary {
        background: #eab308; color: white; padding: 10px 20px; border-radius: 6px;
        font-weight: 600; border: none; cursor: pointer; width: 100%;
        display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px;
    }
    .btn-primary:hover { background: #ca8a04; }

    .btn-cancel {
        background: #ffffff; color: #4b5563; padding: 10px 20px; border-radius: 6px;
        font-weight: 600; border: 1px solid #d1d5db; cursor: pointer; text-align: center;
        display: inline-block; text-decoration: none; font-size: 14px;
    }

    /* GAME LIST */
    .game-item {
        background: white; border: 1px solid #e5e7eb; border-radius: 8px;
        padding: 12px; display: flex; align-items: center; gap: 12px;
    }
    
    .game-thumb {
        width: 48px; height: 48px; border-radius: 6px; object-fit: cover;
        background: #f3f4f6; border: 1px solid #e5e7eb; flex-shrink: 0;
    }
    
    .badge {
        font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: 700; text-transform: uppercase;
    }
    .bg-uid { background: #eff6ff; color: #1e40af; }
    .bg-voucher { background: #fdf2f8; color: #9d174d; }
    .bg-cat { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }

    /* ACTION BUTTONS */
    .btn-icon {
        width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
        border-radius: 6px; font-size: 13px; cursor: pointer; border: 1px solid transparent;
    }
    .btn-edit { background: #fff; border-color: #e5e7eb; color: #4b5563; }
    .btn-del { background: #fff; border-color: #fee2e2; color: #ef4444; }
</style>

<div class="container mx-auto px-4 py-6 max-w-5xl">
    
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Game Manager</h1>
            <p class="text-sm text-gray-500">Add or edit games easily.</p>
        </div>
        <?php if(!$editMode): ?>
            <button onclick="document.getElementById('gameForm').scrollIntoView({behavior: 'smooth'})" class="md:hidden w-full bg-yellow-500 text-white py-2.5 rounded-lg font-bold">
                + Add Game
            </button>
        <?php endif; ?>
    </div>

    <div id="gameForm" class="flat-card border-t-4 border-t-yellow-400">
        <div class="flat-header">
            <span class="flex items-center gap-2">
                <i class="fa-solid <?php echo $editMode ? 'fa-pen-to-square' : 'fa-plus-circle'; ?> text-yellow-600"></i> 
                <?php echo $editMode ? 'Edit Game' : 'Create New Game'; ?>
            </span>
            <?php if($editMode): ?>
                <a href="game.php" class="text-xs text-red-500 font-medium">Cancel</a>
            <?php endif; ?>
        </div>
        <div class="flat-body">
            <form method="POST" enctype="multipart/form-data">
                <?php if($editMode): ?>
                    <input type="hidden" name="update_id" value="<?php echo $editId; ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="flat-label">Game Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" class="flat-input" required>
                    </div>

                    <div>
                        <label class="flat-label">Category</label>
                        <select name="category_id" class="flat-select">
                            <option value="0">Uncategorized</option>
                            <?php 
                            $cat_q = $conn->query("SELECT * FROM categories ORDER BY priority ASC, id ASC");
                            if($cat_q):
                                while($c = $cat_q->fetch_assoc()):
                                    $selected = ($c['id'] == $catId) ? 'selected' : '';
                                    echo "<option value='{$c['id']}' $selected>" . htmlspecialchars($c['name']) . "</option>";
                                endwhile;
                            endif;
                            ?>
                        </select>
                    </div>

                    <div>
                        <label class="flat-label">Topup Type</label>
                        <select name="type" class="flat-select">
                            <option value="uid" <?php echo $type=='uid'?'selected':''; ?>>UID Code</option>
                            <option value="voucher" <?php echo ($type=='voucher' || $type=='unipin')?'selected':''; ?>>Voucher / UniPin</option>
                        </select>
                    </div>

                    <div>
                        <label class="flat-label">API Key (Optional)</label>
                        <input type="text" name="api_key" value="<?php echo htmlspecialchars($apiKey); ?>" class="flat-input font-mono text-xs">
                    </div>

                    <div class="md:col-span-2">
                        <label class="flat-label">Cover Image</label>
                        <div class="flex items-center gap-3 border border-gray-300 p-2 rounded bg-gray-50">
                            <?php if($editMode && !empty($currImg)): ?>
                                <img src="../<?php echo $currImg; ?>" class="w-10 h-10 rounded object-cover border">
                            <?php endif; ?>
                            <input type="file" name="image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-gray-200 file:text-gray-700 cursor-pointer" <?php echo $editMode ? '' : 'required'; ?>>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="flat-label">Rules (গেমের নিয়মাবলী)</label>
                        <textarea name="description" rows="3" class="flat-textarea resize-none" placeholder="বাংলায় নিয়ম লিখুন..."><?php echo htmlspecialchars($desc); ?></textarea>
                    </div>

                    <div class="md:col-span-2 flex flex-col md:flex-row gap-3 pt-2">
                        <button type="submit" name="save_game" class="btn-primary flex-1">
                            <?php echo $editMode ? 'Update' : 'Save'; ?>
                        </button>
                        <?php if($editMode): ?>
                            <a href="game.php" class="btn-cancel w-full md:w-auto">Cancel</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <h3 class="font-bold text-gray-800 text-sm mb-3 mt-8 uppercase tracking-wide">Active Games List</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        <?php 
        $sql = "SELECT g.*, c.name as cat_name FROM games g LEFT JOIN categories c ON g.category_id = c.id ORDER BY g.id DESC";
        $games = $conn->query($sql); 
        
        if($games && $games->num_rows > 0):
            while($g = $games->fetch_assoc()): 
                $badgeClass = ($g['type'] == 'uid') ? 'bg-uid' : 'bg-voucher';
                $catName = !empty($g['cat_name']) ? $g['cat_name'] : '-';
        ?>
        <div class="game-item">
            <img src="../<?php echo $g['image']; ?>" class="game-thumb">
            
            <div class="flex-1 min-w-0"> 
                <h4 class="font-bold text-gray-900 text-sm truncate"><?php echo $g['name']; ?></h4>
                <div class="flex flex-wrap items-center gap-1 mt-1">
                    <span class="badge <?php echo $badgeClass; ?>"><?php echo strtoupper($g['type']); ?></span>
                    <span class="badge bg-cat"><?php echo $catName; ?></span>
                </div>
            </div>
            
            <div class="flex gap-2">
                <a href="?edit=<?php echo $g['id']; ?>" class="btn-icon btn-edit"><i class="fa-solid fa-pen"></i></a>
                <button onclick="confirmDelete(<?php echo $g['id']; ?>)" class="btn-icon btn-del"><i class="fa-solid fa-trash"></i></button>
            </div>
        </div>
        <?php 
            endwhile; 
        else:
        ?>
            <div class="col-span-full py-10 text-center text-gray-400 border border-dashed border-gray-300 rounded bg-gray-50">
                <p class="text-xs">No games found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete Game?',
            text: "This will delete the game and all its products.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "?del=" + id;
            }
        });
    }
</script>
