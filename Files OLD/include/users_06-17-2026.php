<?php
require_once __DIR__ . '/../includes/layout.php';
require_role('admin');
$db = db();
$me = current_user();

if ($_SERVER['REQUEST_METHOD']==='POST') {
	csrf_verify();
    $action = $_POST['action']??'';
    if ($action==='create') {
        $email = trim($_POST['email']);
        $check = $db->prepare("SELECT id FROM users WHERE email=?"); $check->execute([$email]);
        if ($check->fetch()) { flash('Email già in uso.','error'); redirect('/techcopy/pages/users.php'); }
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $words = explode(' ', trim($_POST['name']));
        $initials = strtoupper(implode('', array_map(fn($w)=>$w[0], $words)));
        $initials = substr($initials,0,2);
        $colors = ['#a78bfa','#00d4ff','#39d353','#f79000','#f44747','#e879f9','#38bdf8'];
        $color  = $colors[count($db->query("SELECT id FROM users")->fetchAll()) % count($colors)];
        $db->prepare("INSERT INTO users (name,email,password,role,avatar,color) VALUES (?,?,?,?,?,?)")
           ->execute([$_POST['name'],$email,$hash,$_POST['role'],$initials,$color]);
        flash('Utente creato ✅'); redirect('/techcopy/pages/users.php');
    }
    if ($action==='toggle') {
        $id=(int)$_POST['id'];
        if ($id==$me['id']) { flash('Non puoi disattivare te stesso.','error'); redirect('/techcopy/pages/users.php'); }
        $db->prepare("UPDATE users SET active=1-active WHERE id=?")->execute([$id]);
        flash('Stato utente aggiornato'); redirect('/techcopy/pages/users.php');
    }
    if ($action==='reset_pwd') {
        $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($_POST['new_password'],PASSWORD_DEFAULT),(int)$_POST['id']]);
        flash('Password aggiornata ✅'); redirect('/techcopy/pages/users.php');
    }
    if ($action==='change_role') {
        $id=(int)$_POST['id'];
        if ($id==$me['id']) { flash('Non puoi cambiare il tuo ruolo.','error'); redirect('/techcopy/pages/users.php'); }
        $allowed = ['admin','supervisor','tech','viewer'];
        if (in_array($_POST['role'],$allowed)) {
            $db->prepare("UPDATE users SET role=? WHERE id=?")->execute([$_POST['role'],$id]);
            flash('Ruolo aggiornato ✅');
        }
        redirect('/techcopy/pages/users.php');
    }
}

$users = $db->query("SELECT u.*, (SELECT COUNT(*) FROM tickets t WHERE t.tech_id=u.id AND t.status!='closed') AS open_tickets, (SELECT COUNT(*) FROM tickets t WHERE t.tech_id=u.id) AS total_tickets FROM users u ORDER BY u.active DESC, FIELD(u.role,'admin','supervisor','tech','viewer'), u.name")->fetchAll();

$roleMeta = ['admin'=>['👑','#a78bfa','Amministratore'],'supervisor'=>['🔍','#00d4ff','Supervisore'],'tech'=>['🔧','#39d353','Tecnico'],'viewer'=>['👁','#8b98a8','Visualizzatore'],'insert'=>['🔧','#00d4ff','Inserimento']];

layout_header('Gestione Utenti','users');
?>

<div style="display:flex;justify-content:flex-end;margin-bottom:16px">
  <button class="btn btn-primary btn-sm" onclick="document.getElementById('create-form').classList.toggle('hidden')">➕ Nuovo Utente</button>
</div>

<!-- FORM CREAZIONE -->
<div id="create-form" class="hidden table-wrap" style="margin-bottom:24px;padding:0">
  <div class="table-head"><h4>➕ Crea Nuovo Utente</h4></div>
  <div style="padding:24px">
    <form method="POST">
      <input type="hidden" name="action" value="create">
      <div class="form-grid">
        <div class="form-group"><label>Nome e Cognome *</label><input name="name" placeholder="Mario Rossi" required></div>
        <div class="form-group"><label>Email *</label><input type="email" name="email" placeholder="mario@azienda.it" required></div>
        <div class="form-group">
          <label>Ruolo</label>
          <select name="role">
            <option value="tech">🔧 Tecnico</option>
            <option value="supervisor">🔍 Supervisore</option>
            <option value="admin">👑 Amministratore</option>
            <option value="viewer">👁 Visualizzatore</option>
			<option value="insert">🔧 Inserimento</option>
          </select>
        </div>
        <div class="form-group"><label>Password *</label><input type="password" name="password" placeholder="Minimo 6 caratteri" minlength="6" required></div>
      </div>
      <button type="submit" class="btn btn-primary btn-sm">✅ Crea Utente</button>
    </form>
  </div>
