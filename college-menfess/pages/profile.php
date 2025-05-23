
<?php
require_once __DIR__ . '/../config/config.php';

session_start();
$errors = [];
$success = false;

// Buat koneksi ke MySQL
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Redirect ke login jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Ambil data user untuk ditampilkan
$stmt = $conn->prepare("SELECT id, username, email, batch_year FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Proses update password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $errors[] = "All password fields are required.";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "New password and confirmation do not match.";
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_data = $result->fetch_assoc();

            if ($user_data && password_verify($current_password, $user_data['password'])) {
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $new_password_hash, $_SESSION['user_id']);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $success = true;
                } else {
                    $errors[] = "Password update failed.";
                }
            } else {
                $errors[] = "Current password is incorrect.";
            }
        }
    }
}

// Fetch user's messages
// Fetch user's messages
$messages = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("
        SELECT m.*, s.title, s.artist, s.spotify_url, s.album_art_url, u.username,
        (SELECT COUNT(*) FROM likes WHERE message_id = m.id) as likes_count,
        (SELECT COUNT(*) FROM dislikes WHERE message_id = m.id) as dislikes_count,
        (SELECT COUNT(*) FROM likes WHERE message_id = m.id AND user_id = ?) AS already_liked,
        (SELECT COUNT(*) FROM dislikes WHERE message_id = m.id AND user_id = ?) AS already_disliked,
        (SELECT COUNT(*) FROM favorites WHERE message_id = m.id AND user_id = ?) AS is_favorite
        FROM messages m
        LEFT JOIN songs s ON m.song_id = s.id
        LEFT JOIN users u ON m.sender_id = u.id
        WHERE m.sender_id = ?
        ORDER BY m.created_at DESC
    ");
    $stmt->bind_param("iiii", $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // Get album art if missing
        if (empty($row['album_art_url']) && !empty($row['spotify_url']) && !empty($row['song_id'])) {
            $row['album_art_url'] = getSpotifyThumbnail($conn, $row['song_id'], $row['spotify_url']);
        }
        $messages[] = $row;
    }
}

