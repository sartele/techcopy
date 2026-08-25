<?php
// ============================================================
//  TechCopy — Report Cumulativi
//  Accessibile a tutti i ruoli; tecnico vede solo i propri dati
// ============================================================
require_once __DIR__ . '/../includes/layout.php';
require_login();
$db = db();
$me = current_user();

// ── PARAMETRI FILTRO ─────────────────────────────────────────
$selYear   = (int)($_GET['year']   ?? date('Y'));
$selMonth  = (int)($_GET['month']  ?? 0);           // 0 = tutti i mesi
$selUser   = (int)($_GET['user_id'] ?? 0);           // 0 = tutti gli utenti
$selStatus = $_GET['status'] ?? 'all';
$viewMode  = $_GET['view']   ?? 'monthly';           // monthly | user | type

// Il tecnico può vedere solo sé stesso
if ($me['role'] === 'tech') $selUser = (int)$me['id'];

// ── ANNI DISPONIBILI ─────────────────────────────────────────
$years = $db->query("SELECT DISTINCT YEAR(created_at) AS y FROM tickets ORDER BY y DESC")->fetchAll(PDO::FETCH_COLUMN);
if (empty($years)) $years = [date('Y')];

// ── UTENTI DISPONIBILI ────────────────────────────────────────
$allUsers = ($me['role'] !== 'tech')
    ? $db->query("SELECT id,name,role,color,avatar FROM users WHERE active=1 AND role IN ('admin','supervisor','tech') ORDER BY FIELD(role,'admin','supervisor','tech'),name")->fetchAll()
    : [];

// ── COSTRUZIONE WHERE ─────────────────────────────────────────
$where  = "YEAR(t.created_at) = ?";
$params = [$selYear];

if ($selMonth > 0) {
    $where .= " AND MONTH(t.created_at) = ?";
    $params[] = $selMonth;
}
if ($selUser > 0) {
    $where .= " AND (t.tech_id = ? OR EXISTS(SELECT 1 FROM ticket_users tu WHERE tu.ticket_id=t.id AND tu.user_id=?))";
    $params[] = $selUser; $params[] = $selUser;
}
if ($selStatus !== 'all') {
    $where .= " AND t.status = ?";
    $params[] = $selStatus;
}

