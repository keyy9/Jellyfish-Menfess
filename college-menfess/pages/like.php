<?php
require_once __DIR__ . '/../config/config.php';

// Pastikan session aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

file_put_contents('like_debug.txt', "POST: " . json_encode($_POST) . "\n", FILE_APPEND);
file_put_contents('like_debug.txt', "SESSION: " . json_encode($_SESSION) . "\n", FILE_APPEND);

// Cek user login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message_id = intval($_POST['message_id']);

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['message' => 'Database connection error']);
        exit;
    }

    // Cek apakah sudah like
    $stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND message_id = ?");
    $stmt->bind_param("ii", $user_id, $message_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Sudah like -> Hapus
        $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND message_id = ?");
        $stmt->bind_param("ii", $user_id, $message_id);
        $stmt->execute();
        $action = 'unliked';
    } else {
        // Belum like -> Tambah
        $stmt = $conn->prepare("INSERT INTO likes (user_id, message_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $message_id);
        $stmt->execute();
        $action = 'liked';
    }

    echo json_encode(['action' => $action]);
    exit;
}
?>
