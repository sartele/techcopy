<?php
require_once __DIR__ . '/../includes/layout.php';
require_login();
$db = db();

// ── AZIONI POST ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && can_manage_consumables()) {
	csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $db->prepare("INSERT INTO consumables (name,code,brand,type,color,stock,min_stock,unit,price,supplier,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
           ->execute([
               trim($_POST['name']), trim($_POST['code']), trim($_POST['brand']),
               $_POST['type'], trim($_POST['color']),
               (int)$_POST['stock'], (int)$_POST['min_stock'], $_POST['unit'],
               $_POST['price'] ? (float)$_POST['price'] : null,
               trim($_POST['supplier']), trim($_POST['notes']),
           ]);
        flash('Consumabile aggiunto ✅');
        redirect('/techcopy/pages/consumables.php');
    }

    if ($action === 'update') {
        $id = (int)$_POST['id'];
        $db->prepare("UPDATE consumables SET name=?,code=?,brand=?,type=?,color=?,min_stock=?,unit=?,price=?,supplier=?,notes=? WHERE id=?")
           ->execute([
               trim($_POST['name']), trim($_POST['code']), trim($_POST['brand']),
               $_POST['type'], trim($_POST['color']),
               (int)$_POST['min_stock'], $_POST['unit'],
               $_POST['price'] ? (float)$_POST['price'] : null,
               trim($_POST['supplier']), trim($_POST['notes']),
               $id,
           ]);
        // Lo stock NON si modifica qui: si usa adjust o gli interventi
        flash('Consumabile aggiornato ✅');
        redirect('/techcopy/pages/consumables.php');
    }

    if ($action === 'adjust') {
        $id    = (int)$_POST['id'];
        // Quantità: campo libero o ±1 dai bottoni rapidi
        $delta = isset($_POST['qty_manual']) && $_POST['qty_manual'] !== ''
                 ? (int)$_POST['qty_manual']
                 : (int)$_POST['delta'];
        if ($delta !== 0) {
            $db->prepare("UPDATE consumables SET stock=GREATEST(0,stock+?) WHERE id=?")->execute([$delta, $id]);
            $type = $delta > 0 ? 'carico' : 'scarico';
            $db->prepare("INSERT INTO stock_movements (consumable_id,user_id,type,quantity,notes) VALUES (?,?,?,?,?)")
               ->execute([$id, current_user()['id'], $type, abs($delta), trim($_POST['mov_notes'] ?? '')]);
            flash('Stock aggiornato');
        }
        redirect('/techcopy/pages/consumables.php');
    }

    if ($action === 'delete' && is_admin()) {
        $id = (int)$_POST['id'];
        $db->prepare("DELETE FROM consumables WHERE id=?")->execute([$id]);
        flash('Consumabile eliminato.');
        redirect('/techcopy/pages/consumables.php');
    }
}

// ── QUERY ────────────────────────────────────────────────────
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['q']      ?? '';
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

$where = '1=1'; $params = [];
if ($filter === 'low') { $where .= " AND stock <= min_stock"; }
if ($filter === 'out') { $where .= " AND stock = 0"; }
if ($search) {
    $where .= " AND (name LIKE ? OR code LIKE ? OR brand LIKE ? OR supplier LIKE ?)";
    $params = array_fill(0, 4, "%$search%");
}
$stmt = $db->prepare("SELECT * FROM consumables WHERE $where ORDER BY CASE WHEN stock=0 THEN 0 WHEN stock<=min_stock THEN 1 ELSE 2 END, type, name");
$stmt->execute($params);
$consumables = $stmt->fetchAll();

$lowCount = $db->query("SELECT COUNT(*) FROM consumables WHERE stock <= min_stock")->fetchColumn();
$outCount = $db->query("SELECT COUNT(*) FROM consumables WHERE stock = 0")->fetchColumn();

// Carica il consumabile da modificare
$editCons = null;
if ($editId) {
    $s = $db->prepare("SELECT * FROM consumables WHERE id=?");
    $s->execute([$editId]);
    $editCons = $s->fetch();
}

layout_header('Consumabili', 'consumables');
?>

<?php if ($lowCount > 0): ?>
<div class="alert alert-warning">⚠️ <?= $lowCount ?> articoli sotto la soglia minima — <?= $outCount ?> esauriti.</div>
<?php endif; ?>

