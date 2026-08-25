<?php
require_once __DIR__ . '/../includes/layout.php';
require_role('admin','supervisor');
layout_header('Nuovo Cliente','clients');
?>
<div style="max-width:720px">
  <div style="margin-bottom:16px"><a href="/techcopy/pages/clients.php" class="btn btn-secondary btn-sm">← Torna ai clienti</a></div>
  <div class="table-wrap">
    <div class="table-head"><h4>➕ Nuovo Cliente</h4></div>
    <div style="padding:24px">
      <form method="POST" action="/techcopy/pages/clients.php">
      <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="form-grid">
          <div class="form-group form-full"><label>Ragione Sociale *</label><input name="name" required placeholder="Es: Studio Rossi Srl"></div>
          <div class="form-group"><label>Referente</label><input name="contact" placeholder="Nome cognome"></div>
          <div class="form-group"><label>Telefono</label><input name="phone" placeholder="02-XXXXXXXX"></div>
          <div class="form-group"><label>Email</label><input name="email" type="email" placeholder="info@azienda.it"></div>
          <div class="form-group form-full"><label>Indirizzo (via e numero)</label><input name="address" placeholder="Via Roma 1"></div>
          <div class="form-group"><label>Città</label><input name="city" placeholder="Milano"></div>
          <div class="form-group"><label>CAP</label><input name="zip" placeholder="20100"></div>
          <div class="form-group"><label>Latitudine GPS</label><input name="lat" type="number" step="0.0001" placeholder="45.4654"></div>
          <div class="form-group"><label>Longitudine GPS</label><input name="lng" type="number" step="0.0001" placeholder="9.1859"></div>
          <div class="form-group form-full"><label>Note (orari, accesso, ecc.)</label><textarea name="notes" placeholder="Es: Ufficio al 3° piano, orari 9-18..."></textarea></div>
        </div>
        <div class="divider"></div>
        <div style="display:flex;gap:10px">
          <button type="submit" class="btn btn-primary">✅ Crea Cliente</button>
          <a href="/techcopy/pages/clients.php" class="btn btn-secondary">Annulla</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php layout_footer(); ?>
