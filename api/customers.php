<?php
require_once __DIR__.'/../config/bootstrap.php';
ApiAuth::require($pdo);
if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(['error'=>'Method not allowed'],405);
$st=$pdo->query('SELECT id,name,email,phone,status,created_at FROM customers ORDER BY id DESC');
json_response(['data'=>$st->fetchAll()]);
