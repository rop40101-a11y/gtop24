<?php
// Safe Variable Definitions
$fab_link = '#';
if(isset($conn)) {
    $conn->query("CREATE TABLE IF NOT EXISTS settings (id INT AUTO_INCREMENT PRIMARY KEY) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    // Check FAB Link Column
    $chk = $conn->query("SHOW COLUMNS FROM settings LIKE 'fab_link'");
    if($chk && $chk->num_rows == 0) {
        $conn->query("ALTER TABLE settings ADD COLUMN fab_link VARCHAR(255) DEFAULT 'https://wa.me/'");
    }

    if (!function_exists('getSetting')) { 
        function getSetting($conn, $key) { 
            $q = $conn->query("SELECT value FROM settings WHERE name='$key' LIMIT 1");
            return ($q && $q->num_rows > 0) ? $q->fetch_assoc()['value'] : '';
        } 
    }

    $l = getSetting($conn, 'fab_link');
    if(!empty($l)) $fab_link = $l;
}

// User Avatar Logic
$user_avatar = 'res/images/default-avatar.png'; 
$is_logged_in = false;
if(isset($_SESSION['user_id'])) {
    $is_logged_in = true;
    $uid_bottom = (int)$_SESSION['user_id'];
    if(isset($conn)) {
        $u_res = $conn->query("SELECT name FROM users WHERE id=$uid_bottom");
        if($u_res && $u_res->num_rows > 0) {
            $u = $u_res->fetch_assoc();
            if(!empty($u['name'])) {
                $user_avatar = "https://ui-avatars.com/api/?name=" . urlencode($u['name']) . "&background=random&color=fff";
            }
        }
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Bree+Serif&display=swap" rel="stylesheet">

<style>
    /* 1. GRADIENT PAGE LOADER (Browser Progress Style) */
    #page-loader {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        height: 4px !important;
        width: 0 !important;
        z-index: 99999 !important;
        /* Vibrant Gradient */
        background: linear-gradient(90deg, #dc2626, #facc15, #2563eb) !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1) !important;
        transition: width 0.4s ease-out !important;
    }

    /* 2. SUPPORT BUTTON */
    #support-btn {
        position: fixed !important;
        bottom: 128px !important;
        right: 16px !important;
        z-index: 9998 !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
    }
    #support-label {
        background-color: #dc2626 !important;
        color: white !important;
        font-size: 12px !important;
        padding: 6px 12px !important;
        border-radius: 4px !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        font-family: 'Noto Sans Bengali', sans-serif !important;
        letter-spacing: 0.025em !important;
    }
    #support-icon-box {
        width: 56px !important;
        height: 56px !important;
        background-color: #dc2626 !important;
        border-radius: 9999px !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: white !important;
        font-size: 24px !important;
        transition: transform 0.2s !important;
    }
    #support-icon-box:hover { transform: scale(1.05) !important; }

    /* 3. BOTTOM NAV */
    #bottom-nav {
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        width: 100% !important;
        background-color: #ffffff !important;
        border-top: 1px solid #e5e7eb !important;
        display: flex !important;
        justify-content: space-between !important;
        padding: 0 8px !important;
        align-items: center !important;
        z-index: 9999 !important;
        padding-bottom: env(safe-area-inset-bottom) !important;
        height: 74px !important;
        box-shadow: none !important;
        color: #000000 !important; 
    }

    .nav-link-item {
        flex: 1 !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        gap: 6px !important;
        padding: 4px !important;
        text-decoration: none !important;
        color: #000000 !important;
        transition: none !important;
    }
    
    .nav-link-item:hover, .nav-link-item:active, .nav-link-item:focus {
        color: #000000 !important;
        opacity: 1 !important;
        background: transparent !important;
    }
    
    .nav-icon-svg {
        width: 24px !important;
        height: 24px !important;
        color: #000000 !important; 
        fill: none !important;
    }
    
    .nav-icon-svg path[fill="currentColor"] {
        fill: #000000 !important;
    }

    .nav-text-label {
        font-size: 11px !important;
        font-family: 'Bree Serif', serif !important; 
        font-weight: 400 !important;
        color: #000000 !important;
    }
