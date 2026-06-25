<?php
/**
 * ═══════════════════════════════════════════════════════════════════
 * config.php — Bootstrap configuration
 * ───────────────────────────────────────────────────────────────────
 * Reads configuration from .env (which is gitignored).
 * Never commit credentials in this file.
 * ═══════════════════════════════════════════════════════════════════
 */

/* ── Tiny .env loader (no Composer dependency) ────────────────── */
(function () {
    $envFile = __DIR__ . '/.env';
    if (!is_readable($envFile)) {
        return; // fall back to real environment variables / defaults
    }
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (!str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        // Strip surrounding quotes
        if (strlen($v) >= 2 && (($v[0] === '"' && $v[-1] === '"') || ($v[0] === "'" && $v[-1] === "'"))) {
            $v = substr($v, 1, -1);
        }
        if (!array_key_exists($k, $_ENV)) {
            $_ENV[$k] = $v;
            putenv("$k=$v");
        }
    }
})();

function env(string $key, $default = null) {
    $v = $_ENV[$key] ?? getenv($key);
    return ($v === false || $v === null || $v === '') ? $default : $v;
}

/* ── Application constants ────────────────────────────────────── */
define('APP_ENV',            env('APP_ENV', 'production'));
define('APP_DEBUG',          (bool) env('APP_DEBUG', '0'));
define('DB_HOST',            env('DB_HOST', 'localhost'));
define('DB_NAME',            env('DB_NAME', 'chlef_biostat'));
define('DB_USER',            env('DB_USER', 'root'));
define('DB_PASS',            env('DB_PASS', ''));
define('SITE_NAME',          env('SITE_NAME', 'Questionnaire Chlef 2026'));
define('SITE_URL',           env('SITE_URL', 'http://localhost'));
define('TARGET_N',           (int) env('TARGET_N', 1220));
define('ADMIN_EMAIL',        env('ADMIN_EMAIL', ''));
define('SESSION_DURATION',   (int) env('SESSION_DURATION', 28800));
define('MAX_LOGIN_ATTEMPTS', (int) env('MAX_LOGIN_ATTEMPTS', 5));
define('LOCKOUT_DURATION',   (int) env('LOCKOUT_DURATION', 900));
define('DEFAULT_LANG',       env('DEFAULT_LANG', 'ar'));

date_default_timezone_set(env('TIMEZONE', 'Africa/Algiers'));

// Hide errors in production; show only locally
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
}

/* ── HTTPS enforcement (fallback when .htaccess is disabled) ──── */
$isHttps =
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
    (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

if (!$isHttps && PHP_SAPI !== 'cli' && APP_ENV === 'production') {
    $host = $_SERVER['HTTP_HOST'] ?? parse_url(SITE_URL, PHP_URL_HOST);
    $uri  = $_SERVER['REQUEST_URI'] ?? '/';
    header('Location: https://' . $host . $uri, true, 301);
    exit;
}

/* ── Database (lazy singleton PDO) ─────────────────────────────── */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            logError('DB connection failed: ' . $e->getMessage());
            $msg = APP_DEBUG ? $e->getMessage() : 'Database connection error';
            die(json_encode(['success' => false, 'message' => $msg]));
        }
    }
    return $pdo;
}

/* ── Session lifecycle ─────────────────────────────────────────── */
function checkSession(): array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $authed = !empty($_SESSION['user_id']) || !empty($_SESSION['is_guest']);
    if (!$authed || empty($_SESSION['expires']) || $_SESSION['expires'] < time()) {
        session_destroy();
        header('Location: /login.php');
        exit;
    }
    $_SESSION['expires'] = time() + SESSION_DURATION;
    return $_SESSION;
}

function isAdmin(): bool { return ($_SESSION['role'] ?? '') === 'admin'; }
function isGuest(): bool { return !empty($_SESSION['is_guest']) || ($_SESSION['role'] ?? '') === 'guest'; }
function canWrite(): bool { return !isGuest(); }

/**
 * Cache-busting helper: appends ?v=<mtime> so browsers reload assets
 * when the file changes. Usage:
 *   <link rel="stylesheet" href="<?= assetUrl('/assets/css/style.css') ?>">
 */
function assetUrl(string $path): string {
    $full = __DIR__ . $path;
    $v = is_file($full) ? filemtime($full) : time();
    return $path . '?v=' . $v;
}

