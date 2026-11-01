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
