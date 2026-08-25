<?php
// ============================================================
//  TechCopy — Report Singolo Intervento
//  Accessibile da tutti i ruoli autenticati
//  URL: /techcopy/pages/report_ticket.php?id=XX
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_login();

$db = db();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: /techcopy/pages/tickets.php'); exit; }

$stmt = $db->prepare("
    SELECT t.*,
           c.name AS client_name, c.address, c.city, c.zip,
           c.phone AS client_phone, c.email AS client_email, c.contact,
           p.brand, p.model, p.serial, p.type AS printer_type,
           p.rete_lan, p.rete_wifi, p.adf, p.has_duplex,
           u.name AS tech_name
    FROM tickets t
    LEFT JOIN clients  c ON c.id = t.client_id
    LEFT JOIN printers p ON p.id = t.printer_id
    LEFT JOIN users    u ON u.id = t.tech_id
    WHERE t.id = ?");
$stmt->execute([$id]);
$t = $stmt->fetch();
if (!$t) { header('Location: /techcopy/pages/tickets.php'); exit; }

// Parti sostituite
$sp = $db->prepare("SELECT tp.*, c.name AS cons_name, c.code AS cons_code FROM ticket_parts tp LEFT JOIN consumables c ON c.id=tp.consumable_id WHERE tp.ticket_id=? ORDER BY tp.id");
$sp->execute([$id]); $parts = $sp->fetchAll();

// Team assegnato
$su = $db->prepare("SELECT tu.role_note, u.name, u.role FROM ticket_users tu JOIN users u ON u.id=tu.user_id WHERE tu.ticket_id=? ORDER BY tu.added_at");
$su->execute([$id]); $team = $su->fetchAll();

// Cronologia
$sh = $db->prepare("SELECT * FROM ticket_history WHERE ticket_id=? ORDER BY created_at ASC");
$sh->execute([$id]); $history = $sh->fetchAll();

$statusLabel = ['open'=>'Aperto','pending'=>'In attesa','closed'=>'Chiuso'];
$priorityLabel = ['normal'=>'Normale','high'=>'Alta','urgent'=>'Urgente'];
$typeLabel = ['guasto'=>'Guasto','manutenzione'=>'Manutenzione','errore'=>'Errore','installazione'=>'Installazione','consulenza'=>'Consulenza'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rapporto Intervento #<?= str_pad($id,4,'0',STR_PAD_LEFT) ?> — TechCopy</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  /* ── SCHERMO ── */
  * { box-sizing:border-box; margin:0; padding:0; }
  body { font-family:'IBM Plex Sans',sans-serif; background:#f0f2f5; color:#1a2233; font-size:14px; }
  .toolbar { background:#1a2233; color:#e6edf3; padding:12px 32px; display:flex; align-items:center; gap:12px; position:sticky; top:0; z-index:100; }
  .toolbar h2 { font-family:'IBM Plex Mono',monospace; font-size:16px; color:#00d4ff; flex:1; }
  .toolbar a,.toolbar button { padding:8px 16px; border-radius:6px; font-size:13px; font-weight:500; cursor:pointer; text-decoration:none; border:none; font-family:'IBM Plex Sans',sans-serif; }
  .btn-back   { background:#2a3448; color:#e6edf3; }
  .btn-print  { background:#00d4ff; color:#000; }
  .btn-back:hover { background:#3a4a64; }
  .btn-print:hover{ background:#00b8e0; }
  .report-wrap { max-width:820px; margin:28px auto; padding:0 16px 60px; }

  /* ── FOGLIO ── */
  .report { background:#fff; border-radius:10px; box-shadow:0 2px 16px rgba(0,0,0,.08); overflow:hidden; }
  .report-header { background:#1a2233; color:#e6edf3; padding:28px 36px; display:flex; align-items:flex-start; justify-content:space-between; gap:20px; }
  .report-header .company h1 { font-family:'IBM Plex Mono',monospace; font-size:22px; color:#00d4ff; margin-bottom:4px; }
  .report-header .company p  { font-size:12px; color:#8b98a8; }
  .report-header .ticket-ref { text-align:right; }
  .report-header .ticket-ref .num { font-family:'IBM Plex Mono',monospace; font-size:32px; font-weight:600; color:#00d4ff; }
  .report-header .ticket-ref .type{ font-size:12px; color:#8b98a8; margin-top:4px; }
  .status-bar { display:flex; gap:0; }
  .status-item { flex:1; padding:10px 20px; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; }
  .status-item .val { font-size:16px; font-weight:700; margin-top:2px; font-family:'IBM Plex Mono',monospace; }
  .s-open    { background:#fff3e0; color:#c25600; }
  .s-closed  { background:#e6f4ea; color:#1a7f37; }
  .s-pending { background:#fffde7; color:#8a6500; }
  .s-urgent  { background:#fde8e8; color:#cf2525; }
  .s-normal  { background:#f0f2f5; color:#4a5568; }
  .report-body { padding:28px 36px; }
  .section { margin-bottom:24px; }
  .section-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:#8b98a8; padding-bottom:8px; border-bottom:2px solid #e4e7ec; margin-bottom:14px; }
  .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
  .grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; }
  .info-box { background:#f8f9fb; border:1px solid #e4e7ec; border-radius:6px; padding:12px 14px; }
  .info-box .lbl { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:#8b98a8; margin-bottom:4px; }
  .info-box .val { font-size:14px; font-weight:500; color:#1a2233; }
  .info-box .val.mono { font-family:'IBM Plex Mono',monospace; }
  .text-block { background:#f8f9fb; border:1px solid #e4e7ec; border-radius:6px; padding:14px 16px; font-size:13px; line-height:1.7; color:#2d3748; white-space:pre-wrap; }
  .work-report-block { background:#f0f7ff; border:2px solid #0077bb; border-radius:6px; padding:14px 16px; font-size:13px; line-height:1.7; color:#1a2233; white-space:pre-wrap; }
  .part-row { display:flex; align-items:center; gap:10px; padding:8px 12px; border-bottom:1px solid #e4e7ec; font-size:13px; }
  .part-row:last-child { border-bottom:none; }
  .part-badge { background:#e4e7ec; border-radius:4px; padding:2px 8px; font-size:11px; font-family:'IBM Plex Mono',monospace; }
  .part-badge.linked { background:#dbeafe; color:#1d4ed8; }
  .qty-badge { background:#1a2233; color:#fff; border-radius:10px; padding:1px 8px; font-size:11px; font-family:'IBM Plex Mono',monospace; }
  .team-row { display:flex; align-items:center; gap:10px; padding:6px 0; font-size:13px; }
  .team-role { font-size:11px; color:#8b98a8; font-family:'IBM Plex Mono',monospace; }
  .hist-row { display:flex; gap:12px; padding:6px 0; border-bottom:1px solid #f0f2f5; font-size:12px; }
  .hist-row:last-child { border-bottom:none; }
  .hist-time { color:#8b98a8; font-family:'IBM Plex Mono',monospace; white-space:nowrap; min-width:140px; }
  .hist-who  { color:#4a5568; min-width:120px; }
  .signature-area { display:grid; grid-template-columns:1fr 1fr; gap:40px; margin-top:32px; padding-top:24px; border-top:2px solid #e4e7ec; }
  .signature-box { text-align:center; }
  .signature-line { border-top:1px solid #1a2233; margin-top:40px; padding-top:6px; font-size:11px; color:#8b98a8; }
  .report-footer { background:#f8f9fb; border-top:1px solid #e4e7ec; padding:12px 36px; display:flex; justify-content:space-between; font-size:11px; color:#8b98a8; font-family:'IBM Plex Mono',monospace; }
  .chip { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:10px; font-size:11px; background:#e4e7ec; color:#4a5568; margin:2px; }

  /* ── STAMPA ── */
  @media print {
    body { background:#fff; }
    .toolbar { display:none; }
    .report-wrap { margin:0; padding:0; max-width:100%; }
    .report { border-radius:0; box-shadow:none; }
    @page { margin:1.2cm 1.5cm; }
  }
</style>
</head>
<body>

<!-- Toolbar (solo schermo) -->
<div class="toolbar">
  <h2>🖨️ TechCopy — Rapporto Intervento</h2>
  <a href="/techcopy/pages/tickets.php?id=<?= $id ?>" class="btn-back">← Torna all'intervento</a>
  <button onclick="window.print()" class="btn-print">🖨️ Stampa / Salva PDF</button>
</div>

<div class="report-wrap">
<div class="report">

  <!-- INTESTAZIONE -->
  <div class="report-header">
    <div class="company">
      <h1>🖨️ Assistenza Stampanti</h1>
      <p>Rapporto di Intervento Tecnico</p>
      <p style="margin-top:8px;font-size:11px;color:#4a5568">Generato il: <?= date('d/m/Y H:i') ?></p>
    </div>
    <div class="ticket-ref">
      <div class="num">#<?= str_pad($t['id'],4,'0',STR_PAD_LEFT) ?></div>
      <div class="type"><?= h($typeLabel[$t['type']] ?? $t['type']) ?></div>
    </div>
  </div>

  <!-- BARRA STATO -->
  <div class="status-bar">
    <div class="status-item <?= $t['status']==='closed'?'s-closed':($t['status']==='pending'?'s-pending':'s-open') ?>">
      Stato<div class="val"><?= h($statusLabel[$t['status']] ?? $t['status']) ?></div>
    </div>
    <div class="status-item <?= $t['priority']==='urgent'?'s-urgent':'s-normal' ?>">
      Priorità<div class="val"><?= h($priorityLabel[$t['priority']] ?? $t['priority']) ?></div>
    </div>
    <div class="status-item s-normal">
      Aperto il<div class="val mono"><?= date('d/m/Y', strtotime($t['created_at'])) ?></div>
    </div>
    <div class="status-item <?= $t['resolved']?'s-closed':'s-normal' ?>">
      Risolto<div class="val"><?= $t['resolved'] ? '✅ Sì' : '⏳ No' ?></div>
    </div>
    <?php if ($t['closed_at']): ?>
    <div class="status-item s-closed">
      Chiuso il<div class="val mono"><?= date('d/m/Y', strtotime($t['closed_at'])) ?></div>
    </div>
    <?php endif; ?>
  </div>

  <div class="report-body">

    <!-- CLIENTE E MACCHINA -->
    <div class="section">
      <div class="section-title">Cliente e Apparecchiatura</div>
      <div class="grid-2" style="margin-bottom:12px">
        <div class="info-box">
          <div class="lbl">Cliente</div>
          <div class="val" style="font-size:16px;font-weight:600"><?= h($t['client_name']) ?></div>
          <?php if($t['contact']): ?><div style="font-size:12px;color:#4a5568;margin-top:4px">👤 <?= h($t['contact']) ?></div><?php endif; ?>
          <?php if($t['client_phone']): ?><div style="font-size:12px;color:#4a5568">📞 <?= h($t['client_phone']) ?></div><?php endif; ?>
        </div>
        <div class="info-box">
          <div class="lbl">Indirizzo</div>
          <div class="val">📍 <?= h($t['address'].', '.$t['city'].' '.$t['zip']) ?></div>
        </div>
      </div>
      <div class="grid-3">
        <div class="info-box">
          <div class="lbl">Stampante</div>
          <div class="val"><?= h($t['brand'].' '.$t['model']) ?></div>
        </div>
        <div class="info-box">
          <div class="lbl">Numero di serie</div>
          <div class="val mono"><?= h($t['serial'] ?? '—') ?></div>
        </div>
        <div class="info-box">
          <div class="lbl">Tipo</div>
          <div class="val"><?= $t['printer_type']==='color'?'🎨 Colore':'⬛ B/N' ?>
            <?php if($t['adf']&&$t['adf']!=='none'): ?><span class="chip"><?= adf_label($t['adf']) ?></span><?php endif; ?>
            <?php if($t['has_duplex']): ?><span class="chip">Duplex</span><?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- TEAM -->
    <div class="section">
      <div class="section-title">Team Tecnico</div>
      <?php if (!empty($team)): ?>
        <?php foreach($team as $tm): ?>
        <div class="team-row">
          <span><?= h($tm['name']) ?></span>
          <span class="team-role"><?= h($tm['role_note'] ?? '') ?></span>
          <span class="chip" style="font-size:10px"><?= match($tm['role']){'admin'=>'👑 Admin','supervisor'=>'🔍 Supervisore','tech'=>'🔧 Tecnico',default=>$tm['role']} ?></span>
        </div>
        <?php endforeach; ?>
      <?php elseif($t['tech_name']): ?>
        <div class="team-row"><span><?= h($t['tech_name']) ?></span><span class="team-role">Responsabile</span></div>
      <?php else: ?>
        <p style="color:#8b98a8;font-size:13px">Nessun tecnico assegnato</p>
      <?php endif; ?>
    </div>

    <!-- TEMPI -->
    <div class="section">
      <div class="section-title">Tempi di Intervento</div>
      <div class="grid-3">
        <div class="info-box">
          <div class="lbl">Tempo spostamento</div>
          <div class="val mono"><?= format_minutes((int)$t['travel_time']) ?></div>
        </div>
        <div class="info-box">
          <div class="lbl">Tempo lavoro</div>
          <div class="val mono"><?= format_minutes((int)$t['work_time']) ?></div>
        </div>
        <div class="info-box">
          <div class="lbl">Totale</div>
          <div class="val mono" style="color:#0077bb;font-size:16px"><?= format_minutes((int)$t['travel_time']+(int)$t['work_time']) ?></div>
        </div>
      </div>
      <?php if($t['counter_bw'] || $t['counter_color']): ?>
      <div class="grid-2" style="margin-top:12px">
        <div class="info-box">
          <div class="lbl">Contatore B/N (al momento intervento)</div>
          <div class="val mono"><?= number_format((int)$t['counter_bw']) ?> copie</div>
        </div>
        <?php if($t['counter_color']): ?>
        <div class="info-box">
          <div class="lbl">Contatore Colore (al momento intervento)</div>
          <div class="val mono"><?= number_format((int)$t['counter_color']) ?> copie</div>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- PROBLEMA SEGNALATO -->
    <div class="section">
      <div class="section-title">Problema Segnalato</div>
      <div style="font-size:16px;font-weight:600;margin-bottom:8px"><?= h($t['title']) ?></div>
      <?php if($t['description']): ?>
      <div class="text-block"><?= h($t['description']) ?></div>
      <?php endif; ?>
    </div>

    <!-- LAVORO SVOLTO -->
    <?php if(!empty($t['work_report'])): ?>
    <div class="section">
      <div class="section-title">📋 Lavoro Svolto</div>
      <div class="work-report-block"><?= h($t['work_report']) ?></div>
    </div>
    <?php endif; ?>

    <!-- NOTE TECNICHE -->
    <?php if(!empty($t['notes'])): ?>
    <div class="section">
      <div class="section-title">Note Tecniche</div>
      <div class="text-block"><?= h($t['notes']) ?></div>
    </div>
    <?php endif; ?>

    <!-- COMPONENTI SOSTITUITI -->
    <?php if(!empty($parts)): ?>
    <div class="section">
      <div class="section-title">Componenti Sostituiti (<?= count($parts) ?>)</div>
      <div style="border:1px solid #e4e7ec;border-radius:6px;overflow:hidden">
        <div style="display:grid;grid-template-columns:1fr 120px 60px;background:#f0f2f5;padding:8px 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#8b98a8">
          <div>Componente</div><div>Codice</div><div style="text-align:center">Qtà</div>
        </div>
        <?php foreach($parts as $p): ?>
        <div class="part-row">
          <div style="flex:1">
            <?= h($p['part_name']) ?>
            <?php if(!empty($p['consumable_id'])): ?><span class="part-badge linked">📦 magazzino</span><?php endif; ?>
          </div>
          <div><span class="part-badge"><?= h($p['part_code'] ?? '—') ?></span></div>
          <div style="text-align:center"><span class="qty-badge"><?= (int)($p['quantity']??1) ?></span></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- CRONOLOGIA -->
    <div class="section">
      <div class="section-title">Cronologia (<?= count($history) ?> eventi)</div>
      <?php foreach($history as $h): ?>
      <div class="hist-row">
        <div class="hist-time"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></div>
        <div class="hist-who"><?= h($h['user_name'] ?? '—') ?></div>
        <div><?= h($h['action']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- FIRME -->
    <div class="signature-area">
      <div class="signature-box">
        <div class="signature-line">Firma Tecnico<br><?= h($t['tech_name'] ?? '___________________') ?></div>
      </div>
      <div class="signature-box">
        <div class="signature-line">Firma Cliente<br><?= h($t['contact'] ?? '___________________') ?></div>
      </div>
    </div>

  </div><!-- .report-body -->

  <div class="report-footer">
    <span>TechCopy — Rapporto Intervento #<?= str_pad($id,4,'0',STR_PAD_LEFT) ?></span>
    <span><?= date('d/m/Y H:i') ?></span>
  </div>

</div><!-- .report -->
</div><!-- .report-wrap -->
</body>
</html>
