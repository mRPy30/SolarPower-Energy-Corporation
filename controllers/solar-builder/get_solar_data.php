<?php
/**
 * Solar Irradiance Data API — SolarPower Energy Corporation
 * 
 * Returns monthly solar production data for a given Philippine city.
 * Used by the solar builder's bar chart and savings calculator.
 * 
 * GET /controllers/solar-builder/get_solar_data.php?city=manila
 * 
 * Response: {
 *   success: true,
 *   city: { key, name, lat, lon },
 *   monthly: { 1: { irradiance, peak_sun_hours, multiplier, season, emoji, note }, ... },
 *   cities: [ { value, label }, ... ]
 * }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config/NasaPowerAPI.php';

try {
    $cityKey = isset($_GET['city']) ? trim(strtolower($_GET['city'])) : 'manila';

    $api = new NasaPowerAPI();
    $monthlyData = $api->getMonthlyData($cityKey);
    $cities = NasaPowerAPI::getCityOptions();

    // Get city info
    $cityInfo = NasaPowerAPI::PH_CITIES[$cityKey] ?? NasaPowerAPI::PH_CITIES['manila'];
    
    // Format response
    $monthly = [];
    foreach ($monthlyData as $month => $d) {
        $monthly[$month] = [
            'irradiance'      => $d['irradiance'],
            'peak_sun_hours'  => $d['peak_sun_hours'],
            'multiplier'      => $d['multiplier'],
            'season'          => $d['season_info']['season'] ?? '',
            'emoji'           => $d['season_info']['emoji'] ?? '',
            'note'            => $d['season_info']['note'] ?? '',
        ];
    }

    echo json_encode([
        'success' => true,
        'city'    => [
            'key'  => $cityKey,
            'name' => $cityInfo['name'],
            'lat'  => $cityInfo['lat'],
            'lon'  => $cityInfo['lon'],
        ],
        'monthly' => $monthly,
        'cities'  => $cities,
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Failed to load solar data.',
        'detail'  => $e->getMessage(),
    ]);
}
