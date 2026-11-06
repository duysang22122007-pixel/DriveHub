USE drivehub;

START TRANSACTION;

INSERT INTO ROLES (name)
VALUES ('admin')
ON DUPLICATE KEY UPDATE name = VALUES(name);

UPDATE USERS AS existing_user
JOIN ROLES AS old_role ON old_role.id = existing_user.role_id AND old_role.name = 'staff'
JOIN ROLES AS admin_role ON admin_role.name = 'admin'
SET existing_user.role_id = admin_role.id;

DELETE FROM ROLES WHERE name = 'staff';

COMMIT;
