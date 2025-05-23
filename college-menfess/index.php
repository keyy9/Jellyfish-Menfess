<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Share Your Thoughts Through Music</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Nunito:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        pink: {
                            light: '#FFB6C1',
                            DEFAULT: '#FF69B4',
                            dark: '#FF1493',
                        }
                    },
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif'],
                        nunito: ['Nunito', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="flex flex-col min-h-screen bg-pink-50">
    <!-- Header -->
    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <a href="/college-menfess/index.php" class="flex items-center space-x-2">
                    <img src="assets/images/jellyfish.png" alt="Logo" class="w-12 h-12">
                    <span class="text-2xl font-bold text-pink-dark font-poppins"><?php echo SITE_NAME; ?></span>
                </a>
                <div class="space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <a href="pages/submit.php" class="bg-pink hover:bg-pink-dark text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-plus mr-2"></i>New Message
                        </a>
                        <a href="pages/profile.php" class="text-gray-600 hover:text-pink">
                            <i class="fas fa-user mr-2"></i>Profile
                        </a>
                        <a href="pages/logout.php" class="text-gray-600 hover:text-pink">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    <?php else: ?>
                        <a href="pages/login.php" class="text-gray-600 hover:text-pink">Login</a>
                        <a href="pages/register.php" class="bg-pink hover:bg-pink-dark text-white px-4 py-2 rounded-lg transition">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-4xl font-bold text-center mb-8">Welcome to Jellyfish Menfess</h1>
            <p class="text-center text-lg mb-8">Express your feelings through music and connect with your college mates.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition">
                    <h2 class="text-xl font-bold mb-4">Batch 2024</h2>
                    <a href="pages/browse.php?batch=2024" class="text-pink hover:text-pink-dark">Browse Messages →</a>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition">
                    <h2 class="text-xl font-bold mb-4">Batch 2023</h2>
                    <a href="pages/browse.php?batch=2023" class="text-pink hover:text-pink-dark">Browse Messages →</a>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition">
                    <h2 class="text-xl font-bold mb-4">Batch 2022</h2>
                    <a href="pages/browse.php?batch=2022" class="text-pink hover:text-pink-dark">Browse Messages →</a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <p>&copy; 2024 <?php echo SITE_NAME; ?>. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
