# Functionality Audit Report

Status of each major feature.

| Feature | Status | Notes |
|---|---|---|
| Registration | PASS | Email verification, phone validation, terms acceptance |
| Login / Logout | PASS | Rate limited, session regeneration; staff login and logout recorded in activity log |
| Two-factor authentication | PASS | TOTP challenge for all staff, recovery codes, admin setup |
| Email verification | PASS | Signed URL, resend |
| Password reset | PASS | Expiring tokens, generic response |
| Customer dashboard | PASS | Overview, orders, profile, security, notifications, support |
| Service catalog | PASS | Categories, active/inactive filter, status badges |
| Service details | PASS | Info blocks, links, images, dynamic form |
| Dynamic fields | PASS | 14 field types, server-side validation, regex |
| Homepage showcase | PASS | Auto-slide, reduced-motion support, per-slide animations, direction-aware swipe |
| Homepage CMS | PASS | Admin-managed slides |
| Guest orders | PASS | Name/email/phone, tracking code |
| Registered orders | PASS | Linked to account |
| Price snapshots | PASS | Service name/price/currency captured at order time |
| Payments | PASS | Bank/Binance/manual, proof upload, verify/reject |
| Order status engine | PASS | History preserved, WAITING_FOR_CUSTOMER flow |
| Order messaging | PASS | Customer/admin, internal notes hidden from customers |
| Order results | PASS | Text/code/link/file delivered to customer |
| Order tracking | PASS | Order number + tracking code |
| Email notifications | PASS | Confirmation + status changes (queued) |
| Internal notifications | PASS | In-app notification center |
| Telegram | PASS | Queued service, admin-configurable events |
| Announcements | PASS | Date windows, locations, auto-hide |
| Support system | PASS | Tickets with attachments, admin assignment |
| FAQ | PASS | Categories, ordering |
| Refunds | PARTIAL | Record table + structure; processing is manual |
| Coupons | PASS | Percent/fixed discounts, usage limits, per-customer limits, service restriction, expiry, admin management |
| Customer consent | PASS | Configurable consent checkbox; consent value + timestamp persisted on the order |
| SEO | PASS | Sitemap, meta tags, clean URLs; canonical + Open Graph/Twitter meta site-wide |
| Legal pages | PASS | Terms/Privacy/Refunds/Acceptable Use |
| Reports | PASS | Revenue/orders by service, payment methods, date range |
| System health | PASS | DB, storage, queue, cache, scheduler, telegram (live probe), mail (live probe), disk, app_debug, secure_cookie |
| Audit logging | PASS | Services, categories, orders, payments, staff (login + logout), settings, telegram, coupons, announcements, FAQ, homepage showcase |
| Admin users/roles | PASS | Staff CRUD, role/permission display |
| Settings | PASS | Website + payment method configuration |
| API-ready | PASS | Clean service layer, external IDs, webhook-ready structure |
| Error pages | PASS | 403/404/419/429/500/503 |

## Notes
- Refunds are recorded manually as specified ("Initial implementation may be manual").
- Coupons are fully implemented (Stage 35): percent/fixed discounts, usage limits, per-customer limits, service restriction, expiry, admin management.
- Real email/Telegram delivery requires production SMTP/Telegram credentials.
