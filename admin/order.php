<?php 
include 'common/header.php'; 

// ====================================================
// PAGINATION LOGIC
// ====================================================
$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;
$start = ($page - 1) * $limit;

// Total Pages
$total_result = $conn->query("SELECT COUNT(*) as total FROM orders");
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// ====================================================
// HANDLE ACTIONS
// ====================================================
if(isset($_POST['update_single_status'])) {
    $oid = (int)$_POST['order_id'];
    $st = $_POST['status'];
    $conn->query("UPDATE orders SET status='$st' WHERE id=$oid");
    echo "<script>window.location.href='order.php?page=$page';</script>";
}

if(isset($_POST['bulk_action_type']) && isset($_POST['selected_orders'])) {
    $ids = array_map('intval', $_POST['selected_orders']);
    $id_list = implode(',', $ids);
    
    if($_POST['bulk_action_type'] == 'delete') {
        $conn->query("DELETE FROM orders WHERE id IN ($id_list)");
        $msg = "Orders deleted.";
    } elseif($_POST['bulk_action_type'] == 'complete') {
        $conn->query("UPDATE orders SET status='completed' WHERE id IN ($id_list)");
        $msg = "Orders completed.";
    }
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({ icon: 'success', title: 'Success', text: '$msg', timer: 1500, showConfirmButton: false })
            .then(() => window.location='order.php?page=$page');
        });
    </script>";
}
?>

