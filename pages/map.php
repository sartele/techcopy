<?php
require_once __DIR__ . '/../includes/layout.php';
require_login(); // tutti i ruoli vedono la mappa

$db = db();

$clients = $db->query("
    SELECT c.*,
        COUNT(DISTINCT p.id) AS printer_count,
        SUM(CASE WHEN t.status='open' AND t.priority='urgent' THEN 1 ELSE 0 END) AS urgent_count,
        SUM(CASE WHEN t.status='open'    THEN 1 ELSE 0 END) AS open_count,
        SUM(CASE WHEN t.status='pending' THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN t.status='closed'  THEN 1 ELSE 0 END) AS closed_count
    FROM clients c
    LEFT JOIN printers p ON p.client_id=c.id AND p.active=1
    LEFT JOIN tickets  t ON t.client_id=c.id
    WHERE c.active=1
    GROUP BY c.id
    ORDER BY c.name
")->fetchAll();

// Geocoding automatico via Nominatim (OSM) per clienti senza coordinate
foreach ($clients as &$c) {
    if ((!$c['lat'] || !$c['lng']) && !empty($c['address']) && !empty($c['city'])) {
        $q   = urlencode($c['address'].', '.$c['city'].', Italia');
        $url = "https://nominatim.openstreetmap.org/search?q={$q}&format=json&limit=1";
        $ctx = stream_context_create(['http'=>['header'=>"User-Agent: TechCopy/2.0\r\n",'timeout'=>3]]);
        $resp = @file_get_contents($url, false, $ctx);
        if ($resp) {
            $geo = json_decode($resp, true);
            if (!empty($geo[0])) {
                $lat = $geo[0]['lat']; $lng = $geo[0]['lon'];
                $db->prepare("UPDATE clients SET lat=?,lng=? WHERE id=?")->execute([$lat,$lng,$c['id']]);
                $c['lat'] = $lat; $c['lng'] = $lng;
            }
        }
    }
}
unset($c);

$withCoords = array_filter($clients, fn($c) => $c['lat'] && $c['lng']);
$noCoords   = array_filter($clients, fn($c) => !$c['lat'] || !$c['lng']);

layout_header('Mappa Clienti', 'map');
?>

<!-- Leaflet 1.9.4 — gratuito, nessuna chiave API -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
.leaflet-popup-content-wrapper { background:var(--surface); border:1px solid var(--border); border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,.5); color:var(--text); }
.leaflet-popup-tip            { background:var(--surface); }
.leaflet-popup-content        { margin:14px 16px; min-width:230px; }
.leaflet-popup-close-button   { color:var(--text2) !important; font-size:18px !important; top:8px !important; right:10px !important; }
.tc-marker { width:30px; height:30px; border-radius:50% 50% 50% 0; transform:rotate(-45deg); border:3px solid rgba(255,255,255,.85); box-shadow:0 3px 10px rgba(0,0,0,.5); display:flex; align-items:center; justify-content:center; cursor:pointer; transition:transform .15s; }
.tc-marker:hover { transform:rotate(-45deg) scale(1.25); }
.tc-marker-inner { transform:rotate(45deg); font-size:12px; }
.tc-badge { position:absolute; top:-7px; right:-7px; border-radius:50%; width:18px; height:18px; font-size:10px; font-weight:700; font-family:var(--mono); display:flex; align-items:center; justify-content:center; color:#fff; box-shadow:0 1px 4px rgba(0,0,0,.4); }
.client-list-item.selected { box-shadow:0 0 0 2px var(--accent) !important; background:var(--accent-dim) !important; }
.layer-btn { padding:5px 10px; background:var(--surface); border:1px solid var(--border); border-radius:4px; color:var(--text2); font-size:12px; cursor:pointer; transition:all .15s; }
.layer-btn.active { background:var(--accent-dim); border-color:var(--accent); color:var(--accent); }
</style>

<?php if (count($noCoords) > 0): ?>
<div class="alert alert-warning" style="margin-bottom:16px">
  ⚠️ <?= count($noCoords) ?> cliente/i senza coordinate GPS.
  <?php if (can_manage_clients()): ?>
  Aggiungile dalla <a href="/techcopy/pages/clients.php" style="color:var(--orange);text-decoration:underline">pagina clienti</a> oppure attendere il geocoding automatico al prossimo caricamento.
  <?php endif; ?>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">

  <!-- MAPPA -->
  <div>
    <div id="leaflet-map" style="height:520px;border-radius:10px;border:1px solid var(--border);overflow:hidden"></div>
    <div class="map-legend">
      <div style="display:flex;align-items:center;gap:6px"><div class="dot" style="background:var(--red)"></div> Urgente</div>
      <div style="display:flex;align-items:center;gap:6px"><div class="dot" style="background:var(--orange)"></div> Aperto</div>
      <div style="display:flex;align-items:center;gap:6px"><div class="dot" style="background:var(--green)"></div> OK</div>
      <div style="margin-left:auto;display:flex;align-items:center;gap:6px;flex-wrap:wrap">
        <span style="font-size:11px;color:var(--text3)"><?= count($withCoords) ?>/<?= count($clients) ?> clienti &nbsp;·&nbsp; Stile:</span>
        <button class="layer-btn active" data-layer="osm"    onclick="switchLayer('osm')">🗺️ Standard</button>
        </div>
    </div>
  </div>

  <!-- LISTA LATERALE -->
  <div>
    <div class="search-bar" style="margin-bottom:10px;width:100%">
      <span>🔍</span>
      <input id="client-filter" placeholder="Filtra clienti..." oninput="filterList(this.value)" style="width:100%">
    </div>
    <div style="overflow-y:auto;max-height:520px;display:flex;flex-direction:column;gap:8px;padding-right:2px">
      <?php foreach ($clients as $c):
        $pc = $c['urgent_count']>0?'red':($c['open_count']>0?'orange':'green');
        $bc = $pc==='red'?'var(--red)':($pc==='orange'?'var(--orange)':'var(--green)');
        $has= $c['lat'] && $c['lng'];
      ?>
      <div id="client-card-<?= $c['id'] ?>"
           class="ticket-card client-list-item"
           data-name="<?= h(strtolower($c['name'].' '.$c['city'].' '.$c['address'])) ?>"
           style="border-left:3px solid <?= $bc ?>;padding:12px;cursor:pointer<?= !$has?';opacity:.5':'' ?>"
           onclick="selectClient(<?= $c['id'] ?>)">
        <div style="font-size:13px;font-weight:600;margin-bottom:4px"><?= h($c['name']) ?></div>
        <div style="font-size:11px;color:var(--text2);margin-bottom:8px"><?= $has?'📍':'❓' ?> <?= h($c['address'].', '.$c['city']) ?></div>
        <div style="display:flex;gap:5px;flex-wrap:wrap">
          <?php if($c['urgent_count']>0): ?><span class="badge badge-urgent">🔴 <?= $c['urgent_count'] ?></span><?php endif; ?>
          <?php if($c['open_count']>0): ?><span class="badge badge-open">🟠 <?= $c['open_count'] ?></span><?php endif; ?>
          <?php if($c['open_count']==0&&$c['urgent_count']==0): ?><span class="badge badge-closed">✅ OK</span><?php endif; ?>
          <span class="badge badge-supervisor">🖨️ <?= $c['printer_count'] ?></span>
          <?php if(!$has): ?><span class="badge" style="background:var(--orange-dim);color:var(--orange)">No GPS</span><?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- PANNELLO DETTAGLIO -->
<div id="client-detail" style="display:none;margin-top:20px">
  <div class="table-wrap" style="padding:0">
    <div class="table-head">
      <h4 id="cd-title">—</h4>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a id="cd-osm"  href="#" target="_blank" class="btn btn-secondary btn-sm">🗺️ OpenStreetMap</a>
        <a id="cd-gmap" href="#" target="_blank" class="btn btn-secondary btn-sm">🔍 Google Maps</a>
        <a id="cd-waze" href="#" target="_blank" class="btn btn-secondary btn-sm">🚗 Waze</a>
        <a id="cd-link" href="#"                 class="btn btn-primary btn-sm">📋 Scheda cliente</a>
        <button class="btn btn-secondary btn-sm" onclick="closeDetail()">✕</button>
      </div>
    </div>
    <div id="cd-body" style="padding:20px"></div>
  </div>
</div>

<script>
const CLIENTS = <?= json_encode(array_values($clients), JSON_UNESCAPED_UNICODE) ?>;

// Inizializza Leaflet
const map = L.map('leaflet-map', {zoomControl:true});

// Tile layers disponibili — OSM è il più affidabile in locale
const tileLayers = {
  osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19
  }),
  carto: L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_matter/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com">CARTO</a>',
    subdomains: 'abcd', maxZoom: 19
  }),
  osmHot: L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19
  })
};

