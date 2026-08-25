<?php
require_once __DIR__ . '/../includes/layout.php';
require_role('admin','supervisor');
layout_header('Nuovo Consumabile','consumables');
?>
<div style="max-width:720px">
  <div style="margin-bottom:16px"><a href="/techcopy/pages/consumables.php" class="btn btn-secondary btn-sm">← Torna ai consumabili</a></div>
  <div class="table-wrap">
    <div class="table-head"><h4>➕ Nuovo Consumabile / Ricambio</h4></div>
    <div style="padding:24px">
      <form method="POST" action="/techcopy/pages/consumables.php">
	  <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="form-grid">
          <div class="form-group form-full"><label>Nome prodotto *</label><input name="name" placeholder="Es: Toner Nero Ricoh IM C3000" required></div>
          <div class="form-group"><label>Codice articolo *</label><input name="code" placeholder="Es: 842312" required></div>
          <div class="form-group"><label>Marca</label><input name="brand" placeholder="Es: Ricoh"></div>
          <div class="form-group"><label>Tipo</label><select name="type"><option>toner</option><option>drum</option><option>carta</option><option>ricambio</option><option>altro</option></select></div>
          <div class="form-group"><label>Colore</label><input name="color" placeholder="black, cyan, magenta, yellow"></div>
          <div class="form-group"><label>Stock iniziale</label><input type="number" name="stock" value="0" min="0"></div>
          <div class="form-group"><label>Stock minimo (soglia allerta)</label><input type="number" name="min_stock" value="2" min="0"></div>
          <div class="form-group"><label>Unità</label><select name="unit"><option value="pz">pz</option><option value="risma">risma</option><option value="kg">kg</option><option value="lt">lt</option></select></div>
          <div class="form-group"><label>Prezzo unitario (€)</label><input type="number" name="price" step="0.01" placeholder="0.00"></div>
          <div class="form-group"><label>Fornitore</label><input name="supplier" placeholder="Nome fornitore"></div>
          <div class="form-group form-full"><label>Note</label><textarea name="notes"></textarea></div>
        </div>
        <div class="divider"></div>
        <button type="submit" class="btn btn-primary">✅ Aggiungi</button>
        <a href="/techcopy/pages/consumables.php" class="btn btn-secondary" style="margin-left:8px">Annulla</a>
      </form>
    </div>
  </div>
</div>
<?php layout_footer(); ?>