</style>

<div id="page-loader"></div>

<div id="support-btn">
    <div id="support-label">সাহায্য লাগবে ?</div>
    <a href="<?php echo $fab_link; ?>" target="_blank" id="support-icon-box">
        <i class="fa-solid fa-phone-volume rotate-[-10deg]"></i>
    </a>
</div>

<nav id="bottom-nav">
    <a href="index.php" class="nav-link-item spa-link">
        <div class="w-6 h-6">
            <svg stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" viewBox="0 0 24 24" class="nav-icon-svg">
                <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
        </div>
        <span class="nav-text-label">Home</span>
    </a>

    <a href="addmoney.php" class="nav-link-item spa-link">
        <div class="w-6 h-6">
            <svg class="nav-icon-svg" viewBox="0 0 24 24">
                <path fill="currentColor" d="M3 0V3H0V5H3V8H5V5H8V3H5V0H3M10 3V5H19V7H13C11.9 7 11 7.9 11 9V15C11 16.1 11.9 17 13 17H19V19H5V10H3V19C3 20.1 3.89 21 5 21H19C20.1 21 21 20.1 21 19V16.72C21.59 16.37 22 15.74 22 15V9C22 8.26 21.59 7.63 21 7.28V5C21 3.9 20.1 3 19 3H10M13 9H20V15H13V9M16 10.5A1.5 1.5 0 0 0 14.5 12A1.5 1.5 0 0 0 16 13.5A1.5 1.5 0 0 0 17.5 12A1.5 1.5 0 0 0 16 10.5Z"></path>
            </svg>
        </div>
        <span class="nav-text-label">Add Money</span>
    </a>

    <a href="order.php" class="nav-link-item spa-link">
        <div class="w-6 h-6">
            <svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" stroke="currentColor" viewBox="0 0 24 24" class="nav-icon-svg">
                <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
            </svg>
        </div>
        <span class="nav-text-label">Orders</span>
    </a>

    <a href="mycode.php" class="nav-link-item spa-link">
        <div class="w-6 h-6">
            <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round" class="nav-icon-svg">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
        </div>
        <span class="nav-text-label">Codes</span>
    </a>

    <a href="profile.php" class="nav-link-item spa-link">
        <div class="w-6 h-6 flex items-center justify-center">
            <?php if($is_logged_in): ?>
                <img src="<?php echo $user_avatar; ?>" alt="User" class="w-full h-full rounded-full border border-gray-300 object-cover block">
            <?php else: ?>
                <svg width="100%" height="100%" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="nav-icon-svg">
                    <path d="M20 21V19C20 17.3431 18.6569 16 17 16H7C5.34315 16 4 17.3431 4 19V21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            <?php endif; ?>
        </div>
        <span class="nav-text-label">Profile</span>
    </a>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const loader = document.getElementById('page-loader');

        // Initial Page Load Animation
        loader.style.width = '70%';
        setTimeout(() => { loader.style.width = '100%'; }, 200);
        setTimeout(() => { loader.style.width = '0%'; }, 500);

        // --- SPA ENGINE (UI SAFE) ---
        // This function intercepts links, loads the new page in background,
        // then swaps content INSTANTLY without a white flash.
        // It then RE-RUNS all scripts so UI elements don't break.
        
        function handleNavigation(e) {
            // Find closest anchor tag
            const link = e.target.closest('a.spa-link');
            if (!link) return;

            const href = link.getAttribute('href');
            // Ignore external links or empty links
            if (!href || href === '#' || href.startsWith('http') && !href.includes(window.location.hostname)) return;

            e.preventDefault();

            // 1. Start Progress Bar
            loader.style.opacity = '1';
            loader.style.width = '20%';
            
            // 2. Fetch New Page in Background
            fetch(href)
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    loader.style.width = '60%';
                    return response.text();
                })
                .then(html => {
                    loader.style.width = '90%';
                    
                    // 3. Create Virtual Document
                    const parser = new DOMParser();
                    const newDoc = parser.parseFromString(html, 'text/html');

                    // 4. Update Title
                    document.title = newDoc.title;

                    // 5. Replace Body Content (Instant Swap)
                    // We only replace the children of body to keep body attributes if any
                    document.body.innerHTML = newDoc.body.innerHTML;

                    // 6. Update URL History
                    window.history.pushState({}, '', href);
                    window.scrollTo(0, 0);

                    // 7. CRITICAL: Re-Execute Scripts to Fix UI Breakage
                    // Browsers don't run <script> tags inserted via innerHTML.
                    // We must manually create new script elements.
                    const scripts = document.body.querySelectorAll('script');
                    scripts.forEach(oldScript => {
                        const newScript = document.createElement('script');
                        
                        // Copy attributes (src, type, etc.)
                        Array.from(oldScript.attributes).forEach(attr => {
                            newScript.setAttribute(attr.name, attr.value);
                        });

                        // Copy content
                        if (oldScript.src) {
                            // If external, we let it load
                        } else {
                            newScript.textContent = oldScript.textContent;
                        }
                        
                        // Replace old script with new executable script
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });

                    // 8. Re-attach Navigation Listeners to new links
                    // (Since we replaced the body, old listeners are gone)
                    attachNavListeners();

                    // Finish Animation
                    loader.style.width = '100%';
                    setTimeout(() => { loader.style.width = '0%'; }, 300);
                })
                .catch(err => {
                    console.error('Nav Error:', err);
                    window.location.href = href; // Fallback to normal reload
                });
        }

        function attachNavListeners() {
            // Remove old listener to prevent duplicates (though body swap usually handles this)
            document.removeEventListener('click', handleNavigation);
            // Attach delegate listener to document
            document.addEventListener('click', handleNavigation);
        }

        // Initialize
        attachNavListeners();
        
        // Handle Browser Back Button
        window.addEventListener('popstate', () => {
            window.location.reload(); // Simple reload to ensure state consistency
        });
    });