let currentLayerKey = 'osm';
tileLayers.osm.addTo(map);  // OSM standard come default — sempre funzionante

function switchLayer(key) {
  Object.values(tileLayers).forEach(l => {
    if (map.hasLayer(l)) map.removeLayer(l);
  });

  tileLayers[key].addTo(map);

  setTimeout(() => {
    map.invalidateSize();
  }, 100);

  currentLayerKey = key;

  document.querySelectorAll('.layer-btn').forEach(b =>
    b.classList.toggle('active', b.dataset.layer === key)
  );
}

function makeIcon(color, count) {
  const badge = count > 0 ? `<div class="tc-badge" style="background:${color}">${count}</div>` : '';
  return L.divIcon({
    className:'',
    html:`<div style="position:relative;width:36px;height:42px"><div class="tc-marker" style="background:${color}"><div class="tc-marker-inner">🖨</div></div>${badge}</div>`,
    iconSize:[36,42], iconAnchor:[18,42], popupAnchor:[0,-44]
  });
}

function makePopup(c) {
  const tickets = parseInt(c.open_count||0) + parseInt(c.pending_count||0);
  const mk = (bg,col,txt) => `<span style="background:${bg};color:${col};padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600">${txt}</span>`;
  return `<div style="font-family:'IBM Plex Sans',sans-serif">
    <div style="font-size:14px;font-weight:700;margin-bottom:4px;color:#e6edf3">${c.name}</div>
    <div style="font-size:11px;color:#8b98a8;margin-bottom:10px">📍 ${c.address}, ${c.city}</div>
    <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:10px">
      ${c.urgent_count>0 ? mk('rgba(244,71,71,.18)','#f44747',`🔴 ${c.urgent_count} urgenti`):''}
      ${c.open_count>0   ? mk('rgba(247,144,0,.18)', '#f79000',`🟠 ${c.open_count} aperti`):''}
      ${tickets===0      ? mk('rgba(57,211,83,.18)', '#39d353','✅ OK'):''}
      ${mk('rgba(0,212,255,.15)','#00d4ff',`🖨️ ${c.printer_count} stampanti`)}
    </div>
    ${c.phone   ? `<div style="font-size:12px;color:#8b98a8;margin-bottom:4px">📞 ${c.phone}</div>`:''}
    ${c.contact ? `<div style="font-size:12px;color:#8b98a8;margin-bottom:10px">👤 ${c.contact}</div>`:''}
    <a href="/techcopy/pages/clients.php?id=${c.id}" style="display:inline-block;padding:6px 14px;background:#00d4ff;color:#000;border-radius:5px;font-size:12px;font-weight:700;text-decoration:none">→ Scheda cliente</a>
  </div>`;
}

