<?php
// ============================================================
// admin/index.php — Panel Admin Nexora (sederhana)
// Lindungi file ini dengan .htaccess atau password di production!
// ============================================================

session_start();

// ── Simple auth ───────────────────────────────────────────
define('ADMIN_USER', 'admin');       // ganti username
define('ADMIN_PASS', 'nexora2025');  // ganti password

if (isset($_POST['login'])) {
    if ($_POST['u'] === ADMIN_USER && $_POST['p'] === ADMIN_PASS) {
        $_SESSION['nexora_admin'] = true;
    } else {
        $login_error = 'Username atau password salah.';
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
if (!isset($_SESSION['nexora_admin'])) {
    // Show login form
    ?><!DOCTYPE html>
    <html lang="id"><head><meta charset="UTF-8"><title>Admin — Login</title>
    <style>
      body{font-family:system-ui,sans-serif;background:#f4f4f4;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
      .box{background:#fff;padding:2.5rem;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.08);width:320px}
      h2{margin:0 0 1.5rem;font-size:1.2rem;color:#0f0f0f}
      input{width:100%;padding:.65rem .9rem;border:1px solid #ddd;border-radius:4px;font-size:.9rem;margin-bottom:.9rem;box-sizing:border-box}
      button{width:100%;padding:.7rem;background:#2d6a4f;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:.9rem}
      .err{color:#c0392b;font-size:.85rem;margin-bottom:.75rem}
    </style></head><body>
    <div class="box">
      <h2>Nexora Admin</h2>
      <?php if (!empty($login_error)): ?><p class="err"><?= htmlspecialchars($login_error) ?></p><?php endif; ?>
      <form method="post">
        <input type="text"     name="u" placeholder="Username" required autofocus>
        <input type="password" name="p" placeholder="Password"  required>
        <button name="login">Masuk</button>
      </form>
    </div>
    </body></html><?php
    exit;
}

// ── Load DB & functions ───────────────────────────────────
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$success_msg = '';
$error_msg   = '';

// ── Handle form saves ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';

    if ($act === 'save_setting') {
        $key = $_POST['key'] ?? '';
        $val = $_POST['val'] ?? '';
        $stmt = $pdo->prepare('UPDATE site_settings SET setting_val = ? WHERE setting_key = ?');
        $stmt->execute([$val, $key]);
        $success_msg = 'Setting berhasil disimpan.';
    }

    if ($act === 'save_feature') {
        $id   = (int)($_POST['id'] ?? 0);
        $icon = $_POST['icon']  ?? '';
        $title= $_POST['title'] ?? '';
        $desc = $_POST['desc']  ?? '';
        $ord  = (int)($_POST['sort_order'] ?? 0);
        if ($id) {
            $pdo->prepare('UPDATE features SET icon=?,title=?,description=?,sort_order=? WHERE id=?')
                ->execute([$icon, $title, $desc, $ord, $id]);
        } else {
            $pdo->prepare('INSERT INTO features (icon,title,description,sort_order) VALUES (?,?,?,?)')
                ->execute([$icon, $title, $desc, $ord]);
        }
        $success_msg = 'Fitur berhasil disimpan.';
    }

    if ($act === 'delete_feature') {
        $pdo->prepare('DELETE FROM features WHERE id=?')->execute([(int)$_POST['id']]);
        $success_msg = 'Fitur dihapus.';
    }

    if ($act === 'save_testimonial') {
        $id     = (int)($_POST['id'] ?? 0);
        $quote  = $_POST['quote']  ?? '';
        $name   = $_POST['name']   ?? '';
        $role   = $_POST['role']   ?? '';
        $avatar = strtoupper(substr($name, 0, 1)) . strtoupper(substr(strrchr($name, ' '), 1, 1));
        $ord    = (int)($_POST['sort_order'] ?? 0);
        if ($id) {
            $pdo->prepare('UPDATE testimonials SET quote=?,name=?,role=?,avatar=?,sort_order=? WHERE id=?')
                ->execute([$quote, $name, $role, $avatar, $ord, $id]);
        } else {
            $pdo->prepare('INSERT INTO testimonials (quote,name,role,avatar,sort_order) VALUES (?,?,?,?,?)')
                ->execute([$quote, $name, $role, $avatar, $ord]);
        }
        $success_msg = 'Testimonial berhasil disimpan.';
    }

    if ($act === 'delete_testimonial') {
        $pdo->prepare('DELETE FROM testimonials WHERE id=?')->execute([(int)$_POST['id']]);
        $success_msg = 'Testimonial dihapus.';
    }

    // Redirect to avoid re-POST on refresh
    $_SESSION['flash'] = $success_msg;
    header('Location: index.php?tab=' . ($_GET['tab'] ?? 'settings'));
    exit;
}

if (isset($_SESSION['flash'])) {
    $success_msg = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// ── Fetch data for display ────────────────────────────────
$settings     = $pdo->query('SELECT setting_key, setting_val FROM site_settings ORDER BY setting_key')->fetchAll();
$features     = get_features($pdo);
$testimonials = get_testimonials($pdo);
$messages     = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 50')->fetchAll();
$subscribers  = $pdo->query('SELECT * FROM newsletter_subscribers ORDER BY created_at DESC LIMIT 50')->fetchAll();
$active_tab   = $_GET['tab'] ?? 'settings';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Nexora — Admin Panel</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:system-ui,sans-serif;background:#f0f0ef;color:#1a1a1a;font-size:14px}
    a{color:#2d6a4f;text-decoration:none}
    .sidebar{position:fixed;top:0;left:0;bottom:0;width:220px;background:#0f0f0f;color:rgba(255,255,255,.7);padding:1.5rem 1rem;display:flex;flex-direction:column;gap:.35rem}
    .sidebar h1{font-size:1.1rem;color:#fff;margin-bottom:1.5rem;letter-spacing:-.02em}
    .sidebar h1 span{color:#52b788}
    .nav-item{display:block;padding:.6rem .9rem;border-radius:6px;color:rgba(255,255,255,.55);font-size:.875rem;transition:background .15s}
    .nav-item:hover,.nav-item.active{background:rgba(255,255,255,.07);color:#fff}
    .logout{margin-top:auto;font-size:.8rem;color:rgba(255,255,255,.3)}
    .main{margin-left:220px;padding:2rem}
    h2{font-size:1.2rem;margin-bottom:1.5rem;color:#0f0f0f}
    .alert{padding:.75rem 1rem;border-radius:6px;margin-bottom:1.25rem;font-size:.875rem}
    .alert.ok{background:#d8f3dc;color:#2d6a4f}
    .card{background:#fff;border:1px solid #e8e6e1;border-radius:8px;padding:1.5rem;margin-bottom:1.5rem}
    .card h3{font-size:.9rem;color:#888;letter-spacing:.05em;text-transform:uppercase;margin-bottom:1rem}
    label{display:block;font-size:.8rem;color:#666;margin-bottom:.35rem;font-weight:500}
    input[type=text],input[type=email],textarea,select{width:100%;padding:.6rem .8rem;border:1px solid #ddd;border-radius:4px;font-size:.875rem;font-family:inherit;color:#1a1a1a;outline:none;transition:border-color .2s}
    input:focus,textarea:focus{border-color:#52b788}
    textarea{resize:vertical;min-height:80px}
    .btn{padding:.55rem 1.25rem;border:none;border-radius:4px;cursor:pointer;font-size:.875rem;font-weight:500;transition:background .2s}
    .btn-green{background:#2d6a4f;color:#fff}.btn-green:hover{background:#1e4d38}
    .btn-red{background:#e74c3c;color:#fff}.btn-red:hover{background:#c0392b}
    .btn-sm{padding:.35rem .8rem;font-size:.8rem}
    .grid2{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
    table{width:100%;border-collapse:collapse;font-size:.85rem}
    th{text-align:left;padding:.6rem .75rem;background:#f4f4f4;font-weight:600;color:#555}
    td{padding:.6rem .75rem;border-top:1px solid #f0f0ef}
    tr:hover td{background:#fafaf8}
    .tag{display:inline-block;padding:.2rem .6rem;border-radius:100px;font-size:.75rem;font-weight:500}
    .tag-new{background:#d8f3dc;color:#2d6a4f}
    .tag-read{background:#e8e6e1;color:#888}
    .form-row{margin-bottom:.9rem}
    .actions{display:flex;gap:.5rem;align-items:center}
    hr{border:none;border-top:1px solid #e8e6e1;margin:1.25rem 0}
  </style>
</head>
<body>

<div class="sidebar">
  <h1>Nexora<span>.</span></h1>
  <a href="index.php?tab=settings"     class="nav-item <?= $active_tab==='settings'?'active':'' ?>">⚙️ Settings</a>
  <a href="index.php?tab=features"     class="nav-item <?= $active_tab==='features'?'active':'' ?>">📋 Fitur</a>
  <a href="index.php?tab=testimonials" class="nav-item <?= $active_tab==='testimonials'?'active':'' ?>">💬 Testimonial</a>
  <a href="index.php?tab=messages"     class="nav-item <?= $active_tab==='messages'?'active':'' ?>">📨 Pesan Masuk</a>
  <a href="index.php?tab=subscribers"  class="nav-item <?= $active_tab==='subscribers'?'active':'' ?>">📧 Subscriber</a>
  <a href="../index.php" target="_blank" class="nav-item">🌐 Lihat Website</a>
  <a href="?logout=1" class="logout nav-item">← Keluar</a>
</div>

<div class="main">
  <h2>Panel Admin</h2>

  <?php if ($success_msg): ?>
    <div class="alert ok"><?= e($success_msg) ?></div>
  <?php endif; ?>

  <!-- ── SETTINGS ── -->
  <?php if ($active_tab === 'settings'): ?>
  <div class="card">
    <h3>Site Settings</h3>
    <?php foreach ($settings as $s): ?>
    <form method="post" style="margin-bottom:1rem">
      <input type="hidden" name="act" value="save_setting">
      <input type="hidden" name="key" value="<?= e($s['setting_key']) ?>">
      <div class="form-row">
        <label><?= e($s['setting_key']) ?></label>
        <?php if (strlen($s['setting_val']) > 80): ?>
          <textarea name="val"><?= e($s['setting_val']) ?></textarea>
        <?php else: ?>
          <input type="text" name="val" value="<?= e($s['setting_val']) ?>">
        <?php endif; ?>
      </div>
      <button class="btn btn-green btn-sm">Simpan</button>
    </form>
    <hr>
    <?php endforeach; ?>
  </div>

  <!-- ── FEATURES ── -->
  <?php elseif ($active_tab === 'features'): ?>
  <div class="card">
    <h3>Tambah Fitur Baru</h3>
    <form method="post">
      <input type="hidden" name="act" value="save_feature">
      <input type="hidden" name="id" value="0">
      <div class="grid2">
        <div class="form-row"><label>Icon (emoji)</label><input type="text" name="icon" maxlength="10" required></div>
        <div class="form-row"><label>Urutan</label><input type="text" name="sort_order" value="<?= count($features)+1 ?>"></div>
      </div>
      <div class="form-row"><label>Judul</label><input type="text" name="title" required></div>
      <div class="form-row"><label>Deskripsi</label><textarea name="desc" required></textarea></div>
      <button class="btn btn-green">Tambah Fitur</button>
    </form>
  </div>
  <div class="card">
    <h3>Daftar Fitur</h3>
    <table>
      <thead><tr><th>#</th><th>Icon</th><th>Judul</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($features as $f): ?>
        <tr>
          <td><?= (int)$f['sort_order'] ?></td>
          <td><?= e($f['icon']) ?></td>
          <td><?= e($f['title']) ?></td>
          <td class="actions">
            <button onclick="editFeature(<?= htmlspecialchars(json_encode($f)) ?>)" class="btn btn-sm btn-green">Edit</button>
            <form method="post" style="display:inline" onsubmit="return confirm('Hapus fitur ini?')">
              <input type="hidden" name="act" value="delete_feature">
              <input type="hidden" name="id"  value="<?= (int)$f['id'] ?>">
              <button class="btn btn-sm btn-red">Hapus</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <!-- Edit modal -->
  <div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:999;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:8px;padding:2rem;width:480px;max-width:95vw">
      <h3 style="margin-bottom:1rem;font-size:1rem">Edit Fitur</h3>
      <form method="post" id="editForm">
        <input type="hidden" name="act" value="save_feature">
        <input type="hidden" name="id"  id="ef_id">
        <div class="grid2">
          <div class="form-row"><label>Icon</label><input type="text" name="icon" id="ef_icon" maxlength="10"></div>
          <div class="form-row"><label>Urutan</label><input type="text" name="sort_order" id="ef_ord"></div>
        </div>
        <div class="form-row"><label>Judul</label><input type="text" name="title" id="ef_title"></div>
        <div class="form-row"><label>Deskripsi</label><textarea name="desc" id="ef_desc"></textarea></div>
        <div style="display:flex;gap:.75rem;margin-top:.5rem">
          <button class="btn btn-green">Simpan</button>
          <button type="button" onclick="document.getElementById('editModal').style.display='none'" class="btn" style="background:#e8e6e1">Batal</button>
        </div>
      </form>
    </div>
  </div>
  <script>
  function editFeature(f){
    document.getElementById('ef_id').value   = f.id;
    document.getElementById('ef_icon').value = f.icon;
    document.getElementById('ef_ord').value  = f.sort_order;
    document.getElementById('ef_title').value= f.title;
    document.getElementById('ef_desc').value = f.description;
    document.getElementById('editModal').style.display='flex';
  }
  </script>

  <!-- ── TESTIMONIALS ── -->
  <?php elseif ($active_tab === 'testimonials'): ?>
  <div class="card">
    <h3>Tambah Testimonial</h3>
    <form method="post">
      <input type="hidden" name="act" value="save_testimonial">
      <input type="hidden" name="id" value="0">
      <div class="grid2">
        <div class="form-row"><label>Nama</label><input type="text" name="name" required></div>
        <div class="form-row"><label>Jabatan · Perusahaan</label><input type="text" name="role" required></div>
      </div>
      <div class="form-row"><label>Kutipan</label><textarea name="quote" required></textarea></div>
      <div class="form-row"><label>Urutan</label><input type="text" name="sort_order" value="<?= count($testimonials)+1 ?>"></div>
      <button class="btn btn-green">Tambah</button>
    </form>
  </div>
  <div class="card">
    <h3>Daftar Testimonial</h3>
    <table>
      <thead><tr><th>#</th><th>Nama</th><th>Jabatan</th><th>Kutipan</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($testimonials as $t): ?>
        <tr>
          <td><?= (int)$t['sort_order'] ?></td>
          <td><?= e($t['name']) ?></td>
          <td><?= e($t['role']) ?></td>
          <td><?= e(mb_substr($t['quote'],0,60)) ?>…</td>
          <td>
            <form method="post" style="display:inline" onsubmit="return confirm('Hapus?')">
              <input type="hidden" name="act" value="delete_testimonial">
              <input type="hidden" name="id"  value="<?= (int)$t['id'] ?>">
              <button class="btn btn-sm btn-red">Hapus</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- ── MESSAGES ── -->
  <?php elseif ($active_tab === 'messages'): ?>
  <div class="card">
    <h3>Pesan Masuk (<?= count($messages) ?>)</h3>
    <?php if (empty($messages)): ?>
      <p style="color:#888">Belum ada pesan.</p>
    <?php else: ?>
    <table>
      <thead><tr><th>Tanggal</th><th>Nama</th><th>Email</th><th>Pesan</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($messages as $m): ?>
        <tr>
          <td style="white-space:nowrap"><?= e(date('d M Y H:i', strtotime($m['created_at']))) ?></td>
          <td><?= e($m['name']) ?></td>
          <td><a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a></td>
          <td><?= e(mb_substr($m['message'],0,80)) ?>…</td>
          <td><span class="tag <?= $m['is_read'] ? 'tag-read' : 'tag-new' ?>"><?= $m['is_read'] ? 'Dibaca' : 'Baru' ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <!-- ── SUBSCRIBERS ── -->
  <?php elseif ($active_tab === 'subscribers'): ?>
  <div class="card">
    <h3>Newsletter Subscribers (<?= count($subscribers) ?>)</h3>
    <?php if (empty($subscribers)): ?>
      <p style="color:#888">Belum ada subscriber.</p>
    <?php else: ?>
    <table>
      <thead><tr><th>#</th><th>Email</th><th>Tanggal Daftar</th></tr></thead>
      <tbody>
      <?php foreach ($subscribers as $i => $sub): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= e($sub['email']) ?></td>
          <td><?= e(date('d M Y H:i', strtotime($sub['created_at']))) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div>
</body>
</html>
