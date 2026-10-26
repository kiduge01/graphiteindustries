'use strict';

// CMS text supports headings and lists; arbitrary HTML is always escaped.
function contentSections(body) {
  const sections = [{ title: '', body: '' }];
  for (const line of String(body || '').split('\n')) {
    if (line.startsWith('## ')) sections.push({ title: line.slice(3).trim(), body: '' });
    else sections[sections.length - 1].body += line + '\n';
  }
  return sections.filter(s => s.title || s.body.trim());
}
function readableText(body, listClass = '') {
  return String(body || '').trim().split(/\n\s*\n/).filter(Boolean).map(block => {
    const lines = block.split('\n');
    if (lines.every(x => /^\d+\. /.test(x))) return `<ol class="${listClass || 'copy-list'}">${lines.map(x => {
      const text = x.replace(/^\d+\. /, ''), cut = text.indexOf(' — ');
      return `<li>${cut < 0 ? esc(text) : `<h3>${esc(text.slice(0, cut))}</h3><p>${esc(text.slice(cut + 3))}</p>`}</li>`;
    }).join('')}</ol>`;
    if (lines.every(x => x.startsWith('- '))) return `<ul class="copy-list">${lines.map(x => `<li>${esc(x.slice(2))}</li>`).join('')}</ul>`;
    return `<p>${lines.map(esc).join('<br>')}</p>`;
  }).join('');
}
function subsections(body) {
  return String(body).split(/^### /m).map((part, i) => {
    if (!i) return { title: '', body: part };
    const cut = part.indexOf('\n');
    return { title: part.slice(0, cut).trim(), body: part.slice(cut + 1) };
  });
}
function pageCover(p, id) {
  const image = safeURL(p.image);
  return `<section class="page-cover cover-${esc(id)} ${image ? 'has-image' : ''}"><div class="cover-copy"><a class="breadcrumb" href="?page=home">Home <span aria-hidden="true">/</span></a><span class="kicker">${esc(p.eyebrow)}</span><h1>${esc(p.title)}</h1><p class="lead">${esc(p.summary)}</p></div>${image ? `<figure class="cover-image"><img src="${esc(image)}" alt="${image.includes('graphite') ? 'Close view of a graphite mineral specimen' : 'Representative industrial context, not the Mpwapwa site'}" width="1000" height="750" fetchpriority="high"><figcaption>${image.includes('graphite') ? 'Graphite mineral · illustrative' : 'Illustrative imagery · not the project site'}</figcaption></figure>` : ''}</section>`;
}
function nextChapter(id, eyebrow, title, text) {
  return `<section class="next-chapter"><div><span class="kicker">${esc(eyebrow)}</span><h2>${esc(title)}</h2></div>${link(id, esc(text), 'chapter-link')}</section>`;
}
function homePage(p) {
  return `<section class="hero quiet-hero"><div class="hero-image" style="background-image:url('${esc(safeURL(p.image))}')"></div><div class="hero-content"><span class="kicker">TANZANIA / GRAPHITE INDUSTRIES LIMITED</span><div class="green-rule"></div><h1>${esc(p.title)}</h1><p>${esc(p.summary)}</p>${link('project', 'Discover Mpwapwa', 'btn green')}</div><span class="image-caption">Illustrative graphite imagery</span><div class="hero-bottom">MPWAPWA, DODOMA <span>EVALUATION & PLANNING</span></div></section>
  <section class="section home-introduction"><span class="kicker">MINERAL POTENTIAL. INDUSTRIAL AMBITION.</span><div><h2>A new chapter<br>for graphite in Tanzania.</h2>${link('about', 'Meet Graphite Industries')}</div></section>
  <section class="explore-grid" aria-label="Explore Graphite Industries">
    <a href="?page=project" class="explore-tile project-tile"><img src="assets/logistics.jpg" alt="Illustrative infrastructure and logistics" loading="lazy"><span class="tile-top">01 / THE PROJECT <span aria-hidden="true">&nearr;</span></span><div><h2>Rooted in Mpwapwa.</h2><span class="tile-action">Explore our proposed development</span></div><small>Illustrative imagery</small></a>
    <a href="?page=products" class="explore-tile mineral-tile"><img src="assets/graphite.jpg" alt="Graphite mineral" loading="lazy"><span class="tile-top">02 / POTENTIAL PRODUCTS <span aria-hidden="true">&nearr;</span></span><div><h2>Material.<br>Possibility.</h2><span class="tile-action">Explore the product pathways</span></div></a>
    <a href="?page=sustainability" class="explore-tile responsibility-tile"><span class="tile-top">03 / OUR RESPONSIBILITIES <span aria-hidden="true">&nearr;</span></span><div><h2>Progress,<br>considered.</h2><span class="tile-action">Explore our approach</span></div></a>
  </section>`;
}

