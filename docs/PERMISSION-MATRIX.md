# Permission Matrix

Roles: SUPER_ADMIN, ADMIN, SUPPORT, FINANCE.
`*` = full CRUD. All admin routes require `auth` + verified + role gate + Policy check.

| Permission              | SUPER_ADMIN | ADMIN | SUPPORT | FINANCE |
|-------------------------|-------------|-------|---------|---------|
| users.view              | x           | x     |         |         |
| users.manage            | x           | x     |         |         |
| customers.view          | x           | x     | x       | x       |
| customers.manage        | x           | x     |         |         |
| orders.view             | x           | x     | x       | x       |
| orders.create           | x           | x     | x       |         |
| orders.edit             | x           | x     |         |         |
| orders.status           | x           | x     | x       |         |
| orders.message          | x           | x     | x       |         |
| orders.result           | x           | x     |         |         |
| services.view           | x           | x     | x       |         |
| services.create         | x           | x     |         |         |
| services.edit           | x           | x     |         |         |
| services.delete         | x           | x     |         |         |
| payments.view           | x           | x     | x       | x       |
| payments.verify         | x           | x     |         | x       |
| payments.reject         | x           | x     |         | x       |
| payments.refund         | x           | x     |         | x       |
| announcements.manage    | x           | x     |         |         |
| homepage.manage         | x           | x     |         |         |
| telegram.manage         | x           | x     |         |         |
| settings.manage         | x           | x     |         |         |
| reports.view            | x           | x     |         | x       |
| audit_logs.view         | x           | x     |         |         |
| admins.manage           | x           | x     |         |         |
| support.manage          | x           | x     | x       |         |
| support.view            | x           | x     | x       | x       |

Implementation:
- `role_user`, `permission_role` tables + `hasPermission()` on User model.
- `EnsureUserHasRole` / `Permission` middleware checks on admin routes.
- Policies (`OrderPolicy`, `PaymentPolicy`, `ServicePolicy`, ...) gate controller actions.
- Every POST/PUT/DELETE on /admin is authorized server-side; hiding UI is not enough.
