<?php 
include 'common/header.php'; 

// Helper Function to convert ANY YouTube link to Embed link
function getYoutubeEmbedUrl($url) {
    $shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_-]+)\??/i';
    $longUrlRegex = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))([a-zA-Z0-9_-]+)/i';

    if (preg_match($longUrlRegex, $url, $matches)) {
        $youtube_id = $matches[count($matches) - 1];
    }
    if (preg_match($shortUrlRegex, $url, $matches)) {
        $youtube_id = $matches[1];
    }
    return isset($youtube_id) ? 'https://www.youtube.com/embed/' . $youtube_id : $url;
}

$videoLink = getSetting($conn, 'add_money_video');
$embedLink = getYoutubeEmbedUrl($videoLink);
?>

<link href="https://fonts.googleapis.com/css2?family=Bree+Serif&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* BACKGROUND SCROLL SETUP */
    body {
        background-image: url('res/backgrounds/bg.png');
        background-repeat: repeat;
        background-size: 100% auto; 
        background-attachment: scroll;
        background-position: top center;
        font-family: 'Inter', sans-serif;
        -webkit-user-select: none; user-select: none;
    }

    /* MAIN CARD WRAPPER */
    .main-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 6px; 
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        overflow: hidden;
        padding-bottom: 20px;
    }

    /* HEADER */
    .card-header {
        padding: 15px 20px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .page-title {
        font-family: 'Bree Serif', serif;
        font-size: 19px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* FORM ELEMENTS */
    .label-text {
        font-size: 13px;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 8px;
        display: block;
        text-transform: uppercase;
    }

    .input-flat {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 12px;
        text-align: center;
        font-weight: 700;
        font-size: 1.2rem;
        outline: none;
        transition: border-color 0.2s;
        color: #334155;
        font-family: 'Inter', sans-serif;
    }

    .input-flat:focus {
        border-color: #2B71AD;
        box-shadow: 0 0 0 2px rgba(43, 113, 173, 0.1);
    }

    .btn-flat-main {
        background-color: #2B71AD;
        color: white;
        padding: 12px;
        font-weight: 700;
        text-transform: uppercase;
        border-radius: 4px;
        width: 100%;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
        margin-top: 15px;
    }
    .btn-flat-main:hover { background-color: #1e5685; }

    /* VIDEO SECTION */
    .video-title {
        font-size: 14px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        border-top: 1px dashed #e2e8f0;
        padding-top: 20px;
        margin-top: 20px;
    }
</style>

<div class="container mx-auto px-4 py-6 mb-20 max-w-lg">
    
    <div class="main-card">
        
        <div class="card-header">
            <h2 class="page-title">Add Money</h2>
        </div>
        
        <div class="p-5">
            <form action="instantpay.php" method="POST"> 
                <input type="hidden" name="game_id" value="0">
                <input type="hidden" name="product_id" value="0">
                <input type="hidden" name="game_name" value="Wallet Deposit">
                <input type="hidden" name="game_type" value="deposit">
                <input type="hidden" name="player_id" value="Wallet Balance">

                <div class="mb-2 text-center">
                    <label class="label-text">Enter Amount to Deposit</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-gray-400 font-bold text-lg">৳</span>
                        <input type="number" class="input-flat" placeholder="100" name="total_amount" required min="10">
                    </div>
                </div>
                
                <button type="submit" class="btn-flat-main">
                    Proceed to Payment
                </button>
            </form>

            <div class="video-title">
                <i class="fa-solid fa-circle-play text-red-500"></i> Tutorial: How to Add Money
            </div>
            
            <?php if($videoLink): ?>
            <div class="aspect-video bg-black rounded-md overflow-hidden shadow-sm border border-gray-100 relative">
                <iframe class="w-full h-full" src="<?php echo $embedLink; ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
            <?php else: ?>
                <div class="bg-gray-50 p-6 text-center rounded-md border border-gray-100 text-gray-400 text-xs italic">
                    <i class="fa-brands fa-youtube text-2xl mb-2 opacity-50"></i><br>
                    Video tutorial coming soon.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
include 'common/footer.php'; 
include 'common/bottom.php'; 
?>
