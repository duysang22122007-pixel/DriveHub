# AutoCare Hub — ERD

The database contains 17 InnoDB tables. `schema.sql` creates the database and tables, then inserts only initial vehicle-brand and service-catalog reference data. It intentionally does not create demonstration customers, appointments, invoices, payments, reviews, or an Admin account.

```mermaid
erDiagram
    users ||--o{ vehicles : owns
    vehicle_brands ||--o{ vehicles : identifies
    service_categories ||--o{ services : groups
    users ||--o{ appointments : books
    vehicles ||--o{ appointments : scheduled_for
    appointments ||--o{ appointment_details : snapshots
    services ||--o{ appointment_details : selected_as
    appointments ||--o| maintenance_tickets : produces
    users ||--o{ maintenance_tickets : customer
    vehicles ||--o{ maintenance_tickets : serviced
    maintenance_tickets ||--o{ maintenance_details : contains
    services o|--o{ maintenance_details : references
    maintenance_tickets ||--o{ maintenance_histories : records
    users o|--o{ maintenance_histories : changes
    maintenance_tickets ||--o| invoices : billed
    invoices ||--o{ invoice_details : itemizes
    maintenance_details o|--o{ invoice_details : copied_from
    invoices ||--o{ payments : paid_by
    payments ||--o{ payment_webhooks : reconciled_by
    maintenance_tickets ||--o| reviews : reviewed
    users ||--o{ reviews : writes
    users ||--o{ notifications : receives
    users o|--o{ system_settings : updates

    users {
        bigint id PK
        enum role
        varchar email UK
        varchar password_hash
        enum status
    }
    vehicle_brands {
        bigint id PK
        varchar name UK
        varchar slug UK
    }
    vehicles {
        bigint id PK
        bigint owner_id FK
        bigint brand_id FK
        varchar license_plate UK
        varchar vin UK
    }
    service_categories {
        bigint id PK
        varchar name UK
        varchar slug UK
    }
    services {
        bigint id PK
        bigint category_id FK
        varchar slug UK
        decimal base_price
        smallint estimated_duration_minutes
    }
    appointments {
        bigint id PK
        bigint customer_id FK
        bigint vehicle_id FK
        datetime scheduled_at
        enum status
        datetime active_slot UK
    }
    appointment_details {
        bigint id PK
        bigint appointment_id FK
        bigint service_id FK
        varchar service_name_snapshot
        decimal unit_price_snapshot
        smallint duration_minutes_snapshot
    }
    maintenance_tickets {
        bigint id PK
        bigint appointment_id FK_UK
        bigint customer_id FK
        bigint vehicle_id FK
        varchar ticket_number UK
        enum status
    }
    maintenance_details {
        bigint id PK
        bigint maintenance_ticket_id FK
        bigint service_id FK
        enum line_type
        decimal quantity
        decimal unit_price
    }
    maintenance_histories {
        bigint id PK
        bigint maintenance_ticket_id FK
        bigint changed_by FK
        enum previous_status
        enum new_status
    }
    invoices {
        bigint id PK
        bigint maintenance_ticket_id FK_UK
        varchar invoice_number UK
        enum status
        decimal subtotal
        decimal tax_rate
        decimal tax_amount
        decimal total_amount
    }
    invoice_details {
        bigint id PK
        bigint invoice_id FK
        bigint maintenance_detail_id FK
        decimal quantity
        decimal unit_price
    }
    payments {
        bigint id PK
        bigint invoice_id FK
        varchar transaction_ref UK
        varchar provider_transaction_id UK
        enum payment_method
        enum status
        decimal amount
    }
    payment_webhooks {
        bigint id PK
        bigint payment_id FK
        varchar provider
        varchar provider_event_id
        boolean signature_valid
        json payload
    }
    reviews {
        bigint id PK
        bigint maintenance_ticket_id FK_UK
        bigint customer_id FK
        tinyint rating
    }
    notifications {
        bigint id PK
        bigint user_id FK
        varchar type
        datetime read_at
    }
    system_settings {
        varchar setting_key PK
        text setting_value
        bigint updated_by FK
    }
```

## Important constraints

- Public registration is expected to create only `customer` users. The `admin` role is for a later, password-prompting CLI provisioning command; this SQL contains no Admin credentials.
- Vehicle ownership is enforced through the composite `(vehicle_id, customer_id)` foreign key on appointments. The maintenance ticket copies and validates the appointment's customer and vehicle.
- `appointments.active_slot` is generated only for pending, confirmed, or checked-in appointments and is unique. Rejected, completed, and cancelled appointments no longer reserve the slot. The application must still validate appointment duration and business hours before insert.
- Appointment detail rows store service name, price, and duration snapshots, so editing the current catalog does not change an existing booking.
- Unique foreign keys on maintenance tickets and invoices enforce at most one ticket per appointment and one invoice per ticket. Payments remain one-to-many per invoice.
- Each payment has an internal transaction reference. A webhook requires a linked payment and a unique `(provider, provider_event_id)` pair; the application must verify provider signatures and transaction details before changing payment state.
- Tax is currently constrained to zero by the agreed business rule. Both pre-tax and total amounts are persisted and must match.
- A review's `(maintenance_ticket_id, customer_id)` foreign key ensures it belongs to the ticket's customer; eligibility still requires the application to check that maintenance is completed.
- Monetary values use VND with two decimal places. No currency conversion is included.

## Import

Import `schema.sql` using phpMyAdmin or the MySQL 8 command-line client. The statements use `CREATE DATABASE IF NOT EXISTS` and `CREATE TABLE IF NOT EXISTS`; reference catalog seed statements are safe to re-run and do not overwrite existing catalog edits. Review business seed prices with the garage before production use.
