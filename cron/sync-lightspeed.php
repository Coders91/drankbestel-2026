<?php

/**
 * Lightspeed Stock Sync Cron Script
 *
 * Calls `wp acorn lightspeed:sync` via shell.
 * Supports both CLI and HTTP (with secret key) access.
 *
 * CLI usage:
 *   php /path/to/cron/sync-lightspeed.php
 *
 * HTTP usage (for Hostinger cron):
 *   wget -qO /dev/null https://drankbestel.nl/wp-content/themes/drankbestel-new/cron/sync-lightspeed.php?secret=your-secret
 *
 * Hostinger cron command:
 *   php /home/u123/domains/drankbestel.nl/public_html/wp-content/themes/drankbestel-new/cron/sync-lightspeed.php
 */

$isCli = php_sapi_name() === 'cli';

// HTTP requests require a secret key
if (! $isCli) {
    // Load WordPress just enough to read the .env config
    $wpLoadPath = dirname(__DIR__, 4) . '/wp-load.php';

    if (! file_exists($wpLoadPath)) {
        http_response_code(500);
        die('WordPress not found.');
    }

    define('WP_USE_THEMES', false);
    require_once $wpLoadPath;

    $secret = $_GET['secret'] ?? '';
    $configuredSecret = config('lightspeed.cron_secret', '');

    if (empty($configuredSecret) || $secret !== $configuredSecret) {
        http_response_code(403);
        die('Unauthorized.');
    }
}

// Find wp binary
$wpBinary = trim(shell_exec('which wp 2>/dev/null') ?? '');

if (empty($wpBinary)) {
    $wpBinary = '/usr/local/bin/wp';
}

// Find WordPress root
$wpRoot = dirname(__DIR__, 4);
$command = sprintf(
    'cd %s && %s acorn lightspeed:sync --refresh-cache 2>&1',
    escapeshellarg($wpRoot),
    escapeshellarg($wpBinary)
);

$output = shell_exec($command);

if ($isCli) {
    echo $output;
} else {
    header('Content-Type: text/plain');
    echo $output;
}
