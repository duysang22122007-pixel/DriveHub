-- Rollback: xoá bảng password_resets
USE autocare_hub;

DROP TABLE IF EXISTS password_resets;