// ── KPI GLOBALI ───────────────────────────────────────────────
$kpiStmt = $db->prepare("
    SELECT
        COUNT(*)                                    AS total,
        SUM(t.status='closed')                      AS closed,
        SUM(t.status='open')                        AS open,
        SUM(t.status='pending')                     AS pending,
        SUM(t.resolved=1)                           AS resolved,
        SUM(t.travel_time)                          AS total_travel,
        SUM(t.work_time)                            AS total_work,
        AVG(NULLIF(t.work_time,0))                  AS avg_work,
        SUM(t.type='guasto')                        AS cnt_guasto,
        SUM(t.type='manutenzione')                  AS cnt_manut,
        SUM(t.type='errore')                        AS cnt_errore,
        SUM(t.type='installazione')                 AS cnt_install,
        SUM(t.type='consulenza')                    AS cnt_consul
    FROM tickets t WHERE $where");
$kpiStmt->execute($params); $kpi = $kpiStmt->fetch();

// ── DATI PER VISTA MENSILE ────────────────────────────────────
$monthlyStmt = $db->prepare("
    SELECT
        MONTH(t.created_at)     AS m,
        MONTHNAME(t.created_at) AS mname,
        COUNT(*)                AS total,
        SUM(t.status='closed')  AS closed,
        SUM(t.status='open')    AS open,
        SUM(t.resolved=1)       AS resolved,
        SUM(t.travel_time)      AS travel,
        SUM(t.work_time)        AS work,
        AVG(NULLIF(t.work_time,0)) AS avg_work
    FROM tickets t WHERE $where
    GROUP BY MONTH(t.created_at), MONTHNAME(t.created_at)
    ORDER BY m ASC");
$monthlyStmt->execute($params); $monthly = $monthlyStmt->fetchAll();

// ── DATI PER VISTA PER UTENTE ─────────────────────────────────
// (usa ticket_users per contare correttamente ticket multi-tecnico)
$userParams = $params;
$userWhere  = $where;
$userStmt = $db->prepare("
    SELECT
        u.id, u.name, u.avatar, u.color, u.role,
        COUNT(DISTINCT t.id)         AS total,
        SUM(t.status='closed')       AS closed,
        SUM(t.status='open')         AS open,
        SUM(t.resolved=1)            AS resolved,
        SUM(t.travel_time)           AS travel,
        SUM(t.work_time)             AS work,
        AVG(NULLIF(t.work_time,0))   AS avg_work
    FROM tickets t
    JOIN ticket_users tu ON tu.ticket_id = t.id
    JOIN users u         ON u.id = tu.user_id
    WHERE $userWhere
    GROUP BY u.id, u.name, u.avatar, u.color, u.role
    ORDER BY total DESC");
$userStmt->execute($userParams); $byUser = $userStmt->fetchAll();

// ── DATI PER TIPO ─────────────────────────────────────────────
$typeStmt = $db->prepare("
    SELECT
        t.type,
        COUNT(*)                AS total,
        SUM(t.status='closed')  AS closed,
        SUM(t.resolved=1)       AS resolved,
        SUM(t.work_time)        AS work
    FROM tickets t WHERE $where
    GROUP BY t.type ORDER BY total DESC");
$typeStmt->execute($params); $byType = $typeStmt->fetchAll();

// ── LISTA TICKET DETTAGLIO ────────────────────────────────────
$listStmt = $db->prepare("
    SELECT t.id, t.title, t.type, t.status, t.priority, t.created_at, t.closed_at,
           t.travel_time, t.work_time, t.resolved,
           c.name AS client_name,
           p.brand, p.model,
           u.name AS tech_name, u.color AS tech_color
    FROM tickets t
    LEFT JOIN clients  c ON c.id = t.client_id
    LEFT JOIN printers p ON p.id = t.printer_id
    LEFT JOIN users    u ON u.id = t.tech_id
    WHERE $where
    ORDER BY t.created_at DESC
    LIMIT 200");
$listStmt->execute($params); $ticketList = $listStmt->fetchAll();

$mesi = ['','Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'];
$roleLabel = ['admin'=>'Admin','supervisor'=>'Supervisore','tech'=>'Tecnico'];
$typeEmoji = ['guasto'=>'🔴','manutenzione'=>'🔵','errore'=>'🟠','installazione'=>'🟢','consulenza'=>'⚪'];

layout_header('Report', 'reports');
?>

<!-- ── FILTRI ── -->
<div class="table-wrap" style="padding:0;margin-bottom:20px">
  <div class="table-head"><h4>🔍 Filtri report</h4></div>
  <div style="padding:16px 20px">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
      <div class="form-group" style="margin:0">
        <label>Anno</label>
        <select name="year" onchange="this.form.submit()">
          <?php foreach($years as $y): ?>
          <option value="<?= $y ?>" <?= $selYear==$y?'selected':'' ?>><?= $y ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" style="margin:0">
        <label>Mese</label>
        <select name="month" onchange="this.form.submit()">
          <option value="0" <?= $selMonth==0?'selected':'' ?>>Tutti i mesi</option>
          <?php for($m=1;$m<=12;$m++): ?>
          <option value="<?= $m ?>" <?= $selMonth==$m?'selected':'' ?>><?= $mesi[$m] ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <?php if ($me['role'] !== 'tech'): ?>
      <div class="form-group" style="margin:0">
        <label>Utente</label>
        <select name="user_id" onchange="this.form.submit()">
          <option value="0" <?= $selUser==0?'selected':'' ?>>Tutti</option>
          <?php foreach($allUsers as $u): ?>
          <option value="<?= $u['id'] ?>" <?= $selUser==$u['id']?'selected':'' ?>><?= h($u['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="form-group" style="margin:0">
        <label>Stato</label>
        <select name="status" onchange="this.form.submit()">
          <option value="all"     <?= $selStatus==='all'?'selected':'' ?>>Tutti</option>
          <option value="closed"  <?= $selStatus==='closed'?'selected':'' ?>>Chiusi</option>
          <option value="open"    <?= $selStatus==='open'?'selected':'' ?>>Aperti</option>
          <option value="pending" <?= $selStatus==='pending'?'selected':'' ?>>In attesa</option>
        </select>
      </div>
      <div class="form-group" style="margin:0">
        <label>Vista</label>
        <select name="view" onchange="this.form.submit()">
          <option value="monthly" <?= $viewMode==='monthly'?'selected':'' ?>>📅 Per mese</option>
          <option value="user"    <?= $viewMode==='user'?'selected':'' ?>>👥 Per utente</option>
          <option value="type"    <?= $viewMode==='type'?'selected':'' ?>>🏷️ Per tipo</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary btn-sm">Applica</button>
      <a href="?year=<?= $selYear ?>&month=<?= $selMonth ?>&user_id=<?= $selUser ?>&status=<?= h($selStatus) ?>&view=<?= h($viewMode) ?>&print=1"
         target="_blank" class="btn btn-secondary btn-sm">🖨️ Stampa</a>
    </form>
  </div>
</div>

<!-- ── KPI ── -->
<div class="stats-grid" style="margin-bottom:20px">
  <div class="stat-card" style="border-top:2px solid var(--accent)">
    <div class="stat-icon">📋</div>
    <div class="value" style="color:var(--accent)"><?= $kpi['total'] ?></div>
    <div class="label">Interventi totali</div>
    <div class="trend" style="color:var(--text2)"><?= $selMonth>0 ? $mesi[$selMonth] : 'Anno '.$selYear ?></div>
  </div>
  <div class="stat-card" style="border-top:2px solid var(--green)">
    <div class="stat-icon">✅</div>
    <div class="value" style="color:var(--green)"><?= $kpi['closed'] ?></div>
    <div class="label">Chiusi / Risolti</div>
    <div class="trend" style="color:var(--green)"><?= $kpi['resolved'] ?> confermati risolti</div>
  </div>
  <div class="stat-card" style="border-top:2px solid var(--orange)">
    <div class="stat-icon">⏱️</div>
    <div class="value" style="color:var(--orange)"><?= format_minutes((int)$kpi['total_work']) ?></div>
    <div class="label">Ore lavoro totali</div>
    <div class="trend" style="color:var(--text2)">Media: <?= format_minutes((int)round($kpi['avg_work']??0)) ?>/intervento</div>
  </div>
  <div class="stat-card" style="border-top:2px solid var(--purple)">
    <div class="stat-icon">🚗</div>
    <div class="value" style="color:var(--purple)"><?= format_minutes((int)$kpi['total_travel']) ?></div>
    <div class="label">Ore spostamento</div>
    <div class="trend" style="color:var(--text2)">Totale: <?= format_minutes((int)$kpi['total_travel']+(int)$kpi['total_work']) ?></div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:20px;margin-bottom:20px">

  <!-- ── TABELLA PRINCIPALE (mensile / per utente / per tipo) ── -->
  <div>
  <?php if ($viewMode === 'monthly'): ?>
    <div class="table-wrap">
      <div class="table-head"><h4>📅 Andamento mensile — <?= $selYear ?></h4></div>
      <table>
        <thead><tr><th>Mese</th><th>Totale</th><th>Chiusi</th><th>Aperti</th><th>Lavoro</th><th>Media</th><th>Spost.</th></tr></thead>
        <tbody>
          <?php if (empty($monthly)): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--text3);padding:24px">Nessun dato per questo periodo</td></tr>
          <?php endif; ?>
          <?php foreach($monthly as $row): ?>
          <tr>
            <td><strong><?= h($mesi[(int)$row['m']]) ?></strong></td>
            <td style="font-family:var(--mono);font-weight:600"><?= $row['total'] ?></td>
            <td><span class="badge badge-closed"><?= $row['closed'] ?></span></td>
            <td><span class="badge badge-open"><?= $row['open'] ?></span></td>
            <td style="font-family:var(--mono)"><?= format_minutes((int)$row['work']) ?></td>
            <td style="font-family:var(--mono);color:var(--text2)"><?= format_minutes((int)round($row['avg_work']??0)) ?></td>
            <td style="font-family:var(--mono);color:var(--text2)"><?= format_minutes((int)$row['travel']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <?php if(!empty($monthly)): ?>
        <tfoot>
          <tr style="background:var(--surface2)">
            <td><strong>TOTALE</strong></td>
            <td style="font-family:var(--mono);font-weight:700;color:var(--accent)"><?= $kpi['total'] ?></td>
            <td><span class="badge badge-closed"><?= $kpi['closed'] ?></span></td>
            <td><span class="badge badge-open"><?= $kpi['open'] ?></span></td>
            <td style="font-family:var(--mono);font-weight:600"><?= format_minutes((int)$kpi['total_work']) ?></td>
            <td style="font-family:var(--mono);color:var(--text2)"><?= format_minutes((int)round($kpi['avg_work']??0)) ?></td>
            <td style="font-family:var(--mono);color:var(--text2)"><?= format_minutes((int)$kpi['total_travel']) ?></td>
          </tr>
        </tfoot>
        <?php endif; ?>
      </table>
    </div>

  <?php elseif ($viewMode === 'user'): ?>
    <div class="table-wrap">
      <div class="table-head"><h4>👥 Per tecnico/supervisore — <?= $selYear ?><?= $selMonth>0?' - '.$mesi[$selMonth]:'' ?></h4></div>
      <table>
        <thead><tr><th>Utente</th><th>Ruolo</th><th>Tot.</th><th>Chiusi</th><th>Risolti</th><th>Lavoro</th><th>Media</th></tr></thead>
        <tbody>
          <?php if(empty($byUser)): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--text3);padding:24px">Nessun dato</td></tr>
          <?php endif; ?>
          <?php foreach($byUser as $u): ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <div style="width:28px;height:28px;border-radius:50%;background:<?= h($u['color']) ?>22;color:<?= h($u['color']) ?>;border:1.5px solid <?= h($u['color']) ?>;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0"><?= h($u['avatar']) ?></div>
                <strong><?= h($u['name']) ?></strong>
              </div>
            </td>
            <td><span class="badge badge-<?= h($u['role']) ?>"><?= h($roleLabel[$u['role']]??$u['role']) ?></span></td>
            <td style="font-family:var(--mono);font-weight:600"><?= $u['total'] ?></td>
            <td><span class="badge badge-closed"><?= $u['closed'] ?></span></td>
            <td><span class="badge badge-viewer"><?= $u['resolved'] ?></span></td>
            <td style="font-family:var(--mono)"><?= format_minutes((int)$u['work']) ?></td>
            <td style="font-family:var(--mono);color:var(--text2)"><?= format_minutes((int)round($u['avg_work']??0)) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  <?php elseif ($viewMode === 'type'): ?>
    <div class="table-wrap">
      <div class="table-head"><h4>🏷️ Per tipo di intervento — <?= $selYear ?><?= $selMonth>0?' - '.$mesi[$selMonth]:'' ?></h4></div>
      <table>
        <thead><tr><th>Tipo</th><th>Totale</th><th>Chiusi</th><th>Risolti</th><th>Ore lavoro</th><th>%</th></tr></thead>
        <tbody>
          <?php foreach($byType as $row): ?>
          <tr>
            <td><strong><?= ($typeEmoji[$row['type']]??'•').' '.h(ucfirst($row['type'])) ?></strong></td>
            <td style="font-family:var(--mono);font-weight:600"><?= $row['total'] ?></td>
            <td><span class="badge badge-closed"><?= $row['closed'] ?></span></td>
            <td><span class="badge badge-viewer"><?= $row['resolved'] ?></span></td>
            <td style="font-family:var(--mono)"><?= format_minutes((int)$row['work']) ?></td>
            <td style="font-family:var(--mono);color:var(--text2)">
              <?= $kpi['total']>0 ? round($row['total']/$kpi['total']*100).'%' : '—' ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
  </div>

  <!-- ── BREAKDOWN TIPI (barra orizzontale) ── -->
  <div>
    <div class="table-wrap">
      <div class="table-head"><h4>📊 Distribuzione per tipo</h4></div>
      <div style="padding:16px">
        <?php
        $typeColors = ['guasto'=>'var(--red)','manutenzione'=>'var(--accent)','errore'=>'var(--orange)','installazione'=>'var(--green)','consulenza'=>'var(--purple)'];
        $types = [
            ['guasto',        $kpi['cnt_guasto'],  'Guasto'],
            ['manutenzione',  $kpi['cnt_manut'],   'Manutenzione'],
            ['errore',        $kpi['cnt_errore'],  'Errore'],
            ['installazione', $kpi['cnt_install'], 'Installazione'],
            ['consulenza',    $kpi['cnt_consul'],  'Consulenza'],
        ];
        foreach ($types as [$key, $cnt, $label]):
            $pct = $kpi['total']>0 ? round($cnt/$kpi['total']*100) : 0;
            $col = $typeColors[$key];
        ?>
        <div style="margin-bottom:14px">
          <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
            <span><?= ($typeEmoji[$key]??'•').' '.$label ?></span>
            <span style="font-family:var(--mono);font-weight:600"><?= $cnt ?> <span style="color:var(--text3)">(<?= $pct ?>%)</span></span>
          </div>
          <div style="height:8px;background:var(--surface3);border-radius:4px;overflow:hidden">
            <div style="height:100%;width:<?= $pct ?>%;background:<?= $col ?>;border-radius:4px;transition:width .4s"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Riepilogo stato -->
    <div class="table-wrap">
      <div class="table-head"><h4>📈 Riepilogo stato</h4></div>
      <div style="padding:16px">
        <?php
        $stateData = [
            ['Chiusi',    $kpi['closed'],  'var(--green)',  '🟢'],
            ['Aperti',    $kpi['open'],    'var(--orange)', '🟠'],
            ['In attesa', $kpi['pending'], 'var(--yellow)', '🟡'],
        ];
        foreach ($stateData as [$lbl, $cnt, $col, $ico]):
            $pct = $kpi['total']>0 ? round($cnt/$kpi['total']*100) : 0;
        ?>
        <div style="margin-bottom:12px">
          <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
            <span><?= $ico.' '.$lbl ?></span>
            <span style="font-family:var(--mono);font-weight:600"><?= $cnt ?> <span style="color:var(--text3)">(<?= $pct ?>%)</span></span>
          </div>
          <div style="height:6px;background:var(--surface3);border-radius:3px;overflow:hidden">
            <div style="height:100%;width:<?= $pct ?>%;background:<?= $col ?>;border-radius:3px"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- ── LISTA INTERVENTI ── -->
<div class="table-wrap">
  <div class="table-head">
    <h4>📋 Lista interventi (<?= count($ticketList) ?>)</h4>
    <?php if(count($ticketList)==200): ?><span class="badge badge-pending">Mostra max 200</span><?php endif; ?>
  </div>
  <table>
    <thead>
      <tr>
        <th>#</th><th>Titolo</th><th>Cliente</th><th>Tipo</th>
        <th>Tecnico</th><th>Stato</th><th>Aperto</th>
        <th>Lavoro</th><th>Report</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($ticketList as $t): ?>
      <tr>
        <td style="font-family:var(--mono);color:var(--accent)">#<?= str_pad($t['id'],4,'0',STR_PAD_LEFT) ?></td>
        <td style="max-width:200px"><a href="/techcopy/pages/tickets.php?id=<?= $t['id'] ?>" style="color:var(--text)"><?= h(mb_substr($t['title'],0,45)).(mb_strlen($t['title'])>45?'…':'') ?></a></td>
        <td style="font-size:12px"><?= h($t['client_name']) ?></td>
        <td><?= ($typeEmoji[$t['type']]??'•').' '.h($t['type']) ?></td>
        <td style="font-size:12px;color:<?= h($t['tech_color']??'var(--text2)') ?>"><?= h($t['tech_name']??'—') ?></td>
        <td><?= status_badge($t['status']) ?></td>
        <td style="font-family:var(--mono);font-size:12px"><?= date('d/m/Y', strtotime($t['created_at'])) ?></td>
        <td style="font-family:var(--mono);font-size:12px"><?= format_minutes((int)$t['work_time']) ?></td>
        <td>
          <a href="/techcopy/pages/report_ticket.php?id=<?= $t['id'] ?>"
             target="_blank" class="btn btn-secondary btn-sm" title="Apri rapporto">📄</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($ticketList)): ?>
      <tr><td colspan="9" style="text-align:center;padding:32px;color:var(--text2)">Nessun intervento trovato per i filtri selezionati.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php layout_footer(); ?>
