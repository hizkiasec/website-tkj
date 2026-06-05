<?php
// ============================================================
// includes/db.php — Koneksi Database MySQL
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'nexora');
define('DB_USER', 'nexora_user');        // ganti sesuai user MySQL Anda
define('DB_PASS', 'Password123!');            // ganti sesuai password MySQL Anda
define('DB_CHAR', 'utf8mb4');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHAR,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Tampilkan pesan error yang aman (tanpa credentials)
    http_response_code(500);
    exit('<p style="font-family:sans-serif;color:red;padding:2rem">
        <strong>Database Error:</strong> Gagal terhubung ke database.<br>
        Pastikan MySQL berjalan dan konfigurasi di <code>includes/db.php</code> sudah benar.<br>
        <em>(' . htmlspecialchars($e->getMessage()) . ')</em>
    </p>');
}
