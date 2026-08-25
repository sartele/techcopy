<?php
require_once __DIR__ . '/../includes/layout.php';
require_login();
$db = db();
$me = current_user();

// ACTIONS
if ($_SERVER['REQUEST_METHOD']==='POST' && can_manage_clients()) {
    csrf_verify();
    $action = $_POST['action']??'';
    if ($action==='create') {
        $db->prepare("INSERT INTO clients (name,contact,phone,email,address,city,zip,lat,lng,notes) VALUES (?,?,?,?,?,?,?,?,?,?)")
           ->execute([$_POST['name'],$_POST['contact'],$_POST['phone'],$_POST['email'],$_POST['address'],$_POST['city'],$_POST['zip'],$_POST['lat']?:null,$_POST['lng']?:null,$_POST['notes']]);
        flash('Cliente creato ✅'); redirect('/techcopy/pages/clients.php');
    }
    if ($action==='update') {
        $db->prepare("UPDATE clients SET name=?,contact=?,phone=?,email=?,address=?,city=?,zip=?,lat=?,lng=?,notes=? WHERE id=?")
           ->execute([$_POST['name'],$_POST['contact'],$_POST['phone'],$_POST['email'],$_POST['address'],$_POST['city'],$_POST['zip'],$_POST['lat']?:null,$_POST['lng']?:null,$_POST['notes'],$_POST['id']]);
        flash('Cliente aggiornato ✅'); redirect('/techcopy/pages/clients.php?id='.$_POST['id']);
    }
    if ($action==='delete') {
        $db->prepare("UPDATE clients SET active=0 WHERE id=?")->execute([$_POST['id']]);
        flash('Cliente disattivato'); redirect('/techcopy/pages/clients.php');
    }
}

$detail = null;
if (isset($_GET['id'])) {
    $stmt=$db->prepare("SELECT * FROM clients WHERE id=?"); $stmt->execute([(int)$_GET['id']]); $detail=$stmt->fetch();
}

$search = $_GET['q']??'';
$where  = "active=1";
$params = [];
if ($search) { $where.=" AND (name LIKE ? OR contact LIKE ? OR city LIKE ?)"; $params=array_fill(0,3,"%$search%"); }
$stmt=$db->prepare("SELECT c.*,COUNT(DISTINCT p.id) AS printer_count,SUM(CASE WHEN t.status!='closed' THEN 1 ELSE 0 END) AS open_tickets FROM clients c LEFT JOIN printers p ON p.client_id=c.id AND p.active=1 LEFT JOIN tickets t ON t.client_id=c.id WHERE c.$where GROUP BY c.id ORDER BY c.name");
$stmt->execute($params);
$clients = $stmt->fetchAll();

layout_header('Clienti','clients');
?>

<?php if ($detail): ?>
<!-- DETTAGLIO CLIENTE -->
<?php
$printers = $db->prepare("SELECT * FROM printers WHERE client_id=? AND active=1 ORDER BY brand,model");
$printers->execute([$detail['id']]); $printers=$printers->fetchAll();
$tickets  = $db->prepare("SELECT t.*,p.brand,p.model FROM tickets t LEFT JOIN printers p ON p.id=t.printer_id WHERE t.client_id=? ORDER BY t.created_at DESC LIMIT 10");
$tickets->execute([$detail['id']]); $tickets=$tickets->fetchAll();
?>
<div style="margin-bottom:16px;display:flex;gap:8px">
  <a href="/techcopy/pages/clients.php" class="btn btn-secondary btn-sm">← Lista clienti</a>
  <?php if (can_manage_clients()): ?>
  <button class="btn btn-primary btn-sm" onclick="document.getElementById('edit-section').classList.toggle('hidden')">✏️ Modifica</button>
  <?php endif; ?>
</div>

<div class="info-grid" style="margin-bottom:24px">
  <div class="info-box"><div class="info-label">Ragione sociale</div><div class="info-value" style="font-size:16px"><?= h($detail['name']) ?></div></div>
  <div class="info-box"><div class="info-label">Referente</div><div class="info-value"><?= h($detail['contact']??'—') ?></div></div>
  <div class="info-box"><div class="info-label">Telefono</div><div class="info-value" style="font-family:var(--mono)"><?= h($detail['phone']??'—') ?></div></div>
  <div class="info-box"><div class="info-label">Email</div><div class="info-value"><?= h($detail['email']??'—') ?></div></div>
  <div class="info-box" style="grid-column:1/-1"><div class="info-label">Indirizzo</div>
    <div class="info-value" style="display:flex;align-items:center;gap:12px">
      📍 <?= h($detail['address'].', '.$detail['city']) ?>
      <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($detail['address'].', '.$detail['city']) ?>" target="_blank" class="btn btn-secondary btn-sm">🗺️ Google Maps</a>
    </div>
  </div>
</div>
<?php if ($detail['notes']): ?><div class="alert alert-info">📝 <?= h($detail['notes']) ?></div><?php endif; ?>