<!-- ── FILTRI E RICERCA ── -->
<div class="filter-bar">
  <form method="GET" style="display:contents">
    <a href="?filter=all" class="btn btn-sm <?= $filter==='all'?'btn-primary':'btn-secondary' ?>">Tutti</a>
    <a href="?filter=low" class="btn btn-sm <?= $filter==='low'?'btn-primary':'btn-secondary' ?>">⚠️ Stock basso (<?= $lowCount ?>)</a>
    <a href="?filter=out" class="btn btn-sm <?= $filter==='out'?'btn-primary':'btn-secondary' ?>">🔴 Esauriti (<?= $outCount ?>)</a>
    <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
      <div class="search-bar">
        <span>🔍</span>
        <input name="q" value="<?= h($search) ?>" placeholder="Cerca nome, codice, marca..." oninput="this.form.submit()">
        <input type="hidden" name="filter" value="<?= h($filter) ?>">
      </div>
      <?php if (can_manage_consumables()): ?>
      <a href="/techcopy/pages/consumable_form.php" class="btn btn-primary btn-sm">➕ Nuovo consumabile</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- ── PANNELLO MODIFICA INLINE ── -->
<?php if ($editCons && can_manage_consumables()): ?>
<div class="table-wrap" style="margin-bottom:24px;padding:0;border-color:var(--accent)">
  <div class="table-head" style="border-bottom-color:var(--accent)">
    <h4 style="color:var(--accent)">✏️ Modifica: <?= h($editCons['name']) ?></h4>
    <a href="/techcopy/pages/consumables.php?filter=<?= h($filter) ?>&q=<?= h($search) ?>" class="btn btn-secondary btn-sm">✕ Annulla</a>
  </div>
  <div style="padding:24px">
    <form method="POST">
	  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" value="<?= $editCons['id'] ?>">
      <div class="form-grid">
        <div class="form-group form-full">
          <label>Nome prodotto *</label>
          <input name="name" value="<?= h($editCons['name']) ?>" required>
        </div>
        <div class="form-group">
          <label>Codice articolo *</label>
          <input name="code" value="<?= h($editCons['code']) ?>" required>
        </div>
        <div class="form-group">
          <label>Marca</label>
          <input name="brand" value="<?= h($editCons['brand']) ?>">
        </div>
        <div class="form-group">
          <label>Tipo</label>
          <select name="type">
            <?php foreach (['toner','drum','carta','ricambio','altro'] as $tp): ?>
            <option value="<?= $tp ?>" <?= $editCons['type']===$tp?'selected':'' ?>><?= $tp ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Colore</label>
          <input name="color" value="<?= h($editCons['color']) ?>" placeholder="black, cyan, magenta, yellow">
        </div>
        <div class="form-group">
          <label>Stock minimo (soglia allerta)</label>
          <input type="number" name="min_stock" value="<?= (int)$editCons['min_stock'] ?>" min="0">
        </div>
        <div class="form-group">
          <label>Unità di misura</label>
          <select name="unit">
            <option value="pz"    <?= $editCons['unit']==='pz'   ?'selected':'' ?>>pz</option>
            <option value="risma" <?= $editCons['unit']==='risma'?'selected':'' ?>>risma</option>
            <option value="kg"    <?= $editCons['unit']==='kg'   ?'selected':'' ?>>kg</option>
            <option value="lt"    <?= $editCons['unit']==='lt'   ?'selected':'' ?>>lt</option>
          </select>
        </div>
        <div class="form-group">
          <label>Prezzo unitario (€)</label>
          <input type="number" name="price" step="0.01" value="<?= h($editCons['price']) ?>" placeholder="0.00">
        </div>
        <div class="form-group">
          <label>Fornitore</label>
          <input name="supplier" value="<?= h($editCons['supplier']) ?>">
        </div>
        <div class="form-group form-full">
          <label>Note</label>
          <textarea name="notes"><?= h($editCons['notes']) ?></textarea>
        </div>
      </div>
      <div class="alert alert-info" style="margin-bottom:16px">
        ℹ️ Stock attuale: <strong><?= $editCons['stock'] ?> <?= h($editCons['unit']) ?></strong> —
        non modificabile qui. Usa i pulsanti <strong>−1 / +1</strong> o il campo <strong>Rettifica</strong> nella tabella.
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <button type="submit" class="btn btn-primary">💾 Salva modifiche</button>
        <a href="/techcopy/pages/consumables.php?filter=<?= h($filter) ?>&q=<?= h($search) ?>" class="btn btn-secondary">Annulla</a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- ── TABELLA ── -->
