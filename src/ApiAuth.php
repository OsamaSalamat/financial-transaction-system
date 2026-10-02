<?php
declare(strict_types=1);
final class ApiAuth {
    public static function issue(PDO $pdo, array $user): string {
        $raw = bin2hex(random_bytes(32));
        $hash = hash('sha256', $raw);
        $stmt = $pdo->prepare('INSERT INTO api_tokens (user_id,token_hash,expires_at) VALUES (?,?,DATE_ADD(NOW(), INTERVAL 12 HOUR))');
        $stmt->execute([(int)$user['id'], $hash]);
        return $raw;
    }
    public static function user(PDO $pdo): ?array {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/^Bearer\s+([A-Fa-f0-9]{64})$/', $header, $m)) return null;
        $stmt = $pdo->prepare('SELECT u.* FROM api_tokens t JOIN users u ON u.id=t.user_id WHERE t.token_hash=? AND t.revoked_at IS NULL AND t.expires_at>NOW() AND u.status="active" LIMIT 1');
        $stmt->execute([hash('sha256', $m[1])]);
        $user = $stmt->fetch();
        if (!$user) return null;
        unset($user['password']);
        $pdo->prepare('UPDATE api_tokens SET last_used_at=NOW() WHERE token_hash=?')->execute([hash('sha256', $m[1])]);
        return $user;
    }
    public static function require(PDO $pdo, array $roles=[]): array {
        $user = self::user($pdo);
        if (!$user) json_response(['error'=>'Unauthorized'],401);
        if ($roles && !in_array($user['role'],$roles,true)) json_response(['error'=>'Forbidden'],403);
        return $user;
    }
}
