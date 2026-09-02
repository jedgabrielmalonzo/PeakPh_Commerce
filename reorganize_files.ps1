# PeakPH Commerce - File Reorganization Script
# This script moves test/debug/archive files to organized folders

Write-Host "================================" -ForegroundColor Cyan
Write-Host "PeakPH Commerce File Cleanup" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

$rootPath = "C:\xampp\htdocs\PeakPH_Commerce"

# Ensure we're in the right directory
Set-Location $rootPath

# Create directories if they don't exist
$directories = @("_archive", "_dev-tools", "api")
foreach ($dir in $directories) {
    if (!(Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
        Write-Host "[✓] Created directory: $dir" -ForegroundColor Green
    } else {
        Write-Host "[→] Directory exists: $dir" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "Moving files..." -ForegroundColor Cyan
Write-Host ""

# Files to move to _dev-tools
$devToolsFiles = @(
    "test_checkout.php",
    "test_complete_flow.php",
    "test_gcash_web.php",
    "test_integration.html",
    "test_orders.php",
    "test_payment.php",
    "test_payment_manual.php",
    "test_paymongo.php",
    "test_store.php",
    "debug_paymongo.php",
    "debug_payment_detailed.php",
    "debug_payment_detailed2.php",
    "check_database.php",
    "check_orders_table.php",
    "check_payments_table.php",
    "create_paymongo_tables.php",
    "setup_database.php",
    "setup_paymongo_db.php",
    "fix_database.php",
    "export_database_schema.php",
    "update_database_schema.php"
)

Write-Host "→ Moving development/test files to _dev-tools/" -ForegroundColor Yellow
$movedCount = 0
foreach ($file in $devToolsFiles) {
    if (Test-Path $file) {
        Move-Item -Path $file -Destination "_dev-tools\$file" -Force
        Write-Host "  [✓] Moved: $file" -ForegroundColor Green
        $movedCount++
    }
}
Write-Host "  Moved $movedCount dev/test files" -ForegroundColor Cyan
Write-Host ""

# Files to move to _archive
$archiveFiles = @(
    "profile_backup.php",
    "profile_new.php",
    "complete_database_schema.sql",
    "database_setup_complete.sql",
    "database_update_order_items.sql",
    "create_user_profiles_table.sql"
)

Write-Host "→ Moving backup/old files to _archive/" -ForegroundColor Yellow
$movedCount = 0
foreach ($file in $archiveFiles) {
    if (Test-Path $file) {
        Move-Item -Path $file -Destination "_archive\$file" -Force
        Write-Host "  [✓] Moved: $file" -ForegroundColor Green
        $movedCount++
    }
}
Write-Host "  Moved $movedCount archive files" -ForegroundColor Cyan
Write-Host ""

# Files to move to api
$apiFiles = @(
    "save_profile.php",
    "process_checkout.php",
    "add_to_cart.php",
    "get_product.php"
)

Write-Host "→ Moving API endpoints to api/" -ForegroundColor Yellow
$movedCount = 0
foreach ($file in $apiFiles) {
    if (Test-Path $file) {
        Copy-Item -Path $file -Destination "api\$file" -Force
        Write-Host "  [✓] Copied: $file (keeping original for now)" -ForegroundColor Green
        $movedCount++
    }
}
Write-Host "  Copied $movedCount API files" -ForegroundColor Cyan
Write-Host ""

Write-Host "================================" -ForegroundColor Cyan
Write-Host "File reorganization complete!" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Update file paths in PHP files (profile.php, checkout.php, etc.)" -ForegroundColor White
Write-Host "2. Test all user-side functionality" -ForegroundColor White
Write-Host "3. Test all admin-side functionality" -ForegroundColor White
Write-Host "4. If everything works, delete originals from root" -ForegroundColor White
Write-Host ""
Write-Host "Files have been organized into:" -ForegroundColor Cyan
Write-Host "  • _dev-tools/  - Test and debug files" -ForegroundColor White
Write-Host "  • _archive/    - Backup and old files" -ForegroundColor White
Write-Host "  • api/         - API endpoints (copied)" -ForegroundColor White
Write-Host ""
