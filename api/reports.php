<?php
require_once __DIR__.'/../config/bootstrap.php';
ApiAuth::require($pdo);
if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(['error'=>'Method not allowed'],405);
$report=new Report($pdo);
json_response(['data'=>$report->transactions($_GET)]);
