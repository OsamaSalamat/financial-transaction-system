<?php
require_once __DIR__.'/../config/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error'=>'Method not allowed'],405);
$data=json_decode(file_get_contents('php://input'),true)??[];
$email=trim((string)($data['email']??'')); $password=(string)($data['password']??'');
if (!filter_var($email,FILTER_VALIDATE_EMAIL) || $password==='') json_response(['error'=>'Valid email and password are required'],422);
$stmt=$pdo->prepare('SELECT * FROM users WHERE email=? AND status="active" LIMIT 1'); $stmt->execute([$email]); $user=$stmt->fetch();
if (!$user || !password_verify($password,$user['password'])) json_response(['error'=>'Invalid credentials'],401);
unset($user['password']);
$token=ApiAuth::issue($pdo,$user);
json_response(['token'=>$token,'token_type'=>'Bearer','expires_in'=>43200,'user'=>$user]);
