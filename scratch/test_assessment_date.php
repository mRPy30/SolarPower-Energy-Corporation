<?php
require_once __DIR__ . '/../includes/assessment-date-policy.php';

$cases = [
    ['2026-09-09', '2026-09-09', false],
    ['2026-09-09', '2026-09-11', false],
    ['2026-09-09', '2026-09-12', true],
    ['2026-09-09', '2026-09-13', false],
    ['2026-09-09', '2026-09-14', true],
    ['2026-09-09', '2025-12-31', false],
    ['2026-12-30', '2027-01-01', false],
    ['2026-12-30', '2027-01-02', true],
    ['2026-12-30', '2027-01-03', false],
    ['2026-09-09', '2027-02-30', false],
    ['2026-09-09', 'garbage', false],
    ['2026-09-09', '', false],
    ['2028-02-26', '2028-02-29', true],
];
foreach ($cases as [$today, $value, $expected]) {
    $actual = solarAssessmentDateAllowed($value, new DateTimeImmutable($today, new DateTimeZone('Asia/Manila')));
    if ($actual !== $expected) {
        fwrite(STDERR, "FAIL: $today -> $value\n");
        exit(1);
    }
}
echo count($cases) . " assessment date policy checks passed.\n";
