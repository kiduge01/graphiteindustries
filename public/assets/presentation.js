'use strict';

// Presentation uses the published CMS content; no project milestones are inferred.
function pageCover(p, id) {
  return `<section class="page-cover cover-${esc(id)} ${p.image ? 'has-image' : ''}">
    <div class="cover-copy"><span class="kicker">${esc(p.eyebrow)}</span><h1>${esc(p.title)}</h1><p class="lead">${esc(p.summary)}</p></div>
    ${p.image ? `<figure class="cover-image"><img src="${esc(safeURL(p.image))}" alt="${id === 'products' ? 'Graphite mineral texture' : 'Illustrative image from company materials'}" fetchpriority="high"><figcaption>Illustrative imagery</figcaption></figure>` : ''}
  </section>`;
}

function contentBlocks(body) {
  return String(body || '').split(/\n\s*\n/).filter(x => x.trim());
}

function editorialCopy(p, label = 'OVERVIEW') {
  return `<section class="section editorial-copy"><span class="kicker">${label}</span><div>${paragraphs(p.body)}</div></section>`;
}

function nextChapter(id, eyebrow, title, text) {
  return `<section class="next-chapter"><div><span class="kicker">${eyebrow}</span><h2>${title}</h2></div>${link(id, text, 'chapter-link')}</section>`;
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
  let html = pageCover(p, id);
  if (id === 'about') {
    html += editorialCopy(p, 'WHO WE ARE');
    html += `<section class="section principles"><div class="section-head"><div><span class="kicker">WHAT GUIDES US</span><h2>Conviction.<br>With discipline.</h2></div><img class="identity-mark" src="assets/logo.png" alt="Graphite Industries Limited" loading="lazy"></div><div class="principle-list">${[['01', 'Evidence first', 'Technical evaluation guides development decisions.'], ['02', 'Industrial relevance', 'Customer requirements shape potential products.'], ['03', 'Responsible progress', 'People, place and accountability inform our approach.']].map(x => `<article><span>${x[0]}</span><h3>${x[1]}</h3><p>${x[2]}</p></article>`).join('')}</div></section>`;
    html += nextChapter('project', 'EXPLORE THE AMBITION', 'The Mpwapwa project.', 'View the project');
  } else if (id === 'project') {
    html += editorialCopy(p, 'PROJECT OVERVIEW');
    html += `<section class="section project-development"><div class="section-head"><div><span class="kicker">THE WAY FORWARD</span><h2>Progress through evidence.</h2></div><span class="stage-label">PROPOSED WORKSTREAMS</span></div><div class="development-list">${stages.map((s, i) => `<details ${i === 0 ? 'open' : ''}><summary><span>${s[0]}</span><h3>${s[1]}</h3><span class="detail-toggle" aria-hidden="true"></span></summary><p>${s[2]}</p></details>`).join('')}</div><p class="fine">Proposed workstreams, not completed milestones or a committed schedule.</p></section>`;
    html += `<section class="section brief compact-brief"><div><span class="kicker">PROJECT DOCUMENT</span><h2>The detail, in one place.</h2><small>Development-stage draft PDF &middot; Not an offer to invest</small></div><a class="btn green download" href="assets/corporate-project-brief-draft.pdf" target="_blank" rel="noopener">Read project brief <span aria-hidden="true">&darr;</span></a></section>`;
    html += nextChapter('products', 'FROM MINERAL TO MATERIAL', 'Explore the possibilities.', 'Potential products');
  } else if (id === 'products') {
    html += editorialCopy(p, 'PRODUCT DEVELOPMENT');
    html += `<section class="section product-collection"><span class="kicker">THREE PATHWAYS UNDER EVALUATION</span>${products()}</section>`;
    html += nextChapter('contact', 'FOR INDUSTRIAL BUYERS', 'Start with your requirements.', 'Discuss an application');
  } else if (id === 'sustainability') {
    const blocks = contentBlocks(p.body);
    const labels = [['01', 'Environment'], ['02', 'People & communities'], ['03', 'Governance']];
    html += `<section class="section responsibility-collection">${blocks.map((body, i) => `<article><span class="responsibility-number">${labels[i]?.[0] || String(i + 1).padStart(2, '0')}</span><div><h2>${labels[i]?.[1] || 'Our approach'}</h2>${paragraphs(body)}</div></article>`).join('')}</section>`;
    html += nextChapter('project', 'RESPONSIBILITY IN CONTEXT', 'Considered from the start.', 'Explore the project');
  } else if (id === 'investors') {
    const blocks = contentBlocks(p.body);
    html += `<section class="section partnership-collection"><div class="section-head"><div><span class="kicker">OPPORTUNITIES TO CONNECT</span><h2>Expertise meets ambition.</h2></div></div><div class="partner-grid">${[['Geology & testing', 'Geology & laboratory services'], ['Engineering & technology', 'Technology & engineering'], ['Industrial buyers', 'Product information'], ['Infrastructure & logistics', 'Infrastructure & logistics'], ['Strategic investment', 'Investment & partnership'], ['Environment & communities', 'Environmental & social services']].map((x, i) => `<a href="?page=contact&category=${encodeURIComponent(x[1])}"><span>0${i + 1}</span><h3>${x[0]}</h3><b aria-hidden="true">&nearr;</b></a>`).join('')}</div><details class="engagement-details"><summary>How engagement works <span aria-hidden="true">+</span></summary>${paragraphs(blocks.slice(1).join('\n\n'))}</details><details class="engagement-details"><summary>Areas of collaboration <span aria-hidden="true">+</span></summary>${paragraphs(blocks[0] || '')}</details></section>`;
    html += `<section class="section brief compact-brief"><div><span class="kicker">BEFORE WE TALK</span><h2>Get to know the project.</h2><small>Introductory draft &middot; Not an offer to invest</small></div><a class="btn green download" href="assets/corporate-project-brief-draft.pdf" target="_blank" rel="noopener">Read the project brief <span aria-hidden="true">&darr;</span></a></section>`;
  } else if (id === 'contact') {
    html += contactForm(q.get('category'));
  } else {
    html += editorialCopy(p);
  }
  return html;
}
