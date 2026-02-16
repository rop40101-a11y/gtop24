<?php 
include 'common/header.php'; 

// ====================================================
// SELF-HEALING DATABASE
// ====================================================
$chk = $conn->query("SHOW COLUMNS FROM users LIKE 'support_pin'");
if($chk->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN support_pin VARCHAR(6) DEFAULT '0000'");
}

// ====================================================
// HANDLE ACTIONS
// ====================================================

// 1. DELETE USER
if(isset($_POST['delete_user_id'])) {
    $uid = (int)$_POST['delete_user_id'];
    $conn->query("DELETE FROM users WHERE id=$uid");
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({ icon: 'success', title: 'Deleted', text: 'User removed successfully.', timer: 1500, showConfirmButton: false });
        });
    </script>";
}

// 2. EDIT USER (Includes Password & Email)
if(isset($_POST['update_user'])) {
    $uid = (int)$_POST['edit_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $bal = (float)$_POST['balance'];
    $pin = $conn->real_escape_string($_POST['support_pin']);
    
    // Base Update
    $sql = "UPDATE users SET name='$name', phone='$phone', email='$email', balance='$bal', support_pin='$pin'";

    // Password Update Logic (Only if filled)
    if(!empty($_POST['password'])) {
        $pass = $_POST['password']; 
        // NOTE: Use password_hash($pass, PASSWORD_DEFAULT) if your login system uses hashing. 
        // Here we update it directly as requested for simple systems.
        $sql .= ", password='$pass'";
    }

    $sql .= " WHERE id=$uid";
    $conn->query($sql);
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({ icon: 'success', title: 'Updated', text: 'User details saved.', timer: 1500, showConfirmButton: false });
        });
    </script>";
}

// ====================================================
// SEARCH & PAGINATION
// ====================================================
$search = isset($_GET['s']) ? $conn->real_escape_string($_GET['s']) : '';
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;
$start = ($page - 1) * $limit;

$where = "WHERE 1";
if(!empty($search)) {
    $where .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%' OR id = '$search')";
}

$total_rows = $conn->query("SELECT COUNT(*) FROM users $where")->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT u.*, 
        (SELECT COALESCE(SUM(amount), 0) FROM orders WHERE user_id = u.id AND status = 'completed') as total_spent 
        FROM users u 
        $where 
        ORDER BY u.id DESC LIMIT $start, $limit";
$users = $conn->query($sql);
?>

