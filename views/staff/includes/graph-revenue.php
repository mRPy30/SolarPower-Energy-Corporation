<?php
// ============================================================
// Real Revenue Data from orders table
// ============================================================
$currentYear = date('Y');

// Get monthly sales for current year (all non-archived, non-cancelled orders)
$monthlySalesQuery = "
    SELECT MONTH(created_at) AS month_num, IFNULL(SUM(total_amount), 0) AS monthly_total
    FROM orders
    WHERE YEAR(created_at) = $currentYear
      AND order_status NOT IN ('archived', 'cancelled')
    GROUP BY MONTH(created_at)
    ORDER BY MONTH(created_at)
";
$monthlySalesResult = mysqli_query($conn, $monthlySalesQuery);

// Initialize 12 months with 0
$monthlySales = array_fill(1, 12, 0);
while ($row = mysqli_fetch_assoc($monthlySalesResult)) {
    $monthlySales[(int)$row['month_num']] = (float)$row['monthly_total'];
}

// Get last year's total for growth rate comparison
$lastYear = $currentYear - 1;
$lastYearQuery = "
    SELECT IFNULL(SUM(total_amount), 0) AS total
    FROM orders
    WHERE YEAR(created_at) = $lastYear
      AND order_status NOT IN ('archived', 'cancelled')
";
$lastYearTotal = (float)mysqli_fetch_assoc(mysqli_query($conn, $lastYearQuery))['total'];

// Calculate stats
$totalSales = array_sum($monthlySales);
$monthsWithData = count(array_filter($monthlySales, function($v) { return $v > 0; }));
$avgPerMonth = $monthsWithData > 0 ? $totalSales / $monthsWithData : 0;

// Growth rate vs last year
if ($lastYearTotal > 0) {
    $growthRate = round((($totalSales - $lastYearTotal) / $lastYearTotal) * 100);
} else {
    $growthRate = $totalSales > 0 ? 100 : 0;
}

// Best quarter
$quarters = [
    $monthlySales[1] + $monthlySales[2] + $monthlySales[3],
    $monthlySales[4] + $monthlySales[5] + $monthlySales[6],
    $monthlySales[7] + $monthlySales[8] + $monthlySales[9],
    $monthlySales[10] + $monthlySales[11] + $monthlySales[12],
];
$bestQuarter = max($quarters);
$bestQuarterIndex = array_search($bestQuarter, $quarters) + 1;

// Find max value for Y-axis scaling
$maxVal = max($monthlySales);
$maxVal = $maxVal > 0 ? $maxVal : 100000; // fallback if no data
// Round up to nice ceiling
$yMax = ceil($maxVal / 50000) * 50000;
if ($yMax < 50000) $yMax = 50000;
$ySteps = 10;
$yStepVal = $yMax / $ySteps;

// Format helper
function formatCurrency($val) {
    if ($val >= 1000000) return round($val / 1000000, 1) . 'M';
    if ($val >= 1000) return round($val / 1000) . 'K';
    return number_format($val, 0);
}

// Calculate SVG points (viewBox 1000x340)
$svgWidth = 1000;
$svgHeight = 340;
$monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

$points = [];
for ($i = 1; $i <= 12; $i++) {
    $x = round(($i - 1) * ($svgWidth / 11));
    $y = $yMax > 0 ? round($svgHeight - ($monthlySales[$i] / $yMax) * $svgHeight) : $svgHeight;
    $points[] = ['x' => $x, 'y' => $y, 'val' => $monthlySales[$i], 'month' => $monthNames[$i - 1]];
}

// Build SVG path strings
$linePath = '';
$areaPath = '';
foreach ($points as $idx => $pt) {
    $prefix = $idx === 0 ? 'M' : 'L';
    $linePath .= " $prefix {$pt['x']},{$pt['y']}";
    $areaPath .= " $prefix {$pt['x']},{$pt['y']}";
}
$areaPath .= " L $svgWidth,$svgHeight L 0,$svgHeight Z";

// Dot colors based on quarter
$dotColors = [];
for ($i = 0; $i < 12; $i++) {
    $q = intdiv($i, 3);
    $colors = ['#667eea', '#48bb78', '#f59e0b', '#e74c3c'];
    $dotColors[] = $colors[$q];
}
?>

