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
    let exitTimer, hideTimer, frame, userOpened = false;

    // A fresh page visit always gets its introduction; dismissal applies to the current page.
    let visits = { count: 0, last: 0, dismissed: false };
    const save = () => {};
    const greeting = bubble.querySelector('p').textContent;
    let assistTimer;
    let drag = null, holdTimer, returningTimer, suppressClick = false;

    const chatAvailable = () => panel && typeof window.toggleChat === 'function';

    function hide() {
        clearTimeout(hideTimer);
        clearTimeout(exitTimer);
        bubble.classList.remove('is-leaving');
        bubble.hidden = true;
        button.setAttribute('aria-expanded', 'false');
    }

    const dockContainer = dock.closest('.floating-chat-dock-container');
    const overlaps = (a, b) => a.left < b.right && a.right > b.left && a.top < b.bottom && a.bottom > b.top;

    function position() {
        frame = null;
        if (drag && drag.active) return;
        const chatOpen = panel && panel.classList.contains('open');
        root.hidden = document.hidden;
        if (root.hidden) { hide(); return; }

        const v = window.visualViewport;
        const x = v ? v.offsetLeft : 0;
        const y = v ? v.offsetTop : 0;
        const width = v ? v.width : innerWidth;
        const height = v ? v.height : innerHeight;
        const mobile = width <= 576;
        const w = root.offsetWidth, h = root.offsetHeight;

        // SOL-P fixed at bottom-right in floating-chat position
        const left = x + width - w - (mobile ? 12 : 24);
        let top = y + height - h - (mobile ? 54 : 16);
        if (top < y + 8) top = y + 8;

        root.style.left = left + 'px';
        root.style.top = top + 'px';

        // Compact Viber & WhatsApp dock along middle-right edge
        if (dockContainer) {
            const dh = dock.offsetHeight || 116;
            const dt = Math.max(y + 12, Math.min(y + (height - dh) / 2, top - dh - 20));
            dockContainer.style.top = dt + 'px';
            dockContainer.style.transform = 'none';
        }

        const d = dock.getBoundingClientRect();

        // Responsive positioning of existing chatbot panel when opened
        if (panel) {
            panel.style.bottom = (innerHeight - top + 12) + 'px';
            panel.style.maxHeight = Math.max(120, top - y - 24) + 'px';
            panel.style.right = (mobile ? 68 : 84) + 'px';
            panel.style.left = 'auto';
            panel.style.width = Math.min(400, width - (mobile ? 80 : 108)) + 'px';
        }

        button.setAttribute('aria-controls', chatAvailable() ? 'chatWidget' : 'solp-greeting');
        button.setAttribute('aria-expanded', String(chatOpen || !bubble.hidden));

        if (chatOpen) { hide(); button.setAttribute('aria-expanded', 'true'); return; }

        if (!bubble.hidden) {
            bubble.style.width = Math.min(280, Math.max(170, left - x - 20)) + 'px';
            const bw = bubble.offsetWidth, bh = bubble.offsetHeight;
            const candidates = [
                // 1. Beside SOL-P to the left
                { left: left - bw - 12, top: Math.max(y + 8, Math.min(top + 8, y + height - bh - 12)) },
                // 2. Above SOL-P
                { left: Math.max(x + 8, Math.min(x + width - bw - 12, left + w - bw)), top: Math.max(y + 8, top - bh - 16) }
            ];

            const fit = candidates.find(r => {
                const rect = { left: r.left, right: r.left + bw, top: r.top, bottom: r.top + bh };
                const inViewport = rect.left >= x + 8 && rect.top >= y + 8 && rect.bottom <= y + height - 8;
                const hitDock = overlaps(rect, d);
                return inViewport && !hitDock;
            }) || candidates[0];

            bubble.style.left = Math.max(x + 8, fit.left) + 'px';
            bubble.style.top = Math.max(y + 8, fit.top) + 'px';
        }
    }

    function schedule() { if (!frame) frame = requestAnimationFrame(position); }

    function dismissSmoothly() {
        if (reduced.matches) { hide(); return; }
        bubble.classList.add('is-leaving');
        exitTimer = setTimeout(hide, 220);
    }

    function show(manual = false, message = greeting) {
        if (panel && panel.classList.contains('open')) return;
        bubble.querySelector('p').textContent = message;
        position();
        if (root.hidden) return;
        clearTimeout(hideTimer);
        clearTimeout(exitTimer);
        bubble.classList.remove('is-leaving');
        userOpened = manual;
        chat.hidden = !chatAvailable();
        bubble.hidden = false;
        button.setAttribute('aria-expanded', 'true');
        position();
        if (!manual) hideTimer = setTimeout(dismissSmoothly, 6500);
    }

    function celebrate() {
        if (drag || reduced.matches || button.classList.contains('is-excited')) return;
        button.classList.remove('is-waving', 'is-assisting');
        clearTimeout(assistTimer);
        button.classList.add('is-excited');
        setTimeout(() => button.classList.remove('is-excited'), 960);
    }

    button.addEventListener('pointerenter', e => {
        if (e.pointerType === 'mouse') {
            celebrate();
            if (!visits.dismissed) show();
        }
    });

    function openChat() {
        if (!chatAvailable()) { show(true); return; }
        if (!panel.classList.contains('open')) window.toggleChat();
        button.classList.remove('is-assisting');
        clearTimeout(assistTimer);
        hide();
        position();
        const control = panel.querySelector('button');
        if (control) control.focus({ preventScroll: true });
    }

    button.addEventListener('click', e => {
        if (suppressClick) {e.preventDefault(); suppressClick = false; return;}
        openChat();
    });
    function grab() {
        if (!drag) return;
        drag.active = true;
        suppressClick = true;
        hide();
        clearTimeout(assistTimer);
        button.classList.remove('is-excited','is-waving','is-assisting','is-hello');
        button.classList.add('is-held');
    }
    button.addEventListener('pointerdown', e => {
        if (!e.isPrimary || e.button !== 0 || (panel && panel.classList.contains('open'))) return;
        clearTimeout(returningTimer);
        root.classList.remove('is-returning');
        suppressClick = false;
        const r = root.getBoundingClientRect();
        drag = {id:e.pointerId,x:e.clientX,y:e.clientY,left:r.left,top:r.top,active:false};
        button.setPointerCapture(e.pointerId);
        holdTimer = setTimeout(grab,300);
    });
    button.addEventListener('pointermove', e => {
        if (!drag || e.pointerId !== drag.id) return;
        const dx=e.clientX-drag.x, dy=e.clientY-drag.y;
        if (!drag.active && Math.hypot(dx,dy)>8) {clearTimeout(holdTimer);grab();}
        if (!drag.active) return;
        const v=window.visualViewport;
        const x=v?v.offsetLeft:0,y=v?v.offsetTop:0,w=v?v.width:innerWidth,h=v?v.height:innerHeight;
        root.style.left=Math.max(x+4,Math.min(x+w-root.offsetWidth-4,drag.left+dx))+'px';
        root.style.top=Math.max(y+4,Math.min(y+h-root.offsetHeight-4,drag.top+dy))+'px';
    });
    function release(e) {
        if (!drag || (e.pointerId !== undefined && e.pointerId !== drag.id)) return;
        clearTimeout(holdTimer);
        const wasHeld=drag.active,id=drag.id;
        drag=null;
        button.classList.remove('is-held');
        if (wasHeld && !reduced.matches) root.classList.add('is-returning');
        if (button.hasPointerCapture(id)) button.releasePointerCapture(id);
        position();
        returningTimer=setTimeout(()=>root.classList.remove('is-returning'),450);
    }
    button.addEventListener('pointerup',release);
    button.addEventListener('pointercancel',release);
    button.addEventListener('lostpointercapture',release);
    button.addEventListener('contextmenu',e=>e.preventDefault());
    window.addEventListener('blur',()=>release({}));
    setInterval(()=>{
        if (!matchMedia('(hover: none), (pointer: coarse)').matches || drag || document.hidden || root.hidden || (panel && panel.classList.contains('open')) || button.classList.contains('is-assisting')) return;
        celebrate();
    },26000);

    // Automatic waving every 10 seconds
    setInterval(() => {
        if (drag || reduced.matches || document.hidden || root.hidden || (panel && panel.classList.contains('open')) || button.classList.contains('is-excited') || button.classList.contains('is-assisting')) return;
        button.classList.add('is-waving');
        setTimeout(() => button.classList.remove('is-waving'), 2250);
    }, 10000);

    // Gadget-assist animation presenting the solar tablet
    function assist() {
        if (drag || document.hidden || root.hidden || visits.dismissed || !bubble.hidden || (panel && panel.classList.contains('open')) || button.classList.contains('is-excited') || button.classList.contains('is-waving')) return;
        show(false, 'Let me help you find the best solar solution for your needs!');
        if (bubble.hidden) return;
        button.classList.add('is-assisting');
        assistTimer = setTimeout(() => button.classList.remove('is-assisting'), 6200);
    }
    setTimeout(assist, 18000);
    setInterval(assist, 180000);

    close.addEventListener('click', () => {
        visits.dismissed = true;
        save();
        hide();
        button.focus({ preventScroll: true });
    });

    chat.addEventListener('click', openChat);

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !bubble.hidden) {
            hide();
            button.focus({ preventScroll: true });
        }
    });

    bubble.addEventListener('focusin', () => clearTimeout(hideTimer));
    bubble.addEventListener('pointerenter', () => clearTimeout(hideTimer));
    bubble.addEventListener('pointerleave', () => {
        if (!userOpened && !bubble.contains(document.activeElement)) hideTimer = setTimeout(dismissSmoothly, 6500);
    });

    function automaticGreeting() {
        const typing = document.activeElement && document.activeElement.matches('input,textarea,select,[contenteditable="true"]');
        if (drag || document.hidden || visits.dismissed || visits.count >= 2 || typing || !bubble.hidden || Date.now() - visits.last < 300000) return;
        show();
        if (!bubble.hidden) {
            visits.count++;
            visits.last = Date.now();
            save();
        }
    }

    new ResizeObserver(schedule).observe(dock);
    if (panel) new MutationObserver(schedule).observe(panel, { attributes: true, attributeFilter: ['class'] });
    addEventListener('resize', schedule, { passive: true });
    addEventListener('scroll', schedule, { passive: true });
    document.addEventListener('visibilitychange', schedule);
    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', schedule);
        window.visualViewport.addEventListener('scroll', schedule);
    }

    position();
    setTimeout(() => {
        if (drag || document.hidden || root.hidden || (panel && panel.classList.contains('open'))) return;
        button.classList.add('is-hello');
        automaticGreeting();
        setTimeout(() => button.classList.remove('is-hello'), 4200);
    }, 900);
    setInterval(automaticGreeting, 60000);
})();
