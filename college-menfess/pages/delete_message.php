<?php
require_once __DIR__ . '/../config/config.php';
session_start();

// Cek login dan metode request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $message_id = intval($_POST['message_id']);

    // Buat koneksi
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database connection failed."]);
        exit;
    }

    // Hapus likes terlebih dahulu
    $stmt = $conn->prepare("DELETE FROM likes WHERE message_id = ?");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();

    // Hapus pesan jika milik user
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ? AND sender_id = ?");
    $stmt->bind_param("ii", $message_id, $_SESSION['user_id']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["status" => "success"]);
    } else {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Not authorized or message not found."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
