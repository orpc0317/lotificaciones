# Project Cleanup Plan
**Date:** October 17, 2025

## 🗑️ Files/Folders to DELETE (Safe to Remove)

### 1. Test Files in `/public/` (Testing/Debug)
- ❌ `public/debug-test.php` - Temporary debug page created during Edit button fix
- ❌ `public/test-emp-data.php` - Temporary test endpoint for employee data
- ❌ `public/security-test.php` - Security testing file
- ❌ `public/validation-test.php` - Validation testing file

### 2. Downloaded Debug Logs (31 files)
- ❌ `downloaded/ci-logs-unpacked/` - 31 curl probe attempt files (debugging artifacts)
- ❌ `downloaded/ci-logs.zip` - Original zip file

### 3. Backup JavaScript Files
- ❌ `public/assets/js/empleados.js.backup` - Original monolithic file (already refactored)
- ❌ `public/assets/js/empleados.js.tmp` - Temporary file

### 4. Test Screenshots
- ❌ `test-results/screenshots/` - Old UI screenshots (if not needed for documentation)
- ❌ `test-results/.last-run.json` - Playwright test metadata

### 5. Migration Scripts (Already Applied)
- ⚠️ `scripts/add_contact_address_fields.sql` - Already applied to DB
- ⚠️ `scripts/migrate_add_fields.php` - Already applied to DB
- ⚠️ `scripts/check_namespace_case.php` - One-time check script

### 6. Storage Logs (Development)
- ⚠️ `storage/logs/pr1.html` - Old debug output

---

## ✅ Files/Folders to KEEP (Required)

### Core Application Files
- ✅ All files in `app/` (Controllers, Models, Views, Security, Helpers)
- ✅ All files in `config/` (Configuration)
- ✅ All files in `routes/` (Routing)
- ✅ `public/index.php` - Main entry point
- ✅ `public/.htaccess` - URL rewriting
- ✅ `public/assets/` - CSS, JS, images (except backups)
- ✅ `public/uploads/` - User uploads

### JavaScript Modules (Active)
- ✅ `public/assets/js/empleados.js` - Main coordinator (refactored)
- ✅ `public/assets/js/constants.js` - Application constants
- ✅ `public/assets/js/layout.js` - Layout management
- ✅ `public/assets/js/theme.js` - Theme management
- ✅ `public/assets/js/modules/*.js` - All 6 modules (dataTable, errorHandler, etc.)

### Dependencies
- ✅ `vendor/` - Composer dependencies
- ✅ `node_modules/` - NPM dependencies
- ✅ `composer.json` & `composer.lock`
- ✅ `package.json` & `package-lock.json`

### Documentation (Keep for Reference)
- ✅ `README.md` - Main documentation
- ✅ `README-*.md` - Feature documentation (11 files)
- ✅ `QA-ANALYSIS-REPORT.md` - Quality analysis
- ✅ `SECURITY-*.md` - Security documentation
- ✅ `VALIDATION-IMPLEMENTATION.md` - Validation docs
- ✅ `FIX-EDIT-BUTTON-NAVIGATION.md` - Recent fix documentation

### Testing (Keep for Future Tests)
- ✅ `tests/empleados.spec.js` - Playwright E2E tests
- ✅ `tests/theme.spec.js` - Theme tests
- ✅ `tests/playwright.config.js` - Test configuration

### Utility Scripts (Keep for Future Use)
- ✅ `scripts/sample_data.sql` - Sample data for testing
- ✅ `scripts/setup_db.php` - Database setup
- ✅ `scripts/capture_ui_screenshots.js` - Screenshot automation
- ✅ `scripts/check_tab_badges.js` - Badge checking

### Git & Config
- ✅ `.git/` - Git repository
- ✅ `.gitignore` - Git ignore rules

---

## 📊 Cleanup Summary

### Total Files to Delete: ~40 files
- 4 test PHP files in `/public/`
- 31 curl probe logs in `/downloaded/ci-logs-unpacked/`
- 1 zip file in `/downloaded/`
- 2 backup/temp JS files
- Test screenshots (count TBD)
- 2-3 old migration scripts
- 1 storage log file

### Estimated Space Saved: ~5-10 MB

### Risk Level: ⚠️ LOW
All files marked for deletion are:
- Temporary test files
- Debug artifacts
- Already-applied migrations
- Backup files (original code preserved in git history)

---

## 🚀 Cleanup Commands

```powershell
# Navigate to project root
cd c:\xampp\htdocs\lotificaciones

# 1. Remove test files from public/
Remove-Item public/debug-test.php -Force
Remove-Item public/test-emp-data.php -Force
Remove-Item public/security-test.php -Force
Remove-Item public/validation-test.php -Force

# 2. Remove downloaded debug logs
Remove-Item downloaded/ci-logs-unpacked -Recurse -Force
Remove-Item downloaded/ci-logs.zip -Force

# 3. Remove backup/temp JavaScript files
Remove-Item public/assets/js/empleados.js.backup -Force
Remove-Item public/assets/js/empleados.js.tmp -Force

# 4. Remove test results (optional - keep if needed for reference)
# Remove-Item test-results/screenshots -Recurse -Force
# Remove-Item test-results/.last-run.json -Force

# 5. Remove old migration scripts (optional - keep for reference)
# Remove-Item scripts/add_contact_address_fields.sql -Force
# Remove-Item scripts/migrate_add_fields.php -Force
# Remove-Item scripts/check_namespace_case.php -Force

# 6. Remove old storage logs
Remove-Item storage/logs/pr1.html -Force

# 7. Clean up entire downloaded folder if empty
Remove-Item downloaded -Recurse -Force

# 8. Verify cleanup
Get-ChildItem public/*.php | Where-Object {$_.Name -like '*test*'}
Get-ChildItem public/assets/js/*.backup
Get-ChildItem public/assets/js/*.tmp
```

---

## ✅ Post-Cleanup Verification

After cleanup, verify the application still works:

1. **Test DataTable:** Navigate to employee list page
2. **Test View:** Open employee detail page
3. **Test Edit:** Edit an employee record
4. **Test Delete:** Delete an employee (cancel before confirming)
5. **Test Theme:** Switch between light/dark themes
6. **Test Language:** Switch languages
7. **Check Console:** No JavaScript errors

---

## 🔄 Rollback Plan

If any issues arise after cleanup:

1. **Git History:** All deleted code is in git history
   ```powershell
   git log --all --full-history -- "path/to/deleted/file"
   git checkout <commit> -- "path/to/deleted/file"
   ```

2. **Backup Location:** empleados.js.backup contains original monolithic code
   - Can restore if modular version has issues

3. **Migration Scripts:** Can be restored from git if needed to re-run

---

## 📝 Notes

- **Git Clean:** Consider running `git clean -fd -n` (dry run) to see untracked files
- **Node Modules:** Keep for dependencies (can reinstall with `npm install`)
- **Vendor:** Keep for Composer dependencies (can reinstall with `composer install`)
- **.env:** Keep if exists (environment configuration)
- **Database Backups:** Not in this cleanup scope

---

## ⏱️ Estimated Time: 5 minutes
## 🎯 Impact: Cleaner project structure, easier navigation, reduced confusion
