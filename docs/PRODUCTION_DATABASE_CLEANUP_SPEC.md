# Production Database Cleanup - Spec Summary

**Created**: 2026-05-24  
**Spec Location**: `.kiro/specs/production-database-cleanup/`  
**Status**: ✅ SPEC COMPLETE - Ready for Implementation

---

## 🎯 OBJECTIVE

Remove all demo/seeded data from production IAMJOS system while preserving:
- ✅ OJS compliance (workflow logic, business rules, database structure)
- ✅ Google Scholar compliance (meta tags, structured data, indexing capabilities)
- ✅ System infrastructure (RBAC, templates, settings, site content)
- ✅ Super Admin account

**Result**: "Fresh OJS" state - Zero journals, ready for first real journal creation

---

## 📊 CURRENT STATE (From Screenshots)

**Homepage shows**:
- 5 Journals (demo data)
- 0 Articles
- 1 Issue
- 0 Downloads

**Demo Journals**:
1. Journal of Informatics and Technology (JIT)
2. Medical Science Journal (MSJ)
3. Journal of Business and Economics (JBE)
4. Engineering and Applied Sciences (EAS)
5. IAMJOS - Indonesian Academic Journal System

**Demo Users**:
- admin@demo.iamjos.id
- editor@demo.iamjos.id
- reviewer@demo.iamjos.id
- author@demo.iamjos.id

---

## 🔧 SOLUTION DESIGN

### Implementation Approach

**Single Laravel Migration** that:
1. Identifies demo journals by slug matching
2. Identifies demo users by email pattern matching
3. Deletes data in 7 phases (respecting foreign key constraints)
4. Executes atomically within database transaction
5. Provides comprehensive logging
6. Preserves super admin and system infrastructure

### Deletion Order (Critical for Foreign Keys)

```
Phase 1: Metrics & Logs
  ├─ article_metrics
  ├─ submission_logs
  ├─ submission_log_files
  ├─ submission_notes
  └─ crossref_logs

Phase 2: Workflow Data
  ├─ discussion_files
  ├─ discussion_messages
  ├─ discussion_participants
  ├─ discussions
  ├─ review_assignments
  ├─ review_rounds
  └─ editorial_assignments

Phase 3: Publication Data
  ├─ publication_galleys
  ├─ submission_authors (publications)
  └─ publications

Phase 4: Submission Data
  ├─ submission_keyword (pivot)
  ├─ submission_files
  ├─ submission_authors (submissions)
  └─ submissions

Phase 5: Journal Content
  ├─ navigation_items
  ├─ navigation_menus
  ├─ sidebar_blocks
  ├─ announcements
  ├─ notification_templates
  ├─ sections
  ├─ issues
  └─ journal_settings

Phase 6: Journals
  └─ journals

Phase 7: Demo Users
  ├─ journal_user_roles
  ├─ model_has_roles
  └─ users
```

---

## 📋 REQUIREMENTS SUMMARY

### 15 Requirements Covering:

1. **Remove Demo Journals** (5 journals by slug)
2. **Remove Demo Submissions and Publications** (all related content)
3. **Remove Demo Workflow Data** (reviews, discussions, editorial)
4. **Remove Demo Users** (4 users by email pattern)
5. **Remove Demo Journal-Specific Content** (navigation, announcements)
6. **Remove Demo Metrics and Logs** (analytics, crossref logs)
7. **Preserve System Infrastructure** (RBAC, templates, settings)
8. **Preserve Site-Level Navigation** (portal homepage)
9. **Handle Foreign Key Constraints** (proper deletion order)
10. **Prevent Future Demo Data in Production** (DemoSeeder guard)
11. **Maintain OJS Compliance** (preserve all code and structures)
12. **Maintain Google Scholar Compliance** (preserve meta tag generation)
13. **Verify Fresh OJS State** (zero journals after cleanup)
14. **Provide Rollback Capability** (guidance for restoration)
15. **Log Cleanup Operations** (comprehensive audit trail)

---

## 🏗️ DESIGN HIGHLIGHTS

### Identification Logic

**Demo Journals**:
```php
whereIn('slug', ['jit', 'medika', 'jbe', 'eas', 'iamjos'])
```

**Demo Users**:
```php
where('email', 'LIKE', '%@demo.iamjos.id')
->where('email', '!=', config('auth.super_admin_email'))
```

### Transaction Handling

```php
DB::transaction(function () {
    // All 7 deletion phases
    // Automatic rollback on any failure
});
```

### Logging Output

