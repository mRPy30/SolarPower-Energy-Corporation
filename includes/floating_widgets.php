<!-- Multi-Channel Floating Chat Dock & Standalone Tracking System Include -->
<style>
    /* -------------------------------------------------------------
       STANDALONE TRACKING BUTTON (Inspired by image_3769e9.png)
       ------------------------------------------------------------- */
    .custom-floating-track-btn {
        position: fixed;
        bottom: 260px; /* Positioned exactly at 260px as requested */
        right: 25px;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #F2A900; /* Rich Solar Gold/Orange */
        box-shadow: 0 4px 15px rgba(242, 169, 0, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1045;
        transition: all 0.2s ease-in-out;
        border: none;
        text-decoration: none;
        cursor: pointer;
    }
    
    .custom-floating-track-btn svg {
        width: 28px;
        height: 28px;
        fill: #ffffff;
        transition: transform 0.2s ease-in-out;
    }
    
    .custom-floating-track-btn:hover {
        transform: translateY(-3px) scale(1.05);
        filter: drop-shadow(0 0 8px rgba(242, 169, 0, 0.6));
        box-shadow: 0 6px 20px rgba(242, 169, 0, 0.6);
    }

    /* -------------------------------------------------------------
       MULTI-CHANNEL FLOATING CHAT DOCK (Inspired by image_3769cd.png)
       ------------------------------------------------------------- */
    .floating-chat-dock-container {
        position: fixed;
        bottom: 25px;
        right: 25px;
        z-index: 1050;
        display: flex;
        align-items: center;
    }

    /* Left Tooltip: aligned vertically with the center of the dock */
    .floating-chat-tooltip {
        background: #2D5016; /* Dark Green */
        color: #ffffff;
        font-family: 'Outfit', 'Inter', 'Segoe UI', sans-serif;
        font-size: 14px;
        font-weight: 700;
        padding: 10px 20px;
        border-radius: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        white-space: nowrap;
        margin-right: 15px;
        position: absolute;
        right: 100%;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        opacity: 1;
        transition: opacity 0.3s ease, transform 0.3s ease;
        border: 1px solid rgba(45, 80, 22, 0.2);
        animation: tooltipFloat 3s ease-in-out infinite;
    }

    /* Tooltip speech bubble pointer pointing to the dock */
    .floating-chat-tooltip::after {
        content: "";
        position: absolute;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
        border-width: 6px;
        border-style: solid;
        border-color: transparent transparent transparent #2D5016;
    }

    @keyframes tooltipFloat {
        0%, 100% {
            transform: translateY(-50%) translateX(0);
        }
        50% {
            transform: translateY(-50%) translateX(-5px);
        }
    }

    /* Vertical Dock Panel - Sleek White Pill Grid */
    .floating-chat-dock {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 40px;
        padding: 12px 8px; /* Strict padding */
        width: 61px; /* Explicit width to prevent layout squash */
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px; /* Strict gap */
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.6);
        transition: all 0.3s ease;
    }

    /* Strict Spacing and Dimensions for Channel Icons */
    .chat-channel {
        width: 45px; /* Explicit width */
        height: 45px; /* Explicit height */
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        cursor: pointer;
        border: none;
        position: relative;
        padding: 0;
        text-decoration: none;
    }

    .chat-channel svg {
        width: 24px;
        height: 24px;
        fill: #ffffff;
        transition: transform 0.3s ease;
    }

    /* Icon 1: Viber (Purple #7360F2) */
    .viber-channel {
        background: #7360F2;
        box-shadow: 0 4px 10px rgba(115, 96, 242, 0.3);
    }

    /* Icon 2: WhatsApp (Green #25D366) */
    .whatsapp-channel {
        background: #25D366;
        box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3);
    }

    /* Icon 3: Live Support / CRM (Forest Green #2D5016 - matching premium brand theme) */
    .crm-channel {
        background: #2D5016;
        box-shadow: 0 4px 10px rgba(45, 80, 22, 0.3);
    }

    .chat-channel:hover {
        transform: scale(1.1);
    }

    /* Pulse online notification badge for CRM/live support chat icon */
    .online-pulse-badge {
        position: absolute;
        top: 0;
        right: 0;
        width: 11px;
        height: 11px;
        background-color: #22C55E;
        border-radius: 50%;
        border: 2px solid #ffffff;
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
        animation: pulseAnimation 1.8s infinite;
    }

    @keyframes pulseAnimation {
        0% {
            transform: scale(0.9);
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 6px rgba(34, 197, 94, 0);
        }
        100% {
            transform: scale(0.9);
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
        }
    }

    /* Override and hide the old chat FAB wrapper completely */
    #chatFabWrapper {
        display: none !important;
    }

    /* -------------------------------------------------------------
       MOBILE RESPONSIVENESS
       ------------------------------------------------------------- */
    @media (max-width: 576px) {
        .floating-chat-dock-container {
            bottom: 15px;
            right: 15px;
            transform: scale(0.85);
            transform-origin: bottom right;
        }
        
        .custom-floating-track-btn {
            bottom: 210px;
            right: 15px;
            transform: scale(0.85);
            transform-origin: bottom right;
        }

        /* Dynamically hide tooltip to prevent screen clutter */
        .floating-chat-tooltip {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
        }
    }
</style>

