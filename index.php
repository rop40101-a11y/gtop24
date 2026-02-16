<?php 
// Include Configuration
include 'common/config.php'; 
include 'common/header.php'; 

// ====================================================
// AUTOMATIC DATABASE UPDATE LOGIC (Run Once)
// ====================================================
if(isset($conn)) {
    // 1. Create Categories Table
    $conn->query("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        priority INT DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Add category_id to games table
    $chk_col = $conn->query("SHOW COLUMNS FROM games LIKE 'category_id'");
    if($chk_col->num_rows == 0) {
        $conn->query("ALTER TABLE games ADD COLUMN category_id INT DEFAULT 0");
    }

    // 3. Add Popup Settings
    $settings_to_add = [
        'popup_image' => '',
        'popup_link' => '#',
        'popup_btn_text' => 'See Offer',
        'popup_text' => ''
    ];
    
    foreach($settings_to_add as $key => $default) {
        $chk_set = $conn->query("SELECT id FROM settings WHERE name='$key'");
        if($chk_set->num_rows == 0) {
            $conn->query("INSERT INTO settings (name, value) VALUES ('$key', '$default')");
        }
    }
}

// ====================================================
// FETCH DATA
// ====================================================

// Fetch Notice
$notice_text = ""; 
if(function_exists('getSetting')) {
    $notice_text = getSetting($conn, 'home_notice');
}

// Fetch Popup Data
$popup_img = getSetting($conn, 'popup_image');
$popup_link = getSetting($conn, 'popup_link');
$popup_btn = getSetting($conn, 'popup_btn_text');
$popup_text = getSetting($conn, 'popup_text'); 

?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bree+Serif&family=Lato:wght@400;700;900&family=Noto+Serif+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* GLOBAL FONT: Bree Serif */
    body { font-family: 'Bree Serif', serif; }

    /* BACKGROUND FIX */
    body {
        background-image: url('res/backgrounds/bg.png');
        background-repeat: repeat;
        background-size: 100% auto; 
        background-attachment: scroll; 
        background-position: top center;
    }
    
    .sharp-edge { border-radius: 0px !important; }

    .slider-aspect {
        aspect-ratio: 2 / 1;
        width: 100%;
        overflow: hidden;
        position: relative;
    }

    /* UPDATED NOTICE STYLES */
    .notice-box {
        background-color: #2B71AD; /* App Main Color */
        color: white;
        border-radius: 0px; /* Sharp Corners */
        padding: 10px 15px; 
        position: relative;
        margin-bottom: 10px; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .notice-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
    }
    .notice-title {
        font-size: 18px;
        /* Title Font: Lato */
        font-family: 'Lato', sans-serif;
        font-weight: 700; 
    }
    .notice-close {
        font-size: 18px;
        cursor: pointer;
        opacity: 0.9;
        transition: opacity 0.2s;
    }
    .notice-close:hover { opacity: 1; transform: scale(1.1); }
    
    .notice-content {
        font-size: 13px;
        /* NOTICE FONT: Lato (Eng) & Noto Serif Bengali (Bangla) */
        font-family: 'Lato', 'Noto Serif Bengali', sans-serif;
        line-height: 1.3; 
        opacity: 0.95;
        font-weight: 400;
    }

    /* Divider Styling */
    .divider-container {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 20px 0 10px 0; 
    }
    .divider-line {
        flex: 1;
        height: 2px;
        background: linear-gradient(to var(--dir), #FFD700, #2B71AD);
    }
    .divider-text {
        font-size: 1.4rem;
        padding: 0 1rem;
        color: #0b224f;
        font-weight: 700; 
        text-transform: lowercase;
    }
    .divider-text::first-letter { text-transform: uppercase; }

    /* Slider */
    #slider {
        display: flex;
        transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        cursor: grab;
    }
    #slider:active { cursor: grabbing; }

    .game-card-img {
        width: 100%;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        border: 1px solid #e2e8f0;
    }

    .dot-active { background-color: #000000 !important; }
    .dot-inactive { background-color: #d1d5db !important; }

    /* --- HOME POPUP STYLES --- */
    #homePopupOverlay {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 10005; 
        display: none; 
        align-items: center;
        justify-content: center;
        padding: 15px;
    }
    
    .popup-content {
        background: transparent;
        max-width: 400px;
        width: 100%;
        position: relative;
        text-align: center;
        border-radius: 6px; 
    }
    
    /* Image Box */
    .popup-img-box {
        width: 100%;
        border-top-left-radius: 6px; 
        border-top-right-radius: 6px; 
        overflow: hidden;
        background: #000;
    }
    .popup-img-box img {
        display: block;
        width: 100%;
        height: auto;
    }

    /* Message Text Area */
    .popup-body {
        background: white;
        padding: 20px;
        border-bottom-left-radius: 6px; 
        border-bottom-right-radius: 6px; 
    }
    
    .popup-text-content {
        font-size: 15px;
        color: #1f2937;
        line-height: 1.5;
        margin-bottom: 15px;
        font-weight: 500;
        /* Popup Body also uses Bree Serif (Default) */
    }

    /* Close Button */
    .popup-close-btn {
        position: absolute;
        top: -10px;
        right: -10px;
        background: white;
        color: black;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 1px solid #e5e7eb;
        z-index: 20;
        font-size: 16px;
    }
    .popup-close-btn:hover { background: #f3f4f6; color: red; }

    /* Action Button */
    .popup-action-btn {
        background: #3b82f6; 
        color: white;
        padding: 10px 20px;
        border-radius: 4px;
        font-family: 'Bree Serif', serif;
        display: block;
        width: 100%;
        font-size: 16px;
        text-decoration: none;
        border: none;
    }
    .popup-action-btn:hover { background: #2563eb; }
</style>

<?php if(!empty($popup_img) || !empty($popup_text)): ?>
<div id="homePopupOverlay">
    <div class="popup-content">
        <button class="popup-close-btn" onclick="closeHomePopup()">
            <i class="fa-solid fa-xmark"></i>
        </button>
        
        <?php if(!empty($popup_img)): ?>
        <div class="popup-img-box">
            <img src="<?php echo $popup_img; ?>" alt="Offer">
        </div>
        <?php endif; ?>

        <div class="popup-body">
            <?php if(!empty($popup_text)): ?>
            <div class="popup-text-content">
                <?php echo nl2br(htmlspecialchars($popup_text)); ?>
            </div>
            <?php endif; ?>

            <?php if(!empty($popup_btn)): ?>
            <a href="<?php echo $popup_link; ?>" class="popup-action-btn">
                <?php echo htmlspecialchars($popup_btn); ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('homePopupOverlay').style.display = 'flex';
    });

    function closeHomePopup() {
        document.getElementById('homePopupOverlay').style.display = 'none';
    }
</script>
<?php endif; ?>


<?php if(!empty($notice_text)): ?>
<div class="container mx-auto px-4 mt-4">
    <div class="notice-box">
        <div class="notice-header">
            <span class="notice-title">Notice</span>
            <i class="fa-solid fa-xmark notice-close" onclick="this.closest('.notice-box').style.display='none'"></i>
        </div>
        <div class="notice-content">
            <?php echo nl2br(htmlspecialchars($notice_text)); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container mx-auto px-4">
    <?php 
    $slides_arr = [];
    if(isset($conn)) {
        $slider_query = $conn->query("SELECT * FROM sliders");
        if($slider_query){
            while($row = $slider_query->fetch_assoc()){ $slides_arr[] = $row; }
        }
    }
    $total_slides = count($slides_arr);
    ?>

    <div class="relative w-full sharp-edge shadow-sm bg-gray-100 slider-aspect group">
        <div id="slider" class="h-full w-full">
            <?php if($total_slides > 0): ?>
                <?php foreach($slides_arr as $slide): ?>
                    <a href="<?php echo $slide['link'] ? $slide['link'] : '#'; ?>" class="min-w-full h-full block select-none">
                        <img src="<?php echo $slide['image']; ?>" class="w-full h-full object-fill pointer-events-none" alt="Slide">
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="w-full h-full flex items-center justify-center text-gray-400 font-normal">No Banners Found</div>
            <?php endif; ?>
        </div>
    </div>

    <?php if($total_slides > 1): ?>
    <div class="flex justify-center gap-2 mt-3 mb-6">
        <?php for($i = 0; $i < $total_slides; $i++): ?>
            <button onclick="goToSlide(<?php echo $i; ?>)" 
                    class="slider-dot h-1 w-6 sharp-edge transition-all duration-300 <?php echo ($i === 0) ? 'dot-active' : 'dot-inactive'; ?>" 
                    data-index="<?php echo $i; ?>">
            </button>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<div class="container mx-auto px-4 pb-12">
    <?php 
    if(isset($conn)) {
        $cat_query = $conn->query("SELECT * FROM categories ORDER BY priority ASC, id ASC");
        
        $has_categories = ($cat_query && $cat_query->num_rows > 0);
        
        function renderGameGrid($conn, $cat_id = null) {
            $sql = "SELECT * FROM games";
            if($cat_id !== null) {
                $sql .= " WHERE category_id = $cat_id";
            } else {
                $sql .= " WHERE category_id = 0";
            }
            
            $games = $conn->query($sql);
            
            if($games && $games->num_rows > 0): ?>
                <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 mt-6">
                    <?php while($game = $games->fetch_assoc()): ?>
                        <a href="game_detail.php?id=<?php echo $game['id']; ?>" class="block group">
                            <div class="sharp-edge overflow-hidden mb-2 relative bg-white shadow-sm">
                                <img src="<?php echo $game['image']; ?>" class="game-card-img" alt="<?php echo $game['name']; ?>">
                            </div>
                            
                            <div class="text-center">
                                <h3 class="text-[#0b224f] text-xs md:text-sm font-normal leading-tight uppercase">
                                    <?php echo $game['name']; ?>
                                </h3>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="py-4 text-center text-gray-400 text-sm italic">No games in this section.</div>
            <?php endif; 
        }

        if($has_categories) {
            while($cat = $cat_query->fetch_assoc()) {
                ?>
                <div class="divider-container px-2">
                    <div class="divider-line" style="--dir: left;"></div>
                    <h2 class="divider-text">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </h2>
                    <div class="divider-line" style="--dir: right;"></div>
                </div>

                <?php renderGameGrid($conn, $cat['id']); ?>
                <?php
            }
            
            $uncat_chk = $conn->query("SELECT id FROM games WHERE category_id = 0 LIMIT 1");
            if($uncat_chk && $uncat_chk->num_rows > 0) {
                 ?>
                 <div class="divider-container px-2 mt-8">
                    <div class="divider-line" style="--dir: left;"></div>
                    <h2 class="divider-text">Others</h2>
                    <div class="divider-line" style="--dir: right;"></div>
                </div>
                <?php renderGameGrid($conn, 0); 
            }

        } else {
            ?>
            <div class="divider-container px-2">
                <div class="divider-line" style="--dir: left;"></div>
                <h2 class="divider-text">All Games</h2>
                <div class="divider-line" style="--dir: right;"></div>
            </div>
            <?php renderGameGrid($conn, null); 
        }
    } 
    ?>
</div>

<script>
    let currentIdx = 0;
    const slider = document.getElementById('slider');
    const dots = document.querySelectorAll('.slider-dot');
    const total = <?php echo $total_slides; ?>;
    let autoPlayInterval;

    function updateSlider() {
        if(total <= 0 || !slider) return;
        slider.style.transform = `translateX(-${currentIdx * 100}%)`;
        dots.forEach((dot, index) => {
            dot.classList.toggle('dot-active', index === currentIdx);
            dot.classList.toggle('dot-inactive', index !== currentIdx);
        });
    }

    function startTimer() {
        if(total > 1) {
            autoPlayInterval = setInterval(() => {
                currentIdx = (currentIdx + 1) % total;
                updateSlider();
            }, 5000);
        }
    }

    function resetTimer() {
        clearInterval(autoPlayInterval);
        startTimer();
    }

    function goToSlide(index) {
        currentIdx = index;
        updateSlider();
        resetTimer();
    }

    function nextSlide() {
        currentIdx = (currentIdx + 1) % total;
        updateSlider();
    }

    function prevSlide() {
        currentIdx = (currentIdx - 1 + total) % total;
        updateSlider();
    }

    // Manual Swipe Logic
    let isDragging = false, startPos = 0;

    if(slider) {
        slider.addEventListener('mousedown', dragStart);
        slider.addEventListener('touchstart', dragStart, {passive: true});
        slider.addEventListener('mouseup', dragEnd);
        slider.addEventListener('mouseleave', dragEnd);
        slider.addEventListener('touchend', dragEnd);
        slider.addEventListener('mousemove', dragAction);
        slider.addEventListener('touchmove', dragAction, {passive: true});
    }

    function dragStart(e) {
        isDragging = true;
        startPos = getPositionX(e);
        clearInterval(autoPlayInterval);
    }

    function dragAction(e) {
        if (!isDragging) return;
        const currentPosition = getPositionX(e);
        const diff = currentPosition - startPos;
        if (Math.abs(diff) > 50) {
            if (diff > 0) prevSlide(); else nextSlide();
            isDragging = false; 
            resetTimer(); 
        }
    }

    function dragEnd() {
        if(isDragging) {
            isDragging = false;
            startTimer();
        }
    }

    function getPositionX(e) { return e.type.includes('mouse') ? e.pageX : e.touches[0].clientX; }

    startTimer();
</script>

<?php 
include 'common/footer.php'; 
include 'common/bottom.php'; 
?>