const markerMap = {}; const allMarkers = [];
CLIENTS.forEach(c => {
  if (!c.lat || !c.lng) return;
  const color = c.urgent_count>0?'#f44747':c.open_count>0?'#f79000':'#39d353';
  const count = parseInt(c.open_count||0)+parseInt(c.pending_count||0);
  const m = L.marker([parseFloat(c.lat),parseFloat(c.lng)],{icon:makeIcon(color,count),title:c.name,riseOnHover:true}).addTo(map);
  m.bindPopup(makePopup(c),{maxWidth:280,minWidth:240});
  m.on('click',()=>selectClient(c.id));
  markerMap[c.id]=m; allMarkers.push(m);
});
if (allMarkers.length>0) map.fitBounds(L.featureGroup(allMarkers).getBounds().pad(0.18));
else map.setView([45.47,9.18],10);

let selectedId = null;
function selectClient(id) {
  const c = CLIENTS.find(x=>x.id==id);
  if (!c) return;
  if (selectedId!=null) document.getElementById('client-card-'+selectedId)?.classList.remove('selected');
  selectedId=id;
  const card=document.getElementById('client-card-'+id);
  if (card) { card.classList.add('selected'); card.scrollIntoView({behavior:'smooth',block:'nearest'}); }
  if (c.lat&&c.lng&&markerMap[id]) { map.setView([parseFloat(c.lat),parseFloat(c.lng)],15,{animate:true,duration:.6}); markerMap[id].openPopup(); }
  showDetail(c);
}

