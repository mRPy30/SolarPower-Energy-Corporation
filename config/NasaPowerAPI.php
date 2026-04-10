<?php
/**
 * NASA POWER API Integration — SolarPower Energy Corporation
 * 
 * Fetches monthly solar irradiance data from NASA's free POWER API.
 * Uses database caching (90-day TTL) with hardcoded Philippine fallbacks.
 * 
 * API Docs: https://power.larc.nasa.gov/docs/
 * Data: ALLSKY_SFC_SW_DWN = All Sky Surface Shortwave Downward Irradiance (kWh/m²/day)
 */

require_once __DIR__ . '/db_pdo.php';

class NasaPowerAPI
{
    // ── NASA POWER API endpoint ──
    private const API_URL = 'https://power.larc.nasa.gov/api/temporal/monthly/point';

    // ── Cache duration: 90 days ──
    private const CACHE_TTL_DAYS = 90;

    // ── Philippine annual average irradiance (kWh/m²/day) ──
    private const PH_AVG_IRRADIANCE = 4.85;

    // ── Default Philippine coordinates (Manila) ──
    private const DEFAULT_LAT = 14.5995;
    private const DEFAULT_LON = 120.9842;

    // ── Major Philippine cities for pre-population ──
    public const PH_CITIES = [
        'manila'       => ['lat' => 14.5995, 'lon' => 120.9842, 'name' => 'Manila / NCR'],
        'cebu'         => ['lat' => 10.3157, 'lon' => 123.8854, 'name' => 'Cebu City'],
        'davao'        => ['lat' =>  7.1907, 'lon' => 125.4553, 'name' => 'Davao City'],
        'iloilo'       => ['lat' => 10.7202, 'lon' => 122.5621, 'name' => 'Iloilo City'],
        'baguio'       => ['lat' => 16.4023, 'lon' => 120.5960, 'name' => 'Baguio City'],
        'clark'        => ['lat' => 15.1851, 'lon' => 120.5464, 'name' => 'Clark / Pampanga'],
        'quezon_city'  => ['lat' => 14.6760, 'lon' => 121.0437, 'name' => 'Quezon City'],
        'makati'       => ['lat' => 14.5547, 'lon' => 121.0244, 'name' => 'Makati City'],
        'laguna'       => ['lat' => 14.2691, 'lon' => 121.4113, 'name' => 'Laguna'],
        'cavite'       => ['lat' => 14.2829, 'lon' => 120.8686, 'name' => 'Cavite'],
        'bulacan'      => ['lat' => 14.7942, 'lon' => 120.8800, 'name' => 'Bulacan'],
        'batangas'     => ['lat' => 13.7565, 'lon' => 121.0583, 'name' => 'Batangas'],
        'rizal'        => ['lat' => 14.6042, 'lon' => 121.3035, 'name' => 'Rizal'],
        'cdo'          => ['lat' => 8.4542,  'lon' => 124.6319, 'name' => 'Cagayan de Oro'],
        'zamboanga'    => ['lat' => 6.9214,  'lon' => 122.0790, 'name' => 'Zamboanga City'],
        'bacolod'      => ['lat' => 10.6840, 'lon' => 122.9563, 'name' => 'Bacolod City'],
        'general_santos' => ['lat' => 6.1164, 'lon' => 125.1716, 'name' => 'General Santos'],
        'tacloban'     => ['lat' => 11.2543, 'lon' => 124.9600, 'name' => 'Tacloban City'],
        'naga'         => ['lat' => 13.6218, 'lon' => 123.1948, 'name' => 'Naga City'],
        'palawan'      => ['lat' => 9.7731,  'lon' => 118.7365, 'name' => 'Puerto Princesa'],
    ];

