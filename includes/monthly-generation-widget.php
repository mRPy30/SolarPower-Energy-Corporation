<!-- Monthly Generation Widget -->
<div class="details-card monthly-gen-card" style="flex: 1.5; min-width: 320px; background: white; padding: 22px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid #f1f5f9; display: flex; flex-direction: column; justify-content: space-between; font-family: 'DM Sans', sans-serif; transition: all 0.3s ease;">
    
    <!-- Widget Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 16px; border-bottom: 1px solid #f8fafc;">
        <div>
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b; letter-spacing: -0.3px;">
                Monthly Generation
            </h3>
            <!-- Dynamic Live Date & Weather -->
            <div style="display: flex; align-items: center; gap: 8px; margin-top: 5px;">
                <span id="mgen-live-date" style="font-size: 11px; color: #64748b; font-weight: 500;">Loading date...</span>
                <span style="color: #cbd5e1; font-size: 10px;">•</span>
                <span id="mgen-live-weather" style="font-size: 11px; color: #3b82f6; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                    Loading weather...
                </span>
            </div>
        </div>
        <!-- Top Right Live Metric -->
        <div id="mgen-current-badge" class="mgen-pulse-anim" style="display: flex; align-items: center; gap: 6px; background: #fffbeb; border: 1px solid #fef3c7; padding: 4px 12px; border-radius: 9999px; color: #d97706; font-size: 13px; font-weight: 700; opacity: 0; transition: opacity 0.4s ease;">
            <span style="font-size: 14px;">⚡</span> <span id="mgen-current-value">0kWh</span>
        </div>
    </div>

    <!-- Content Area (Cards Grid / Skeletons) -->
    <div id="mgen-grid-container" style="padding: 20px 0; min-height: 150px; display: flex; align-items: center; justify-content: center;">
        <!-- Loading Skeletons -->
        <div id="mgen-skeletons" style="width: 100%; display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
            <?php for ($i = 0; $i < 4; $i++): ?>
                <div style="background: #f8fafc; border: 1px dashed #e2e8f0; border-radius: 14px; padding: 16px 12px; display: flex; flex-direction: column; align-items: center; animation: mgenPulse 1.5s infinite ease-in-out;">
                    <div style="height: 14px; width: 32px; background: #e2e8f0; border-radius: 4px; margin-bottom: 15px;"></div>
                    <div style="height: 36px; width: 36px; background: #e2e8f0; border-radius: 50%; margin-bottom: 15px;"></div>
                    <div style="height: 12px; width: 44px; background: #e2e8f0; border-radius: 4px;"></div>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Dynamic Content Grid (Initially Hidden) -->
        <div id="mgen-grid" style="display: none; width: 100%; grid-template-columns: repeat(4, 1fr); gap: 12px;">
            <!-- Rendered by JS -->
        </div>

        <!-- Error State (Initially Hidden) -->
        <div id="mgen-error" style="display: none; flex-direction: column; align-items: center; text-align: center; gap: 8px;">
            <p style="margin: 0; font-size: 13px; color: #64748b;">Unable to fetch real-time solar inverter metrics.</p>
            <button onclick="mgenModule.fetchData()" style="background: #f1f5f9; border: none; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; color: #475569; cursor: pointer; display: flex; align-items: center; gap: 5px; transition: background 0.2s;">
                <i class="fas fa-redo" style="font-size: 10px;"></i> Retry Connection
            </button>
        </div>
    </div>

    <!-- Widget Footer -->
    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 14px; border-top: 1px solid #f8fafc; min-height: 24px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <!-- Vertical Blue Pill Accent -->
            <div style="width: 4.5px; height: 18px; background: #2563eb; border-radius: 99px; box-shadow: 0 2px 8px rgba(37,99,235,0.25);"></div>
            <span style="font-size: 13px; font-weight: 700; color: #1e293b; letter-spacing: -0.2px;">Maximal Used</span>
        </div>
        <div id="mgen-footer-value" style="font-size: 13px; font-weight: 700; color: #1e293b; opacity: 0; transition: opacity 0.4s ease;">
            <!-- Rendered by JS -->
        </div>
    </div>

</div>

<!-- Styles Specific to the Widget -->
<style>
@keyframes mgenPulse {
    0%, 100% { opacity: 0.6; }
    50% { opacity: 1; }
}
@keyframes mgenSpin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.mgen-spin-slow {
    animation: mgenSpin 20s linear infinite;
    display: inline-block;
}
.mgen-monthly-card {
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 14px;
    padding: 16px 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}
.mgen-monthly-card:hover {
    background: #ffffff;
    border-color: #e2e8f0;
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(148, 163, 184, 0.08);
}
.mgen-monthly-card:hover .mgen-weather-svg {
    transform: scale(1.08);
    color: #3b82f6;
}
.mgen-weather-svg {
    transition: all 0.3s ease;
}
</style>

