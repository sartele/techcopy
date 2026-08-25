<?php
// ============================================================
//  TechCopy — Layout condiviso
// ============================================================
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

function layout_header(string $title = 'TechCopy', string $active = ''): void {
    $user      = current_user();
    $flash     = get_flash();
    $openCount = 0;
    $lowStock  = 0;
    try {
        if ($user && $user['role'] === 'tech') {
            $s = db()->prepare("SELECT COUNT(*) FROM tickets WHERE status='open' AND tech_id=?");
            $s->execute([$user['id']]);
            $openCount = $s->fetchColumn();
        } else {
            $openCount = db()->query("SELECT COUNT(*) FROM tickets WHERE status='open'")->fetchColumn();
        }
        $lowStock = db()->query("SELECT COUNT(*) FROM consumables WHERE stock <= min_stock")->fetchColumn();
    } catch (Exception $e) {}

    $roleMeta = match($user['role'] ?? '') {
        'admin'      => ['👑', '#a78bfa', 'Amministratore'],
        'supervisor' => ['🔍', '#00d4ff', 'Supervisore'],
        'tech'       => ['🔧', '#39d353', 'Tecnico'],
        'viewer'     => ['👁', '#8b98a8', 'Visualizzatore'],
		'insert'     => ['🔧', '#00d4ff', 'Inserimento'],
        default      => ['•',  '#8b98a8', ''],
    };

    // ── HEADER HTTP DI SICUREZZA ──────────────────────────────
    // Inviati su ogni pagina autenticata
    header('X-Frame-Options: DENY');                          // blocca embedding in iframe (clickjacking)
    header('X-Content-Type-Options: nosniff');                // blocca MIME sniffing
    header('X-XSS-Protection: 1; mode=block');               // protezione XSS browser legacy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

    // Content-Security-Policy: specifica esattamente da dove possono arrivare risorse
    // 'nonce' per script inline se necessario — qui usiamo 'unsafe-inline' limitato
    // alle sole CDN note (Google Fonts, Leaflet, Unpkg)
    $cspParts = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' https://unpkg.com",
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com",
        "font-src 'self' https://fonts.gstatic.com",
        "img-src 'self' data: https://*.tile.openstreetmap.org https://*.basemaps.cartocdn.com https://*.openstreetmap.fr",
        "connect-src 'self' https://nominatim.openstreetmap.org",
        "frame-ancestors 'none'",
        "base-uri 'self'",
        "form-action 'self'",
    ];
    header('Content-Security-Policy: ' . implode('; ', $cspParts));

    // Cache: le pagine autenticate non devono essere cachate dal browser
    header('Cache-Control: no-store, no-cache, must-revalidate, private');
    header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($title) ?> — Assistenza Stampanti</title>
