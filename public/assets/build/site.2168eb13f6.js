'use strict';
;// core.js
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


;// leadership-spotlight.js
'use strict';
(() => {
  let activeRoot = null;
  let dispose = () => {};
  window.initLeadershipSpotlight = root => {
    if (root === activeRoot) return;
    dispose();
    activeRoot = root;
    if (!root) return;
    const controller = new AbortController();
    const options = {signal:controller.signal};
    const query = selector => root.querySelector(selector);
    const buttons = [...root.querySelectorAll('[data-ls-person]')];
    if (!buttons.length) return;
    const people = buttons.map(button => ({...button.dataset,button}));
    const photo = query('[data-ls-photo]');
    const copy = query('[data-ls-copy]');
    const play = query('[data-ls-play]');
    const viewer = query('.ls-viewer');
    const motion = matchMedia('(prefers-reduced-motion: reduce)');
    let current = 0, group = 'board', timer = null;
    let wantsLoop = !motion.matches, hovered = false, focused = false, visible = false;
    const groupName = value => value === 'board' ? 'Board of Directors' : 'Senior Management Team';
    const members = () => people.filter(person => person.group === group);
    function stopTimer() {
      if (timer) clearInterval(timer);
      timer = null;
      root.classList.remove('is-playing');
    }
    function syncPlayback() {
      stopTimer();
      play.setAttribute('aria-pressed',String(wantsLoop));
      play.setAttribute('aria-label',wantsLoop ? 'Pause automatic leadership loop' : 'Play automatic leadership loop');
      play.textContent = wantsLoop ? 'Ⅱ Pause' : '▶ Play';
      copy.setAttribute('aria-live',wantsLoop ? 'off' : 'polite');
      if (wantsLoop && !motion.matches && visible && !document.hidden && !hovered && !focused && !viewer.open) {
        root.classList.add('is-playing');
        timer = setInterval(()=>step(1),5000);
      }
    }
    function pause() {
      wantsLoop = false;
      syncPlayback();
    }
    function picture(person, large = false) {
      const image = document.createElement('img');
      image.src = `assets/team/${person.photo}-${large ? 960 : 480}.webp`;
      if (!large) {
        image.srcset = `assets/team/${person.photo}-480.webp 480w, assets/team/${person.photo}-960.webp 960w`;
        image.sizes = '(max-width: 360px) 88px, (max-width: 600px) 108px, 160px';
      }
      image.alt = person.name;
      image.width = 480; image.height = 600;
      image.decoding = 'async';
      return image;
    }
    function show(index, animate = true) {
      current = index;
      const person = people[index];
      group = person.group;
      photo.replaceChildren();
      if (person.photo) {
        photo.append(picture(person));
        const enlarge = document.createElement('span');
        enlarge.className = 'ls-enlarge';
        enlarge.setAttribute('aria-hidden','true'); enlarge.textContent = '↗';
        photo.append(enlarge);
        photo.href = `assets/team/${person.photo}-960.webp`;
        photo.setAttribute('aria-label','Enlarge portrait of '+person.name);
        photo.removeAttribute('aria-disabled'); photo.removeAttribute('tabindex');
      } else {
        const initials = document.createElement('span');
        initials.className = 'ls-initials'; initials.textContent = person.initials;
        initials.setAttribute('aria-hidden','true');
        photo.append(initials);
        photo.removeAttribute('href'); photo.setAttribute('tabindex','-1');
        photo.setAttribute('aria-disabled','true');
        photo.setAttribute('aria-label','Portrait not supplied for '+person.name);
      }
      photo.classList.toggle('is-unavailable',!person.photo);
      copy.querySelector('h3').textContent = person.name;
      copy.querySelector('p').textContent = person.role;
      query('[data-ls-group]').textContent = groupName(group);
      const selectedMembers = members();
      const position = selectedMembers.indexOf(person)+1;
      query('[data-ls-count]').textContent = `${String(position).padStart(2,'0')} / ${String(selectedMembers.length).padStart(2,'0')}`;
      buttons.forEach((button,i) => {
        button.hidden = people[i].group !== group;
        button.setAttribute('aria-pressed',String(i===current));
      });
      root.querySelectorAll('[data-ls-filter]').forEach(button => button.setAttribute('aria-pressed',String(button.dataset.lsFilter===group)));
      [photo,copy].forEach(element => {
        element.classList.remove('ls-entering');
        if (animate && !motion.matches) {void element.offsetWidth;element.classList.add('ls-entering');}
      });
    }
    function step(direction) {
      const selected = members();
      const next = (selected.indexOf(people[current])+direction+selected.length)%selected.length;
      show(people.indexOf(selected[next]));
    }
    root.addEventListener('click',event => {
      const button = event.target.closest('button');
      if (!button || !root.contains(button)) return;
      if (button.hasAttribute('data-ls-person')) {show(buttons.indexOf(button));syncPlayback();}
      if (button.hasAttribute('data-ls-filter')) {show(people.findIndex(person=>person.group===button.dataset.lsFilter));syncPlayback();}
      if (button.hasAttribute('data-ls-prev')) {step(-1);syncPlayback();}
      if (button.hasAttribute('data-ls-next')) {step(1);syncPlayback();}
      if (button.hasAttribute('data-ls-play')) {
        if (motion.matches) return;
        wantsLoop = !wantsLoop;
        syncPlayback();
      }
      if (button.hasAttribute('data-ls-close')) viewer.close();
    },options);
    root.addEventListener('keydown',event => {
      if (viewer.open || !['ArrowLeft','ArrowRight'].includes(event.key)) return;
      event.preventDefault();step(event.key==='ArrowRight'?1:-1);syncPlayback();
    },options);
    root.addEventListener('pointerenter',event=>{if(event.pointerType!=='touch'){hovered=true;syncPlayback();}},options);
    root.addEventListener('pointerleave',()=>{hovered=false;syncPlayback();},options);
    root.addEventListener('focusin',event=>{focused=event.target!==play;syncPlayback();},options);
    root.addEventListener('focusout',()=>queueMicrotask(()=>{focused=root.contains(document.activeElement)&&document.activeElement!==play;syncPlayback();}),options);
    document.addEventListener('visibilitychange',syncPlayback,options);
    const updateMotion = () => {if(motion.matches)pause();play.disabled=motion.matches;};
    motion.addEventListener('change',updateMotion);
    const observer = 'IntersectionObserver' in window ? new IntersectionObserver(entries=>{visible=entries[0].isIntersecting;syncPlayback();}) : null;
    observer?.observe(root);
    if (!observer) visible = true;
    photo.addEventListener('click',event => {
      const person = people[current];
      if (!person.photo || !viewer.showModal) return;
      event.preventDefault();stopTimer();
      const content = query('[data-ls-viewer-content]');
      const imageBox = document.createElement('div');imageBox.className='ls-viewer-photo';imageBox.append(picture(person,true));
      const text = document.createElement('div');text.className='ls-viewer-copy';
      const title = document.createElement('h3');title.id='ls-viewer-name';title.textContent=person.name;
      const role = document.createElement('p');role.textContent=person.role;
      text.append(title,role);content.replaceChildren(imageBox,text);viewer.showModal();
    },options);
    viewer.addEventListener('click',event => {
      if(event.target!==viewer)return;
      const box=viewer.getBoundingClientRect();
      if(event.clientX<box.left||event.clientX>box.right||event.clientY<box.top||event.clientY>box.bottom)viewer.close();
    },options);
    viewer.addEventListener('close',()=>{if(photo.isConnected)photo.focus();},options);
    dispose = () => {wantsLoop=false;stopTimer();controller.abort();motion.removeEventListener('change',updateMotion);observer?.disconnect();if(viewer.open)viewer.close();};
    root.classList.add('is-interactive');
    root.querySelectorAll('[data-ls-controls],[data-ls-count],[data-ls-roster]').forEach(element=>element.hidden=false);
    show(0,false);updateMotion();syncPlayback();
  };
  window.initLeadershipSpotlight(document.querySelector('[data-leadership-spotlight]'));
})();

