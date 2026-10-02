<?php
declare(strict_types=1);
$config = require __DIR__ . '/config.php';
session_name('ledgerflow_session');
session_start();

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $config['db']['host'], $config['db']['port'], $config['db']['name'], $config['db']['charset']);
try {
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed. Check config/config.php and create the database using database/schema.sql.');
}
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Ledger.php';
require_once __DIR__ . '/../src/Report.php';
require_once __DIR__ . '/../src/ApiAuth.php';