// Fetch user's favorite messages
$favorites = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("
        SELECT m.*, s.title, s.artist, s.spotify_url, s.album_art_url, u.username,
        (SELECT COUNT(*) FROM likes WHERE message_id = m.id) as likes_count,
        (SELECT COUNT(*) FROM dislikes WHERE message_id = m.id) as dislikes_count
        FROM favorites f
        JOIN messages m ON f.message_id = m.id
        LEFT JOIN songs s ON m.song_id = s.id
        LEFT JOIN users u ON m.sender_id = u.id
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // Get album art if missing
        if (empty($row['album_art_url']) && !empty($row['spotify_url']) && !empty($row['song_id'])) {
            $row['album_art_url'] = getSpotifyThumbnail($conn, $row['song_id'], $row['spotify_url']);
        }
        $favorites[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Nunito:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="flex flex-col min-h-screen bg-pink-50">
<header class="bg-white shadow-md">
    <nav class="container mx-auto px-6 py-4">
        <div class="flex justify-between items-center">
            <a href="/college-menfess/index.php" class="flex items-center space-x-2">
            <img src="/college-menfess/assets/images/jellyfish.png" alt="Logo" class="w-12 h-12">
                <span class="text-2xl font-bold text-pink-dark font-poppins"><?php echo SITE_NAME; ?></span>
            </a>
            <div class="space-x-4">
                <a href="submit.php" class="bg-pink hover:bg-pink-dark text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i>New Message
                </a>
                <a href="browse.php" class="text-gray-600 hover:text-pink">
                    <i class="fas fa-list mr-2"></i>Browse
                </a>
                <a href="logout.php" class="text-gray-600 hover:text-pink">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>
</header>
<main class="flex-grow container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="md:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-6">Profile Settings</h2>

                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        Password updated successfully!
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label for="username" class="block text-gray-700 mb-2">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly
                               class="w-full px-4 py-2 border rounded-lg bg-gray-100">
                    </div>

                    <div>
                        <label for="email" class="block text-gray-700 mb-2">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly
                               class="w-full px-4 py-2 border rounded-lg bg-gray-100">
                    </div>

                    <div>
                        <label for="batch_year" class="block text-gray-700 mb-2">Batch Year</label>
                        <input type="text" id="batch_year" value="<?php echo htmlspecialchars($user['batch_year']); ?>" disabled
                               class="w-full px-4 py-2 border rounded-lg bg-gray-100">
                    </div>

                    <div class="border-t pt-4 mt-4">
                        <h3 class="text-lg font-semibold mb-4">Change Password</h3>

                        <div>
                            <label for="current_password" class="block text-gray-700 mb-2">Current Password</label>
                            <input type="password" id="current_password" name="current_password"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink focus:border-pink">
                        </div>

                        <div>
                            <label for="new_password" class="block text-gray-700 mb-2">New Password</label>
                            <input type="password" id="new_password" name="new_password"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink focus:border-pink">
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-gray-700 mb-2">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink focus:border-pink">
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full bg-pink hover:bg-pink-dark text-white font-bold py-3 px-4 rounded-lg transition">
                        Update Profile
                    </button>
                </form>
            </div>
        </div>

        <div class="md:col-span-2">
    <!-- Tab Navigation -->
    <div class="mb-6 border-b">
        <ul class="flex flex-wrap -mb-px">
            <li class="mr-2">
                <a href="#messages" class="inline-block p-4 border-b-2 border-pink-500 rounded-t-lg active" id="messages-tab">
                    Your Messages
                </a>
            </li>
            <li class="mr-2">
                <a href="#favorites" class="inline-block p-4 border-b-2 border-pink-500 rounded-t-lg hover:text-pink-600 hover:border-pink-300" id="favorites-tab">
                    Favorites
                </a>
            </li>
        </ul>
    </div>

    <!-- Messages Tab Content -->
    <!-- Messages Tab Content -->
<div id="messages-content" class="tab-content">
    <?php if (empty($messages)): ?>
        <div class="text-center py-12">
            <p class="text-gray-600 text-lg">No messages found.</p>
            <a href="submit.php" class="inline-block mt-4 text-pink hover:text-pink-dark">
                Be the first to share a message!
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($messages as $message): ?>
                <div class="bg-white rounded-lg shadow-md p-6 flex flex-col space-y-4 hover:shadow-xl transition-shadow">
                    <div class="flex justify-between mb-4">
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
                    <?php if (!empty($message['song_id'])): ?>
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
                    <?php endif; ?>
                    
                    <!-- Message Footer -->
                    <div class="flex justify-between items-center text-sm text-gray-500">
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
                                        class="<?php echo !empty($message['already_liked']) ? 'text-pink' : 'text-gray-400'; ?> hover:text-pink-dark focus:outline-none transition transform">
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
                                        class="<?php echo !empty($message['already_disliked']) ? 'text-blue-500' : 'text-gray-400'; ?> hover:text-blue-600 focus:outline-none transition transform">
                                    <i class="fas fa-thumbs-down"></i>
                                </button>
                                <span id="dislikes-count-<?php echo $message['id']; ?>">
                                    <?php echo $message['dislikes_count']; ?>
                                </span>
                            </div>
                            
                            <!-- Favorite Button -->
                            <button id="favorite-btn-<?php echo $message['id']; ?>" 
                                    onclick="favoriteMessage(<?php echo $message['id']; ?>)" 
                                    class="<?php echo !empty($message['is_favorite']) ? 'text-yellow-500' : 'text-gray-400'; ?> hover:text-yellow-400 focus:outline-none transition transform">
                                <i class="fas fa-star"></i>
                            </button>

                            <button class="delete-button text-red-500 hover:text-red-700" data-id="<?php echo $message['id']; ?>">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

    <!-- Favorites Tab Content -->
    <div id="favorites-content" class="tab-content hidden">
        <?php if (empty($favorites)): ?>
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <p class="text-gray-600">You haven't favorited any messages yet.</p>
                <a href="browse.php" class="inline-block mt-4 text-pink hover:text-pink-dark">Browse messages</a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($favorites as $message): ?>
                    <div class="bg-white rounded-lg shadow-md p-6 flex flex-col space-y-4 hover:shadow-xl transition-shadow">
                        <div class="flex justify-between mb-4">
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
                        <?php if (!empty($message['song_id'])): ?>
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
                                       target="_blank"
                                       class="text-green-500 hover:text-green-600">
                                        <i class="fab fa-spotify text-2xl"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Message Footer -->
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <div>
                                <?php if (!$message['is_anonymous']): ?>
                                    From: <?php echo htmlspecialchars($message['username']); ?>
                                <?php else: ?>
                                    From: Anonymous
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-heart text-pink"></i>
                                    <span><?php echo $message['likes_count']; ?></span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-thumbs-down text-blue-500"></i>
                                    <span><?php echo $message['dislikes_count']; ?></span>
                                </div>
                                <button class="unfavorite-button text-yellow-500 hover:text-yellow-400" data-id="<?php echo $message['id']; ?>">
                                    <i class="fas fa-star"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
</main>
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
document.querySelectorAll('.delete-button').forEach(button => {
    button.addEventListener('click', function () {
        const messageId = this.getAttribute('data-id');

        if (confirm('Are you sure you want to delete this message?')) {
            fetch('delete_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ message_id: messageId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    this.closest('.bg-white').remove();
                } else {
                    alert(data.message || 'Failed to delete message.');
                }
            })
            .catch(error => {
                alert('Error: ' + error);
            });
        }
    });
});

document.getElementById('messages-tab').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('messages-tab').classList.add('border-pink-500');
    document.getElementById('favorites-tab').classList.remove('border-pink-500');
    document.getElementById('messages-content').classList.remove('hidden');
    document.getElementById('favorites-content').classList.add('hidden');
});

document.getElementById('favorites-tab').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('favorites-tab').classList.add('border-pink-500');
    document.getElementById('messages-tab').classList.remove('border-pink-500');
    document.getElementById('favorites-content').classList.remove('hidden');
    document.getElementById('messages-content').classList.add('hidden');
});

// Delete message functionality
document.querySelectorAll('.delete-button').forEach(button => {
    button.addEventListener('click', function () {
        const messageId = this.getAttribute('data-id');

        if (confirm('Are you sure you want to delete this message?')) {
            fetch('delete_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ message_id: messageId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    this.closest('.bg-white').remove();
                } else {
                    alert(data.message || 'Failed to delete message.');
                }
            })
            .catch(error => {
                alert('Error: ' + error);
            });
        }
    });
});

// Unfavorite functionality
document.querySelectorAll('.unfavorite-button').forEach(button => {
    button.addEventListener('click', function () {
        const messageId = this.getAttribute('data-id');
        
        fetch('/college-menfess/pages/favorite.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'message_id=' + encodeURIComponent(messageId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.action === 'unfavorited') {
                this.closest('.bg-white').remove();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});

document.querySelectorAll('.unfavorite-button').forEach(button => {
    button.addEventListener('click', function (e) {
        e.preventDefault(); // mencegah default behavior
        const favId = this.getAttribute('data-id');

        // Langsung kirim tanpa confirm
        fetch('unfavorite_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `fav_id=${encodeURIComponent(favId)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Berhasil dihapus dari favorit!');
                location.reload(); // reload halaman
            } else {
                alert('Gagal menghapus favorit.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});

</script>
</body>
</html>
