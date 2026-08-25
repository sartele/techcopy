<?php
require_once __DIR__ . '/../includes/layout.php';
require_role('admin','supervisor','tech');
$db = db();
$clients  = $db->query("SELECT id,name FROM clients WHERE active=1 ORDER BY name")->fetchAll();
$techUsers= $db->query("SELECT id,name FROM users WHERE role!='viewer' AND active=1 ORDER BY name")->fetchAll();
$me = current_user();
layout_header('Nuovo Intervento','tickets');
?>
<div style="max-width:720px">
  <div style="margin-bottom:16px">
    <a href="/techcopy/pages/tickets.php" class="btn btn-secondary btn-sm">← Torna agli interventi</a>
  </div>
  <div class="table-wrap">
    <div class="table-head"><h4>➕ Nuovo Intervento</h4></div>
    <div style="padding:24px">
      <form method="POST" action="/techcopy/pages/tickets.php">
      <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="section-title">Cliente e Macchina</div>
        <div class="form-grid">
          <div class="form-group">
            <label>Cliente *</label>
            <select name="client_id" id="sel-client" onchange="loadPrintersByClient(this.value,'sel-printer')" required>
              <option value="">— Seleziona cliente —</option>
              <?php foreach ($clients as $c): ?>
              <option value="<?= $c['id'] ?>"><?= h($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Stampante *</label>
            <select name="printer_id" id="sel-printer" required>
              <option value="">— Prima seleziona il cliente —</option>
            </select>
          </div>
        </div>
        <div class="section-title">Dettagli Intervento</div>
        <div class="form-group">
          <label>Titolo / Problema segnalato *</label>
          <input type="text" name="title" placeholder="Es: Carta inceppata, Stampa sbiadita, Errore E04..." required>
        </div>
        <div class="form-group">
          <label>Descrizione dettagliata</label>
          <textarea name="description" placeholder="Descrivi il problema segnalato dal cliente, codici errore, sintomi..."></textarea>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label>Tipo intervento</label>
            <select name="type">
              <option>guasto</option><option>manutenzione</option><option>errore</option><option>installazione</option><option>consulenza</option>
            </select>
          </div>
          <div class="form-group">
            <label>Priorità</label>
            <select name="priority">
              <option value="normal">Normale</option>
              <option value="high">Alta</option>
              <option value="urgent">🔴 Urgente</option>
            </select>
          </div>
          <div class="form-group">
            <label>Tecnico assegnato</label>
            <select name="tech_id">
              <option value="">— Nessuno —</option>
              <?php foreach ($techUsers as $u): ?>
              <option value="<?= $u['id'] ?>" <?= $u['id']==$me['id']?'selected':'' ?>><?= h($u['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="section-title">Tempi e Contatori (opzionale)</div>
        <div class="form-grid">
          <div class="form-group">
            <label>Tempo spostamento (min)</label>
            <input type="number" name="travel_time" value="0" min="0">
          </div>
          <div class="form-group">
            <label>Tempo lavoro (min)</label>
            <input type="number" name="work_time" value="0" min="0">
          </div>
          <div class="form-group">
            <label>Contatore BN al momento intervento</label>
            <input type="number" name="counter_bw" value="0" min="0">
          </div>
          <div class="form-group">
            <label>Contatore Colore al momento intervento</label>
            <input type="number" name="counter_color" value="0" min="0">
          </div>
        </div>
        <div class="divider"></div>
        <div style="display:flex;gap:10px">
          <button type="submit" class="btn btn-primary">✅ Crea Intervento</button>
          <a href="/techcopy/pages/tickets.php" class="btn btn-secondary">Annulla</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php layout_footer(); ?>
