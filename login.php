<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if (current_user()) { header('Location: /techcopy/index.php'); exit; }

$error = '';
$blocked = false;
$waitMinutes = 0;
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
csrf_verify();
    // Controlla rate limit PRIMA di toccare il database
    $waitSec = login_remaining_time($ip);
    if ($waitSec > 0) {
        $blocked = true;
        $waitMinutes = ceil($waitSec / 60);
        $error = "Troppi tentativi. Riprova tra {$waitMinutes} minuto/i.";
    } else {
        
        $start = microtime(true);

        if (login_user(trim($_POST['email'] ?? ''), $_POST['password'] ?? '')) {
            
            $next = $_GET['next'] ?? '';
            $safe = $next && str_starts_with($next, '/techcopy/') ? $next : '/techcopy/index.php';
            header('Location: ' . $safe);
            exit;
        }

        
        $elapsed = microtime(true) - $start;
        if ($elapsed < 0.5) usleep((int)((0.5 - $elapsed) * 1000000));

        $waitSec = login_remaining_time($ip);
        if ($waitSec > 0) {
            $waitMinutes = ceil($waitSec / 60);
            $error = "Troppi tentativi falliti. Account bloccato per {$waitMinutes} minuto/i.";
            $blocked = true;
        } else {
            $attempts = LOGIN_MAX_ATTEMPTS - ($_SESSION['login_attempts_'.md5($ip)]['count'] ?? 0);
            $error = 'Email o password non validi.';
            if ($attempts <= 2 && $attempts > 0) {
                $error .= " (ancora {$attempts} tentativo/i prima del blocco)";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Scrive &amp; Riscrive SRL</title>
<link rel="icon" type="image/png" href="/techcopy/assets/img/favicon.png">
<link rel="apple-touch-icon" href="/techcopy/assets/img/favicon.png">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/techcopy/assets/css/style.css">
<script>(function(){if(localStorage.getItem('techcopy_theme')==='light')document.documentElement.classList.add('preload-light');})();</script>
<style>html.preload-light body{background:#f4f6f9;}</style>
<?php
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; img-src \'self\' data:; form-action \'self\'; frame-ancestors \'none\'');
header('Cache-Control: no-store, no-cache, must-revalidate, private');
?>
</head>
<body>
<div class="login-screen">
  <div class="login-box">
    <div class="login-logo">
      <div class="icon"><img src="/techcopy/assets/img/favicon.png" width="40" height="40"></div>
      <div><h1>Scrive &amp; Riscrive SRL</h1><span>Gestione Assistenza Stampanti</span></div>
    </div>

    <?php if ($error): ?>
    <div class="error-msg" role="alert">
      <?php if ($blocked): ?>⏳<?php else: ?>⚠️<?php endif; ?>
      <?= h($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" autocomplete="on" <?= $blocked ? 'style="opacity:.5;pointer-events:none"' : '' ?>>
	<?= csrf_field() ?>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email"
               value="<?= h($_POST['email'] ?? '') ?>"
               placeholder="email@azienda.it"
               required autofocus autocomplete="username">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="••••••••"
               required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px"
              <?= $blocked ? 'disabled' : '' ?>>
        🔐 Accedi
      </button>
    </form>

    <?php
    if (defined('APP_DEBUG') && APP_DEBUG):
    ?>
    <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border)">
      <p style="font-size:11px;color:var(--text3);margin-bottom:10px;font-family:var(--mono)">
        ⚙️ MODALITÀ DEBUG — Account demo (rimuovere in produzione)
      </p>
      <?php
      try {
          $users = db()->query("SELECT name,email,role,avatar,color FROM users WHERE active=1 ORDER BY FIELD(role,'admin','supervisor','tech','viewer','insert') LIMIT 6")->fetchAll();
          $roleIcon  = ['admin'=>'👑','supervisor'=>'🔍','tech'=>'🔧','viewer'=>'👁','insert'=>'🔧'];
          $roleLabel = ['admin'=>'Amministratore','supervisor'=>'Supervisore','tech'=>'Tecnico','viewer'=>'Visualizzatore','insert'=>'Inserimento'];
          foreach ($users as $u):
      ?>
      <div class="user-pill"
           onclick="document.getElementById('email').value='<?= h($u['email']) ?>';document.getElementById('password').value='admin123'">
        <div style="width:30px;height:30px;border-radius:50%;background:<?= h($u['color']) ?>22;color:<?= h($u['color']) ?>;border:1.5px solid <?= h($u['color']) ?>;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;flex-shrink:0"><?= h($u['avatar']) ?></div>
        <div style="flex:1;font-size:13px"><?= h($u['name']) ?></div>
        <span class="badge badge-<?= h($u['role']) ?>"><?= h(($roleIcon[$u['role']]??'').' '.($roleLabel[$u['role']]??$u['role'])) ?></span>
      </div>
      <?php endforeach; } catch(Exception $e) {} ?>
    </div>
    <?php endif; ?>

  </div>
</div>
<script>
(function(){const t=localStorage.getItem('techcopy_theme')||'dark';if(t==='light'){document.body.classList.add('light');document.documentElement.classList.remove('preload-light');}})();
</script>
</body>
</html>
