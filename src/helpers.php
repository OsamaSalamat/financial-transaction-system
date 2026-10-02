<?php
declare(strict_types=1);
function e(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function redirect(string $path): never { header('Location: ' . $path); exit; }
function csrf_token(): string { if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32)); return $_SESSION['_csrf']; }
function verify_csrf(?string $token): void { if (!$token || !hash_equals($_SESSION['_csrf'] ?? '', $token)) { http_response_code(419); exit('Invalid CSRF token.'); } }
function money(string|int|float $amount, string $currency='USD'): string { return $currency . ' ' . number_format((float)$amount, 2); }
function flash(string $key, ?string $value=null): ?string { if ($value !== null) { $_SESSION['_flash'][$key] = $value; return null; } $v = $_SESSION['_flash'][$key] ?? null; unset($_SESSION['_flash'][$key]); return $v; }
function json_response(array $data, int $status=200): never { http_response_code($status); header('Content-Type: application/json'); echo json_encode($data, JSON_UNESCAPED_SLASHES); exit; }
