<?php 
include 'common/header.php'; 
?>

<div class="container mx-auto px-4 py-6 mb-20">
    <div class="flex items-center gap-2 mb-6 border-b pb-2">
        <i class="fa-solid fa-gamepad text-blue-600 text-xl"></i>
        <h2 class="text-xl font-bold text-gray-800">All Games</h2>
    </div>

    <div class="mb-6">
        <input type="text" id="searchGame" onkeyup="filterGames()" placeholder="Search game..." class="w-full border p-3 rounded-xl shadow-sm focus:outline-none focus:border-blue-500">
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="gameGrid">
        <?php 
        $games = $conn->query("SELECT * FROM games ORDER BY name ASC");
        if($games->num_rows > 0):
            while($game = $games->fetch_assoc()): ?>
            <a href="game_detail.php?id=<?php echo $game['id']; ?>" class="game-card bg-white rounded-xl shadow hover:shadow-lg transition transform hover:-translate-y-1 block overflow-hidden group">
                <div class="aspect-square bg-gray-200 overflow-hidden">
                    <img src="<?php echo $game['image']; ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" alt="<?php echo $game['name']; ?>">
                </div>
                <div class="p-3 text-center">
                    <h3 class="game-name font-bold text-sm text-gray-800 truncate"><?php echo $game['name']; ?></h3>
                    <p class="text-xs text-blue-500 mt-1 font-bold">Top Up</p>
                </div>
            </a>
        <?php endwhile; 
        else: ?>
            <p class="col-span-2 text-gray-500 text-center">No games available.</p>
        <?php endif; ?>
    </div>

    <div class="text-center py-4 text-xs text-gray-400 mt-6">
        <p>&copy; 2025 <?php echo getSetting($conn, 'site_name'); ?>.</p>
        <a href="https://t.me/mraiprime" target="_blank" class="text-blue-400 hover:text-blue-600 transition decoration-none">
            Developed by Mr Ai Prime
        </a>
    </div>
</div>

<script>
    function filterGames() {
        let input = document.getElementById('searchGame').value.toLowerCase();
        let cards = document.getElementsByClassName('game-card');
        
        for (let i = 0; i < cards.length; i++) {
            let name = cards[i].getElementsByClassName('game-name')[0].innerText.toLowerCase();
            if (name.includes(input)) {
                cards[i].style.display = "";
            } else {
                cards[i].style.display = "none";
            }
        }
    }
</script>

<?php include 'common/bottom.php'; ?>