;// site.js
'use strict';
// Public-site behaviour. Every page, including navigation and footer, is rendered by
// the server (public/index.php); this script only adds forms, consent, analytics and
// the mobile navigation.
const currentPage = () => document.documentElement.dataset.page || 'home';
const enquiryCategories = ['Investment', 'Products', 'Technology & Engineering', 'Supply & Logistics', 'General'];
const enquiryTopics = {
  'mpwapwa-project': 'I would like to discuss the proposed Mpwapwa graphite project. My area of interest is ',
  'project-information': 'Please share the available information about the proposed Mpwapwa graphite project, including its current development status. I would particularly like to know about ',
  'graphite-concentrate': 'I would like to discuss the proposed graphite concentrate pathway. My intended application and requirements are ',
  'micronised-graphite': 'I would like to discuss the proposed micronised graphite pathway. My intended application and requirements are ',
  'expandable-graphite': 'I would like to discuss the proposed expandable graphite pathway. My intended application and requirements are ',
  'battery-materials': 'I would like to discuss future battery-material requirements and the further processing and qualification they would need. My requirements are ',
};
const categoryGuidance = {
  Investment: 'Introduce your organisation, relevant project experience and the type of investment or development discussion you have in mind.',
  Products: 'Tell us about your application and requirements. Use the optional fields for any specifications you already know.',
  'Technology & Engineering': 'Outline your capabilities, relevant experience and the technical scope you could help assess.',
  'Supply & Logistics': 'Describe the material or service, source or location, supporting information and proposed scope.',
  General: 'Tell us what you would like to explore and the information you need from the team.',
};
const buyerFields = [['application', 'Intended application'], ['quality', 'Carbon content / impurity requirements'], ['size', 'Particle or flake size'], ['demand', 'Indicative demand and time period'], ['packaging', 'Packaging'], ['destination', 'Delivery destination'], ['qualification', 'Testing or qualification needs']];

