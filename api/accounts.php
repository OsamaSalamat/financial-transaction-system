<?php
require_once __DIR__.'/../config/bootstrap.php';
ApiAuth::require($pdo);
if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(['error'=>'Method not allowed'],405);
$st=$pdo->query('SELECT id,account_code,name,account_type,status FROM accounts ORDER BY account_code');
json_response(['data'=>$st->fetchAll()]);
