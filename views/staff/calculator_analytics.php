<?php
// views/staff/calculator_analytics.php - Calculator Usage & Lead Monitoring Dashboard
error_reporting(0);
ini_set('display_errors', 0);
session_start();

// Redirect if not authorized
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solar Savings Calculator Analytics</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="p-4 md:p-6 text-slate-800">

    <div class="max-w-[1600px] mx-auto space-y-6">

        <!-- Top Section / Header -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Solar Savings Calculator Analytics</h2>
                <p class="text-sm text-slate-500">Monitor interactive simulator metrics and conversion actions</p>
            </div>
            
            <div class="flex items-center gap-3">
                <select id="dateFilter" class="bg-white border border-slate-300 text-slate-700 py-2 px-4 pr-8 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm">
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month" selected>This Month</option>
                </select>
                <button onclick="fetchAnalytics()" class="bg-teal-600 hover:bg-teal-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition text-sm flex items-center gap-2">
                    <i class="fa-solid fa-arrows-rotate"></i>
                    <span>Refresh</span>
                </button>
            </div>
        </div>

        <!-- Stats Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Card 1: Total Uses -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Interactions</span>
                    <h3 id="statTotalUses" class="text-3xl font-bold text-slate-900">--</h3>
                    <span class="text-xs text-emerald-500 font-medium"><i class="fa-solid fa-arrow-trend-up"></i> +12% vs last month</span>
                </div>
                <div class="bg-emerald-50 text-emerald-600 p-4 rounded-2xl">
                    <i class="fa-solid fa-sliders text-2xl"></i>
                </div>
            </div>

            <!-- Card 2: Average Bill -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Avg. Monthly Bill</span>
                    <h3 id="statAvgBill" class="text-3xl font-bold text-slate-900">₱0</h3>
                    <span class="text-xs text-slate-500">Representative user input</span>
                </div>
                <div class="bg-amber-50 text-amber-600 p-4 rounded-2xl">
                    <i class="fa-solid fa-calculator text-2xl"></i>
                </div>
            </div>

            <!-- Card 3: Common System -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Common Size</span>
                    <h3 id="statCommonSize" class="text-3xl font-bold text-slate-900">--</h3>
                    <span class="text-xs text-slate-500">Highest frequency estimate</span>
                </div>
                <div class="bg-sky-50 text-sky-600 p-4 rounded-2xl">
                    <i class="fa-solid fa-bolt text-2xl"></i>
                </div>
            </div>

            <!-- Card 4: Conversion Rate -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Lead Conversion Rate</span>
                    <h3 id="statConversionRate" class="text-3xl font-bold text-slate-900 font-semibold">--</h3>
                    <span class="text-xs text-emerald-500 font-medium"><i class="fa-solid fa-arrow-trend-up"></i> +2.4% conversion velocity</span>
                </div>
                <div class="bg-indigo-50 text-indigo-600 p-4 rounded-2xl">
                    <i class="fa-solid fa-user-check text-2xl"></i>
                </div>
            </div>

        </div>

        <!-- Chart Section -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Usage Frequency over Time</h3>
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Hourly/Daily Trends</span>
            </div>
            <div class="relative h-80">
                <canvas id="usageChart"></canvas>
            </div>
        </div>

        <!-- Activity Log Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            
            <!-- Table Controls -->
            <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <h3 class="text-lg font-bold text-slate-900">Recent Activity Logs</h3>
                
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <!-- Search Box -->
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" id="searchInput" placeholder="Search user, system size..." class="bg-slate-50 border border-slate-200 text-slate-700 pl-10 pr-4 py-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 w-full sm:w-64">
                    </div>

                    <!-- Status Filter -->
                    <select id="statusFilter" class="bg-slate-50 border border-slate-200 text-slate-700 py-2 px-4 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="all">All Actions</option>
                        <option value="converted">Converted Leads</option>
                        <option value="no_action">Calculated Only</option>
                    </select>
                </div>
            </div>

            <!-- Responsive Table Wrapper -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-xs text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                            <th class="py-4 px-6">Timestamp</th>
                            <th class="py-4 px-6">User / Lead Name</th>
                            <th class="py-4 px-6">Contact & Email</th>
                            <th class="py-4 px-6">Bill Inputted</th>
                            <th class="py-4 px-6">Recommended Size</th>
                            <th class="py-4 px-6">Action Taken</th>
                        </tr>
                    </thead>
                    <tbody id="logsTableBody" class="divide-y divide-slate-100 text-sm">
                        <!-- JS populated rows -->
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Interactive Lead Simulator Details -->
        <div class="bg-slate-900 text-slate-100 p-6 rounded-2xl flex flex-col md:flex-row md:items-center md:justify-between gap-6 border border-slate-800">
            <div class="space-y-1">
                <h4 class="font-bold text-base text-teal-400">Interactive Lead Simulation Details</h4>
                <p class="text-xs text-slate-400">This client-side dashboard mimics tracking interactions from the user-facing slider. Drag events are debounced for 2 seconds to avoid excessive network requests.</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="simulateSliderInteraction()" class="bg-slate-800 hover:bg-slate-700 text-teal-400 font-semibold py-2 px-4 rounded-lg text-xs transition border border-teal-500/20">
                    <i class="fa-solid fa-play mr-1"></i> Simulate Slider Input
                </button>
                <button onclick="simulateLeadConversion()" class="bg-teal-500 hover:bg-teal-600 text-slate-950 font-semibold py-2 px-4 rounded-lg text-xs transition">
                    <i class="fa-solid fa-handshake mr-1"></i> Simulate Lead Conversion
                </button>
            </div>
        </div>

    </div>

    <!-- Script details -->
    <script>
        let chartInstance = null;
        let sliderTimeout = null;

        document.addEventListener('DOMContentLoaded', () => {
            fetchAnalytics();
            document.getElementById('dateFilter').addEventListener('change', fetchAnalytics);
            document.getElementById('statusFilter').addEventListener('change', fetchAnalytics);
            document.getElementById('searchInput').addEventListener('input', debounce(fetchAnalytics, 300));
        });

        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        function fetchAnalytics() {
            const dateFilter = document.getElementById('dateFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const searchQuery = document.getElementById('searchInput').value;

            // Notice we pull from absolute/parent paths to point to api/get_calculator_logs.php
            const apiUrl = `../../api/get_calculator_logs.php?date_filter=${dateFilter}&status=${statusFilter}&search=${encodeURIComponent(searchQuery)}`;

            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    updateSummaryMetrics(data.metrics);
                    renderAnalyticsChart(data.chart);
                    renderLogsTable(data.logs);
                })
                .catch(error => {
                    console.error('Error fetching analytics:', error);
                });
        }

        function updateSummaryMetrics(metrics) {
            document.getElementById('statTotalUses').textContent = metrics.total_uses;
            document.getElementById('statAvgBill').textContent = `₱${Number(metrics.avg_bill).toLocaleString()}`;
            document.getElementById('statCommonSize').textContent = metrics.most_common_size;
            document.getElementById('statConversionRate').textContent = `${metrics.conversion_rate}%`;
        }

        function renderAnalyticsChart(chartData) {
            const ctx = document.getElementById('usageChart').getContext('2d');
            
            if (chartInstance) {
                chartInstance.destroy();
            }

            // Create background gradient
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(13, 92, 58, 0.15)');
            gradient.addColorStop(1, 'rgba(13, 92, 58, 0.0)');

            chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Interactions',
                        data: chartData.data,
                        borderColor: '#0d5c3a', // Deep green
                        backgroundColor: gradient,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#f2a900', // Gold/yellow point center
                        pointBorderColor: '#0d5c3a', // Deep green point border
                        pointBorderWidth: 3,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointHoverBackgroundColor: '#f2a900',
                        pointHoverBorderColor: '#0d5c3a',
                        pointHoverBorderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            },
                            ticks: {
                                color: '#64748b',
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: '#f1f5f9'
                            },
                            ticks: {
                                color: '#64748b',
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
        }

        function renderLogsTable(logs) {
            const tbody = document.getElementById('logsTableBody');
            tbody.innerHTML = '';

            if (logs.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400">
                            <i class="fa-regular fa-folder-open text-3xl mb-2 block"></i>
                            No interaction logs found.
                        </td>
                    </tr>
                `;
                return;
            }

            logs.forEach(log => {
                let badgeClass = '';
                if (log.action === 'viber') {
                    badgeClass = 'bg-indigo-100 text-indigo-700 border border-indigo-200';
                } else if (log.action === 'messenger') {
                    badgeClass = 'bg-sky-100 text-sky-700 border border-sky-200';
                } else {
                    badgeClass = 'bg-slate-100 text-slate-600 border border-slate-200';
                }

                // Format date to: June 26, 2026 11:48:34
                let formattedDate = log.timestamp;
                if (log.timestamp) {
                    const dateObj = new Date(log.timestamp.replace(/-/g, '/')); // replace dash for cross-browser support
                    if (!isNaN(dateObj)) {
                        const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                        const month = months[dateObj.getMonth()];
                        const day = dateObj.getDate();
                        const year = dateObj.getFullYear();
                        const time = dateObj.toTimeString().split(' ')[0]; // HH:MM:SS
                        formattedDate = `${month} ${day}, ${year} ${time}`;
                    }
                }

                const row = document.createElement('tr');
                row.className = 'hover:bg-slate-50 transition-colors';
                
                const contactHtml = log.lead_phone || log.lead_email 
                    ? `<div class="font-medium text-slate-800">${log.lead_phone || '—'}</div>
                       <div class="text-xs text-slate-400">${log.lead_email || '—'}</div>`
                    : '<span class="text-slate-400">—</span>';

                row.innerHTML = `
                    <td class="py-4 px-6 text-slate-500 font-medium whitespace-nowrap">${formattedDate}</td>
                    <td class="py-4 px-6 text-slate-900 font-semibold">${log.user_type}</td>
                    <td class="py-4 px-6">${contactHtml}</td>
                    <td class="py-4 px-6 text-slate-900">₱${Number(log.bill).toLocaleString()}</td>
                    <td class="py-4 px-6"><span class="bg-teal-50 text-teal-800 font-medium px-2.5 py-0.5 rounded text-xs border border-teal-100">${log.system_size}</span></td>
                    <td class="py-4 px-6"><span class="px-2.5 py-1 rounded-full text-xs font-semibold ${badgeClass}">${log.action_label}</span></td>
                `;
                tbody.appendChild(row);
            });
        }

        function trackSliderInteraction(billInput, recommendedSize) {
            clearTimeout(sliderTimeout);
            sliderTimeout = setTimeout(() => {
                alert(`Slider Input Tracked (Debounced 2s):\n₱${billInput} (${recommendedSize})`);
                fetchAnalytics();
            }, 2000);
        }

        function simulateSliderInteraction() {
            const sampleBills = [3500, 10000, 20000, 45000];
            const sampleSizes = ["2.0 kWp", "5.0 kWp", "8.0 kWp", "15.0 kWp"];
            const index = Math.floor(Math.random() * sampleBills.length);
            
            alert(`Simulating user drag movement... debouncing for 2 seconds`);
            trackSliderInteraction(sampleBills[index], sampleSizes[index]);
        }

        function simulateLeadConversion() {
            alert("Simulated customer clicking 'Via Messenger'. User converted to Lead!");
            fetchAnalytics();
        }
    </script>
</body>
</html>