function enquiryForm(category) {
  const selected = enquiryCategories.includes(category) ? category : 'General';
  return `<form id="contact-form" class="panel"><div class="field-row"><label>Full name <span aria-hidden="true">*</span><input name="name" required maxlength="120" autocomplete="name"></label><label>Email address <span aria-hidden="true">*</span><input name="email" type="email" required maxlength="190" autocomplete="email"></label></div><label>Organisation <small>(optional)</small><input name="organisation" maxlength="190" autocomplete="organization"></label><label>Enquiry category<select name="category" aria-describedby="category-help">${enquiryCategories.map(x => `<option${x === selected ? ' selected' : ''}>${esc(x)}</option>`).join('')}</select></label><p id="category-help" class="field-help"></p><details class="buyer-details"${selected === 'Products' ? ' open' : ''}><summary>Product requirements <small>(optional)</small></summary><p class="field-help">Share whichever details you have. Specifications can be discussed later.</p>${buyerFields.map(([name, label]) => `<label>${label}<input name="buyer_${name}" maxlength="300"></label>`).join('')}</details><label>Your message <span aria-hidden="true">*</span><textarea name="message" rows="6" required minlength="10" maxlength="2600" aria-describedby="message-help"></textarea></label><small id="message-help" class="field-help">Required fields are marked *. Please include at least 10 characters.</small><label class="honey" aria-hidden="true">Leave empty<input name="website" tabindex="-1" autocomplete="off"></label><label class="check"><input name="consent" type="checkbox" required> I agree to the <a href="privacy">privacy notice</a> and consent to being contacted about this enquiry.</label><button class="btn green" type="submit">Send enquiry <span aria-hidden="true">↗</span></button><p id="form-result" role="status" aria-live="polite" class="form-result">Please share non-confidential information only.</p></form>`;
}

