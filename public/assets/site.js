'use strict';
const navigation = [['home', 'Home'], ['about', 'About Us'], ['project', 'Our Project'], ['products', 'Products & Applications'], ['sustainability', 'Sustainability'], ['investors', 'Investors & Partners'], ['news', 'News & Updates'], ['contact', 'Contact Us']];
let content;
const link = (id, text, cls = 'text-link') => `<a class="${cls}" href="?page=${id}">${text} <span>↗</span></a>`;
const stages = [['01', 'Verify the foundation', 'Feedstock, mineral rights and representative sampling.'], ['02', 'Test & evaluate', 'Mineralogy, metallurgical testing and product pathways.'], ['03', 'Define the project', 'Engineering, infrastructure and commercial assessment.'], ['04', 'Advance responsibly', 'Approvals, financing and implementation decisions.']];
const roadmap = () => `<div class="roadmap">${stages.map(s => `<article><span>${s[0]}</span><h3>${s[1]}</h3><p>${s[2]}</p></article>`).join('')}</div>`;
function products() {
  return `<div class="product-grid">${[['Concentrate', 'Separating graphite from surrounding minerals.'], ['Micronised graphite', 'Fine graphite particles for potential industrial applications.'], ['Expandable graphite', 'Specialised processing for graphite that expands when heated.']].map((p, i) => `<article class="product"><div class="product-swatch swatch-${i}" aria-hidden="true"><span>0${i + 1}</span></div><span class="kicker">PATHWAY 0${i + 1}</span><h3>${p[0]}</h3><p>${p[1]}</p><small>Under evaluation</small></article>`).join('')}</div>`
}
function esg() {
  return `<div class="esg-grid">${[['E', 'Environment', 'Water stewardship, energy efficiency, waste management and rehabilitation.'], ['S', 'People & communities', 'Worker safety, local participation, skills and inclusive engagement.'], ['G', 'Governance', 'Accountability, ethical conduct, risk management and regulatory compliance.']].map(x => `<article><span class="esg-letter">${x[0]}</span><h3>${x[1]}</h3><p>${x[2]}</p></article>`).join('')}</div>`
}
function footer() {
  let s = content.settings;
  $('#footer').innerHTML = `<div class="footer-top"><h2>The next chapter<br>starts with a conversation.</h2>${link('contact', 'Start a conversation', 'btn green')}</div><div class="footer-grid"><div><strong>GRAPHITE INDUSTRIES LIMITED</strong><p>Mpwapwa project · Tanzania</p><small>Proposed development. Subject to technical evaluation,<br>applicable approvals and financing.</small></div><div><a href="mailto:${esc(s.email)}">${esc(s.email)}</a><a href="tel:${esc(s.phone.replace(/\s/g, ''))}">${esc(s.phone)}</a></div><div>${link('privacy', 'Privacy notice')}${link('terms', 'Website terms')}<a href="admin.php">Website admin ↗</a></div></div><div class="copyright">© ${new Date().getFullYear()} Graphite Industries Limited <span>Advancing Graphite. Creating Industrial Value.</span></div>`
}
function titleBlock(p) {
  return `<section class="page-heading"><span class="kicker">${esc(p.eyebrow)}</span><h1>${esc(p.title)}</h1><p class="lead">${esc(p.summary)}</p></section>`
}
async function render() {
  let q = new URLSearchParams(location.search), id = q.get('page') || 'home';
  if (id === 'news' && !DEMO) {
    try {
      content = await api('public', undefined, undefined, { newsPage: q.get('newsPage') || '1', ...(q.has('post') ? { post: q.get('post') } : {}) })
    } catch (e) {
      location.href = '?page=unavailable';
      return
    }
  }
  let p = content.pages.find(p => p.id === id);
  $('#site-nav').innerHTML = navigation.filter(n => ['news'].includes(n[0]) || content.pages.some(p => p.id === n[0])).map(n => `<a ${n[0] === id ? 'class="active" aria-current="page"' : ''} href="?page=${n[0]}">${n[1]}<span>↗</span></a>`).join('');
  let html = '';
  if (id === 'home' && p) {
    html = homePage(p);
  } else if (p) {
    html = editorialPage(p, id, q);
  } else if (id === 'news') {
    let post = content.selectedPost || content.posts.find(p => String(p.id) === q.get('post'));
    html = titleBlock({ eyebrow: 'NEWS & PROJECT UPDATES', title: post ? post.title : 'Progress, shared with clarity.', summary: post ? post.summary : 'Company announcements and verified project developments.' });
    if (post) html += `<section class="section article-copy"><div>${post.image ? `<img class="post-photo" src="${esc(safeURL(post.image))}" alt="${esc(post.title)}">` : ''}${paragraphs(post.body)}${link('news', 'All updates')}</div></section>`; else html += `<section class="section">${content.posts.length ? `<div class="news-grid">${content.posts.map(p => `<article><span class="kicker">${esc((p.published_at || '').slice(0, 10))}</span><h2>${esc(p.title)}</h2><p>${esc(p.summary)}</p><a class="text-link" href="?page=news&post=${p.id}">Read update ↗</a></article>`).join('')}</div>` : '<div class="empty editorial"><span class="kicker">KEEP IN TOUCH</span><h2>Our story is developing.</h2><p>There are no published updates yet. Contact us for current project information.</p>' + link('contact', 'Contact our team') + '</div>'}</section>`;
  } else if (id === 'privacy' || id === 'terms') {
    const legal = content.legal?.[id] || { title: id === 'privacy' ? 'Privacy notice.' : 'Website terms.', summary: 'Website information', body: 'Contact our team for details.' };
    html = titleBlock({ ...legal, eyebrow: 'WEBSITE INFORMATION' }) + `<section class="section legal">${paragraphs(legal.body)}${id === 'privacy' ? '<button class="btn secondary" id="reset-consent">Change analytics preference</button>' : ''}</section>`;
  } else {
    html = titleBlock({ eyebrow: 'PAGE UNAVAILABLE', title: 'This page is not published.', summary: 'Use the navigation to explore the available company information.' })
  }
  if (id === 'news' && !q.has('post') && content.newsPagination?.pages > 1) {
    let pg = content.newsPagination;
    html += `<nav class="section pagination" aria-label="News pages">${pg.page > 1 ? `<a class="btn secondary" href="?page=news&newsPage=${pg.page - 1}">Previous</a>` : ''}<span>Page ${pg.page} of ${pg.pages}</span>${pg.page < pg.pages ? `<a class="btn secondary" href="?page=news&newsPage=${pg.page + 1}">Next</a>` : ''}</nav>`
  }
  $('#main').innerHTML = html;
  document.body.dataset.page = id;
  document.title = p?.seo || ((id === 'news' ? (content.selectedPost?.title || 'News & Updates') : (content.legal?.[id]?.title || 'Page not found')) + ' | Graphite Industries');
  footer();
  bind();
  document.body.classList.remove('nav-open');
  $('.mobile-toggle').setAttribute('aria-expanded', 'false');
  $('.mobile-toggle').setAttribute('aria-label', 'Open navigation');
  track('page', id);
}
function contactForm(category) {
  let s = content.settings;
  return `<section class="section contact-layout"><div><h2>Contact our team.</h2><p><a href="mailto:${esc(s.email)}">${esc(s.email)}</a><br><a href="tel:${esc(s.phone.replace(/\s/g, ''))}">${esc(s.phone)}</a></p><p>${esc(s.address)}</p><small>Project location: Mpwapwa District,<br>Dodoma Region, Tanzania.</small></div><form id="contact-form" class="panel"><div class="field-row"><label>Full name<input name="name" required maxlength="120" autocomplete="name"></label><label>Email address<input name="email" type="email" required maxlength="190" autocomplete="email"></label></div><label>Organisation<input name="organisation" maxlength="190" autocomplete="organization"></label><label>Enquiry category<select name="category">${['General enquiry', 'Investment & partnership', 'Product information', 'Technology & engineering', 'Geology & laboratory services', 'Infrastructure & logistics', 'Environmental & social services'].map(x => `<option ${category && x.toLowerCase().includes(category.split(' ')[0].toLowerCase()) ? 'selected' : ''}>${x}</option>`).join('')}</select></label><label>Your message<textarea name="message" rows="5" required minlength="10" maxlength="5000"></textarea></label><label class="honey" aria-hidden="true">Leave empty<input name="website" tabindex="-1" autocomplete="off"></label><label class="check"><input type="checkbox" required> I agree to the <a href="?page=privacy">privacy notice</a> and consent to being contacted about this enquiry.</label><button class="btn green" type="submit">Send enquiry <span>↗</span></button><p id="form-result" role="status" class="fine">Please share non-confidential information only.</p></form></section>`
}
function bind() {
  let f = $('#contact-form');
  if (f) f.onsubmit = async e => {
    e.preventDefault();
    let b = $('button[type=submit]', f);
    b.disabled = true;
    try {
      await api('enquire', Object.fromEntries(new FormData(f)));
      f.reset();
      $('#form-result').textContent = 'Thank you. Your enquiry has been received.'
    } catch (e) {
      $('#form-result').textContent = e.message
    } finally {
      b.disabled = false
    }
  };
  $$('.download').forEach(a => a.onclick = () => track('download', 'corporate-project-brief'));
  $('#reset-consent')?.addEventListener('click', () => {
    localStorage.removeItem('graphite-analytics-consent');
    consent()
  })
}
async function track(kind, page) {
  if (DEMO || localStorage.getItem('graphite-analytics-consent') !== 'yes') return;
  let sid = sessionStorage.getItem('graphite-visit');
  if (!sid) {
    sid = crypto.randomUUID();
    sessionStorage.setItem('graphite-visit', sid)
  }
  try {
    await api('track', { kind, page, sid, referrer: document.referrer })
  } catch { }
}
function consent() {
  if (localStorage.getItem('graphite-analytics-consent')) return;
  $('.consent')?.remove();
  let el = document.createElement('div');
  el.className = 'consent';
  el.innerHTML = '<div><strong>Help us understand what’s useful.</strong><p>Allow optional first-party visitor analytics? No advertising trackers.</p></div><button class="btn secondary" data-value="no">Decline</button><button class="btn green" data-value="yes">Allow analytics</button>';
  document.body.append(el);
  $$('button', el).forEach(b => b.onclick = () => {
    localStorage.setItem('graphite-analytics-consent', b.dataset.value);
    el.remove();
    if (b.dataset.value === 'yes') track('page', new URLSearchParams(location.search).get('page') || 'home')
  })
}
window.onpopstate = render;
$('.mobile-toggle').onclick = () => {
  let open = document.body.classList.toggle('nav-open');
  $('.mobile-toggle').setAttribute('aria-expanded', String(open));
  $('.mobile-toggle').setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation')
};
function closeNavigation() { document.body.classList.remove('nav-open'); $('.mobile-toggle').setAttribute('aria-expanded', 'false'); $('.mobile-toggle').setAttribute('aria-label', 'Open navigation'); }
$('.shade').onclick = closeNavigation;
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeNavigation(); });
(async () => {
  try {
    await init();
    content = await api('public');
    if (DEMO) {
      let b = document.createElement('div');
      b.className = 'preview-banner';
      b.innerHTML = 'INTERACTIVE DESIGN PREVIEW <span>PHP/MySQL features require installation.</span> <a href="admin.html">Explore dashboard ↗</a>';
      document.body.prepend(b);
      document.body.classList.add('is-preview')
    }
    render();
    consent()
  } catch (e) {
    if (!$('#main').textContent.trim()) $('#main').innerHTML = '<section class="page-heading"><h1>Temporarily unavailable.</h1><p>Please try again later.</p></section>';
    console.error(e)
  }
})();

