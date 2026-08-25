/* ============================================================
   TechCopy — JavaScript principale
   ============================================================ */

// ---- MODAL ----
function openModal(html) {
  let overlay = document.getElementById('modal-overlay');
  if (!overlay) {
    overlay = document.createElement('div');
    overlay.id = 'modal-overlay';
    overlay.className = 'modal-overlay';
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
    document.body.appendChild(overlay);
  }
  overlay.innerHTML = html;
  overlay.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeModal() {
  const o = document.getElementById('modal-overlay');
  if (o) { o.style.display = 'none'; o.innerHTML = ''; }
  document.body.style.overflow = '';
}
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

// ---- TABS ----
function switchTab(el, tabId) {
  const container = el.closest('.tabs').parentElement;
  container.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  container.querySelectorAll('[data-tab]').forEach(panel => {
    panel.classList.toggle('hidden', panel.dataset.tab !== tabId);
  });
}

// ---- FLASH TOAST ----
function showToast(msg, type = 'success') {
  const t = document.createElement('div');
  const colors = { success: 'var(--green)', error: 'var(--red)', warning: 'var(--orange)', info: 'var(--accent)' };
  t.style.cssText = `position:fixed;bottom:24px;right:24px;background:var(--surface);border:1px solid ${colors[type]||colors.success};border-radius:8px;padding:12px 20px;font-size:14px;z-index:9999;box-shadow:var(--shadow);animation:slideIn .3s ease;color:var(--text);font-family:var(--sans)`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 3500);
}

// ---- CONFIRM DELETE ----
function confirmDelete(formId, msg) {
  if (confirm(msg || 'Confermi l\'eliminazione?')) {
    document.getElementById(formId).submit();
  }
}

// ---- SEARCH FILTER (client-side table) ----
function filterTable(inputEl, tableId) {
  const q = inputEl.value.toLowerCase();
  const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
  rows.forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}

// ---- FILE UPLOAD PREVIEW ----
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

// ---- STOCK ADJUST (AJAX) ----
function adjustStock(id, delta) {
  fetch('/techcopy/api/stock.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id, delta })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      document.getElementById('stock-' + id).textContent = data.stock;
      const fill = document.getElementById('fill-' + id);
      if (fill) fill.style.width = data.pct + '%';
      showToast('Stock aggiornato');
    } else {
      showToast(data.error || 'Errore', 'error');
    }
  })
  .catch(() => showToast('Errore di rete', 'error'));
}

// ---- PRINTER SELECT (load by client) ----
function loadPrintersByClient(clientId, selectId, selectedId) {
  fetch('/techcopy/api/printers_by_client.php?client_id=' + clientId)
    .then(r => r.json())
    .then(printers => {
      const sel = document.getElementById(selectId);
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

// ---- AUTO-INIT ----
document.addEventListener('DOMContentLoaded', () => {
  // File uploads
  initFileUpload('file-input', 'file-list');
  // Highlight active nav from URL
  document.querySelectorAll('.nav-item').forEach(a => {
    if (a.href && window.location.pathname.includes(a.getAttribute('href').split('?')[0])) {
      a.classList.add('active');
    }
  });
});

// ---- CSS ANIM ----
const style = document.createElement('style');
style.textContent = '@keyframes slideIn { from { opacity:0; transform:translateY(14px) } to { opacity:1; transform:translateY(0) } }';
document.head.appendChild(style);

// ---- MENU MOBILE ----
function openMobileMenu() {
  document.querySelector('.sidebar')?.classList.add('open');
  document.getElementById('sidebar-backdrop')?.classList.add('visible');
  document.body.style.overflow = 'hidden';   // blocca scroll pagina
}
function closeMobileMenu() {
  document.querySelector('.sidebar')?.classList.remove('open');
  document.getElementById('sidebar-backdrop')?.classList.remove('visible');
  document.body.style.overflow = '';
}
function toggleMenu() {
  const isOpen = document.querySelector('.sidebar')?.classList.contains('open');
  isOpen ? closeMobileMenu() : openMobileMenu();
}
// Chiudi con tasto ESC
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeMobileMenu();
});
// Chiudi cliccando su un link della sidebar su mobile
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.sidebar .nav-item').forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth <= 768) closeMobileMenu();
    });
  });
});