// Shared submit handling: disables the button, reports progress and shows the API result.
function handleSubmit(form, result, pending, success, send) {
  form.onsubmit = async e => {
    e.preventDefault();
    const button = $('button[type=submit]', form);
    button.disabled = true;
    result.dataset.state = 'pending';
    result.textContent = pending;
    try {
      await send(Object.fromEntries(new FormData(form)));
      form.reset();
      result.dataset.state = 'success';
      result.textContent = success;
    } catch (error) {
      result.dataset.state = 'error';
      result.textContent = error.message;
    } finally {
      button.disabled = false;
    }
  };
}

function bindEnquiryForm() {
  const slot = $('#contact-form-slot');
  if (!slot) return;
  const query = new URLSearchParams(location.search);
  slot.innerHTML = enquiryForm(query.get('category'));
  const form = $('#contact-form');
  const topic = enquiryTopics[query.get('topic')];
  if (topic) form.elements.message.value = topic;
  const updateGuidance = () => {
    const product = form.elements.category.value === 'Products';
    $('#category-help').textContent = categoryGuidance[form.elements.category.value];
    $('.buyer-details', form).hidden = !product;
    $$('[name^=buyer_]', form).forEach(input => input.disabled = !product);
  };
  form.elements.category.addEventListener('change', updateGuidance);
  form.addEventListener('reset', () => setTimeout(updateGuidance, 0));
  updateGuidance();
  handleSubmit(form, $('#form-result'), 'Sending your enquiry…', 'Thank you. Your enquiry has been received in our website inbox for review.', data => {
    const requirements = Object.entries(data).filter(([key, value]) => key.startsWith('buyer_') && value.trim()).map(([key, value]) => `${key.replace('buyer_', '')}: ${value.trim()}`);
    if (requirements.length) data.message += '\n\nProduct requirements:\n' + requirements.join('\n');
    Object.keys(data).filter(key => key.startsWith('buyer_')).forEach(key => delete data[key]);
    return api('enquire', data);
  });
}

function bind() {
  bindEnquiryForm();
  const updates = $('#updates-form');
  if (updates) handleSubmit(updates, $('.form-result', updates), 'Subscribing…', 'Thank you. You will receive verified company updates by email.', data => api('subscribe', data));
  window.initLeadershipSpotlight?.(document.querySelector('[data-leadership-spotlight]'));
  $$('.download').forEach(a => a.onclick = () => track('download', a.getAttribute('href') || 'document'));
  $('#reset-consent')?.addEventListener('click', () => {
    localStorage.removeItem('graphite-analytics-consent');
    consent();
  });
}

async function track(kind, page) {
  if (localStorage.getItem('graphite-analytics-consent') !== 'yes') return;
  let sid = sessionStorage.getItem('graphite-visit');
  if (!sid) {
    sid = crypto.randomUUID();
    sessionStorage.setItem('graphite-visit', sid);
  }
  try {
    await api('track', { kind, page, sid, referrer: document.referrer });
  } catch { }
}

function consent() {
  if (localStorage.getItem('graphite-analytics-consent')) return;
  $('.consent')?.remove();
  const el = document.createElement('div');
  el.className = 'consent';
  el.innerHTML = '<div><strong>Help us understand what’s useful.</strong><p>Allow optional first-party visitor analytics? No advertising trackers.</p></div><button class="btn secondary" data-value="no">Decline</button><button class="btn green" data-value="yes">Allow analytics</button>';
  document.body.append(el);
  $$('button', el).forEach(b => b.onclick = () => {
    localStorage.setItem('graphite-analytics-consent', b.dataset.value);
    el.remove();
    if (b.dataset.value === 'yes') track('page', currentPage());
  });
}

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
bind();
consent();
// The session request provides the form protection token used by api().
init().then(() => track('page', currentPage())).catch(error => console.error(error));
