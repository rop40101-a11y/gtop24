<link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&display=swap" rel="stylesheet">

<style>
    /* GLOBAL FONT */
    .font-lato { font-family: 'Lato', sans-serif; }

    /* CUSTOM SCROLLBAR */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
    .custom-scrollbar:hover::-webkit-scrollbar-thumb { background: #d1d5db; }

    /* CUSTOM GRADIENT PAGE LOADER */
    #custom-progress-bar {
        position: fixed;
        top: 0;
        left: 0;
        height: 4px; /* Visible Thickness */
        width: 0%;
        z-index: 999999; /* Highest Layer */
        /* Vibrant Gradient: Yellow -> Red -> Blue */
        background: linear-gradient(90deg, #FFD700, #ff5e62, #2563eb);
        box-shadow: 0 2px 10px rgba(255, 215, 0, 0.5); /* Glow Effect */
        transition: width 0.4s cubic-bezier(0.25, 0.8, 0.25, 1), opacity 0.3s ease;
        opacity: 1;
    }
</style>

<div id="custom-progress-bar"></div>

<aside id="adminSidebar" class="fixed inset-y-0 left-0 w-72 bg-white border-r border-gray-200 transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-50 md:static md:block flex flex-col flex-shrink-0 font-lato shadow-2xl md:shadow-none">
    
    <div class="h-24 flex items-center justify-center border-b border-gray-100 relative shrink-0">
        <a href="index.php" class="block transition-transform hover:scale-105">
            <img src="../res/logo.png" alt="Logo" class="h-12 w-auto object-contain">
        </a>
        <button onclick="toggleSidebar()" class="md:hidden absolute right-5 text-gray-400 hover:text-red-500 focus:outline-none transition-colors">
            <i class="fa-solid fa-xmark text-2xl"></i>
        </button>
    </div>
    
    <nav class="flex-1 overflow-y-auto p-5 space-y-2 custom-scrollbar">
        
        <?php 
        function navItem($file, $icon, $label) {
            $current = basename($_SERVER['PHP_SELF']);
            
            // ACTIVE: Solid Yellow, White Text, Bold
            $activeClass = "bg-yellow-500 text-white shadow-md font-bold translate-x-1"; 
            
            // INACTIVE: Gray Text, Hover Yellow
            $inactiveClass = "text-gray-600 hover:bg-yellow-50 hover:text-yellow-600 font-medium";
            
            $class = ($current == $file) ? $activeClass : $inactiveClass;
            
            echo '<a href="'.$file.'" class="'.$class.' flex items-center gap-4 py-3.5 px-5 rounded-xl transition-all duration-200 text-[15px]">
                    <i class="'.$icon.' w-5 text-center text-lg"></i> 
                    <span>'.$label.'</span>
                  </a>';
        }

        // --- DASHBOARD & ORDERS ---
        navItem('index.php', 'fa-solid fa-chart-pie', 'Dashboard');
        navItem('order.php', 'fa-solid fa-cart-shopping', 'Orders List');
        navItem('addmoney_request.php', 'fa-solid fa-money-bill-transfer', 'Wallet Requests');
        
        // --- USERS ---
        navItem('user.php', 'fa-solid fa-users', 'User Management');
        
        // --- GAME & PRODUCT MANAGEMENT ---
        navItem('categories.php', 'fa-solid fa-layer-group', 'Game Categories');
        navItem('game.php', 'fa-solid fa-gamepad', 'Games List');
        navItem('product.php', 'fa-solid fa-tags', 'Products & Topup');
        navItem('redeemcode.php', 'fa-solid fa-ticket', 'Redeem Codes');
        
        // --- FRONTEND CUSTOMIZATION ---
        navItem('sliders.php', 'fa-solid fa-images', 'Banner Sliders');
        navItem('popup.php', 'fa-solid fa-bullhorn', 'Announcement Popup');
        
        // --- CONFIGURATION ---
        navItem('paymentmethod.php', 'fa-solid fa-wallet', 'Payment Gateways');
        navItem('setting.php', 'fa-solid fa-gears', 'Website Settings');
        ?>

    </nav>

    <div class="p-5 border-t border-gray-100 shrink-0">
        <a href="../index.php" target="_blank" class="flex items-center justify-center gap-2 w-full bg-gray-50 border border-gray-200 text-gray-600 py-3 rounded-xl text-sm hover:bg-yellow-500 hover:text-white hover:border-yellow-500 transition-all duration-300 font-bold shadow-sm group">
            <i class="fa-solid fa-external-link-alt group-hover:rotate-45 transition-transform"></i> Visit Website
        </a>
    </div>
</aside>

<div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/40 z-40 hidden md:hidden transition-opacity"></div>

<script>
    // --- SIDEBAR LOGIC ---
    function toggleSidebar() {
        const sb = document.getElementById('adminSidebar');
        const ov = document.getElementById('sidebarOverlay');
        
        if (sb.classList.contains('-translate-x-full')) {
            sb.classList.remove('-translate-x-full');
            ov.classList.remove('hidden');
        } else {
            sb.classList.add('-translate-x-full');
            ov.classList.add('hidden');
        }
    }

    // --- PROGRESS BAR ANIMATION LOGIC ---
    // Runs immediately to simulate "Page Loading" visually
    document.addEventListener("DOMContentLoaded", function() {
        const loader = document.getElementById('custom-progress-bar');
        
        // 1. Start Fast to 40%
        loader.style.width = '40%';
        
        // 2. Slow down a bit to 70%
        setTimeout(() => {
            loader.style.width = '70%';
        }, 150);

        // 3. Complete when window fully loads
        window.addEventListener('load', () => {
            loader.style.width = '100%';
            
            // Fade Out
            setTimeout(() => {
                loader.style.opacity = '0';
            }, 400);

            // Reset (Hidden)
            setTimeout(() => {
                loader.style.width = '0%';
            }, 800);
        });
        
        // Fallback: If window.load fires too fast or missed, force complete
        setTimeout(() => {
            if(loader.style.width !== '100%') {
                loader.style.width = '100%';
                setTimeout(() => { loader.style.opacity = '0'; }, 300);
            }
        }, 1000);
    });
</script>