</div>

<!-- TABELLA UTENTI -->
<div class="table-wrap">
  <table>
    <thead><tr><th>Utente</th><th>Email</th><th>Ruolo</th><th>Ticket aperti</th><th>Totale ticket</th><th>Stato</th><th>Azioni</th></tr></thead>
    <tbody>
      <?php foreach($users as $u):
        $rm = $roleMeta[$u['role']] ?? ['•','#8b98a8',$u['role']];
      ?>
      <tr style="<?= !$u['active']?'opacity:.5':'' ?>">
        <td>
          <div style="display:flex;align-items:center;gap:10px">
            <div style="width:34px;height:34px;border-radius:50%;background:<?= h($u['color']) ?>22;color:<?= h($u['color']) ?>;border:1.5px solid <?= h($u['color']) ?>;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;flex-shrink:0"><?= h($u['avatar']) ?></div>
            <div>
              <div style="font-weight:500"><?= h($u['name']) ?></div>
              <?php if ($u['id']==$me['id']): ?><span class="chip" style="font-size:10px">Tu</span><?php endif; ?>
            </div>
          </div>
        </td>
        <td style="font-family:var(--mono);font-size:12px"><?= h($u['email']) ?></td>
        <td>
          <?php if ($u['id']!=$me['id']): ?>
          <form method="POST" style="display:inline">
		  <?= csrf_field() ?>
            <input type="hidden" name="action" value="change_role">
            <input type="hidden" name="id" value="<?= $u['id'] ?>">
            <select name="role" onchange="this.form.submit()" style="background:var(--surface2);border:1px solid var(--border);color:var(--text);padding:4px 8px;border-radius:4px;font-size:12px;cursor:pointer">
              <option value="admin"      <?= $u['role']==='admin'?'selected':'' ?>>👑 Admin</option>
              <option value="supervisor" <?= $u['role']==='supervisor'?'selected':'' ?>>🔍 Supervisore</option>
              <option value="tech"       <?= $u['role']==='tech'?'selected':'' ?>>🔧 Tecnico</option>
              <option value="viewer"     <?= $u['role']==='viewer'?'selected':'' ?>>👁 Viewer</option>
			  <option value="insert"     <?= $u['role']==='insert'?'selected':'' ?>>🔧 Inserimento</option>
            </select>
          </form>
          <?php else: ?>
          <span style="color:<?= $rm[1] ?>;font-size:13px"><?= $rm[0].' '.$rm[2] ?></span>
          <?php endif; ?>
        </td>
        <td><?= $u['open_tickets']>0 ? '<span class="badge badge-open">'.$u['open_tickets'].'</span>' : '<span style="color:var(--text3)">—</span>' ?></td>
        <td style="font-family:var(--mono);font-size:12px"><?= $u['total_tickets'] ?></td>
        <td><span class="badge <?= $u['active']?'badge-closed':'badge-urgent' ?>"><?= $u['active']?'Attivo':'Inattivo' ?></span></td>
        <td>
          <div class="td-actions">
            <button class="btn btn-secondary btn-sm" onclick="showPwdReset(<?= $u['id'] ?>,'<?= h($u['name']) ?>')">🔑 Pwd</button>
            <?php if ($u['id']!=$me['id']): ?>
            <form method="POST" style="display:inline">
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="id" value="<?= $u['id'] ?>">
              <button type="submit" class="btn <?= $u['active']?'btn-danger':'btn-success' ?> btn-sm"><?= $u['active']?'Disabilita':'Abilita' ?></button>
            </form>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- MODAL RESET PASSWORD -->
<div id="pwd-modal" style="display:none" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
  <div class="modal" style="width:400px">
    <div class="modal-header"><h3>🔑 Reset Password</h3><button class="close-btn" onclick="document.getElementById('pwd-modal').style.display='none'">✕</button></div>
    <div class="modal-body">
      <form method="POST">
	  <?= csrf_field() ?>
        <input type="hidden" name="action" value="reset_pwd">
        <input type="hidden" name="id" id="pwd-uid">
        <div class="form-group"><label>Nuova password per <strong id="pwd-uname"></strong></label><input type="password" name="new_password" placeholder="Minimo 6 caratteri" minlength="6" required></div>
        <button type="submit" class="btn btn-primary btn-full">💾 Aggiorna Password</button>
      </form>
    </div>
  </div>
</div>
<script>
function showPwdReset(id,name){document.getElementById('pwd-uid').value=id;document.getElementById('pwd-uname').textContent=name;document.getElementById('pwd-modal').style.display='flex';}
</script>
<?php layout_footer(); ?>
