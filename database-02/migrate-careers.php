<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__.'/../config/db_pdo.php';
$db=getPDO();
$db->exec(file_get_contents(__DIR__.'/careers.sql'));
echo "Careers tables are ready.\n";
