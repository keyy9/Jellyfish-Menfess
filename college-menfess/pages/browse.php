<?php
require_once __DIR__ . '/../config/config.php';

$batch_year = isset($_GET['batch']) ? htmlspecialchars(strip_tags($_GET['batch'])) : null;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;

try {
    // Prepare base query
    $where = $batch_year ? "WHERE batch_visibility = ?" : "";
    $sql = "SELECT m.*, s.title, s.artist, s.spotify_url, u.username,
    (SELECT COUNT(*) FROM likes WHERE message_id = m.id) as likes_count,
    (SELECT COUNT(*) FROM dislikes WHERE message_id = m.id) as dislikes_count," . 
    (isset($_SESSION['user_id']) ? 
        "(SELECT COUNT(*) FROM likes WHERE message_id = m.id AND user_id = {$_SESSION['user_id']}) AS already_liked,
        (SELECT COUNT(*) FROM dislikes WHERE message_id = m.id AND user_id = {$_SESSION['user_id']}) AS already_disliked,
        (SELECT COUNT(*) FROM favorites WHERE message_id = m.id AND user_id = {$_SESSION['user_id']}) AS is_favorite"
        : "0 AS already_liked, 0 AS already_disliked, 0 AS is_favorite") . "
    FROM messages m
    LEFT JOIN songs s ON m.song_id = s.id
    LEFT JOIN users u ON m.sender_id = u.id
    $where
    ORDER BY m.created_at DESC
    LIMIT ? OFFSET ?";


    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM messages " . $where;
    $stmt = $conn->prepare($count_sql);
    if ($batch_year) {
        $stmt->bind_param("s", $batch_year);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $total = $result->fetch_assoc()['total'];
    $total_pages = ceil($total / $per_page);

    // Get messages
    $offset = ($page - 1) * $per_page;
    $stmt = $conn->prepare($sql);
    if ($batch_year) {
        $stmt->bind_param("sii", $batch_year, $per_page, $offset);
    } else {
        $stmt->bind_param("ii", $per_page, $offset);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        // Ambil thumbnail (cache jika belum ada)
        if (empty($row['album_art_url']) && !empty($row['spotify_url']) && !empty($row['song_id'])) {
            $row['album_art_url'] = getSpotifyThumbnail($conn, $row['song_id'], $row['spotify_url']);
        }
        $messages[] = $row;
    }    
    }
catch (Exception $e) {
    $error = "Failed to load messages. Please try again.";
}


// Check for success message
$success = isset($_GET['status']) && $_GET['status'] === 'success';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Messages - <?php echo SITE_NAME; ?></title>
    
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
                <img src="/college-menfess/assets/images/jellyfish.png" alt="Logo" class="w-12 h-12">
                    <span class="text-2xl font-bold text-pink-dark font-poppins"><?php echo SITE_NAME; ?></span>
                </a>
                <div class="space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <a href="submit.php" class="bg-pink hover:bg-pink-dark text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-plus mr-2"></i>New Message
                        </a>
                        <a href="profile.php" class="text-gray-600 hover:text-pink">
                            <i class="fas fa-user mr-2"></i>Profile
                        </a>
                        <a href="logout.php" class="text-gray-600 hover:text-pink">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-600 hover:text-pink">Login</a>
                        <a href="register.php" class="bg-pink hover:bg-pink-dark text-white px-4 py-2 rounded-lg transition">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 py-8">
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">Your message has been posted.</span>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Batch Filter -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 font-poppins">Browse Messages</h1>
            <div class="flex space-x-2">
                <a href="browse.php" class="px-4 py-2 rounded-lg <?php echo !$batch_year ? 'bg-pink text-white' : 'bg-white text-gray-600 hover:bg-pink-light'; ?>">
                    All
                </a>
                <a href="browse.php?batch=2024" class="px-4 py-2 rounded-lg <?php echo $batch_year === '2024' ? 'bg-pink text-white' : 'bg-white text-gray-600 hover:bg-pink-light'; ?>">
                    2024
                </a>
                <a href="browse.php?batch=2023" class="px-4 py-2 rounded-lg <?php echo $batch_year === '2023' ? 'bg-pink text-white' : 'bg-white text-gray-600 hover:bg-pink-light'; ?>">
                    2023
                </a>
                <a href="browse.php?batch=2022" class="px-4 py-2 rounded-lg <?php echo $batch_year === '2022' ? 'bg-pink text-white' : 'bg-white text-gray-600 hover:bg-pink-light'; ?>">
                    2022
                </a>
            </div>
        </div>

        <!-- Messages Grid -->
        <div class="grid grid-cols-1 gap-6">
    <?php if (empty($messages)): ?>
        <div class="text-center py-12">
            <p class="text-gray-600 text-lg">No messages found.</p>
            <a href="submit.php" class="inline-block mt-4 text-pink hover:text-pink-dark">
                Be the first to share a message!
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($messages as $message): ?>
            <div class="message-card" style="margin: 10px; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background-color: white; display: inline-block; vertical-align: top;"> <!-- New class here -->
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <div class="text-sm text-gray-500">To: <?php echo htmlspecialchars($message['receiver_name']); ?></div>
                        <div class="text-sm text-gray-500">
                            <?php echo date('F j, Y', strtotime($message['created_at'])); ?>
                        </div>
                    </div>
                    <div class="text-sm">
                        <span class="bg-pink-light text-pink-dark px-3 py-1 rounded-full">
                            Batch <?php echo htmlspecialchars($message['batch_visibility']); ?>
                        </span>
                    </div>
                </div>
                
                <p class="text-gray-800 mb-4"><?php echo nl2br(htmlspecialchars($message['message_content'])); ?></p>
                
                <!-- Song Info -->
                <div class="border-t pt-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <img src="<?php echo !empty($message['album_art_url']) ? htmlspecialchars($message['album_art_url']) : 'https://via.placeholder.com/150'; ?>" alt="Album Art" class="w-16 h-16 rounded-md object-cover" />    
                            <div>
                                <h4 class="font-semibold"><?php echo htmlspecialchars($message['title']); ?></h4>
                                <p class="text-gray-600"><?php echo htmlspecialchars($message['artist']); ?></p>
                            </div>
                        </div>
                        <?php if (!empty($message['spotify_url'])): ?>
                            <a href="<?php echo htmlspecialchars($message['spotify_url']); ?>" 
                               target="_blank" class="text-green-500 hover:text-green-600">
                                <i class="fab fa-spotify text-2xl"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Message Footer -->
                <div class="mt-4 flex justify-between items-center text-sm text-gray-500">
                    <div>
                        <?php if (!$message['is_anonymous']): ?>
                            From: <?php echo htmlspecialchars($message['username']); ?>
                        <?php else: ?>
                            From: Anonymous
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Like Button -->
                        <div class="flex items-center space-x-1">
                            <button id="like-btn-<?php echo $message['id']; ?>" 
                                    onclick="likeMessage(<?php echo $message['id']; ?>)" 
                                    class="<?php echo $message['already_liked'] ? 'text-pink' : 'text-gray-400'; ?> hover:text-pink-dark focus:outline-none transition transform">
                                <i class="fas fa-heart"></i>
                            </button>
                            <span id="likes-count-<?php echo $message['id']; ?>">
                                <?php echo $message['likes_count']; ?>
                            </span>
                        </div>
                        
                        <!-- Dislike Button -->
                        <div class="flex items-center space-x-1">
                            <button id="dislike-btn-<?php echo $message['id']; ?>" 
                                    onclick="dislikeMessage(<?php echo $message['id']; ?>)" 
                                    class="<?php echo $message['already_disliked'] ? 'text-blue-500' : 'text-gray-400'; ?> hover:text-blue-600 focus:outline-none transition transform">
                                <i class="fas fa-thumbs-down"></i>
                            </button>
                            <span id="dislikes-count-<?php echo $message['id']; ?>">
                                <?php echo $message['dislikes_count']; ?>
                            </span>
                        </div>
                        
                        <!-- Favorite Button -->
                        <button id="favorite-btn-<?php echo $message['id']; ?>" 
                                onclick="favoriteMessage(<?php echo $message['id']; ?>)" 
                                class="<?php echo $message['is_favorite'] ? 'text-yellow-500' : 'text-gray-400'; ?> hover:text-yellow-400 focus:outline-none transition transform">
                            <i class="fas fa-star"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="mt-8 flex justify-center space-x-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $batch_year ? '&batch=' . urlencode($batch_year) : ''; ?>" 
                       class="px-4 py-2 bg-white rounded-lg text-pink hover:bg-pink-light">
                        Previous
                    </a>
                <?php endif; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $batch_year ? '&batch=' . urlencode($batch_year) : ''; ?>" 
                       class="px-4 py-2 bg-white rounded-lg text-pink hover:bg-pink-light">
                        Next
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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
    
<script>
function likeMessage(messageId) {
    const button = document.getElementById('like-btn-' + messageId);
    const countSpan = document.getElementById('likes-count-' + messageId);
    const dislikeButton = document.getElementById('dislike-btn-' + messageId);

    fetch('/college-menfess/pages/like.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'message_id=' + encodeURIComponent(messageId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.action === 'liked') {
            button.classList.remove('text-gray-400');
            button.classList.add('text-pink');
            button.classList.add('scale-125');
            setTimeout(() => {
                button.classList.remove('scale-125');
            }, 200);
            countSpan.innerText = parseInt(countSpan.innerText) + 1;
            
            // Remove dislike if exists
            if (dislikeButton.classList.contains('text-blue-500')) {
                dislikeButton.classList.remove('text-blue-500');
                dislikeButton.classList.add('text-gray-400');
                const dislikeCount = document.getElementById('dislikes-count-' + messageId);
                dislikeCount.innerText = parseInt(dislikeCount.innerText) - 1;
            }
        } else if (data.action === 'unliked') {
            button.classList.remove('text-pink');
            button.classList.add('text-gray-400');
            countSpan.innerText = parseInt(countSpan.innerText) - 1;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function dislikeMessage(messageId) {
    const button = document.getElementById('dislike-btn-' + messageId);
    const countSpan = document.getElementById('dislikes-count-' + messageId);
    const likeButton = document.getElementById('like-btn-' + messageId);

    fetch('/college-menfess/pages/dislike.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'message_id=' + encodeURIComponent(messageId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.action === 'disliked') {
            button.classList.remove('text-gray-400');
            button.classList.add('text-blue-500');
            button.classList.add('scale-125');
            setTimeout(() => {
                button.classList.remove('scale-125');
            }, 200);
            countSpan.innerText = parseInt(countSpan.innerText) + 1;
            
            // Remove like if exists
            if (likeButton.classList.contains('text-pink')) {
                likeButton.classList.remove('text-pink');
                likeButton.classList.add('text-gray-400');
                const likeCount = document.getElementById('likes-count-' + messageId);
                likeCount.innerText = parseInt(likeCount.innerText) - 1;
            }
        } else if (data.action === 'undisliked') {
            button.classList.remove('text-blue-500');
            button.classList.add('text-gray-400');
            countSpan.innerText = parseInt(countSpan.innerText) - 1;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function favoriteMessage(messageId) {
    const button = document.getElementById('favorite-btn-' + messageId);

    fetch('/college-menfess/pages/favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'message_id=' + encodeURIComponent(messageId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.action === 'favorited') {
            button.classList.remove('text-gray-400');
            button.classList.add('text-yellow-500');
            button.classList.add('scale-125');
            setTimeout(() => {
                button.classList.remove('scale-125');
            }, 200);
        } else if (data.action === 'unfavorited') {
            button.classList.remove('text-yellow-500');
            button.classList.add('text-gray-400');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
</body>
</html>