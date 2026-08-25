<?php
require_once __DIR__ . '/includes/layout.php';
require_login();
$db = db();
$me = current_user();

$openTickets   = 0; $urgentTickets = 0; $closedTickets = 0;
$clientCount   = 0; $printerCount  = 0; $lowStock = 0;

if ($me['role'] === 'tech') {
    $s = $db->prepare("SELECT COUNT(*) FROM tickets WHERE status='open' AND (tech_id=? OR EXISTS(SELECT 1 FROM ticket_users tu WHERE tu.ticket_id=tickets.id AND tu.user_id=?))"); $s->execute([$me['id'],$me['id']]); $openTickets=$s->fetchColumn();
    $s = $db->prepare("SELECT COUNT(*) FROM tickets WHERE status!='closed' AND (priority='urgent' OR priority='high') AND (tech_id=? OR EXISTS(SELECT 1 FROM ticket_users tu WHERE tu.ticket_id=tickets.id AND tu.user_id=?))"); $s->execute([$me['id'],$me['id']]); $urgentTickets=$s->fetchColumn();
    $s = $db->prepare("SELECT COUNT(*) FROM tickets WHERE status='closed' AND (tech_id=? OR EXISTS(SELECT 1 FROM ticket_users tu WHERE tu.ticket_id=tickets.id AND tu.user_id=?))"); $s->execute([$me['id'],$me['id']]); $closedTickets=$s->fetchColumn();
} else {
    $openTickets   = $db->query("SELECT COUNT(*) FROM tickets WHERE status='open'")->fetchColumn();
    $urgentTickets = $db->query("SELECT COUNT(*) FROM tickets WHERE status!='closed' AND (priority='urgent' OR priority='high')")->fetchColumn();
    $closedTickets = $db->query("SELECT COUNT(*) FROM tickets WHERE status='closed'")->fetchColumn();
}
$clientCount  = $db->query("SELECT COUNT(*) FROM clients WHERE active=1")->fetchColumn();
$printerCount = $db->query("SELECT COUNT(*) FROM printers WHERE active=1")->fetchColumn();
$lowStock     = $db->query("SELECT COUNT(*) FROM consumables WHERE stock<=min_stock")->fetchColumn();

