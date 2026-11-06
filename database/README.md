# DriveHub database setup

1. Import `database/schema.sql` into MySQL (for example, with phpMyAdmin). It creates the `drivehub` database, the appointment/repair/history tables, and the service records used by the booking form.
2. Keep `configs/env.php` pointed at the same database. Create a customer account through **Đăng ký** in the application.
3. Admin accounts are provisioned by a trusted database administrator; public registration cannot create admin accounts. Generate a password hash with PHP:

   ```sh
   php -r "echo password_hash('replace-with-a-strong-password', PASSWORD_DEFAULT), PHP_EOL;"
   ```

   Then insert the admin user and employee profile (replace the sample identity and hash). The employee profile links actions such as vehicle check-in to the admin account:

   ```sql
   INSERT INTO USERS (role_id, username, email, password_hash)
   SELECT id, 'admin1', 'admin@example.com', '<generated-password-hash>'
   FROM ROLES WHERE name = 'admin';

   INSERT INTO EMPLOYEES (user_id, full_name, phone, position)
   VALUES (LAST_INSERT_ID(), 'Quản trị viên DriveHub', '0900000000', 'Quản trị gara');
   ```

   Do not store a plaintext password in the database.
4. Sign in as a customer to book and view that customer's appointments/cars. Sign in as an admin account to manage appointments, check vehicles in, and manage repair orders.

The script is a fresh-install schema. If you already imported an earlier schema that uses the `staff` role, back up the database, then run `database/migrate_staff_to_admin.sql` once to convert existing staff users to admin without changing their passwords or employee profiles. For other existing tables, reconcile columns/constraints before importing; `CREATE TABLE IF NOT EXISTS` does not alter existing tables.
