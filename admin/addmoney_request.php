<?php include 'common/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    /* Custom Scrollbar for Mobile */
    .custom-scroll::-webkit-scrollbar {
        height: 6px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: #f1f1f1; 
        border-radius: 10px;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: #c7c7c7; 
        border-radius: 10px;
    }
    .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8; 
    }
    /* Glass Effect */
    .glass-effect {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
</style>

<?php 
// --- Handle Actions (Approve/Reject) ---
if(isset($_POST['action'])) {
    $req_id = (int)$_POST['req_id'];
    $act = $_POST['action'];

    if($act == 'approve') {
        $req = $conn->query("SELECT * FROM deposits WHERE id=$req_id AND status='pending'")->fetch_assoc();
        if($req) {
            $amount = $req['amount'];
            $uid = $req['user_id'];
            $conn->query("UPDATE deposits SET status='approved' WHERE id=$req_id");
            $conn->query("UPDATE users SET balance = balance + $amount WHERE id=$uid");
            
            // SweetAlert Success Message
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Approved!',
                        text: 'Balance has been added successfully.',
                        confirmButtonColor: '#10B981',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => { window.location.href='addmoney_request.php?tab=approved'; });
                });
            </script>";
        }
    } elseif($act == 'reject') {
        $conn->query("UPDATE deposits SET status='rejected' WHERE id=$req_id");
        
        // SweetAlert Reject Message
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Rejected!',
                    text: 'Request has been rejected.',
                    confirmButtonColor: '#EF4444',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => { window.location.href='addmoney_request.php?tab=rejected'; });
            });
        </script>";
    }
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'pending';
?>

