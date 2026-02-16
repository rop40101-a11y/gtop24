<?php 
include 'common/header.php'; 

// ====================================================
// SELF-HEALING DATABASE
// ====================================================
$conn->query("CREATE TABLE IF NOT EXISTS redeem_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    code VARCHAR(255) NOT NULL,
    status ENUM('active','used') DEFAULT 'active',
    order_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// ====================================================
// HANDLE ACTIONS
// ====================================================

// 1. ADD NEW CODE
if(isset($_POST['add_code'])) {
    $pid = (int)$_POST['product_id'];
    $code = $conn->real_escape_string($_POST['code']);
    
    // Support multiple codes separated by new lines
    $codes = explode("\n", $code);
    foreach($codes as $c) {
        $c = trim($c);
        if(!empty($c)) {
            $conn->query("INSERT INTO redeem_codes (product_id, code) VALUES ($pid, '$c')");
        }
    }
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({ icon: 'success', title: 'Success', text: 'Voucher codes added successfully!', timer: 1500, showConfirmButton: false });
        });
    </script>";
}

// 2. ASSIGN TO ORDER
if(isset($_POST['assign_order'])) {
    $cid = (int)$_POST['code_id'];
    $oid = (int)$_POST['order_id'];
    
    // Check if order exists
    $chk = $conn->query("SELECT id FROM orders WHERE id=$oid");
    if($chk->num_rows > 0) {
        $conn->query("UPDATE redeem_codes SET order_id=$oid, status='used' WHERE id=$cid");
        $conn->query("UPDATE orders SET status='completed' WHERE id=$oid"); // Auto complete order
        
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({ icon: 'success', title: 'Assigned', text: 'Order #$oid marked as Completed.', timer: 2000, showConfirmButton: false });
            });
        </script>";
    } else {
        echo "<script>alert('Order ID #$oid not found!');</script>";
    }
}