<link rel="icon" type="image/png" href="/techcopy/assets/img/favicon.png">
<link rel="apple-touch-icon" href="/techcopy/assets/img/favicon.png">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/techcopy/assets/css/style.css">
<script>(function(){if(localStorage.getItem('techcopy_theme')==='light')document.documentElement.classList.add('preload-light');})();</script>
<style>html.preload-light body{background:#f4f6f9;}</style>
</head>
<body>
<div class="app">
  <div class="sidebar-backdrop" id="sidebar-backdrop" onclick="closeMobileMenu()"></div>
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <h2><img src="/techcopy/assets/img/favicon.png" width="20" height="20"> Assistenza</h2>
      <p>Stampanti &amp; Fotocopiatori</p>
    </div>
    <?php if ($user): ?>
    <div class="sidebar-user">
      <div class="avatar" style="background:<?= h($user['color']) ?>22;color:<?= h($user['color']) ?>;border:1.5px solid <?= h($user['color']) ?>"><?= h($user['avatar']) ?></div>
      <div class="info">
        <div class="name"><?= h($user['name']) ?></div>
        <div class="role" style="color:<?= $roleMeta[1] ?>;font-size:11px"><?= $roleMeta[0] ?> <?= $roleMeta[2] ?></div>
      </div>
    </div>
    <?php endif; ?>
    <nav class="sidebar-nav">
      <div class="nav-section">Principale</div>
      <a href="/techcopy/index.php"              class="nav-item <?= $active==='dashboard'?'active':'' ?>"><span class="icon">📊</span> Dashboard</a>
      <a href="/techcopy/pages/map.php"          class="nav-item <?= $active==='map'?'active':'' ?>"><span class="icon">🗺️</span> Mappa Clienti</a>
      <div class="nav-section">Operativo</div>
      <a href="/techcopy/pages/tickets.php"      class="nav-item <?= $active==='tickets'?'active':'' ?>">
        <span class="icon">🔧</span> Interventi
        <?php if ($openCount > 0): ?><span class="nav-badge"><?= $openCount ?></span><?php endif; ?>
      </a>
      <a href="/techcopy/pages/clients.php"      class="nav-item <?= $active==='clients'?'active':'' ?>"><span class="icon">🏢</span> Clienti</a>
      <a href="/techcopy/pages/printers.php"     class="nav-item <?= $active==='printers'?'active':'' ?>"><span class="icon">🖨️</span> Stampanti</a>
      <div class="nav-section">Gestione</div>
      <a href="/techcopy/pages/consumables.php"  class="nav-item <?= $active==='consumables'?'active':'' ?>">
        <span class="icon">📦</span> Consumabili
        <?php if ($lowStock > 0): ?><span class="nav-badge" style="background:var(--orange)"><?= $lowStock ?></span><?php endif; ?>
      </a>
      <a href="/techcopy/pages/reports.php"      class="nav-item <?= $active==='reports'?'active':'' ?>"><span class="icon">📊</span> Report</a>
      <?php if ($user && $user['role'] === 'admin'): ?>
      <a href="/techcopy/pages/users.php"        class="nav-item <?= $active==='users'?'active':'' ?>"><span class="icon">👥</span> Utenti</a>
	  <a href="/techcopy/pages/settings.php"     class="nav-item <?= $active==='settings'?'active':'' ?>"><span class="icon">✉️</span> Email</a>
      <?php endif; ?>
    </nav>
    <div class="sidebar-footer">
      <button class="theme-toggle" onclick="toggleTheme()" id="theme-btn">
        <span id="theme-label">🌙 Tema scuro</span>
        <div class="toggle-track"><div class="toggle-knob"></div></div>
      </button>
      <div style="height:8px"></div>
      <a href="/techcopy/logout.php" class="btn btn-secondary btn-full btn-sm">⏻ Esci</a>
    </div>
  </aside>
  <div class="main">
    <div class="topbar">
      <button class="menu-toggle" onclick="toggleMenu()" aria-label="Menu">☰</button>
      <h3><?= h($title) ?></h3>
      <div class="topbar-actions" id="topbar-actions"></div>
    </div>
    <?php if ($flash): ?>
    <div class="flash-msg flash-<?= h($flash['type']) ?>"><?= h($flash['msg']) ?></div>
    <?php endif; ?>
    <div class="page">
<?php
}

function layout_footer(): void {
?>
    </div>
  </div>
</div>
<script src="/techcopy/assets/js/app.js"></script>
<script>
(function initTheme() {
  const saved = localStorage.getItem('techcopy_theme') || 'dark';
  applyTheme(saved, false);
})();
function applyTheme(theme, save) {
  const isLight = theme === 'light';
  document.body.classList.toggle('light', isLight);
  document.documentElement.classList.remove('preload-light');
  const label = document.getElementById('theme-label');
  if (label) label.textContent = isLight ? '☀️ Tema chiaro' : '🌙 Tema scuro';
  if (save) localStorage.setItem('techcopy_theme', theme);
}
function toggleTheme() {
  applyTheme(document.body.classList.contains('light') ? 'dark' : 'light', true);
}
</script>
</body>
</html>
<?php
}
