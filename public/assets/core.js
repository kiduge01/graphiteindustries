'use strict';
const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => [...r.querySelectorAll(s)];
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const safeURL = s => /^(assets\/|uploads\/|https:\/\/)/.test(s || '') ? s : '';
function toast(s) {
  const e = $('#toast');
  e.textContent = s;
  e.classList.add('show');
  setTimeout(() => e.classList.remove('show'), 4000)
}
let csrf = '';
async function api(action, data, method, params = {}) {
  const res = await fetch('api.php?' + new URLSearchParams({ action, ...params }), { method: method || (data ? 'POST' : 'GET'), headers: data ? { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf } : {}, body: data ? JSON.stringify(data) : undefined, credentials: 'same-origin' });
  const out = await res.json();
  if (!res.ok) throw Error(out.error || 'The request could not be completed.');
  if (out.csrf) csrf = out.csrf;
  return out;
}
// Starts the session and obtains the form protection token.
async function init() {
  await api('health');
}
function paragraphs(t) {
  return String(t || '').split(/\n\s*\n/).filter(Boolean).map(p => '<p>' + esc(p).replace(/\n/g, '<br>') + '</p>').join('')
}