<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&family=Noto+Serif+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* CLASSIC ADMIN THEME */
    body { font-family: 'Lato', 'Noto Serif Bengali', sans-serif; background-color: #f3f4f6; color: #1f2937; }
    
    /* TABLE CONTAINER */
    .table-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    /* TABLE STYLES */
    .classic-table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    
    .classic-table th { 
        text-align: left; padding: 12px 16px; 
        background: #f9fafb; color: #4b5563; font-weight: 700; 
        text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .classic-table td { 
        padding: 14px 16px; border-bottom: 1px solid #f3f4f6; 
        color: #374151; vertical-align: middle; font-size: 14px;
    }
    .classic-table tr:hover td { background-color: #fdfdfd; }

    /* TICKET STYLE UID */
    .ticket-uid {
        display: inline-flex; align-items: center; justify-content: space-between;
        background: #fffbeb; border: 1px solid #fcd34d; border-left-width: 4px; 
        padding: 6px 10px; border-radius: 4px;
        font-family: 'Lato', monospace; font-size: 13px; font-weight: 700; color: #92400e;
        min-width: 140px;
    }

    /* DIAMOND HIGHLIGHT */
    .product-highlight {
        color: #d97706; /* Gold/Amber Color */
        font-weight: 800;
        font-size: 14px;
        display: flex; align-items: center; gap: 6px;
    }

    /* STATUS SELECT */
    .status-badge {
        appearance: none; -webkit-appearance: none;
        padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 800; text-transform: uppercase;
        border: 1px solid transparent; cursor: pointer; text-align: center; width: 110px;
    }
    .st-pending { background: #fff7ed; color: #c2410c; border-color: #ffedd5; }
    .st-completed { background: #f0fdf4; color: #15803d; border-color: #dcfce7; }
    .st-cancelled { background: #fef2f2; color: #b91c1c; border-color: #fee2e2; }

    /* PAGINATION & FOOTER */
    .table-footer {
        background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 12px 16px;
        display: flex; flex-direction: column; md:flex-row; justify-content: space-between; items-center; gap: 10px;
    }
    .page-link {
        padding: 6px 12px; border: 1px solid #e5e7eb; background: white; font-size: 13px; font-weight: 600; color: #4b5563;
        border-radius: 4px; transition: all 0.2s;
    }
    .page-link:hover:not(:disabled) { border-color: #d1d5db; background: #f3f4f6; }
    .page-link:disabled { opacity: 0.5; cursor: default; }

    /* BULK ACTION BUTTONS (Clean Black Text) */
    .bulk-btn {
        background: transparent; border: none; color: #111827; /* Black */
        font-size: 13px; font-weight: 600; cursor: pointer;
        display: flex; align-items: center; gap: 6px;
        padding: 6px 10px; border-radius: 4px; transition: background 0.2s;
    }
    .bulk-btn:hover { background: #e5e7eb; }
    .bulk-btn i { font-size: 14px; }
    
    /* CHECKBOX */
    .check-input { width: 16px; height: 16px; accent-color: #eab308; cursor: pointer; }
</style>

<div class="container mx-auto px-4 py-8 max-w-full">

    <div class="mb-5">
        <h1 class="text-2xl font-bold text-gray-800">Orders Management</h1>
        <p class="text-xs text-gray-500 font-medium mt-0.5">Manage user purchases & status</p>
    </div>

    <form id="bulkForm" method="POST" class="table-card">
        
        <div class="overflow-x-auto">
            <table class="classic-table">
                <thead>
                    <tr>
                        <th width="40" class="text-center">
                            <input type="checkbox" id="selectAll" class="check-input" onclick="toggleSelectAll()">
                        </th>
                        <th>Order ID</th>
                        <th>Player Info (UID)</th>
                        <th>Item Details</th>
                        <th>Transaction</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sql = "SELECT o.*, u.name as uname, p.name as pname, g.name as gname 
                            FROM orders o 
                            JOIN users u ON o.user_id=u.id 
                            JOIN products p ON o.product_id=p.id 
                            JOIN games g ON o.game_id=g.id 
                            ORDER BY o.id DESC LIMIT $start, $limit";
                    $orders = $conn->query($sql);

                    if($orders && $orders->num_rows > 0):
                        while($o = $orders->fetch_assoc()): 
                            $st = $o['status'];
                            $badgeClass = 'st-pending';
                            if($st == 'completed') $badgeClass = 'st-completed';
                            if($st == 'cancelled') $badgeClass = 'st-cancelled';
                    ?>
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" name="selected_orders[]" value="<?php echo $o['id']; ?>" class="check-input order-check">
                        </td>

                        <td>
                            <span class="font-mono text-xs font-bold text-gray-400">#<?php echo $o['id']; ?></span>
                        </td>

                        <td>
                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-bold text-gray-500 flex items-center gap-1">
                                    <i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($o['uname']); ?>
                                </span>
                                <div class="ticket-uid">
                                    <span><?php echo htmlspecialchars($o['player_id']); ?></span>
                                    <i class="fa-regular fa-copy text-yellow-600 cursor-pointer hover:text-yellow-800" onclick="copyText('<?php echo $o['player_id']; ?>')"></i>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="text-xs font-bold text-gray-400 uppercase mb-0.5"><?php echo htmlspecialchars($o['gname']); ?></div>
                            
                            <div class="product-highlight">
                                <i class="fa-regular fa-gem"></i> 
                                <?php echo htmlspecialchars($o['pname']); ?>
                            </div>

                            <div class="text-xs font-bold text-gray-800 mt-1">৳ <?php echo number_format($o['amount']); ?></div>
                        </td>

                        <td>
                            <div class="font-mono text-xs text-gray-600 bg-gray-50 border px-2 py-1 rounded inline-block">
                                <?php echo htmlspecialchars($o['transaction_id']); ?>
                            </div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase mt-1">
                                <?php echo htmlspecialchars($o['payment_method']); ?>
                            </div>
                        </td>

                        <td>
                            <select onchange="updateStatus(<?php echo $o['id']; ?>, this.value)" class="status-badge <?php echo $badgeClass; ?>">
                                <option value="pending" <?php echo $st=='pending'?'selected':''; ?>>Pending</option>
                                <option value="completed" <?php echo $st=='completed'?'selected':''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $st=='cancelled'?'selected':''; ?>>Cancelled</option>
                            </select>
                        </td>

                        <td class="text-right">
                            <a href="order_detail.php?id=<?php echo $o['id']; ?>" class="w-8 h-8 inline-flex items-center justify-center border border-gray-200 rounded hover:bg-gray-100 text-gray-500 transition">
                                <i class="fa-regular fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; 
                    else: ?>
                        <tr><td colspan="7" class="text-center py-8 text-gray-400 italic">No orders found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-footer md:flex-row flex-col-reverse">
            
            <div class="flex items-center gap-4 w-full md:w-auto">
                <?php if($orders && $orders->num_rows > 0): ?>
                    <button type="button" onclick="confirmBulkAction('complete')" class="bulk-btn">
                        <i class="fa-solid fa-check"></i> Complete
                    </button>
                    <button type="button" onclick="confirmBulkAction('delete')" class="bulk-btn hover:text-red-600">
                        <i class="fa-solid fa-trash"></i> Delete
                    </button>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto justify-end">
                <span class="text-xs text-gray-400 mr-2 hidden md:inline">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                <button type="button" onclick="window.location.href='?page=<?php echo $page-1; ?>'" class="page-link" <?php echo ($page <= 1) ? 'disabled' : ''; ?>>
                    Prev
                </button>
                <button type="button" onclick="window.location.href='?page=<?php echo $page+1; ?>'" class="page-link" <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>>
                    Next
                </button>
            </div>
        </div>
    </form>

    <form id="singleUpdateForm" method="POST" class="hidden">
        <input type="hidden" name="update_single_status" value="1">
        <input type="hidden" name="order_id" id="hidden_oid">
        <input type="hidden" name="status" id="hidden_status">
    </form>
    
    <input type="hidden" form="bulkForm" name="bulk_action_type" id="bulkActionInput">

</div>

<script>
    // Copy Logic
    function copyText(text) {
        if (!text) return;
        navigator.clipboard.writeText(text).then(() => {
            const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 1000,
                didOpen: (toast) => { toast.onmouseenter = Swal.stopTimer; toast.onmouseleave = Swal.resumeTimer; }
            });
            Toast.fire({ icon: 'success', title: 'Copied' });
        });
    }

    // Select All
    function toggleSelectAll() {
        const master = document.getElementById('selectAll');
        document.querySelectorAll('.order-check').forEach(cb => cb.checked = master.checked);
    }

    // Single Status Update
    function updateStatus(id, status) {
        document.getElementById('hidden_oid').value = id;
        document.getElementById('hidden_status').value = status;
        document.getElementById('singleUpdateForm').submit();
    }

    // Bulk Action Confirmation (SweetAlert)
    function confirmBulkAction(type) {
        // Check if any checkbox selected
        const checks = document.querySelectorAll('.order-check:checked');
        if(checks.length === 0) {
            Swal.fire({ icon: 'warning', title: 'No items selected', text: 'Please select at least one order.', timer: 1500, showConfirmButton: false });
            return;
        }

        let titleText = type === 'delete' ? 'Delete Selected Orders?' : 'Mark Selected as Completed?';
        let btnColor = type === 'delete' ? '#d33' : '#10b981';
        let confirmText = type === 'delete' ? 'Yes, Delete' : 'Yes, Complete';

        Swal.fire({
            title: titleText,
            text: "This action applies to " + checks.length + " items.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: btnColor,
            cancelButtonColor: '#6b7280',
            confirmButtonText: confirmText
        }).then((result) => {
            if (result.isConfirmed) {
                // Set the action type and submit the bulk form
                document.getElementById('bulkActionInput').value = type;
                document.getElementById('bulkForm').submit();
            }
        });
    }
</script>
