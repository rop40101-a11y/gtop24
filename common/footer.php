<?php
// --- SELF HEALING: FOOTER SETTINGS ---
// Ensure columns exist in settings table
$f_cols = ['facebook', 'instagram', 'youtube', 'telegram_link', 'contact_email', 'whatsapp_number', 'fab_link'];
if(isset($conn)) {
    foreach($f_cols as $col) {
        $chk = $conn->query("SHOW COLUMNS FROM settings LIKE '$col'");
        if($chk->num_rows == 0) {
            $conn->query("ALTER TABLE settings ADD COLUMN $col TEXT DEFAULT NULL");
        }
    }
}

// Fetch Settings (Relies on $conn from config/header)
if (!function_exists('getSetting')) {
    // Fallback if not defined in header
    function getSetting($conn, $key) {
        if(!$conn) return '';
        $q = $conn->query("SELECT value FROM settings WHERE name='$key' LIMIT 1");
        return ($q && $q->num_rows > 0) ? $q->fetch_assoc()['value'] : '';
    }
}

$site_name = getSetting($conn, 'site_name');
$site_logo = getSetting($conn, 'site_logo');

// Links
$fb_link = getSetting($conn, 'facebook');
$ig_link = getSetting($conn, 'instagram');
$yt_link = getSetting($conn, 'youtube');
$email_link = getSetting($conn, 'contact_email');
$tg_link = getSetting($conn, 'telegram_link');
$wa_link = getSetting($conn, 'whatsapp_number'); 
$helpline_num = getSetting($conn, 'fab_link'); 
?>

<link href="https://fonts.googleapis.com/css2?family=Bree+Serif&family=Noto+Serif+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* Footer Container */
    .footer-section {
        background-color: #f3f4f6; /* Solid Gray Background */
        color: #1f2937;
        font-family: 'Noto Serif Bengali', serif; 
        padding-top: 20px;
        /* Padding matches Bottom Nav height to prevent content overlap */
        /* Background extends into this padding, covering the 'transparent gap' */
        padding-bottom: 80px; 
        margin-bottom: 0px !important; 
        border-top: 1px solid #e5e7eb;
        width: 100%;
        position: relative;
        z-index: 1; /* Sits below the fixed bottom nav */
    }

    .footer-logo {
        height: 35px;
        object-fit: contain;
        margin-bottom: 10px;
    }

    .footer-text {
        color: #4b5563;
        font-size: 14px;
        line-height: 1.5;
        margin-bottom: 5px;
        font-weight: 500;
    }

    /* TOP SOCIAL GRID (2x2) */
    .top-social-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 20px;
        margin-top: 15px;
    }

    .social-btn {
        background: white;
        border-radius: 8px;
        padding: 10px 15px;
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        color: #000;
        font-weight: 500;
        font-size: 14px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        border: 1px solid white; 
        transition: all 0.2s;
        font-family: 'Bree Serif', serif;
    }
    .social-btn:active { transform: scale(0.98); background: #f9fafb; }

    .social-icon { 
        font-size: 18px; 
        width: 24px; 
        text-align: center; 
        color: #000 !important; 
    }
    
    /* CONTACT US SECTION */
    .contact-title {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 10px;
        color: #000;
        font-family: 'Bree Serif', serif;
    }

    .contact-card {
        background: white;
        border-radius: 12px;
        padding: 15px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        margin-bottom: 10px; 
        text-decoration: none;
    }
    .contact-card:active { background: #f9fafb; }

    .contact-icon-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        border: 2px solid #000; 
        color: #000;
    }

    .contact-details h4 {
        font-size: 13px;
        font-weight: 500;
        color: #000;
        margin: 0;
        font-family: 'Bree Serif', serif;
    }
    .contact-details p {
        font-size: 12px;
        color: #4b5563;
        margin: 2px 0 0 0;
        font-family: 'Noto Serif Bengali', serif;
        font-weight: 400;
    }

    .bottom-bar {
        text-align: center;
        font-size: 11px;
        color: #9ca3af;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #e5e7eb;
        font-family: 'Bree Serif', serif;
    }
    .dev-link { color: #2563eb; text-decoration: none; font-weight: 500; }
</style>

<footer class="footer-section">
    <div class="container mx-auto px-4">
        
        <div class="mb-4">
            <?php if(!empty($site_logo)): ?>
                <img src="<?php echo $site_logo; ?>" alt="Logo" class="footer-logo">
            <?php else: ?>
                <h2 class="text-2xl font-black text-blue-600 mb-2 uppercase italic" style="font-family: 'Bree Serif', serif;">TOPUP<span class="text-red-500">BD</span></h2>
            <?php endif; ?>

            <p class="footer-text">
                কোন সমস্যায় পড়লে হোয়াটসঅ্যাপ এ যোগাযোগ করবেন। তাহলে দ্রুত সমাধান পেয়ে যাবেন।
            </p>
        </div>

        <div class="top-social-grid">
            <a href="<?php echo !empty($ig_link) ? $ig_link : '#'; ?>" target="_blank" class="social-btn">
                <i class="fa-brands fa-instagram social-icon"></i>
                <span>Instagram</span>
            </a>

            <a href="<?php echo !empty($yt_link) ? $yt_link : '#'; ?>" target="_blank" class="social-btn">
                <i class="fa-brands fa-youtube social-icon"></i>
                <span>YouTube</span>
            </a>

            <a href="<?php echo !empty($fb_link) ? $fb_link : '#'; ?>" target="_blank" class="social-btn">
                <i class="fa-brands fa-facebook-f social-icon"></i>
                <span>Facebook</span>
            </a>

            <a href="mailto:<?php echo !empty($email_link) ? $email_link : '#'; ?>" class="social-btn">
                <i class="fa-regular fa-envelope social-icon"></i>
                <span>Email</span>
            </a>
        </div>

        <h3 class="contact-title">Contact Us</h3>
        
        <a href="<?php echo !empty($helpline_num) ? "https://wa.me/$helpline_num" : '#'; ?>" target="_blank" class="contact-card">
            <div class="contact-icon-circle">
                <i class="fa-brands fa-whatsapp"></i>
            </div>
            <div class="contact-details">
                <h4>Whatsapp HelpLine - <?php echo !empty($helpline_num) ? $helpline_num : '01xxxxxxxxx'; ?></h4>
                <p>সকাল ৯টা থেকে রাত ১২টা</p>
            </div>
        </a>

        <a href="<?php echo !empty($tg_link) ? $tg_link : '#'; ?>" target="_blank" class="contact-card">
            <div class="contact-icon-circle">
                <i class="fa-brands fa-telegram"></i>
            </div>
            <div class="contact-details">
                <h4>Telegram Support</h4>
                <p>টেলিগ্রামে সাপোর্ট</p>
            </div>
        </a>

        <div class="bottom-bar">
            All Rights Reserved | Developed By <a href="https://t.me/DeveloperSketvia01" class="dev-link">Developer Sketvia</a>
        </div>

    </div>
</footer>
