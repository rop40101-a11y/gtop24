<?php
// Ensure database connection exists
if(isset($conn)) {
    // SELF-HEALING: Check if 'avatar' column exists, if not create it
    $colCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'avatar'");
    if($colCheck->num_rows == 0) {
        $conn->query("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL");
    }
}
?>

<div id="sidebarOverlay" onclick="toggleUserSidebar()" class="fixed inset-0 bg-black/50 z-[99998] hidden transition-opacity"></div>

<div id="userSidebar" class="fixed inset-y-0 right-0 w-[280px] bg-white transform translate-x-full transition-transform duration-300 z-[99999] lg:hidden flex flex-col border-l border-gray-200 font-bree shadow-2xl">
    
    <div class="p-6 border-b border-gray-100 flex items-center gap-4 bg-white">
        <?php 
        $s_name = "Guest";
        $s_email = "Please login";
        $is_log = false;
        $db_avatar = ""; // Default empty
        
        if(isset($_SESSION['user_id'])) {
            $is_log = true;
            $uid = $_SESSION['user_id'];
            if(isset($conn)) {
                // Fetch Name, Email AND Avatar
                $u_res = $conn->query("SELECT name, email, avatar FROM users WHERE id=$uid");
                if($u_res && $u_res->num_rows > 0){
                    $ud = $u_res->fetch_assoc();
                    $s_name = $ud['name'];
                    $s_email = $ud['email'];
                    $db_avatar = $ud['avatar']; // Get avatar from DB
                }
            }
        }
        
        // Avatar Logic: Use DB avatar if exists, else generate one
        if (!empty($db_avatar)) {
            $av_url = $db_avatar;
        } else {
            $av_url = "https://ui-avatars.com/api/?name=".urlencode($s_name)."&background=random&color=fff";
        }
        ?>
        
        <img src="<?php echo $av_url; ?>" class="w-12 h-12 rounded-full border border-gray-100 object-cover">
        
        <div class="flex-1 overflow-hidden">
            <h2 class="font-medium text-gray-800 text-base truncate"><?php echo $s_name; ?></h2>
            <p class="text-xs text-gray-500 truncate font-sans"><?php echo $s_email; ?></p>
        </div>
    </div>

    <div class="px-6 pb-4 pt-4">
        <?php if($is_log): ?>
            <a href="logout.php" class="bg-[#2B71AD] hover:opacity-90 text-white text-sm font-medium w-full py-2.5 rounded-lg flex items-center justify-center gap-2 transition-opacity shadow-sm">
                <svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="width: 0.83rem;" fill="currentColor">
                  <path d="M388.5 46.3C457.9 90.3 504 167.8 504 256c0 136.8-110.8 247.7-247.5 248C120 504.3 8.2 393 8 256.4 7.9 168 54 90.3 123.5 46.3c5.8-3.7 13.5-1.8 16.9 4.2l11.8 20.9c3.1 5.5 1.4 12.5-3.9 15.9C92.8 122.9 56 185.1 56 256c0 110.5 89.5 200 200 200s200-89.5 200-200c0-70.9-36.8-133.1-92.3-168.6-5.3-3.4-7-10.4-3.9-15.9l11.8-20.9c3.3-6.1 11.1-7.9 16.9-4.3zM280 276V12c0-6.6-5.4-12-12-12h-24c-6.6 0-12 5.4-12 12v264c0 6.6 5.4 12 12 12h24c6.6 0 12-5.4 12-12z"></path>
                </svg>
                Logout
            </a>
        <?php else: ?>
            <a href="login.php" class="bg-[#2B71AD] hover:opacity-90 text-white text-sm font-medium w-full py-2.5 rounded-lg block text-center transition-opacity shadow-sm">Login</a>
        <?php endif; ?>
    </div>

    <nav class="px-4 py-2 space-y-1 flex-1 overflow-y-auto">
        
        <a href="index.php" class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 group transition-colors">
            <div class="w-5 h-5 text-gray-700">
                <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class="w-full h-full">
                    <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
            </div>
            <span class="font-medium text-gray-700 text-sm tracking-wide">Home</span>
        </a>

        <a href="profile.php" class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 group transition-colors">
            <div class="w-5 h-5 text-gray-700">
                <svg width="100%" height="100%" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 21V19C20 17.3431 18.6569 16 17 16H7C5.34315 16 4 17.3431 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <span class="font-medium text-gray-700 text-sm tracking-wide">My Account</span>
        </a>

        <a href="order.php" class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 group transition-colors">
            <div class="w-5 h-5 text-gray-700">
                 <svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                </svg>
            </div>
            <span class="font-medium text-gray-700 text-sm tracking-wide">My Orders</span>
        </a>

        <a href="mycode.php" class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 group transition-colors">
            <div class="w-5 h-5 text-gray-700">
                 <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
            </div>
            <span class="font-medium text-gray-700 text-sm tracking-wide">My Codes</span>
        </a>

        <a href="transactions.php" class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 group transition-colors">
            <div class="w-5 h-5 text-gray-700">
                 <svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <span class="font-medium text-gray-700 text-sm tracking-wide">My Transactions</span>
        </a>

        <a href="addmoney.php" class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 group transition-colors">
            <div class="w-5 h-5 text-gray-700">
                <svg viewBox="0 0 24 24">
                    <path fill="currentColor" d="M3 0V3H0V5H3V8H5V5H8V3H5V0H3M10 3V5H19V7H13C11.9 7 11 7.9 11 9V15C11 16.1 11.9 17 13 17H19V19H5V10H3V19C3 20.1 3.89 21 5 21H19C20.1 21 21 20.1 21 19V16.72C21.59 16.37 22 15.74 22 15V9C22 8.26 21.59 7.63 21 7.28V5C21 3.9 20.1 3 19 3H10M13 9H20V15H13V9M16 10.5A1.5 1.5 0 0 0 14.5 12A1.5 1.5 0 0 0 16 13.5A1.5 1.5 0 0 0 17.5 12A1.5 1.5 0 0 0 16 10.5Z"></path>
                </svg>
            </div>
            <span class="font-medium text-gray-700 text-sm tracking-wide">Add Money</span>
        </a>

    </nav>
    
    <div class="p-6 border-t border-gray-100 mt-auto">
        <a href="<?php echo getSetting($conn, 'fab_link'); ?>" target="_blank" class="bg-[#2B71AD] hover:opacity-90 text-white w-full py-3 rounded-lg flex items-center justify-center gap-3 font-medium transition-opacity shadow-sm">
            <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" height="20" width="20" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 2C6.486 2 2 6.486 2 12v4.143C2 17.167 2.897 18 4 18h1a1 1 0 0 0 1-1v-5.143a1 1 0 0 0-1-1h-.908C4.648 6.987 7.978 4 12 4s7.352 2.987 7.908 6.857H19a1 1 0 0 0-1 1V18c0 1.103-.897 2-2 2h-2v-1h-4v3h6c2.206 0 4-1.794 4-4 1.103 0 2-.833 2-1.857V12c0-5.514-4.486-10-10-10z"></path>
            </svg>
            Support
        </a>
    </div>

</div>

<script>
    function toggleUserSidebar() {
        const sb = document.getElementById('userSidebar');
        const ov = document.getElementById('sidebarOverlay');
        const isHidden = sb.classList.contains('translate-x-full');
        
        if (isHidden) {
            sb.classList.remove('translate-x-full');
            ov.classList.remove('hidden');
        } else {
            sb.classList.add('translate-x-full');
            ov.classList.add('hidden');
        }
    }
</script>
