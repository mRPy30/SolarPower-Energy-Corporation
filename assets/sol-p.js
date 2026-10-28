(() => {
    'use strict';
    const root = document.getElementById('solp-assistant');
    const bubble = document.getElementById('solp-greeting');
    const dock = document.querySelector('.floating-chat-dock');
    if (!root || !bubble || !dock || root.dataset.ready) return;
    root.dataset.ready = 'true';
    const button = root.querySelector('button');
    const close = bubble.querySelector('.solp-close');
    const chat = bubble.querySelector('.solp-chat');
    const panel = document.getElementById('chatWidget');
    const reduced = matchMedia('(prefers-reduced-motion: reduce)');
    let hideTimer, frame, userOpened = false;
    const key = 'solp-greetings-v1';
    let visits = {count:0,last:0,dismissed:false};
    try { visits = {...visits, ...JSON.parse(sessionStorage.getItem(key) || '{}')}; } catch (_) {}
    const save = () => { try {sessionStorage.setItem(key, JSON.stringify(visits));} catch (_) {} };
    const chatAvailable = () => panel && typeof window.toggleChat === 'function';
    function hide() {
        clearTimeout(hideTimer);
        bubble.hidden = true;
        button.setAttribute('aria-expanded', 'false');
    }
    function position() {
        frame = null;
        const d = dock.getBoundingClientRect();
        const chatOpen = panel && panel.classList.contains('open');
        root.hidden = chatOpen || d.width === 0 || document.hidden;
        if (root.hidden) {hide(); return;}
        const v = window.visualViewport;
        const leftEdge = v ? v.offsetLeft : 0;
        const topEdge = v ? v.offsetTop : 0;
        const width = v ? v.width : innerWidth;
        const height = v ? v.height : innerHeight;
        const w = root.offsetWidth, h = root.offsetHeight;
        const left = Math.max(leftEdge + 8, Math.min(leftEdge + width - w - 8, d.right - w));
        const top = d.top - h - 14;
        // Hide while the keyboard or very small viewport leaves no room above the dock.
        if (top < topEdge + 22 || d.top > topEdge + height) {root.hidden = true; hide(); return;}
        root.style.left = left + 'px'; root.style.top = top + 'px';
        if (!bubble.hidden) {
            const available = left - leftEdge - 24;
            bubble.style.width = Math.min(280, Math.max(170, available)) + 'px';
            const bw = bubble.offsetWidth, bh = bubble.offsetHeight;
            const beside = available >= 170;
            bubble.style.left = Math.max(leftEdge + 12, Math.min(leftEdge + width - bw - 12, beside ? left - bw - 12 : left + w - bw)) + 'px';
            bubble.style.top = Math.max(topEdge + 12, Math.min(topEdge + height - bh - 12, beside ? top + 8 : top - bh - 12)) + 'px';
        }
    }
    function schedule() {if (!frame) frame = requestAnimationFrame(position);}
    function show(manual = false) {
        position();
        if (root.hidden) return;
        clearTimeout(hideTimer);
        userOpened = manual;
        chat.hidden = !chatAvailable();
        bubble.hidden = false;
        button.setAttribute('aria-expanded', 'true');
        position();
        if (!manual) hideTimer = setTimeout(hide, 6500);
    }
    function celebrate() {
        if (reduced.matches || button.classList.contains('is-excited')) return;
        button.classList.add('is-excited');
        setTimeout(() => button.classList.remove('is-excited'), 960);
    }
    button.addEventListener('pointerenter', e => {if (e.pointerType === 'mouse') celebrate();});
    button.addEventListener('click', () => {celebrate(); show(true);});
    close.addEventListener('click', () => {visits.dismissed = true; save(); hide(); button.focus({preventScroll:true});});
    chat.addEventListener('click', () => {
        if (chatAvailable() && !panel.classList.contains('open')) window.toggleChat();
        hide(); schedule();
        const control = panel && panel.querySelector('button');
        if (control) control.focus({preventScroll:true});
    });
    document.addEventListener('keydown', e => {if (e.key === 'Escape' && !bubble.hidden) {hide(); button.focus({preventScroll:true});}});
    bubble.addEventListener('focusin', () => clearTimeout(hideTimer));
    bubble.addEventListener('pointerenter', () => clearTimeout(hideTimer));
    bubble.addEventListener('pointerleave', () => {if (!userOpened && !bubble.contains(document.activeElement)) hideTimer = setTimeout(hide, 6500);});
    function automaticGreeting() {
        const typing = document.activeElement && document.activeElement.matches('input,textarea,select,[contenteditable="true"]');
        if (document.hidden || visits.dismissed || visits.count >= 2 || typing || !bubble.hidden || Date.now() - visits.last < 300000) return;
        show();
        if (!bubble.hidden) {visits.count++; visits.last = Date.now(); save();}
    }
    new ResizeObserver(schedule).observe(dock);
    if (panel) new MutationObserver(schedule).observe(panel, {attributes:true, attributeFilter:['class']});
    addEventListener('resize', schedule, {passive:true});
    addEventListener('scroll', schedule, {passive:true});
    document.addEventListener('visibilitychange', schedule);
    if (window.visualViewport) {window.visualViewport.addEventListener('resize', schedule); window.visualViewport.addEventListener('scroll', schedule);}
    position();
    setTimeout(automaticGreeting, 1800);
    setInterval(automaticGreeting, 60000);
})();