</script>

<div id="notifModal" class="fixed inset-0 z-[120] flex items-center justify-center bg-black/60 hidden">
    <div class="bg-white w-80 rounded-2xl shadow-2xl p-6 text-center transform scale-95 transition-all duration-300" id="notifContent">
        <div id="notifIcon" class="text-5xl mb-4"></div>
        <h3 id="notifTitle" class="text-xl font-bold text-gray-800 mb-2"></h3>
        <p id="notifMsg" class="text-sm text-gray-500 mb-6"></p>
        <button onclick="closeNotif()" class="bg-[#2B71AD] text-white w-full py-3 rounded-xl font-bold hover:opacity-90">Okay</button>
    </div>
</div>

<script>
    function showNotif(type, title, msg) {
        const modal = document.getElementById('notifModal');
        const content = document.getElementById('notifContent');
        const iconEl = document.getElementById('notifIcon');
        
        document.getElementById('notifTitle').innerText = title;
        document.getElementById('notifMsg').innerText = msg;
        
        if(type === 'success') iconEl.innerHTML = '<i class="fa-solid fa-circle-check text-green-500 text-5xl"></i>';
        else if(type === 'error') iconEl.innerHTML = '<i class="fa-solid fa-circle-xmark text-red-500 text-5xl"></i>';
        else iconEl.innerHTML = '<i class="fa-solid fa-circle-info text-[#2B71AD] text-5xl"></i>';
        
        modal.classList.remove('hidden');
        setTimeout(() => { content.classList.remove('scale-95'); content.classList.add('scale-100'); }, 10);
    }

    function closeNotif() {
        const modal = document.getElementById('notifModal');
        const content = document.getElementById('notifContent');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 200);
    }
</script>
