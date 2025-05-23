<?php
require_once __DIR__ . '/../config/config.php';

// Pastikan session sudah jalan
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session data
session_destroy();

// Redirect ke home page
header('Location: ' . BASE_URL . '/');
exit;
?>