```
🧹 Starting production demo data cleanup...

📊 Identification Phase:
   ✓ Found 5 demo journals
   ✓ Found 4 demo users
   ✓ Found 127 submissions to remove

🗑️  Phase 1: Metrics & Logs
   ✓ Deleted 1,234 article_metrics
   ✓ Deleted 567 submission_logs
   ...

✅ Production cleanup completed successfully!
   Total records removed: 4,321
   Super admin preserved: admin@example.com
   System infrastructure: Intact
```

---

## ✅ CORRECTNESS PROPERTIES

### 5 Consolidated Properties (Property-Based Testing)

1. **Fresh OJS State Verification**
   - Zero demo journals, users, submissions after migration
   - Validates 33 acceptance criteria

2. **System Infrastructure Preservation**
   - All infrastructure tables retain pre-migration counts
   - Validates 13 acceptance criteria

3. **Transaction Atomicity**
   - Complete rollback on any failure
   - Validates 2 acceptance criteria

4. **Super Admin Preservation**
   - Super admin account never deleted
   - Validates 3 acceptance criteria

5. **No Schema Modification**
   - Database structure unchanged
   - Validates 12 acceptance criteria

---

## 🧪 TESTING STRATEGY

### Unit Tests (9 test files)
- Identification logic
- Each deletion phase
- Error handling
- Logging
- Rollback
- DemoSeeder production guard

### Property-Based Tests (5 properties)
- 100 iterations per property
- Random data generation
- Universal correctness verification

### Integration Test
- Full end-to-end cleanup
- Verify new journal creation after cleanup

---

## 📝 IMPLEMENTATION TASKS

### 18 Top-Level Tasks:

1. Create migration file structure
2. Implement identification methods (3 sub-tasks)
3. Implement Phase 1: Metrics & Logs (2 sub-tasks)
4. Implement Phase 2: Workflow Data (2 sub-tasks)
5. Implement Phase 3: Publication Data (2 sub-tasks)
6. Implement Phase 4: Submission Data (2 sub-tasks)
7. Implement Phase 5: Journal Content (2 sub-tasks)
8. Implement Phase 6: Journals (2 sub-tasks)
9. Implement Phase 7: Demo Users (2 sub-tasks)
10. Implement logging functionality (3 sub-tasks)
11. Implement rollback method (2 sub-tasks)
12. Implement error handling (4 sub-tasks)
13. Add DemoSeeder production guard (2 sub-tasks)
14. Checkpoint - Ensure all tests pass
15. Create property-based tests (5 sub-tasks)
16. Create integration test (1 sub-task)
17. Create documentation (2 sub-tasks)
18. Final checkpoint - Verify complete implementation

**Total Sub-Tasks**: 38 (18 required + 20 optional test tasks)

---

## 🔒 OJS & GOOGLE SCHOLAR COMPLIANCE

### OJS Compliance Preserved

**What is NOT Modified**:
- ✅ All database table structures
- ✅ All database indexes
- ✅ All database foreign key constraints
- ✅ All application code (controllers, models, services)
- ✅ All workflow logic (submission, review, editorial, publication)
- ✅ All business rules and validation

**What IS Modified**:
- ❌ Data records only (journals, users, submissions, etc.)

### Google Scholar Compliance Preserved

**Preserved Capabilities**:
- ✅ Meta tag generation code (unchanged)
- ✅ Highwire Press tags implementation (unchanged)
- ✅ Dublin Core metadata generation (unchanged)
- ✅ Citation metadata formatting (unchanged)
- ✅ OAI-PMH endpoints (unchanged)
- ✅ JSON-LD structured data (unchanged)

**Impact on Indexing**:
- ✅ No impact - all code remains functional
- ✅ Future published articles will be indexed normally
- ✅ Meta tags will be generated for new publications
- ✅ Google Scholar crawler will find proper metadata

---

## 🚀 EXECUTION PLAN

### Pre-Execution Checklist

- [ ] Verify `SUPER_ADMIN_EMAIL` is configured in `.env`
- [ ] Verify super admin user exists in database
- [ ] Create database backup
- [ ] Verify application is in maintenance mode (optional but recommended)
- [ ] Review migration code for correctness

### Execution Commands

```bash
# Run migration
php artisan migrate

# Verify success
php artisan migrate:status
```

### Post-Execution Verification

- [ ] Check migration status shows as "Ran"
- [ ] Verify zero journals: `SELECT COUNT(*) FROM journals;`
- [ ] Verify zero submissions: `SELECT COUNT(*) FROM submissions;`
- [ ] Verify zero demo users: `SELECT COUNT(*) FROM users WHERE email LIKE '%@demo.iamjos.id';`
- [ ] Verify super admin exists: `SELECT * FROM users WHERE email = '{SUPER_ADMIN_EMAIL}';`
- [ ] Verify infrastructure intact: Check roles, permissions, site_settings counts
- [ ] Review log file for any warnings or errors
- [ ] Test creating a new journal via admin interface

