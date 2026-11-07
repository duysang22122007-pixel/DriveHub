# DriveHub database setup

1. Import `database/schema.sql` into MySQL (for example, with phpMyAdmin). It creates the `drivehub` database, the appointment/repair/history tables, and the service records used by the booking form.
2. Keep `configs/env.php` pointed at the same database. Create a customer account through **Đăng ký** in the application.
3. Provision the initial Admin account through a trusted database administrator; public registration cannot create admin accounts. After the first Admin is provisioned, signed-in Admin users can create and manage additional Admin accounts from the management screen. Generate the initial password hash with PHP:

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
4. Sign in as a customer at `/DriveHub/login` to book and view that customer's appointments/cars. Sign in as an admin at `/DriveHub/admin/login` to open the admin dashboard and manage appointments, repair orders, Admin accounts, and customers. The two login routes accept only their corresponding account role. Active sessions are revalidated against the account role and status in MySQL on each request, so a disabled account loses access without waiting for logout. The application stores customer and admin sessions separately in `$_SESSION['customer']` and `$_SESSION['admin']`.

For testing, create a customer through **Đăng ký**. The sample admin identity above uses username `admin1` or email `admin@example.com`; its password is the one used to generate the hash, not a built-in default. Do not use a shared demo password on a deployed system.

The script is a fresh-install schema. If you already imported an earlier schema, back up the database and run `database/migrate_appointment_status_history.sql` to enable appointment timelines and `database/migrate_repair_details.sql` if `REPAIR_DETAILS` is missing. If the earlier schema uses the `staff` role, also run `database/migrate_staff_to_admin.sql` to convert existing staff users to admin without changing their passwords or employee profiles. The timeline migration marks the status observed at migration time; it does not invent timestamps for earlier status changes. For other existing tables, reconcile columns/constraints before importing; `CREATE TABLE IF NOT EXISTS` does not alter existing tables.
