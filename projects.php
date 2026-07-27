<?php
require_once 'config/dbconn.php';

function project_escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function project_slug($value): string
{
    $slug = strtolower(trim((string) $value));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim((string) $slug, '-');
    return $slug !== '' ? $slug : 'project';
}

function project_images($rawImages): array
{
    $fallback = 'assets/img/product-placeholder.png';
    $decoded = json_decode((string) $rawImages, true);

    if (is_array($decoded) && !empty($decoded)) {
        $images = $decoded;
    } elseif (!empty($rawImages)) {
        $images = [(string) $rawImages];
    } else {
        $images = [$fallback];
    }

    $images = array_values(array_filter(array_map('trim', $images)));
    return !empty($images) ? $images : [$fallback];
}

function project_format_metric(float $value, string $unit = ''): string
{
    if ($value >= 1000000) {
        return rtrim(rtrim(number_format($value / 1000000, 2), '0'), '.') . 'M' . $unit;
    }

    if ($value >= 1000) {
        return rtrim(rtrim(number_format($value / 1000, 1), '0'), '.') . 'K' . $unit;
    }

    return rtrim(rtrim(number_format($value, 1), '0'), '.') . $unit;
}

function project_category(array $project): array
{
    $source = strtolower(implode(' ', [
        $project['project_name'] ?? '',
        $project['subtitle'] ?? '',
        $project['service_type'] ?? '',
        $project['system_type'] ?? '',
    ]));

    if (strpos($source, 'preventive') !== false || strpos($source, 'maintenance') !== false || strpos($source, 'repair') !== false) {
        return ['slug' => 'preventive-maintenance', 'label' => 'Preventive Maintenance'];
    }

    if (strpos($source, 'commercial') !== false || strpos($source, 'industrial') !== false || strpos($source, 'business') !== false || strpos($source, 'warehouse') !== false) {
        return ['slug' => 'commercial', 'label' => 'Commercial'];
    }

    return ['slug' => 'residential', 'label' => 'Residential'];
}

function project_capacity_kw($systemType): float
{
    if (preg_match('/\d+(?:\.\d+)?/', (string) $systemType, $match)) {
        return (float) $match[0];
    }

    return 0.0;
}

function project_specs(array $project): array
{
    $systemType = trim((string) ($project['system_type'] ?? ''));
    $serviceType = trim((string) ($project['service_type'] ?? ''));
    $capacity = project_capacity_kw($systemType);
    $systemLower = strtolower($systemType . ' ' . $serviceType);

    $inverter = $capacity > 0 ? project_format_metric($capacity, 'kW') . ' inverter class' : 'Project-specific configuration';
    $panelRating = $capacity > 0 ? 'Tier-1 PV modules sized for ' . project_format_metric($capacity, 'kW') : 'Inspection and maintenance scope';
    $battery = strpos($systemLower, 'hybrid') !== false || strpos($systemLower, 'battery') !== false
        ? 'Hybrid-ready battery storage'
        : 'No dedicated battery storage listed';

    if (strpos($systemLower, 'maintenance') !== false || strpos($systemLower, 'repair') !== false) {
        $inverter = 'Preventive inspection and diagnostics';
        $panelRating = 'Panel cleaning and performance checks';
        $battery = 'Battery check as applicable';
    }

    return [
        'system' => $systemType !== '' ? $systemType : ($serviceType !== '' ? $serviceType : 'Solar project'),
        'inverter' => $inverter,
        'panel' => $panelRating,
        'battery' => $battery,
    ];
}

function project_ensure_video_table(mysqli $conn): void
{
    $sql = "CREATE TABLE IF NOT EXISTS portfolio_videos (
        id INT(11) NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        project_id INT(11) DEFAULT NULL,
        video_format ENUM('landscape','vertical') NOT NULL DEFAULT 'landscape',
        media_type ENUM('file','url') NOT NULL DEFAULT 'url',
        media_url TEXT NOT NULL,
        thumbnail_url TEXT DEFAULT NULL,
        category_tag ENUM('Residential','Commercial','Maintenance','Showcase') NOT NULL DEFAULT 'Showcase',
        status ENUM('Published','Draft') NOT NULL DEFAULT 'Draft',
        view_count INT UNSIGNED NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_project_id (project_id),
        KEY idx_status_format (status, video_format),
        KEY idx_category_tag (category_tag)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    mysqli_query($conn, $sql);
}

function project_video_asset($path, string $fallback = 'assets/img/product-placeholder.png'): string
{
    $path = trim((string) $path);
    return $path !== '' ? $path : $fallback;
}

function project_video_payload(array $video): array
{
    return [
        'id' => (int) ($video['id'] ?? 0),
        'title' => trim((string) ($video['title'] ?? 'Solar Video')),
        'projectTitle' => trim((string) ($video['project_name'] ?? '')),
        'format' => trim((string) ($video['video_format'] ?? 'landscape')),
        'mediaType' => trim((string) ($video['media_type'] ?? 'url')),
        'mediaUrl' => project_video_asset($video['media_url'] ?? ''),
        'thumbnail' => project_video_asset($video['thumbnail_url'] ?? ''),
        'category' => trim((string) ($video['category_tag'] ?? 'Showcase')),
        'views' => (int) ($video['view_count'] ?? 0),
    ];
}

project_ensure_video_table($conn);

$portfolio_result = mysqli_query($conn, "SELECT * FROM portfolio_projects ORDER BY created_at DESC");
$portfolio_projects = [];

if ($portfolio_result) {
    while ($row = mysqli_fetch_assoc($portfolio_result)) {
        $portfolio_projects[] = $row;
    }
}

