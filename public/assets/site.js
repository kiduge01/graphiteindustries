'use strict';
const navigation = [['home', 'Home'], ['about', 'About Us'], ['project', 'Our Project'], ['products', 'Products & Applications'], ['sustainability', 'Sustainability'], ['investors', 'Investors & Partners'], ['news', 'News & Updates'], ['contact', 'Contact Us']];
let content;
const link = (id, text, cls = 'text-link') => `<a class="${cls}" href="?page=${id}">${text} <span>↗</span></a>`;
function originalHomepageFooter() {
  let s = content.settings;
  $('#footer').innerHTML = `<div class="footer-top"><h2>The next chapter<br>starts with a conversation.</h2>${link('contact', 'Start a conversation', 'btn green')}</div><div class="footer-grid"><div><strong>GRAPHITE INDUSTRIES LIMITED</strong><p>Mpwapwa project · Tanzania</p><small>Proposed development. Subject to technical evaluation,<br>applicable approvals and financing.</small></div><div><a href="mailto:${esc(s.email)}">${esc(s.email)}</a><a href="tel:${esc(s.phone.replace(/\s/g, ''))}">${esc(s.phone)}</a></div><div>${link('privacy', 'Privacy notice')}${link('terms', 'Website terms')}<a href="admin.php">Website admin ↗</a></div></div><div class="copyright">© ${new Date().getFullYear()} Graphite Industries Limited <span>Advancing Graphite. Creating Industrial Value.</span></div>`
}
function footer() {
  if (document.body.dataset.page === 'home') return originalHomepageFooter();
  let s = content.settings;
  $('#footer').innerHTML = `<div class="footer-top"><strong>${esc(s.company)}</strong><span>${esc(s.tagline)}</span></div><div class="footer-grid"><div><p>Mpwapwa project · Tanzania</p><small>Proposed development, subject to technical evaluation, applicable approvals and financing.</small></div><div><a href="mailto:${esc(s.email)}">${esc(s.email)}</a><a href="tel:${esc(s.phone.replace(/\s/g, ''))}">${esc(s.phone)}</a></div><div>${link('contact', 'Contact our team')}${link('privacy', 'Privacy notice')}${link('terms', 'Website terms')}<a href="admin.php">Website admin ↗</a></div></div><div class="copyright">© ${new Date().getFullYear()} Graphite Industries Limited <span>${esc(s.tagline)}</span></div>`
}
function titleBlock(p) {
  return `<section class="page-heading"><span class="kicker">${esc(p.eyebrow)}</span><h1>${esc(p.title)}</h1><p class="lead">${esc(p.summary)}</p></section>`
}
async function render() {
  let q = new URLSearchParams(location.search), id = q.get('page') || 'home';
  document.querySelector('#public-refinements').media = id === 'home' ? 'not all' : 'all';
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
  closeNavigation(false);
  track('page', id);
}
function contactForm(category) {
  const s = content.settings;
  const categories = ['Investment', 'Products', 'Technology & Engineering', 'Supply & Logistics', 'General'];
  const selected = categories.includes(category) ? category : 'General';
  return `<section class="section contact-layout"><div class="contact-copy"><span class="kicker">CONTACT OUR TEAM</span><h2>Good ideas begin with a conversation.</h2><p><a href="mailto:${esc(s.email)}">${esc(s.email)}</a><br><a href="tel:${esc(s.phone.replace(/\s/g, ''))}">${esc(s.phone)}</a></p><p>${esc(s.address)}</p><small>Proposed project: Mpwapwa District,<br>Dodoma Region, Tanzania.</small><p class="fine">Share non-confidential information only. Enquiries are received in our website inbox for review by the team.</p></div><form id="contact-form" class="panel"><div class="field-row"><label>Full name <span aria-hidden="true">*</span><input name="name" required maxlength="120" autocomplete="name"></label><label>Email address <span aria-hidden="true">*</span><input name="email" type="email" required maxlength="190" autocomplete="email"></label></div><label>Organisation <small>(optional)</small><input name="organisation" maxlength="190" autocomplete="organization"></label><label>Enquiry category<select name="category" aria-describedby="category-help">${categories.map(x => `<option${x === selected ? ' selected' : ''}>${esc(x)}</option>`).join('')}</select></label><p id="category-help" class="field-help"></p><details class="buyer-details"${selected === 'Products' ? ' open' : ''}><summary>Product requirements <small>(optional)</small></summary><p class="field-help">Share whichever details you have. Specifications can be discussed later.</p>${[['application', 'Intended application'], ['quality', 'Carbon content / impurity requirements'], ['size', 'Particle or flake size'], ['demand', 'Indicative demand and time period'], ['packaging', 'Packaging'], ['destination', 'Delivery destination'], ['qualification', 'Testing or qualification needs']].map(([name, label]) => `<label>${label}<input name="buyer_${name}" maxlength="300"></label>`).join('')}</details><label>Your message <span aria-hidden="true">*</span><textarea name="message" rows="6" required minlength="10" maxlength="2600" aria-describedby="message-help"></textarea></label><small id="message-help" class="field-help">Required fields are marked *. Please include at least 10 characters.</small><label class="honey" aria-hidden="true">Leave empty<input name="website" tabindex="-1" autocomplete="off"></label><label class="check"><input name="consent" type="checkbox" required> I agree to the <a href="?page=privacy">privacy notice</a> and consent to being contacted about this enquiry.</label><button class="btn green" type="submit">Send enquiry <span aria-hidden="true">↗</span></button><p id="form-result" role="status" aria-live="polite" class="form-result">Please share non-confidential information only.</p></form></section>`;
}
function bind() {
  let f = $('#contact-form');
  if (f) {
    const guidance = {
      Investment: 'Introduce your organisation, relevant project experience and the type of investment or development discussion you have in mind.',
      Products: 'Tell us about your application and requirements. Use the optional fields for any specifications you already know.',
      'Technology & Engineering': 'Outline your capabilities, relevant experience and the technical scope you could help assess.',
      'Supply & Logistics': 'Describe the material or service, source or location, supporting information and proposed scope.',
      General: 'Tell us what you would like to explore and the information you need from the team.',
    };
    const updateGuidance = () => {
      const product = f.elements.category.value === 'Products';
      $('#category-help').textContent = guidance[f.elements.category.value];
      $('.buyer-details', f).hidden = !product;
      $$('[name^=buyer_]', f).forEach(input => input.disabled = !product);
    };
    f.elements.category.addEventListener('change', updateGuidance);
    f.addEventListener('reset', () => setTimeout(updateGuidance, 0));
    updateGuidance();
  }
  if (f) f.onsubmit = async e => {
    e.preventDefault();
    let b = $('button[type=submit]', f);
    b.disabled = true;
    const result = $('#form-result');
    result.dataset.state = 'pending';
    result.textContent = 'Sending your enquiry…';
    try {
      const data = Object.fromEntries(new FormData(f));
      const requirements = Object.entries(data).filter(([key, value]) => key.startsWith('buyer_') && value.trim()).map(([key, value]) => `${key.replace('buyer_', '')}: ${value.trim()}`);
      if (requirements.length) data.message += '\n\nProduct requirements:\n' + requirements.join('\n');
      Object.keys(data).filter(key => key.startsWith('buyer_')).forEach(key => delete data[key]);
      await api('enquire', data);
      f.reset();
      result.dataset.state = 'success';
      result.textContent = 'Thank you. Your enquiry has been received in our website inbox for review.';
    } catch (e) {
      result.dataset.state = 'error';
      result.textContent = e.message;
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
const mobileNavigation = matchMedia('(max-width:850px)');
function syncNavigation() {
  const open = document.body.classList.contains('nav-open');
  $('.sidebar').inert = mobileNavigation.matches && !open;
  $('.site-wrap').inert = mobileNavigation.matches && open;
  $('.mobile-toggle').setAttribute('aria-expanded', String(open));
  $('.mobile-toggle').setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
  $('.mobile-toggle').textContent = open ? '×' : '☰';
}
function closeNavigation(restoreFocus = true) {
  const wasOpen = document.body.classList.contains('nav-open');
  document.body.classList.remove('nav-open');
  syncNavigation();
  if (wasOpen && restoreFocus) $('.mobile-toggle').focus();
}
$('.mobile-toggle').onclick = () => {
  const open = document.body.classList.toggle('nav-open');
  syncNavigation();
  if (open) $('.nav-close').focus();
};
$('.nav-close').onclick = () => closeNavigation();
$('.shade').onclick = closeNavigation;
mobileNavigation.addEventListener('change', () => closeNavigation(false));
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeNavigation();
  if (e.key === 'Tab' && mobileNavigation.matches && document.body.classList.contains('nav-open')) {
    const items = [$('.mobile-toggle'), ...$$('.sidebar a, .sidebar button')], first = items[0], last = items.at(-1);
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  }
});
document.documentElement.classList.remove('no-js');
syncNavigation();
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