<!-- Widget Logic -->
<script>
const mgenModule = {
    // ── Weather to SVG Outline Icons Mapping ──
    icons: {
        sunny: `<svg class="mgen-weather-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="width: 38px; height: 38px; color: #64748b;"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>`,
        cloudy: `<svg class="mgen-weather-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="width: 38px; height: 38px; color: #64748b;"><path d="M12 2v2M4.93 4.93l1.41 1.41M20 12h2M19.07 4.93l-1.41 1.41"/><path d="M15.22 22a4.5 4.5 0 0 0 .78-8.93 5 5 0 0 0-9.62 1.57A4.5 4.5 0 0 0 7.5 22z"/></svg>`,
        windy: `<svg class="mgen-weather-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="width: 38px; height: 38px; color: #64748b;"><path d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2M15 2a5 5 0 0 0-5 5h1M18 12a3 3 0 0 0-3 3h1"/></svg>`,
        rainy: `<svg class="mgen-weather-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="width: 38px; height: 38px; color: #64748b;"><path d="M12 2v2M4.93 4.93l1.41 1.41M19.07 4.93l-1.41 1.41"/><path d="M16 14a5 5 0 0 0-9.62 1.57A4.5 4.5 0 0 0 7.5 22h8.5a4.5 4.5 0 0 0 0-9z"/><path d="M10 20v2M14 20v2"/></svg>`
    },

    // ── Mock Solar Inverter API call ──
    async mockApiCall() {
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                // Highly realistic solar telemetry response
                resolve({
                    success: true,
                    current_month_total: 300,
                    data: [
                        { month: 'Sept', generation_kwh: 280, weather_condition: 'sunny' },
                        { month: 'Oct', generation_kwh: 352, weather_condition: 'cloudy' },
                        { month: 'Nov', generation_kwh: 158, weather_condition: 'windy' },
                        { month: 'Dec', generation_kwh: 215, weather_condition: 'rainy' }
                    ]
                });
            }, 1000);
        });
    },

    async fetchData() {
        const skeletons = document.getElementById('mgen-skeletons');
        const grid = document.getElementById('mgen-grid');
        const errorDiv = document.getElementById('mgen-error');
        const badge = document.getElementById('mgen-current-badge');
        const footerVal = document.getElementById('mgen-footer-value');

        // Reset visibility to loading state
        skeletons.style.display = 'grid';
        grid.style.display = 'none';
        errorDiv.style.display = 'none';
        badge.style.opacity = '0';
        footerVal.style.opacity = '0';

        try {
            const res = await this.mockApiCall();
            if (res.success && res.data) {
                // Populate current month generation value
                document.getElementById('mgen-current-value').innerText = `${res.current_month_total}kWh`;
                
                // Calculate dynamic "Maximal Used" (highest production month)
                let maxMonth = 'N/A';
                let maxValue = -Infinity;
                
                res.data.forEach(item => {
                    if (item.generation_kwh > maxValue) {
                        maxValue = item.generation_kwh;
                        maxMonth = item.month;
                    }
                });

                // Populate footer with blue highlight for max month
                footerVal.innerHTML = `${maxMonth} <span style="color: #2563eb; font-weight: 800;">${maxValue} kWh</span>`;

                // Build monthly cards
                grid.innerHTML = res.data.map(item => {
                    const iconSvg = this.icons[item.weather_condition] || this.icons.sunny;
                    return `
                        <div class="mgen-monthly-card">
                            <span style="font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 12px;">${item.month}</span>
                            <div style="margin-bottom: 12px; display: flex; align-items: center; justify-content: center;">
                                ${iconSvg}
                            </div>
                            <span style="font-size: 13px; font-weight: 700; color: #334155;">
                                ${item.generation_kwh} <span style="font-size: 9px; font-weight: 500; color: #94a3b8; margin-left: 1px;">kWh</span>
                            </span>
                        </div>
                    `;
                }).join('');

                // Display loaded state with animations
                skeletons.style.display = 'none';
                grid.style.display = 'grid';
                badge.style.opacity = '1';
                footerVal.style.opacity = '1';
            } else {
                throw new Error();
            }
        } catch (err) {
            skeletons.style.display = 'none';
            errorDiv.style.display = 'flex';
        }
    }
};

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    // Populate dynamic local date in Philippines format
    const today = new Date();
    const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateStr = today.toLocaleDateString('en-US', dateOptions);
    document.getElementById('mgen-live-date').innerText = dateStr;

    // Populate dynamic local weather based on time of day
    const hour = today.getHours();
    let weatherHtml = '';
    if (hour >= 6 && hour < 18) {
        weatherHtml = `<i class="fas fa-sun mgen-spin-slow" style="color: #eab308; margin-right: 2px;"></i> Sunny, 32°C`;
    } else {
        weatherHtml = `<i class="fas fa-moon" style="color: #94a3b8; margin-right: 2px; text-shadow: 0 0 8px rgba(148,163,184,0.4);"></i> Clear Night, 26°C`;
    }
    document.getElementById('mgen-live-weather').innerHTML = weatherHtml;

    mgenModule.fetchData();
});
</script>
