<?php
require_once __DIR__.'/../config/bootstrap.php';
$user=ApiAuth::require($pdo);
if ($_SERVER['REQUEST_METHOD']==='DELETE') {
 $h=$_SERVER['HTTP_AUTHORIZATION']??''; preg_match('/^Bearer\s+([A-Fa-f0-9]{64})$/',$h,$m);
 if ($m) $pdo->prepare('UPDATE api_tokens SET revoked_at=NOW() WHERE token_hash=?')->execute([hash('sha256',$m[1])]);
 json_response(['message'=>'Token revoked']);
}
json_response(['user'=>$user]);
