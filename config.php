<?php
// Shared setup: session, database (SQLite, no server needed), helpers.
session_start();

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) mkdir($dataDir, 0775, true); // created automatically on first run
$db = new PDO('sqlite:' . $dataDir . '/users.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
)');

function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

function clean(string $v): string { return trim(preg_replace('/\s+/', ' ', $v)); }

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_ok(): bool {
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

function flash(string $type, string $msg): void { $_SESSION['flash'] = [$type, $msg]; }

function take_flash(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function redirect(string $to): never { header("Location: $to"); exit; }
