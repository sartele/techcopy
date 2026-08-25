<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_login();

$db = db();
$id = (int)($_GET['id'] ?? 0);
if (!$id) exit('Ticket non valido');

$stmt = $db->prepare("
    SELECT t.*,
           c.name AS client_name, c.address, c.city, c.zip,
           c.phone AS client_phone, c.contact,
           p.brand, p.model, p.serial,
           u.name AS tech_name
    FROM tickets t
    LEFT JOIN clients c ON c.id=t.client_id
    LEFT JOIN printers p ON p.id=t.printer_id
    LEFT JOIN users u ON u.id=t.tech_id
    WHERE t.id=?
");
$stmt->execute([$id]);
$t = $stmt->fetch();

if (!$t) exit('Ticket non trovato');

$sp = $db->prepare("
    SELECT * FROM ticket_parts
    WHERE ticket_id=?
");
$sp->execute([$id]);
$parts = $sp->fetchAll();

function fm($m){
    $h = floor($m/60);
    $mm = $m%60;
    return "{$h}h {$mm}m";
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<link rel="icon" type="image/png" href="/techcopy/assets/img/favicon.png">
<link rel="apple-touch-icon" href="/techcopy/assets/img/favicon.png">
<title>Report Intervento</title>
<style>
body{
    font-family:Arial,sans-serif;
    font-size:12px;
    color:#222;
    margin:0;
    background:#eee;
}
.wrapper{
    max-width:900px;
    margin:20px auto;
    background:#fff;
    padding:25px;
}
h1,h2,h3{margin:0}
.header{
    display:flex;
    justify-content:space-between;
    border-bottom:2px solid #000;
    padding-bottom:10px;
    margin-bottom:20px;
}
.section{
    margin-bottom:18px;
}
.section-title{
    font-size:13px;
    font-weight:bold;
    padding-bottom:2px;
    margin-bottom:8px;
}
table{
    width:100%;
    border-collapse:collapse;
}
td,th{
    border:0px solid #ddd;
    padding:6px;
}
.text-box{
    border:1px solid #ddd;
    padding:5px;
    min-height:50px;
    white-space:normal;
}
.signatures{
    margin-top:40px;
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:60px;
}
.signature{
    text-align:center;
}
.signature img{
    max-height:90px;
    max-width:100%;
}
.line{
    border-top:1px solid #000;
    margin-top:40px;
    padding-top:6px;
}
.toolbar{
    padding:15px;
    background:#111;
}
.toolbar button{
    padding:10px 16px;
}
.report-footer{
/*	background:#eee; */
/*	padding:12px 36px; */
	display:flex; 
	justify-content:space-between;
	font-size:11px;
	color:#222;
	font-family:Arial,sans-serif;
}

@media print{
    .toolbar{display:none;}
    body{background:#fff;}
    .wrapper{margin:0; padding:0;}
}
</style>
</head>
<body>

<div class="toolbar">
    <button onclick="window.print()">🖨 Stampa</button> <button onclick="window.location.href='tickets.php?id=<?= $t['id'] ?>'">Torna</button>
</div>

<div class="wrapper">

<div class="header">
    <div>
        <h2><img src="/techcopy/assets/img/favicon.png" width="20" height="20"> Scrive &amp; Riscrive SRL - Rapporto Intervento Tecnico</h2>
    </div>
    <div style="text-align:right">
        <h2>#<?= str_pad($t['id'],4,'0',STR_PAD_LEFT) ?></h2> <?= date('d/m/Y') ?>
    </div>
</div>

<div class="section">
    <div class="section-title">Cliente / Apparecchiatura</div>
    <table>
        <tr>
            <td><b>Cliente</b><br><?= h($t['client_name']) ?></td>
            <td><b>Contatto</b><br><?= h($t['contact']) ?></td>
        </tr>
        <tr>
            <td colspan="2"><b>Indirizzo</b><br><?= h($t['address'].' - '.$t['zip'].' - '.$t['city']) ?></td>
        </tr>
        <tr>
            <td><b>Stampante</b><br><?= h($t['brand'].' '.$t['model']) ?></td>
            <td><b>Seriale</b><br><?= h($t['serial']) ?></td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-title">Problema Segnalato: <b><?= h($t['title']) ?></b></div>
    <div class="text-box">
        <?= h($t['description']) ?>
    </div>
</div>

<div class="section">
    <div class="section-title">Intervento Eseguito</div>
    <div class="text-box">
        <?= h($t['work_report']) ?>
    </div>
</div>

<div class="section">
    <div class="section-title">Tempi</div>
    <table>
        <tr>
            <td><b>Spostamento</b><br><?= fm((int)$t['travel_time']) ?></td>
            <td><b>Lavoro</b><br><?= fm((int)$t['work_time']) ?></td>
            <td><b>Totale</b><br><?= fm((int)$t['travel_time'] + (int)$t['work_time']) ?></td>
        </tr>
    </table>
</div>

<?php if($parts): ?>
<div class="section">
    <div class="section-title">Componenti Sostituiti</div>
    <table>
        <tr>
            <th>Componente</th>
            <th>Codice</th>
            <th>Qtà</th>
        </tr>
        <?php foreach($parts as $p): ?>
        <tr>
            <td><?= h($p['part_name']) ?></td>
            <td><?= h($p['part_code']) ?></td>
            <td><?= (int)$p['quantity'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>

<div class="signatures">
    <div class="signature">
        <div class="line">
            Firma Tecnico<br>
            <?= h($t['tech_name']) ?>
        </div>
    </div>

    <div class="signature">
        <?php if(!empty($t['client_signature_path'])): ?>
            <img src="<?= h($t['client_signature_path']) ?>">
        <?php endif; ?>

        <div class="line">
            Firma Cliente<br>
            <?= h($t['client_name']) ?>
        </div>
    </div>
</div>
<br>
<br>
  <div class="report-footer">
    <span>Scrive &amp; Riscrive SRL — Rapporto Intervento <?= h($t['client_name']) ?></span>
    <span><?= date('d/m/Y H:i') ?></span>
  </div>
</div>
</body>
</html>