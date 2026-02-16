<?php 
include 'common/header.php'; 

// ====================================================
// FETCH STATISTICS
// ====================================================
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$total_orders = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$rev_query = $conn->query("SELECT SUM(amount) FROM orders WHERE status='completed'");
$total_revenue = $rev_query->fetch_row()[0] ?? 0;
$pending_orders = $conn->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetch_row()[0];

// Fetch Recent 10 Orders
$recent_orders = $conn->query("
    SELECT o.*, u.name as user_name, g.name as game_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    LEFT JOIN games g ON o.game_id = g.id 
    ORDER BY o.created_at DESC LIMIT 10
");
?>

<link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&family=Noto+Serif+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* TYPOGRAPHY */
    body { font-family: 'Lato', 'Noto Serif Bengali', sans-serif; background-color: #f8fafc; color: #334155; }
    
    /* NEW STATS CARD DESIGN */
    .stat-card {
        background: white;
        border-radius: 8px;
        padding: 16px 20px; /* Compact Padding */
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid #e2e8f0;
        transition: border-color 0.2s;
    }
    .stat-card:hover { border-color: #eab308; }

    .stat-label { font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-value { font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 4px; }
    
    .icon-box {
        width: 42px; height: 42px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
    }

    /* COLORS */
    .bg-yellow-soft { background: #fefce8; color: #ca8a04; }
    .bg-green-soft { background: #f0fdf4; color: #16a34a; }
    .bg-blue-soft { background: #eff6ff; color: #2563eb; }
    .bg-red-soft { background: #fef2f2; color: #dc2626; }

    /* TABLE DESIGN */
    .table-container {
        background: white;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-top: 20px;
    }
    
    .pro-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    
    .pro-table th { 
        text-align: left; padding: 12px 16px; 
        background: #f8fafc; color: #64748b; font-weight: 700; 
        text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .pro-table td { 
        padding: 12px 16px; border-bottom: 1px solid #f1f5f9; 
        color: #1e293b; vertical-align: middle; font-weight: 500;
    }
    
    .pro-table tr:last-child td { border-bottom: none; }

    /* STATUS PILLS */
    .status-pill { padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
    .st-pending { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
    .st-completed { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
    .st-cancelled { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }
</style>

<div class="container mx-auto px-4 py-6 max-w-full">
    
    <div class="mb-5 px-1">
        <h1 class="text-xl font-bold text-gray-800">Dashboard</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="stat-card">
            <div>
                <p class="stat-label">Total Revenue</p>
                <h2 class="stat-value">৳ <?php echo number_format($total_revenue); ?></h2>
            </div>
            <div class="icon-box bg-green-soft"><i class="fa-solid fa-bangladeshi-taka-sign"></i></div>
        </div>

        <div class="stat-card">
            <div>
                <p class="stat-label">Total Orders</p>
                <h2 class="stat-value"><?php echo number_format($total_orders); ?></h2>
            </div>
            <div class="icon-box bg-yellow-soft"><i class="fa-solid fa-cart-shopping"></i></div>
        </div>

        <div class="stat-card">
            <div>
                <p class="stat-label">Total Users</p>
                <h2 class="stat-value"><?php echo number_format($total_users); ?></h2>
            </div>
            <div class="icon-box bg-blue-soft"><i class="fa-solid fa-users"></i></div>
        </div>

        <div class="stat-card border-l-4 border-l-red-500">
            <div>
                <p class="stat-label text-red-500">Pending</p>
                <h2 class="stat-value text-red-600"><?php echo number_format($pending_orders); ?></h2>
            </div>
            <div class="icon-box bg-red-soft"><i class="fa-solid fa-clock"></i></div>
        </div>

    </div>

    <div class="table-container">
        <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center bg-white">
            <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-list-ul text-yellow-500"></i> Recent Orders
            </h3>
            <a href="order.php" class="text-xs font-bold text-yellow-600 hover:text-yellow-700 hover:underline">View All</a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th width="10%">ID</th>
                        <th width="20%">User</th>
                        <th width="25%">Product</th>
                        <th width="15%">Price</th>
                        <th width="15%">Date</th>
                        <th width="15%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($recent_orders && $recent_orders->num_rows > 0): 
                        while($row = $recent_orders->fetch_assoc()): 
                            $st = strtolower($row['status']);
                            $badge = 'st-pending';
                            
                            if($st == 'completed') $badge = 'st-completed';
                            if($st == 'cancelled') $badge = 'st-cancelled';

                            $prodName = htmlspecialchars((string)($row['product_name'] ?? 'Topup')); 
                    ?>
                    <tr>
                        <td class="font-mono text-xs text-gray-500 font-bold">#<?php echo $row['id']; ?></td>
                        <td>
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded bg-gray-100 flex items-center justify-center text-[10px] font-bold text-gray-500">
                                    <?php echo strtoupper(substr($row['user_name'] ?? 'G', 0, 1)); ?>
                                </span>
                                <span class="font-semibold text-gray-700 text-sm"><?php echo htmlspecialchars($row['user_name'] ?? 'Guest'); ?></span>
                            </div>
                        </td>
                        <td class="text-sm text-gray-600">
                            <?php echo htmlspecialchars($row['game_name']); ?>
                            <span class="text-gray-400 text-[10px] block"><?php echo $prodName; ?></span>
                        </td>
                        <td class="font-bold text-gray-800">৳ <?php echo number_format($row['amount']); ?></td>
                        <td class="text-xs text-gray-400"><?php echo date('d M, h:i A', strtotime($row['created_at'])); ?></td>
                        <td>
                            <span class="status-pill <?php echo $badge; ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-8">
                            <p class="text-xs text-gray-400">No recent orders found.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
