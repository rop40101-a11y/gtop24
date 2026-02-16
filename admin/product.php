<?php 
include 'common/header.php'; 

// ====================================================
// SELF-HEALING DATABASE: Ensure 'unipin_code' exists
// ====================================================
$checkCol = $conn->query("SHOW COLUMNS FROM products LIKE 'unipin_code'");
if($checkCol->num_rows == 0) {
    $conn->query("ALTER TABLE products ADD COLUMN unipin_code VARCHAR(100) DEFAULT NULL");
}

// ====================================================
// INITIALIZE VARIABLES (Add/Edit Mode)
// ====================================================
$editMode = false;
$editId = 0;
$game_id = '';
$name = '';
$price = '';
$unipin_code = '';

// Handle "Edit" Click
if(isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM products WHERE id=$editId");
    if($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $editMode = true;
        $game_id = $row['game_id'];
        $name = $row['name'];
        $price = $row['price'];
        $unipin_code = $row['unipin_code'];
    }
}

// ====================================================
// HANDLE FORM SUBMISSION (ADD OR UPDATE)
// ====================================================
if(isset($_POST['save_product'])) {
    $gid = (int)$_POST['game_id'];
    $n = $conn->real_escape_string($_POST['name']);
    $p = (float)$_POST['price'];
    $uCode = $conn->real_escape_string($_POST['unipin_code']);
    
    if($editMode) {
        // UPDATE
        $pid = (int)$_POST['update_id'];
        $conn->query("UPDATE products SET game_id=$gid, name='$n', price='$p', unipin_code='$uCode' WHERE id=$pid");
        $msg = "Product updated successfully.";
    } else {
        // INSERT
        $conn->query("INSERT INTO products (game_id, name, price, unipin_code) VALUES ($gid, '$n', '$p', '$uCode')");
        $msg = "New product added.";
    }
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success', title: 'Success', text: '$msg', timer: 1000, showConfirmButton: false
            }).then(() => { window.location='product.php'; });
        });
    </script>";
}