function editorialPage(p, id, q) {
  if (id === 'about') return aboutSampleA();
  let html = pageCover(p, id);
  if (id === 'contact') return html + contactForm(q.get('category'));
  let inPurpose = false;
  let introduction = '';
  contentSections(p.body).forEach((s, i) => {
    if (!s.title) {
      if (id === 'about') html += `<section class="section page-introduction"><span class="kicker">OUR APPROACH</span><div>${readableText(s.body)}</div></section>`;
      else introduction = s.body;
      return;
    }
    const purpose = id === 'about' && ['Our vision', 'Our mission'].includes(s.title);
    if (purpose && !inPurpose) { html += '<div class="purpose-grid">'; inPurpose = true; }
    if (!purpose && inPurpose) { html += '</div>'; inPurpose = false; }
    const process = id === 'project' && s.title === 'From feedstock to finished material';
    const roadmap = id === 'project' && s.title === 'A development roadmap';
    const applications = id === 'products' && s.title === 'Where graphite can make a difference';
    const partners = id === 'investors' && s.title === 'Contributions we seek';
    const product = id === 'products' && ['Graphite concentrate', 'Micronised graphite', 'Expandable graphite'].includes(s.title);
    const classes = ['section', 'editorial-section', purpose ? 'purpose-section' : '', process ? 'process-section' : '', roadmap ? 'roadmap-section' : '', applications ? 'applications-section' : '', partners ? 'partners-section' : '', product ? 'product-pathway' : '', id === 'sustainability' ? 'esg-section' : ''].filter(Boolean).join(' ');
    html += `<section class="${classes}"><div class="editorial-heading"><span class="section-number">${String(i).padStart(2, '0')}</span><h2>${esc(s.title)}</h2>${product ? '<span class="status-label">Proposed pathway · under evaluation</span>' : ''}</div><div class="editorial-body">`;
    if (introduction) { html += readableText(introduction); introduction = ''; }
    if (applications || partners) {
      const parts = subsections(s.body), categories = ['Investment', 'Technology & Engineering', 'Technology & Engineering', 'Supply & Logistics', 'Products', 'Supply & Logistics'];
      html += readableText(parts[0].body);
      html += `<div class="${partners ? 'partner-grid' : 'application-grid'}">${parts.slice(1).map((part, n) => partners ? `<a href="?page=contact&category=${encodeURIComponent(categories[n] || 'General')}"><span>0${n + 1}</span><h3>${esc(part.title)}</h3>${readableText(part.body)}<span class="partner-action">Discuss this area <b aria-hidden="true">↗</b></span></a>` : `<article><span class="application-icon" aria-hidden="true">${['↯', '◈', '≈', '◎'][n]}</span><h3>${esc(part.title)}</h3>${readableText(part.body)}</article>`).join('')}</div>`;
      if (applications) html += '<p class="source-note">Industry context: <a href="https://www.usgs.gov/centers/national-minerals-information-center/graphite-statistics-and-information" target="_blank" rel="noopener">USGS graphite information ↗</a>. Applications are subject to product and customer qualification.</p>';
    } else html += readableText(s.body, process ? 'process-chain' : roadmap ? 'development-roadmap' : id === 'investors' ? 'engagement-steps' : '');
    if (product && s.title === 'Expandable graphite') html += '<p class="source-note">Industry context: <a href="https://www.luh.de/en/expandable-graphite/" target="_blank" rel="noopener">Georg H. LUH on expandable graphite ↗</a>.</p>';
    html += '</div></section>';
  });
  if (introduction) html += `<section class="section"><div class="editorial-body">${readableText(introduction)}</div></section>`;
  if (inPurpose) html += '</div>';
  const next = {
    about: ['project', 'SEE THE IDEA IN CONTEXT', 'Explore the proposed Mpwapwa project.', 'Our Project'],
    project: ['products', 'FROM PROCESS TO PURPOSE', 'What could graphite become?', 'Products & Applications'],
    products: ['contact&category=Products', 'FOR INDUSTRIAL BUYERS', 'Tell us what your application needs.', 'Discuss your requirements'],
    investors: ['contact&category=Investment', 'A PLACE TO BEGIN', 'Bring your experience to the conversation.', 'Introduce your organisation'],
    sustainability: ['contact', 'QUESTIONS & PERSPECTIVES', 'Responsible progress takes dialogue.', 'Contact our team'],
  };
  if (next[id]) html += nextChapter(...next[id]);
  return html;
}
