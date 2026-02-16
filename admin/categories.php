<?php 
include 'common/header.php'; 

// ====================================================
// SELF-HEALING DATABASE
// ====================================================
// Ensure 'categories' table exists
$conn->query("CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    priority INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// ====================================================
// INITIALIZE VARIABLES
// ====================================================
$editMode = false;
$editId = 0;
$name = '';
$priority = 0;

// Edit Mode
if(isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM categories WHERE id=$editId");
    if($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $editMode = true;
        $name = $row['name'];
        $priority = $row['priority'];
    }
}

// ====================================================
// HANDLE SAVE (ADD/UPDATE)
// ====================================================
if(isset($_POST['save_cat'])) {
    $n = $conn->real_escape_string($_POST['name']);
    $p = (int)$_POST['priority'];
    
    if(empty($n)) {
        echo "<script>alert('Category Name is required!');</script>";
    } else {
        if($editMode) {
            $uid = (int)$_POST['update_id'];
            $stmt = $conn->prepare("UPDATE categories SET name=?, priority=? WHERE id=?");
            $stmt->bind_param("sii", $n, $p, $uid);
            $msg = "Category updated successfully!";
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, priority) VALUES (?, ?)");
            $stmt->bind_param("si", $n, $p);
            $msg = "New category added successfully!";
        }
        
        if($stmt->execute()) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    const Toast = Swal.mixin({
                        toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true,
                        didOpen: (toast) => { toast.addEventListener('mouseenter', Swal.stopTimer); toast.addEventListener('mouseleave', Swal.resumeTimer); }
                    });
                    Toast.fire({ icon: 'success', title: '$msg' }).then(() => { window.location='categories.php'; });
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
    // Optional: Reset games in this category to 0 (Uncategorized)
    $conn->query("UPDATE games SET category_id = 0 WHERE category_id = $id");
    $conn->query("DELETE FROM categories WHERE id=$id");
    echo "<script>window.location='categories.php';</script>";
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
    
    .flat-input {
        width: 100%; border: 1px solid #d1d5db; border-radius: 6px;
        padding: 10px 12px; font-size: 14px; color: #1f2937; background: #fff;
        transition: border 0.2s;
    }
    .flat-input:focus { border-color: #ca8a04; outline: none; }

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

    /* LIST ITEM */
    .cat-item {
        background: white; border: 1px solid #e5e7eb; border-radius: 8px;
        padding: 12px 16px; display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 10px; transition: transform 0.1s;
    }
    .cat-item:hover { transform: translateY(-2px); border-color: #eab308; }

    .cat-info h4 { font-weight: 700; font-size: 14px; color: #111827; margin: 0; }
    .cat-info span { font-size: 11px; color: #6b7280; font-weight: 500; }

    .action-group { display: flex; gap: 8px; }
    .btn-icon {
        width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
        border-radius: 6px; font-size: 13px; cursor: pointer; border: 1px solid transparent;
    }
    .btn-edit { background: #fff; border-color: #e5e7eb; color: #4b5563; }
    .btn-del { background: #fff; border-color: #fee2e2; color: #ef4444; }
</style>

<div class="container mx-auto px-4 py-6 max-w-4xl">
    
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
            <p class="text-sm text-gray-500">Organize your games into sections.</p>
        </div>
        <?php if(!$editMode): ?>
            <button onclick="document.getElementById('catForm').scrollIntoView({behavior: 'smooth'})" class="md:hidden w-full bg-yellow-500 text-white py-2.5 rounded-lg font-bold">
                + Add Category
            </button>
        <?php endif; ?>
    </div>

    <div id="catForm" class="flat-card border-t-4 border-t-yellow-400">
        <div class="flat-header">
            <span class="flex items-center gap-2">
                <i class="fa-solid <?php echo $editMode ? 'fa-pen-to-square' : 'fa-layer-group'; ?> text-yellow-600"></i> 
                <?php echo $editMode ? 'Edit Category' : 'Create Category'; ?>
            </span>
            <?php if($editMode): ?>
                <a href="categories.php" class="text-xs text-red-500 font-medium">Cancel</a>
            <?php endif; ?>
        </div>
        <div class="flat-body">
            <form method="POST">
                <?php if($editMode): ?>
                    <input type="hidden" name="update_id" value="<?php echo $editId; ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="flat-label">Category Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="e.g. Free Fire, PUBG" class="flat-input" required>
                    </div>

                    <div>
                        <label class="flat-label">Priority Order (0 = Top)</label>
                        <input type="number" name="priority" value="<?php echo $priority; ?>" class="flat-input">
                    </div>

                    <div class="md:col-span-2 flex flex-col md:flex-row gap-3 pt-2">
                        <button type="submit" name="save_cat" class="btn-primary flex-1">
                            <?php echo $editMode ? 'Update' : 'Save'; ?>
                        </button>
                        <?php if($editMode): ?>
                            <a href="categories.php" class="btn-cancel w-full md:w-auto">Cancel</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <h3 class="font-bold text-gray-800 text-sm mb-3 mt-8 uppercase tracking-wide">Active Categories</h3>
    
    <div>
        <?php 
        $cats = $conn->query("SELECT * FROM categories ORDER BY priority ASC, id ASC"); 
        
        if($cats && $cats->num_rows > 0):
            while($c = $cats->fetch_assoc()): 
                // Count games in this category
                $cnt = $conn->query("SELECT COUNT(*) as t FROM games WHERE category_id = {$c['id']}")->fetch_assoc()['t'];
        ?>
        <div class="cat-item">
            <div class="cat-info">
                <h4><?php echo htmlspecialchars($c['name']); ?></h4>
                <span>Priority: <?php echo $c['priority']; ?> • Games: <?php echo $cnt; ?></span>
            </div>
            
            <div class="action-group">
                <a href="?edit=<?php echo $c['id']; ?>" class="btn-icon btn-edit"><i class="fa-solid fa-pen"></i></a>
                <button onclick="confirmDelete(<?php echo $c['id']; ?>)" class="btn-icon btn-del"><i class="fa-solid fa-trash"></i></button>
            </div>
        </div>
        <?php 
            endwhile; 
        else:
        ?>
            <div class="py-10 text-center text-gray-400 border border-dashed border-gray-300 rounded bg-gray-50">
                <p class="text-xs">No categories found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete Category?',
            text: "Games in this category will be marked as 'Uncategorized'.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "?del=" + id;
            }
        });
    }
</script>
