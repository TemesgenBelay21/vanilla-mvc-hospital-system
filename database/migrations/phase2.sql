-- ==============================================================
-- Phase 2 migration — run against a Phase 1 database.
-- Adds nurse / pharmacist / lab_technician roles and the
-- admissions, ward, vitals, pharmacy and lab schema.
-- ==============================================================
USE `hospital_management_db`;

-- ---------------------------------------------------------------
-- 1. Extend the users role enum
-- ---------------------------------------------------------------
ALTER TABLE users
    MODIFY role ENUM('admin','doctor','receptionist','patient','nurse','pharmacist','lab_technician') NOT NULL;

-- ---------------------------------------------------------------
-- 2. Wards and beds
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS wards (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_wards_name (name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS beds (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ward_id     INT UNSIGNED NOT NULL,
    bed_number  VARCHAR(20) NOT NULL,
    status      ENUM('free','occupied') NOT NULL DEFAULT 'free',
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_beds_ward_number (ward_id, bed_number),
    KEY idx_beds_status (status),
    CONSTRAINT fk_beds_ward FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- 3. Nurses — profile row linking a nurse user to the wards charge
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS nurses (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_nurses_user (user_id),
    CONSTRAINT fk_nurses_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS nurse_ward_assignments (
    nurse_id    INT UNSIGNED NOT NULL,
    ward_id     INT UNSIGNED NOT NULL,
    PRIMARY KEY (nurse_id, ward_id),
    CONSTRAINT fk_nwa_nurse FOREIGN KEY (nurse_id) REFERENCES nurses(id) ON DELETE CASCADE,
    CONSTRAINT fk_nwa_ward  FOREIGN KEY (ward_id)  REFERENCES wards(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- 4. Admissions and vitals
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admissions (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id        INT UNSIGNED NOT NULL,
    ward_id           INT UNSIGNED NOT NULL,
    bed_id            INT UNSIGNED NOT NULL,
    admitting_doctor_id INT UNSIGNED DEFAULT NULL,
    admission_reason  TEXT DEFAULT NULL,
    admission_date    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status            ENUM('admitted','discharged') NOT NULL DEFAULT 'admitted',
    discharge_date    DATETIME DEFAULT NULL,
    discharge_notes   TEXT DEFAULT NULL,
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_admissions_bed_active (bed_id, status),
    KEY idx_admissions_patient (patient_id),
    KEY idx_admissions_status (status),
    CONSTRAINT fk_admissions_patient  FOREIGN KEY (patient_id)          REFERENCES patients(id) ON DELETE CASCADE,
    CONSTRAINT fk_admissions_ward     FOREIGN KEY (ward_id)             REFERENCES wards(id)    ON DELETE RESTRICT,
    CONSTRAINT fk_admissions_bed      FOREIGN KEY (bed_id)              REFERENCES beds(id)     ON DELETE RESTRICT,
    CONSTRAINT fk_admissions_doctor   FOREIGN KEY (admitting_doctor_id) REFERENCES doctors(id)  ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vitals (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admission_id     INT UNSIGNED NOT NULL,
    recorded_by      INT UNSIGNED DEFAULT NULL,
    temperature      DECIMAL(4,1) DEFAULT NULL,
    systolic         TINYINT UNSIGNED DEFAULT NULL,
    diastolic        TINYINT UNSIGNED DEFAULT NULL,
    heart_rate       TINYINT UNSIGNED DEFAULT NULL,
    notes            TEXT DEFAULT NULL,
    recorded_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vitals_admission FOREIGN KEY (admission_id) REFERENCES admissions(id) ON DELETE CASCADE,
    CONSTRAINT fk_vitals_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    KEY idx_vitals_admission (admission_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- 5. Pharmacy inventory and prescriptions
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS medicines (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name               VARCHAR(150) NOT NULL,
    category           VARCHAR(100) DEFAULT NULL,
    stock_quantity     INT NOT NULL DEFAULT 0,
    unit_price         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    expiry_date        DATE DEFAULT NULL,
    supplier           VARCHAR(150) DEFAULT NULL,
    low_stock_threshold INT NOT NULL DEFAULT 10,
    created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_medicines_name (name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS prescriptions (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id     INT UNSIGNED NOT NULL,
    doctor_id      INT UNSIGNED NOT NULL,
    medicine_id    INT UNSIGNED NOT NULL,
    dosage         VARCHAR(100) NOT NULL,
    frequency      VARCHAR(100) NOT NULL,
    duration       VARCHAR(100) NOT NULL,
    quantity       INT NOT NULL DEFAULT 1,
    notes          TEXT DEFAULT NULL,
    status         ENUM('pending','dispensed') NOT NULL DEFAULT 'pending',
    prescribed_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    dispensed_at   DATETIME DEFAULT NULL,
    dispensed_by   INT UNSIGNED DEFAULT NULL,
    CONSTRAINT fk_prescriptions_patient   FOREIGN KEY (patient_id)  REFERENCES patients(id) ON DELETE CASCADE,
    CONSTRAINT fk_prescriptions_doctor    FOREIGN KEY (doctor_id)   REFERENCES doctors(id)  ON DELETE RESTRICT,
    CONSTRAINT fk_prescriptions_medicine  FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE RESTRICT,
    CONSTRAINT fk_prescriptions_dispensed_by FOREIGN KEY (dispensed_by) REFERENCES users(id) ON DELETE SET NULL,
    KEY idx_prescriptions_status (status),
    KEY idx_prescriptions_patient (patient_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- 6. Laboratory requests
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS lab_requests (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id     INT UNSIGNED NOT NULL,
    doctor_id      INT UNSIGNED NOT NULL,
    test_name      VARCHAR(150) NOT NULL,
    notes          TEXT DEFAULT NULL,
    priority       ENUM('normal','urgent') NOT NULL DEFAULT 'normal',
    status         ENUM('requested','in_progress','completed') NOT NULL DEFAULT 'requested',
    result_text    TEXT DEFAULT NULL,
    result_file    VARCHAR(255) DEFAULT NULL,
    requested_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    started_at     DATETIME DEFAULT NULL,
    completed_at   DATETIME DEFAULT NULL,
    completed_by   INT UNSIGNED DEFAULT NULL,
    CONSTRAINT fk_lab_patient   FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    CONSTRAINT fk_lab_doctor    FOREIGN KEY (doctor_id)  REFERENCES doctors(id)  ON DELETE RESTRICT,
    CONSTRAINT fk_lab_completed_by FOREIGN KEY (completed_by) REFERENCES users(id) ON DELETE SET NULL,
    KEY idx_lab_status (status),
    KEY idx_lab_patient (patient_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- 7. Seed — demo staff accounts for the new roles
-- ---------------------------------------------------------------
INSERT INTO users (role, name, email, password, phone) VALUES
('nurse',          'Alemitu Bekele',      'nurse@hospital.com',      '$2y$10$43IzIcnwQ4UNtnOKJvWy3uQWocUmlJ67mnMTIZok9u6Ysd2DRkbCS', '+251 911 000 555'),
('pharmacist',     'Daniel Girma',        'pharmacist@hospital.com', '$2y$10$GciBx2Rdnpm0jOYqklUCBefOUY3ZBmzfmyzli.sKzsyRVdrwjGvtu', '+251 911 000 666'),
('lab_technician', 'Feven Tadesse',       'lab@hospital.com',        '$2y$10$nYScZ34nuHnw9INXca1EOuy6KE0ZbQ1FTvvMK.OggnNEsRa7djazy', '+251 911 000 777');

-- ---------------------------------------------------------------
-- 8. Seed — wards and beds
-- ---------------------------------------------------------------
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

-- ---------------------------------------------------------------
-- 9. Seed — assign nurse Alemitu to the General Ward and ICU
-- ---------------------------------------------------------------
INSERT INTO nurses (user_id) VALUES ((SELECT id FROM users WHERE email = 'nurse@hospital.com'));
INSERT INTO nurse_ward_assignments (nurse_id, ward_id) VALUES
((SELECT n.id FROM nurses n JOIN users u ON u.id = n.user_id WHERE u.email = 'nurse@hospital.com'), @general),
((SELECT n.id FROM nurses n JOIN users u ON u.id = n.user_id WHERE u.email = 'nurse@hospital.com'), @icu);

-- ---------------------------------------------------------------
-- 10. Seed — pharmacy inventory (one item below low-stock threshold)
-- ---------------------------------------------------------------
INSERT INTO medicines (name, category, stock_quantity, unit_price, expiry_date, supplier, low_stock_threshold) VALUES
('Paracetamol 500mg',   'Analgesic',      120, 25.00, '2028-06-30', 'Ethio Pharma', 20),
('Amoxicillin 250mg',   'Antibiotic',      60, 45.00, '2027-12-31', 'Ethio Pharma', 15),
('Ibuprofen 400mg',     'Analgesic',       18, 30.00, '2028-03-31', 'Addis Pharm',  20),
('Metformin 500mg',     'Antidiabetic',    80, 35.00, '2029-01-31', 'Addis Pharm',  15),
('Ciprofloxacin 500mg', 'Antibiotic',       7, 90.00, '2027-08-31', 'Best Med',     10),
('Loratadine 10mg',     'Antihistamine',   40, 28.00, '2028-11-30', 'Best Med',     10);