// Interventi recenti
$recentWhere = $me['role']==='tech' ? "AND (t.tech_id={$me['id']} OR EXISTS(SELECT 1 FROM ticket_users tu WHERE tu.ticket_id=t.id AND tu.user_id={$me['id']}))" : '';
$recentTickets = $db->query("
    SELECT t.*,c.name AS client_name,u.name AS tech_name,u.color AS tech_color,p.brand,p.model
    FROM tickets t
    LEFT JOIN clients c  ON c.id=t.client_id
    LEFT JOIN users u    ON u.id=t.tech_id
    LEFT JOIN printers p ON p.id=t.printer_id
    WHERE t.status!='closed' $recentWhere
    ORDER BY FIELD(t.priority,'urgent','high','normal'), t.created_at DESC
    LIMIT 5")->fetchAll();

$criticalStock = $db->query("SELECT * FROM consumables WHERE stock<=min_stock ORDER BY stock ASC LIMIT 6")->fetchAll();

$operativi = $db->query("
    SELECT u.id,u.name,u.color,u.avatar,u.role,
           COUNT(t.id) AS open_tickets
    FROM users u
    LEFT JOIN tickets t ON t.tech_id=u.id AND t.status!='closed'
    WHERE u.role IN ('tech','supervisor') AND u.active=1
    GROUP BY u.id ORDER BY FIELD(u.role,'supervisor','tech'), u.name")->fetchAll();

layout_header('Dashboard', 'dashboard');
?>

<div class="stats-grid">
  <div class="stat-card" style="border-top:2px solid var(--orange)">
    <div class="stat-icon">🔧</div>
    <div class="value" style="color:var(--orange)"><?= $openTickets ?></div>
    <div class="label"><?= $me['role']==='tech'?'Miei interventi aperti':'Interventi aperti' ?></div>
    <div class="trend" style="color:var(--orange)">↑ <?= $urgentTickets ?> urgenti</div>
  </div>
  <div class="stat-card" style="border-top:2px solid var(--green)">
    <div class="stat-icon">✅</div>
    <div class="value" style="color:var(--green)"><?= $closedTickets ?></div>
    <div class="label">Interventi chiusi</div>
    <div class="trend" style="color:var(--text2)">totale storico</div>
  </div>
  <div class="stat-card" style="border-top:2px solid var(--accent)">
    <div class="stat-icon">🏢</div>
    <div class="value" style="color:var(--accent)"><?= $clientCount ?></div>
    <div class="label">Clienti attivi</div>
    <div class="trend" style="color:var(--text2)"><?= $printerCount ?> stampanti</div>
  </div>
  <div class="stat-card" style="border-top:2px solid var(--red)">
    <div class="stat-icon">📦</div>
    <div class="value" style="color:var(--red)"><?= $lowStock ?></div>
    <div class="label">Stock critico</div>
    <div class="trend" style="color:var(--red)">Riordino necessario</div>
  </div>
</div>

<div class="two-col">
  <div>
    <div class="table-wrap">
      <div class="table-head">
        <h4>🔴 Interventi urgenti / aperti</h4>
        <a href="/techcopy/pages/tickets.php" class="btn btn-primary btn-sm">Vedi tutti</a>
      </div>
      <div style="padding:12px">
        <?php foreach ($recentTickets as $t):
          $cls = $t['priority']==='urgent' ? 'urgent' : 'open'; ?>
        <a href="/techcopy/pages/tickets.php?id=<?= $t['id'] ?>" class="ticket-card <?= $cls ?>" style="display:block">
          <div class="ticket-top">
            <span class="ticket-id">#<?= str_pad($t['id'],4,'0',STR_PAD_LEFT) ?></span>
            <span class="ticket-title"><?= h($t['title']) ?></span>
            <?= status_badge($t['status']) ?>
          </div>
          <div class="ticket-meta">
            <span>🏢 <?= h($t['client_name']) ?></span>
            <span>🖨️ <?= h($t['brand'].' '.$t['model']) ?></span>
            <span>📅 <?= substr($t['created_at'],0,10) ?></span>
          </div>
        </a>
        <?php endforeach; ?>
        <?php if (empty($recentTickets)): ?>
        <p style="color:var(--text2);font-size:13px;padding:8px">Nessun intervento aperto 🎉</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="table-wrap">
      <div class="table-head"><h4>⚠️ Stock critico</h4></div>
      <div style="padding:12px">
        <?php foreach ($criticalStock as $c): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border)">
          <div style="flex:1">
            <div style="font-size:13px;font-weight:500"><?= h($c['name']) ?></div>
            <div style="font-size:11px;color:var(--text2);font-family:var(--mono)"><?= h($c['code']) ?></div>
          </div>
          <span class="badge <?= $c['stock']==0?'badge-urgent':'badge-open' ?>"><?= $c['stock'] ?> <?= h($c['unit']) ?></span>
        </div>
        <?php endforeach; ?>
        <?php if (empty($criticalStock)): ?><p style="color:var(--text2);font-size:13px;padding:8px">Magazzino OK ✅</p><?php endif; ?>
      </div>
    </div>

    <div class="table-wrap">
      <div class="table-head"><h4>👥 Team operativo</h4></div>
      <div style="padding:12px">
        <?php foreach ($operativi as $u): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border)">
          <div style="width:32px;height:32px;border-radius:50%;background:<?= h($u['color']) ?>22;color:<?= h($u['color']) ?>;border:1.5px solid <?= h($u['color']) ?>;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;flex-shrink:0"><?= h($u['avatar']) ?></div>
          <div style="flex:1;font-size:13px"><?= h($u['name']) ?></div>
          <span class="badge badge-<?= h($u['role']) ?>"><?= $u['role']==='supervisor'?'🔍 Sup':'🔧 Tec' ?></span>
          <span class="badge badge-open"><?= $u['open_tickets'] ?> aperti</span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php layout_footer(); ?>
