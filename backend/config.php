<?php
// config.php - Database connection and configurations for KAWAL

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials from Environment Variables (set via Docker Compose)
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_USER', getenv('DB_USER') ?: 'kawal_user');
define('DB_PASS', getenv('DB_PASSWORD') ?: 'kawal_pass');
define('DB_NAME', getenv('DB_NAME') ?: 'kawal_db');

// Admin Panel Credentials
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'kawal2026'); // Admin password for the demo dashboard

// Gemini API Key
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');

// Establish connection to database
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // If database connection fails during startup, output error nicely
    die("Koneksi database gagal: " . $e->getMessage());
}

/**
 * Helper to sanitise output strings
 */
function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>
