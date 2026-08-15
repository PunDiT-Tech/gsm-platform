# Scalability Audit Report

## Database
- Proper indexes on: orders(order_number unique), orders(tracking_token unique), orders(customer_id), orders(status), orders(payment_status), payments(order_id), order_field_values(order_id), service_fields(service_id), services(category_id), announcements(active window).
- Composite indexes added: `orders(status, payment_status)`, `orders(status, created_at)`, `orders(created_at)`, `payments(order_id, status)`, `payments(created_at)` for dashboard counting and date-range reports at scale.
- Query efficiency: reports aggregate at the DB layer, no full-table loads.

## Queries
- Eager loading used on order detail, dashboard, admin lists.
- Pagination on all list pages.
- Recommendation: implement query monitoring (e.g., Laravel Telescope) before heavy traffic.

## Queues
- All notifications (email, Telegram) dispatched via queue. Worker failure does not roll back DB transactions.
- Recommendation: Redis queue + Supervisor with multiple workers in production.

## Cache
- Rate limiter uses cache; scheduler heartbeat cached.
- Recommendation: cache the service catalog and currency/price lookups with short TTL at scale.

## Storage
- Private files on `local` disk, separate from code.
- Recommendation: move to S3-compatible object storage for multi-server deployments.

## Sessions
- DB session driver.
- Recommendation: Redis session driver for horizontal scaling.

## Images
- Uploads validated (MIME/size); on-the-fly resizing/WebP serving with disk cache implemented (query params `?w=`/`?h=`).

## Reports
- Aggregated in SQL, grouped, limited.

## Notifications
- Queued; failure isolated.

## API readiness
- Service layer separate from controllers; orders/fields use snapshot + external identifiers (order_number, tracking_token), no raw IDs exposed to customers.
- Design supports future reseller API, API keys and webhooks.

## Horizontal scaling readiness
- Stateless application (no in-process state), externalized config, separate file storage.
- Read replicas + Redis sessions recommended for further scale.

## Identified bottlenecks
1. Dashboard/order counts run multiple aggregate queries per request — acceptable now, cache later.
2. Dynamic field validation loops per field — fine for <50 fields/service.
3. Order search uses LIKE on order_number/customer — add full-text index if order volume grows.

## Recommendations (priority)
1. Redis for sessions + queue + cache (production).
2. S3 storage for private files.
3. Database read replicas.
4. Full-text search for orders.
5. Enable query log + Telescope in staging.
