'use strict';
let user, state, tab = 'overview';
const sections = [['overview', '◫', 'Overview'], ['pages', '▤', 'Pages'], ['posts', '▧', 'News & Updates'], ['media', '▨', 'Media Library'], ['enquiries', '✉', 'Enquiries'], ['analytics', '▥', 'Analytics'], ['users', '♧', 'Users & Roles'], ['settings', '⚙', 'Settings']];
const status = s => `<span class="badge ${esc(s)}">${esc(s)}</span>`;
function openModal(html) {
  $('#modal-body').innerHTML = html;
  $('#modal').showModal()
}
$('.dialog-close').onclick = () => $('#modal').close();
async function start() {
  try {
    await init();
    let m = await api('me');
    user = m.user;
    if (!user) return login();
    await load();
    shell()
  } catch (e) {
    $('#admin-root').innerHTML = `<div class="login-form"><h2>Installation required</h2><p>Configure the database and run the installation command from README.md.</p><p class="error">${esc(e.message)}</p></div>`
  }
}
function login() {
  $('#admin-root').innerHTML = `<div class="login-page"><div class="login-art"><a class="brand" href="index.php"><img src="assets/logo.png" alt="Company logo"><span>GRAPHITE<small>INDUSTRIES LIMITED</small></span></a><div><span class="kicker">WEBSITE MANAGEMENT</span><h1>A clear view.<br>A confident next step.</h1><p>Manage your company’s story, content and conversations.</p></div><small>Graphite Industries Limited</small></div><form class="login-form" id="login-form"><span class="kicker">YOUR PUBLISHING WORKSPACE</span><h2>Welcome back.</h2><p class="muted">Sign in to manage the website.</p>${DEMO ? '<div class="demo-notice">DESIGN PREVIEW · Browser-local content only. No real accounts, messages or visitor statistics.</div>' : '<label>Email address<input type="email" name="email" autocomplete="username" required></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label>'}<button class="btn green">${DEMO ? 'Enter demo dashboard ↗' : 'Sign in ↗'}</button><p class="error" id="login-error" role="alert"></p><p class="fine">${DEMO ? 'Edits in this preview stay in this browser.' : 'Access is restricted to authorised company staff.'}</p><a class="text-link" href="${DEMO ? 'index.html' : 'index.php'}">← Back to website</a></form></div>`;
  $('#login-form').onsubmit = async e => {
    e.preventDefault();
    const b = $('button', e.target);
    b.disabled = true;
    try {
      let r = await api('login', Object.fromEntries(new FormData(e.target)));
      user = r.user;
      await load();
      shell()
    } catch (e) {
      $('#login-error').textContent = e.message
    } finally {
      b.disabled = false
    }
  }
}
async function load() {
  state = await api('dashboard')
}
function shell() {
  let nav = sections.filter(s => user.role === 'admin' || !['users', 'settings'].includes(s[0]));
  $('#admin-root').innerHTML = `<aside class="sidebar"><a class="brand" href="${DEMO ? 'index.html' : 'index.php'}"><img src="assets/logo.png" alt="Company logo"><span>GRAPHITE<small>INDUSTRIES LIMITED</small></span></a><div class="nav-label">CONTENT & MANAGEMENT</div><nav aria-label="Admin navigation">${nav.map(s => `<a href="#${s[0]}" data-tab="${s[0]}"><div><i>${s[1]}</i>${s[2]}</div><span>${s[0] === 'enquiries' ? (state.stats.new_enquiries ?? state.enquiries.filter(e => e.status === 'new').length) : ''}</span></a>`).join('')}</nav><div class="side-bottom"><a href="${DEMO ? 'index.html' : 'index.php'}" target="_blank">View website ↗</a><button id="logout">Sign out</button></div></aside><div class="admin-wrap"><header class="admin-top"><span>Workspace / <b id="breadcrumb">Overview</b></span><button class="mobile-admin">☰ Menu</button><span><span class="avatar">${esc(user.name.slice(0, 2).toUpperCase())}</span>${esc(user.name)} · ${esc(user.role)}</span></header><main id="admin-main"></main><footer class="admin-foot">GRAPHITE INDUSTRIES LIMITED <span>Website management workspace</span></footer></div>`;
  $$('[data-tab]').forEach(a => a.onclick = e => {
    e.preventDefault();
    location.hash = a.dataset.tab;
    go(a.dataset.tab)
  });
  $('#logout').onclick = async () => {
    await api('logout', {});
    user = null;
    login()
  };
  $('.sidebar').id = 'admin-sidebar';
  const menu = $('.mobile-admin');
  menu.setAttribute('aria-controls', 'admin-sidebar');
  menu.setAttribute('aria-expanded', 'false');
  menu.onclick = () => menu.setAttribute('aria-expanded', String($('.sidebar').classList.toggle('open')));
  go(location.hash.slice(1) || 'overview')
}
function heading(title, sub, actions = '') {
  return `<div class="admin-heading"><div><h1>${title}</h1><p>${sub}</p></div>${actions}</div>${DEMO ? '<div class="demo-notice">DESIGN PREVIEW · Edits are browser-local. Analytics and enquiries are not connected.</div>' : ''}`
}
function chart() {
  let d = state.stats.daily;
  if (!d.length) return '<div class="chart"><div class="chart-empty">No consented visitor activity in the last 30 days.</div></div>';
  let max = Math.max(...d.map(x => +x.views), 1);
  let points = d.map((x, i) => `${20 + i * (760 / Math.max(d.length - 1, 1))},${190 - x.views / max * 165}`).join(' ');
  return `<div class="chart"><svg viewBox="0 0 800 210" role="img" aria-label="Daily page views in the last 30 days"><defs><linearGradient id="area" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#174A3A" stop-opacity=".18"/><stop offset="100%" stop-color="#174A3A" stop-opacity="0"/></linearGradient></defs><polygon points="20,205 ${points} 780,205" fill="url(#area)"/><polyline points="${points}" fill="none" stroke="#174A3A" stroke-width="3"/></svg></div><div class="chart-labels"><span>${esc(d[0].day)}</span><span>Daily page views</span><span>${esc(d[d.length - 1].day)}</span></div>`
}
function contentTable(pages = state.pages) {
  return `<div class="table-wrap"><table><thead><tr><th>Title</th><th>Status</th><th>Page</th><th>Action</th></tr></thead><tbody>${pages.map(p => `<tr><td>${esc(p.title.slice(0, 52))}</td><td>${status(p.status)}</td><td>${esc(p.id)}</td><td><button class="table-btn edit-page" data-id="${esc(p.id)}">Edit ↗</button></td></tr>`).join('')}</tbody></table></div>`
}
function overview() {
  let stats = state.stats;
  return heading('Your website, at a glance.', 'Content, conversations and performance in one place.', '<button class="btn green" id="create-post">+ Create update</button>') + `<div class="metric-grid">${[['Visitors', stats.visitors, 'Consented sessions · 30 days', '♧'], ['Page views', stats.pageviews, 'Last 30 days', '▥'], ['New enquiries', (state.stats.new_enquiries ?? state.enquiries.filter(e => e.status === 'new').length), 'Awaiting review', '✉'], ['Published pages', state.pages.filter(p => p.status === 'published').length, 'Website content', '▤']].map(m => `<article class="metric"><span class="metric-icon">${m[3]}</span><label>${m[0]}</label><strong>${Number(m[1]).toLocaleString()}</strong><small>${m[2]}</small></article>`).join('')}</div><div class="dashboard-grid"><section class="panel"><div class="panel-title"><h2>Audience overview</h2><small>Last 30 days</small></div>${chart()}</section><section class="panel"><div class="panel-title"><h2>Publishing workspace</h2></div><div class="quick">${[['pages', '▤', 'Edit website pages', 'Update text, images and page details.'], ['posts', '✎', 'Publish an update', 'Draft and share company news.'], ['media', '↑', 'Upload photos & PDFs', 'Keep your resources organised.'], ['enquiries', '✉', 'Review enquiries', 'Follow up with potential partners.']].map(x => `<a href="#${x[0]}" data-go="${x[0]}"><b>${x[1]}</b><div><strong>${x[2]}</strong><small>${x[3]}</small></div><em>›</em></a>`).join('')}</div></section></div><div class="dashboard-grid"><section class="panel"><div class="panel-title"><h2>Recent content</h2><a href="#pages" data-go="pages">View all pages ↗</a></div>${contentTable(state.pages.slice(0, 4))}</section><section class="panel"><div class="panel-title"><h2>Enquiry inbox</h2><a href="#enquiries" data-go="enquiries">View all ↗</a></div>${state.enquiries.length ? state.enquiries.slice(0, 4).map(e => `<div class="revision-list"><div><span><strong>${esc(e.name)}</strong><br><small>${esc(e.category)}</small></span>${status(e.status)}</div></div>`).join('') : '<div class="empty"><p>No enquiries yet.</p><small>Submitted website enquiries will appear here.</small></div>'}</section></div>`
}
function renderTab(id) {
  tab = sections.some(s => s[0] === id) ? id : 'overview';
  $$('[data-tab]').forEach(a => {
    const active = a.dataset.tab === tab;
    a.classList.toggle('active', active);
    if (active) a.setAttribute('aria-current', 'page');
    else a.removeAttribute('aria-current');
  });
  $('#breadcrumb').textContent = sections.find(s => s[0] === tab)[2];
  $('.sidebar').classList.remove('open');
  $('.mobile-admin').setAttribute('aria-expanded', 'false');
  let h = '';
  if (tab === 'overview') h = overview();
  if (tab === 'pages') h = heading('Website pages.', 'Edit the content that tells your company’s story.') + '<section class="panel"><div class="toolbar"><input id="page-search" placeholder="Search pages…" aria-label="Search pages"></div><div id="page-table">' + contentTable() + '</div></section>';
  if (tab === 'posts') h = heading('News & updates.', 'Draft, preview and publish company announcements.', '<button class="btn green" id="create-post">+ Create update</button>') + `<section class="panel">${state.posts.length ? `<div class="table-wrap"><table><thead><tr><th>Title</th><th>Status</th><th>Action</th></tr></thead><tbody>${state.posts.map(p => `<tr><td>${esc(p.title)}</td><td>${status(p.status)}</td><td><button class="table-btn edit-post" data-id="${p.id}">Edit</button> <button class="table-btn delete-post" data-id="${p.id}">Delete</button></td></tr>`).join('')}</tbody></table></div>` : '<div class="empty"><h2>Your next update starts here.</h2><p>Create a draft when there is approved information to share.</p><button class="btn green" id="first-post">Create your first update</button></div>'}</section>`;
  if (tab === 'media') h = heading('Media library.', 'Images and documents ready for your website.') + `<form id="upload-form" class="upload-box"><label>Upload an image or PDF<input type="file" name="file" accept="image/jpeg,image/png,image/webp,application/pdf" required></label><button class="btn green">Upload file ↑</button><small>JPEG, PNG, WebP or PDF · Maximum 8 MB. Files are public when uploaded.</small></form><div class="media-grid">${state.media.map(m => `<article class="media-tile">${m.type.startsWith('image/') ? `<img src="${esc(safeURL(m.url))}" alt="${esc(m.name)}" loading="lazy">` : '<div class="media-file">PDF</div>'}<div><strong>${esc(m.name)}</strong><small>${esc(m.type)}</small><a href="${esc(safeURL(m.url))}" target="_blank" rel="noopener">Open file ↗</a><br><button class="table-btn copy-url" data-url="${esc(m.url)}">Copy path</button></div></article>`).join('')}</div>`;
  if (tab === 'enquiries') h = heading('Enquiry inbox.', 'Review and follow up on business conversations.') + `<section class="panel"><div class="toolbar"><input id="enquiry-search" placeholder="Search enquiries…" aria-label="Search enquiries"><select id="enquiry-filter" aria-label="Filter status"><option value="">All statuses</option><option>new</option><option>progress</option><option>resolved</option></select><button class="btn secondary" id="export-enquiries">Export this page as CSV ↓</button></div><div id="enquiry-table"></div></section>`;
  if (tab === 'analytics') h = heading('Visitor analytics.', 'First-party, consent-based measurement. No stored IP addresses.') + `<div class="metric-grid">${[['Visitors', state.stats.visitors], ['Page views', state.stats.pageviews], ['Document clicks', state.stats.downloads], ['Enquiries', state.stats.enquiries ?? state.enquiries.length]].map(m => `<div class="metric"><label>${m[0]}</label><strong>${m[1]}</strong><small>${m[0] === 'Enquiries' ? 'All time' : 'Last 30 days'}</small></div>`).join('')}</div><section class="panel"><div class="panel-title"><h2>Daily page views</h2><small>Last 30 days</small></div>${chart()}</section><div class="dashboard-grid" style="margin-top:22px">${[['Top pages', state.stats.top, 'page'], ['Traffic sources', state.stats.sources, 'source']].map(t => `<section class="panel"><div class="panel-title"><h2>${t[0]}</h2></div>${t[1].length ? `<table><thead><tr><th>Name</th><th>Views</th></tr></thead><tbody>${t[1].map(r => `<tr><td>${esc(r[t[2]] || 'Direct')}</td><td>${r.views}</td></tr>`).join('')}</tbody></table>` : '<p class="fine">No recorded activity yet.</p>'}</section>`).join('')}</div><p class="fine">Visitors are browser-session identifiers, not verified unique people. Country reporting is not enabled. Counts exclude visitors who decline analytics.</p>`;
  if (tab === 'users') h = heading('Users & roles.', 'Administrators manage access. Editors manage content.', user.role === 'admin' ? '<button class="btn green" id="add-user">+ Add user</button>' : '') + `<section class="panel"><div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Action</th></tr></thead><tbody>${state.users.map(u => `<tr><td>${esc(u.name)}</td><td>${esc(u.email)}</td><td>${status(u.role)}</td><td>${status(Number(u.active ?? 1) ? 'active' : 'inactive')}</td><td>${u.id !== user.id ? `<button class="table-btn manage-user" data-id="${u.id}">Manage access</button>` : 'Current account'}</td></tr>`).join('')}</tbody></table></div></section>`;
  if (tab === 'settings') h = heading('Website settings.', 'Company details, account security and recovery.') + `<div class="editor-grid"><form class="panel" id="settings-form"><h3>Company information</h3>${Object.entries(state.settings).map(([k, v]) => `<label>${esc(k.charAt(0).toUpperCase() + k.slice(1))}<input name="${esc(k)}" value="${esc(v)}" ${k === 'email' ? 'type="email"' : ''} required maxlength="500"></label>`).join('')}<button class="btn green">Save settings</button></form><div><form class="panel" id="password-form"><h3>Change your password</h3><label>Current password<input type="password" name="current" autocomplete="current-password" required></label><label>New password<input type="password" name="password" autocomplete="new-password" minlength="12" required></label><button class="btn secondary">Update password</button></form><section class="panel" style="margin-top:20px"><h3>Content backup</h3><p class="fine">Export content, settings and enquiries as JSON. Store exports securely; they contain personal enquiry data. Media files require a separate hosting backup.</p><button class="btn secondary" id="backup">Export content backup ↓</button>${DEMO ? '<button class="btn danger" id="reset-demo" style="margin-top:15px">Reset demo content</button>' : ''}</section></div></div>`;
  $('#admin-main').innerHTML = h;
  bindAdmin();
}
function bindAdmin() {
  $$('[data-go]').forEach(a => a.onclick = e => {
    e.preventDefault();
    go(a.dataset.go)
  });
  $$('.edit-page').forEach(b => b.onclick = () => editPage(b.dataset.id));
  $('#page-search')?.addEventListener('input', e => {
    $('#page-table').innerHTML = contentTable(state.pages.filter(p => (p.title + ' ' + p.id).toLowerCase().includes(e.target.value.toLowerCase())));
    $$('.edit-page').forEach(b => b.onclick = () => editPage(b.dataset.id))
  });
  $('#create-post')?.addEventListener('click', () => editPost());
  $('#first-post')?.addEventListener('click', () => editPost());
  $$('.edit-post').forEach(b => b.onclick = () => editPost(b.dataset.id));
  $$('.delete-post').forEach(b => b.onclick = () => {
    openModal('<h2>Delete this update?</h2><p>This removes the update from the website.</p><button class="btn danger" id="confirm-delete">Delete update</button>');
    $('#confirm-delete').onclick = async () => {
      await mutate('deletePost', { id: b.dataset.id });
      $('#modal').close()
    }
  });
  $$('.copy-url').forEach(b => b.onclick = async () => {
    try {
      await navigator.clipboard.writeText(b.dataset.url);
      toast('File path copied.')
    } catch {
      openModal('<h2>File path</h2><input readonly value="' + esc(b.dataset.url) + '">')
    }
  });
  $('#upload-form')?.addEventListener('submit', async e => {
    e.preventDefault();
    if (DEMO) return toast('Uploads are enabled after PHP installation.');
    let b = $('button', e.target);
    b.disabled = true;
    try {
      let r = await fetch('api.php?action=upload', { method: 'POST', headers: { 'X-CSRF-Token': csrf }, body: new FormData(e.target), credentials: 'same-origin' });
      let out = await r.json();
      if (!r.ok) throw Error(out.error);
      await load();
      go('media');
      toast('File uploaded.')
    } catch (e) {
      toast(e.message)
    } finally {
      b.disabled = false
    }
  });
  if (tab === 'enquiries') {
    renderEnquiries();
    $('#enquiry-search').oninput = () => scheduleEnquirySearch();
    $('#enquiry-filter').onchange = () => searchEnquiries();
    $('#export-enquiries').onclick = () => {
      let rows = [['Name', 'Email', 'Organisation', 'Category', 'Message', 'Status', 'Date'], ...filteredEnquiries().map(e => [e.name, e.email, e.organisation, e.category, e.message, e.status, e.created_at])];
      download('graphite-enquiries.csv', rows.map(r => r.map(c => '"' + String(c ?? '').replace(/^[=+@\-]/, "'$&").replace(/"/g, '""') + '"').join(',')).join('\r\n'), 'text/csv')
    }
  }
  $('#settings-form')?.addEventListener('submit', async e => {
    e.preventDefault();
    await mutate('settings', Object.fromEntries(new FormData(e.target)))
  });
  $('#password-form')?.addEventListener('submit', async e => {
    e.preventDefault();
    try {
      await api('password', Object.fromEntries(new FormData(e.target)));
      e.target.reset();
      toast('Password updated.')
    } catch (e) {
      toast(e.message)
    }
  });
  $('#backup')?.addEventListener('click', async () => {
    try {
      const out = DEMO ? state : await api('export');
      download('graphite-content-backup.json', JSON.stringify(out, null, 2), 'application/json')
    } catch (e) {
      toast(e.message)
    }
  });
  $('#reset-demo')?.addEventListener('click', () => {
    localStorage.removeItem(demoKey);
    location.reload()
  });
  $('#add-user')?.addEventListener('click', () => {
    openModal('<h2>Add a team member</h2><form id="user-form"><label>Name<input name="name" required maxlength="120"></label><label>Email<input name="email" type="email" required></label><label>Role<select name="role"><option value="editor">Editor</option><option value="admin">Administrator</option></select></label><label>Initial password<input name="password" type="password" minlength="12" required autocomplete="new-password"></label><button class="btn green">Create user</button></form>');
    $('#user-form').onsubmit = async e => {
      e.preventDefault();
      try {
        await api('saveUser', Object.fromEntries(new FormData(e.target)));
        $('#modal').close();
        await load();
        go('users');
        toast('User created.')
      } catch (e) {
        toast(e.message)
      }
    }
  })
}
async function mutate(a, d) {
  try {
    await api(a, d);
    await load();
    await go(tab);
    toast('Changes saved.');
    return true
  } catch (e) {
    toast(e.message);
    return false
  }
}
function editPage(id) {
  let p = state.pages.find(p => p.id === id);
  $('#admin-main').innerHTML = heading('Edit ' + (id === 'home' ? 'homepage' : id) + '.', 'Changes appear on the website when published.', '<button class="btn secondary" id="back-pages">← All pages</button>') + `<form id="editor-form" class="editor-grid"><section class="panel"><label>Eyebrow<input name="eyebrow" value="${esc(p.eyebrow)}" maxlength="160"></label><label>Page title<input name="title" value="${esc(p.title)}" required maxlength="200"></label><label>Summary<textarea name="summary" maxlength="1000">${esc(p.summary)}</textarea></label><label>Body text<textarea class="body-editor" name="body" maxlength="30000">${esc(p.body)}</textarea></label><small>Plain text. Separate paragraphs with a blank line. HTML is displayed as text.</small></section><aside class="editor-side"><section class="panel"><h3>Publishing</h3><label>Status<select name="status"><option value="published" ${p.status === 'published' ? 'selected' : ''}>Published</option><option value="draft" ${p.status === 'draft' ? 'selected' : ''}>Draft</option></select></label><button class="btn green" type="submit">Save page</button><button class="btn secondary" type="button" id="preview-page">Preview draft ↗</button><p class="fine">Saving as Draft removes this page from public navigation.</p><button class="table-btn" type="button" id="revisions">Revision history</button></section><section class="panel"><h3>Hero image</h3>${p.image ? `<img class="image-preview" src="${esc(safeURL(p.image))}" alt="Current page image">` : ''}<label>Image path<select name="image"><option value="">No image</option>${state.media.filter(m => m.type.startsWith('image/')).map(m => `<option value="${esc(m.url)}" ${p.image === m.url ? 'selected' : ''}>${esc(m.name)}</option>`).join('')}</select></label><small>Select an image from the media library.</small></section><section class="panel"><h3>Search appearance</h3><label>SEO title<input name="seo" value="${esc(p.seo)}" maxlength="200"></label><small>The summary is used as the meta description.</small></section></aside></form>`;
  $('#back-pages').onclick = () => go('pages');
  attachImagePicker(p.image);
  $('#editor-form').onsubmit = async e => {
    e.preventDefault();
    await mutate('savePage', { id, ...Object.fromEntries(new FormData(e.target)) })
  };
  $('#preview-page').onclick = () => {
    let d = Object.fromEntries(new FormData($('#editor-form')));
    openModal(`<span class="kicker">UNPUBLISHED CONTENT PREVIEW</span><h2>${esc(d.title)}</h2>${d.image ? `<img class="image-preview" src="${esc(safeURL(d.image))}" alt="Draft image">` : ''}<p>${esc(d.summary)}</p>${paragraphs(d.body)}`)
  };
  $('#revisions').onclick = () => {
    let rev = state.revisions.filter(r => r.page_id === id);
    openModal(`<h2>Revision history</h2><p>Restoring a revision saves the current version in history first.</p><div class="revision-list">${rev.length ? rev.map(r => `<div><span>${esc(r.created_at)}</span><button class="table-btn restore" data-id="${r.id}">Restore</button></div>`).join('') : '<p class="fine">No earlier revisions yet.</p>'}</div>`);
    $$('.restore').forEach(b => b.onclick = async () => {
      if (await mutate('restore', { id: b.dataset.id })) {
        $('#modal').close();
        editPage(id)
      }
    })
  }
}
function editPost(id) {
  let p = state.posts.find(p => p.id == id) || { title: '', summary: '', body: '', image: '', status: 'draft' };
  openModal(`<h2>${id ? 'Edit update' : 'Create an update'}</h2><form id="post-form"><label>Title<input name="title" value="${esc(p.title)}" required maxlength="200"></label><label>Summary<textarea name="summary" maxlength="1000">${esc(p.summary)}</textarea></label><label>Body<textarea name="body" rows="9" required maxlength="30000">${esc(p.body)}</textarea></label><label>Image<select name="image"><option value="">No image</option>${state.media.filter(m => m.type.startsWith('image/')).map(m => `<option value="${esc(m.url)}" ${p.image === m.url ? 'selected' : ''}>${esc(m.name)}</option>`).join('')}</select></label><label>Status<select name="status"><option value="draft" ${p.status === 'draft' ? 'selected' : ''}>Draft</option><option value="published" ${p.status === 'published' ? 'selected' : ''}>Published</option></select></label><button class="btn green">Save update</button></form>`);
  attachImagePicker(p.image);
  $('#post-form').onsubmit = async e => {
    e.preventDefault();
    if (await mutate('savePost', { id: id || null, ...Object.fromEntries(new FormData(e.target)) })) $('#modal').close()
  }
}
function filteredEnquiries() {
  let s = ($('#enquiry-search')?.value || '').toLowerCase(), f = $('#enquiry-filter')?.value;
  return state.enquiries.filter(e => (!f || e.status === f) && (e.name + ' ' + e.email + ' ' + e.category + ' ' + e.message).toLowerCase().includes(s))
}
function renderEnquiries() {
  let rows = filteredEnquiries();
  $('#enquiry-table').innerHTML = rows.length ? `<div class="table-wrap"><table><thead><tr><th>From</th><th>Category</th><th>Status</th><th>Received</th><th>Action</th></tr></thead><tbody>${rows.map(e => `<tr><td>${esc(e.name)}<small>${esc(e.email)}</small></td><td>${esc(e.category)}</td><td>${status(e.status)}</td><td>${esc(e.created_at)}</td><td><button class="table-btn read-enquiry" data-id="${e.id}">Open</button></td></tr>`).join('')}</tbody></table></div>` : '<div class="empty"><p>No matching enquiries.</p></div>';
  $$('.read-enquiry').forEach(b => b.onclick = () => {
    let e = state.enquiries.find(e => e.id == b.dataset.id);
    openModal(`<h2>${esc(e.category)}</h2><p><strong>${esc(e.name)}</strong> · ${esc(e.organisation)}<br><a class="text-link" href="mailto:${esc(e.email)}">${esc(e.email)}</a></p><div class="message-body">${esc(e.message)}</div><label>Status<select id="message-status">${['new', 'progress', 'resolved'].map(s => `<option ${e.status === s ? 'selected' : ''}>${s}</option>`).join('')}</select></label><button class="btn green" id="save-status">Save status</button>`);
    $('#save-status').onclick = async () => {
      if (await mutate('enquiryStatus', { id: e.id, status: $('#message-status').value })) $('#modal').close()
    }
  })
}
function download(name, text, type) {
  let a = document.createElement('a'), url = URL.createObjectURL(new Blob([text], { type }));
  a.href = url;
  a.download = name;
  a.click();
  setTimeout(() => URL.revokeObjectURL(url), 1000)
}
let navigationVersion = 0, enquiryTimer;
const collectionPages = { posts: 1, media: 1, enquiries: 1, users: 1 };
const collectionQueries = {};

async function fetchCollection(resource, page = 1, search = '', status = '') {
  if (DEMO) return { items: state[resource], page: 1, pages: 1, total: state[resource].length };
  return api('list', undefined, undefined, { resource, page: String(page), search, status });
}

async function go(id) {
  const version = ++navigationVersion;
  const resource = ['posts', 'media', 'enquiries', 'users'].includes(id) ? id : null;
  if (resource && id === 'users' && user.role !== 'admin') id = 'overview';
  try {
    if (resource && id !== 'overview') {
      const query = collectionQueries[resource] || { search: '', status: '' };
      const result = await fetchCollection(resource, collectionPages[resource], query.search, query.status);
      if (version !== navigationVersion) return;
      state[resource] = result.items;
      state.pagination ??= {};
      state.pagination[resource] = result;
      collectionPages[resource] = result.page;
    }
    if (version !== navigationVersion) return;
    renderTab(id);
    if (resource && id !== 'overview') {
      const query = collectionQueries[resource] || { search: '', status: '' };
      if (resource === 'enquiries') {
        $('#enquiry-search').value = query.search;
        $('#enquiry-filter').value = query.status;
        renderEnquiries();
      } else {
        const toolbar = document.createElement('form');
        toolbar.className = 'toolbar';
        toolbar.innerHTML = `<label>Search ${esc(resource)}<input name="search" value="${esc(query.search)}" maxlength="190"></label><button class="btn secondary">Search</button>`;
        $('.admin-heading').after(toolbar);
        toolbar.onsubmit = e => {
          e.preventDefault();
          collectionQueries[resource] = { search: new FormData(toolbar).get('search'), status: '' };
          collectionPages[resource] = 1;
          go(resource)
        };
      }
      drawPager(resource);
    }
    $$('.manage-user').forEach(button => button.onclick = () => manageUser(Number(button.dataset.id)));
  } catch (e) {
    toast(e.message)
  }
}

function drawPager(resource) {
  $('#collection-pager')?.remove();
  const p = state.pagination?.[resource];
  if (!p) return;
  const nav = document.createElement('nav');
  nav.id = 'collection-pager';
  nav.className = 'pagination';
  nav.setAttribute('aria-label', resource + ' pages');
  nav.innerHTML = `<button class="btn secondary" data-page="${p.page - 1}" ${p.page <= 1 ? 'disabled' : ''}>Previous</button><span>Page ${p.page} of ${p.pages} · ${p.total} records</span><button class="btn secondary" data-page="${p.page + 1}" ${p.page >= p.pages ? 'disabled' : ''}>Next</button>`;
  $('#admin-main').append(nav);
  $$('button', nav).forEach(b => b.onclick = () => {
    collectionPages[resource] = Number(b.dataset.page);
    go(resource)
  });
}

function scheduleEnquirySearch() {
  clearTimeout(enquiryTimer);
  enquiryTimer = setTimeout(searchEnquiries, 300)
}
async function searchEnquiries() {
  const search = $('#enquiry-search').value, status = $('#enquiry-filter').value;
  collectionQueries.enquiries = { search, status };
  collectionPages.enquiries = 1;
  const version = ++navigationVersion;
  try {
    const result = await fetchCollection('enquiries', 1, search, status);
    if (version !== navigationVersion || tab !== 'enquiries') return;
    state.enquiries = result.items;
    state.pagination.enquiries = result;
    renderEnquiries();
    drawPager('enquiries');
  } catch (e) {
    toast(e.message)
  }
}

function manageUser(id) {
  const target = state.users.find(u => Number(u.id) === id);
  openModal(`<h2>Manage ${esc(target.name)}</h2><form id="access-form"><label>Role<select name="role"><option value="admin" ${target.role === 'admin' ? 'selected' : ''}>Administrator</option><option value="editor" ${target.role === 'editor' ? 'selected' : ''}>Editor</option></select></label><label class="check"><input type="checkbox" name="active" ${Number(target.active ?? 1) ? 'checked' : ''}> Account active</label><p>Saving revokes the account's existing sessions.</p><button class="btn green">Save access</button></form>`);
  $('#access-form').onsubmit = async e => {
    e.preventDefault();
    const form = new FormData(e.target);
    if (await mutate('updateUser', { id, role: form.get('role'), active: form.has('active') })) $('#modal').close()
  };
}

function attachImagePicker(current) {
  if (DEMO) return;
  const select = $('select[name=image]');
  if (!select) return;
  if (current && ![...select.options].some(o => o.value === current)) select.add(new Option(current, current, true, true));
  const controls = document.createElement('div');
  controls.className = 'image-picker';
  controls.innerHTML = '<label>Find image<input type="search" placeholder="Search image names" maxlength="190"></label><button type="button" class="table-btn find-images">Find</button><button type="button" class="table-btn more-images" disabled>Next image results</button><p class="fine image-count" role="status"></p>';
  select.after(controls);
  let page = 1, pages = 1;
  const search = async () => {
    try {
      const result = await api('list', undefined, undefined, { resource: 'media', images: '1', search: $('input', controls).value, page: String(page) });
      pages = result.pages;
      page = result.page;
      const selected = select.value;
      select.replaceChildren(new Option('No image', ''));
      if (selected) select.add(new Option(selected, selected, true, true));
      for (const item of result.items) if (item.url !== selected) select.add(new Option(item.name, item.url));
      $('.more-images', controls).disabled = page >= pages;
      $('.image-count', controls).textContent = `Image results ${page} of ${pages} · ${result.total} images`;
    } catch (e) {
      toast(e.message)
    }
  };
  $('.find-images', controls).onclick = () => {
    page = 1;
    search()
  };
  $('.more-images', controls).onclick = () => {
    if (page < pages) {
      page++;
      search()
    }
  };
}

window.addEventListener('hashchange', () => {
  if (user && location.hash.slice(1) !== tab) go(location.hash.slice(1))
});
start();