$portfolio_videos = [];
$video_result = mysqli_query($conn, "SELECT v.*, p.project_name
    FROM portfolio_videos v
    LEFT JOIN portfolio_projects p ON p.id = v.project_id
    WHERE v.status = 'Published'
    ORDER BY v.created_at DESC, v.id DESC");

if ($video_result) {
    while ($row = mysqli_fetch_assoc($video_result)) {
        $portfolio_videos[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    <title>Our Projects | SolarPower Energy Corporation</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">

    <style>
        :root {
            --project-green: #0a5c3d;
            --project-green-700: #083f2b;
            --project-green-soft: #e9f6ef;
            --project-gold: #f2a900;
            --project-gold-soft: #fff6d7;
            --project-ink: #17231d;
            --project-muted: #66746d;
            --project-line: rgba(10, 92, 61, 0.14);
            --project-page: #F8FAFC;
            --project-card: rgba(255, 255, 255, 0.92);
            --project-shadow: 0 18px 45px rgba(16, 35, 27, 0.10);
        }

        body {
            background: #F8FAFC;
        }

        .hero-projects {
            position: relative;
            min-height: 450px;
            display: flex;
            align-items: center;
            padding: 60px 0;
            overflow: hidden;
            color: #fff;
            background:
                linear-gradient(135deg, rgba(15, 23, 42, 0.92) 0%, rgba(8, 63, 43, 0.78) 100%),
                url('assets/img/projects.png') no-repeat center center/cover;
            z-index: 1;
        }

        .hero-projects::after {
            content: none;
        }

        .hero-projects .container {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .hero-eyebrow {
            display: inline-block;
            margin-bottom: 10px;
            color: #ffe08a;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .hero-eyebrow::before {
            content: none;
        }

        .hero-projects h1 {
            max-width: 820px;
            margin: 0 auto;
            font-size: clamp(2.2rem, 4vw, 3.6rem);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: 0;
        }

        .hero-projects p.hero-copy {
            max-width: 760px;
            margin: 20px auto 0;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 14px;
            margin-top: 28px;
        }

        .hero-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 50px;
            padding: 0 28px;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, color 0.2s ease;
        }

        .hero-btn:hover {
            transform: translateY(-2px);
            text-decoration: none;
        }

        .hero-btn-primary {
            background: var(--project-gold);
            border: 1px solid var(--project-gold);
            color: #000;
            box-shadow: 0 12px 28px rgba(242, 169, 0, 0.26);
        }

        .hero-btn-primary:hover {
            color: #000;
            background: #ffbc1c;
            border-color: #ffbc1c;
        }

        .hero-btn-outline {
            border: 1px solid rgba(255, 255, 255, 0.72);
            color: #fff;
        }

        .hero-btn-outline:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
        }

        .project-showcase {
            position: relative;
            z-index: 2;
            margin-top: 0;
            background: #F8FAFC;
            padding: 76px 0 92px;
        }

        .portfolio-heading {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 18px;
            margin-bottom: 28px;
            padding-top: 0;
        }

        .portfolio-kicker {
            color: var(--project-green);
            font-size: 0.78rem;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: 0.13em;
            margin-bottom: 8px;
        }

        .portfolio-title {
            color: var(--project-ink);
            font-size: clamp(1.85rem, 4vw, 3.2rem);
            font-weight: 900;
            line-height: 1;
            margin: 0;
            letter-spacing: 0;
        }

        .portfolio-subtitle {
            max-width: 680px;
            color: var(--project-muted);
            line-height: 1.75;
            margin: 12px 0 0;
        }

        .project-filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 0 0 28px;
        }

        .project-filter {
            position: relative;
            border: 1px solid rgba(10, 92, 61, 0.18);
            border-radius: 999px;
            background: #fff;
            color: var(--project-green-700);
            padding: 10px 17px;
            font-size: 0.87rem;
            font-weight: 800;
            cursor: pointer;
            transition: color 0.25s ease, border-color 0.25s ease, transform 0.25s ease, box-shadow 0.25s ease;
            overflow: hidden;
        }

        .project-filter::before {
            content: "";
            position: absolute;
            inset: 3px;
            border-radius: inherit;
            background: linear-gradient(135deg, var(--project-green), var(--project-gold));
            opacity: 0;
            transform: scale(0.9);
            transition: opacity 0.25s ease, transform 0.25s ease;
        }

        .project-filter span {
            position: relative;
            z-index: 1;
        }

        .project-filter:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 22px rgba(10, 92, 61, 0.10);
        }

        .project-filter.is-active {
            color: #fff;
            border-color: transparent;
        }

        .project-filter.is-active::before {
            opacity: 1;
            transform: scale(1);
        }

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
            align-items: stretch;
        }

        .project-card-shell {
            transition: opacity 0.24s ease, transform 0.24s ease;
        }

        .project-card-shell.is-filtered-out,
        .project-card-shell.is-collapsed {
            display: none;
        }

        .portfolio-card {
            height: 100%;
            position: relative;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(10, 92, 61, 0.12);
            border-radius: 16px;
            overflow: hidden;
            background: var(--project-card);
            box-shadow: 0 12px 34px rgba(16, 35, 27, 0.08);
            cursor: pointer;
            transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
        }

        .portfolio-card:hover,
        .portfolio-card:focus-within {
            transform: translateY(-6px);
            border-color: rgba(242, 169, 0, 0.45);
            box-shadow: 0 24px 56px rgba(16, 35, 27, 0.15);
        }

        .project-image-wrap {
            position: relative;
            aspect-ratio: 16 / 9;
            overflow: hidden;
            background: #dfe9e3;
        }

        .project-image-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.55s ease;
        }

        .portfolio-card:hover .project-image-wrap img {
            transform: scale(1.05);
        }

        .system-badge {
            position: absolute;
            top: 14px;
            right: 14px;
            max-width: calc(100% - 28px);
            border: 1px solid rgba(255, 255, 255, 0.45);
            border-radius: 999px;
            background: rgba(9, 34, 23, 0.68);
            color: #fff;
            padding: 7px 11px;
            font-size: 0.72rem;
            font-weight: 850;
            line-height: 1.2;
            backdrop-filter: blur(12px);
        }

        .project-card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 22px;
        }

        .project-title {
            color: var(--project-ink);
            font-size: 1.15rem;
            font-weight: 900;
            line-height: 1.2;
            margin: 0;
            letter-spacing: 0;
        }

        .project-location {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 11px;
            color: var(--project-muted);
            font-size: 0.9rem;
        }

        .project-location i {
            color: var(--project-gold);
        }

        .project-spec-line {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 18px 0 0;
            color: var(--project-green-700);
            font-weight: 850;
            font-size: 0.92rem;
        }

        .impact-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 16px;
        }

        .impact-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: 1px solid rgba(10, 92, 61, 0.12);
            border-radius: 999px;
            background: var(--project-green-soft);
            color: var(--project-green-700);
            padding: 7px 10px;
            font-size: 0.76rem;
            font-weight: 800;
        }

        .impact-chip.gold {
            border-color: rgba(242, 169, 0, 0.22);
            background: var(--project-gold-soft);
            color: #7b5600;
        }

        .card-action {
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: auto;
            padding-top: 20px;
            color: var(--project-green);
            font-size: 0.88rem;
            font-weight: 900;
            text-decoration: none;
            opacity: 0.78;
            transition: opacity 0.25s ease, transform 0.25s ease;
        }

        .portfolio-card:hover .card-action {
            opacity: 1;
            transform: translateX(2px);
        }

        .empty-projects {
            grid-column: 1 / -1;
            border: 1px dashed rgba(10, 92, 61, 0.22);
            border-radius: 16px;
            padding: 42px;
            text-align: center;
            background: #fff;
            color: var(--project-muted);
        }

        .view-more-wrap {
            display: flex;
            justify-content: center;
            margin-top: 36px;
        }

        .view-more-btn {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            border: 1px solid rgba(10, 92, 61, 0.18);
            border-radius: 999px;
            background: #fff;
            color: var(--project-green);
            padding: 13px 24px;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 12px 28px rgba(16, 35, 27, 0.08);
            transition: transform 0.25s ease, box-shadow 0.25s ease, color 0.25s ease, background 0.25s ease;
        }

        .view-more-btn:hover {
            transform: translateY(-2px);
            background: var(--project-green);
            color: #fff;
            box-shadow: 0 18px 36px rgba(10, 92, 61, 0.20);
        }

        .solar-reels-section {
            margin-top: 72px;
            padding-top: 52px;
            border-top: 1px solid rgba(15, 23, 42, 0.08);
        }

        .reels-heading {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 22px;
            margin-bottom: 26px;
        }

        .reels-heading h2 {
            margin: 0;
            color: var(--project-ink);
            font-size: clamp(1.65rem, 3vw, 2.45rem);
            font-weight: 950;
            letter-spacing: 0;
        }

        .reels-heading p {
            max-width: 570px;
            margin: 10px 0 0;
            color: var(--project-muted);
            line-height: 1.7;
        }

        .video-showcase-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 0 0 24px;
        }

        .video-filter-btn {
            border: 1px solid rgba(10, 92, 61, 0.16);
            border-radius: 999px;
            background: #fff;
            color: var(--project-green-700);
            padding: 10px 16px;
            font-size: 0.84rem;
            font-weight: 900;
            cursor: pointer;
            transition: background 0.22s ease, color 0.22s ease, border-color 0.22s ease, transform 0.22s ease;
        }

        .video-filter-btn:hover,
        .video-filter-btn.is-active {
            border-color: var(--project-green);
            background: var(--project-green);
            color: #fff;
            transform: translateY(-1px);
        }

        .reels-track {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .reel-card {
            position: relative;
            min-height: 410px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 24px;
            background: #0f172a;
            color: #fff;
            cursor: pointer;
            box-shadow: 0 20px 48px rgba(15, 23, 42, 0.15);
            transition: transform 0.28s ease, box-shadow 0.28s ease;
        }

        .reel-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 28px 64px rgba(15, 23, 42, 0.24);
        }

        .reel-card.is-hidden {
            display: none;
        }

        .reel-card img {
            width: 100%;
            height: 100%;
            min-height: 410px;
            object-fit: cover;
            display: block;
            opacity: 0.82;
            transition: transform 0.55s ease, opacity 0.25s ease;
        }

        .reel-card:hover img {
            transform: scale(1.05);
            opacity: 0.74;
        }

        .reel-card::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.08) 0%, rgba(15, 23, 42, 0.74) 100%);
        }

        .reel-play {
            position: absolute;
            inset: 0;
            z-index: 2;
            display: grid;
            place-items: center;
            pointer-events: none;
        }

        .reel-play span {
            width: 64px;
            height: 64px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.93);
            color: var(--project-green);
            box-shadow: 0 20px 38px rgba(0, 0, 0, 0.22);
        }

        .reel-views {
            position: absolute;
            top: 14px;
            right: 14px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.62);
            color: #fff;
            padding: 7px 10px;
            font-size: 0.74rem;
            font-weight: 850;
            backdrop-filter: blur(10px);
        }

        .reel-type {
            position: absolute;
            top: 14px;
            left: 14px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.90);
            color: var(--project-green-700);
            padding: 7px 10px;
            font-size: 0.72rem;
            font-weight: 950;
            backdrop-filter: blur(10px);
        }

        .reel-info {
            position: absolute;
            left: 18px;
            right: 18px;
            bottom: 18px;
            z-index: 2;
        }

        .reel-category {
            display: inline-flex;
            margin-bottom: 10px;
            border-radius: 999px;
            background: rgba(242, 169, 0, 0.94);
            color: #111;
            padding: 6px 10px;
            font-size: 0.68rem;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .reel-title {
            margin: 0;
            color: #fff;
            font-size: 1.02rem;
            font-weight: 950;
            line-height: 1.22;
        }

        .video-modal {
            position: fixed;
            inset: 0;
            z-index: 1090;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 22px;
            background: rgba(2, 6, 23, 0.82);
            backdrop-filter: blur(14px);
        }

        .video-modal.is-open {
            display: flex;
        }

        .video-modal-dialog {
            width: min(980px, 100%);
            max-height: 92vh;
            border-radius: 24px;
            overflow: hidden;
            background: #020617;
            box-shadow: 0 30px 90px rgba(0, 0, 0, 0.45);
        }

        .video-modal.is-vertical .video-modal-dialog {
            width: min(440px, 100%);
        }

        .video-modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 18px;
            color: #fff;
            background: #0f172a;
        }

        .video-modal-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 900;
            line-height: 1.3;
        }

        .video-modal-close {
            width: 38px;
            height: 38px;
            border: none;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.10);
            color: #fff;
            cursor: pointer;
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .video-modal-close:hover {
            transform: rotate(90deg);
            background: rgba(255, 255, 255, 0.18);
        }

        .video-player-shell {
            position: relative;
            aspect-ratio: 16 / 9;
            background: #000;
        }

        .video-modal.is-vertical .video-player-shell {
            aspect-ratio: 9 / 16;
            max-height: calc(92vh - 74px);
        }

        .video-player-shell iframe,
        .video-player-shell video {
            width: 100%;
            height: 100%;
            display: block;
            border: 0;
            background: #000;
        }

        .project-modal {
            position: fixed;
            inset: 0;
            z-index: 1080;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 22px;
            background: rgba(7, 18, 13, 0.72);
            backdrop-filter: blur(12px);
        }

        .project-modal.is-open {
            display: flex;
        }

        .project-modal-dialog {
            width: min(1120px, 100%);
            max-height: 92vh;
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(340px, 0.9fr);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 30px 90px rgba(0, 0, 0, 0.36);
        }

        .modal-gallery {
            min-height: 520px;
            background: #0e1c15;
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .modal-main-image {
            position: relative;
            flex: 1;
            min-height: 360px;
            overflow: hidden;
            border-radius: 16px;
            background: #13251b;
        }

        .modal-main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .gallery-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.45);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.90);
            color: var(--project-green);
            cursor: pointer;
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .gallery-nav:hover {
            transform: translateY(-50%) scale(1.06);
            background: var(--project-gold);
        }

        .gallery-nav.prev {
            left: 14px;
        }

        .gallery-nav.next {
            right: 14px;
        }

        .modal-thumbs {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 3px;
        }

        .modal-thumb {
            width: 86px;
            height: 58px;
            flex: 0 0 auto;
            border: 2px solid transparent;
            border-radius: 10px;
            padding: 0;
            overflow: hidden;
            background: transparent;
            cursor: pointer;
            opacity: 0.68;
            transition: opacity 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
        }

        .modal-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .modal-thumb.is-active,
        .modal-thumb:hover {
            opacity: 1;
            border-color: var(--project-gold);
            transform: translateY(-1px);
        }

        .modal-details {
            position: relative;
            overflow-y: auto;
            padding: 34px;
        }

        .modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 38px;
            height: 38px;
            border: 1px solid rgba(10, 92, 61, 0.14);
            border-radius: 999px;
            background: #fff;
            color: var(--project-ink);
            cursor: pointer;
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .modal-close:hover {
            transform: rotate(90deg);
            background: var(--project-gold);
        }

        .modal-category {
            display: inline-flex;
            border-radius: 999px;
            background: var(--project-green-soft);
            color: var(--project-green);
            padding: 7px 12px;
            font-size: 0.74rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .modal-title {
            margin: 18px 42px 8px 0;
            color: var(--project-ink);
            font-size: clamp(1.55rem, 3vw, 2.35rem);
            font-weight: 950;
            line-height: 1.05;
            letter-spacing: 0;
        }

        .modal-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 16px;
            color: var(--project-muted);
            font-size: 0.9rem;
        }

        .modal-meta span {
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .detail-section {
            margin-top: 26px;
        }

        .detail-section h3 {
            color: var(--project-ink);
            font-size: 0.84rem;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin: 0 0 14px;
        }

        .spec-list {
            display: grid;
            gap: 10px;
        }

        .spec-row {
            display: grid;
            grid-template-columns: 145px 1fr;
            gap: 12px;
            border: 1px solid rgba(10, 92, 61, 0.10);
            border-radius: 12px;
            padding: 12px;
            background: #fbfdfb;
        }

        .spec-label {
            color: var(--project-muted);
            font-size: 0.76rem;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .spec-value {
            color: var(--project-ink);
            font-weight: 850;
        }

        .modal-impact-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .modal-impact {
            border-radius: 14px;
            padding: 16px;
            background: linear-gradient(135deg, var(--project-green-soft), #fff);
            border: 1px solid rgba(10, 92, 61, 0.12);
        }

        .modal-impact strong {
            display: block;
            color: var(--project-green);
            font-size: 1.25rem;
            font-weight: 950;
        }

        .modal-impact span {
            color: var(--project-muted);
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .modal-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            margin-top: 28px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--project-green), var(--project-green-700));
            color: #fff;
            padding: 14px 18px;
            text-decoration: none;
            font-weight: 950;
            box-shadow: 0 18px 30px rgba(10, 92, 61, 0.20);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .modal-cta:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 24px 42px rgba(10, 92, 61, 0.28);
        }

        @media (max-width: 991px) {
            .reels-heading {
                display: block;
            }

            .reels-track {
                display: flex;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                padding-bottom: 10px;
            }

            .reel-card {
                flex: 0 0 270px;
                scroll-snap-align: start;
            }

            .project-modal-dialog {
                grid-template-columns: 1fr;
                overflow-y: auto;
            }

            .modal-gallery {
                min-height: auto;
            }

            .modal-main-image {
                min-height: 290px;
            }
        }

        @media (max-width: 575px) {
            .project-showcase {
                padding: 56px 0 72px;
            }

            .projects-grid {
                grid-template-columns: 1fr;
            }

            .project-card-body {
                padding: 18px;
            }

            .project-modal {
                padding: 10px;
            }

            .modal-details {
                padding: 24px 18px;
            }

            .spec-row {
                grid-template-columns: 1fr;
                gap: 4px;
            }

            .modal-impact-grid {
                grid-template-columns: 1fr;
            }

            .video-modal {
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <?php include "includes/header.php"; ?>

    <section class="hero-projects">
        <div class="container" data-aos="fade-up">
            <span class="hero-eyebrow">Our Portfolio</span>
            <h1>Turning Vision Into Power</h1>
            <p class="hero-copy">Explore SolarPower Energy Corporation installations across homes, businesses, and long-term maintenance projects.</p>
            <div class="hero-actions">
                <a href="#projectShowcase" class="hero-btn hero-btn-primary">View Projects</a>
                <a href="/contact" class="hero-btn hero-btn-outline">Get a Similar Quote</a>
            </div>
        </div>
    </section>

    <main class="project-showcase" id="projectShowcase">
        <div class="container">
            <div class="portfolio-heading" data-aos="fade-up">
                <div>
                    <div class="portfolio-kicker">Project Showcase</div>
                    <h2 class="portfolio-title">Built solar systems with measurable impact.</h2>
                    <p class="portfolio-subtitle">Browse recent installations and service work, then open each project for gallery images and technical details.</p>
                </div>
            </div>

            <div class="project-filter-bar" role="tablist" aria-label="Filter projects by category" data-aos="fade-up" data-aos-delay="120">
                <button class="project-filter is-active" type="button" data-filter="all" role="tab" aria-selected="true"><span>All</span></button>
                <button class="project-filter" type="button" data-filter="residential" role="tab" aria-selected="false"><span>Residential</span></button>
                <button class="project-filter" type="button" data-filter="commercial" role="tab" aria-selected="false"><span>Commercial</span></button>
                <button class="project-filter" type="button" data-filter="preventive-maintenance" role="tab" aria-selected="false"><span>Preventive Maintenance</span></button>
            </div>

            <section class="projects-grid" id="projectsGrid" aria-live="polite">
                <?php if (empty($portfolio_projects)): ?>
                    <div class="empty-projects">
                        <h3>No projects available yet.</h3>
                        <p class="mb-0">Published portfolio projects will appear here.</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($portfolio_projects as $index => $project):
                    $projectId = (int) ($project['id'] ?? 0);
                    $category = project_category($project);
                    $images = project_images($project['image_url'] ?? '');
                    $mainImage = $images[0];
                    $title = trim((string) ($project['project_name'] ?? 'Solar Project'));
                    $subtitle = trim((string) ($project['subtitle'] ?? 'Solar Project'));
                    $location = trim((string) ($project['location'] ?? 'Philippines'));
                    $systemType = trim((string) ($project['system_type'] ?? 'Solar PV System'));
                    $serviceType = trim((string) ($project['service_type'] ?? 'Solar Project'));
                    $co2 = trim((string) ($project['co2_reduction'] ?? '0 t'));
                    $trees = trim((string) ($project['efficiency_rate'] ?? '0'));
                    $dateCompleted = !empty($project['created_at']) ? date('M d, Y', strtotime($project['created_at'])) : 'Recently completed';
                    $specs = project_specs($project);
                    $isInitiallyHidden = $index >= 6;
                    $projectPayload = [
                        'id' => $projectId,
                        'title' => $title,
                        'category' => $category['label'],
                        'categorySlug' => $category['slug'],
                        'subtitle' => $subtitle,
                        'location' => $location,
                        'dateCompleted' => $dateCompleted,
                        'serviceType' => $serviceType,
                        'systemType' => $systemType,
                        'co2' => $co2,
                        'trees' => $trees,
                        'images' => $images,
                        'specs' => $specs,
                    ];
                    $projectJson = htmlspecialchars(json_encode($projectPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8');
                ?>
                    <article class="project-card-shell<?= $isInitiallyHidden ? ' is-collapsed' : ''; ?>"
                             data-category="<?= project_escape($category['slug']); ?>"
                             data-project-index="<?= (int) $index; ?>"
                             data-aos="fade-up"
                             data-aos-delay="<?= (int) (($index % 6) * 70); ?>">
                        <div class="portfolio-card" tabindex="0" role="button" aria-label="View details for <?= project_escape($title); ?>" data-project-id="<?= $projectId; ?>" data-project='<?= $projectJson; ?>'>
                            <div class="project-image-wrap">
                                <img src="<?= project_escape($mainImage); ?>" alt="<?= project_escape($title); ?>" loading="lazy">
                                <span class="system-badge"><?= project_escape(($systemType !== '' ? $systemType : $serviceType) . ' • ' . $category['label']); ?></span>
                            </div>
                            <div class="project-card-body">
                                <h3 class="project-title"><?= project_escape($title); ?></h3>
                                <div class="project-location">
                                    <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                                    <span><?= project_escape($location); ?></span>
                                </div>
                                <div class="project-spec-line">
                                    <i class="fas fa-solar-panel" aria-hidden="true"></i>
                                    <span><?= project_escape($systemType !== '' ? $systemType : $serviceType); ?></span>
                                </div>
                                <div class="impact-chips" aria-label="Environmental impact">
                                    <span class="impact-chip"><i class="fas fa-smog" aria-hidden="true"></i><?= project_escape($co2); ?> CO2 saved</span>
                                    <span class="impact-chip gold"><i class="fas fa-tree" aria-hidden="true"></i><?= project_escape($trees); ?> trees</span>
                                </div>
                                <span class="card-action">
                                    View Project Details
                                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                                </span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>

            <?php if (count($portfolio_projects) > 6): ?>
                <div class="view-more-wrap">
                    <button class="view-more-btn" type="button" id="viewMoreProjects">
                        <span>View More Projects</span>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (!empty($portfolio_videos)): ?>
                <section class="solar-reels-section" aria-label="Solar Video Tours and Site Reels">
                    <div class="reels-heading" data-aos="fade-up">
                        <div>
                            <div class="portfolio-kicker">Video Showcase</div>
                            <h2>Solar Video Tours &amp; Site Reels</h2>
                        </div>
                        <p>Watch our installation walkthroughs and solar short reels.</p>
                    </div>

                    <div class="video-showcase-filters" role="tablist" aria-label="Filter solar videos" data-aos="fade-up" data-aos-delay="70">
                        <button class="video-filter-btn is-active" type="button" data-video-filter="all" aria-selected="true">All Videos</button>
                        <button class="video-filter-btn" type="button" data-video-filter="landscape" aria-selected="false">Landscape Tours</button>
                        <button class="video-filter-btn" type="button" data-video-filter="vertical" aria-selected="false">Short Reels</button>
                    </div>

                    <div class="reels-track">
                        <?php foreach ($portfolio_videos as $videoIndex => $video):
                            $videoPayload = project_video_payload($video);
                            $videoJson = htmlspecialchars(json_encode($videoPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8');
                            $videoTitle = $videoPayload['title'];
                            $videoThumb = $videoPayload['thumbnail'];
                            $videoViews = project_format_metric((float) $videoPayload['views']);
                            $videoFormat = $videoPayload['format'] === 'vertical' ? 'vertical' : 'landscape';
                            $videoTypeLabel = $videoFormat === 'vertical' ? 'Short Reel' : 'Video Tour';
                        ?>
                            <article class="reel-card"
                                     role="button"
                                     tabindex="0"
                                     data-video='<?= $videoJson; ?>'
                                     data-video-format="<?= project_escape($videoFormat); ?>"
                                     data-aos="fade-up"
                                     data-aos-delay="<?= (int) (($videoIndex % 4) * 70); ?>"
                                     aria-label="Play video <?= project_escape($videoTitle); ?>">
                                <img src="<?= project_escape($videoThumb); ?>" alt="<?= project_escape($videoTitle); ?>" loading="lazy">
                                <span class="reel-type"><i class="fas <?= $videoFormat === 'vertical' ? 'fa-mobile-screen-button' : 'fa-tv'; ?>" aria-hidden="true"></i><?= project_escape($videoTypeLabel); ?></span>
                                <span class="reel-views"><i class="fas fa-eye" aria-hidden="true"></i><?= project_escape($videoViews); ?></span>
                                <span class="reel-play"><span><i class="fas fa-play" aria-hidden="true"></i></span></span>
                                <div class="reel-info">
                                    <span class="reel-category"><?= project_escape($videoPayload['category']); ?></span>
                                    <h3 class="reel-title"><?= project_escape($videoTitle); ?></h3>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>

    <div class="project-modal" id="projectModal" role="dialog" aria-modal="true" aria-labelledby="modalProjectTitle">
        <div class="project-modal-dialog">
            <section class="modal-gallery" aria-label="Project image gallery">
                <div class="modal-main-image">
                    <img src="assets/img/product-placeholder.png" alt="" id="modalMainImage">
                    <button class="gallery-nav prev" type="button" id="galleryPrev" aria-label="Previous image"><i class="fas fa-chevron-left"></i></button>
                    <button class="gallery-nav next" type="button" id="galleryNext" aria-label="Next image"><i class="fas fa-chevron-right"></i></button>
                </div>
                <div class="modal-thumbs" id="modalThumbs"></div>
            </section>

            <section class="modal-details">
                <button class="modal-close" type="button" id="modalClose" aria-label="Close project details">
                    <i class="fas fa-times"></i>
                </button>
                <span class="modal-category" id="modalCategory">Project</span>
                <h2 class="modal-title" id="modalProjectTitle">Project Name</h2>
                <div class="modal-meta">
                    <span><i class="fas fa-map-marker-alt" aria-hidden="true"></i><span id="modalLocation">Location</span></span>
                    <span><i class="fas fa-calendar-check" aria-hidden="true"></i><span id="modalDate">Date</span></span>
                </div>

                <div class="detail-section">
                    <h3>System Specs</h3>
                    <div class="spec-list">
                        <div class="spec-row"><span class="spec-label">Project Type</span><span class="spec-value" id="modalServiceType">-</span></div>
                        <div class="spec-row"><span class="spec-label">Inverter Size</span><span class="spec-value" id="modalInverter">-</span></div>
                        <div class="spec-row"><span class="spec-label">Solar Panel Rating</span><span class="spec-value" id="modalPanel">-</span></div>
                        <div class="spec-row"><span class="spec-label">Battery Storage</span><span class="spec-value" id="modalBattery">-</span></div>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>Environmental Impact</h3>
                    <div class="modal-impact-grid">
                        <div class="modal-impact">
                            <strong id="modalCo2">0 t</strong>
                            <span>Tons of CO2 Saved</span>
                        </div>
                        <div class="modal-impact">
                            <strong id="modalTrees">0</strong>
                            <span>Trees Planted Equivalent</span>
                        </div>
                    </div>
                </div>

                <a href="/contact" class="modal-cta">
                    Get a Similar Quote
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </section>
        </div>
    </div>

    <div class="video-modal" id="videoModal" role="dialog" aria-modal="true" aria-labelledby="videoModalTitle">
        <div class="video-modal-dialog">
            <div class="video-modal-head">
                <h2 class="video-modal-title" id="videoModalTitle">Solar Video</h2>
                <button class="video-modal-close" type="button" id="videoModalClose" aria-label="Close video">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="video-player-shell" id="videoPlayerShell"></div>
        </div>
    </div>

    <?php include "includes/footer.php"; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        if (typeof AOS !== 'undefined') {
            AOS.init({ duration: 750, once: true, offset: 90 });
        }

        (function () {
            'use strict';

            var modal = document.getElementById('videoModal');
            var closeButton = document.getElementById('videoModalClose');
            var title = document.getElementById('videoModalTitle');
            var shell = document.getElementById('videoPlayerShell');

            function sourceUrl(path) {
                if (!path) {
                    return '';
                }
                if (/^https?:\/\//i.test(path)) {
                    return path;
                }
                return path.replace(/^\/+/, '');
            }

            function embedUrl(rawUrl) {
                try {
                    var url = new URL(rawUrl, window.location.href);
                    var host = url.hostname.replace(/^www\./, '');
                    var videoId = '';

                    if (host === 'youtu.be') {
                        videoId = url.pathname.split('/').filter(Boolean)[0] || '';
                        return videoId ? 'https://www.youtube.com/embed/' + encodeURIComponent(videoId) + '?autoplay=1&rel=0' : rawUrl;
                    }

                    if (host.indexOf('youtube.com') !== -1) {
                        if (url.pathname.indexOf('/shorts/') === 0) {
                            videoId = url.pathname.split('/').filter(Boolean)[1] || '';
                        } else {
                            videoId = url.searchParams.get('v') || '';
                        }
                        return videoId ? 'https://www.youtube.com/embed/' + encodeURIComponent(videoId) + '?autoplay=1&rel=0' : rawUrl;
                    }

                    if (host.indexOf('vimeo.com') !== -1) {
                        videoId = url.pathname.split('/').filter(Boolean).pop() || '';
                        return videoId ? 'https://player.vimeo.com/video/' + encodeURIComponent(videoId) + '?autoplay=1' : rawUrl;
                    }
                } catch (error) {
                    return rawUrl;
                }

                return rawUrl;
            }

            function looksLikeVideoFile(url) {
                return /\.(mp4|webm|ogg|mov)(\?|#|$)/i.test(url || '');
            }

            function trackView(video) {
                if (!video || !video.id) {
                    return;
                }

                try {
                    var fd = new FormData();
                    fd.append('id', video.id);
                    fetch('api/portfolio_video_view.php', { method: 'POST', body: fd }).catch(function () {});
                } catch (error) {}
            }

            function openVideo(video) {
                if (!modal || !shell || !video) {
                    return;
                }

                var mediaUrl = sourceUrl(video.mediaUrl || video.media_url);
                var isVertical = video.format === 'vertical' || video.video_format === 'vertical';
                modal.classList.toggle('is-vertical', isVertical);
                title.textContent = video.title || 'Solar Video';
                shell.innerHTML = '';

                if ((video.mediaType || video.media_type) === 'file' || looksLikeVideoFile(mediaUrl)) {
                    var videoEl = document.createElement('video');
                    videoEl.src = mediaUrl;
                    videoEl.controls = true;
                    videoEl.autoplay = true;
                    videoEl.playsInline = true;
                    shell.appendChild(videoEl);
                } else {
                    var iframe = document.createElement('iframe');
                    iframe.src = embedUrl(mediaUrl);
                    iframe.title = video.title || 'Solar video';
                    iframe.allow = 'autoplay; fullscreen; picture-in-picture';
                    iframe.allowFullscreen = true;
                    shell.appendChild(iframe);
                }

                modal.classList.add('is-open');
                document.body.style.overflow = 'hidden';
                trackView(video);
            }

            function closeVideo() {
                if (!modal || !shell) {
                    return;
                }
                modal.classList.remove('is-open');
                modal.classList.remove('is-vertical');
                shell.innerHTML = '';
                document.body.style.overflow = '';
            }

            document.querySelectorAll('.reel-card').forEach(function (card) {
                function launch() {
                    try {
                        openVideo(JSON.parse(card.dataset.video));
                    } catch (error) {
                        console.error('Unable to open reel:', error);
                    }
                }

                card.addEventListener('click', launch);
                card.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        launch();
                    }
                });
            });

            document.querySelectorAll('.video-filter-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    var filter = button.dataset.videoFilter || 'all';

                    document.querySelectorAll('.video-filter-btn').forEach(function (item) {
                        var isActive = item === button;
                        item.classList.toggle('is-active', isActive);
                        item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                    });

                    document.querySelectorAll('.reel-card').forEach(function (card) {
                        var matches = filter === 'all' || card.dataset.videoFormat === filter;
                        card.classList.toggle('is-hidden', !matches);
                    });

                    if (typeof AOS !== 'undefined') {
                        AOS.refresh();
                    }
                });
            });

            if (closeButton) {
                closeButton.addEventListener('click', closeVideo);
            }

            if (modal) {
                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        closeVideo();
                    }
                });
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal && modal.classList.contains('is-open')) {
                    closeVideo();
                }
            });
        }());

        (function () {
            'use strict';

            var grid = document.getElementById('projectsGrid');
            var cards = Array.prototype.slice.call(document.querySelectorAll('.project-card-shell'));
            var filters = Array.prototype.slice.call(document.querySelectorAll('.project-filter'));
            var viewMoreBtn = document.getElementById('viewMoreProjects');
            var activeFilter = 'all';
            var expanded = false;
            var initialVisibleCount = 6;

            function setVisible(card, visible) {
                if (visible) {
                    card.classList.remove('is-filtered-out');
                    card.classList.remove('is-collapsed');
                } else {
                    card.classList.add('is-filtered-out');
                }
            }

            function applyFilters() {
                var visibleMatches = 0;
                var hasHiddenProjects = false;

                cards.forEach(function (card) {
                    var matchesFilter = activeFilter === 'all'
                        || card.dataset.category === activeFilter;

                    if (!matchesFilter) {
                        setVisible(card, false);
                        return;
                    }

                    visibleMatches++;
                    var shouldCollapse = activeFilter === 'all' && !expanded && visibleMatches > initialVisibleCount;
                    card.classList.remove('is-filtered-out');
                    card.classList.toggle('is-collapsed', shouldCollapse);
                    if (shouldCollapse) {
                        hasHiddenProjects = true;
                    }
                });

                if (viewMoreBtn) {
                    viewMoreBtn.style.display = activeFilter === 'all' && (hasHiddenProjects || expanded) ? 'inline-flex' : 'none';
                    viewMoreBtn.querySelector('span').textContent = expanded ? 'Show Fewer Projects' : 'View More Projects';
                    viewMoreBtn.querySelector('i').className = expanded ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
                }

                if (typeof AOS !== 'undefined') {
                    AOS.refresh();
                }
            }

            filters.forEach(function (button) {
                button.addEventListener('click', function () {
                    activeFilter = button.dataset.filter;
                    expanded = activeFilter !== 'all';

                    filters.forEach(function (item) {
                        var isActive = item === button;
                        item.classList.toggle('is-active', isActive);
                        item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                    });

                    applyFilters();
                });
            });

            if (viewMoreBtn) {
                viewMoreBtn.addEventListener('click', function () {
                    expanded = !expanded;
                    applyFilters();
                });
            }

            if (grid) {
                applyFilters();
            }
        }());

        (function () {
            'use strict';

            var modal = document.getElementById('projectModal');
            var closeButton = document.getElementById('modalClose');
            var mainImage = document.getElementById('modalMainImage');
            var thumbs = document.getElementById('modalThumbs');
            var prevButton = document.getElementById('galleryPrev');
            var nextButton = document.getElementById('galleryNext');
            var currentImages = [];
            var currentIndex = 0;

            function text(id, value) {
                var el = document.getElementById(id);
                if (el) {
                    el.textContent = value || '-';
                }
            }

            function renderImage(index) {
                if (!currentImages.length) {
                    return;
                }

                currentIndex = (index + currentImages.length) % currentImages.length;
                mainImage.src = currentImages[currentIndex];
                mainImage.alt = 'Project image ' + (currentIndex + 1);

                thumbs.querySelectorAll('.modal-thumb').forEach(function (thumb, thumbIndex) {
                    thumb.classList.toggle('is-active', thumbIndex === currentIndex);
                });
            }

            function renderThumbs(images) {
                thumbs.innerHTML = '';
                images.forEach(function (src, index) {
                    var button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'modal-thumb' + (index === 0 ? ' is-active' : '');
                    button.setAttribute('aria-label', 'View image ' + (index + 1));

                    var img = document.createElement('img');
                    img.src = src;
                    img.alt = '';
                    button.appendChild(img);

                    button.addEventListener('click', function () {
                        renderImage(index);
                    });

                    thumbs.appendChild(button);
                });

                var showControls = images.length > 1;
                prevButton.style.display = showControls ? 'inline-flex' : 'none';
                nextButton.style.display = showControls ? 'inline-flex' : 'none';
                thumbs.style.display = showControls ? 'flex' : 'none';
            }

            function openModal(project) {
                currentImages = Array.isArray(project.images) && project.images.length ? project.images : ['assets/img/product-placeholder.png'];
                currentIndex = 0;

                text('modalCategory', project.category);
                text('modalProjectTitle', project.title);
                text('modalLocation', project.location);
                text('modalDate', project.dateCompleted);
                text('modalServiceType', project.serviceType || project.systemType);
                text('modalInverter', project.specs && project.specs.inverter);
                text('modalPanel', project.specs && project.specs.panel);
                text('modalBattery', project.specs && project.specs.battery);
                text('modalCo2', project.co2);
                text('modalTrees', project.trees);

                renderThumbs(currentImages);
                renderImage(0);

                modal.classList.add('is-open');
                document.body.style.overflow = 'hidden';
                closeButton.focus();
            }

            function closeModal() {
                modal.classList.remove('is-open');
                document.body.style.overflow = '';
            }

            function launchCard(card) {
                try {
                    openModal(JSON.parse(card.dataset.project));
                } catch (error) {
                    console.error('Unable to open project details:', error);
                }
            }

            document.querySelectorAll('.portfolio-card').forEach(function (card) {
                function launch() {
                    launchCard(card);
                }

                card.addEventListener('click', launch);
                card.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        launch();
                    }
                });
            });

            (function openLinkedProject() {
                var params = new URLSearchParams(window.location.search);
                var projectId = params.get('project');
                if (!projectId) {
                    return;
                }

                var linkedCard = Array.prototype.slice.call(document.querySelectorAll('.portfolio-card')).find(function (card) {
                    return String(card.dataset.projectId || '') === String(projectId);
                });
                if (!linkedCard) {
                    return;
                }

                var showcase = document.getElementById('projectShowcase');
                if (showcase) {
                    showcase.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }

                window.setTimeout(function () {
                    launchCard(linkedCard);
                }, 450);
            }());

            closeButton.addEventListener('click', closeModal);
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });
            prevButton.addEventListener('click', function () {
                renderImage(currentIndex - 1);
            });
            nextButton.addEventListener('click', function () {
                renderImage(currentIndex + 1);
            });
            document.addEventListener('keydown', function (event) {
                if (!modal.classList.contains('is-open')) {
                    return;
                }

                if (event.key === 'Escape') {
                    closeModal();
                } else if (event.key === 'ArrowLeft') {
                    renderImage(currentIndex - 1);
                } else if (event.key === 'ArrowRight') {
                    renderImage(currentIndex + 1);
                }
            });
        }());
    </script>
</body>

</html>
