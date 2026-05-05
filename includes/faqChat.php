    <link rel="stylesheet" href="assets/style.css?v=20260223-chatbot">

    <!-- Floating Chat FAB Wrapper -->
    <div class="chat-fab-wrapper" id="chatFabWrapper">
        <!-- "Talk to our agent now!" tooltip -->
        <span class="chat-fab-tooltip" id="chatTooltip" onclick="toggleChat()">
            Talk to our agent now!
        </span>

        <!-- Toggle Button -->
        <button class="chat-fab" id="chatFab" onclick="toggleChat()" aria-label="Open Chat">
            <i class="fas fa-comment-dots fab-open"></i>
            <i class="fas fa-times fab-close"></i>
        </button>
    </div>

    <!-- Chat Widget Panel -->
    <div class="chatbot-widget" id="chatWidget">
        <div class="chat-header">
            <div class="chat-header-avatar"><i class="fas fa-solar-panel"></i></div>
            <div class="chat-header-info">
                <h3>SolarPower Energy Support</h3>
                <span>Online &bull; Replies instantly</span>
            </div>
            <div class="chat-header-actions">
                <button onclick="resetChat()" title="Reset Chat"><i class="fas fa-redo-alt"></i></button>
                <button onclick="toggleChat()" title="Close"><i class="fas fa-times"></i></button>
            </div>
        </div>
        <div class="chat-body" id="chatBody"></div>
        <div class="chat-cta">
            <a href="https://m.me/757917280729034" target="_blank" class="chat-talk-btn">
                <i class="fas fa-phone-alt"></i> Talk to our agent via Messenger
            </a>
            <div class="chat-powered">Powered by SolarPower Energy Corporation</div>
        </div>
    </div>


    <script>
        // Chatbot Data & Logic
        const faqData = [{
                id: 'cost',
                question: 'How much does solar cost?',
                icon: 'fa-coins',
                iconBg: '#fef3c7',
                iconColor: '#f59e0b',
                answer: `Solar installation costs vary based on your system size and energy needs. Here's a typical breakdown for the Philippines:<br><br><ul><li><strong>Small Residential (3-5kW):</strong> ₱150,000 – ₱250,000</li><li><strong>Medium Residential (8-12kW):</strong> ₱400,000 – ₱600,000</li><li><strong>Large Residential/Commercial (20kW+):</strong> Custom pricing based on requirements</li></ul>💳 We offer <span class="highlight">flexible payment plans</span> and can help you access government incentives and financing options to reduce upfront costs. Many of our customers finance their systems and see immediate savings on their monthly bills!`
            },
            {
                id: 'roi',
                question: 'How long is the ROI (Return on Investment)?',
                icon: 'fa-chart-line',
                iconBg: '#dbeafe',
                iconColor: '#3b82f6',
                answer: `Most of our Filipino customers achieve <span class="highlight">full ROI within 4-6 years</span>, depending on several factors:<br><br><ul><li><strong>Current electricity bill:</strong> Higher bills = faster payback</li><li><strong>System size and efficiency:</strong> Quality components maximize production</li><li><strong>Location and sunlight:</strong> Philippines has excellent solar potential!</li><li><strong>Net metering:</strong> Selling excess power speeds up ROI</li><li><strong>Electricity rate increases:</strong> MERALCO rates typically rise 3-5% annually</li></ul>⚡ After payback, you'll enjoy <strong>FREE electricity for 20+ years</strong> since solar panels last 25-30 years with proper maintenance. That's decades of zero or minimal electric bills!`
            },
            {
                id: 'brownout',
                question: 'What happens during brownouts or power outages?',
                icon: 'fa-bolt',
                iconBg: '#fce7f3',
                iconColor: '#ec4899',
                answer: `This is one of the most common questions in the Philippines! The answer depends on your system type:<br><br><ul><li><strong>Grid-Tied System:</strong> Automatically shuts off during outages for safety (to protect utility workers). When power returns, your system automatically reconnects.</li><li><strong>Hybrid System (with battery backup):</strong> You'll have <span class="highlight">continuous power during brownouts!</span> Your batteries keep essential appliances running — perfect for frequent Philippine outages.</li><li><strong>Off-Grid System:</strong> Complete independence from the grid with 24/7 backup power from your battery bank.</li></ul>🔋 <strong>Our recommendation:</strong> Hybrid systems are ideal for the Philippines due to frequent brownouts. Solar charges batteries during the day, and stored power covers outages and nighttime!`
            },
            {
                id: 'netmeter',
                question: 'Do you assist with net-metering applications?',
                icon: 'fa-file-alt',
                iconBg: '#ede9fe',
                iconColor: '#8b5cf6',
                answer: `<strong>Yes, absolutely!</strong> We handle the entire net-metering process from start to finish:<br><br><ul><li>✅ <strong>Document preparation:</strong> We compile all required forms and technical specs</li><li>✅ <strong>Submission to utility:</strong> Coordination with MERALCO or your local distribution utility</li><li>✅ <strong>Bi-directional meter installation:</strong> We arrange the meter replacement</li><li>✅ <strong>Follow-up and approval:</strong> We track your application until approved</li><li>✅ <strong>Final inspection:</strong> We coordinate with ERC and utility inspectors</li></ul>💡 With net-metering, <span class="highlight">excess energy goes back to the grid</span> and you get credits that reduce your bill even further. The process typically takes 2-4 months, and we handle all the paperwork!`
            },
            {
                id: 'maintenance',
                question: 'Is there maintenance required for solar panels?',
                icon: 'fa-tools',
                iconBg: '#d1fae5',
                iconColor: '#10b981',
                answer: `Great news — solar panels require <span class="highlight">very minimal maintenance!</span> No moving parts. Here's what's recommended:<br><br><ul><li><strong>Panel cleaning:</strong> 2-4 times per year to remove dust, leaves, and bird droppings</li><li><strong>Visual inspection:</strong> Check for physical damage or debris after typhoons</li><li><strong>Electrical inspection:</strong> Annual checkup of inverter, wiring, and connections</li><li><strong>Performance monitoring:</strong> Track daily production through our mobile app (we'll alert you to any issues)</li></ul>🛠️ <strong>We offer maintenance packages:</strong> quarterly cleaning & inspection, priority response, performance optimization, and warranty extensions. Systems run at peak efficiency for 25-30 years!`
            }
        ];

        const chatBody = document.getElementById('chatBody');
        const chatFab = document.getElementById('chatFab');
        const chatWidget = document.getElementById('chatWidget');
        let chatInited = false;

        function toggleChat() {
            const isOpen = chatWidget.classList.toggle('open');
            chatFab.classList.toggle('open', isOpen);
            const wrapper = document.getElementById('chatFabWrapper');
            if (wrapper) wrapper.classList.toggle('open', isOpen);
            if (isOpen && !chatInited) {
                chatInited = true;
                initChat();
            }
        }

        function getTimeStr() {
            return new Date().toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function scrollToBottom() {
            requestAnimationFrame(() => {
                chatBody.scrollTop = chatBody.scrollHeight;
            });
        }

        function addBotMessage(html, showTime = true) {
            const row = document.createElement('div');
            row.className = 'msg-row bot';
            row.innerHTML = `<div class="msg-avatar"><i class="fas fa-solar-panel"></i></div><div class="msg-bubble">${html}${showTime ? '<span class="msg-time">' + getTimeStr() + '</span>' : ''}</div>`;
            chatBody.appendChild(row);
            scrollToBottom();
        }

        function addUserMessage(text) {
            const row = document.createElement('div');
            row.className = 'msg-row user';
            row.innerHTML = `<div class="msg-avatar"><i class="fas fa-user"></i></div><div class="msg-bubble">${text}<span class="msg-time">${getTimeStr()}</span></div>`;
            chatBody.appendChild(row);
            scrollToBottom();
        }

        function showTyping() {
            const el = document.createElement('div');
            el.className = 'typing-indicator';
            el.id = 'typingIndicator';
            el.innerHTML = `<div class="msg-avatar" style="background:#e8f5e9;color:#2d5016;"><i class="fas fa-solar-panel"></i></div><div class="typing-dots"><span></span><span></span><span></span></div>`;
            chatBody.appendChild(el);
            scrollToBottom();
        }

        function hideTyping() {
            const el = document.getElementById('typingIndicator');
            if (el) el.remove();
        }

        function renderMenu() {
            const old = chatBody.querySelector('.msg-row.topics-row');
            if (old) old.remove();
            const row = document.createElement('div');
            row.className = 'msg-row bot topics-row';
            let btnsHtml = '';
            faqData.forEach(faq => {
                btnsHtml += `<button class="inline-topic-btn" data-faq="${faq.id}"><div class="qa-icon" style="background:${faq.iconBg};color:${faq.iconColor};"><i class="fas ${faq.icon}"></i></div>${faq.question}</button>`;
            });
            row.innerHTML = `<div class="msg-avatar"><i class="fas fa-solar-panel"></i></div><div class="msg-bubble"><div class="inline-topics">${btnsHtml}</div></div>`;
            chatBody.appendChild(row);
            row.querySelectorAll('.inline-topic-btn').forEach(btn => {
                btn.addEventListener('click', () => handleQuestion(btn.dataset.faq));
            });
            scrollToBottom();
        }
        let isBusy = false;

        function handleQuestion(id) {
            if (isBusy) return;
            const faq = faqData.find(f => f.id === id);
            if (!faq) return;
            isBusy = true;
            const topicsRow = chatBody.querySelector('.msg-row.topics-row');
            if (topicsRow) topicsRow.remove();
            addUserMessage(faq.question);
            showTyping();
            const delay = Math.min(900 + faq.answer.length * 1, 2200);
            setTimeout(() => {
                hideTyping();
                addBotMessage(faq.answer);
                setTimeout(() => {
                    addBotMessage(`Would you like to know about something else? Choose a topic below. 👇`, false);
                    renderMenu();
                    isBusy = false;
                }, 500);
            }, delay);
        }

        function resetChat() {
            chatBody.innerHTML = '';
            initChat();
        }

        function initChat() {
            showTyping();
            setTimeout(() => {
                hideTyping();
                addBotMessage(`Hi! 👋 Welcome to <strong>SolarPower Energy</strong>. I'm here to answer your questions. Choose a topic below!`);
                setTimeout(() => {
                    renderMenu();
                }, 300);
            }, 1200);
        }
    </script>