// ====================================================
// HANDLE DELETION
// ====================================================
if(isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $conn->query("DELETE FROM products WHERE id=$id");
    echo "<script>window.location='product.php';</script>";
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
        box-shadow: 0 1px 2px rgba(0,0,0,0.05); overflow: hidden;
    }
    
    .flat-header {
        background: #f9fafb; padding: 14px 18px; border-bottom: 1px solid #e5e7eb;
        font-weight: 700; color: #374151; font-size: 13px; text-transform: uppercase;
        letter-spacing: 0.05em; display: flex; justify-content: space-between; align-items: center;
    }

    .flat-body { padding: 20px; }

    /* FORM ELEMENTS */
    .input-label { display: block; font-size: 11px; font-weight: 700; color: #6b7280; margin-bottom: 6px; text-transform: uppercase; }
    
    .input-field, .select-field {
        width: 100%; border: 1px solid #d1d5db; border-radius: 4px; padding: 10px 12px;
        font-size: 14px; color: #111827; background: #fff; transition: all 0.2s;
    }
    .input-field:focus, .select-field:focus { border-color: #eab308; outline: none; }

    /* TABLE STYLES */
    .pro-table th { 
        text-align: left; padding: 12px 16px; 
        background: #f9fafb; color: #4b5563; font-weight: 700; 
        text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em;
        border-bottom: 2px solid #e5e7eb; white-space: nowrap;
    }
    .pro-table td { 
        padding: 12px 16px; border-bottom: 1px solid #f3f4f6; 
        color: #374151; vertical-align: middle; font-size: 13px; white-space: nowrap;
    }
    .pro-table tr:last-child td { border-bottom: none; }
    .pro-table tr:hover td { background-color: #fdfdfd; }

    /* BADGES */
    .code-badge {
        font-family: monospace; font-size: 11px; background: #fffbeb; 
        padding: 3px 6px; border-radius: 4px; border: 1px solid #fcd34d; color: #92400e; font-weight: 700;
    }
    .price-badge {
        font-weight: 800; color: #15803d; background: #f0fdf4; 
        padding: 2px 8px; border-radius: 4px; border: 1px solid #dcfce7; font-size: 12px;
    }

    /* BUTTONS */
    .btn-submit {
        background: #eab308; color: white; padding: 10px; border-radius: 6px; font-weight: 700;
        border: none; cursor: pointer; width: 100%; font-size: 14px;
        transition: background 0.2s;
    }
    .btn-submit:hover { background: #ca8a04; }

    .btn-cancel {
        background: #f3f4f6; color: #4b5563; padding: 10px; border-radius: 6px; font-weight: 700;
        border: 1px solid #d1d5db; cursor: pointer; text-align: center;
        display: block; text-decoration: none; width: 100%; font-size: 14px;
    }

    .action-icon {
        width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;
        border-radius: 4px; border: 1px solid #e5e7eb; color: #6b7280; background: white;
        transition: all 0.2s; cursor: pointer; margin-left: 4px;
    }
    .action-icon:hover { background: #f3f4f6; border-color: #d1d5db; color: #111827; }
    .del-icon:hover { background: #fef2f2; border-color: #fee2e2; color: #ef4444; }
</style>

<div class="container mx-auto px-4 py-6 max-w-full">
    
    <div class="mb-5">
        <h1 class="text-2xl font-bold text-gray-800">Products & Topup</h1>
        <p class="text-xs text-gray-500 font-medium mt-0.5">Manage game packages and pricing.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-1">
            <div class="flat-card border-t-4 border-t-yellow-500 sticky top-4">
                <div class="flat-header">
                    <span>
                        <i class="fa-solid <?php echo $editMode ? 'fa-pen-to-square' : 'fa-plus-circle'; ?> mr-2 text-yellow-600"></i> 
                        <?php echo $editMode ? 'Edit Package' : 'Add New Package'; ?>
                    </span>
                </div>
                <div class="flat-body">
                    <form method="POST" class="space-y-4">
                        
                        <?php if($editMode): ?>
                            <input type="hidden" name="update_id" value="<?php echo $editId; ?>">
                        <?php endif; ?>

                        <div>
                            <label class="input-label">Select Game</label>
                            <select name="game_id" class="select-field" required>
                                <option value="" disabled <?php echo !$editMode?'selected':''; ?>>-- Choose Game --</option>
                                <?php 
                                $games = $conn->query("SELECT id, name, type FROM games ORDER BY name ASC");
                                while($g = $games->fetch_assoc()): 
                                    $selected = ($editMode && $game_id == $g['id']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $g['id']; ?>" <?php echo $selected; ?>>
                                        <?php echo $g['name']; ?> (<?php echo strtoupper($g['type']); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="input-label">Package Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="e.g. 100 Diamonds" class="input-field" required>
                        </div>

                        <div>
                            <label class="input-label">Selling Price</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-400 font-bold text-sm">৳</span>
                                <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($price); ?>" placeholder="0.00" class="input-field pl-8 font-bold text-green-700" required>
                            </div>
                        </div>

                        <div>
                            <label class="input-label">UniPin Code (Optional)</label>
                            <input type="text" name="unipin_code" value="<?php echo htmlspecialchars($unipin_code); ?>" placeholder="e.g. UPBD-Q-S-xxxxxx" class="input-field font-mono text-xs bg-gray-50">
                            <p class="text-[10px] text-gray-400 mt-1 italic">Used for auto-delivery APIs.</p>
                        </div>

                        <div class="pt-2 flex gap-2">
                            <button type="submit" name="save_product" class="btn-submit flex-1">
                                <?php echo $editMode ? 'Update' : 'Add Product'; ?>
                            </button>
                            <?php if($editMode): ?>
                                <a href="product.php" class="btn-cancel flex-1">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="flat-card">
                <div class="flat-header">
                    <span><i class="fa-solid fa-list mr-2 text-gray-400"></i> All Packages</span>
                    <span class="text-xs font-bold text-gray-400 bg-gray-100 px-2 py-1 rounded">
                        <?php 
                        $cnt = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
                        echo $cnt; 
                        ?>
                    </span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full pro-table border-collapse">
                        <thead>
                            <tr>
                                <th>Game Title</th>
                                <th>Package Name</th>
                                <th>UniPin Code</th>
                                <th>Price</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $prods = $conn->query("SELECT p.*, g.name as gname, g.image as gimg FROM products p JOIN games g ON p.game_id = g.id ORDER BY p.id DESC");
                            
                            if($prods->num_rows > 0):
                                while($p = $prods->fetch_assoc()): 
                                    $codeDisplay = !empty($p['unipin_code']) ? '<span class="code-badge">'.$p['unipin_code'].'</span>' : '<span class="text-gray-300 text-xs">-</span>';
                            ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <img src="../<?php echo $p['gimg']; ?>" class="w-6 h-6 rounded object-cover border border-gray-200">
                                        <span class="font-bold text-gray-700 text-xs"><?php echo $p['gname']; ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="font-bold text-gray-800 text-sm"><?php echo $p['name']; ?></span>
                                </td>
                                <td><?php echo $codeDisplay; ?></td>
                                <td>
                                    <span class="price-badge">৳ <?php echo number_format($p['price'], 2); ?></span>
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end">
                                        <a href="?edit=<?php echo $p['id']; ?>" class="action-icon" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="?del=<?php echo $p['id']; ?>" onclick="return confirm('Delete this package?')" class="action-icon del-icon" title="Delete">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; 
                            else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-10 text-gray-400 text-sm italic">
                                    No products found. Add one from the left.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </div>
</div>
