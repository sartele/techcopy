<?php
require_once __DIR__ . '/../includes/layout.php';
require_role('admin','supervisor');
$db = db();
$clients = $db->query("SELECT id,name FROM clients WHERE active=1 ORDER BY name")->fetchAll();
layout_header('Nuova Stampante','printers');
?>
<div style="max-width:720px">
  <div style="margin-bottom:16px"><a href="/techcopy/pages/printers.php" class="btn btn-secondary btn-sm">← Torna alle stampanti</a></div>
  <div class="table-wrap">
    <div class="table-head"><h4>➕ Nuova Stampante / Fotocopiatore</h4></div>
    <div style="padding:24px">
      <form method="POST" action="/techcopy/pages/printers.php">
      <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="form-grid">
          <div class="form-group form-full"><label>Cliente associato *</label>
            <select name="client_id" required>
              <option value="">— Seleziona cliente —</option>
              <?php foreach($clients as $c): ?><option value="<?= $c['id'] ?>"><?= h($c['name']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Marca *</label><input name="brand" placeholder="Konica Minolta, Ricoh, Canon..." required></div>
          <div class="form-group"><label>Modello *</label><input name="model" placeholder="Es: bizhub C308" required></div>
          <div class="form-group"><label>Numero di serie *</label><input name="serial" placeholder="A7PY000000" required></div>
          <div class="form-group"><label>Tipo</label><select name="type"><option value="color">🎨 Colore</option><option value="bw">⬛ Solo B/N (monocromatico)</option></select></div>
          <div class="form-group"><label>Posizione in sede</label><input name="location" placeholder="Es: Ufficio segreteria, Piano 2..."></div>
          <div class="form-group"><label>Contatore BN di partenza</label><input type="number" name="counter_bw" value="0" min="0"></div>
          <div class="form-group"><label>Contatore Colore di partenza</label><input type="number" name="counter_color" value="0" min="0"></div>
          <div class="form-group"><label>Data acquisto</label><input type="date" name="purchase_date"></div>
          <div class="form-group"><label>Scadenza garanzia</label><input type="date" name="warranty_exp"></div>
          <div class="form-group form-full" style="display:flex;gap:20px;flex-wrap:wrap;align-items:center">
            <label class="form-check"><input type="checkbox" name="has_adf" checked> ADF (alimentatore automatico documenti)</label>
            <label class="form-check"><input type="checkbox" name="has_duplex" checked> Duplex automatico</label>
            <label class="form-check"><input type="checkbox" name="has_fax"> Fax</label>
            <label class="form-check"><input type="checkbox" name="has_scan" checked> Scanner di rete</label>
          </div>
          <div class="form-group form-full"><label>Note</label><textarea name="notes" placeholder="Note aggiuntive sulla macchina..."></textarea></div>
        </div>
        <div class="divider"></div>
        <div style="display:flex;gap:10px">
          <button type="submit" class="btn btn-primary">✅ Aggiungi Stampante</button>
          <a href="/techcopy/pages/printers.php" class="btn btn-secondary">Annulla</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php layout_footer(); ?>
