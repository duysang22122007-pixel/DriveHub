-- Indicative starting prices in VND for common passenger vehicles in Hanoi.
-- Parts, materials, vehicle class, damage scope, and travel distance may change
-- the final quote. This migration intentionally updates only the current catalog IDs.
USE autocare_hub;

START TRANSACTION;

UPDATE services
SET price = CASE id
    WHEN 1 THEN 1200000.00
    WHEN 2 THEN 2000000.00
    WHEN 3 THEN 3500000.00
    WHEN 4 THEN 6500000.00
    WHEN 5 THEN 300000.00
    WHEN 6 THEN 1500000.00
    WHEN 7 THEN 500000.00
    WHEN 8 THEN 300000.00
    WHEN 9 THEN 100000.00
    WHEN 10 THEN 800000.00
    WHEN 11 THEN 15000000.00
    WHEN 12 THEN 2000000.00
    WHEN 13 THEN 300000.00
    WHEN 14 THEN 300000.00
    WHEN 15 THEN 500000.00
    WHEN 16 THEN 3000000.00
    WHEN 17 THEN 300000.00
    WHEN 18 THEN 300000.00
    WHEN 19 THEN 500000.00
    WHEN 20 THEN 500000.00
    WHEN 21 THEN 200000.00
    WHEN 22 THEN 300000.00
END
WHERE id BETWEEN 1 AND 22;

COMMIT;