/* ── Brute-force protection ────────────────────────────────────── */
function checkBruteForce(string $username): bool {
    try {
        $db    = getDB();
        $ip    = getClientIP();
        $since = date('Y-m-d H:i:s', time() - LOCKOUT_DURATION);
        $stmt  = $db->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE (username = ? OR ip_address = ?)
               AND success = 0
               AND attempted_at > ?'
        );
        $stmt->execute([$username, $ip, $since]);
        return (int) $stmt->fetchColumn() >= MAX_LOGIN_ATTEMPTS;
    } catch (Throwable $e) {
        return false;
    }
}

function recordLoginAttempt(string $username, bool $success): void {
    try {
        getDB()
            ->prepare('INSERT INTO login_attempts (username, ip_address, success) VALUES (?, ?, ?)')
            ->execute([$username, getClientIP(), $success ? 1 : 0]);
    } catch (Throwable $e) {
        // silently ignore — this is best-effort logging
    }
}

/* ── CSRF protection ───────────────────────────────────────────── */
function generateCSRF(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRF(?string $token): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRF() . '">';
}

/* ── Audit log ─────────────────────────────────────────────────── */
function auditLog(string $action, ?string $table = null, ?int $recordId = null, ?string $details = null): void {
    try {
        $uid = $_SESSION['user_id'] ?? null;
        getDB()
            ->prepare('INSERT INTO audit_log (user_id, action, table_name, record_id, details, ip_address)
                       VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([$uid, $action, $table, $recordId, $details, getClientIP()]);
    } catch (Throwable $e) {
        // best-effort
    }
}

/* ── Error log (file-based, no PII beyond IP) ─────────────────── */
function logError(string $msg, array $ctx = []): void {
    $dir = __DIR__ . '/logs';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $line = sprintf(
        "[%s] %s%s | IP:%s\n",
        date('Y-m-d H:i:s'),
        $msg,
        $ctx ? ' | ' . json_encode($ctx, JSON_UNESCAPED_UNICODE) : '',
        getClientIP()
    );
    @file_put_contents($dir . '/errors.log', $line, FILE_APPEND | LOCK_EX);
}

/* ── Milestone notifications ───────────────────────────────────── */
function checkMilestones(int $total): void {
    if (!ADMIN_EMAIL) return;
    try {
        $db   = getDB();
        $stmt = $db->prepare(
            "SELECT threshold FROM notifications
             WHERE type = 'count_milestone' AND sent_at IS NULL AND threshold <= ?"
        );
        $stmt->execute([$total]);
        foreach ($stmt->fetchAll() as $m) {
            $pct  = round($total / TARGET_N * 100, 1);
            $subj = '[' . SITE_NAME . "] Milestone reached: {$m['threshold']} ({$pct}%)";
            $body = "Total: $total / " . TARGET_N . " ($pct%)\n" . SITE_URL;
            @mail(ADMIN_EMAIL, $subj, $body, 'From: noreply@' . parse_url(SITE_URL, PHP_URL_HOST));
            $db->prepare("UPDATE notifications SET sent_at = NOW() WHERE type = 'count_milestone' AND threshold = ?")
               ->execute([$m['threshold']]);
        }
    } catch (Throwable $e) {
        // best-effort
    }
}

/* ── Progress snapshot (used by progress.php) ─────────────────── */
function getProgress(): array {
    try {
        $db        = getDB();
        $n         = (int) $db->query('SELECT COUNT(*) FROM responses')->fetchColumn();
        $today     = (int) $db->query('SELECT COUNT(*) FROM responses WHERE DATE(entered_at) = CURDATE()')->fetchColumn();
        $firstDate = $db->query('SELECT MIN(DATE(entered_at)) FROM responses')->fetchColumn();
        $days      = $firstDate
            ? max(1, (int) $db->query("SELECT DATEDIFF(CURDATE(), '$firstDate') + 1")->fetchColumn())
            : 1;
        $rate      = (int) round($n / $days);
        $remaining = max(0, TARGET_N - $n);
        $eta       = $rate > 0 ? (int) ceil($remaining / $rate) : null;
        $pct       = round($n / TARGET_N * 100, 1);
        return compact('n', 'today', 'days', 'rate', 'remaining', 'eta', 'pct');
    } catch (Throwable $e) {
        return ['n' => 0, 'today' => 0, 'days' => 1, 'rate' => 0, 'remaining' => TARGET_N, 'eta' => null, 'pct' => 0];
    }
}

/* ── Utilities ─────────────────────────────────────────────────── */
function getClientIP(): string {
    foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) {
            return trim(explode(',', $_SERVER[$k])[0]);
        }
    }
    return '0.0.0.0';
}

function sanitize(?string $v): string {
    return htmlspecialchars(strip_tags(trim($v ?? '')), ENT_QUOTES, 'UTF-8');
}

function jsonResponse(array $data): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
