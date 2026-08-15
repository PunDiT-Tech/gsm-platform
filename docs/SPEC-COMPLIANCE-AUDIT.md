# Spec Compliance Audit Report

Audit date: 2026-08-15. Result of a full comparison of the running application against the master spec (`B.txt`, Stage 0 - Stage 69). Findings marked `FIXED` were remediated on 2026-08-15 (round 2: refund admin flow, checkout payment-method display/selection, order review step, service/showcase images, field/block/link edit+toggle UI, message read flags, SEO canonical/Twitter meta; round 3: support assign/attachment/read status, homepage CMS content, payment-proof email, FAQ on services, customer suspend, admin notifications, WebP optimization, API + external_id; round 4: production hardening probes, homepage animation types + direction-aware swipe, per-service payment-method override, customer unread-message badge; round 5: staff logout audit, composite indexes, on-the-fly image resize/WebP, nginx + env production templates).

Legend: PASS = spec met; PARTIAL = partially met; FAIL/OPEN = not met. Severity: CRITICAL / HIGH / MEDIUM / LOW.

## Stage 0-1 - Architecture & Database
| Item | Result | Notes |
|---|---|---|
| Clean layered architecture | PASS | Controllers / Services / Models; no business logic in Blade |
| All spec tables + models present | PASS | 33 domain tables; model for each |
| Order snapshots (`service_name`, `price`, `currency`) | PASS | Written inside order-creation transaction |
| Order field values preserved | PASS | `order_field_values.label` snapshot + `nullOnDelete` FK |
| Order number + tracking token | PASS | Both `unique()`; tracking token stored bcrypt-hashed |
| Consent fields | FIXED | `orders.consent_given_at` added + persisted |
| 2FA columns | PASS | `two_factor_secret` (encrypted), confirmed_at, recovery codes |
| Support message read status | FIXED | `support_messages.read_at` added; admin + customer reads mark messages read |
| `homepage_showcases.service_id` FK | FIXED | Constraint + index added |
| `order_messages.read_at` read status | FIXED | Customer reads mark messages read (admin + tracking); admin order page marks all read |

## Stage 2-4, 42 - Auth, RBAC, Admin, Admin security
| Item | Result | Notes |
|---|---|---|
| Register/login/logout/email verify/reset/change-password | PASS | Full flows + expiry + throttling |
| Generic reset response (no enumeration) | PASS | Always returns `passwords.sent` |
| Roles + 24 permissions | PASS | SUPER_ADMIN/ADMIN/SUPPORT/FINANCE |
| Server-side RBAC on admin routes | PASS | `role:` + `permission:` middleware on all admin routes |
| Admin dashboard stats + nav | FIXED | All items present; admin Notifications nav + page added (staff notified on order/payment/support) |
| `payments.refund` permission implemented | FIXED | Admin refund flow/route/UI added (manual record); Telegram + audit + notifications |
| Staff password policy | FIXED | Now `min(8)+letters+numbers+mixedCase` |
| Secure cookie in production | FIXED | `checkSecureCookie` health probe: warns unless secure cookie set in production |
| 2FA for staff | PASS | Full TOTP challenge, recovery codes, rate-limited, encrypted secret |
| APP_DEBUG for production | FIXED | `checkAppDebug` health probe: warns when `APP_DEBUG=true` |

## Stage 5-10 - Categories, Services, Fields, Content, Images
| Item | Result | Notes |
|---|---|---|
| Category CRUD + soft delete + guard | PASS | Delete blocked when services exist; toggle audited |
| Service engine fields | FIXED | All fields present; per-service `payment_method_id` override added (nullable FK, admin select, checkout restriction) |
| 14 dynamic field types | PASS | Type registry + render + server-side validation |
| SERIAL_NUMBER validation    | FIXED  | Server-side format regex now applied (also PHONE) |
| Field admin edit / active toggle | FIXED | Field edit (value/options) + active toggle routes + UI added |
| Information block types + order | PASS | 9 types, rendered in order |
| Block/links active toggle + preview | FIXED | Block/link edit + active toggle routes + UI added |
| Links safe-URL validation | PASS | `url` + `not_regex:/^javascript:/i` |
| Service/showcase images (upload, validation, WebP) | FIXED | Admin upload/remove (jpg/jpeg/png/webp ≤5MB) + public streaming routes; WebP conversion when GD available |
| Slug uniqueness race | FIXED | Generated slug checked after generation (services + categories) |

