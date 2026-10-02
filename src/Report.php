<?php
declare(strict_types=1);
final class Report {
    public function __construct(private PDO $pdo) {}
    public function dashboard(): array {
        return [
            'total_posted'=>(float)$this->pdo->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE status='posted'")->fetchColumn(),
            'transaction_count'=>(int)$this->pdo->query("SELECT COUNT(*) FROM transactions WHERE status='posted'")->fetchColumn(),
            'customers'=>(int)$this->pdo->query("SELECT COUNT(*) FROM customers WHERE status='active'")->fetchColumn(),
            'funding_sources'=>(int)$this->pdo->query("SELECT COUNT(*) FROM funding_sources WHERE status='active'")->fetchColumn(),
        ];
    }
    public function transactions(array $f=[]): array {
        $where=[];$p=[]; if(!empty($f['customer_id'])){$where[]='t.customer_id=?';$p[]=$f['customer_id'];} if(!empty($f['type'])){$where[]='t.transaction_type=?';$p[]=$f['type'];} if(!empty($f['from'])){$where[]='DATE(t.created_at)>=?';$p[]=$f['from'];} if(!empty($f['to'])){$where[]='DATE(t.created_at)<=?';$p[]=$f['to'];}
        $sql='SELECT t.*,c.name customer_name,fs.name funding_source FROM transactions t LEFT JOIN customers c ON c.id=t.customer_id LEFT JOIN funding_sources fs ON fs.id=t.funding_source_id '.($where?'WHERE '.implode(' AND ',$where):'').' ORDER BY t.id DESC LIMIT 500'; $st=$this->pdo->prepare($sql);$st->execute($p);return $st->fetchAll();
    }
}
