-- ==============================================================
-- Hospital Management System — Phase 1 seed data
-- Demo logins (all passwords are the plain "…123" strings):
--   Admin        admin@hospital.com      / admin123
--   Doctor       doctor@hospital.com     / doctor123
--   Receptionist receptionist@hospital.com / reception123
--   Patient      patient@hospital.com    / patient123
-- ==============================================================
USE `hospital_management_db`;

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