export default {
    name: "Defense Shield",
    description: "Acts as a malware scanner and configuration validator.",
    instructions: `
When executing this skill, you must perform the following actions:

1. **Malware Scan**:
   - Use 'grep_search' to find potentially dangerous PHP functions.
   - Search for: 'system(', 'shell_exec(', 'exec(', 'passthru(', and 'eval(' within the 'app/' and 'public/storage/' directories.
   - Report any findings immediately as they pose severe security risks.

2. **Supabase & RLS Check**:
   - Perform a codebase search for 'supabase' or 'RLS' (Row Level Security) configurations.
   - Since this is a native Laravel project (with MySQL/PostgreSQL/SQLite), verify that no accidental external database dependencies or conflicting RLS security rules from previous project boilerplates are present.
`
};
