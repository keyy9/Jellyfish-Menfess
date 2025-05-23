<?php
require_once __DIR__ . '/config/config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create dislikes table
$sql = "CREATE TABLE IF NOT EXISTS dislikes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_dislike (message_id, user_id)
)";

if ($conn->query($sql) === FALSE) {
    echo "Error creating dislikes table: " . $conn->error . "<br>";
} else {
    echo "Dislikes table created successfully<br>";
}

// Create favorites table
$sql = "CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (message_id, user_id)
)";

if ($conn->query($sql) === FALSE) {
    echo "Error creating favorites table: " . $conn->error . "<br>";
} else {
    echo "Favorites table created successfully<br>";
}

$conn->close();
echo "Database update completed!";
?>