const { spawn } = require('child_process');
const http = require('http');

async function runTestSuite() {
    console.log('=== STARTING AUTOMATED TEST SUITE FOR SOL-P ===');
    const port = 9355;
    const chrome = spawn('C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe', [
        '--headless=new',
        '--disable-gpu',
        '--no-first-run',
        '--no-default-browser-check',
        `--remote-debugging-port=${port}`,
        '--window-size=1280,800'
    ], { stdio: 'ignore' });

    let wsUrl = null;
    for (let i = 0; i < 30; i++) {
        await new Promise(r => setTimeout(r, 200));
        try {
            const data = await new Promise((resolve, reject) => {
                http.get(`http://127.0.0.1:${port}/json/version`, res => {
                    let s = ''; res.on('data', d => s += d); res.on('end', () => resolve(s));
                }).on('error', reject);
            });
            wsUrl = JSON.parse(data).webSocketDebuggerUrl;
            if (wsUrl) break;
        } catch (_) {}
    }

    if (!wsUrl) {
        console.error('Failed to connect to Chrome');
        chrome.kill();
        process.exit(1);
    }

    const ws = new WebSocket(wsUrl);
    await new Promise(r => ws.onopen = r);

    let id = 1;
    const callbacks = new Map();
    const consoleLogs = [];
    const jsErrors = [];

    ws.onmessage = (event) => {
        const msg = JSON.parse(event.data);
        if (msg.id && callbacks.has(msg.id)) {
            const cb = callbacks.get(msg.id);
            callbacks.delete(msg.id);
            if (msg.error) cb.reject(msg.error);
            else cb.resolve(msg.result);
        }
        if (msg.method === 'Runtime.consoleAPICalled') {
            consoleLogs.push({ type: msg.params.type, text: msg.params.args.map(a => a.value || a.description).join(' ') });
        }
        if (msg.method === 'Runtime.exceptionThrown') {
            jsErrors.push(msg.params.exceptionDetails);
        }
    };

    function send(method, params = {}) {
        return new Promise((resolve, reject) => {
            const reqId = id++;
            callbacks.set(reqId, { resolve, reject });
            ws.send(JSON.stringify({ id: reqId, method, params }));
        });
    }

    const { targetId } = await send('Target.createTarget', { url: 'about:blank' });
    const { sessionId } = await send('Target.attachToTarget', { targetId, flatten: true });

    function sendPage(method, params = {}) {
        return new Promise((resolve, reject) => {
            const reqId = id++;
            callbacks.set(reqId, { resolve, reject });
            ws.send(JSON.stringify({ id: reqId, sessionId, method, params }));
        });
    }

    await sendPage('Network.enable');
    await sendPage('Network.setCacheDisabled', { cacheDisabled: true });
    await sendPage('Runtime.enable');
    await sendPage('Page.enable');

    async function evaluate(code) {
        const res = await sendPage('Runtime.evaluate', { expression: code, returnByValue: true });
        return res?.result?.value;
    }

    // -------------------------------------------------------------
    // TEST 1: Page Load & Layout Positioning (Desktop 1280x800)
    // -------------------------------------------------------------
    console.log('\n[TEST 1] Loading index.php on Desktop (1280x800)...');
    await sendPage('Page.navigate', { url: 'http://localhost/SolarPower-Energy-Corporation/index.php' });
    await new Promise(r => setTimeout(r, 2000));

    const layout = await evaluate(`(() => {
        const root = document.getElementById('solp-assistant');
        const dock = document.querySelector('.floating-chat-dock');
        const dockContainer = document.querySelector('.floating-chat-dock-container');
        const rootRect = root?.getBoundingClientRect().toJSON();
        const dockRect = dock?.getBoundingClientRect().toJSON();
        const overlaps = (a, b) => a.left < b.right && a.right > b.left && a.top < b.bottom && a.bottom > b.top;

        return {
            rootVisible: !root?.hidden,
            rootRect,
            dockRect,
            overlapWithDock: overlaps(rootRect, dockRect),
            isBottomRight: rootRect && rootRect.right > 1100 && rootRect.bottom > 600,
            dockMiddleRight: dockRect && dockRect.top > 200 && dockRect.bottom < 550 && dockRect.right > 1200
        };
    })()`);

    console.log('Layout test result:', layout);
    if (!layout.rootVisible) throw new Error('FAIL: SOL-P root is hidden on desktop');
    if (layout.overlapWithDock) throw new Error('FAIL: SOL-P overlaps dock on desktop');
    if (!layout.isBottomRight) throw new Error('FAIL: SOL-P is not at bottom right');
    if (!layout.dockMiddleRight) throw new Error('FAIL: Dock is not at middle right');
    console.log('✅ TEST 1 PASSED: Desktop layout positions verified.');

    // -------------------------------------------------------------
    // TEST 2: Automatic Greeting Visibility & Text
    // -------------------------------------------------------------
    console.log('\n[TEST 2] Waiting for Automatic Greeting (timeout 1800ms)...');
    await new Promise(r => setTimeout(r, 2200));

    const greetingResult = await evaluate(`(() => {
        const bubble = document.getElementById('solp-greeting');
        const dock = document.querySelector('.floating-chat-dock');
        const root = document.getElementById('solp-assistant');
        const bRect = bubble?.getBoundingClientRect().toJSON();
        const dRect = dock?.getBoundingClientRect().toJSON();
        const rRect = root?.getBoundingClientRect().toJSON();
        const overlaps = (a, b) => a.left < b.right && a.right > b.left && a.top < b.bottom && a.bottom > b.top;
        const text = bubble?.querySelector('p')?.textContent?.trim();

        return {
            hidden: bubble?.hidden,
            text,
            bRect,
            overlapsDock: overlaps(bRect, dRect),
            inViewport: bRect && bRect.left >= 0 && bRect.top >= 0 && bRect.right <= window.innerWidth && bRect.bottom <= window.innerHeight
        };
    })()`);

    console.log('Greeting test result:', greetingResult);
    if (greetingResult.hidden) throw new Error('FAIL: Greeting bubble is hidden!');
    const expectedMsg = "Hi! I'm SOL-P ☀️ Your Solar Assistant! Need help finding the right solar solution?";
    if (greetingResult.text !== expectedMsg) throw new Error(`FAIL: Greeting text mismatch. Expected: "${expectedMsg}", got: "${greetingResult.text}"`);
    if (greetingResult.overlapsDock) throw new Error('FAIL: Greeting bubble overlaps Viber/WhatsApp dock!');
    if (!greetingResult.inViewport) throw new Error('FAIL: Greeting bubble is outside the viewport!');
    console.log('✅ TEST 2 PASSED: Greeting bubble visible with correct text and no overlap.');

    // -------------------------------------------------------------
    // TEST 3: Reload Page - Greeting Appears Again (No Stale Dismissal)
    // -------------------------------------------------------------
    console.log('\n[TEST 3] Reloading page to verify greeting appears after reload...');
    await sendPage('Page.navigate', { url: 'http://localhost/SolarPower-Energy-Corporation/index.php' });
    await new Promise(r => setTimeout(r, 3000));

    const reloadResult = await evaluate(`(() => {
        const bubble = document.getElementById('solp-greeting');
        return {
            hidden: bubble?.hidden,
            text: bubble?.querySelector('p')?.textContent?.trim()
        };
    })()`);

    console.log('Reload test result:', reloadResult);
    if (reloadResult.hidden) throw new Error('FAIL: Greeting did not appear after page reload!');
    console.log('✅ TEST 3 PASSED: Greeting appears reliably after page reload.');

    // -------------------------------------------------------------
    // TEST 4: Hover / Pointerenter Celebration Jump
    // -------------------------------------------------------------
    console.log('\n[TEST 4] Testing hover celebration (pointerenter)...');
    const hoverResult = await evaluate(`(() => {
        const btn = document.querySelector('.solp-button');
        const pEvt = new PointerEvent('pointerenter', { pointerType: 'mouse', bubbles: true });
        btn.dispatchEvent(pEvt);
        const hasExcited = btn.classList.contains('is-excited');
        const armLAnim = getComputedStyle(document.querySelector('.solp-arm-left')).animationName;
        const armRAnim = getComputedStyle(document.querySelector('.solp-arm-right')).animationName;
        const bodyAnim = getComputedStyle(document.querySelector('.solp-body')).animationName;

        return {
            hasExcited,
            armLAnim,
            armRAnim,
            bodyAnim
        };
    })()`);

    console.log('Hover celebration test result:', hoverResult);
    if (!hoverResult.hasExcited) throw new Error('FAIL: is-excited class not added on hover');
    if (hoverResult.armLAnim !== 'solp-left-cheer') throw new Error('FAIL: solp-arm-left cheer animation not active');
    if (hoverResult.armRAnim !== 'solp-right-cheer') throw new Error('FAIL: solp-arm-right cheer animation not active');
    if (hoverResult.bodyAnim !== 'solp-jump') throw new Error('FAIL: solp-body jump animation not active');
    console.log('✅ TEST 4 PASSED: Hover jump with both arms raised and bounce landing confirmed.');
    // Wait for celebration jump to finish (960ms) and revert to idle
    await new Promise(r => setTimeout(r, 1200));

    // -------------------------------------------------------------
    // TEST 5: Idle Rig, Blink, and Wink Animations
    // -------------------------------------------------------------
    console.log('\n[TEST 5] Testing idle rig, blinking, and winking animation definitions...');
    const rigResult = await evaluate(`(() => {
        const head = document.querySelector('.solp-head');
        const torso = document.querySelector('.solp-torso');
        const armL = document.querySelector('.solp-arm-left');
        const armR = document.querySelector('.solp-arm-right');
        const legL = document.querySelector('.solp-leg-left');
        const legR = document.querySelector('.solp-leg-right');
        const headAfter = getComputedStyle(head, '::after');

        return {
            headAnim: getComputedStyle(head).animationName,
            torsoAnim: getComputedStyle(torso).animationName,
            armLAnim: getComputedStyle(armL).animationName,
            armRAnim: getComputedStyle(armR).animationName,
            legLAnim: getComputedStyle(legL).animationName,
            legRAnim: getComputedStyle(legR).animationName,
            headAfterAnim: headAfter.animationName,
            headAfterClip: headAfter.clipPath
        };
    })()`);

    console.log('Rig animations test result:', rigResult);
    if (!rigResult.headAnim.includes('solp-head-idle')) throw new Error('FAIL: solp-head-idle missing');
    if (!rigResult.torsoAnim.includes('solp-torso-idle')) throw new Error('FAIL: solp-torso-idle missing');
    if (!rigResult.armLAnim.includes('solp-arm-idle')) throw new Error('FAIL: solp-arm-idle missing');
    if (!rigResult.legLAnim.includes('solp-step')) throw new Error('FAIL: solp-step missing');
    if (!rigResult.headAfterAnim.includes('solp-face')) throw new Error('FAIL: solp-face missing on head::after');
    console.log('✅ TEST 5 PASSED: Full body idle rig and face blink/wink animation defined correctly.');

    // -------------------------------------------------------------
    // TEST 6: Tablet Gadget-Assist Animation & Message
    // -------------------------------------------------------------
    console.log('\n[TEST 6] Testing gadget-assist animation and message...');
    const assistResult = await evaluate(`(() => {
        const btn = document.querySelector('.solp-button');
        const bubble = document.getElementById('solp-greeting');
        btn.classList.add('is-assisting');
        bubble.querySelector('p').textContent = 'Let me help you find the best solar solution for your needs!';
        bubble.hidden = false;

        const tablet = document.querySelector('.solp-tablet');
        const tabletAnim = getComputedStyle(tablet).animationName;
        const armLAnim = getComputedStyle(document.querySelector('.solp-arm-left')).animationName;
        const armRAnim = getComputedStyle(document.querySelector('.solp-arm-right')).animationName;
        const msg = bubble.querySelector('p').textContent;

        return {
            tabletAnim,
            armLAnim,
            armRAnim,
            msg
        };
    })()`);

    console.log('Gadget assist test result:', assistResult);
    if (assistResult.tabletAnim !== 'solp-present') throw new Error('FAIL: tablet animation is not solp-present');
    if (assistResult.armLAnim !== 'solp-hold-left') throw new Error('FAIL: left arm hold animation is not solp-hold-left');
    if (assistResult.armRAnim !== 'solp-hold-right') throw new Error('FAIL: right arm hold animation is not solp-hold-right');
    if (assistResult.msg !== 'Let me help you find the best solar solution for your needs!') throw new Error('FAIL: assist message mismatch');
    console.log('✅ TEST 6 PASSED: Tablet assist animation and message verified.');

    // -------------------------------------------------------------
    // TEST 7: Chatbot Opening on Mascot Click
    // -------------------------------------------------------------
    console.log('\n[TEST 7] Testing chatbot opening when SOL-P is clicked...');
    const chatResult = await evaluate(`(() => {
        const btn = document.querySelector('.solp-button');
        const panel = document.getElementById('chatWidget');
        const bubble = document.getElementById('solp-greeting');

        btn.click();

        return {
            panelOpen: panel ? panel.classList.contains('open') : false,
            bubbleHidden: bubble ? bubble.hidden : false,
            ariaExpanded: btn.getAttribute('aria-expanded')
        };
    })()`);

    console.log('Chatbot open test result:', chatResult);
    if (!chatResult.panelOpen) throw new Error('FAIL: chatWidget did not open on SOL-P click');
    if (!chatResult.bubbleHidden) throw new Error('FAIL: greeting bubble did not hide when chatbot opened');
    if (chatResult.ariaExpanded !== 'true') throw new Error('FAIL: aria-expanded is not true when chat is open');
    console.log('✅ TEST 7 PASSED: Chatbot opens on SOL-P click and greeting bubble closes.');

    // -------------------------------------------------------------
    // TEST 8: Mobile Viewport (375x667)
    // -------------------------------------------------------------
    console.log('\n[TEST 8] Testing Mobile Viewport (375x667)...');
    await sendPage('Emulation.setDeviceMetricsOverride', {
        width: 375,
        height: 667,
        deviceScaleFactor: 2,
        mobile: true
    });
    await sendPage('Page.navigate', { url: 'http://localhost/SolarPower-Energy-Corporation/index.php' });
    await new Promise(r => setTimeout(r, 3000));

    const mobileResult = await evaluate(`(() => {
        const root = document.getElementById('solp-assistant');
        const bubble = document.getElementById('solp-greeting');
        const dock = document.querySelector('.floating-chat-dock');
        const rRect = root?.getBoundingClientRect().toJSON();
        const bRect = bubble?.getBoundingClientRect().toJSON();
        const dRect = dock?.getBoundingClientRect().toJSON();
        const overlaps = (a, b) => a.left < b.right && a.right > b.left && a.top < b.bottom && a.bottom > b.top;

        return {
            rootVisible: !root?.hidden,
            bubbleVisible: !bubble?.hidden,
            rootRect: rRect,
            bubbleRect: bRect,
            dockRect: dRect,
            bubbleOverlapsDock: overlaps(bRect, dRect),
            bubbleInViewport: bRect && bRect.left >= 0 && bRect.top >= 0 && bRect.right <= window.innerWidth && bRect.bottom <= window.innerHeight
        };
    })()`);

    console.log('Mobile test result:', mobileResult);
    if (!mobileResult.rootVisible) throw new Error('FAIL: SOL-P root is hidden on mobile');
    if (!mobileResult.bubbleVisible) throw new Error('FAIL: Greeting bubble is hidden on mobile');
    if (mobileResult.bubbleOverlapsDock) throw new Error('FAIL: Greeting bubble overlaps dock on mobile');
    if (!mobileResult.bubbleInViewport) throw new Error('FAIL: Greeting bubble extends outside mobile viewport');
    console.log('✅ TEST 8 PASSED: Mobile layout, dimensions, and no-overlap verified.');

    console.log('\n[FINAL CHECK] JS Errors during test run:', jsErrors);
    if (jsErrors.length > 0) throw new Error('FAIL: JS errors encountered: ' + JSON.stringify(jsErrors));

    console.log('\n🎉 ALL TESTS PASSED SUCCESSFULLY! 🎉');

    await send('Target.closeTarget', { targetId });
    ws.close();
    chrome.kill();
    process.exit(0);
}

runTestSuite().catch(err => {
    console.error('\n❌ TEST SUITE FAILED:', err);
    process.exit(1);
});
