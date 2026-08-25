<?php
// ============================================================
//  TechCopy — Impostazioni Email
//  Accessibile solo agli amministratori
// ============================================================
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/notifications.php';
require_role('admin');
$db  = db();
$me  = current_user();

// ── AZIONI POST ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    // Test invio email
    if ($action === 'test_email') {
        $toEmail = trim($_POST['test_to'] ?? $me['email']);
        $toName  = $me['name'];
        if (filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $ok = send_test_email($toEmail, $toName);
            flash($ok
                ? "Email di test inviata a {$toEmail} ✅"
                : "Errore invio. Verifica la configurazione SMTP in includes/config.php.",
                $ok ? 'success' : 'error'
            );
        } else {
            flash('Indirizzo email non valido.', 'error');
        }
        redirect('/techcopy/pages/settings.php');
    }

    // Aggiorna preferenza notifiche di un utente
    if ($action === 'toggle_notify') {
        $uid = (int)$_POST['user_id'];
        $val = (int)$_POST['notify_email'];
        $db->prepare("UPDATE users SET notify_email=? WHERE id=?")->execute([$val ? 1 : 0, $uid]);
        flash('Preferenza notifiche aggiornata.');
        redirect('/techcopy/pages/settings.php');
    }
}

// Carica tutti gli utenti con preferenza notifiche
$users = $db->query("
    SELECT id, name, email, role, avatar, color, notify_email, active
    FROM users
    ORDER BY FIELD(role,'admin','supervisor','tech','viewer'), name
")->fetchAll();

$mailEnabled = defined('MAIL_ENABLED') && MAIL_ENABLED;
$roleMeta = [
    'admin'      => ['👑','#a78bfa','Amministratore'],
    'supervisor' => ['🔍','#00d4ff','Supervisore'],
    'tech'       => ['🔧','#39d353','Tecnico'],
    'viewer'     => ['👁','#8b98a8','Visualizzatore'],
];

layout_header('Impostazioni Email', 'settings');
?>

<!-- ── STATO SISTEMA ── -->
<div class="alert <?= $mailEnabled ? 'alert-success' : 'alert-warning' ?>" style="margin-bottom:24px">
  <?php if ($mailEnabled): ?>
  ✅ Il sistema di notifiche email è <strong>attivo</strong>.
  Mittente: <code style="font-family:var(--mono)"><?= h(MAIL_FROM) ?></code>
  &nbsp;·&nbsp; SMTP: <code style="font-family:var(--mono)"><?= h(MAIL_SMTP_HOST.':'.MAIL_SMTP_PORT) ?></code>
  <?php else: ?>
  ⚠️ Il sistema di notifiche email è <strong>disabilitato</strong>.
  Per attivarlo aprire <code style="font-family:var(--mono)">includes/config.php</code>
  e impostare <code style="font-family:var(--mono)">MAIL_ENABLED = true</code>
  dopo aver configurato i parametri SMTP.
  <?php endif; ?>
</div>

<div class="two-col" style="align-items:start">

  <!-- ── CONFIGURAZIONE SMTP ── -->
  <div>
    <div class="table-wrap" style="margin-bottom:20px;padding:0">
      <div class="table-head"><h4>⚙️ Configurazione SMTP (config.php)</h4></div>
      <div style="padding:20px">

        <!-- Valori attuali -->
        <div style="background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius);padding:16px;margin-bottom:20px;font-family:var(--mono);font-size:12px;line-height:2">
          <div style="margin-bottom:4px;font-size:11px;color:var(--text2);font-family:var(--sans);text-transform:uppercase;letter-spacing:.5px">Valori attuali</div>
          <div><span style="color:var(--text2)">MAIL_ENABLED  </span> <span style="color:<?= $mailEnabled?'var(--green)':'var(--orange)' ?>"><?= $mailEnabled ? 'true' : 'false' ?></span></div>
          <div><span style="color:var(--text2)">MAIL_MODE     </span> <?= h(defined('MAIL_MODE') ? MAIL_MODE : '—') ?></div>
          <div><span style="color:var(--text2)">MAIL_FROM     </span> <?= h(defined('MAIL_FROM') ? MAIL_FROM : '—') ?></div>
          <div><span style="color:var(--text2)">MAIL_SMTP_HOST</span> <?= h(defined('MAIL_SMTP_HOST') ? MAIL_SMTP_HOST : '—') ?></div>
          <div><span style="color:var(--text2)">MAIL_SMTP_PORT</span> <?= defined('MAIL_SMTP_PORT') ? MAIL_SMTP_PORT : '—' ?></div>
          <div><span style="color:var(--text2)">MAIL_SMTP_ENC </span> <?= h(defined('MAIL_SMTP_ENC') ? (MAIL_SMTP_ENC ?: 'nessuna') : '—') ?></div>
          <div><span style="color:var(--text2)">MAIL_SMTP_USER</span> <?= defined('MAIL_SMTP_USER') && MAIL_SMTP_USER ? h(MAIL_SMTP_USER) : '<span style="color:var(--orange)">non impostato</span>' ?></div>
          <div><span style="color:var(--text2)">MAIL_SMTP_PASS</span> <?= defined('MAIL_SMTP_PASS') && MAIL_SMTP_PASS ? '••••••••' : '<span style="color:var(--orange)">non impostata</span>' ?></div>
        </div>

        <div class="alert alert-info" style="font-size:12px;margin-bottom:16px">
          💡 Per modificare la configurazione SMTP aprire il file
          <strong>includes/config.php</strong> e aggiornare le costanti
          nella sezione <em>EMAIL / SMTP</em>. Dopo la modifica, ricaricare questa pagina.
        </div>

        <!-- Istruzioni provider più comuni -->
        <div class="section-title">Guide per i provider più comuni</div>
        <div style="display:flex;flex-direction:column;gap:8px">
          <?php
          $providers = [
//            ['Gmail',       'smtp.gmail.com', 587, 'tls', 'Usare una <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color:var(--accent)">App Password</a> (non la password Google). Attivare "Accesso app meno sicure" oppure 2FA + App Password.'],
//            ['Outlook/Hotmail','smtp-mail.outlook.com', 587, 'tls', 'Usare email e password Microsoft normali.'],
//            ['Yahoo',       'smtp.mail.yahoo.com', 587, 'tls', 'Generare una <a href="https://security.yahoo.com/security/generate-app-password" target="_blank" style="color:var(--accent)">App Password</a> da Account Yahoo.'],
//            ['Aruba',       'smtps.aruba.it', 465, 'ssl', 'Porta 465 con SSL. Usare email e password Aruba.'],
            ['Server custom','mail.tuodominio.it', 587, 'tls', 'Usare i dati forniti dal provider di hosting.'],
          ];
          foreach ($providers as [$name, $host, $port, $enc, $note]):
          ?>
          <div style="background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius);padding:12px 14px">
            <div style="font-weight:600;font-size:13px;margin-bottom:4px"><?= $name ?></div>
            <div style="font-family:var(--mono);font-size:11px;color:var(--text2);margin-bottom:4px">
              Host: <?= $host ?> &nbsp;·&nbsp; Port: <?= $port ?> &nbsp;·&nbsp; Enc: <?= strtoupper($enc) ?>
            </div>
            <div style="font-size:12px;color:var(--text2);line-height:1.5"><?= $note ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Test invio -->
    <div class="table-wrap" style="padding:0">
      <div class="table-head"><h4>📧 Test invio email</h4></div>
      <div style="padding:20px">
        <?php if (!$mailEnabled): ?>
        <div class="alert alert-warning">⚠️ Attivare MAIL_ENABLED prima di testare.</div>
        <?php else: ?>
        <form method="POST">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="test_email">
          <div class="form-group">
            <label>Invia email di test a</label>
            <input type="email" name="test_to" value="<?= h($me['email'] ?? '') ?>"
                   placeholder="email@esempio.it" required>
          </div>
          <button type="submit" class="btn btn-primary btn-sm">📤 Invia email di test</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ── PREFERENZE NOTIFICHE UTENTI ── -->
  <div>
    <div class="table-wrap" style="padding:0">
      <div class="table-head">
        <h4>🔔 Preferenze notifiche utenti</h4>
        <span style="font-size:12px;color:var(--text2)">Admin e Supervisor ricevono tutte le notifiche</span>
      </div>
      <div style="padding:16px">
        <?php foreach ($users as $u):
          $rm = $roleMeta[$u['role']] ?? ['•','#8b98a8',$u['role']];
        ?>
        <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border);<?= !$u['active']?'opacity:.45':'' ?>">
          <div style="width:32px;height:32px;border-radius:50%;background:<?= h($u['color']) ?>22;color:<?= h($u['color']) ?>;border:1.5px solid <?= h($u['color']) ?>;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0"><?= h($u['avatar']) ?></div>
          <div style="flex:1">
            <div style="font-size:13px;font-weight:500"><?= h($u['name']) ?></div>
            <div style="font-size:11px;color:var(--text2)"><?= h($u['email'] ?: 'nessuna email') ?></div>
          </div>
          <span style="font-size:10px;color:<?= $rm[1] ?>"><?= $rm[0].' '.$rm[2] ?></span>
          <form method="POST" style="display:flex;align-items:center;gap:6px">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="toggle_notify">
            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
            <input type="hidden" name="notify_email" value="<?= $u['notify_email'] ? 0 : 1 ?>">
            <button type="submit"
                    class="btn btn-sm <?= $u['notify_email'] ? 'btn-success' : 'btn-secondary' ?>"
                    title="<?= $u['notify_email'] ? 'Notifiche attive — clicca per disabilitare' : 'Notifiche disabilitate — clicca per attivare' ?>"
                    <?= !$u['email'] ? 'disabled title="Nessuna email configurata"' : '' ?>>
              <?= $u['notify_email'] ? '🔔 Attivo' : '🔕 Off' ?>
            </button>
          </form>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Quando vengono inviate le notifiche -->
    <div class="table-wrap" style="padding:0;margin-top:16px">
      <div class="table-head"><h4>📋 Quando vengono inviate le notifiche</h4></div>
      <div style="padding:16px">
        <?php
        $events = [
          ['🆕', 'Nuovo intervento aperto',       'Tecnico assegnato + Admin/Supervisor'],
          ['👤', 'Tecnico assegnato/riassegnato',  'Solo il tecnico appena assegnato'],
          ['🔄', 'Stato cambiato (aperto→attesa)', 'Tutti i tecnici del team + Admin/Supervisor'],
          ['✅', 'Intervento chiuso/risolto',      'Tutti i tecnici del team + Admin/Supervisor'],
          ['🔴', 'Intervento eliminato',           'Nessuna notifica (solo log interno)'],
        ];
        foreach ($events as [$ico, $evento, $destinatari]):
        ?>
        <div style="display:flex;gap:10px;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px">
          <span><?= $ico ?></span>
          <div style="flex:1"><strong><?= $evento ?></strong></div>
          <div style="font-size:12px;color:var(--text2);text-align:right"><?= $destinatari ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</div>

<?php layout_footer(); ?>
