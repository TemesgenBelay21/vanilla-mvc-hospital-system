-- ==============================================================
-- Hospital Management System — Phase 1 schema
-- Database: hospital_management_db
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `hospital_management_db`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE `hospital_management_db`;

-- ---------------------------------------------------------------
-- Users (all logins: admin, doctor, receptionist, patient)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role           ENUM('admin','doctor','receptionist','patient') NOT NULL,
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