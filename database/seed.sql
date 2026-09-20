-- ==============================================================
-- Hospital Management System — Phase 1 seed data
-- Demo logins (all passwords are the plain "…123" strings):
--   Admin        admin@hospital.com      / admin123
--   Doctor       doctor@hospital.com     / doctor123
--   Receptionist receptionist@hospital.com / reception123
--   Patient      patient@hospital.com    / patient123
-- ==============================================================

-- -----------------------------------------------------------------
-- Departments (editable starter list)
-- -----------------------------------------------------------------
INSERT INTO departments (name, description) VALUES
('Cardiology',      'Diagnosis and treatment of heart and cardiovascular conditions.'),
('Pediatrics',      'Medical care for infants, children and adolescents.'),
('Orthopedics',     'Treatment of the musculoskeletal system — bones, joints and muscles.'),
('General Medicine','Comprehensive care for a wide range of adult health concerns.'),
('Dermatology',     'Diagnosis and treatment of skin, hair and nail conditions.'),
('Gynecology',      'Care for the female reproductive system and pregnancy.'),
('Neurology',       'Diagnosis and treatment of disorders of the nervous system.');

-- -----------------------------------------------------------------
-- Users (passwords hashed with password_hash, PASSWORD_DEFAULT)
-- -----------------------------------------------------------------
INSERT INTO users (role, name, email, password, phone) VALUES
('admin',        'System Administrator', 'admin@hospital.com',         '$2y$10$kC9qVXQrgP.cyks6t8yMTO7Cl5YQ3Ul.1FgMu3tBZLhu1zie5KpG6', '+251 911 000 111'),
('doctor',       'Dr. Abebe Kebede',     'doctor@hospital.com',        '$2y$10$Wd1VoH1O9yt8qITSu564ceU28pDaaneDbz47hd10H4LRILErt6lMW', '+251 911 000 222'),
('doctor',       'Dr. Sara Alemu',       'sara@hospital.com',          '$2y$10$Wd1VoH1O9yt8qITSu564ceU28pDaaneDbz47hd10H4LRILErt6lMW', '+251 911 000 223'),
('receptionist', 'Hanna Tesfaye',        'receptionist@hospital.com',  '$2y$10$bstZXMGGhnYXP8v4CKqfOO9CWppGijUs5f5q4j1PG.qabmRmQqS0G', '+251 911 000 333'),
('patient',      'Dawit Getachew',       'patient@hospital.com',       '$2y$10$xRPyjMc/eThzrWPXqCN0meQI2BLEl7X6uzFWMkNY0S/IaiO/Qocwa', '+251 911 000 444');

-- -----------------------------------------------------------------
-- Doctors: Dr. Abebe (Cardiology), Dr. Sara (Pediatrics)
-- -----------------------------------------------------------------
INSERT INTO doctors (user_id, department_id, specialization, qualification) VALUES
((SELECT id FROM users WHERE email = 'doctor@hospital.com'), (SELECT id FROM departments WHERE name = 'Cardiology'),
 'Interventional Cardiologist', 'MD, FACC — Addis Ababa University'),
((SELECT id FROM users WHERE email = 'sara@hospital.com'),   (SELECT id FROM departments WHERE name = 'Pediatrics'),
 'General Pediatrician',        'MD — Gondar University');

-- -----------------------------------------------------------------
-- Patient medical profile
-- -----------------------------------------------------------------
INSERT INTO patients (user_id, date_of_birth, gender, address, emergency_contact_name, emergency_contact_phone, blood_type, allergies)
VALUES (
    (SELECT id FROM users WHERE email = 'patient@hospital.com'),
    '1992-04-15', 'male', 'Bole, Addis Ababa',
    'Marta Getachew', '+251 911 555 444', 'O+',
    'Penicillin, peanuts'
);

-- -----------------------------------------------------------------
-- Doctor weekly availability (one row = one 30-minute slot)
--   Dr. Abebe (Cardiology): Mon/Wed/Fri 09:00–10:30, Tue 14:00–15:00
--   Dr. Sara  (Pediatrics): Mon/Tue/Thu 14:00–15:30
-- -----------------------------------------------------------------
INSERT INTO availabilities (doctor_id, day_of_week, start_time) VALUES
-- Dr. Abebe
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'doctor@hospital.com')), 1, '09:00:00'),
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'doctor@hospital.com')), 1, '09:30:00'),
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'doctor@hospital.com')), 1, '10:00:00'),
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'doctor@hospital.com')), 3, '09:00:00'),
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'doctor@hospital.com')), 3, '09:30:00'),
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'doctor@hospital.com')), 3, '10:00:00'),
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'doctor@hospital.com')), 5, '09:00:00'),
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'doctor@hospital.com')), 5, '09:30:00'),
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'doctor@hospital.com')), 5, '10:00:00'),
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'doctor@hospital.com')), 2, '14:00:00'),
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'doctor@hospital.com')), 2, '14:30:00'),
-- Dr. Sara
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'sara@hospital.com')), 1, '14:00:00'),
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'sara@hospital.com')), 1, '14:30:00'),
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'sara@hospital.com')), 1, '15:00:00'),
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'sara@hospital.com')), 2, '14:00:00'),
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'sara@hospital.com')), 2, '14:30:00'),
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'sara@hospital.com')), 2, '15:00:00'),
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'sara@hospital.com')), 4, '14:00:00'),
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'sara@hospital.com')), 4, '14:30:00'),
((SELECT id FROM doctors WHERE user_id = (SELECT id FROM users WHERE email = 'sara@hospital.com')), 4, '15:00:00');
-- ==============================================================
-- Phase 2 seed data
-- Demo logins:
--   Nurse        nurse@hospital.com        / nurse123
--   Pharmacist   pharmacist@hospital.com   / pharmacist123
--   Lab Tech     lab@hospital.com          / lab123
-- ==============================================================

