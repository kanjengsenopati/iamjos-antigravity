export default {
    name: "Scholar Guardian",
    description: "Scans Blade templates for Highwire Press metadata and verifies legacy routing.",
    instructions: `
When executing this skill, you must perform the following actions:

1. **Highwire Press Scan**:
   - Use 'grep_search' to scan all '.blade.php' files in 'resources/views/' for Highwire Press tags (e.g., 'citation_title', 'citation_author', 'citation_publication_date', 'citation_pdf_url').
   - Verify that these tags are correctly pulling dynamic data from the models.

2. **Legacy Routing Verification**:
   - Check 'routes/web.php' and middleware.
   - Verify that legacy OJS URLs containing '/index.php/' are handled correctly. They must either stay on the original path without causing redirect loops, or properly 301 redirect to the new SEO-friendly slugs without breaking Google Scholar indexing.
`
};