<div class="container mx-auto px-4 py-6 animate__animated animate__fadeIn">
    
    <div class="mb-8 flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">Add Money Requests</h2>
            <p class="text-gray-500 text-sm mt-1">Manage user deposit requests efficiently.</p>
        </div>
        
        <div class="bg-white p-1.5 rounded-xl shadow-sm border border-gray-100 flex">
            <a href="?tab=pending" class="px-5 py-2.5 rounded-lg text-sm font-bold transition-all duration-300 <?php echo $tab=='pending'?'bg-blue-600 text-white shadow-md transform scale-105':'text-gray-500 hover:bg-gray-50 hover:text-blue-500'; ?>">
                <i class="fa-regular fa-clock mr-1"></i> Pending
            </a>
            <a href="?tab=approved" class="px-5 py-2.5 rounded-lg text-sm font-bold transition-all duration-300 <?php echo $tab=='approved'?'bg-green-600 text-white shadow-md transform scale-105':'text-gray-500 hover:bg-gray-50 hover:text-green-500'; ?>">
                <i class="fa-regular fa-circle-check mr-1"></i> Approved
            </a>
            <a href="?tab=rejected" class="px-5 py-2.5 rounded-lg text-sm font-bold transition-all duration-300 <?php echo $tab=='rejected'?'bg-red-600 text-white shadow-md transform scale-105':'text-gray-500 hover:bg-gray-50 hover:text-red-500'; ?>">
                <i class="fa-regular fa-circle-xmark mr-1"></i> Rejected
            </a>
        </div>
    </div>

    <div class="glass-effect rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto custom-scroll">
            <table class="w-full text-sm text-left whitespace-nowrap">
                <thead class="bg-gray-50/50 text-gray-600 uppercase text-xs tracking-wider border-b border-gray-200">
                    <tr>
                        <th class="p-5 font-bold">User Details</th>
                        <th class="p-5 font-bold">Amount</th>
                        <th class="p-5 font-bold">Payment Info</th>
                        <th class="p-5 font-bold">Time</th>
                        <th class="p-5 text-center font-bold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php 
                    $sql = "SELECT d.*, u.name, u.phone FROM deposits d JOIN users u ON d.user_id = u.id WHERE d.status='$tab' ORDER BY d.id DESC";
                    $res = $conn->query($sql);
                    
                    if($res->num_rows > 0):
                        while($row = $res->fetch_assoc()): ?>
                        <tr class="hover:bg-blue-50/30 transition duration-200 group animate__animated animate__fadeInUp">
                            <td class="p-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg shadow-sm">
                                        <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-800"><?php echo $row['name']; ?></div>
                                        <div class="text-xs text-gray-500 font-mono"><?php echo $row['phone']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-5">
                                <span class="font-extrabold text-xl text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">
                                    <?php echo getSetting($conn, 'currency').$row['amount']; ?>
                                </span>
                            </td>
                            <td class="p-5">
                                <div class="flex flex-col gap-1">
                                    <div class="text-xs text-gray-500">Method: <span class="font-bold text-gray-700"><?php echo $row['method']; ?></span></div>
                                    <div class="text-xs text-gray-500">TrxID: <span class="font-mono text-purple-600 font-bold select-all bg-purple-50 px-1 rounded cursor-pointer" onclick="copyToClipboard('<?php echo $row['trx_id']; ?>')"><?php echo $row['trx_id']; ?></span></div>
                                </div>
                            </td>
                            <td class="p-5 text-xs text-gray-400">
                                <div class="flex items-center gap-1"><i class="fa-regular fa-calendar"></i> <?php echo date('d M Y', strtotime($row['created_at'])); ?></div>
                                <div class="flex items-center gap-1 mt-1"><i class="fa-regular fa-clock"></i> <?php echo date('h:i A', strtotime($row['created_at'])); ?></div>
                            </td>
                            <td class="p-5 text-center">
                                <?php if($tab == 'pending'): ?>
                                    <div class="flex items-center justify-center gap-3">
                                        <button onclick="showDetails('<?php echo $row['trx_id']; ?>', '<?php echo $row['wallet_number']; ?>', '<?php echo $row['method']; ?>', '<?php echo $row['amount']; ?>')" 
                                                class="w-9 h-9 rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-blue-600 hover:text-white hover:border-blue-600 shadow-sm transition-all transform hover:scale-110 flex items-center justify-center" title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        
                                        <form id="approve-form-<?php echo $row['id']; ?>" method="POST">
                                            <input type="hidden" name="req_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="button" onclick="confirmAction('approve', <?php echo $row['id']; ?>)" 
                                                    class="w-9 h-9 rounded-full bg-green-50 text-green-600 hover:bg-green-500 hover:text-white shadow-sm transition-all transform hover:scale-110 flex items-center justify-center" title="Approve">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>

                                        <form id="reject-form-<?php echo $row['id']; ?>" method="POST">
                                            <input type="hidden" name="req_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="button" onclick="confirmAction('reject', <?php echo $row['id']; ?>)" 
                                                    class="w-9 h-9 rounded-full bg-red-50 text-red-600 hover:bg-red-500 hover:text-white shadow-sm transition-all transform hover:scale-110 flex items-center justify-center" title="Reject">
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide <?php echo $tab=='approved'?'bg-green-100 text-green-700 border border-green-200':'bg-red-100 text-red-700 border border-red-200'; ?>">
                                        <i class="fa-solid <?php echo $tab=='approved'?'fa-check-circle':'fa-times-circle'; ?>"></i>
                                        <?php echo $tab; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; 
                    else: ?>
                        <tr>
                            <td colspan="5" class="p-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-300">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                        <i class="fa-solid fa-inbox text-3xl opacity-50"></i>
                                    </div>
                                    <p class="text-gray-400 font-medium">No <?php echo $tab; ?> requests found.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // 1. Details Popup with Animation
    function showDetails(trx, wallet, method, amount) {
        Swal.fire({
            title: '<span class="text-gray-700">Transaction Details</span>',
            html: `
                <div class="text-left bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="mb-2 border-b pb-2 flex justify-between">
                        <span class="text-gray-500 text-sm">Amount:</span>
                        <span class="font-bold text-blue-600 text-lg">${amount}</span>
                    </div>
                    <div class="mb-2 flex justify-between">
                        <span class="text-gray-500 text-sm">Method:</span>
                        <span class="font-medium text-gray-800">${method}</span>
                    </div>
                    <div class="mb-2 flex justify-between">
                        <span class="text-gray-500 text-sm">Wallet:</span>
                        <span class="font-mono text-gray-800 bg-white px-2 rounded border">${wallet}</span>
                    </div>
                    <div class="flex flex-col mt-3">
                        <span class="text-gray-500 text-xs mb-1">Transaction ID:</span>
                        <div class="font-mono text-purple-600 font-bold bg-purple-50 p-2 rounded border border-purple-100 select-all text-center tracking-wider">
                            ${trx}
                        </div>
                    </div>
                </div>
            `,
            showCloseButton: true,
            showConfirmButton: false,
            buttonsStyling: false,
            customClass: {
                popup: 'rounded-2xl shadow-2xl animate__animated animate__zoomIn'
            }
        });
    }

    // 2. Confirmation Popup for Approve/Reject
    function confirmAction(type, id) {
        const isApprove = type === 'approve';
        
        Swal.fire({
            title: isApprove ? 'Approve Request?' : 'Reject Request?',
            text: isApprove ? "Amount will be added to user's wallet." : "This action cannot be undone.",
            icon: isApprove ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonColor: isApprove ? '#10B981' : '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: isApprove ? 'Yes, Approve!' : 'Yes, Reject!',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'px-4 py-2 rounded-lg font-bold',
                cancelButton: 'px-4 py-2 rounded-lg'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Processing...',
                    didOpen: () => { Swal.showLoading() },
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
                // Submit the specific form
                document.getElementById(type + '-form-' + id).submit();
            }
        });
    }

    // 3. Copy to Clipboard Helper
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text);
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
        });
        Toast.fire({
            icon: 'success',
            title: 'TrxID Copied!'
        });
    }
</script>

</body>
</html>
