<a href="game_detail.php?id=<?php echo $game['id']; ?>" class="block group game-card shadow-sm hover:shadow-md transition-shadow relative">
    <div class="auto-badge">
        Auto delivery
    </div>
    
    <div class="aspect-square overflow-hidden relative">
        <img src="<?php echo $game['image']; ?>" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" alt="<?php echo $game['name']; ?>">
    </div>
    
    <div class="p-3 text-center bg-white">
        <h3 class="font-bree text-[#0b224f] text-xs md:text-sm font-bold leading-tight uppercase truncate">
            <?php echo $game['name']; ?>
        </h3>
    </div>
</a>
