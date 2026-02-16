<?php 
include '../common/config.php'; 

// CHANGED: Redirect to Root Login (../login.php) if not admin
if(!isset($_SESSION['admin_id'])) { 
    header("Location: ../login.php"); 
    exit; 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#f59e0b', // Amber-500 (Yellow Main)
                        secondary: '#d97706', // Amber-600 (Darker Yellow)
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans text-slate-800">
    <div class="flex h-screen overflow-hidden">
        
        <?php include 'sidebar.php'; ?>

        <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
            
            <header class="bg-white shadow-sm p-4 flex justify-between items-center md:hidden z-10 border-b-2 border-yellow-400">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="text-slate-800 text-xl focus:outline-none hover:text-yellow-600 transition-colors">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div class="font-bold text-lg text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-yellow-500"></i>
                        Admin Panel
                    </div>
                </div>
                
                <a href="../index.php" target="_blank" class="text-gray-500 hover:text-yellow-600 transition-colors text-lg">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
            </header>

            <main class="flex-1 overflow-y-auto p-4 md:p-8 pb-24 md:pb-8 bg-gray-50/50">
