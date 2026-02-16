<?php 
include 'common/header.php'; 

// Validate ID
if(!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>window.location.href='order.php';</script>";
    exit;
}

$oid = (int)$_GET['id'];

// Fetch Order Data
$sql = "SELECT o.*, u.name as uname, u.phone as uphone, u.email as uemail, g.name as gname, p.name as pname 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        JOIN games g ON o.game_id = g.id 
        JOIN products p ON o.product_id = p.id 
        WHERE o.id = $oid";
$result = $conn->query($sql);

if($result->num_rows == 0) {
    echo "<script>window.location.href='order.php';</script>";
    exit;
}

$order = $result->fetch_assoc();

// Status Logic
$st = $order['status'];
$badgeClass = 'bg-gray-100 text-gray-600 border-gray-200';
if($st == 'pending') $badgeClass = 'bg-orange-50 text-orange-600 border-orange-200';
if($st == 'completed') $badgeClass = 'bg-green-50 text-green-600 border-green-200';
if($st == 'cancelled') $badgeClass = 'bg-red-50 text-red-600 border-red-200';
?>

<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&family=Noto+Serif+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* GLOBAL STYLES */
    body { font-family: 'Lato', 'Noto Serif Bengali', sans-serif; background-color: #f3f4f6; color: #1f2937; }

    /* DETAIL CARD (Classic) */
    .detail-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    /* HEADERS */
    .section-header {
        font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
        color: #6b7280; margin-bottom: 8px; border-bottom: 1px solid #f3f4f6; padding-bottom: 4px;
    }

    /* DATA BOX (Ticket Style) */
    .data-box {
        background: #f9fafb; border: 1px solid #e5e7eb;
        padding: 12px; border-radius: 4px;
        font-family: 'Lato', monospace; font-size: 14px; font-weight: 700; color: #111827;
        display: flex; align-items: center; justify-content: space-between;
    }
    .data-box.highlight { background: #fffbeb; border-color: #fcd34d; color: #92400e; }

    /* COPY BUTTON */
    .copy-btn { color: #9ca3af; cursor: pointer; transition: color 0.2s; }
    .copy-btn:hover { color: #d97706; }

    /* ACTION BUTTONS */
    .action-btn {
        flex: 1; padding: 12px; border-radius: 4px; font-weight: 700; font-size: 13px;
        text-align: center; border: 1px solid transparent; transition: all 0.2s;
        display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer;
    }
    .btn-pending { background: white; color: #c2410c; border-color: #ffedd5; }
    .btn-pending:hover { background: #fff7ed; }
    
    .btn-complete { background: white; color: #15803d; border-color: #dcfce7; }
    .btn-complete:hover { background: #f0fdf4; }
    
    .btn-cancel { background: white; color: #b91c1c; border-color: #fee2e2; }
    .btn-cancel:hover { background: #fef2f2; }
</style>

<div class="container mx-auto px-4 py-8 max-w-4xl">

    <a href="order.php" class="inline-flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-gray-800 mb-4 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Back to Orders
    </a>

    <div class="detail-card">
        
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
            <div>
                <h1 class="text-lg font-bold text-gray-800">Order #<?php echo $order['id']; ?></h1>
                <p class="text-xs text-gray-400 font-mono"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></p>
            </div>
            <span class="px-3 py-1 rounded-full border text-[10px] font-bold uppercase <?php echo $badgeClass; ?>">
                <?php echo $order['status']; ?>
            </span>
        </div>

        <div class="p-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                
                <div class="space-y-6">
                    
                    <div>
                        <h4 class="section-header">Customer</h4>
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded bg-gray-100 flex items-center justify-center text-gray-500 font-bold">
                                <?php echo strtoupper(substr($order['uname'], 0, 1)); ?>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 text-sm"><?php echo htmlspecialchars($order['uname']); ?></p>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($order['uphone']); ?></p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="section-header">Product</h4>
                        <div class="bg-gray-50 border border-gray-100 rounded p-3">
                            <p class="text-xs font-bold text-gray-500 uppercase"><?php echo htmlspecialchars($order['gname']); ?></p>
                            <div class="flex items-center gap-2 mt-1 text-yellow-600 font-bold text-sm">
                                <i class="fa-regular fa-gem"></i> <?php echo htmlspecialchars($order['pname']); ?>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="space-y-6">
                    
                    <div>
                        <h4 class="section-header text-yellow-600">Target Player ID (UID)</h4>
                        <div class="data-box highlight">
                            <span><?php echo htmlspecialchars($order['player_id']); ?></span>
                            <i class="fa-regular fa-copy copy-btn" onclick="copyText('<?php echo $order['player_id']; ?>')"></i>
                        </div>
                    </div>

                    <div>
                        <h4 class="section-header">Payment Details</h4>
                        <div class="data-box mb-2">
                            <span class="font-mono text-sm"><?php echo htmlspecialchars($order['transaction_id']); ?></span>
                            <i class="fa-regular fa-copy copy-btn" onclick="copyText('<?php echo $order['transaction_id']; ?>')"></i>
                        </div>
                        <div class="flex justify-between items-center text-sm border-t border-dashed border-gray-200 pt-2">
                            <span class="text-gray-500">Via <b class="text-gray-700"><?php echo htmlspecialchars($order['payment_method']); ?></b></span>
                            <span class="font-bold text-lg text-gray-900">৳ <?php echo number_format($order['amount']); ?></span>
                        </div>
                    </div>

                </div>
            </div>

            <div class="border-t border-gray-100 pt-6">
                <h4 class="section-header mb-3">Actions</h4>
                
                <form id="actionForm" action="order.php" method="POST" class="flex flex-col sm:flex-row gap-3">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <input type="hidden" name="update_single_status" value="1">
                    <input type="hidden" name="status" id="statusInput">

                    <button type="button" onclick="confirmUpdate('pending')" class="action-btn btn-pending">
                        <i class="fa-regular fa-clock"></i> Pending
                    </button>
                    
                    <button type="button" onclick="confirmUpdate('completed')" class="action-btn btn-complete">
                        <i class="fa-solid fa-check"></i> Complete
                    </button>
                    
                    <button type="button" onclick="confirmUpdate('cancelled')" class="action-btn btn-cancel">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    // 1. COPY TO CLIPBOARD
    function copyText(text) {
        if (!text) return;
        navigator.clipboard.writeText(text).then(() => {
            const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 1500, timerProgressBar: true,
                didOpen: (toast) => { toast.onmouseenter = Swal.stopTimer; toast.onmouseleave = Swal.resumeTimer; }
            });
            Toast.fire({ icon: 'success', title: 'Copied!' });
        });
    }

    // 2. CONFIRM UPDATE (SWEETALERT)
    function confirmUpdate(status) {
        let titleText = "";
        let bodyText = "";
        let btnColor = "";
        let confirmText = "";

        // Customise Message based on action
        if(status === 'completed') {
            titleText = 'Complete Order?';
            bodyText = 'This will mark the order as successful.';
            btnColor = '#10b981';
            confirmText = 'Yes, Complete it!';
        } else if(status === 'cancelled') {
            titleText = 'Cancel Order?';
            bodyText = 'This will mark the order as failed/cancelled.';
            btnColor = '#ef4444';
            confirmText = 'Yes, Cancel it!';
        } else {
            titleText = 'Mark as Pending?';
            bodyText = 'Set status back to pending.';
            btnColor = '#f59e0b';
            confirmText = 'Yes, Set Pending';
        }

        Swal.fire({
            title: titleText,
            text: bodyText,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: btnColor,
            cancelButtonColor: '#6b7280',
            confirmButtonText: confirmText
        }).then((result) => {
            if (result.isConfirmed) {
                // Set hidden input and submit
                document.getElementById('statusInput').value = status;
                document.getElementById('actionForm').submit();
            }
        });
    }
</script>
