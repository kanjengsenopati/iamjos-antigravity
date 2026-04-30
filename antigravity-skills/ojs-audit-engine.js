export default {
    name: "OJS Audit Engine",
    description: "Verifies Waterfall Compliance and audits the Wizard Installer readiness.",
    instructions: `
When executing this skill, you must perform the following actions:

1. **Waterfall Compliance**:
   - Read the contents of 'MASTER_LIST_TODO.md'.
   - Analyze the progress of the current phase.
   - Report which critical tasks are still unchecked and require immediate attention based on the Waterfall methodology.

2. **Wizard Installer Audit**:
   - Check if an 'install/' folder or installer routing still exists and is exposed.
   - Check if the '.env' file is fully populated with production-ready keys, or if it's still using default/empty values.
   - Report on the security posture of the installation process.
`
};
