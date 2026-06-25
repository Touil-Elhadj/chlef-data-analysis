<?php
/**
 * ════════════════════════════════════════════════════════════════════
 *  bin/backup.php — Daily backup utility
 *  ────────────────────────────────────────────────────────────────
 *  Generates a UTF-8 CSV dump of the `responses` table, writes it to
 *  `backups/` next to this script, and optionally emails it to the
 *  address configured in $ADMIN_EMAIL.
 *
 *  Usage:
 *      php bin/backup.php
 *
 *  Suggested cron entry (daily at 23:00):
 *      0 23 * * *  php /path/to/chlef-touilelhadj/bin/backup.php >> /var/log/backup.log 2>&1
 *
 *  Retention: files older than RETENTION_DAYS (default: 30) are
 *  automatically pruned.
 * ════════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/../config.php';

const RETENTION_DAYS = 30;

// Restrict execution: CLI only, or admin via web (for debugging)
$isCLI = (PHP_SAPI === 'cli');
if (!$isCLI) {
    @session_start();
    if (!isAdmin()) {
        http_response_code(403);
        die('Forbidden — this endpoint is CLI-only or admin-only.');
    }
}

$db   = getDB();
$date = date('Y-m-d');
$n    = (int) $db->query('SELECT COUNT(*) FROM responses')->fetchColumn();

// ── Build CSV in memory ────────────────────────────────────────────
$rows = $db->query('SELECT * FROM responses ORDER BY questionnaire_num')->fetchAll();

ob_start();
$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));   // UTF-8 BOM (Excel-friendly)
if ($rows) {
    fputcsv($out, array_keys($rows[0]));
    foreach ($rows as $row) {
        fputcsv($out, $row);
    }
}
fclose($out);
$csv = ob_get_clean();

// ── Write to disk ──────────────────────────────────────────────────
$backupDir = dirname(__DIR__) . '/backups';
if (!is_dir($backupDir)) {
    @mkdir($backupDir, 0700, true);  // 0700: backups may contain PII
}

$filename = "backup_{$date}_n{$n}.csv";
$filepath = "$backupDir/$filename";
file_put_contents($filepath, $csv);
@chmod($filepath, 0600);

// ── Prune old files ────────────────────────────────────────────────
$cutoff = time() - RETENTION_DAYS * 86400;
foreach (glob($backupDir . '/backup_*.csv') as $f) {
    if (filemtime($f) < $cutoff) {
        @unlink($f);
    }
}

// ── Optional e-mail to administrator ──────────────────────────────
$sent = false;
if (ADMIN_EMAIL && $rows) {
    $host     = parse_url(SITE_URL, PHP_URL_HOST) ?: 'localhost';
    $boundary = bin2hex(random_bytes(8));
    $headers  = "From: backup@$host\r\n"
              . "MIME-Version: 1.0\r\n"
              . "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

    $pct = TARGET_N > 0 ? round($n / TARGET_N * 100, 1) : 0.0;

    $body  = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $body .= "Automated backup — $date\n";
    $body .= "Records: $n / " . TARGET_N . " ($pct %)\n";
    $body .= "Attached: $filename (" . strlen($csv) . " bytes)\n";
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: text/csv; charset=UTF-8\r\n";
    $body .= "Content-Disposition: attachment; filename=\"$filename\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= chunk_split(base64_encode($csv)) . "\r\n";
    $body .= "--$boundary--";

    $sent = @mail(ADMIN_EMAIL, '[' . SITE_NAME . "] Backup $date — $n records", $body, $headers);
}

// ── Audit + output ────────────────────────────────────────────────
auditLog('EXPORT', 'responses', null, "Auto-backup: $n rows, email:" . ($sent ? 'yes' : 'no'));

$result = [
    'success' => true,
    'date'    => $date,
    'records' => $n,
    'file'    => $filename,
    'path'    => $filepath,
    'size_kb' => round(strlen($csv) / 1024, 1),
    'email'   => $sent ? 'sent' : (ADMIN_EMAIL ? 'failed' : 'not configured'),
];

if ($isCLI) {
    printf(
        "[backup] %s — %d records — %s — email: %s\n",
        date('Y-m-d H:i:s'),
        $n,
        $filename,
        $result['email']
    );
    exit(0);
}

jsonResponse($result);
