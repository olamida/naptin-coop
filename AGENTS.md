# AGENTS.md

## Working Conventions

### GitHub Definition-of-Done (DoD) Ritual
After **every logical feature/fix** is completed and verified, perform the DoD ritual:

1. **Verify** — run tests and lint:
   - `composer test` (runs `php artisan config:clear` + `php artisan test`)
   - `vendor/bin/pint` (Laravel Pint style fixes) if available
2. **Review** — inspect `git status` and `git diff` to confirm only intended changes
3. **Commit** — create a concise, descriptive commit message matching repo style
4. **Push** — push the commit to the remote

Do NOT commit or push unless this ritual is triggered (per-project workflow request).
