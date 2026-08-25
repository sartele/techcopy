<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_login();

$db = db();
$id = (int)($_GET['id'] ?? 0);
if (!$id) exit('Ticket non valido');

$stmt = $db->prepare("
    SELECT t.id, t.client_signature_path, c.name AS client_name
    FROM tickets t
    LEFT JOIN clients c ON c.id=t.client_id
    WHERE t.id=?
");
$stmt->execute([$id]);
$t = $stmt->fetch();

if (!$t) exit('Ticket non trovato');
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Firma Cliente</title>

<style>
body{
    margin:0;
    background:#f5f6f8;
    font-family:Arial,sans-serif;
}
.wrapper{
    max-width:700px;
    margin:30px auto;
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 2px 20px rgba(0,0,0,.08);
}
h2,h3{
    margin-top:0;
}
.signature-box{
    border:2px dashed #999;
    border-radius:8px;
    margin:20px 0;
    overflow:hidden;
}
canvas{
    display:block;
    width:100%;
    height:280px;
    touch-action:none;
    background:#fff;
}
.actions{
    display:flex;
    gap:10px;
}
button{
    padding:12px 18px;
    border:none;
    border-radius:6px;
    cursor:pointer;
}
.btn-clear{
    background:#ddd;
}
.btn-save{
    background:#0077bb;
    color:#fff;
}
.btn-refirma{
    background:#f0ad4e;
    color:#fff;
}
.signature-preview{
    max-width:100%;
    border:1px solid #ccc;
    margin:15px 0;
}
</style>
</head>
<body>

<div class="wrapper">
    <h2>Firma Cliente</h2>

    <p><strong>Ticket:</strong> #<?= str_pad($t['id'],4,'0',STR_PAD_LEFT) ?></p>
    <p><strong>Cliente:</strong> <?= h($t['client_name']) ?></p>

    <?php if (!empty($t['client_signature_path'])): ?>
        <h3>Firma già presente</h3>
        <img src="<?= h($t['client_signature_path']) ?>" class="signature-preview">

        <div style="margin-bottom:20px;">
            <button class="btn-refirma" onclick="showCanvas()">Rifirma</button>
        </div>
    <?php endif; ?>

    <div id="signature-area" <?= !empty($t['client_signature_path']) ? 'style="display:none;"' : '' ?>>

        <p>Firmare nel riquadro sottostante.</p>

        <div class="signature-box">
            <canvas id="signature-pad"></canvas>
        </div>

        <div class="actions">
            <button class="btn-clear" onclick="clearPad()">Pulisci</button>
            <button class="btn-save" onclick="saveSignature()">Salva Firma</button>
        </div>
    </div>

    <div id="msg" style="margin-top:20px;"></div>
</div>

<script>
const canvas = document.getElementById('signature-pad');
const ctx = canvas.getContext('2d');

function resizeCanvas() {
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width;
    canvas.height = 280;

    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
}
resizeCanvas();

let drawing = false;
let signed = false;

function showCanvas(){
    const area = document.getElementById('signature-area');
    area.style.display = 'block';

    setTimeout(() => {
        resizeCanvas();
        clearPad();
    }, 50);
}

function getPos(e){
    const rect = canvas.getBoundingClientRect();
    if (e.touches) {
        return {
            x: e.touches[0].clientX - rect.left,
            y: e.touches[0].clientY - rect.top
        };
    }
    return {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top
    };
}

function start(e){
    drawing = true;
    const pos = getPos(e);
    ctx.beginPath();
    ctx.moveTo(pos.x,pos.y);
}

function draw(e){
    if(!drawing) return;

    signed = true;
    e.preventDefault();

    const pos = getPos(e);
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.lineTo(pos.x,pos.y);
    ctx.stroke();
}

function stop(){
    drawing = false;
}

canvas.addEventListener('mousedown', start);
canvas.addEventListener('mousemove', draw);
canvas.addEventListener('mouseup', stop);

canvas.addEventListener('touchstart', start);
canvas.addEventListener('touchmove', draw);
canvas.addEventListener('touchend', stop);

function clearPad(){
    ctx.clearRect(0,0,canvas.width,canvas.height);
    signed = false;
}

function saveSignature(){

    if(!signed){
        alert("Inserire una firma");
        return;
    }

    const data = canvas.toDataURL('image/png');

    fetch('save_signature.php', {
        method:'POST',
        headers:{
            'Content-Type':'application/json'
        },
        body: JSON.stringify({
            ticket_id: <?= $t['id'] ?>,
            signature: data
        })
    })
    .then(r => r.json())
    .then(data => {
        if(data.success){

            window.open(
                "report_ticket_compact.php?id=<?= $t['id'] ?>",
                "_blank"
            );

            window.location.href = "tickets.php?id=<?= $t['id'] ?>";

        } else {
            document.getElementById('msg').innerHTML = data.message;
        }
    })
    .catch(err => {
        console.error(err);
        document.getElementById('msg').innerHTML = "Errore di connessione";
    });
}
</script>

</body>
</html>