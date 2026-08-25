/* ============================================================
   TechCopy — JavaScript principale v2.0
   ============================================================ */

// ── MODAL ──
function openModal(html) {
  let o = document.getElementById('modal-overlay');
  if (!o) {
    o = document.createElement('div');
    o.id = 'modal-overlay'; o.className = 'modal-overlay';
    o.addEventListener('click', e => { if (e.target === o) closeModal(); });
    document.body.appendChild(o);
  }
  o.innerHTML = html; o.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeModal() {
  const o = document.getElementById('modal-overlay');
  if (o) { o.style.display = 'none'; o.innerHTML = ''; }
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeModal(); closeMobileMenu(); } });

// ── TABS ──
function switchTab(el, tabId) {
  const container = el.closest('.tabs').parentElement;
  container.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  container.querySelectorAll('[data-tab]').forEach(p => {
    p.classList.toggle('hidden', p.dataset.tab !== tabId);
  });
}

// ── TOAST ──
function showToast(msg, type = 'success') {
  const colors = { success:'var(--green)', error:'var(--red)', warning:'var(--orange)', info:'var(--accent)' };
  const t = document.createElement('div');
  t.style.cssText = `position:fixed;bottom:24px;right:24px;background:var(--surface);border:1px solid ${colors[type]||colors.success};border-radius:8px;padding:12px 20px;font-size:14px;z-index:9999;box-shadow:var(--shadow);animation:slideIn .3s ease;color:var(--text);font-family:var(--sans)`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 3500);
}
const _s = document.createElement('style');
_s.textContent = '@keyframes slideIn{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}';
document.head.appendChild(_s);

// ── CONFIRM DELETE ──
function confirmDelete(formId, msg) {
  if (confirm(msg || "Confermi l'eliminazione?")) document.getElementById(formId).submit();
}

// ── SEARCH FILTER (tabella client-side) ──
function filterTable(inputEl, tableId) {
  const q = inputEl.value.toLowerCase();
  document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}

// ── FILE UPLOAD PREVIEW ──
function initFileUpload(inputId, listId) {
  const input = document.getElementById(inputId);
  const list  = document.getElementById(listId);
  if (!input || !list) return;
  input.addEventListener('change', () => {
    list.innerHTML = '';
    Array.from(input.files).forEach(f => {
      const item = document.createElement('div');
      item.className = 'file-item';
      item.innerHTML = `<span>📎</span><span style="flex:1">${f.name}</span><span style="font-size:11px;color:var(--text2);font-family:var(--mono)">${(f.size/1024).toFixed(1)} KB</span>`;
      list.appendChild(item);
    });
  });
}

// ── STOCK ADJUST (AJAX) ──
function adjustStock(id, delta) {
  fetch('/techcopy/api/stock.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({id, delta})
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const el = document.getElementById('stock-'+id);
      if (el) el.textContent = data.stock + ' ' + (data.unit||'pz');
      const fill = document.getElementById('fill-'+id);
      if (fill) fill.style.width = data.pct + '%';
      showToast('Stock aggiornato ✅');
    } else { showToast(data.error||'Errore', 'error'); }
  })
  .catch(() => showToast('Errore di rete', 'error'));
}

// ── PRINTER SELECT (carica per cliente) ──
function loadPrintersByClient(clientId, selectId, selectedId) {
  if (!clientId) return;
  fetch('/techcopy/api/printers_by_client.php?client_id=' + clientId)
    .then(r => r.json())
    .then(printers => {
      const sel = document.getElementById(selectId);
      if (!sel) return;
      sel.innerHTML = '<option value="">— Seleziona stampante —</option>';
      printers.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = p.brand + ' ' + p.model + ' — ' + p.serial;
        if (p.id == selectedId) opt.selected = true;
        sel.appendChild(opt);
      });
    });
}

// ── MENU MOBILE ──
function openMobileMenu() {
  document.getElementById('sidebar')?.classList.add('open');
  document.getElementById('sidebar-backdrop')?.classList.add('visible');
  document.body.style.overflow = 'hidden';
}
function closeMobileMenu() {
  document.getElementById('sidebar')?.classList.remove('open');
  document.getElementById('sidebar-backdrop')?.classList.remove('visible');
  document.body.style.overflow = '';
}
function toggleMenu() {
  document.getElementById('sidebar')?.classList.contains('open') ? closeMobileMenu() : openMobileMenu();
}

// ── TICKET: sync checkbox "risolto" con select status ──
function initResolvedCheckbox() {
  const chk        = document.getElementById('chk-resolved');
  const notice     = document.getElementById('resolved-notice');
  const selVisible = document.getElementById('status-select');
  const hidStatus  = document.getElementById('status-hidden');
  if (!chk) return;

  function sync() {
    if (chk.checked) {
      if (selVisible) { selVisible.value = 'closed'; selVisible.style.opacity = '.5'; selVisible.style.pointerEvents = 'none'; }
      if (hidStatus)  hidStatus.value = 'closed';
      if (notice)     notice.style.display = 'flex';
    } else {
      if (selVisible) { selVisible.style.opacity = ''; selVisible.style.pointerEvents = ''; if (selVisible.value === 'closed') { selVisible.value = 'open'; if (hidStatus) hidStatus.value = 'open'; } }
      if (notice)     notice.style.display = 'none';
    }
  }
  if (selVisible) selVisible.addEventListener('change', () => { if (hidStatus) hidStatus.value = selVisible.value; });
  chk.addEventListener('change', sync);
  sync();
}

// ── AUTO-INIT ──
document.addEventListener('DOMContentLoaded', () => {
  initFileUpload('file-input', 'file-list');
  initResolvedCheckbox();
  // Chiudi menu mobile cliccando su voce di navigazione
  document.querySelectorAll('.sidebar .nav-item').forEach(a => {
    a.addEventListener('click', () => { if (window.innerWidth <= 768) closeMobileMenu(); });
  });
});