<!-- Standalone Tracking System Button -->
<button onclick="triggerTracking()" class="custom-floating-track-btn" aria-label="Track Order" title="Track Order">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm12 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm1.5-6.5H17V9h2.5l2 3H19.5z"/>
    </svg>
</button>

<!-- Multi-Channel Floating Chat Dock -->
<div class="floating-chat-dock-container">
    <div class="floating-chat-tooltip" id="floatingChatTooltip">
        Talk to our agent now!
    </div>
    
    <div class="floating-chat-dock">
        <!-- Viber Chat Icon (Purple #7360F2 - Official Font Awesome Viber Logo) -->
        <a id="floating-viber-link" href="#" target="_blank" class="chat-channel viber-channel" aria-label="Chat on Viber" title="Chat on Viber">
            <i class="fab fa-viber" style="font-size: 24px; color: #ffffff; line-height: 1;"></i>
        </a>
        
        <!-- WhatsApp Chat Icon (Green #25D366) -->
        <a id="floating-whatsapp-link" href="#" target="_blank" class="chat-channel whatsapp-channel" aria-label="Chat on WhatsApp" title="Chat on WhatsApp">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12.004 2C6.48 2 2.004 6.477 2.004 12c0 1.767.46 3.427 1.267 4.973l-1.347 4.92 5.033-1.32A9.954 9.954 0 0 0 12.004 22c5.52 0 10-4.477 10-10s-4.48-10-10-10zm5.92 14.24c-.247.693-1.42 1.267-1.953 1.347-.487.073-1.12.133-3.247-.747-2.713-1.127-4.46-3.893-4.593-4.08-.133-.187-1.1-1.467-1.1-2.8 0-1.333.693-1.987.947-2.253.253-.267.56-.333.747-.333.187 0 .373.007.533.013.167.007.393-.067.613.46.227.547.78 1.907.847 2.04.067.133.113.293.02.48-.093.187-.14.307-.28.467-.14.16-.293.36-.42.48-.14.133-.287.28-.127.56.16.28.707 1.167 1.52 1.893.1.087.18.14.28.2.14.093.287.147.387.053.187-.173.78-.907.987-1.22.2-.313.4-.26.673-.16.273.1.173.813 1.48 1.46.127.067.247.1.34.127.28.087.547.04.753-.007.247-.053.867-.547 1.087-1.08.22-.533.22-.987.153-1.08-.067-.093-.247-.147-.533-.287z"/>
            </svg>
        </a>
        
        <!-- Live Support Chat Button (Forest Green #2D5016) -->
        <button onclick="triggerPrimaryLiveChat()" class="chat-channel crm-channel" aria-label="Open Chatbot" title="Open Chatbot">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 9h12v2H6V9zm8 5H6v-2h8v2zm4-6H6V6h12v2z"/>
            </svg>
            <span class="online-pulse-badge"></span>
        </button>
    </div>
</div>

<script>
    // Global variable links
    window.computedWhatsAppLink = "https://api.whatsapp.com/send?phone=639953947379";
    window.computedViberLink = "viber://chat?number=%2B639953947379";

    // Dynamic Header Phone Number Extraction
    (function() {
        function extractPhoneNumber() {
            let extractedNum = "";
            const phoneElement = document.getElementById("phoneNumber");
            if (phoneElement) {
                extractedNum = phoneElement.textContent.trim();
            } else {
                // Fallback to searching the header top layout
                const headerTop = document.querySelector(".header-top");
                if (headerTop) {
                    const match = headerTop.textContent.match(/\+?\d[\d\s-]{9,}/);
                    if (match) {
                        extractedNum = match[0].trim();
                    }
                }
            }

            if (extractedNum) {
                // Remove non-numeric characters except optionally leading '+' (clean up spaces and dashes)
                let cleanDigits = extractedNum.replace(/\D/g, "");
                // Convert leading '09' to standard Philippine country code format '639'
                if (cleanDigits.startsWith("09")) {
                    cleanDigits = "63" + cleanDigits.substring(1);
                }
                
                window.computedWhatsAppLink = "https://api.whatsapp.com/send?phone=" + cleanDigits;
                window.computedViberLink = "viber://chat?number=%2B" + cleanDigits;

                // Update the DOM links with the new computed values
                const waLink = document.getElementById("floating-whatsapp-link");
                if (waLink) {
                    waLink.setAttribute("href", window.computedWhatsAppLink);
                }
                const viberLink = document.getElementById("floating-viber-link");
                if (viberLink) {
                    viberLink.setAttribute("href", window.computedViberLink);
                }
            }
        }

        // Run when DOM is ready
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", extractPhoneNumber);
        } else {
            extractPhoneNumber();
        }
    })();

    // Function to trigger primary live chat panel
    function triggerPrimaryLiveChat() {
        if (typeof toggleChat === 'function') {
            toggleChat();
        } else {
            const oldFab = document.getElementById("chatFab");
            if (oldFab) {
                oldFab.click();
            } else {
                console.warn("CRM / Live Chat toggle function (toggleChat) not found.");
            }
        }
    }

    // Function to handle standalone tracking button action
    function triggerTracking() {
        if (typeof toggleTrackPanel === 'function') {
            toggleTrackPanel();
        } else {
            window.location.href = "track-order.php";
        }
    }
</script>