<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>Prodotto</th>
        <th>Codice</th>
        <th>Tipo</th>
        <th>Marca / Fornitore</th>
        <th>Stock</th>
        <th>Min.</th>
        <th>Livello</th>
        <?php if (can_manage_consumables()): ?>
        <th>Rettifica stock</th>
        <th>Azioni</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($consumables as $c):
        $pct      = $c['min_stock'] > 0 ? min(100, ($c['stock'] / ($c['min_stock'] * 2)) * 100) : 50;
        $color    = $c['stock'] == 0 ? 'var(--red)' : ($c['stock'] <= $c['min_stock'] ? 'var(--orange)' : 'var(--green)');
        $isEditing = ($editId === (int)$c['id']);
      ?>
      <tr style="<?= $isEditing ? 'background:var(--accent-dim);' : '' ?>">
        <td>
          <div style="font-weight:600"><?= h($c['name']) ?></div>
          <?php if ($c['notes']): ?>
          <div style="font-size:11px;color:var(--text3);margin-top:2px"><?= h(mb_substr($c['notes'],0,70)).(mb_strlen($c['notes'])>70?'…':'') ?></div>
          <?php endif; ?>
        </td>
        <td style="font-family:var(--mono);font-size:12px"><?= h($c['code']) ?></td>
        <td>
          <span class="chip"><?= h($c['type']) ?></span>
          <?php if ($c['color']): ?><br><span style="font-size:11px;color:var(--text3)"><?= h($c['color']) ?></span><?php endif; ?>
        </td>
        <td style="font-size:13px">
          <?= h($c['brand'] ?: '—') ?>
          <?php if ($c['supplier']): ?><br><span style="font-size:11px;color:var(--text3)">🏭 <?= h($c['supplier']) ?></span><?php endif; ?>
          <?php if ($c['price']): ?><br><span style="font-size:11px;color:var(--text2);font-family:var(--mono)">€ <?= number_format((float)$c['price'],2) ?></span><?php endif; ?>
        </td>
        <td id="stock-<?= $c['id'] ?>" style="font-family:var(--mono);font-weight:600;color:<?= $color ?>"><?= $c['stock'] ?> <?= h($c['unit']) ?></td>
        <td style="font-family:var(--mono);font-size:12px;color:var(--text2)"><?= $c['min_stock'] ?></td>
        <td style="min-width:90px">
          <div class="stock-bar">
            <div class="stock-fill" id="fill-<?= $c['id'] ?>" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
          </div>
        </td>

        <?php if (can_manage_consumables()): ?>
        <!-- Rettifica stock -->
        <td>
          <div style="display:flex;align-items:center;gap:4px;flex-wrap:wrap">
            <!-- -1 rapido -->
            <form method="POST" style="display:contents">
			  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
              <input type="hidden" name="action" value="adjust">
              <input type="hidden" name="id"     value="<?= $c['id'] ?>">
              <input type="hidden" name="delta"  value="-1">
              <button type="submit" class="btn btn-danger btn-sm"
                      <?= $c['stock']==0?'disabled':'' ?> title="Togli 1">−1</button>
            </form>
            <!-- Quantità libera + causale -->
            <form method="POST" style="display:contents">
			  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
              <input type="hidden" name="action" value="adjust">
              <input type="hidden" name="id"     value="<?= $c['id'] ?>">
              <input type="hidden" name="delta"  value="0">
              <input type="number" name="qty_manual"
                     placeholder="±Qtà" min="-9999" max="9999"
                     style="width:62px;background:var(--bg);border:1px solid var(--border);border-radius:var(--radius);padding:5px 6px;color:var(--text);font-size:12px;font-family:var(--mono);text-align:center"
                     title="Numero positivo = carico, negativo = scarico">
              <input type="text" name="mov_notes"
                     placeholder="Causale"
                     style="width:100px;background:var(--bg);border:1px solid var(--border);border-radius:var(--radius);padding:5px 8px;color:var(--text);font-size:12px;font-family:var(--sans)"
                     title="Causale del movimento (opzionale)">
              <button type="submit" class="btn btn-secondary btn-sm" title="Applica">✓</button>
            </form>
            <!-- +1 rapido -->
            <form method="POST" style="display:contents">
			  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
              <input type="hidden" name="action" value="adjust">
              <input type="hidden" name="id"     value="<?= $c['id'] ?>">
              <input type="hidden" name="delta"  value="1">
              <button type="submit" class="btn btn-success btn-sm" title="Aggiungi 1">+1</button>
            </form>
          </div>
        </td>

        <!-- Modifica / Elimina -->
        <td>
          <div class="td-actions">
            <a href="?edit=<?= $c['id'] ?>&filter=<?= h($filter) ?>&q=<?= h($search) ?>"
               class="btn btn-secondary btn-sm"
               style="<?= $isEditing?'background:var(--accent-dim);border-color:var(--accent);color:var(--accent)':'' ?>"
               title="Modifica dati consumabile">✏️ Modifica</a>
            <?php if (is_admin()): ?>
            <button type="button" class="btn btn-danger btn-sm"
                    onclick="confirmDelete('del-cons-<?= $c['id'] ?>','Eliminare «<?= h(addslashes($c['name'])) ?>»?\nAttenzione: verrà rimosso dagli interventi collegati.')"
                    title="Elimina">🗑️</button>
            <form method="POST" id="del-cons-<?= $c['id'] ?>" style="display:none">
			  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id"     value="<?= $c['id'] ?>">
            </form>
            <?php endif; ?>
          </div>
        </td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>

      <?php if (empty($consumables)): ?>
      <tr>
        <td colspan="9" style="text-align:center;padding:40px;color:var(--text2)">
          Nessun consumabile trovato.
        </td>
      </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php layout_footer(); ?>