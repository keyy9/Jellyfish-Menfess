<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Site configuration
define('SITE_NAME', 'Jellyfish Menfess');
define('SITE_URL', 'http://localhost:8000');
define('BASE_URL', '/college-menfess');


// Database connection
require_once __DIR__ . '/database.php';

// Helper functions
function redirect($path) {
    header("Location: " . SITE_URL . $path);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) return null;

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        return $result->fetch_assoc();
    }
    return null;
}

// Security functions
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function getSpotifyThumbnail($conn, $songId, $spotifyUrl) {
    $oembedUrl = 'https://open.spotify.com/oembed?url=' . urlencode($spotifyUrl);

    // Gunakan cURL
    $ch = curl_init($oembedUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0'); // Tambahkan User-Agent
    $json = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($json, true);
    $thumbnail = $data['thumbnail_url'] ?? 'https://via.placeholder.com/150';

    // Update ke DB jika berhasil
    if ($thumbnail !== 'https://via.placeholder.com/150') {
        $stmt = $conn->prepare("UPDATE songs SET album_art_url = ? WHERE id = ?");
        $stmt->bind_param("si", $thumbnail, $songId);
        $stmt->execute();
    }

    return $thumbnail;
}


// Set CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generateToken();
}
?>
