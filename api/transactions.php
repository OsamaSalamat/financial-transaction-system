<?php
require_once __DIR__.'/../config/bootstrap.php';
$user=ApiAuth::require($pdo);
$ledger=new Ledger($pdo);
if ($_SERVER['REQUEST_METHOD']==='POST') {
 if ($user['role']==='viewer') json_response(['error'=>'Viewer cannot post transactions'],403);
 $data=json_decode(file_get_contents('php://input'),true)??[];
 try { $result=$ledger->post($data,(int)$user['id']); json_response($result,$result['duplicate']?200:201); }
 catch(Throwable $e){ json_response(['error'=>$e->getMessage()],422); }
}
if ($_SERVER['REQUEST_METHOD']==='GET') {
 $report=new Report($pdo); json_response(['data'=>$report->transactions($_GET)]);
}
if ($_SERVER['REQUEST_METHOD']==='DELETE') {
 $id=(int)($_GET['id']??0); if(!$id) json_response(['error'=>'Transaction id is required'],422);
 if ($user['role']==='viewer') json_response(['error'=>'Viewer cannot reverse transactions'],403);
 try { json_response($ledger->reverse($id,(int)$user['id'])); } catch(Throwable $e){ json_response(['error'=>$e->getMessage()],422); }
}
json_response(['error'=>'Method not allowed'],405);
