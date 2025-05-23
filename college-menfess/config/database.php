<?php
// Database configuration
define('DB_HOST', 'localhost'); // Database host
define('DB_USER', 'jellyfish'); // Your MySQL username
define('DB_PASS', 'jellycev'); // Your MySQL password
define('DB_NAME', 'jellyfish_menfess'); // Database name

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db(DB_NAME);

// Create tables
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        batch_year ENUM('2022', '2023', '2024') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        profile_picture VARCHAR(255)
    )",
    
    "CREATE TABLE IF NOT EXISTS songs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        artist VARCHAR(255) NOT NULL,
        spotify_url VARCHAR(255) NOT NULL,
        album_art_url VARCHAR(255) NOT NULL,
        preview_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT,
        receiver_name VARCHAR(100) NOT NULL,
        message_content TEXT NOT NULL,
        song_id INT,
        batch_visibility ENUM('2022', '2023', '2024') NOT NULL,
        is_anonymous BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE SET NULL
    )",
    
    "CREATE TABLE IF NOT EXISTS likes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message_id INT NOT NULL,
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_like (message_id, user_id)
    )",
    "CREATE TABLE IF NOT EXISTS dislikes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_dislike (message_id, user_id)
    )",
    "CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (message_id, user_id)
    )"
];

foreach ($tables as $sql) {
    if ($conn->query($sql) === FALSE) {
        die("Error creating table: " . $conn->error);
    }
}

// Return connection
return $conn;
?>