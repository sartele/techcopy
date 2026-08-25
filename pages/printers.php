<?php
require_once __DIR__ . '/../includes/layout.php';
require_login();
$db = db();

if ($_SERVER['REQUEST_METHOD']==='POST' && can_manage_printers()) {
	csrf_verify();
    $action = $_POST['action']??'';
    $adfVal = in_array($_POST['adf']??'', ['none','simple','duplex']) ? $_POST['adf'] : 'none';
    if ($action==='create') {
        $db->prepare("INSERT INTO printers (client_id,brand,model,serial,type,location,rete_lan,rete_wifi,adf,has_duplex,has_scan,counter_bw,counter_color,purchase_date,warranty_exp,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
           ->execute([$_POST['client_id'],$_POST['brand'],$_POST['model'],$_POST['serial'],$_POST['type'],$_POST['location'],isset($_POST['rete_lan'])?1:0,isset($_POST['rete_wifi'])?1:0,$adfVal,isset($_POST['has_duplex'])?1:0,isset($_POST['has_scan'])?1:0,(int)$_POST['counter_bw'],(int)$_POST['counter_color'],$_POST['purchase_date']?:null,$_POST['warranty_exp']?:null,$_POST['notes']]);
        flash('Stampante aggiunta ✅'); redirect('/techcopy/pages/printers.php');
    }
    if ($action==='update') {
        $db->prepare("UPDATE printers SET brand=?,model=?,serial=?,type=?,location=?,rete_lan=?,rete_wifi=?,adf=?,has_duplex=?,has_scan=?,counter_bw=?,counter_color=?,purchase_date=?,warranty_exp=?,notes=? WHERE id=?")
           ->execute([$_POST['brand'],$_POST['model'],$_POST['serial'],$_POST['type'],$_POST['location'],isset($_POST['rete_lan'])?1:0,isset($_POST['rete_wifi'])?1:0,$adfVal,isset($_POST['has_duplex'])?1:0,isset($_POST['has_scan'])?1:0,(int)$_POST['counter_bw'],(int)$_POST['counter_color'],$_POST['purchase_date']?:null,$_POST['warranty_exp']?:null,$_POST['notes'],(int)$_POST['id']]);
        flash('Stampante aggiornata ✅'); redirect('/techcopy/pages/printers.php?id='.$_POST['id']);
    }
    if ($action==='delete' && is_admin()) {
        $db->prepare("UPDATE printers SET active=0 WHERE id=?")->execute([$_POST['id']]);
        flash('Stampante rimossa.'); redirect('/techcopy/pages/printers.php');
    }
}

$detail = null;
if (isset($_GET['id'])) {
    $s=$db->prepare("SELECT p.*,c.name AS client_name FROM printers p LEFT JOIN clients c ON c.id=p.client_id WHERE p.id=?"); $s->execute([(int)$_GET['id']]); $detail=$s->fetch();
}

$search=$_GET['q']??''; $where="p.active=1"; $params=[];
if ($search) { $where.=" AND (p.brand LIKE ? OR p.model LIKE ? OR p.serial LIKE ? OR c.name LIKE ?)"; $params=array_fill(0,4,"%$search%"); }
$stmt=$db->prepare("SELECT p.*,c.name AS client_name FROM printers p LEFT JOIN clients c ON c.id=p.client_id WHERE $where ORDER BY c.name,p.brand");
$stmt->execute($params); $printers=$stmt->fetchAll();
$clients=$db->query("SELECT id,name FROM clients WHERE active=1 ORDER BY name")->fetchAll();

layout_header('Stampanti','printers');
?>

<?php if ($detail): ?>
<?php $p=$detail;
$tks=$db->prepare("SELECT t.*,u.name AS tech_name FROM tickets t LEFT JOIN users u ON u.id=t.tech_id WHERE t.printer_id=? ORDER BY t.created_at DESC"); $tks->execute([$p['id']]); $tks=$tks->fetchAll();
?>
<div style="margin-bottom:16px;display:flex;gap:8px;flex-wrap:wrap">
  <a href="/techcopy/pages/printers.php" class="btn btn-secondary btn-sm">← Lista stampanti</a>
  <?php if(can_manage_printers()): ?><button class="btn btn-primary btn-sm" onclick="document.getElementById('edit-sec').classList.toggle('hidden')">✏️ Modifica</button><?php endif; ?>
</div>

<div class="info-grid" style="margin-bottom:20px">
<div class="info-box"><div class="info-label">Cliente</div><div class="info-value"><a href="/techcopy/pages/clients.php?id=<?= $p['client_id'] ?>" style="color:var(--accent)"><?= h($p['client_name']) ?></a></div></div>
  <div class="info-box"><div class="info-label">Marca</div><div class="info-value"><?= h($p['brand']) ?></div></div>
  <div class="info-box"><div class="info-label">Modello</div><div class="info-value"><?= h($p['model']) ?></div></div>
  <div class="info-box"><div class="info-label">N° Serie</div><div class="info-value" style="font-family:var(--mono)"><?= h($p['serial']) ?></div></div>
  <div class="info-box"><div class="info-label">Tipo</div><div class="info-value"><span class="badge badge-<?= $p['type']==='color'?'open':'viewer' ?>"><?= $p['type']==='color'?'🎨 Colore':'⬛ B/N' ?></span></div></div>
  <div class="info-box"><div class="info-label">Posizione</div><div class="info-value"><?= h($p['location']??'—') ?></div></div>
  <div class="info-box"><div class="info-label">ADF</div><div class="info-value"><?= adf_label($p['adf']) ?></div></div>
  <div class="info-box"><div class="info-label">Duplex stampa</div><div class="info-value"><?= $p['has_duplex']?'✅ Sì':'❌ No' ?></div></div>
  <div class="info-box"><div class="info-label">Scanner di rete</div><div class="info-value"><?= $p['has_scan']?'✅ Sì':'❌ No' ?></div></div>
  <div class="info-box"><div class="info-label">Connettività</div><div class="info-value">
    <?php if($p['rete_lan']): ?><span class="chip" style="margin:2px">🔌 LAN</span><?php endif; ?>
    <?php if($p['rete_wifi']): ?><span class="chip" style="margin:2px">📶 WiFi</span><?php endif; ?>
    <?php if(!$p['rete_lan']&&!$p['rete_wifi']): ?>—<?php endif; ?>
  </div></div>
  <div class="info-box"><div class="info-label">Contatore Nero</div><div class="info-value" style="font-family:var(--mono)"><?= number_format($p['counter_bw']) ?></div></div>
  <div class="info-box"><div class="info-label">Contatore Colore</div><div class="info-value" style="font-family:var(--mono)"><?= number_format($p['counter_color']) ?></div></div>
  <?php if($p['purchase_date']): ?><div class="info-box"><div class="info-label">Data acquisto</div><div class="info-value" style="font-family:var(--mono);font-size:12px"><?= h($p['purchase_date']) ?></div></div><?php endif; ?>
  <?php if($p['warranty_exp']): ?>
  <div class="info-box"><div class="info-label">Fine garanzia</div>
    <div class="info-value" style="font-family:var(--mono);font-size:12px;color:<?= strtotime($p['warranty_exp'])<time()?'var(--red)':'var(--green)' ?>">
      <?= h($p['warranty_exp']) ?> <?= strtotime($p['warranty_exp'])<time()?'⚠️ scaduta':'' ?>
    </div>
  </div>
  <?php endif; ?><div class="info-box"><div class="info-label">Note</div><div class="info-value"><?= h($p['notes']??'—') ?></div></div>
</div>

<?php if(can_manage_printers()): ?>
<div id="edit-sec" class="hidden table-wrap" style="margin-bottom:24px;padding:0">
  <div class="table-head"><h4>✏️ Modifica Stampante</h4></div>
  <div style="padding:24px">
    <form method="POST">
	<?= csrf_field() ?>
      <input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= $p['id'] ?>">
      <div class="form-grid">
        <div class="form-group"><label>Marca *</label><input name="brand" value="<?= h($p['brand']) ?>" required></div>
        <div class="form-group"><label>Modello *</label><input name="model" value="<?= h($p['model']) ?>" required></div>
        <div class="form-group"><label>N° Serie *</label><input name="serial" value="<?= h($p['serial']) ?>" required></div>
        <div class="form-group"><label>Tipo</label><select name="type"><option value="color" <?= $p['type']==='color'?'selected':'' ?>>🎨 Colore</option><option value="bw" <?= $p['type']==='bw'?'selected':'' ?>>⬛ Solo B/N</option></select></div>
        <div class="form-group"><label>Posizione in sede</label><input name="location" value="<?= h($p['location']) ?>"></div>
        <div class="form-group"><label>ADF</label>
          <select name="adf">
            <option value="none"   <?= $p['adf']==='none'?'selected':'' ?>>Nessun ADF</option>
            <option value="simple" <?= $p['adf']==='simple'?'selected':'' ?>>ADF Fronte (solo fronte)</option>
            <option value="duplex" <?= $p['adf']==='duplex'?'selected':'' ?>>ADF Fronte/Retro</option>
          </select>
        </div>
        <div class="form-group"><label>Contatore BN</label><input type="number" name="counter_bw" value="<?= (int)$p['counter_bw'] ?>"></div>
        <div class="form-group"><label>Contatore Colore</label><input type="number" name="counter_color" value="<?= (int)$p['counter_color'] ?>"></div>
        <div class="form-group"><label>Data acquisto</label><input type="date" name="purchase_date" value="<?= h($p['purchase_date']) ?>"></div>
        <div class="form-group"><label>Scadenza garanzia</label><input type="date" name="warranty_exp" value="<?= h($p['warranty_exp']) ?>"></div>
        <div class="form-group form-full" style="display:flex;gap:20px;flex-wrap:wrap;align-items:center">
          <label class="form-check"><input type="checkbox" name="has_duplex" <?= $p['has_duplex']?'checked':'' ?>> Duplex stampa</label>
          <label class="form-check"><input type="checkbox" name="has_scan"   <?= $p['has_scan']?'checked':'' ?>> Scanner di rete</label>
          <label class="form-check"><input type="checkbox" name="rete_lan"   <?= $p['rete_lan']?'checked':'' ?>> 🔌 Rete LAN</label>
          <label class="form-check"><input type="checkbox" name="rete_wifi"  <?= $p['rete_wifi']?'checked':'' ?>> 📶 Rete WiFi</label>
        </div>
        <div class="form-group form-full"><label>Note</label><textarea name="notes"><?= h($p['notes']) ?></textarea></div>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <button type="submit" class="btn btn-primary btn-sm">💾 Salva</button>
        <?php if(is_admin()): ?>
        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('del-printer','Rimuovere questa stampante?')">🗑️ Rimuovi</button>
        <?php endif; ?>
      </div>
    </form>
    <?php if(is_admin()): ?>
    <form method="POST" id="del-printer" style="display:none"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>"></form>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<div class="section-title">Interventi (<?= count($tks) ?>)</div>
<?php foreach($tks as $t): $cls=$t['status']==='closed'?'closed':($t['priority']==='urgent'?'urgent':'open'); ?>
<a href="/techcopy/pages/tickets.php?id=<?= $t['id'] ?>" class="ticket-card <?= $cls ?>" style="display:block;margin-bottom:8px;padding:12px">
  <div class="ticket-top"><span class="ticket-id">#<?= str_pad($t['id'],4,'0',STR_PAD_LEFT) ?></span><span style="flex:1"><?= h($t['title']) ?></span><?= status_badge($t['status']) ?></div>
  <div class="ticket-meta"><span>👤 <?= h($t['tech_name']??'—') ?></span><span>📅 <?= substr($t['created_at'],0,10) ?></span></div>
</a>
<?php endforeach; ?>

<?php else: ?>
<div class="filter-bar">
  <form method="GET" style="display:contents">
    <div class="search-bar"><span>🔍</span><input name="q" value="<?= h($search) ?>" placeholder="Cerca marca, modello, seriale..." oninput="this.form.submit()"></div>
    <?php if(can_manage_printers()): ?><a href="/techcopy/pages/printer_form.php" class="btn btn-primary btn-sm" style="margin-left:auto">➕ Nuova Stampante</a><?php endif; ?>
  </form>
</div>
<div class="table-wrap">
  <table>
    <thead><tr><th>Marca / Modello</th><th>N° Serie</th><th>Cliente</th><th>Tipo</th><th>ADF</th><th>Connettività</th><th>Contatori</th><th></th></tr></thead>
    <tbody>
      <?php foreach($printers as $p): ?>
      <tr>
        <td><strong><?= h($p['brand']) ?></strong><br><span style="color:var(--text2);font-size:12px"><?= h($p['model']) ?></span></td>
        <td style="font-family:var(--mono);font-size:12px"><?= h($p['serial']) ?></td>
        <td><a href="/techcopy/pages/clients.php?id=<?= $p['client_id'] ?>" style="color:var(--accent)"><?= h($p['client_name']) ?></a></td>
        <td><span class="badge badge-<?= $p['type']==='color'?'open':'viewer' ?>"><?= $p['type']==='color'?'🎨 Colore':'⬛ B/N' ?></span></td>
        <td style="font-size:12px"><?= adf_label($p['adf']) ?></td>
        <td style="font-size:12px">
          <?php if($p['rete_lan']): ?><span class="chip" style="margin:1px">🔌 LAN</span><?php endif; ?>
          <?php if($p['rete_wifi']): ?><span class="chip" style="margin:1px">📶 WiFi</span><?php endif; ?>
          <?php if(!$p['rete_lan']&&!$p['rete_wifi']): ?><span style="color:var(--text3)">—</span><?php endif; ?>
        </td>
        <td style="font-family:var(--mono);font-size:12px">BN: <?= number_format($p['counter_bw']) ?><?= $p['counter_color']>0?'<br>Col: '.number_format($p['counter_color']):'' ?></td>
        <td><a href="?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm">Dettagli</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
<?php layout_footer(); ?>
