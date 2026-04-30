export default {
    name: "ETL Legacy Handler",
    description: "Audits ETL logic for migrating OJS legacy articles to the new Laravel schema.",
    instructions: `
When executing this skill, you must perform the following actions:

1. **Schema Mapping Check**:
   - Analyze the ETL scripts (e.g., Artisan commands in 'app/Console/Commands') to ensure legacy OJS 'articles' and 'published_articles' tables are correctly mapped to the new 'submissions' and 'publications' tables.
   
2. **Legacy Flagging Logic**:
   - Verify that the migration logic explicitly inserts 'is_legacy = true' on migrated records so the system can differentiate between new and imported submissions.

3. **SEO Preservation (Original URLs)**:
   - Check if the ETL script captures and stores the original OJS URL path (e.g., '/index.php/journal/article/view/123') in a 'legacy_url' column or metadata JSON.
   - This ensures that old indexed links in Google Scholar do not break (404) after migration.
`
};
