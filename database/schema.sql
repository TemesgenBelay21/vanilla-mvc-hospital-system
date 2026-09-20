-- ==============================================================
-- Hospital Management System — Phase 1 + Phase 2 schema
-- Database: hospital_management_db
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `hospital_management_db`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- Users (roles: admin, doctor, receptionist, patient, nurse,
--            pharmacist, lab_technician)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role           ENUM('admin','doctor','receptionist','patient','nurse','pharmacist','lab_technician') NOT NULL,
    name           VARCHAR(100) NOT NULL,
    email          VARCHAR(150) NOT NULL,
    password       VARCHAR(255) NOT NULL,
    phone          VARCHAR(30)  DEFAULT NULL,
    photo          VARCHAR(255) DEFAULT NULL,
    status         ENUM('active','inactive') NOT NULL DEFAULT 'active',
    last_login_at  DATETIME DEFAULT NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Departments
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS departments (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_departments_name (name)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Doctors (profile details tied to a users row with role 'doctor')
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS doctors (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED NOT NULL,
    department_id  INT UNSIGNED NOT NULL,
    specialization VARCHAR(150) NOT NULL,
    qualification  VARCHAR(255) DEFAULT NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_doctors_user (user_id),
    CONSTRAINT fk_doctors_user       FOREIGN KEY (user_id)       REFERENCES users(id)       ON DELETE CASCADE,
    CONSTRAINT fk_doctors_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Patients (medical details tied to a users row with role 'patient')
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS patients (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id                 INT UNSIGNED NOT NULL,
    date_of_birth           DATE DEFAULT NULL,
    gender                  ENUM('male','female','other') DEFAULT NULL,
    address                 VARCHAR(255) DEFAULT NULL,
    emergency_contact_name  VARCHAR(100) DEFAULT NULL,
    emergency_contact_phone VARCHAR(30)  DEFAULT NULL,
    blood_type              ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
    allergies               TEXT DEFAULT NULL,
    created_at              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_patients_user (user_id),
    CONSTRAINT fk_patients_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Doctor weekly availability (one row = one fixed 30-minute slot)
-- day_of_week: 0 = Sunday ... 6 = Saturday
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS availabilities (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    doctor_id   INT UNSIGNED NOT NULL,
    day_of_week TINYINT NOT NULL,
    start_time  TIME NOT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_availability (doctor_id, day_of_week, start_time),
    CONSTRAINT fk_availability_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Appointments
-- Unique (doctor_id, appointment_date, start_time) prevents
-- double-booking at the database level.
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS appointments (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id       INT UNSIGNED NOT NULL,
    doctor_id        INT UNSIGNED NOT NULL,
    department_id    INT UNSIGNED NOT NULL,
    appointment_date DATE NOT NULL,
    start_time       TIME NOT NULL,
    end_time         TIME NOT NULL,
    status           ENUM('pending','approved','rejected','rescheduled','completed')
                     NOT NULL DEFAULT 'pending',
    patient_notes    TEXT DEFAULT NULL,
    staff_notes      TEXT DEFAULT NULL,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_appointment_slot (doctor_id, appointment_date, start_time),
    KEY idx_appointments_date (appointment_date),
    CONSTRAINT fk_appointments_patient     FOREIGN KEY (patient_id)    REFERENCES patients(id)    ON DELETE CASCADE,
    CONSTRAINT fk_appointments_doctor      FOREIGN KEY (doctor_id)     REFERENCES doctors(id)     ON DELETE RESTRICT,
    CONSTRAINT fk_appointments_department  FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
-- ==============================================================
-- Phase 2 tables — wards, admissions, pharmacy and laboratory
-- ==============================================================

-- ---------------------------------------------------------------
-- Wards and beds
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
-- Nurses (profile row linking a nurse user to their wards)
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
-- Admissions (one active admission per bed enforced by unique key)
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
    active_flag       TINYINT GENERATED ALWAYS AS (IF(status = 'admitted', 1, NULL)) STORED,
    discharge_date    DATETIME DEFAULT NULL,
    discharge_notes   TEXT DEFAULT NULL,
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_admissions_bed_active (bed_id, active_flag),
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
-- Pharmacy inventory and prescriptions
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
-- Laboratory requests
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
