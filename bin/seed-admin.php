<?php
/**
 * ════════════════════════════════════════════════════════════════════
 *  bin/seed-admin.php — Seed or reset the administrator account
 *  ────────────────────────────────────────────────────────────────
 *  Use this CLI script instead of the default credentials shipped in
 *  database/seed.sql (which exist only for first-boot convenience).
 *
 *  Usage:
 *      php bin/seed-admin.php <username> <password> [full_name]
 *
 *  Examples:
 *      php bin/seed-admin.php admin 'S3cur3!Passw0rd'
 *      php bin/seed-admin.php elhadj 'My$tr0ng#PW' 'TOUIL Elhadj'
 *
 *  Behaviour:
 *      • If the user exists → its password and full_name are updated.
 *      • If the user does not exist → it is created with role=admin.
 *      • Passwords shorter than 12 characters are rejected.
 *      • Echoes the bcrypt hash to stdout for verification.
 * ════════════════════════════════════════════════════════════════════
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die("This script is CLI-only.\n");
}

if ($argc < 3) {
    fwrite(STDERR, "Usage: php bin/seed-admin.php <username> <password> [full_name]\n");
    exit(1);
}

$username = trim($argv[1]);
$password = $argv[2];
$fullName = $argv[3] ?? 'Administrator';

if ($username === '' || strlen($username) > 50) {
    fwrite(STDERR, "Error: username must be 1–50 characters.\n");
    exit(1);
}

if (strlen($password) < 12) {
    fwrite(STDERR, "Error: password must be at least 12 characters.\n");
    exit(1);
}

require_once __DIR__ . '/../config.php';

$db   = getDB();
$hash = password_hash($password, PASSWORD_BCRYPT);

$existing = $db->prepare('SELECT id FROM users WHERE username = ?');
$existing->execute([$username]);
$row = $existing->fetch();

if ($row) {
    $db->prepare('UPDATE users SET password_hash = ?, full_name = ?, role = ? WHERE id = ?')
       ->execute([$hash, $fullName, 'admin', $row['id']]);
    printf("[ok] User '%s' updated (id=%d).\n", $username, $row['id']);
} else {
    $db->prepare('INSERT INTO users (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)')
       ->execute([$username, $hash, $fullName, 'admin']);
    printf("[ok] User '%s' created (id=%d).\n", $username, (int) $db->lastInsertId());
}

printf("    hash:     %s\n", $hash);
printf("    note:     keep this password in a safe place; it cannot be recovered.\n");
exit(0);