// 3. DELETE CODE
if(isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $conn->query("DELETE FROM redeem_codes WHERE id=$id");
    echo "<script>window.location='redeemcode.php';</script>";
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
    
    .input-field, .select-field, .textarea-field {
        width: 100%; border: 1px solid #d1d5db; border-radius: 4px; padding: 10px 12px;
        font-size: 14px; color: #111827; background: #fff; transition: all 0.2s;
    }
    .input-field:focus, .select-field:focus, .textarea-field:focus { border-color: #eab308; outline: none; }

    /* TABLE */
    .pro-table th { 
        text-align: left; padding: 12px 16px; 
        background: #f9fafb; color: #4b5563; font-weight: 700; 
        text-transform: uppercase; font-size: 11px; border-bottom: 2px solid #e5e7eb; white-space: nowrap;
    }
    .pro-table td { 
        padding: 12px 16px; border-bottom: 1px solid #f3f4f6; 
        color: #374151; vertical-align: middle; font-size: 13px; white-space: nowrap;
    }
    .pro-table tr:hover td { background-color: #fdfdfd; }
    .pro-table tr:last-child td { border-bottom: none; }

    /* CODE TICKET */
    .code-ticket {
        font-family: monospace; font-size: 12px; background: #eff6ff; 
        padding: 4px 8px; border-radius: 4px; border: 1px dashed #bfdbfe; color: #1e40af; font-weight: 700;
        display: inline-flex; align-items: center; gap: 6px;
    }
    
    .copy-icon { cursor: pointer; color: #93c5fd; transition: color 0.2s; }
    .copy-icon:hover { color: #2563eb; }

    /* BUTTONS */
    .btn-submit {
        background: #eab308; color: white; padding: 10px; border-radius: 6px; font-weight: 700;
        border: none; cursor: pointer; width: 100%; font-size: 14px; transition: background 0.2s;
    }
    .btn-submit:hover { background: #ca8a04; }

    .btn-assign {
        background: #0f172a; color: white; padding: 10px; border-radius: 6px; font-weight: 700;
        border: none; cursor: pointer; width: 100%; font-size: 14px; transition: background 0.2s;
    }
    .btn-assign:hover { background: #334155; }

    .del-btn {
        width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;
        border-radius: 4px; border: 1px solid #e5e7eb; color: #6b7280; background: white; cursor: pointer;
    }
    .del-btn:hover { background: #fef2f2; border-color: #fee2e2; color: #ef4444; }
</style>

<div class="container mx-auto px-4 py-6 max-w-full">
    
    <div class="mb-5">
        <h1 class="text-2xl font-bold text-gray-800">Voucher & Redeem Codes</h1>
        <p class="text-xs text-gray-500 font-medium mt-0.5">Manage codes for automatic delivery.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-1 space-y-6">
            
            <div class="flat-card border-t-4 border-t-yellow-500">
                <div class="flat-header">
                    <span><i class="fa-solid fa-plus-circle mr-2 text-yellow-600"></i> Add New Codes</span>
                </div>
                <div class="flat-body">
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="input-label">Select Product</label>
                            <select name="product_id" class="select-field" required>
                                <option value="">-- Choose Voucher --</option>
                                <?php 
                                // Only show products from games marked as 'voucher' type
                                $prods = $conn->query("SELECT p.id, p.name, g.name as gname FROM products p JOIN games g ON p.game_id=g.id WHERE g.type='voucher' OR g.type='unipin'");
                                while($p = $prods->fetch_assoc()) echo "<option value='{$p['id']}'>{$p['gname']} - {$p['name']}</option>"; 
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="input-label">Redeem Codes</label>
                            <textarea name="code" rows="5" placeholder="Enter codes here...&#10;One code per line&#10;Example: UP-12345" class="textarea-field font-mono text-xs" required></textarea>
                            <p class="text-[10px] text-gray-400 mt-1">Paste multiple codes (one per line) to bulk add.</p>
                        </div>
                        <button type="submit" name="add_code" class="btn-submit">
                            <i class="fa-solid fa-save mr-1"></i> Save Codes
                        </button>
                    </form>
                </div>
            </div>

            <div class="flat-card border-t-4 border-t-gray-800">
                <div class="flat-header">
                    <span><i class="fa-solid fa-handshake mr-2"></i> Manual Assign</span>
                </div>
                <div class="flat-body">
                    <div class="text-xs text-gray-500 mb-3 leading-relaxed">
                        Manually give a code to a user if automation failed. This will mark the order as <b>Completed</b>.
                    </div>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="input-label">Order ID</label>
                            <input type="number" name="order_id" placeholder="e.g. 154" class="input-field" required>
                        </div>
                        <div>
                            <label class="input-label">Select Available Code</label>
                            <select name="code_id" class="select-field" required>
                                <option value="">-- Select Code --</option>
                                <?php 
                                $codes = $conn->query("SELECT r.*, p.name, g.name as gname FROM redeem_codes r JOIN products p ON r.product_id=p.id JOIN games g ON p.game_id=g.id WHERE r.status='active'");
                                if($codes->num_rows > 0) {
                                    while($c = $codes->fetch_assoc()) {
                                        // Truncate long codes for display
                                        $displayCode = strlen($c['code']) > 15 ? substr($c['code'], 0, 12).'...' : $c['code'];
                                        echo "<option value='{$c['id']}'>{$c['gname']} ({$c['name']}) - $displayCode</option>";
                                    }
                                } else {
                                    echo "<option disabled>No active codes available</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" name="assign_order" class="btn-assign">
                            Assign & Complete
                        </button>
                    </form>
                </div>
            </div>

        </div>

        <div class="lg:col-span-2">
            <div class="flat-card">
                <div class="flat-header">
                    <span><i class="fa-solid fa-list mr-2 text-gray-400"></i> Active Inventory</span>
                    <span class="text-xs font-bold text-gray-400 bg-gray-100 px-2 py-1 rounded">
                        <?php 
                        $cnt = $conn->query("SELECT COUNT(*) as c FROM redeem_codes WHERE status='active'")->fetch_assoc()['c'];
                        echo $cnt . ' Codes'; 
                        ?>
                    </span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full pro-table border-collapse">
                        <thead>
                            <tr>
                                <th>Game / Product</th>
                                <th>Voucher Code</th>
                                <th>Added Date</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Fetch only active codes for the list
                            $activeCodes = $conn->query("SELECT r.*, p.name as pname, g.name as gname, g.image as gimg FROM redeem_codes r JOIN products p ON r.product_id=p.id JOIN games g ON p.game_id=g.id WHERE r.status='active' ORDER BY r.id DESC");
                            
                            if($activeCodes && $activeCodes->num_rows > 0):
                                while($c = $activeCodes->fetch_assoc()): 
                            ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <img src="../<?php echo $c['gimg']; ?>" class="w-6 h-6 rounded border">
                                        <div>
                                            <div class="font-bold text-gray-700 text-xs"><?php echo $c['gname']; ?></div>
                                            <div class="text-[10px] text-gray-400"><?php echo $c['pname']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="code-ticket">
                                        <span><?php echo $c['code']; ?></span>
                                        <i class="fa-regular fa-copy copy-icon" onclick="copyText('<?php echo $c['code']; ?>')"></i>
                                    </div>
                                </td>
                                <td class="text-xs text-gray-500">
                                    <?php echo date('d M', strtotime($c['created_at'])); ?>
                                </td>
                                <td class="text-right">
                                    <a href="?del=<?php echo $c['id']; ?>" onclick="return confirm('Delete this code?')" class="del-btn" title="Delete">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; 
                            else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-10 text-gray-400 text-sm italic">
                                    <i class="fa-solid fa-ticket text-3xl mb-2 opacity-20"></i><br>
                                    No active codes found.
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

<script>
    function copyText(text) {
        if (!text) return;
        navigator.clipboard.writeText(text).then(() => {
            const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 1000,
                didOpen: (toast) => { toast.onmouseenter = Swal.stopTimer; toast.onmouseleave = Swal.resumeTimer; }
            });
            Toast.fire({ icon: 'success', title: 'Copied!' });
        });
    }
</script>
