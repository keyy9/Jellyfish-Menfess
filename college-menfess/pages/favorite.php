<?php
require_once __DIR__ . '/../config/config.php';

// Ensure session is active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
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

    // Check if already favorited
    $stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND message_id = ?");
    $stmt->bind_param("ii", $user_id, $message_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Already favorited -> Remove from favorites
        $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND message_id = ?");
        $stmt->bind_param("ii", $user_id, $message_id);
        $stmt->execute();
        $action = 'unfavorited';
    } else {
        // Not favorited -> Add to favorites
        $stmt = $conn->prepare("INSERT INTO favorites (user_id, message_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $message_id);
        $stmt->execute();
        $action = 'favorited';
    }

    echo json_encode(['action' => $action]);
    exit;
}
?>