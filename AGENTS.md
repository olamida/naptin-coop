# AGENTS.md

## Working Conventions

### Documentation Map (read on every session start)
- `PENDING-TASKS.md` — **session continuity ledger**. Read first; it records the current state, the NEXT UP task, the pending backlog and the work log, so a session can resume even after a disconnect. Update it when tasks start/finish.
- `APP-GUIDE.md` — single source of truth for features, architecture, routes, permissions, workflows and the changelog.
- `USER-GUIDE.md` — end-user manual for admins/members.
- `SYSTEM-DOCUMENTATION.md` — deep technical doc (request flow, layers, ledger internals, DB, how to add a feature).

### Session Continuity Protocol
1. On session start, read `PENDING-TASKS.md` before doing anything else.
2. If the last session stalled, its task is marked `IN PROGRESS` — finish that task before starting new work.
3. Keep `PENDING-TASKS.md` accurate: move the active task to `IN PROGRESS`, then to `DONE` with the commit hash. This is what lets a closed session be resumed later.

### GitHub Definition-of-Done (DoD) Ritual
After **every logical feature/fix** is completed and verified, perform the DoD ritual:

1. **Verify** — run tests and lint:
   - `composer test` (runs `php artisan config:clear` + `php artisan test`)
   - `vendor/bin/pint` (Laravel Pint style fixes) if available
2. **Document** — update `APP-GUIDE.md` to capture the change (architecture tree, module reference, routes, permissions, user-guide tasks) so the guide stays the single source of truth. Add a **changelog entry** for user-visible features. Do NOT skip this step for shipped features.
3. **Review** — inspect `git status` and `git diff` to confirm only intended changes
4. **Commit** — create a concise, descriptive commit message matching repo style
5. **Push** — push the commit to the remote

Do NOT commit or push unless this ritual is triggered (per-project workflow request).
