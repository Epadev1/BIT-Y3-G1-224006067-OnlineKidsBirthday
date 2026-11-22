USE kids_party_planner;

UPDATE IGNORE users SET email = 'admin@gmail.com' WHERE email = 'admin@example.com';
UPDATE IGNORE users SET email = 'parent@gmail.com' WHERE email = 'parent@example.com';

INSERT INTO users (username, email, password_hash, role) VALUES
('Admin', 'admin@gmail.com', '$2y$10$5UxJyUxl1UY8touM0KWsH.6AQnGh6XMRmwHS4JGcLosnVvo87vUUS', 'admin'),
('Parent', 'parent@gmail.com', '$2y$10$5UxJyUxl1UY8touM0KWsH.6AQnGh6XMRmwHS4JGcLosnVvo87vUUS', 'parent')
ON DUPLICATE KEY UPDATE
    username = VALUES(username),
    password_hash = VALUES(password_hash),
    role = VALUES(role);

INSERT INTO vendors (name, service_type, price, contact_email)
SELECT 'Umunezero Cakes Kigali', 'Cake', 35000, 'umunezerocakes@gmail.com'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM vendors WHERE contact_email = 'umunezerocakes@gmail.com');
INSERT INTO vendors (name, service_type, price, contact_email)
SELECT 'Imirasire Party Decor Kigali', 'Decorations', 45000, 'imirasiredecor@gmail.com'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM vendors WHERE contact_email = 'imirasiredecor@gmail.com');
INSERT INTO vendors (name, service_type, price, contact_email)
SELECT 'Ibyishimo Kids Entertainment', 'Entertainment', 60000, 'ibyishimokids@gmail.com'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM vendors WHERE contact_email = 'ibyishimokids@gmail.com');
INSERT INTO vendors (name, service_type, price, contact_email)
SELECT 'Kigali Little Moments Photography', 'Photography', 50000, 'kigalilittlemoments@gmail.com'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM vendors WHERE contact_email = 'kigalilittlemoments@gmail.com');

INSERT INTO parties (parent_id, child_name, party_date, theme, budget)
SELECT user_id, 'Jamie', DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY), 'Space explorers', 900000
FROM users u
WHERE u.email = 'parent@gmail.com'
  AND NOT EXISTS (SELECT 1 FROM parties p WHERE p.parent_id = u.user_id AND p.child_name = 'Jamie');
INSERT INTO parties (parent_id, child_name, party_date, theme, budget)
SELECT user_id, 'Riley', DATE_ADD(CURRENT_DATE, INTERVAL 60 DAY), 'Enchanted garden', 1200000
FROM users u
WHERE u.email = 'parent@gmail.com'
  AND NOT EXISTS (SELECT 1 FROM parties p WHERE p.parent_id = u.user_id AND p.child_name = 'Riley');

INSERT INTO bookings (party_id, vendor_id, amount, status)
SELECT p.party_id, v.vendor_id, v.price, 'confirmed'
FROM parties p
JOIN users u ON u.user_id = p.parent_id
JOIN vendors v ON v.contact_email = 'umunezerocakes@gmail.com'
WHERE u.email = 'parent@gmail.com' AND p.child_name = 'Jamie'
  AND NOT EXISTS (SELECT 1 FROM bookings b WHERE b.party_id = p.party_id AND b.vendor_id = v.vendor_id);
INSERT INTO bookings (party_id, vendor_id, amount, status)
SELECT p.party_id, v.vendor_id, v.price, 'pending'
FROM parties p
JOIN users u ON u.user_id = p.parent_id
JOIN vendors v ON v.contact_email = 'imirasiredecor@gmail.com'
WHERE u.email = 'parent@gmail.com' AND p.child_name = 'Jamie'
  AND NOT EXISTS (SELECT 1 FROM bookings b WHERE b.party_id = p.party_id AND b.vendor_id = v.vendor_id);
