# Archive Folder

This folder contains backup files and old versions that are no longer in active use.

## Files in this Folder

### Backup Files
- `profile_backup.php` - Old profile page backup
- `profile_new.php` - Alternate profile implementation

### SQL Migration Files
- `create_paymongo_tables.php` - PayMongo tables creation (use database_setup.sql instead)
- `create_user_profiles_table.sql` - User profiles migration (included in database_setup.sql)
- `complete_database_schema.sql` - Old complete schema
- `database_setup_complete.sql` - Alternate database setup
- `database_update_order_items.sql` - Order items update migration

## Purpose

These files are kept for:
- Historical reference
- Rollback capability
- Comparing old vs new implementations
- Learning from previous iterations

## Current Active Files

**Use these instead:**
- Main database setup: `database_setup.sql` (in root)
- Current profile page: `profile.php` (in root)

## Restoration

If you need to restore an old version:
1. Copy the file from `_archive/`
2. Rename appropriately
3. Test thoroughly before using

## Cleanup

These files can be safely deleted if:
- Current implementation is stable
- You have Git history as backup
- No need for historical reference
