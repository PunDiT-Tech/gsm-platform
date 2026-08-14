# Database Relationship Diagram

## RBAC
```
users ──┬── role_user ──┬── roles ──┬── permission_role ──┬── permissions
        │              │
        └── customers (1:1 optional)
```

## Catalog
```
service_categories ──┬── services ──┬── service_fields
                     │             ├── service_field_options
                     │             ├── service_information_blocks
                     │             ├── service_links
                     │             └── service_images
```

## Orders
```
customers ──┬── orders ──┬── order_field_values
            │            ├── order_status_history
            │            ├── order_messages
            │            └── order_results
            └── (guest orders reference a lightweight customer row)
```

## Payments
```
orders ──┬── payments ──┬── payment_proofs
         │              └── refunds (order_id + payment_id)
         └── payment_methods (1:1 config tables)
```

## Content & Support
```
announcements        homepage_sections
support_tickets ──┬── support_messages
                  └── users (assignee)

```

## System
```
users ──┬── audit_logs
        ├── admin_activity_logs
        └── notifications (database notifications)
telegram_settings     website_settings
```

## Key relationships (FK)
- orders.customer_id → customers.id
- orders.service_id → services.id (nullable, historical safe)
- order_field_values.order_id → orders.id; service_field_id → service_fields.id (nullable)
- payments.order_id → orders.id; payment_method_id → payment_methods.id
- refunds.order_id / payment_id
- service_fields.service_id → services.id
- role_user.user_id/role_id; permission_role.role_id/permission_id

## Integrity notes
- Orders never hard-delete; service references soft-deleted so snapshots stay valid.
- `order_field_values` store the label + value text at submission time.
- indexes: orders(order_number unique), orders(tracking_token unique), orders(customer_id), orders(service_id), payments(order_id), order_field_values(order_id), service_fields(service_id), unique(services.slug), unique(service_categories.slug).