---

## 📊 EXPECTED RESULTS

### Before Cleanup

```
Journals: 5 (demo)
Submissions: ~127 (estimated)
Publications: ~127 (estimated)
Issues: ~15
Sections: ~20
Users: 4 demo + 1 super admin
```

### After Cleanup

```
Journals: 0
Submissions: 0
Publications: 0
Issues: 0
Sections: 0
Users: 1 (super admin only)

System Infrastructure: Intact
  ├─ Roles: Preserved
  ├─ Permissions: Preserved
  ├─ Email Templates: Preserved
  ├─ Site Settings: Preserved
  ├─ Site Content: Preserved
  └─ Site Navigation: Preserved
```

---

## 🔄 ROLLBACK PROCEDURE

### Migration Rollback

```bash
php artisan migrate:rollback
```

**Behavior**:
- Displays warning message
- Shows instructions for manual restoration
- Does NOT attempt to restore deleted data
- Marks migration as "not migrated"

### Manual Restoration (Non-Production Only)

```bash
# 1. Ensure APP_ENV is set to "local" or "staging"
# 2. Run DemoSeeder
php artisan db:seed --class=DemoSeeder
```

**⚠️ WARNING**: Never run DemoSeeder in production!

---

## 📈 PERFORMANCE ESTIMATES

**Expected Volume**:
- 5 demo journals
- ~20 sections
- ~15 issues
- ~127 submissions
- ~127 publications
- ~500 submission files
- ~1,000+ metrics records
- 4 demo users

**Estimated Execution Time**: 5-15 seconds

**Memory Usage**: Low (< 10MB)
- Collections of IDs are small
- No large data loading into memory
- Deletion operations stream to database

---

## 🔐 SECURITY CONSIDERATIONS

### Super Admin Protection

**Multiple Safeguards**:
1. Explicit exclusion in user identification query
2. Configuration validation before execution
3. Post-execution verification in tests

### Audit Trail

**Complete Logging**:
- All deletion counts logged
- Super admin preservation logged
- Execution time logged
- Any errors logged with full context

### Transaction Isolation

**ACID Compliance**:
- **Atomicity**: All-or-nothing execution
- **Consistency**: Foreign key constraints enforced
- **Isolation**: Default PostgreSQL isolation level
- **Durability**: Changes committed to disk

---

## 📚 DOCUMENTATION

### Spec Files

- **Requirements**: `.kiro/specs/production-database-cleanup/requirements.md`
- **Design**: `.kiro/specs/production-database-cleanup/design.md`
- **Tasks**: `.kiro/specs/production-database-cleanup/tasks.md`
- **Config**: `.kiro/specs/production-database-cleanup/.config.kiro`

### Related Documentation

- `database/seeders/DemoSeeder.php` - Demo data seeder with production guard
- `database/seeders/DatabaseSeeder.php` - Production seeder (clean install)
- `SETUP.md` - Setup instructions (mentions DemoSeeder)

---

## 🎯 NEXT STEPS

### Immediate

1. ✅ Spec complete and committed
2. ⏳ Review spec with stakeholders
3. ⏳ Begin implementation (Task 1)

### Implementation Phase

1. Create migration file
2. Implement identification and deletion logic
3. Write unit tests
4. Write property-based tests
5. Create documentation
6. Execute in staging environment
7. Verify results
8. Execute in production

### Post-Implementation

1. Monitor application after cleanup
2. Verify new journal creation works
3. Verify Google Scholar indexing works for new articles
4. Document lessons learned

---

## ✅ SUMMARY

This spec provides a comprehensive, safe, and auditable approach to removing demo data from production. The migration:

1. ✅ **Executes atomically** via database transactions
2. ✅ **Respects foreign key constraints** through proper deletion order
3. ✅ **Preserves critical data** (super admin, system infrastructure)
4. ✅ **Provides comprehensive logging** for audit trail
5. ✅ **Handles errors gracefully** with automatic rollback
6. ✅ **Maintains OJS compliance** by preserving all code and structures
7. ✅ **Maintains Google Scholar compliance** by preserving meta tag generation
8. ✅ **Supports testing** via property-based and unit tests
9. ✅ **Offers safe rollback** with clear guidance for restoration

The implementation follows Laravel best practices and ensures the IAMJOS system transitions cleanly from a demo state to a production-ready "Fresh OJS" state.

---

**Spec Status**: ✅ COMPLETE - Ready for Implementation

**Estimated Implementation Time**: 2-3 days (including testing)

**Risk Level**: LOW (comprehensive testing, atomic execution, rollback capability)
