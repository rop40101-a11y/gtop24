<div class="md:hidden fixed bottom-0 left-0 w-full bg-slate-900 text-gray-400 border-t border-gray-800 flex justify-around py-3 z-50">
    <a href="index.php" class="flex flex-col items-center gap-1 <?php echo basename($_SERVER['PHP_SELF'])=='index.php'?'text-blue-500':''; ?>">
        <i class="fa-solid fa-gauge text-xl"></i>
    </a>
    <a href="order.php" class="flex flex-col items-center gap-1 <?php echo basename($_SERVER['PHP_SELF'])=='order.php'?'text-blue-500':''; ?>">
        <i class="fa-solid fa-list-check text-xl"></i>
    </a>
    <div class="relative -top-6">
        <a href="game.php" class="bg-blue-600 text-white w-12 h-12 rounded-full flex items-center justify-center shadow-lg shadow-blue-500/50">
            <i class="fa-solid fa-plus text-xl"></i>
        </a>
    </div>
    <a href="user.php" class="flex flex-col items-center gap-1 <?php echo basename($_SERVER['PHP_SELF'])=='user.php'?'text-blue-500':''; ?>">
        <i class="fa-solid fa-users text-xl"></i>
    </a>
    <a href="setting.php" class="flex flex-col items-center gap-1 <?php echo basename($_SERVER['PHP_SELF'])=='setting.php'?'text-blue-500':''; ?>">
        <i class="fa-solid fa-gear text-xl"></i>
    </a>
</div>