-- -----------------------------------------------------------------
-- New role accounts
-- -----------------------------------------------------------------
INSERT INTO users (role, name, email, password, phone) VALUES
('nurse',          'Alemitu Bekele',      'nurse@hospital.com',      '$2y$10$43IzIcnwQ4UNtnOKJvWy3uQWocUmlJ67mnMTIZok9u6Ysd2DRkbCS', '+251 911 000 555'),
('pharmacist',     'Daniel Girma',        'pharmacist@hospital.com', '$2y$10$GciBx2Rdnpm0jOYqklUCBefOUY3ZBmzfmyzli.sKzsyRVdrwjGvtu', '+251 911 000 666'),
('lab_technician', 'Feven Tadesse',       'lab@hospital.com',        '$2y$10$nYScZ34nuHnw9INXca1EOuy6KE0ZbQ1FTvvMK.OggnNEsRa7djazy', '+251 911 000 777'),
('accountant',     'Mulualem Worku',      'accountant@hospital.com', '$2y$10$SfK/JaRHRClL96HMHQnJT.AvqiD.sZ3eEy6DZ/tI01kdpAKjIwvj6', '+251 911 000 888');

-- -----------------------------------------------------------------
-- Wards and beds
-- -----------------------------------------------------------------
INSERT INTO wards (name, description) VALUES
('General Ward',  'General medicine beds for monitored recovery.'),
('ICU',           'Intensive care unit for critically ill patients.'),
('Maternity',     'Maternity and post-natal care.'),
('Pediatric Ward','Care for infants, children and adolescents.');

SET @general = (SELECT id FROM wards WHERE name = 'General Ward');
SET @icu     = (SELECT id FROM wards WHERE name = 'ICU');
SET @mat     = (SELECT id FROM wards WHERE name = 'Maternity');
SET @ped     = (SELECT id FROM wards WHERE name = 'Pediatric Ward');

INSERT INTO beds (ward_id, bed_number, status) VALUES
(@general, 'G-1', 'free'), (@general, 'G-2', 'free'), (@general, 'G-3', 'free'), (@general, 'G-4', 'free'),
(@icu,     'I-1', 'free'), (@icu,     'I-2', 'free'), (@icu,     'I-3', 'free'),
(@mat,     'M-1', 'free'), (@mat,     'M-2', 'free'), (@mat,     'M-3', 'free'),
(@ped,     'P-1', 'free'), (@ped,     'P-2', 'free'), (@ped,     'P-3', 'free');

-- -----------------------------------------------------------------
-- Nurse ward assignment — Alemitu covers General Ward and ICU
-- -----------------------------------------------------------------
INSERT INTO nurses (user_id) VALUES ((SELECT id FROM users WHERE email = 'nurse@hospital.com'));
INSERT INTO nurse_ward_assignments (nurse_id, ward_id) VALUES
((SELECT n.id FROM nurses n JOIN users u ON u.id = n.user_id WHERE u.email = 'nurse@hospital.com'), @general),
((SELECT n.id FROM nurses n JOIN users u ON u.id = n.user_id WHERE u.email = 'nurse@hospital.com'), @icu);

-- -----------------------------------------------------------------
-- Pharmacy inventory (Ibuprofen and Ciprofloxacin are below threshold)
-- -----------------------------------------------------------------
INSERT INTO medicines (name, category, stock_quantity, unit_price, expiry_date, supplier, low_stock_threshold) VALUES
('Paracetamol 500mg',   'Analgesic',      120, 25.00, '2028-06-30', 'Ethio Pharma', 20),
('Amoxicillin 250mg',   'Antibiotic',      60, 45.00, '2027-12-31', 'Ethio Pharma', 15),
('Ibuprofen 400mg',     'Analgesic',       18, 30.00, '2028-03-31', 'Addis Pharm',  20),
('Metformin 500mg',     'Antidiabetic',    80, 35.00, '2029-01-31', 'Addis Pharm',  15),
('Ciprofloxacin 500mg', 'Antibiotic',       7, 90.00, '2027-08-31', 'Best Med',     10),
('Loratadine 10mg',     'Antihistamine',   40, 28.00, '2028-11-30', 'Best Med',     10);
