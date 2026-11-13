document.addEventListener('DOMContentLoaded', () => {
  const btn = document.querySelector('.menu-btn'), menu = document.querySelector('.nav-links');
  if (btn) btn.addEventListener('click', () => menu.classList.toggle('open'));
  document.querySelectorAll('[data-confirm]').forEach(el =>
    el.addEventListener('click', ev => { if (!confirm(el.dataset.confirm)) ev.preventDefault(); }));
  const fl = document.querySelector('.flash'); if (fl) setTimeout(() => fl.remove(), 5000);
  document.querySelectorAll('[data-filter]').forEach(inp => inp.addEventListener('input', () => {
    const q = inp.value.toLowerCase();
    document.querySelectorAll(inp.dataset.filter + ' tbody tr').forEach(r =>
      r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none');
  }));
  // live points preview on the Log Waste form
  const w = document.getElementById('weight'), s = document.getElementById('seg'), p = document.getElementById('pts');
  if (w && s && p) {
    const upd = () => { p.textContent = s.value === '1' ? 10 + Math.round(parseFloat(w.value) || 0) : 2; };
    w.addEventListener('input', upd); s.addEventListener('change', upd); upd();
  }
});