    /**
     * Default monthly production multipliers for Philippines.
     * Based on NASA POWER historical data (2010-2024) averaged across Luzon, Visayas, Mindanao.
     * 
     * Multiplier = month_irradiance / annual_average_irradiance
     * 
     * Seasons:
     *   - DRY (Mar-May):     Peak solar, clear skies. +20-30% above average.
     *   - WET (Jun-Sep):     Southwest monsoon (Habagat), heavy clouds/rain. -8-15% below.
     *   - COOL DRY (Oct-Feb): NE monsoon (Amihan), generally good. +3-15% above.
     * 
     * "Ber months" note: Sept-Dec transition from wet to dry.
     *   September has typhoon season + monsoon tail = still reduced.
     *   October improves but still has typhoon risk.
     *   November-December: significantly better, approaching dry season.
     */
    public const DEFAULT_MONTHLY_MULTIPLIERS = [
        1  => 1.08,   // January   — Cool dry (Amihan), moderate clouds in some areas
        2  => 1.13,   // February  — Dry season ramp-up, longer daylight
        3  => 1.22,   // March     — Hot dry begins, excellent production
        4  => 1.28,   // April     — Peak dry season, highest irradiance
        5  => 1.20,   // May       — Still dry but pre-monsoon buildup begins late May
        6  => 0.98,   // June      — Habagat starts, cloud cover increases significantly
        7  => 0.84,   // July      — Peak monsoon, heavy rains, lowest production
        8  => 0.82,   // August    — Monsoon continues + typhoon season peak
        9  => 0.86,   // September — Typhoon season, still very cloudy/rainy
        10 => 0.93,   // October   — Monsoon weakening, but typhoons still possible
        11 => 1.02,   // November  — Amihan returns, improving conditions
        12 => 1.04,   // December  — Cool dry, good production but shorter days
    ];

    /**
     * Seasonal weather descriptions for UI display.
     */
    public const SEASON_INFO = [
        1  => ['season' => 'Cool Dry (Amihan)',      'emoji' => '🌤️', 'note' => 'Northeast monsoon, mostly clear'],
        2  => ['season' => 'Cool Dry (Amihan)',      'emoji' => '☀️',  'note' => 'Dry season starting, great output'],
        3  => ['season' => 'Hot Dry Season',         'emoji' => '☀️',  'note' => 'Excellent solar conditions'],
        4  => ['season' => 'Peak Dry Season',        'emoji' => '🔥',  'note' => 'Best month for solar production'],
        5  => ['season' => 'Hot Dry (Late)',         'emoji' => '☀️',  'note' => 'Still excellent, pre-monsoon late May'],
        6  => ['season' => 'Wet Season (Habagat)',   'emoji' => '🌧️', 'note' => 'Southwest monsoon begins, more clouds'],
        7  => ['season' => 'Wet Season (Peak)',      'emoji' => '🌧️', 'note' => 'Heavy monsoon rains, reduced output'],
        8  => ['season' => 'Wet Season + Typhoons',  'emoji' => '🌀', 'note' => 'Typhoon season peak, lowest production'],
        9  => ['season' => 'Wet (Ber Month)',        'emoji' => '🌀', 'note' => 'Still rainy, typhoon risk continues'],
        10 => ['season' => 'Transition (Ber Month)', 'emoji' => '⛅',  'note' => 'Monsoon weakening, improving slowly'],
        11 => ['season' => 'Cool Dry (Amihan)',      'emoji' => '🌤️', 'note' => 'Dry season returning, good recovery'],
        12 => ['season' => 'Cool Dry (Amihan)',      'emoji' => '🎄', 'note' => 'Good production, shorter daylight'],
    ];

