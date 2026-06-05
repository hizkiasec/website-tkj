<?php
// ============================================================
// includes/functions.php — Helper Functions
// ============================================================

/**
 * Ambil satu setting dari tabel site_settings
 */
function get_setting(PDO $pdo, string $key, string $default = ''): string {
    static $cache = [];
    if (!isset($cache[$key])) {
        $stmt = $pdo->prepare('SELECT setting_val FROM site_settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        $cache[$key] = $row ? $row['setting_val'] : $default;
    }
    return $cache[$key];
}

/**
 * Escape HTML — shortcut aman
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Ambil semua fitur aktif
 */
function get_features(PDO $pdo): array {
    $stmt = $pdo->query('SELECT * FROM features WHERE is_active = 1 ORDER BY sort_order ASC');
    return $stmt->fetchAll();
}

/**
 * Ambil semua steps aktif
 */
function get_steps(PDO $pdo): array {
    $stmt = $pdo->query('SELECT * FROM steps WHERE is_active = 1 ORDER BY sort_order ASC');
    return $stmt->fetchAll();
}

/**
 * Ambil semua testimonial aktif
 */
function get_testimonials(PDO $pdo): array {
    $stmt = $pdo->query('SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order ASC');
    return $stmt->fetchAll();
}

/**
 * Ambil semua pricing plans aktif beserta features-nya
 */
function get_pricing(PDO $pdo): array {
    $plans = $pdo->query('SELECT * FROM pricing_plans WHERE is_active = 1 ORDER BY sort_order ASC')->fetchAll();
    foreach ($plans as &$plan) {
        $stmt = $pdo->prepare('SELECT feature FROM pricing_features WHERE plan_id = ? ORDER BY sort_order ASC');
        $stmt->execute([$plan['id']]);
        $plan['features'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    return $plans;
}

/**
 * Ambil logo strip aktif
 */
function get_logos(PDO $pdo): array {
    $stmt = $pdo->query('SELECT name FROM logo_strip WHERE is_active = 1 ORDER BY sort_order ASC');
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Handle subscribe newsletter
 * @return array ['success' => bool, 'message' => string]
 */
function handle_subscribe(PDO $pdo, string $email): array {
    $email = trim($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Email tidak valid.'];
    }
    try {
        $stmt = $pdo->prepare('INSERT INTO newsletter_subscribers (email) VALUES (?)');
        $stmt->execute([$email]);
        return ['success' => true, 'message' => 'Terima kasih! Anda telah berlangganan.'];
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') { // Duplicate entry
            return ['success' => false, 'message' => 'Email sudah terdaftar sebelumnya.'];
        }
        return ['success' => false, 'message' => 'Terjadi kesalahan. Coba lagi nanti.'];
    }
}

/**
 * Handle kontak / pesan masuk
 * @return array ['success' => bool, 'message' => string]
 */
function handle_contact(PDO $pdo, string $name, string $email, string $message): array {
    $name    = trim($name);
    $email   = trim($email);
    $message = trim($message);

    if ($name === '' || $message === '') {
        return ['success' => false, 'message' => 'Nama dan pesan wajib diisi.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Email tidak valid.'];
    }

    $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)');
    $stmt->execute([$name, $email, $message]);
    return ['success' => true, 'message' => 'Pesan Anda terkirim! Kami akan menghubungi Anda segera.'];
}
