<?php
// unfavorite_message.php

// Koneksi ke database
include 'db_connection.php'; // Pastikan file ini ada untuk koneksi $conn

// Ambil data dari request
$favId = intval($_POST['fav_id'] ?? 0);

if ($favId > 0) {
    // Query hapus favorite
    $query = "DELETE FROM favorites WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $favId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Gagal menghapus dari favorit.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'ID favorit tidak valid.']);
}

$conn->close();
?>
