<?php
// ============================================================
// index.php — Nexora Landing Page (Dynamic + MySQL)
// ============================================================

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// ── Handle AJAX / POST requests ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');

    $action = $_POST['action'] ?? '';

    if ($action === 'subscribe') {
        $email = $_POST['email'] ?? '';
        echo json_encode(handle_subscribe($pdo, $email));
        exit;
    }

    if ($action === 'contact') {
        $name    = $_POST['name']    ?? '';
        $email   = $_POST['email']   ?? '';
        $message = $_POST['message'] ?? '';
        echo json_encode(handle_contact($pdo, $name, $email, $message));
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenal.']);
    exit;
}

// ── Ambil semua data dari DB ─────────────────────────────────
$features     = get_features($pdo);
$steps        = get_steps($pdo);
$testimonials = get_testimonials($pdo);
$pricing      = get_pricing($pdo);
$logos        = get_logos($pdo);

// Settings
$hero_badge        = get_setting($pdo, 'hero_badge');
$hero_title        = get_setting($pdo, 'hero_title');
$hero_desc         = get_setting($pdo, 'hero_desc');
$stat_teams        = get_setting($pdo, 'stat_teams');
$stat_satisfaction = get_setting($pdo, 'stat_satisfaction');
$stat_productivity = get_setting($pdo, 'stat_productivity');
$cta_title         = get_setting($pdo, 'cta_title');
$cta_desc          = get_setting($pdo, 'cta_desc');
$footer_tagline    = get_setting($pdo, 'footer_tagline');
$footer_copyright  = get_setting($pdo, 'footer_copyright');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Nexora — Produktivitas Tanpa Batas</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --ink: #0f0f0f;
      --ink-2: #444;
      --ink-3: #888;
      --paper: #fafaf8;
      --paper-2: #f2f1ee;
      --paper-3: #e8e6e1;
      --accent: #2d6a4f;
      --accent-light: #d8f3dc;
      --accent-mid: #52b788;
      --white: #ffffff;
      --radius: 4px;
      --radius-lg: 12px;
      --serif: 'DM Serif Display', Georgia, serif;
      --sans: 'DM Sans', system-ui, sans-serif;
      --mono: 'JetBrains Mono', monospace;
    }

    html { scroll-behavior: smooth; }

    body {
      font-family: var(--sans);
      background: var(--paper);
      color: var(--ink);
      line-height: 1.6;
      overflow-x: hidden;
    }

    /* ── NAV ── */
    nav {
      position: fixed; top: 0; left: 0; right: 0; z-index: 100;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 5vw;
      height: 64px;
      background: rgba(250,250,248,0.85);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--paper-3);
    }
    .nav-logo {
      font-family: var(--serif);
      font-size: 1.35rem;
      letter-spacing: -0.02em;
      color: var(--ink);
      text-decoration: none;
    }
    .nav-logo span { color: var(--accent); }
    .nav-links { display: flex; gap: 2rem; list-style: none; }
    .nav-links a {
      font-size: 0.875rem; font-weight: 400;
      color: var(--ink-2); text-decoration: none;
      transition: color 0.2s;
    }
    .nav-links a:hover { color: var(--ink); }
    .nav-cta {
      font-size: 0.875rem; font-weight: 500;
      color: var(--white); background: var(--ink);
      border: none; border-radius: var(--radius);
      padding: 0.5rem 1.25rem; cursor: pointer;
      text-decoration: none; transition: background 0.2s, transform 0.15s;
    }
    .nav-cta:hover { background: var(--accent); transform: translateY(-1px); }

    /* ── HERO ── */
    .hero {
      min-height: 100vh;
      display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      text-align: center;
      padding: 10rem 5vw 6rem;
      position: relative; overflow: hidden;
    }
    .hero::before {
      content: '';
      position: absolute; inset: 0;
      background: radial-gradient(ellipse 60% 50% at 50% 0%, rgba(82,183,136,0.08) 0%, transparent 70%);
      pointer-events: none;
    }
    .hero-badge {
      display: inline-flex; align-items: center; gap: 0.5rem;
      font-family: var(--mono); font-size: 0.75rem;
      color: var(--accent); background: var(--accent-light);
      border: 1px solid rgba(45,106,79,0.2);
      border-radius: 100px; padding: 0.35rem 1rem;
      margin-bottom: 2rem;
      animation: fadeUp 0.6s ease both;
    }
    .hero-badge::before { content: '●'; font-size: 0.6rem; }
    .hero h1 {
      font-family: var(--serif);
      font-size: clamp(2.8rem, 7vw, 5.5rem);
      line-height: 1.05; letter-spacing: -0.03em;
      color: var(--ink); max-width: 14ch;
      margin: 0 auto 1.5rem;
      animation: fadeUp 0.6s 0.1s ease both;
    }
    .hero h1 em { font-style: italic; color: var(--accent); }
    .hero > p {
      font-size: clamp(1rem, 2vw, 1.2rem);
      color: var(--ink-2); max-width: 52ch;
      margin: 0 auto 2.5rem; font-weight: 300; line-height: 1.7;
      animation: fadeUp 0.6s 0.2s ease both;
    }
    .hero-actions {
      display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;
      animation: fadeUp 0.6s 0.3s ease both;
    }
    .btn-primary {
      font-size: 0.95rem; font-weight: 500;
      background: var(--ink); color: var(--white);
      border: 1.5px solid var(--ink); border-radius: var(--radius);
      padding: 0.75rem 2rem; cursor: pointer; text-decoration: none;
      transition: all 0.2s;
    }
    .btn-primary:hover { background: var(--accent); border-color: var(--accent); transform: translateY(-2px); }
    .btn-ghost {
      font-size: 0.95rem; font-weight: 400;
      background: transparent; color: var(--ink-2);
      border: 1.5px solid var(--paper-3); border-radius: var(--radius);
      padding: 0.75rem 2rem; cursor: pointer; text-decoration: none;
      transition: all 0.2s;
    }
    .btn-ghost:hover { border-color: var(--ink-3); color: var(--ink); }
    .hero-stats {
      display: flex; gap: 3rem; justify-content: center;
      margin-top: 5rem; padding-top: 3rem;
      border-top: 1px solid var(--paper-3);
      animation: fadeUp 0.6s 0.4s ease both;
    }
    .stat { text-align: center; }
    .stat-num {
      font-family: var(--serif); font-size: 2rem;
      color: var(--ink); letter-spacing: -0.03em;
    }
    .stat-label {
      font-size: 0.8rem; color: var(--ink-3);
      font-family: var(--mono); margin-top: 0.2rem;
    }

    /* ── LOGO STRIP ── */
    .logos {
      background: var(--paper-2); padding: 2.5rem 5vw;
      text-align: center;
      border-top: 1px solid var(--paper-3);
      border-bottom: 1px solid var(--paper-3);
    }
    .logos p {
      font-size: 0.75rem; font-family: var(--mono);
      color: var(--ink-3); letter-spacing: 0.1em;
      text-transform: uppercase; margin-bottom: 1.5rem;
    }
    .logo-row {
      display: flex; gap: 3rem; justify-content: center;
      flex-wrap: wrap; align-items: center;
    }
    .logo-item {
      font-family: var(--serif); font-size: 1.1rem;
      color: var(--paper-3); filter: grayscale(1);
      opacity: 0.4; letter-spacing: -0.02em;
    }

    /* ── FEATURES ── */
    .features { padding: 8rem 5vw; max-width: 1200px; margin: 0 auto; }
    .section-label {
      font-family: var(--mono); font-size: 0.75rem;
      color: var(--accent); letter-spacing: 0.1em;
      text-transform: uppercase; margin-bottom: 1rem;
    }
    .section-title {
      font-family: var(--serif);
      font-size: clamp(2rem, 4vw, 3rem);
      letter-spacing: -0.03em; line-height: 1.1;
      color: var(--ink); max-width: 20ch; margin-bottom: 1rem;
    }
    .section-title em { font-style: italic; color: var(--accent); }
    .section-sub {
      font-size: 1rem; color: var(--ink-2);
      max-width: 50ch; font-weight: 300; margin-bottom: 4rem;
    }
    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
    }
    .feature-card {
      background: var(--white); border: 1px solid var(--paper-3);
      border-radius: var(--radius-lg); padding: 2rem;
      transition: border-color 0.2s, transform 0.2s;
    }
    .feature-card:hover { border-color: var(--accent-mid); transform: translateY(-3px); }
    .feature-icon {
      width: 44px; height: 44px; border-radius: 10px;
      background: var(--accent-light);
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 1.25rem; font-size: 1.25rem;
    }
    .feature-card h3 {
      font-family: var(--serif); font-size: 1.15rem;
      letter-spacing: -0.02em; color: var(--ink); margin-bottom: 0.5rem;
    }
    .feature-card p {
      font-size: 0.9rem; color: var(--ink-2);
      font-weight: 300; line-height: 1.65;
    }

    /* ── HOW IT WORKS ── */
    .how { padding: 8rem 5vw; background: var(--ink); color: var(--white); }
    .how-inner { max-width: 1200px; margin: 0 auto; }
    .how .section-label { color: var(--accent-mid); }
    .how .section-title { color: var(--white); }
    .how .section-sub { color: rgba(255,255,255,0.5); }
    .steps {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 2rem; margin-top: 3rem;
    }
    .step {
      padding: 2rem; border: 1px solid rgba(255,255,255,0.08);
      border-radius: var(--radius-lg);
      background: rgba(255,255,255,0.03);
    }
    .step-num {
      font-family: var(--mono); font-size: 0.75rem;
      color: var(--accent-mid); letter-spacing: 0.1em; margin-bottom: 1rem;
    }
    .step h3 {
      font-family: var(--serif); font-size: 1.15rem;
      color: var(--white); letter-spacing: -0.02em; margin-bottom: 0.5rem;
    }
    .step p {
      font-size: 0.875rem; color: rgba(255,255,255,0.45);
      font-weight: 300; line-height: 1.65;
    }

    /* ── TESTIMONIALS ── */
    .testimonials { padding: 8rem 5vw; max-width: 1200px; margin: 0 auto; }
    .testi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem; margin-top: 3rem;
    }
    .testi-card {
      background: var(--white); border: 1px solid var(--paper-3);
      border-radius: var(--radius-lg); padding: 2rem;
    }
    .testi-quote {
      font-family: var(--serif); font-size: 1.05rem;
      color: var(--ink); line-height: 1.6; letter-spacing: -0.01em;
      margin-bottom: 1.5rem;
    }
    .testi-quote::before { content: '\201C'; color: var(--accent); }
    .testi-quote::after  { content: '\201D'; color: var(--accent); }
    .testi-author { display: flex; align-items: center; gap: 0.75rem; }
    .testi-avatar {
      width: 36px; height: 36px; border-radius: 50%;
      background: var(--accent-light);
      display: flex; align-items: center; justify-content: center;
      font-size: 0.8rem; font-weight: 500; color: var(--accent);
      flex-shrink: 0;
    }
    .testi-name { font-size: 0.875rem; font-weight: 500; color: var(--ink); }
    .testi-role { font-size: 0.75rem; color: var(--ink-3); font-family: var(--mono); }

    /* ── PRICING ── */
    .pricing {
      padding: 8rem 5vw; background: var(--paper-2);
      border-top: 1px solid var(--paper-3);
      border-bottom: 1px solid var(--paper-3);
    }
    .pricing-inner { max-width: 1100px; margin: 0 auto; }
    .pricing-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem; margin-top: 3rem; align-items: start;
    }
    .price-card {
      background: var(--white); border: 1px solid var(--paper-3);
      border-radius: var(--radius-lg); padding: 2rem;
    }
    .price-card.featured {
      border-color: var(--accent); border-width: 1.5px; position: relative;
    }
    .featured-badge {
      position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
      font-family: var(--mono); font-size: 0.7rem;
      background: var(--accent); color: var(--white);
      padding: 0.25rem 0.9rem; border-radius: 100px;
      white-space: nowrap; letter-spacing: 0.05em;
    }
    .price-tier {
      font-family: var(--mono); font-size: 0.75rem;
      color: var(--ink-3); letter-spacing: 0.1em;
      text-transform: uppercase; margin-bottom: 0.75rem;
    }
    .price-amount {
      font-family: var(--serif); font-size: 2.5rem;
      color: var(--ink); letter-spacing: -0.04em; margin-bottom: 0.25rem;
    }
    .price-amount span {
      font-family: var(--sans); font-size: 1rem;
      font-weight: 300; color: var(--ink-3);
    }
    .price-desc {
      font-size: 0.85rem; color: var(--ink-2);
      font-weight: 300; margin-bottom: 1.75rem;
    }
    .price-features { list-style: none; margin-bottom: 2rem; }
    .price-features li {
      font-size: 0.875rem; color: var(--ink-2);
      padding: 0.45rem 0; border-bottom: 1px solid var(--paper-2);
      display: flex; align-items: center; gap: 0.6rem;
    }
    .price-features li::before {
      content: '✓'; color: var(--accent);
      font-weight: 500; font-size: 0.8rem; flex-shrink: 0;
    }
    .btn-outline {
      width: 100%; font-size: 0.9rem; font-weight: 500;
      background: transparent; color: var(--ink);
      border: 1.5px solid var(--paper-3); border-radius: var(--radius);
      padding: 0.7rem 1.5rem; cursor: pointer; transition: all 0.2s;
    }
    .btn-outline:hover { border-color: var(--ink); }
    .btn-accent {
      width: 100%; font-size: 0.9rem; font-weight: 500;
      background: var(--accent); color: var(--white);
      border: 1.5px solid var(--accent); border-radius: var(--radius);
      padding: 0.7rem 1.5rem; cursor: pointer; transition: all 0.2s;
    }
    .btn-accent:hover { background: var(--ink); border-color: var(--ink); }

    /* ── CTA BAND ── */
    .cta-band {
      padding: 8rem 5vw; text-align: center;
      max-width: 700px; margin: 0 auto;
    }
    .cta-band h2 {
      font-family: var(--serif);
      font-size: clamp(2.2rem, 5vw, 3.5rem);
      letter-spacing: -0.03em; line-height: 1.1;
      color: var(--ink); margin-bottom: 1.25rem;
    }
    .cta-band h2 em { font-style: italic; color: var(--accent); }
    .cta-band > p {
      font-size: 1rem; color: var(--ink-2);
      font-weight: 300; margin-bottom: 2.5rem;
    }

    /* ── CONTACT FORM ── */
    .contact-section {
      padding: 6rem 5vw;
      background: var(--paper-2);
      border-top: 1px solid var(--paper-3);
    }
    .contact-inner { max-width: 560px; margin: 0 auto; }
    .form-group { margin-bottom: 1.25rem; }
    .form-group label {
      display: block; font-size: 0.8rem;
      font-family: var(--mono); color: var(--ink-3);
      letter-spacing: 0.05em; text-transform: uppercase;
      margin-bottom: 0.45rem;
    }
    .form-group input,
    .form-group textarea {
      width: 100%; padding: 0.75rem 1rem;
      font-family: var(--sans); font-size: 0.9rem;
      color: var(--ink); background: var(--white);
      border: 1px solid var(--paper-3); border-radius: var(--radius);
      transition: border-color 0.2s; outline: none;
    }
    .form-group input:focus,
    .form-group textarea:focus { border-color: var(--accent-mid); }
    .form-group textarea { resize: vertical; min-height: 120px; }
    .form-submit {
      width: 100%; font-size: 0.95rem; font-weight: 500;
      background: var(--ink); color: var(--white);
      border: none; border-radius: var(--radius);
      padding: 0.85rem 2rem; cursor: pointer; transition: all 0.2s;
    }
    .form-submit:hover { background: var(--accent); }
    .form-submit:disabled { opacity: 0.6; cursor: not-allowed; }
    .form-msg {
      margin-top: 1rem; font-size: 0.875rem;
      padding: 0.75rem 1rem; border-radius: var(--radius);
      display: none;
    }
    .form-msg.success { background: var(--accent-light); color: var(--accent); display: block; }
    .form-msg.error   { background: #fde8e8; color: #c0392b; display: block; }

    /* ── NEWSLETTER ── */
    .newsletter {
      padding: 4rem 5vw; background: var(--ink); text-align: center;
    }
    .newsletter h3 {
      font-family: var(--serif); font-size: 1.5rem;
      color: var(--white); letter-spacing: -0.02em; margin-bottom: 0.75rem;
    }
    .newsletter p {
      font-size: 0.9rem; color: rgba(255,255,255,0.4);
      font-weight: 300; margin-bottom: 1.5rem;
    }
    .newsletter-form {
      display: flex; gap: 0.75rem; justify-content: center;
      flex-wrap: wrap; max-width: 420px; margin: 0 auto;
    }
    .newsletter-form input {
      flex: 1; min-width: 200px;
      padding: 0.7rem 1rem; font-family: var(--sans);
      font-size: 0.9rem; color: var(--ink); background: var(--white);
      border: none; border-radius: var(--radius); outline: none;
    }
    .newsletter-form button {
      font-size: 0.875rem; font-weight: 500;
      background: var(--accent); color: var(--white);
      border: none; border-radius: var(--radius);
      padding: 0.7rem 1.5rem; cursor: pointer; transition: background 0.2s;
      white-space: nowrap;
    }
    .newsletter-form button:hover { background: var(--accent-mid); }
    .newsletter-msg {
      margin-top: 0.75rem; font-size: 0.8rem;
      color: var(--accent-light); display: none;
    }
    .newsletter-msg.visible { display: block; }

    /* ── FOOTER ── */
    footer {
      background: var(--ink); color: rgba(255,255,255,0.4);
      padding: 4rem 5vw 2.5rem;
    }
    .footer-inner {
      max-width: 1200px; margin: 0 auto;
      display: grid;
      grid-template-columns: 2fr repeat(3, 1fr);
      gap: 3rem; padding-bottom: 3rem;
      border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    .footer-brand .nav-logo { color: rgba(255,255,255,0.9); }
    .footer-tagline {
      font-size: 0.85rem; color: rgba(255,255,255,0.3);
      font-weight: 300; margin-top: 0.75rem;
      max-width: 26ch; line-height: 1.6;
    }
    .footer-col h4 {
      font-size: 0.75rem; font-family: var(--mono);
      letter-spacing: 0.1em; text-transform: uppercase;
      color: rgba(255,255,255,0.5); margin-bottom: 1.2rem;
    }
    .footer-col ul { list-style: none; }
    .footer-col li { margin-bottom: 0.6rem; }
    .footer-col a {
      font-size: 0.875rem; color: rgba(255,255,255,0.3);
      text-decoration: none; transition: color 0.2s;
    }
    .footer-col a:hover { color: rgba(255,255,255,0.8); }
    .footer-bottom {
      max-width: 1200px; margin: 2rem auto 0;
      display: flex; justify-content: space-between; align-items: center;
      font-size: 0.8rem; flex-wrap: wrap; gap: 1rem;
    }

    /* ── ANIMATIONS ── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .reveal {
      opacity: 0; transform: translateY(20px);
      transition: opacity 0.6s ease, transform 0.6s ease;
    }
    .reveal.visible { opacity: 1; transform: none; }

    /* ── RESPONSIVE ── */
    @media (max-width: 768px) {
      .nav-links { display: none; }
      .hero-stats { gap: 1.5rem; flex-wrap: wrap; }
      .footer-inner { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 480px) {
      .footer-inner { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<!-- NAV -->
<nav>
  <a href="#" class="nav-logo">Nexora<span>.</span></a>
  <ul class="nav-links">
    <li><a href="#features">Fitur</a></li>
    <li><a href="#cara-kerja">Cara Kerja</a></li>
    <li><a href="#harga">Harga</a></li>
    <li><a href="#kontak">Kontak</a></li>
  </ul>
  <a href="#harga" class="nav-cta">Mulai Gratis</a>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-badge"><?= e($hero_badge) ?></div>
  <h1><?= $hero_title /* sudah mengandung HTML <em> dari DB */ ?></h1>
  <p><?= e($hero_desc) ?></p>
  <div class="hero-actions">
    <a href="#harga" class="btn-primary">Coba Gratis 14 Hari</a>
    <a href="#cara-kerja" class="btn-ghost">Lihat Demo →</a>
  </div>
  <div class="hero-stats">
    <div class="stat">
      <div class="stat-num"><?= e($stat_teams) ?></div>
      <div class="stat-label">Tim Aktif</div>
    </div>
    <div class="stat">
      <div class="stat-num"><?= e($stat_satisfaction) ?></div>
      <div class="stat-label">Kepuasan</div>
    </div>
    <div class="stat">
      <div class="stat-num"><?= e($stat_productivity) ?></div>
      <div class="stat-label">Lebih Produktif</div>
    </div>
  </div>
</section>

<!-- LOGO STRIP -->
<?php if (!empty($logos)): ?>
<div class="logos">
  <p>Dipercaya oleh perusahaan terkemuka</p>
  <div class="logo-row">
    <?php foreach ($logos as $logo): ?>
      <div class="logo-item"><?= e($logo) ?></div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- FEATURES -->
<?php if (!empty($features)): ?>
<section class="features" id="features">
  <div class="reveal">
    <p class="section-label">// Fitur Unggulan</p>
    <h2 class="section-title">Semua yang Anda butuhkan, <em>tanpa kerumitan</em></h2>
    <p class="section-sub">Didesain untuk tim modern yang menghargai waktu dan menginginkan hasil yang terukur.</p>
  </div>
  <div class="features-grid reveal">
    <?php foreach ($features as $f): ?>
    <div class="feature-card">
      <div class="feature-icon"><?= e($f['icon']) ?></div>
      <h3><?= e($f['title']) ?></h3>
      <p><?= e($f['description']) ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- HOW IT WORKS -->
<?php if (!empty($steps)): ?>
<section class="how" id="cara-kerja">
  <div class="how-inner">
    <div class="reveal">
      <p class="section-label">// Cara Kerja</p>
      <h2 class="section-title" style="color:#fff">Mulai dalam <em style="color:var(--accent-mid)">tiga langkah</em></h2>
      <p class="section-sub">Tidak ada setup yang rumit. Dalam 10 menit, tim Anda sudah bisa berjalan.</p>
    </div>
    <div class="steps reveal">
      <?php foreach ($steps as $s): ?>
      <div class="step">
        <div class="step-num"><?= e($s['step_num']) ?></div>
        <h3><?= e($s['title']) ?></h3>
        <p><?= e($s['description']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- TESTIMONIALS -->
<?php if (!empty($testimonials)): ?>
<section class="testimonials">
  <div class="reveal">
    <p class="section-label">// Ulasan Pengguna</p>
    <h2 class="section-title">Tim yang <em>berkembang</em> bersama kami</h2>
  </div>
  <div class="testi-grid reveal">
    <?php foreach ($testimonials as $t): ?>
    <div class="testi-card">
      <p class="testi-quote"><?= e($t['quote']) ?></p>
      <div class="testi-author">
        <div class="testi-avatar"><?= e($t['avatar']) ?></div>
        <div>
          <div class="testi-name"><?= e($t['name']) ?></div>
          <div class="testi-role"><?= e($t['role']) ?></div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- PRICING -->
<?php if (!empty($pricing)): ?>
<section class="pricing" id="harga">
  <div class="pricing-inner">
    <div class="reveal">
      <p class="section-label">// Harga</p>
      <h2 class="section-title">Transparan, <em>tanpa biaya tersembunyi</em></h2>
      <p class="section-sub">Pilih paket yang sesuai dengan skala tim Anda. Semua paket termasuk uji coba 14 hari gratis.</p>
    </div>
    <div class="pricing-grid reveal">
      <?php foreach ($pricing as $plan): ?>
      <div class="price-card<?= $plan['is_featured'] ? ' featured' : '' ?>">
        <?php if ($plan['is_featured']): ?>
          <div class="featured-badge">Paling Populer</div>
        <?php endif; ?>
        <div class="price-tier"><?= e($plan['tier_name']) ?></div>
        <div class="price-amount">
          <?= e($plan['price_label']) ?><span> <?= e($plan['price_suffix']) ?></span>
        </div>
        <div class="price-desc"><?= e($plan['description']) ?></div>
        <?php if (!empty($plan['features'])): ?>
        <ul class="price-features">
          <?php foreach ($plan['features'] as $feat): ?>
            <li><?= e($feat) ?></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <button class="btn-<?= e($plan['btn_style']) ?>"><?= e($plan['btn_label']) ?></button>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CTA BAND -->
<section class="cta-band">
  <div class="reveal">
    <h2><?= $cta_title ?></h2>
    <p><?= e($cta_desc) ?></p>
    <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
      <a href="#harga" class="btn-primary">Mulai Gratis Sekarang</a>
      <a href="#kontak" class="btn-ghost">Jadwalkan Demo</a>
    </div>
  </div>
</section>

<!-- CONTACT FORM -->
<section class="contact-section" id="kontak">
  <div class="contact-inner">
    <div class="reveal">
      <p class="section-label">// Hubungi Kami</p>
      <h2 class="section-title">Ada pertanyaan? <em>Kami siap membantu</em></h2>
      <p class="section-sub" style="margin-bottom:2rem">Kirim pesan dan tim kami akan menghubungi Anda dalam 1×24 jam.</p>
    </div>
    <div id="contactMsg" class="form-msg"></div>
    <div class="reveal">
      <div class="form-group">
        <label for="c_name">Nama Lengkap</label>
        <input type="text" id="c_name" placeholder="Budi Santoso" maxlength="150" />
      </div>
      <div class="form-group">
        <label for="c_email">Alamat Email</label>
        <input type="email" id="c_email" placeholder="budi@perusahaan.co.id" maxlength="255" />
      </div>
      <div class="form-group">
        <label for="c_msg">Pesan</label>
        <textarea id="c_msg" placeholder="Ceritakan kebutuhan tim Anda..."></textarea>
      </div>
      <button class="form-submit" id="contactBtn" onclick="submitContact()">Kirim Pesan</button>
    </div>
  </div>
</section>

<!-- NEWSLETTER -->
<div class="newsletter">
  <h3>Dapatkan update terbaru</h3>
  <p>Tips produktivitas, fitur baru, dan promo eksklusif langsung ke inbox Anda.</p>
  <div class="newsletter-form">
    <input type="email" id="nl_email" placeholder="email@anda.com" maxlength="255" />
    <button onclick="submitNewsletter()">Langganan</button>
  </div>
  <p class="newsletter-msg" id="nlMsg"></p>
</div>

<!-- FOOTER -->
<footer>
  <div class="footer-inner">
    <div class="footer-brand">
      <a href="#" class="nav-logo">Nexora<span>.</span></a>
      <p class="footer-tagline"><?= e($footer_tagline) ?></p>
    </div>
    <div class="footer-col">
      <h4>Produk</h4>
      <ul>
        <li><a href="#features">Fitur</a></li>
        <li><a href="#harga">Harga</a></li>
        <li><a href="#">Integrasi</a></li>
        <li><a href="#">Changelog</a></li>
        <li><a href="#">Roadmap</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Perusahaan</h4>
      <ul>
        <li><a href="#">Tentang Kami</a></li>
        <li><a href="#">Blog</a></li>
        <li><a href="#">Karir</a></li>
        <li><a href="#">Press Kit</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Dukungan</h4>
      <ul>
        <li><a href="#">Dokumentasi</a></li>
        <li><a href="#">Pusat Bantuan</a></li>
        <li><a href="#">Status Sistem</a></li>
        <li><a href="mailto:hello@nexora.id">hello@nexora.id</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <span><?= e($footer_copyright) ?></span>
    <span style="display:flex; gap:1.5rem;">
      <a href="#" style="color:rgba(255,255,255,0.3); text-decoration:none; font-size:0.8rem;">Privasi</a>
      <a href="#" style="color:rgba(255,255,255,0.3); text-decoration:none; font-size:0.8rem;">Syarat</a>
      <a href="#" style="color:rgba(255,255,255,0.3); text-decoration:none; font-size:0.8rem;">Cookie</a>
    </span>
  </div>
</footer>

<script>
// ── Scroll reveal ──────────────────────────────────────────
const reveals = document.querySelectorAll('.reveal');
const io = new IntersectionObserver((entries) => {
  entries.forEach((e, i) => {
    if (e.isIntersecting) {
      setTimeout(() => e.target.classList.add('visible'), i * 80);
      io.unobserve(e.target);
    }
  });
}, { threshold: 0.12 });
reveals.forEach(el => io.observe(el));

// ── Fetch helper ───────────────────────────────────────────
async function postData(payload) {
  const res = await fetch(window.location.href, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(payload)
  });
  if (!res.ok) throw new Error('Network error');
  return res.json();
}

// ── Contact form ───────────────────────────────────────────
async function submitContact() {
  const btn  = document.getElementById('contactBtn');
  const msg  = document.getElementById('contactMsg');
  const name  = document.getElementById('c_name').value.trim();
  const email = document.getElementById('c_email').value.trim();
  const text  = document.getElementById('c_msg').value.trim();

  msg.className = 'form-msg';
  msg.style.display = 'none';

  if (!name || !email || !text) {
    msg.textContent = 'Semua field wajib diisi.';
    msg.className = 'form-msg error';
    return;
  }

  btn.disabled = true;
  btn.textContent = 'Mengirim…';

  try {
    const data = await postData({ action: 'contact', name, email, message: text });
    msg.textContent = data.message;
    msg.className = 'form-msg ' + (data.success ? 'success' : 'error');
    if (data.success) {
      document.getElementById('c_name').value  = '';
      document.getElementById('c_email').value = '';
      document.getElementById('c_msg').value   = '';
    }
  } catch {
    msg.textContent = 'Terjadi kesalahan jaringan. Coba lagi.';
    msg.className = 'form-msg error';
  } finally {
    btn.disabled = false;
    btn.textContent = 'Kirim Pesan';
  }
}

// ── Newsletter ─────────────────────────────────────────────
async function submitNewsletter() {
  const email = document.getElementById('nl_email').value.trim();
  const msgEl = document.getElementById('nlMsg');

  msgEl.className = 'newsletter-msg';

  if (!email) {
    msgEl.textContent = 'Masukkan email Anda terlebih dahulu.';
    msgEl.classList.add('visible');
    return;
  }

  try {
    const data = await postData({ action: 'subscribe', email });
    msgEl.textContent = data.message;
    msgEl.classList.add('visible');
    if (data.success) document.getElementById('nl_email').value = '';
  } catch {
    msgEl.textContent = 'Terjadi kesalahan. Coba lagi.';
    msgEl.classList.add('visible');
  }
}
</script>
</body>
</html>
