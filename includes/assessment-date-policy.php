<?php
function solarAssessmentDateAllowed(string $value, ?DateTimeImmutable $today = null): bool
{
    $timezone = new DateTimeZone('Asia/Manila');
    $today = ($today ?? new DateTimeImmutable('now', $timezone))->setTimezone($timezone)->setTime(0, 0);
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, $timezone);
    return $date !== false
        && $date->format('Y-m-d') === $value
        && $date >= $today->modify('+3 days')
        && $date->format('w') !== '0';
}