## Stage 11-16, 22-27, 35-36 - Customer site, orders, status, coupons, consent
| Item | Result | Notes |
|---|---|---|
| Public pages (home/services/details/how-it-works/faq/announcements/contact/login/register/check-order) | PASS | All present with responsive Tailwind UI |
| Homepage showcase | FIXED | Auto-slide, pause-on-hover, reduced-motion; all 7 animation types applied per-slide; swipe now direction-aware (left/right) |
| Homepage CMS (hero/stats/CTA/footer) | FIXED | Hero/stats/how-it-works/CTA text + footer copyright admin-editable via homepage content form |
| Order engine transactional | PASS | DB::transaction; price always from DB (browser price ignored) |
| Review step | FIXED | Separate post-form REVIEW page shows server price/coupon; confirm submits order |
| Guest orders + dual-key tracking | PASS | Requires order number AND code |
| Registered orders ownership | PASS | Ownership checked on every access |
| Status engine + history | PASS | Every change writes history (incl. auto-expire) |
| Waiting-for-customer | PASS | Admin sets status + message; customer ACTION REQUIRED + reply/upload |
| Messaging internal filtering | PASS | INTERNAL never exposed to customers |
| Message read status | FIXED | `read_at` set by customer (tracking/order) and admin reads |
| Results security | PASS | Private storage, authorized downloads; public file link now shows "log in" note + COMPLETED gate |
| Tracking page | PASS | Shows service/date/payment/status/timeline/messages/results |
| Customer dashboard | FIXED | Waiting/rejected/cancelled counts added + dashboard announcements + unread order-message badge |
| Coupons | FIXED | Service restriction added (service_id); per-customer + usage limits + expiry already present |
| Consent | FIXED | Configurable + enforced + now persisted with timestamp |

## Stage 17-21, 28-34 - Payments, email, notifications, telegram, announcements, support, FAQ, refunds
| Item | Result | Notes |
|---|---|---|
| Payment methods + statuses | PASS | 5 codes modeled, 6 statuses; admin config JSON |
| Binance/Bank config data shown at checkout | FIXED | Config rendered (QR + key/value); customer can select/pick payment method at checkout |
| Payment proof MIME/size/private storage | PASS | jpg/jpeg/png/pdf, max 10MB, `local` disk, authorized download route |
| Transaction-ID-only proof | FIXED | `file_path` now nullable |
| Payment verify/reject + notifications | PASS | Transactional, queued notifications after commit |
| Proof upload transactional | FIXED | Now wrapped in `DB::transaction` |
| Email notifications queued; failure isolated | PASS | All dispatch after commit; `ShouldQueue` |
| "Payment proof received" email | FIXED | Customer email sent on proof upload |
| Notification center unread count | FIXED | Badge in dashboard sidebar |
| Telegram events + queued + retry | FIXED | Job now throws on failure so `tries=3`/`backoff`/`failed_jobs` engage |
| Announcements date-window auto-hide | PASS | Active scope by date |
| Announcement locations (services/dashboard) | FIXED | Now rendered on service pages + customer dashboard |
| Support tickets + attachments | FIXED | Ticket + attachment stored; admin assign route + attachment download (admin + customer) added; admin reply supports attachment |
| FAQ everywhere | FIXED | Homepage + FAQ page + service pages |
| Refunds | FIXED | Manual record admin UI (payments + orders), only VERIFIED, amount capped; payment/order set REFUNDED |
| Auto-expire customer notification | FIXED | Now emails + notifies + Telegram on cancel |

## Stage 37-59 - Ops / quality / docs
| Item | Result | Notes |
|---|---|---|
| SEO meta (canonical/OG/Twitter) | FIXED | Canonical + site-wide OG/Twitter in base layout; service pages use image |
| Legal pages | PASS | 4 pages; content is static (not admin-edited) |
| Reports | PASS | Orders/revenue/service/method/date-range aggregates |
| System health | FIXED | All checkers present; mail/telegram now live probes (transport connect / `getMe`); app_debug + secure_cookie hardening checks added |
| Audit logging | FIXED-EXPANDED | Services/categories/orders/payments + now staff/settings/telegram/coupons/announcements/FAQ/showcase/toggle/feature; staff logout now audited |
| API-readiness | FIXED | `api.php` with Bearer-token auth (`api.auth`), services + order-lookup endpoints, API key setting, `external_id` on services/orders |
| Performance | FIXED-EXPANDED | Homepage N+1 fixed; reports already aggregates; composite indexes added (`orders(status,payment_status)`, `orders(status,created_at)`, `orders(created_at)`, `payments(order_id,status)`, `payments(created_at)`); on-the-fly WebP resize with disk cache |
| Transactions everywhere | FIXED-EXPANDED | Proof upload, expiry, order status history now transactional |
| Error pages 403/404/419/429/500/503 | PASS | All present, no stack traces in views |
| Rate limiting all listed actions | FIXED | Order file upload now throttled like the rest |
| Docs deliverable | PASS | README + INSTALLATION + DEPLOYMENT + SECURITY-AUDIT + FUNCTIONALITY-AUDIT + SCALABILITY-AUDIT + BACKUP/RESTORE + TROUBLESHOOTING + PERFORMANCE + CODE-QUALITY + per-spec audit docs + `deploy/nginx-gsm-platform.conf` + `.env.production.example` |

## Remaining known gaps (highest first)
- None blocking. Production deployment must set `APP_DEBUG=false` + `SESSION_SECURE_COOKIE=true` in `.env` (now enforced by health probes; hardened defaults in `.env.production.example`).

## Verification performed after fixes
- `php artisan test` -> 112 passed / 303 assertions.
- `php -l` across app, database, routes, tests -> all clean.
- `npx vite build` -> success.
- `migrate` -> applied to dev DB (payment_method_id on services, composite indexes).
- `migrate:fresh --seed` -> 1 admin, 4 roles, 27 permissions, 3 payment methods, 3 services.