    /**
     * Average peak sun hours per month for Philippines (Manila baseline).
     * Based on: irradiance × correction factor for panel orientation + temperature derating.
     */
    public const DEFAULT_PEAK_SUN_HOURS = [
        1  => 4.40,
        2  => 4.72,
        3  => 5.20,
        4  => 5.50,
        5  => 5.15,
        6  => 4.10,
        7  => 3.50,
        8  => 3.40,
        9  => 3.60,
        10 => 3.90,
        11 => 4.20,
        12 => 4.30,
    ];

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getPDO();
        $this->ensureCacheTable();
    }

    /**
     * Create cache table if it doesn't exist.
     */
    private function ensureCacheTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `solar_irradiance_cache` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `location_id` VARCHAR(100) NOT NULL,
                `location_name` VARCHAR(255) DEFAULT NULL,
                `latitude` DECIMAL(8,4) NOT NULL,
                `longitude` DECIMAL(8,4) NOT NULL,
                `month` TINYINT NOT NULL,
                `avg_irradiance` DECIMAL(5,2) DEFAULT NULL COMMENT 'kWh/m²/day',
                `peak_sun_hours` DECIMAL(4,2) DEFAULT NULL,
                `production_multiplier` DECIMAL(4,2) DEFAULT NULL,
                `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `source` VARCHAR(50) DEFAULT 'static' COMMENT 'nasa-power or static',
                UNIQUE KEY `loc_month` (`location_id`, `month`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    /**
     * Get monthly solar data for a location. Tries cache first, then NASA API, then fallback.
     * 
     * @param string $cityKey  Key from PH_CITIES array (e.g., 'manila')
     * @return array  Monthly data [1..12] with keys: irradiance, peak_sun_hours, multiplier, season_info
     */
    public function getMonthlyData(string $cityKey = 'manila'): array
    {
        $city = self::PH_CITIES[$cityKey] ?? self::PH_CITIES['manila'];
        $lat = $city['lat'];
        $lon = $city['lon'];
        $locationId = $this->makeLocationId($lat, $lon);

        // 1. Check cache
        $cached = $this->getCachedData($locationId);
        if ($cached !== null) {
            return $this->enrichWithSeasonInfo($cached);
        }

        // 2. Try NASA POWER API
        $apiData = $this->fetchFromNasaPower($lat, $lon);
        if ($apiData !== null) {
            $this->cacheData($locationId, $city['name'], $lat, $lon, $apiData, 'nasa-power');
            return $this->enrichWithSeasonInfo($apiData);
        }

        // 3. Fallback to defaults
        $fallback = $this->getDefaultData();
        $this->cacheData($locationId, $city['name'], $lat, $lon, $fallback, 'static');
        return $this->enrichWithSeasonInfo($fallback);
    }

    /**
     * Get default (Philippine national average) data.
     */
    public function getDefaultData(): array
    {
        $data = [];
        foreach (self::DEFAULT_MONTHLY_MULTIPLIERS as $month => $multiplier) {
            $data[$month] = [
                'irradiance'      => round(self::PH_AVG_IRRADIANCE * $multiplier, 2),
                'peak_sun_hours'  => self::DEFAULT_PEAK_SUN_HOURS[$month],
                'multiplier'      => $multiplier,
            ];
        }
        return $data;
    }

    /**
     * Fetch monthly irradiance from NASA POWER API.
     * Returns null on failure (API down, timeout, etc.)
     */
    private function fetchFromNasaPower(float $lat, float $lon): ?array
    {
        $currentYear = (int) date('Y');
        $startYear = $currentYear - 5;  // Last 5 years for averaging
        $endYear = $currentYear - 1;    // Up to last full year

        $url = self::API_URL . '?' . http_build_query([
            'parameters' => 'ALLSKY_SFC_SW_DWN',
            'community'  => 'RE',
            'longitude'  => round($lon, 4),
            'latitude'   => round($lat, 4),
            'start'      => $startYear,
            'end'        => $endYear,
            'format'     => 'JSON',
        ]);

        // Use cURL for reliability
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'SolarPower-Energy-Corp/1.0',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode !== 200 || !$response) {
            error_log("[NasaPowerAPI] Failed to fetch data for ({$lat}, {$lon}): HTTP {$httpCode}, Error: {$error}");
            return null;
        }

        $json = json_decode($response, true);
        if (!$json || !isset($json['properties']['parameter']['ALLSKY_SFC_SW_DWN'])) {
            error_log("[NasaPowerAPI] Invalid response structure for ({$lat}, {$lon})");
            return null;
        }

        $irradianceData = $json['properties']['parameter']['ALLSKY_SFC_SW_DWN'];

        // Average across years for each month
        $monthlyAvg = [];
        for ($m = 1; $m <= 12; $m++) {
            $values = [];
            for ($y = $startYear; $y <= $endYear; $y++) {
                $key = $y . str_pad($m, 2, '0', STR_PAD_LEFT);
                if (isset($irradianceData[$key]) && $irradianceData[$key] > 0) {
                    $values[] = $irradianceData[$key];
                }
            }
            $monthlyAvg[$m] = count($values) > 0 ? array_sum($values) / count($values) : null;
        }

        // Calculate annual average for multiplier computation
        $validMonths = array_filter($monthlyAvg, fn($v) => $v !== null);
        if (count($validMonths) < 10) {
            error_log("[NasaPowerAPI] Insufficient data for ({$lat}, {$lon}): only " . count($validMonths) . " months");
            return null;
        }

        $annualAvg = array_sum($validMonths) / count($validMonths);

        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $irr = $monthlyAvg[$m] ?? ($annualAvg * self::DEFAULT_MONTHLY_MULTIPLIERS[$m]);
            $multiplier = $annualAvg > 0 ? round($irr / $annualAvg, 2) : self::DEFAULT_MONTHLY_MULTIPLIERS[$m];

            // Convert irradiance to peak sun hours (PSH ≈ GHI × panel_correction_factor)
            // For typical fixed-tilt panels in Philippines: PSH ≈ GHI × 0.85
            $psh = round($irr * 0.85, 2);

            $data[$m] = [
                'irradiance'      => round($irr, 2),
                'peak_sun_hours'  => $psh,
                'multiplier'      => $multiplier,
            ];
        }

        return $data;
    }

    /**
     * Check database cache for location data (within TTL).
     */
    private function getCachedData(string $locationId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT month, avg_irradiance, peak_sun_hours, production_multiplier
            FROM solar_irradiance_cache
            WHERE location_id = ?
              AND last_updated >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ORDER BY month ASC
        ");
        $stmt->execute([$locationId, self::CACHE_TTL_DAYS]);
        $rows = $stmt->fetchAll();

        if (count($rows) < 12) {
            return null;
        }

        $data = [];
        foreach ($rows as $row) {
            $m = (int) $row['month'];
            $data[$m] = [
                'irradiance'      => (float) $row['avg_irradiance'],
                'peak_sun_hours'  => (float) $row['peak_sun_hours'],
                'multiplier'      => (float) $row['production_multiplier'],
            ];
        }

        return $data;
    }

    /**
     * Store monthly data in cache.
     */
    private function cacheData(string $locationId, string $name, float $lat, float $lon, array $data, string $source): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO solar_irradiance_cache 
                (location_id, location_name, latitude, longitude, month, avg_irradiance, peak_sun_hours, production_multiplier, source)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                avg_irradiance = VALUES(avg_irradiance),
                peak_sun_hours = VALUES(peak_sun_hours),
                production_multiplier = VALUES(production_multiplier),
                source = VALUES(source),
                last_updated = CURRENT_TIMESTAMP
        ");

        foreach ($data as $month => $d) {
            $stmt->execute([
                $locationId,
                $name,
                $lat,
                $lon,
                $month,
                $d['irradiance'],
                $d['peak_sun_hours'],
                $d['multiplier'],
                $source,
            ]);
        }
    }

    /**
     * Add season information to data array.
     */
    private function enrichWithSeasonInfo(array $data): array
    {
        foreach ($data as $month => &$d) {
            $d['season_info'] = self::SEASON_INFO[$month] ?? [];
        }
        return $data;
    }

    /**
     * Generate location ID from coordinates.
     */
    private function makeLocationId(float $lat, float $lon): string
    {
        return sprintf('ph_%.2f_%.2f', $lat, $lon);
    }

    /**
     * Get list of available cities for dropdown.
     */
    public static function getCityOptions(): array
    {
        $options = [];
        foreach (self::PH_CITIES as $key => $city) {
            $options[] = [
                'value' => $key,
                'label' => $city['name'],
            ];
        }
        return $options;
    }

    /**
     * Pre-populate cache for all major cities.
     * Run this once via CLI or admin panel.
     */
    public function prepopulateAllCities(): array
    {
        $results = [];
        foreach (self::PH_CITIES as $key => $city) {
            try {
                $data = $this->getMonthlyData($key);
                $results[$key] = ['status' => 'ok', 'source' => $data[1]['season_info'] ? 'cached' : 'fresh'];
            } catch (\Exception $e) {
                $results[$key] = ['status' => 'error', 'message' => $e->getMessage()];
            }
        }
        return $results;
    }
}
