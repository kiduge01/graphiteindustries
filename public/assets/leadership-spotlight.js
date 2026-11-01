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
