<?php
// Metadata only: no database writes, sessions, or visible markup.
function solar_seo_json(array $data): void {
    echo '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) . '</script>';
}
function solar_seo_text(string $value, int $limit = 160): string {
    $value = preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    return mb_substr(trim($value), 0, $limit);
}
function solar_seo_meta(string $path, string $description, array $crumbs = []): void {
    $url = 'https://solarpower.com.ph' . $path;
    echo '<link rel="canonical" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    echo '<meta name="description" content="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '">' . "\n";
    if ($crumbs) {
        $items = []; $position = 1;
        foreach ($crumbs as $label => $href) $items[] = ['@type'=>'ListItem','position'=>$position++,'name'=>$label,'item'=>'https://solarpower.com.ph'.$href];
        solar_seo_json(['@context'=>'https://schema.org','@type'=>'BreadcrumbList','itemListElement'=>$items]);
    }
}
function solar_seo_product_slug(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = strtolower(preg_replace('~-+~', '-', trim($text, '-')));
    return empty($text) ? 'n-a' : $text;
}