<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&family=Noto+Serif+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* GLOBAL THEME */
    body { font-family: 'Lato', 'Noto Serif Bengali', sans-serif; background-color: #f3f4f6; color: #1f2937; }
    
    /* TABLE CONTAINER */
    .table-card {
        background: white; border: 1px solid #e5e7eb; border-radius: 4px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    /* TABLE STYLES */
    .pro-table th { 
        text-align: left; padding: 12px 16px; 
        background: #f9fafb; color: #4b5563; font-weight: 700; 
        text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em;
        border-bottom: 2px solid #e5e7eb; white-space: nowrap;
    }
    .pro-table td { 
        padding: 14px 16px; border-bottom: 1px solid #f3f4f6; 
        color: #374151; vertical-align: middle; font-size: 13px; white-space: nowrap;
    }
    .pro-table tr:hover td { background-color: #fdfdfd; }

    /* AVATAR */
    .user-avatar {
        width: 32px; height: 32px; border-radius: 4px;
        background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 800; text-transform: uppercase;
    }

    /* BADGES & TAGS */
    .badge-balance {
        background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7;
        padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 12px;
    }
    
    .credential-box {
        display: flex; flex-direction: column; gap: 2px;
    }
    .email-text { font-weight: 600; color: #4b5563; font-size: 13px; }
    .pass-mask { 
        font-family: monospace; color: #9ca3af; font-size: 14px; 
        background: #f3f4f6; padding: 0 6px; border-radius: 3px; display: inline-block; width: fit-content;
    }

    /* ACTION BTNS */
    .action-btn {
        width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;
        border-radius: 4px; border: 1px solid #e5e7eb; color: #6b7280; background: white;
        transition: all 0.2s; cursor: pointer;
    }
    .action-btn:hover { background: #f3f4f6; color: #111827; }
    .btn-del:hover { background: #fef2f2; border-color: #fee2e2; color: #ef4444; }

    /* MODAL */
    #editModal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 50; align-items: center; justify-content: center; }
    .modal-content {
        background: white; width: 95%; max-width: 500px; border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2); overflow: hidden; animation: slideIn 0.2s ease-out;
    }
    @keyframes slideIn { from { transform: scale(0.98); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    
    .input-group { margin-bottom: 12px; }
    .input-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 4px; text-transform: uppercase; }
    .input-field { 
        width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px; 
    }
    .input-field:focus { border-color: #eab308; outline: none; }
</style>

<div class="container mx-auto px-4 py-8 max-w-full">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
            <p class="text-xs text-gray-500 font-medium mt-0.5">Total Users: <?php echo $total_rows; ?></p>
        </div>
        <form class="flex w-full md:w-auto shadow-sm">
            <input type="text" name="s" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search Name, Email, Phone..." class="px-4 py-2 border border-gray-300 rounded-l-md text-sm w-full md:w-64 focus:outline-none focus:border-yellow-500">
            <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded-r-md text-sm font-bold hover:bg-yellow-600 transition">
                <i class="fa-solid fa-search"></i>
            </button>
        </form>
    </div>

    <div class="table-card">
        <div class="overflow-x-auto">
            <table class="w-full pro-table border-collapse">
                <thead>
                    <tr>
                        <th width="40">ID</th>
                        <th>User Profile</th>
                        <th>Credentials (Email/Pass)</th>
                        <th>Wallet</th>
                        <th>Total Spent</th>
                        <th>Support PIN</th>
                        <th>Joined</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($users && $users->num_rows > 0): 
                        while($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td><span class="font-mono text-xs font-bold text-gray-400">#<?php echo $u['id']; ?></span></td>

                        <td>
                            <div class="flex items-center gap-3">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($u['name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="font-bold text-gray-800 text-sm"><?php echo htmlspecialchars($u['name']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($u['phone']); ?></div>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="credential-box">
                                <div class="email-text"><?php echo htmlspecialchars($u['email']); ?></div>
                                <div class="pass-mask">••••••••</div>
                            </div>
                        </td>

                        <td>
                            <div class="badge-balance">
                                ৳ <?php echo number_format($u['balance'], 2); ?>
                            </div>
                        </td>

                        <td>
                            <span class="text-xs font-bold text-gray-600 bg-gray-50 border border-gray-200 px-2 py-1 rounded">
                                ৳ <?php echo number_format($u['total_spent']); ?>
                            </span>
                        </td>

                        <td>
                            <span class="font-mono text-xs font-bold text-orange-700 bg-orange-50 border border-orange-100 px-2 py-1 rounded">
                                <i class="fa-solid fa-lock text-[9px] mr-1"></i>
                                <?php echo !empty($u['support_pin']) ? $u['support_pin'] : '0000'; ?>
                            </span>
                        </td>

                        <td class="text-xs text-gray-500 font-medium">
                            <?php echo date('d M Y', strtotime($u['created_at'])); ?>
                        </td>

                        <td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick='openEditModal(<?php echo json_encode($u); ?>)' class="action-btn" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button onclick="confirmDelete(<?php echo $u['id']; ?>)" class="action-btn btn-del" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; 
                    else: ?>
                        <tr><td colspan="8" class="text-center py-8 text-gray-400 text-sm italic">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($total_pages > 1): ?>
        <div class="border-t border-gray-200 bg-gray-50 px-4 py-3 flex justify-between items-center">
            <span class="text-xs text-gray-500 font-medium hidden md:inline">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
            <div class="flex gap-1 w-full md:w-auto justify-end">
                <a href="?page=<?php echo $page-1; ?>&s=<?php echo $search; ?>" class="px-3 py-1.5 border border-gray-300 bg-white rounded text-xs font-bold <?php echo ($page<=1)?'pointer-events-none opacity-50 text-gray-300':'text-gray-600 hover:bg-gray-50'; ?>">Prev</a>
                <a href="?page=<?php echo $page+1; ?>&s=<?php echo $search; ?>" class="px-3 py-1.5 border border-gray-300 bg-white rounded text-xs font-bold <?php echo ($page>=$total_pages)?'pointer-events-none opacity-50 text-gray-300':'text-gray-600 hover:bg-gray-50'; ?>">Next</a>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>

<div id="editModal">
    <div class="modal-content">
        <form method="POST">
            <div class="flex justify-between items-center p-4 border-b bg-gray-50">
                <h3 class="font-bold text-gray-800">Edit User</h3>
                <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-red-500 text-2xl leading-none">&times;</button>
            </div>
            
            <div class="p-5">
                <input type="hidden" name="edit_id" id="edit_id">
                <input type="hidden" name="update_user" value="1">

                <div class="grid grid-cols-2 gap-4">
                    <div class="input-group">
                        <label class="input-label">Full Name</label>
                        <input type="text" name="name" id="edit_name" class="input-field" required>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Phone</label>
                        <input type="text" name="phone" id="edit_phone" class="input-field" required>
                    </div>

                    <div class="input-group col-span-2">
                        <label class="input-label">Email Address</label>
                        <input type="email" name="email" id="edit_email" class="input-field" required>
                    </div>
                    
                    <div class="input-group col-span-2">
                        <label class="input-label text-yellow-600">Set New Password <span class="text-gray-300 font-normal">(Leave empty to keep current)</span></label>
                        <input type="text" name="password" class="input-field border-yellow-200 bg-yellow-50" placeholder="Type to reset password...">
                    </div>

                    <div class="input-group">
                        <label class="input-label text-green-700">Wallet Balance</label>
                        <input type="number" step="0.01" name="balance" id="edit_balance" class="input-field font-bold text-green-700" required>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Support PIN</label>
                        <input type="text" name="support_pin" id="edit_pin" class="input-field font-mono text-center" maxlength="6">
                    </div>
                </div>

                <button type="submit" class="w-full bg-yellow-500 text-white py-3 rounded-md font-bold hover:bg-yellow-600 transition shadow-sm mt-3">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<form id="deleteForm" method="POST" class="hidden">
    <input type="hidden" name="delete_user_id" id="del_id_input">
</form>

<script>
    function openEditModal(user) {
        document.getElementById('edit_id').value = user.id;
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_phone').value = user.phone;
        document.getElementById('edit_balance').value = user.balance;
        document.getElementById('edit_pin').value = user.support_pin || '0000';
        document.getElementById('editModal').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    window.onclick = function(e) {
        if (e.target == document.getElementById('editModal')) closeEditModal();
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete User?',
            text: "This cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('del_id_input').value = id;
                document.getElementById('deleteForm').submit();
            }
        });
    }
</script>
