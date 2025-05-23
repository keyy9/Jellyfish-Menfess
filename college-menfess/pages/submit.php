<?php
require_once __DIR__ . '/../config/config.php';

// Check if the user is logged in
if (!isLoggedIn()) {
    redirect('/login'); // Redirect to login if not logged in
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $message_content = htmlspecialchars(strip_tags($_POST['message_content']));
    $receiver_name = htmlspecialchars(strip_tags($_POST['receiver_name']));
    $song_title = htmlspecialchars(strip_tags($_POST['song_title']));
    $song_artist = htmlspecialchars(strip_tags($_POST['song_artist']));
    $spotify_url = htmlspecialchars(strip_tags($_POST['spotify_url']));
    $batch_visibility = isset($_POST['batch_visibility']) ? htmlspecialchars(strip_tags($_POST['batch_visibility'])) : null;
    $is_anonymous = isset($_POST['is_anonymous']) ? (bool)$_POST['is_anonymous'] : false;

    // Validate input
    if (empty($message_content)) {
        $errors[] = "Message content is required.";
    }
    if (empty($song_title) || empty($song_artist) || empty($spotify_url)) {
        $errors[] = "Complete song details are required.";
    }
    if (empty($batch_visibility) || !in_array($batch_visibility, ['2022', '2023', '2024'])) {
        $errors[] = "Invalid batch visibility.";
    }

            if (empty($errors)) {
                try {
                    // Insert song
                    $stmt = $conn->prepare("INSERT INTO songs (title, artist, spotify_url, album_art_url) VALUES (?, ?, ?, ?)");
                    $placeholder_album_art = 'https://via.placeholder.com/150';
                    $stmt->bind_param("ssss", $song_title, $song_artist, $spotify_url, $placeholder_album_art);
                    $stmt->execute();
                    $song_id = $stmt->insert_id;
                    $stmt->close();

                    // Insert message
                    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_name, message_content, song_id, batch_visibility, is_anonymous) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("issisi", $_SESSION['user_id'], $receiver_name, $message_content, $song_id, $batch_visibility, $is_anonymous);

                    // Execute the statement
                    if ($stmt->execute()) {
                        $success = true;
                // Redirect to the browse page with a success status
                header("Location: browse.php?status=success");

                        exit();
                    } else {
                        $errors[] = "Failed to submit the message. Please try again.";
                    }
                    $stmt->close();
                } catch (Exception $e) {
                    $errors[] = "Error: " . $e->getMessage();
                }
            }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Message - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Nunito:wght@400;600&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css" />
</head>
<body class="flex flex-col min-h-screen bg-pink-50">
    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="/college-menfess/index.php" class="flex items-center space-x-2">
            <img src="/college-menfess/assets/images/jellyfish.png" alt="Logo" class="w-12 h-12">
                <span class="text-2xl font-bold text-pink-dark font-poppins"><?php echo SITE_NAME; ?></span>
            </a>
            <div class="space-x-4">
                <a href="browse.php" class="text-gray-600 hover:text-pink"><i class="fas fa-list mr-2"></i>Browse</a>
                <a href="profile.php" class="text-gray-600 hover:text-pink"><i class="fas fa-user mr-2"></i>Profile</a>
                <a href="logout.php" class="text-gray-600 hover:text-pink"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
            </div>
        </nav>
    </header>
    <main class="flex-grow container mx-auto px-6 py-8 max-w-2xl">
        <h1 class="text-3xl font-bold text-gray-900 mb-8 font-poppins">Share Your Message</h1>
            <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 animate-pulse">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <p>Message submitted successfully!</p>
            </div>
        <?php endif; ?>
        <form method="POST" class="space-y-6">
            <div>
                <label for="receiver_name" class="block text-gray-700 mb-2">To</label>
                <input type="text" id="receiver_name" name="receiver_name" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink focus:border-pink transition duration-300 ease-in-out" />
            </div>
            <div>
                <label for="message_content" class="block text-gray-700 mb-2">Your Message</label>
                <textarea id="message_content" name="message_content" rows="4" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink focus:border-pink transition duration-300 ease-in-out"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="song_title" class="block text-gray-700 mb-2">Song Title</label>
                    <input type="text" id="song_title" name="song_title" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink focus:border-pink transition duration-300 ease-in-out" />
                </div>
                <div>
                    <label for="song_artist" class="block text-gray-700 mb-2">Artist</label>
                    <input type="text" id="song_artist" name="song_artist" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink focus:border-pink transition duration-300 ease-in-out" />
                </div>
            </div>
            <div>
                <label for="spotify_url" class="block text-gray-700 mb-2">Spotify URL</label>
                <input type="url" id="spotify_url" name="spotify_url" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink focus:border-pink transition duration-300 ease-in-out" />
                <p class="text-sm text-gray-600 mt-1">You can search for songs on <a href="https://open.spotify.com/search" target="_blank" rel="noopener noreferrer" class="text-pink hover:underline">Spotify.com</a> and paste the song URL here.</p>
            </div>
            <div>
                <label for="batch_visibility" class="block text-gray-700 mb-2">Show to Batch</label>
                <select id="batch_visibility" name="batch_visibility" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink focus:border-pink transition duration-300 ease-in-out">
                    <option value="">Select batch year</option>
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                    <option value="2022">2022</option>
                </select>
            </div>
            <div class="flex items-center">
                <input type="checkbox" id="is_anonymous" name="is_anonymous" class="h-4 w-4 text-pink focus:ring-pink border-gray-300 rounded" />
                <label for="is_anonymous" class="ml-2 block text-sm text-gray-700">Send anonymously</label>
            </div>
            <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 rounded-lg transition">
                <i class="fas fa-paper-plane mr-2"></i> Send Message
            </button>
        </form>
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
</body>
</html>