<!-- Revenue Graph Section (Themed) -->
<div class="card shadow-sm mt-4" style="border: none; border-radius: 12px; background: #fff;">
    <div class="card-body" style="padding: 32px 32px 24px 32px;">
        <!-- Chart Title -->
        <h2 style="text-align: center; color: #222; font-weight: 700; margin-bottom: 32px; font-size: 2rem; letter-spacing: 0.5px;">Monthly Revenue <?php echo $currentYear; ?></h2>

        <!-- Chart Container -->
        <div style="position: relative; height: 340px; border-left: 2px solid #e5e7eb; border-bottom: 2px solid #e5e7eb; padding: 16px 16px 32px 56px; background: #fff; border-radius: 12px;">
            <!-- Y-axis labels -->
            <div style="position: absolute; left: 0; top: 16px; bottom: 32px; width: 56px; display: flex; flex-direction: column; justify-content: space-between; align-items: flex-end; padding-right: 8px;">
                <?php for ($i = $ySteps; $i >= 0; $i--): ?>
                    <span style="color: #bdbdbd; font-size: 13px; font-weight: 600; font-family: 'Segoe UI', Arial, sans-serif; letter-spacing: 0.2px;">
                        <?php echo formatCurrency($i * $yStepVal); ?>
                    </span>
                <?php endfor; ?>
            </div>
            <!-- Grid lines -->
            <div style="position: absolute; left: 56px; right: 16px; top: 16px; bottom: 32px;">
                <?php for ($i = 0; $i <= $ySteps; $i++): ?>
                    <div style="position: absolute; width: 100%; height: 1px; background-color: #f3f4f6; top: <?php echo ($i / $ySteps) * 100; ?>%;"></div>
                <?php endfor; ?>
            </div>
            <!-- SVG Line Graph -->
            <svg style="position: absolute; left: 56px; right: 16px; top: 16px; bottom: 32px; width: calc(100% - 72px); height: calc(100% - 48px);" viewBox="0 0 <?php echo $svgWidth; ?> <?php echo $svgHeight; ?>">
                <defs>
                    <!-- Blue area fill -->
                    <linearGradient id="areaFill" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:#e3f0ff;stop-opacity:0.7" />
                        <stop offset="100%" style="stop-color:#fffde7;stop-opacity:0.2" />
                    </linearGradient>
                    <!-- Soft shadow for line -->
                    <filter id="shadow">
                        <feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="#f9d923" flood-opacity="0.18"/>
                    </filter>
                </defs>
                <!-- Area under the line -->
                <path d="<?php echo $areaPath; ?>" fill="url(#areaFill)" />
                <!-- Main line: bold yellow -->
                <path d="<?php echo $linePath; ?>"
                      stroke="#ffe600" stroke-width="4" fill="none" filter="url(#shadow)" style="transition: all 0.3s;" />
                <!-- Data points: yellow with blue border -->
                <?php foreach ($points as $idx => $pt): ?>
                <g class="data-point-group" style="cursor: pointer;">
                    <circle cx="<?php echo $pt['x']; ?>" cy="<?php echo $pt['y']; ?>" r="7" fill="#ffe600" stroke="#1976d2" stroke-width="2.5"
                        style="transition: all 0.3s;" 
                        onmouseover="this.setAttribute('r', '11'); this.nextElementSibling.style.display='block';" 
                        onmouseout="this.setAttribute('r', '7'); this.nextElementSibling.style.display='none';">
                        <animate attributeName="opacity" from="0" to="1" dur="0.5s" begin="<?php echo 0.2 + ($idx * 0.2); ?>s" fill="freeze" />
                    </circle>
                    <!-- Tooltip -->
                    <g style="display:none;">
                        <rect x="<?php echo $pt['x'] - 48; ?>" y="<?php echo $pt['y'] - 38; ?>" width="96" height="28" rx="6" fill="#222" opacity="0.95"/>
                        <text x="<?php echo $pt['x']; ?>" y="<?php echo $pt['y'] - 20; ?>" text-anchor="middle" fill="#ffe600" font-size="13" font-weight="700" font-family="'Segoe UI', Arial, sans-serif">
                            <?php echo $pt['month']; ?>: ₱<?php echo formatCurrency($pt['val']); ?>
                        </text>
                    </g>
                </g>
                <?php endforeach; ?>
            </svg>
            <!-- X-axis labels -->
            <div style="position: absolute; left: 56px; right: 16px; bottom: 0; height: 36px; display: flex; justify-content: space-between; gap: 8px;">
                <?php foreach ($monthNames as $mn): ?>
                <div style="flex: 1; text-align: center; color: #1976d2; font-size: 14px; font-weight: 600; font-family: 'Segoe UI', Arial, sans-serif; padding-top: 8px; letter-spacing: 0.2px;">
                    <?php echo $mn; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- Enhanced Stats Cards (Themed) -->
        <div class="dashboard-stats-flex" style="display: flex; flex-wrap: wrap; gap: 2rem; justify-content: center; align-items: stretch; margin-top: 2.5rem;">
            <div class="dashboard-stat-card" style="flex: 1 1 220px; min-width: 220px; max-width: 320px; background: #ffe600; border-radius: 14px; box-shadow: 0 4px 18px #ffe60033; display: flex; flex-direction: column; align-items: center; padding: 32px 18px 24px 18px; margin-bottom: 0.5rem; transition: box-shadow 0.2s;">
                <div style="font-size: 2.2rem; font-weight: 800; color: #222; display: flex; align-items: center; gap: 0.5rem;"><span style="font-size:1.5rem;"></span>₱<?php echo formatCurrency($totalSales); ?></div>
                <div style="font-size: 1rem; font-weight: 700; color: #222; margin-top: 10px; letter-spacing: 0.2px;">Total Sales</div>
            </div>
            <div class="dashboard-stat-card" style="flex: 1 1 220px; min-width: 220px; max-width: 320px; background: #1976d2; border-radius: 14px; box-shadow: 0 4px 18px #1976d233; display: flex; flex-direction: column; align-items: center; padding: 32px 18px 24px 18px; margin-bottom: 0.5rem; transition: box-shadow 0.2s;">
                <div style="font-size: 2.2rem; font-weight: 800; color: #fff; display: flex; align-items: center; gap: 0.5rem;"><span style="font-size:1.5rem;">📈</span><?php echo ($growthRate >= 0 ? '+' : '') . $growthRate; ?>%</div>
                <div style="font-size: 1rem; font-weight: 700; color: #fff; margin-top: 10px; letter-spacing: 0.2px;">Growth Rate vs <?php echo $lastYear; ?></div>
            </div>
            <div class="dashboard-stat-card" style="flex: 1 1 220px; min-width: 220px; max-width: 320px; background: #fffde7; border-radius: 14px; box-shadow: 0 4px 18px #ffe60033; display: flex; flex-direction: column; align-items: center; padding: 32px 18px 24px 18px; margin-bottom: 0.5rem; transition: box-shadow 0.2s;">
                <div style="font-size: 2.2rem; font-weight: 800; color: #222; display: flex; align-items: center; gap: 0.5rem;"><span style="font-size:1.5rem;">🏆</span>₱<?php echo formatCurrency($bestQuarter); ?></div>
                <div style="font-size: 1rem; font-weight: 700; color: #222; margin-top: 10px; letter-spacing: 0.2px;">Best Quarter (Q<?php echo $bestQuarterIndex; ?>)</div>
            </div>
            <div class="dashboard-stat-card" style="flex: 1 1 220px; min-width: 220px; max-width: 320px; background: #f3f4f6; border-radius: 14px; box-shadow: 0 4px 18px #1976d233; display: flex; flex-direction: column; align-items: center; padding: 32px 18px 24px 18px; margin-bottom: 0.5rem; transition: box-shadow 0.2s;">
                <div style="font-size: 2.2rem; font-weight: 800; color: #1976d2; display: flex; align-items: center; gap: 0.5rem;"><span style="font-size:1.5rem;">📅</span>₱<?php echo formatCurrency($avgPerMonth); ?></div>
                <div style="font-size: 1rem; font-weight: 700; color: #1976d2; margin-top: 10px; letter-spacing: 0.2px;">Avg/Month</div>
            </div>
        </div>
    </div>
</div>