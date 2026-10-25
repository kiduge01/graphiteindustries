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
let DEMO = false, csrf = '';
const demoKey = 'graphite-design-preview-v1';
async function api(action, data, method, params = {}) {
  if (DEMO) return demoAPI(action, data);
  const res = await fetch('api.php?' + new URLSearchParams({ action, ...params }), { method: method || (data ? 'POST' : 'GET'), headers: data ? { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf } : {}, body: data ? JSON.stringify(data) : undefined, credentials: 'same-origin' });
  const out = await res.json();
  if (!res.ok) throw Error(out.error || 'The request could not be completed.');
  if (out.csrf) csrf = out.csrf;
  return out;
}
async function init() {
  // Only the separately packaged static preview enables demo mode.
  if (document.documentElement.dataset.preview === 'true') {
    DEMO = true;
    let raw = localStorage.getItem(demoKey);
    try {
      window.demo = raw ? JSON.parse(raw) : null
    } catch { }
    if (!window.demo) {
      window.demo = await (await fetch('assets/seed.json')).json();
      Object.assign(window.demo, { enquiries: [], revisions: [], users: [{ id: 1, name: 'Preview Administrator', email: 'preview@example.test', role: 'admin' }] });
      persistDemo()
    }
    return
  }
  await api('health');
}
function persistDemo() {
  localStorage.setItem(demoKey, JSON.stringify(window.demo))
}
async function demoAPI(a, d = {}) {
  let x = window.demo;
  if (a === 'public') return { pages: x.pages.filter(p => p.status === 'published'), settings: x.settings, posts: x.posts.filter(p => p.status === 'published') };
  if (a === 'me') return { user: sessionStorage.getItem('graphite-preview-login') ? x.users[0] : null };
  if (a === 'login') {
    sessionStorage.setItem('graphite-preview-login', '1');
    return { user: x.users[0] }
  }
  if (a === 'logout') {
    sessionStorage.removeItem('graphite-preview-login');
    return { ok: true }
  }
  if (a === 'enquire') {
    throw Error('This is a design preview. Enquiries are enabled after PHP/MySQL installation.')
  }
  if (a === 'track') return { ok: true };
  if (!sessionStorage.getItem('graphite-preview-login')) throw Error('Sign in to the preview first.');
  if (a === 'dashboard') return { ...x, stats: { visitors: 0, pageviews: 0, downloads: 0, daily: [], top: [], sources: [] } };
  if (a === 'savePage') {
    let i = x.pages.findIndex(p => p.id === d.id);
    x.revisions.unshift({ id: Date.now(), page_id: d.id, content: JSON.stringify(x.pages[i]), created_at: new Date().toISOString() });
    x.pages[i] = { ...x.pages[i], ...d };
  }
  if (a === 'savePost') {
    d.id = d.id || Date.now();
    let i = x.posts.findIndex(p => p.id == d.id);
    if (i < 0) x.posts.unshift(d); else x.posts[i] = d;
  }
  if (a === 'deletePost') x.posts = x.posts.filter(p => p.id != d.id);
  if (a === 'settings') x.settings = { ...x.settings, ...d };
  if (a === 'enquiryStatus') {
    let e = x.enquiries.find(e => e.id == d.id);
    if (e) e.status = d.status;
  }
  if (a === 'restore') {
    let r = x.revisions.find(r => r.id == d.id);
    if (r) {
      let i = x.pages.findIndex(p => p.id === r.page_id);
      let current = x.pages[i];
      x.revisions.unshift({ id: Date.now(), page_id: r.page_id, content: JSON.stringify(current), created_at: new Date().toISOString() });
      x.pages[i] = JSON.parse(r.content)
    }
  }
  if (a === 'saveUser' || a === 'password') throw Error('Account changes are available in the installed PHP application.');
  persistDemo();
  return { ok: true };
}
function paragraphs(t) {
  return String(t || '').split(/\n\s*\n/).filter(Boolean).map(p => '<p>' + esc(p).replace(/\n/g, '<br>') + '</p>').join('')
}