<!-- FORM MODIFICA (nascosto) -->
<?php if (can_manage_clients()): ?>
<div id="edit-section" class="hidden table-wrap" style="margin-bottom:24px;padding:0">
  <div class="table-head"><h4>✏️ Modifica Cliente</h4></div>
  <div style="padding:24px">
    <form method="POST">
      <?= csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= $detail['id'] ?>">
      <div class="form-grid">
        <div class="form-group form-full"><label>Ragione Sociale</label><input name="name" value="<?= h($detail['name']) ?>" required></div>
        <div class="form-group"><label>Referente</label><input name="contact" value="<?= h($detail['contact']) ?>"></div>
        <div class="form-group"><label>Telefono</label><input name="phone" value="<?= h($detail['phone']) ?>"></div>
        <div class="form-group"><label>Email</label><input name="email" value="<?= h($detail['email']) ?>"></div>
        <div class="form-group form-full"><label>Indirizzo</label><input name="address" value="<?= h($detail['address']) ?>"></div>
        <div class="form-group"><label>Città</label><input name="city" value="<?= h($detail['city']) ?>"></div>
        <div class="form-group"><label>CAP</label><input name="zip" value="<?= h($detail['zip']) ?>"></div>
        <div class="form-group"><label>Latitudine GPS</label><input name="lat" type="number" step="0.0001" value="<?= h($detail['lat']) ?>"></div>
        <div class="form-group"><label>Longitudine GPS</label><input name="lng" type="number" step="0.0001" value="<?= h($detail['lng']) ?>"></div>
        <div class="form-group form-full"><label>Note</label><textarea name="notes"><?= h($detail['notes']) ?></textarea></div>
      </div>
      <button type="submit" class="btn btn-primary btn-sm">💾 Salva</button>
    </form>
  </div>
</div>
<?php endif; ?>

<div class="section-title">Stampanti associate (<?= count($printers) ?>)</div>
<?php foreach ($printers as $p): ?>
<div class="ticket-card" style="padding:14px;margin-bottom:10px;cursor:default">
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
    <span style="font-size:20px">🖨️</span>
    <div style="flex:1"><div style="font-weight:600"><?= h($p['brand'].' '.$p['model']) ?></div>
    <div style="font-size:11px;color:var(--text2);font-family:var(--mono)">S/N: <?= h($p['serial']) ?></div></div>
    <span class="badge badge-<?= $p['type']==='color'?'tech':'viewer' ?>"><?= $p['type']==='color'?'🎨 Colore':'⬛ B/N' ?></span>
    <?php if($p['has_adf']): ?><span class="chip">ADF</span><?php endif; ?>
    <?php if($p['has_duplex']): ?><span class="chip">Duplex</span><?php endif; ?>
    <?php if($p['has_fax']): ?><span class="chip">Fax</span><?php endif; ?>
  </div>
  <div style="font-size:12px;color:var(--text2)">
    📍 <?= h($p['location']) ?> &nbsp;|&nbsp;
    BN: <span style="font-family:var(--mono)"><?= number_format($p['counter_bw']) ?></span>
    <?php if ($p['counter_color']>0): ?> &nbsp;|&nbsp; Col: <span style="font-family:var(--mono)"><?= number_format($p['counter_color']) ?></span><?php endif; ?>
  </div>
</div>
<?php endforeach; ?>

<div class="section-title" style="margin-top:24px">Storico interventi (<?= count($tickets) ?>)</div>
<?php foreach ($tickets as $t): $cls=$t['status']==='closed'?'closed':($t['priority']==='urgent'?'urgent':'open'); ?>
<a href="/techcopy/pages/tickets.php?id=<?= $t['id'] ?>" class="ticket-card <?= $cls ?>" style="display:block;margin-bottom:8px;padding:12px">
  <div class="ticket-top"><span class="ticket-id">#<?= str_pad($t['id'],4,'0',STR_PAD_LEFT) ?></span><span style="flex:1"><?= h($t['title']) ?></span><?= status_badge($t['status']) ?></div>
  <div class="ticket-meta"><span>🖨️ <?= h($t['brand'].' '.$t['model']) ?></span><span>📅 <?= substr($t['created_at'],0,10) ?></span></div>
</a>
<?php endforeach; ?>

<?php else: ?>
<!-- LISTA CLIENTI -->
<div class="filter-bar">
  <form method="GET" style="display:contents">
    <div class="search-bar"><span>🔍</span><input name="q" value="<?= h($search) ?>" placeholder="Cerca cliente..." oninput="this.form.submit()"></div>
    <?php if (can_manage_clients()): ?>
    <a href="/techcopy/pages/client_form.php" class="btn btn-primary btn-sm" style="margin-left:auto">➕ Nuovo Cliente</a>
    <?php endif; ?>
  </form>
</div>
<div class="table-wrap">
  <table>
    <thead><tr><th>Cliente</th><th>Referente</th><th>Città</th><th>Telefono</th><th>Stampanti</th><th>Interventi</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($clients as $c): ?>
      <tr>
        <td><strong><?= h($c['name']) ?></strong></td>
        <td style="color:var(--text2)"><?= h($c['contact']??'—') ?></td>
        <td><?= h($c['city']??'—') ?></td>
        <td style="font-family:var(--mono);font-size:12px"><?= h($c['phone']??'—') ?></td>
        <td><span class="badge badge-tech">🖨️ <?= $c['printer_count'] ?></span></td>
        <td><?= $c['open_tickets']>0 ? '<span class="badge badge-open">'.$c['open_tickets'].' aperti</span>' : '<span class="badge badge-closed">✅ OK</span>' ?></td>
        <td><a href="?id=<?= $c['id'] ?>" class="btn btn-secondary btn-sm">Dettagli</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
<?php layout_footer(); ?>
