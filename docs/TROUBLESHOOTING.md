# Troubleshooting Guide

Common issues during installation and operation, with root-cause fixes (not workarounds).

## Installation
| Symptom | Cause | Fix |
|---|---|---|
| `Class "..." not found` after deploy | Composer deps outdated | `composer install --no-dev --optimize-autoloader` |
| White page / 500 immediately | `.env` missing or `APP_KEY` empty | `cp .env.example .env && php artisan key:generate` |
| Vite assets not loading | Assets not built | `npm install && npm run build` (dev: `npm run dev`) |
| `Database driver [sqlite] not configured` | DB_CONNECTION mismatch | Set `DB_CONNECTION` + credentials in `.env` |

## Login & access
| Symptom | Cause | Fix |
|---|---|---|
| Login redirects back with error | Wrong creds or account suspended | Check `is_active`; reset password via reset flow |
| Staff prompted for 2FA code unexpectedly | 2FA enabled for that account | Enter the current code from the authenticator app; if no device, disable 2FA via DB (`two_factor_secret=NULL`, `two_factor_confirmed_at=NULL`) or use a recovery code |
| 2FA code rejected | Clock skew / wrong secret | Ensure device time is synced (TOTP window ±30 s); re-run setup from `/profile/two-factor` |
| Lost authenticator device + all recovery codes | No fallback | Admin must clear the user's 2FA fields in DB; consider enforcing recovery-code storage |
| Admin routes 403 | Missing role/permission | Assign role via seeder or staff management; see PERMISSION-MATRIX.md |
| Session expires quickly | DB session + local timezone | `SESSION_LIFETIME`; inspect session table writes |
| 419 on form submission | CSRF token/session mismatch | Regenerate `APP_KEY` only once; clear browser cookies; check `SESSION_SECURE_COOKIE` vs HTTP/HTTPS |

## Database
| Symptom | Cause | Fix |
|---|---|---|
| `Unknown column` on queries | App code ahead of migrations | `php artisan migrate` |
| Migrations not in expected state | Partial apply | Check `migrations` table; fix and re-run specific migration |
| Tests all return 419 / use wrong DB | Stale config cache baked from `.env` | `php artisan config:clear` before running tests (see below) |

### Test environment 419 (CSRF)
Symptom: GET-based feature tests pass but POST tests fail with 419.

Cause: a cached `bootstrap/cache/config.php` pins `app.env` to `local` (and the real DB), so Laravel does not detect the `testing` environment and CSRF verification is enabled during tests.

Fix: `php artisan config:clear` (and `php artisan cache:clear`), then re-run `php artisan test`. Do not run `config:cache` while developing; cache config only for production deploys.

## Queues & notifications
| Symptom | Cause | Fix |
|---|---|---|
| Emails/Telegram not sent | Queue worker not running | `php artisan queue:work` or services Supervisor group |
| Jobs fail silently | Worker crashed / queue driver | Check logs (`storage/logs/laravel.log`); `php artisan queue:restart` |
| Telegram messages missing | Token/chat misconfig | Verify `TELEGRAM_*` env and admin Telegram settings page; enable `QUEUE_CONNECTION` in production |

## Scheduler
| Symptom | Cause | Fix |
|---|---|---|
| Unpaid orders never expire | Scheduler not running | Add cron: `* * * * * php artisan schedule:run` (see DEPLOYMENT.md) |
| `Schedule` worker errors | Missing crontab user permissions | Run cron as the app user; verify file permissions |

## Files & uploads
| Symptom | Cause | Fix |
|---|---|---|
| Upload rejected | Wrong MIME/extension/size | Accepted: jpg/jpeg/png/pdf, max 10 MB |
| Downloads 404 | Missing file or storage path | Files stored on `local` disk under `storage/app/private`; ensure storage linked/served appropriately |
| Proofs invisible in admin | Permission | `FINANCE`/`SUPER_ADMIN` require `payments.view` |

## Frontend build
| Symptom | Cause | Fix |
|---|---|---|
| `npm run build` fails | Node/Tailwind mismatch | Use Node 20+; delete stale `node_modules` and reinstall |
| Tailwind classes missing on new views | Build not rerun | Re-run `npm run build`; never rely on hot-reload in production |

## Health & monitoring
- `GET /up` verifies the app responds; scheduler heartbeat is cached.
- Check `php artisan route:list`, `php artisan migrate:status`, and `storage/logs/laravel.log` for anomalies.
- If order counts or dashboard pages slow down, see PERFORMANCE-AUDIT.md and SCALABILITY-AUDIT.md.

## Escalation principle
Investigate the root cause and fix it rather than patching symptoms. If a fix is unclear, inspect logs and reproduce locally before changing production data.