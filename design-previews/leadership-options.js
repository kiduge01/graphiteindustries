'use strict';
const team = [
  {name:'Mr. Amini N. Kimaro',role:'Chief Executive Officer / Board Chairman',photo:'amini-kimaro',group:'Board of Directors'},
  {name:'Dr. Humphrey Musyoka',role:'Director',initials:'HM',group:'Board of Directors'},
  {name:'Dr. Alfred C. Msemo',role:'Director',initials:'AM',group:'Board of Directors'},
  {name:'Harish Kumar',role:'Chief Finance Officer (CFO)',initials:'HK',group:'Board of Directors'},
  {name:'Mrs. Juliette Kimaro',role:'Chief Legal Counsel',photo:'juliette-kimaro',group:'Board of Directors'},
  {name:'Denis A. Kimaro',role:'Director',photo:'denis-kimaro',group:'Board of Directors'},
  {name:'Nickson Kimbori',role:'Operations & Logistics',photo:'nickson-kimbori',group:'Senior Management Team'},
  {name:'Ridhiwani M. Mbaruku',role:'Mining Operations',photo:'ridhiwani-mbaruku',group:'Senior Management Team'},
];
const motion = matchMedia('(prefers-reduced-motion: reduce)');
const escapeText = value => String(value).replace(/[&<>"']/g, char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
function portrait(person, large = false, dark = false) {
  return person.photo
    ? `<img class="portrait" src="../assets/team/${person.photo}-${large?'960':'480'}.webp" alt="${escapeText(person.name)}" width="480" height="600" decoding="async">`
    : `<span class="portrait portrait-initials${dark?' dark':''}" aria-hidden="true">${person.initials}</span>`;
}
function profile(index) {
  const person=team[index];
  return `<article class="profile-card reveal" style="--delay:${(index%3)*80}ms">${person.photo?`<button class="card-photo" data-portrait="${index}" aria-label="View portrait of ${escapeText(person.name)}">${portrait(person)}<span class="photo-hint" aria-hidden="true">↗</span></button>`:`<div class="card-photo">${portrait(person)}</div>`}<div class="profile-copy"><h3>${escapeText(person.name)}</h3><p>${escapeText(person.role)}</p></div></article>`;
}
document.querySelector('#cards-content').innerHTML = `
  <div class="section-label"><span class="eyebrow">BOARD OF DIRECTORS</span></div>
  <article class="chair-feature reveal"><button class="chair-photo" data-portrait="0" aria-label="View portrait of Mr. Amini N. Kimaro">${portrait(team[0])}<span class="photo-hint" aria-hidden="true">↗</span></button><div class="chair-copy"><span class="eyebrow">BOARD CHAIRMAN &amp; CEO</span><h2>${team[0].name}</h2><p>${team[0].role}</p><span class="quiet-label">GRAPHITE INDUSTRIES LIMITED</span></div></article>
  <div class="profile-grid">${[1,2,3,4,5].map(profile).join('')}</div>
  <div class="management-block"><div class="section-label"><span class="eyebrow">SENIOR MANAGEMENT TEAM</span></div><div class="profile-grid">${[6,7].map(profile).join('')}</div></div>`;
function orb(index) {
  const person=team[index];
  const content=`<span class="orb-photo">${portrait(person)}</span><strong>${escapeText(person.name)}</strong><small>${escapeText(person.role)}</small>`;
  return person.photo?`<button class="orb-person" data-portrait="${index}" aria-label="View portrait of ${escapeText(person.name)}">${content}</button>`:`<div class="orb-person">${content}</div>`;
}
document.querySelector('#tree-content').innerHTML=`
  <section class="orb-board" aria-labelledby="orb-board-heading"><h2 class="orb-heading" id="orb-board-heading">Board of Directors</h2><div class="orb-root">${orb(0)}</div><div class="orb-children">${[1,2,3,4,5].map((index,i)=>`<div class="orb-child reveal" style="--delay:${i*90}ms">${orb(index)}</div>`).join('')}</div></section>
  <section class="orb-management" aria-labelledby="orb-management-heading"><div class="orb-management-root"><h2 class="orb-heading" id="orb-management-heading">Senior Management Team</h2></div><div class="orb-children">${[6,7].map(index=>`<div class="orb-child reveal">${orb(index)}</div>`).join('')}</div></section>`;
document.querySelector('#spotlight-content').innerHTML=`
  <div class="spotlight-stage"><div class="spotlight-topline"><span class="eyebrow" id="slide-group"></span><span class="slide-count" id="slide-count"></span></div><div class="spotlight-photo" id="slide-photo"></div><div class="spotlight-copy" id="slide-copy" aria-live="polite" aria-atomic="true"></div><div class="spotlight-controls"><button class="round-button" id="slide-prev" aria-label="Previous team member">←</button><button class="round-button" id="slide-next" aria-label="Next team member">→</button><button class="play-button" id="slide-play" aria-pressed="false">▶ Play slideshow</button></div></div>
  <div class="spotlight-roster" aria-label="Choose a team member"><h2 class="roster-title">Meet the leadership</h2>${team.map((person,index)=>`<button class="roster-button" data-slide="${index}" aria-pressed="${index===0}"><span class="roster-photo">${portrait(person)}</span><span class="roster-copy"><strong>${escapeText(person.name)}</strong><small>${escapeText(person.role)}</small></span><span class="roster-arrow" aria-hidden="true">↗</span></button>`).join('')}</div>`;

let observer;
function reveal(panel) {
  const elements=panel.querySelectorAll('.reveal');
  if (!('IntersectionObserver' in window) || motion.matches) {elements.forEach(el=>el.classList.add('is-visible'));return;}
  document.documentElement.classList.add('js-motion');
  if (!observer) observer=new IntersectionObserver(entries=>entries.forEach(entry=>{
    if(entry.isIntersecting){entry.target.classList.add('is-visible');observer.unobserve(entry.target);}
  }), {threshold:0.08});
  elements.forEach(el=>{el.classList.remove('is-visible');observer.observe(el);});
}
const tabs=[...document.querySelectorAll('[data-concept]')];
function selectConcept(button, updateHash=true) {
  stopSlideshow();
  observer?.disconnect();
  tabs.forEach(tab=>{
    const selected=tab===button;
    tab.setAttribute('aria-selected',String(selected));tab.tabIndex=selected?0:-1;
    document.querySelector('#'+tab.getAttribute('aria-controls')).hidden=!selected;
  });
  const panel=document.querySelector('#'+button.getAttribute('aria-controls'));
  reveal(panel);
  if(button.dataset.concept==='tree') {
    const tree=document.querySelector('#tree-content');
    tree.getAnimations({subtree:true}).forEach(animation=>{if(animation.effect?.getTiming().iterations!==Infinity){animation.cancel();animation.play();}});
  }
  if(updateHash)history.replaceState(null,'','#'+button.dataset.concept);
}
tabs.forEach((button,index)=>{
  button.addEventListener('click',()=>selectConcept(button));
  button.addEventListener('keydown',event=>{
    let next;
    if(event.key==='ArrowRight')next=(index+1)%tabs.length;
    if(event.key==='ArrowLeft')next=(index-1+tabs.length)%tabs.length;
    if(event.key==='Home')next=0;
    if(event.key==='End')next=tabs.length-1;
    if(next!==undefined){event.preventDefault();tabs[next].focus();selectConcept(tabs[next]);}
  });
});

const viewer=document.querySelector('#portrait-viewer');
let portraitOpener;
document.addEventListener('click',event=>{
  const opener=event.target.closest('[data-portrait]');
  if(!opener)return;
  stopSlideshow();
  const person=team[Number(opener.dataset.portrait)];
  portraitOpener=opener;
  document.querySelector('#portrait-content').innerHTML=`<div class="modal-photo">${portrait(person,true)}</div><div class="modal-copy"><h2 id="portrait-name">${escapeText(person.name)}</h2><p>${escapeText(person.role)}</p><small>${escapeText(person.group)}</small></div>`;
  viewer.showModal();
});
document.querySelector('.modal-close').addEventListener('click',()=>viewer.close());
viewer.addEventListener('click',event=>{
  if(event.target!==viewer)return;
  const bounds=viewer.getBoundingClientRect();
  if(event.clientX<bounds.left||event.clientX>bounds.right||event.clientY<bounds.top||event.clientY>bounds.bottom)viewer.close();
});
viewer.addEventListener('close',()=>portraitOpener?.focus());

let currentSlide=0,slideTimer=null;
function showSlide(index) {
  currentSlide=(index+team.length)%team.length;
  const person=team[currentSlide];
  document.querySelector('#slide-group').textContent=person.group;
  document.querySelector('#slide-count').textContent=`${String(currentSlide+1).padStart(2,'0')} / ${String(team.length).padStart(2,'0')}`;
  const photo=document.querySelector('#slide-photo'),copy=document.querySelector('#slide-copy');
  photo.innerHTML=portrait(person,true,true);
  copy.innerHTML=`<h2>${escapeText(person.name)}</h2><p>${escapeText(person.role)}</p>`;
  [photo,copy].forEach(el=>{el.classList.remove('is-changing');void el.offsetWidth;el.classList.add('is-changing');});
  document.querySelectorAll('[data-slide]').forEach(button=>{
    button.setAttribute('aria-pressed',String(Number(button.dataset.slide)===currentSlide));
  });
}
function stopSlideshow() {
  if(slideTimer)clearInterval(slideTimer);
  slideTimer=null;
  document.querySelector('#spotlight-content').classList.remove('is-playing');
  document.querySelector('#slide-play').setAttribute('aria-pressed','false');
  document.querySelector('#slide-play').textContent='▶ Play slideshow';
  document.querySelector('#slide-copy').setAttribute('aria-live','polite');
}
document.querySelector('#slide-play').addEventListener('click',()=>{
  if(slideTimer){stopSlideshow();return;}
  if(motion.matches)return;
  document.querySelector('#spotlight-content').classList.add('is-playing');
  document.querySelector('#slide-play').setAttribute('aria-pressed','true');
  document.querySelector('#slide-play').textContent='Ⅱ Pause slideshow';
  document.querySelector('#slide-copy').setAttribute('aria-live','off');
  slideTimer=setInterval(()=>showSlide(currentSlide+1),4500);
});
document.querySelectorAll('[data-slide]').forEach(button=>button.addEventListener('click',()=>{stopSlideshow();showSlide(Number(button.dataset.slide));}));
document.querySelector('#slide-prev').addEventListener('click',()=>{stopSlideshow();showSlide(currentSlide-1);});
document.querySelector('#slide-next').addEventListener('click',()=>{stopSlideshow();showSlide(currentSlide+1);});
document.querySelector('#spotlight-content').addEventListener('keydown',event=>{
  if(event.key==='ArrowLeft'||event.key==='ArrowRight'){event.preventDefault();stopSlideshow();showSlide(currentSlide+(event.key==='ArrowRight'?1:-1));}
});
document.querySelector('#spotlight-content').addEventListener('mouseenter',stopSlideshow);
document.addEventListener('visibilitychange',()=>{if(document.hidden)stopSlideshow();});
function updateMotionPreference(){
  if(motion.matches){stopSlideshow();document.documentElement.classList.remove('js-motion');}
  document.querySelector('#slide-play').disabled=motion.matches;
  document.querySelector('#slide-play').title=motion.matches?'Automatic playback is disabled for reduced motion':'';
}
motion.addEventListener('change',updateMotionPreference);
showSlide(0);updateMotionPreference();
selectConcept(tabs.find(tab=>tab.dataset.concept===location.hash.slice(1))||tabs[0],false);
