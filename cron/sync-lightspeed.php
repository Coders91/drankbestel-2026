<?php

/**
 * Lightspeed Stock Sync Cron Script
 *
 * This script is designed to be called directly by cron to sync product stock
 * between Lightspeed Retail and WooCommerce.
 *
 * Usage:
 *   php /path/to/theme/cron/sync-lightspeed.php [options]
 *
 * Options:
 *   --type=<strategy>    Sync strategy (default: stock)
 *   --refresh-cache      Force cache refresh before sync
 *   --dry-run           Show what would be synced without making changes
 *   --limit=<n>          Maximum number of products to sync
 *   --secret=<key>       Security key (required, must match LIGHTSPEED_CRON_SECRET env var)
 *
 * Cron example:
 *   * /15 * * * * php /var/www/html/wp-content/themes/drankbestel-new/cron/sync-lightspeed.php --secret=your-secret >> /var/log/lightspeed-sync.log 2>&1
 */

// Ensure we're running from CLI
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('This script can only be run from the command line.');
}

// Parse command line arguments
$options = getopt('', [
    'type::',
    'refresh-cache',
    'dry-run',
    'limit::',
    'secret::',
    'help',
]);

// Show help
if (isset($options['help'])) {
    echo <<<HELP
Lightspeed Stock Sync

Usage: php sync-lightspeed.php [options]

Options:
  --type=<strategy>    Sync strategy: stock (default)
  --refresh-cache      Force cache refresh before sync
  --dry-run            Show what would be synced without making changes
  --limit=<n>          Maximum number of products to sync (0 = unlimited)
  --secret=<key>       Security key (must match LIGHTSPEED_CRON_SECRET)
  --help               Show this help message

HELP;
    exit(0);
}

// Log function
function logMessage(string $message): void
{
    $timestamp = date('Y-m-d H:i:s');
    echo "[{$timestamp}] {$message}\n";
}

logMessage('Starting Lightspeed sync...');

// Find WordPress root
$wpLoadPath = dirname(__DIR__, 4) . '/wp-load.php';

if (! file_exists($wpLoadPath)) {
    logMessage('ERROR: Could not find wp-load.php at: ' . $wpLoadPath);
    exit(1);
}

// Bootstrap WordPress
logMessage('Loading WordPress...');
define('WP_USE_THEMES', false);
require_once $wpLoadPath;

// Verify secret key
$providedSecret = $options['secret'] ?? '';
$configuredSecret = config('lightspeed.cron_secret', '');

if (empty($configuredSecret)) {
    logMessage('WARNING: No LIGHTSPEED_CRON_SECRET configured. Please set this in your .env file.');
} elseif ($providedSecret !== $configuredSecret) {
    logMessage('ERROR: Invalid or missing secret key.');
    exit(1);
}

// Ensure WooCommerce is loaded
if (! function_exists('WC')) {
    logMessage('ERROR: WooCommerce is not active.');
    exit(1);
}

// Get the sync service
try {
    $syncService = app(\App\Services\Lightspeed\LightspeedSyncService::class);
} catch (\Exception $e) {
    logMessage('ERROR: Could not initialize sync service: ' . $e->getMessage());
    exit(1);
}

// Parse options
$strategyName = $options['type'] ?? 'stock';
$refreshCache = isset($options['refresh-cache']);
$dryRun = isset($options['dry-run']);
$limit = isset($options['limit']) ? (int) $options['limit'] : 0;

// Validate strategy
$availableStrategies = $syncService->getAvailableStrategies();

if (! in_array($strategyName, $availableStrategies)) {
    logMessage('ERROR: Unknown strategy "' . $strategyName . '". Available: ' . implode(', ', $availableStrategies));
    exit(1);
}

logMessage("Strategy: {$strategyName}");
logMessage("Refresh cache: " . ($refreshCache ? 'yes' : 'no'));
logMessage("Dry run: " . ($dryRun ? 'yes' : 'no'));
logMessage("Limit: " . ($limit > 0 ? $limit : 'unlimited'));

// Run the sync
try {
    $result = $syncService->sync(
        strategyName: $strategyName,
        refreshCache: $refreshCache,
        dryRun: $dryRun,
        limit: $limit,
        progressCallback: function (string $message) {
            logMessage($message);
        }
    );

    logMessage('');
    logMessage('=== SYNC COMPLETE ===');
    logMessage('Processed: ' . $result->processed);
    logMessage('Updated: ' . $result->updated);
    logMessage('Skipped: ' . $result->skipped);
    logMessage('Errors: ' . $result->errors);

    if (! empty($result->errorMessages)) {
        logMessage('');
        logMessage('Error details:');
        foreach ($result->errorMessages as $error) {
            logMessage('  - ' . $error);
        }
    }

    exit($result->isSuccessful() ? 0 : 1);
} catch (\Exception $e) {
    logMessage('FATAL ERROR: ' . $e->getMessage());
    logMessage('Stack trace: ' . $e->getTraceAsString());
    exit(1);
}