function showDetail(c) {
  const addr=encodeURIComponent(c.address+', '+c.city);
  const tickets=parseInt(c.open_count||0)+parseInt(c.pending_count||0);
  document.getElementById('client-detail').style.display='block';
  document.getElementById('cd-title').textContent='🏢 '+c.name;
  document.getElementById('cd-osm').href =`https://www.openstreetmap.org/search?query=${addr}`;
  document.getElementById('cd-gmap').href=`https://www.google.com/maps/search/?api=1&query=${addr}`;
  document.getElementById('cd-waze').href=`https://waze.com/ul?q=${addr}`;
  document.getElementById('cd-link').href=`/techcopy/pages/clients.php?id=${c.id}`;
  document.getElementById('cd-body').innerHTML=`
    <div class="info-grid" style="margin-bottom:14px">
      <div class="info-box"><div class="info-label">Referente</div><div class="info-value">${c.contact||'—'}</div></div>
      <div class="info-box"><div class="info-label">Telefono</div><div class="info-value" style="font-family:var(--mono)">${c.phone||'—'}</div></div>
      <div class="info-box"><div class="info-label">Email</div><div class="info-value" style="font-size:12px">${c.email||'—'}</div></div>
      <div class="info-box" style="grid-column:1/-1"><div class="info-label">Indirizzo</div><div class="info-value">📍 ${c.address}, ${c.city} ${c.zip||''}</div></div>
    </div>
    ${c.notes?`<div class="alert alert-info" style="margin-bottom:12px">📝 ${c.notes}</div>`:''}
    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px">
      ${c.urgent_count>0  ?`<span class="badge badge-urgent">🔴 ${c.urgent_count} urgenti</span>`:''}
      ${c.open_count>0    ?`<span class="badge badge-open">🟠 ${c.open_count} aperti</span>`:''}
      ${c.pending_count>0 ?`<span class="badge badge-pending">🟡 ${c.pending_count} in attesa</span>`:''}
      ${c.closed_count>0  ?`<span class="badge badge-closed">🟢 ${c.closed_count} chiusi</span>`:''}
      ${tickets===0       ?`<span class="badge badge-closed">✅ Nessun intervento aperto</span>`:''}
      <span class="badge badge-supervisor">🖨️ ${c.printer_count} stampanti</span>
    </div>
    ${c.lat&&c.lng?`<span style="font-size:11px;color:var(--text3);font-family:var(--mono)">GPS: ${parseFloat(c.lat).toFixed(5)}, ${parseFloat(c.lng).toFixed(5)}</span>`:'<div class="alert alert-warning" style="font-size:12px">⚠️ Coordinate GPS mancanti.</div>'}
  `;
  document.getElementById('client-detail').scrollIntoView({behavior:'smooth',block:'nearest'});
}
function closeDetail() {
  document.getElementById('client-detail').style.display='none';
  if (selectedId!=null){document.getElementById('client-card-'+selectedId)?.classList.remove('selected');selectedId=null;}
}
function filterList(q) {
  q=q.toLowerCase();
  document.querySelectorAll('.client-list-item').forEach(el=>{el.style.display=el.dataset.name.includes(q)?'':'none';});
}
</script>
<?php layout_footer(); ?>