#!/bin/bash
# ================================================
# Sync production → staging database
# PURE mysqldump/mysql version (no wp-cli db)
# ================================================

# ───── 1. LOG SETUP ─────
LOGDIR="/home/u726942614/domains/staging.drankbestel.nl/logs"
LOGFILE="$LOGDIR/db-sync.log"
mkdir -p "$LOGDIR"
chmod 755 "$LOGDIR"
exec > "$LOGFILE" 2>&1

echo "============================================================"
echo "DB SYNC STARTED — $(date '+%Y-%m-%d %H:%M:%S')"
echo "Running as: $USER | PWD: $PWD"
echo "============================================================"

# ───── 2. PATH FIX ─────
export PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:$PATH"

# ───── 3. SETTINGS ─────
PROD_PATH="/home/u726942614/domains/drankbestel.nl/public_html"
STAGING_PATH="/home/u726942614/domains/staging.drankbestel.nl/public_html"
PROD_URL="https://drankbestel.nl"
STAGING_URL="https://staging.drankbestel.nl"
EXPORT_PATH="/tmp/prod_db_$(date +%s).sql"
WP_CLI=$(which wp || echo "/usr/local/bin/wp")

# ───── 4. READ PROD DB CREDS FROM wp-config.php ─────
cd "$PROD_PATH" || { echo "Production path not found!"; exit 1; }

DB_NAME=$(grep "DB_NAME" wp-config.php | cut -d "'" -f4)
DB_USER=$(grep "DB_USER" wp-config.php | cut -d "'" -f4)
DB_PASS=$(grep "DB_PASSWORD" wp-config.php | cut -d "'" -f4)
DB_HOST=$(grep "DB_HOST" wp-config.php | cut -d "'" -f4)

echo "PROD DB: $DB_NAME  USER: $DB_USER  HOST: $DB_HOST"

# ───── 5. EXPORT FULL DB (every table, every row) ─────
echo "[$(date)] Dumping production DB..."
mysqldump \
  --single-transaction \
  --routines \
  --triggers \
  --quick \
  --lock-tables=false \
  -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" \
  "$DB_NAME" > "$EXPORT_PATH"

if [ $? -ne 0 ] || [ ! -s "$EXPORT_PATH" ]; then
  echo "mysqldump FAILED"
  rm -f "$EXPORT_PATH"
  exit 1
fi
echo "Exported $(du -h "$EXPORT_PATH" | cut -f1)"

# ───── 6. READ STAGING DB CREDS & DROP/RECREATE ─────
cd "$STAGING_PATH" || { echo "Staging path not found!"; exit 1; }

STAGING_DB_NAME=$(grep "DB_NAME" wp-config.php | cut -d "'" -f4)
STAGING_DB_USER=$(grep "DB_USER" wp-config.php | cut -d "'" -f4)
STAGING_DB_PASS=$(grep "DB_PASSWORD" wp-config.php | cut -d "'" -f4)
STAGING_DB_HOST=$(grep "DB_HOST" wp-config.php | cut -d "'" -f4)

echo "STAGING DB: $STAGING_DB_NAME  USER: $STAGING_DB_USER  HOST: $STAGING_DB_HOST"
echo "Dropping & recreating staging DB ($STAGING_DB_NAME)..."
mysql -h"$STAGING_DB_HOST" -u"$STAGING_DB_USER" -p"$STAGING_DB_PASS" -e "DROP DATABASE IF EXISTS \`$STAGING_DB_NAME\`; CREATE DATABASE \`$STAGING_DB_NAME\`;"

# ───── 7. IMPORT FULL DUMP ─────
echo "Importing dump into staging..."
mysql -h"$STAGING_DB_HOST" -u"$STAGING_DB_USER" -p"$STAGING_DB_PASS" "$STAGING_DB_NAME" < "$EXPORT_PATH"

if [ $? -ne 0 ]; then
  echo "mysql import FAILED"
  rm -f "$EXPORT_PATH"
  exit 1
fi
echo "Imported successfully"

# ───── 8. URL REPLACE ─────
echo "Replacing URLs..."
cd "$STAGING_PATH"
$WP_CLI search-replace "$PROD_URL" "$STAGING_URL" --all-tables --skip-columns=guid --allow-root --precise --quiet --path="$STAGING_PATH"
echo "URLs replaced"

# ───── 9. CLEANUP ─────
rm -f "$EXPORT_PATH"
$WP_CLI cache flush --allow-root --path="$STAGING_PATH" 2>/dev/null || true
echo "Cache flushed, temp file removed"

# ───── 10. DONE ─────
echo "============================================================"
echo "SYNC COMPLETED SUCCESSFULLY — $(date '+%Y-%m-%d %H:%M:%S')"
echo "============================================================"
