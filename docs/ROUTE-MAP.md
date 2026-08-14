# Route Map

Prefix conventions: customer pages `/`, admin pages `/admin`, auth `/`.

## Public (guest)
```
GET  /                         Homepage (showcase, featured, announcements, how-it-works, FAQ, CTA)
GET  /services                 Service catalog (active categories + services)
GET  /services/{slug}          Service detail (info blocks, links, images, dynamic form)
GET  /how-it-works             Static page
GET  /faq                      FAQ page
GET  /announcements            Announcement list
GET  /contact                  Contact page
POST /contact                  Contact form (rate limited)
GET  /check-order              Public order tracking form
POST /check-order              Lookup order (order number + tracking code)
GET  /terms /privacy /refunds /acceptable-use   Legal pages
GET  /sitemap.xml              Sitemap
```

## Auth (customer)
```
GET/POST /register             Register
GET/POST /login                Login
POST /logout                   Logout
GET /email/verify/{id}/{hash}  Email verification
POST /email/verification-notification
GET  /forgot-password          Forgot password form
POST /forgot-password          Send reset link
GET  /reset-password/{token}   Reset password form
POST /reset-password           Perform reset
```

## Customer (authenticated)
```
GET /dashboard                 Overview
GET /dashboard/orders          My orders
GET /dashboard/orders/{order}  Order detail (messages, timeline, result)
POST /dashboard/orders/{order}/messages   Send message
POST /dashboard/orders/{order}/upload     Upload requested info
GET /dashboard/notifications   Notification list
POST /dashboard/notifications/read       Mark read
GET /dashboard/profile         Profile
PATCH /dashboard/profile       Update profile
GET /dashboard/security        Security settings
PATCH /dashboard/password      Change password
```

## Order placement
```
GET  /services/{slug}                  Form (renders dynamic fields)
POST /orders                           Create order (guest or logged in)
GET  /orders/{order}/{token}           Order confirmation/tracking (secure token)
```

## Admin (authenticated, /admin, role-gated)
```
GET     /admin                        Dashboard
GET     /admin/orders                 Order list + search (imei/serial/order id)
GET     /admin/orders/{order}         Order detail
POST    /admin/orders/{order}/status  Change status
POST    /admin/orders/{order}/message Send admin message
POST    /admin/orders/{order}/result  Add result
GET     /admin/services               Service list
GET/POST/PUT/DELETE  /admin/services/*  Service CRUD
GET/POST/PUT/DELETE  /admin/categories/* Category CRUD
GET/POST/PUT/DELETE  /admin/services/{service}/fields  Dynamic field builder
GET/POST/PUT/DELETE  /admin/services/{service}/blocks  Info blocks
GET/POST/PUT/DELETE  /admin/services/{service}/links   Links
GET/POST/PUT/DELETE  /admin/services/{service}/images  Images
GET/POST/PUT/DELETE  /admin/payments*    Payment verification/rejection
GET     /admin/customers*           Customer list/detail
GET/POST/PUT/DELETE  /admin/support*    Support tickets
GET/POST/PUT/DELETE  /admin/homepage*   Homepage CMS
GET/POST/PUT/DELETE  /admin/announcements*
GET/POST/PUT/DELETE  /admin/telegram*   Telegram settings
GET     /admin/notifications*       (staff notifications)
GET     /admin/reports*             Reports
GET/POST/PUT/DELETE  /admin/staff*     Admin users, roles, permissions
GET/POST/PUT/DELETE  /admin/settings*  Website settings, payment methods
GET     /admin/system               System health
GET     /admin/audit-logs           Audit logs
GET     /admin/files/{file}         Authorized download of proofs/results
```

## System
```
GET /up                       Health check
scheduler: every minute       Scheduler (queue, order maintenance)
```
