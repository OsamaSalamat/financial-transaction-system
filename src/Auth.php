<?php
declare(strict_types=1);
final class Auth {
    public static function user(): ?array { return $_SESSION['user'] ?? null; }
    public static function check(): bool { return isset($_SESSION['user']); }
    public static function login(PDO $pdo, string $email, string $password): bool {
        $st=$pdo->prepare('SELECT * FROM users WHERE email=? AND status="active" LIMIT 1'); $st->execute([$email]); $u=$st->fetch();
        if (!$u || !password_verify($password,$u['password'])) return false;
        session_regenerate_id(true); unset($u['password']); $_SESSION['user']=$u; return true;
    }
    public static function requireLogin(): void { if (!self::check()) redirect('login.php'); }
    public static function logout(): never { $_SESSION=[]; session_destroy(); redirect('login.php'); }
    public static function requireRole(array $roles): void { self::requireLogin(); if (!in_array($_SESSION['user']['role'],$roles,true)) { http_response_code(403); exit('Forbidden'); } }
}
