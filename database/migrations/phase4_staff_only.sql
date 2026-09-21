-- ==============================================================
-- Phase 4 correction migration — remove the Patient role / portal.
--
-- The hospital system is internal and staff-only: patients are purely
-- data records and never authenticate. This migration converts the old
-- "patient = a users row with role 'patient'" model into a standalone
-- patients table, then removes the patient role from users.
--
-- The full consolidated baseline lives in database/schema.sql; run
-- schema.sql + seed.sql for a fresh install. This migration upgrades an
-- existing Phase 1–3 database.
--
-- IMPORTANT ORDER: patient name/email/phone are copied out of users and
-- the patients.user_id foreign key is dropped BEFORE deleting patient
-- users, otherwise ON DELETE CASCADE would erase the patient records.
-- ==============================================================

-- ---------------------------------------------------------------
-- 1. Add (nullable) contact columns to the standalone patients table
-- ---------------------------------------------------------------
ALTER TABLE patients
    ADD COLUMN name  VARCHAR(100) DEFAULT NULL AFTER id,
    ADD COLUMN email VARCHAR(150) DEFAULT NULL AFTER name,
    ADD COLUMN phone VARCHAR(30)  DEFAULT NULL AFTER email;

-- ---------------------------------------------------------------
-- 2. Backfill contact details from the linked patient user account
-- ---------------------------------------------------------------
UPDATE patients p
    JOIN users u ON u.id = p.user_id
SET p.name  = u.name,
    p.email = u.email,
    p.phone = u.phone;

-- Fallback for any orphaned rows (should not normally happen)
UPDATE patients SET name = CONCAT('Patient #', id) WHERE name = '' OR name IS NULL;

-- ---------------------------------------------------------------
-- 3. Enforce name and detach patients from users
--              (drop FK, unique key, user_id column)
-- ---------------------------------------------------------------
ALTER TABLE patients MODIFY name VARCHAR(100) NOT NULL;
ALTER TABLE patients DROP FOREIGN KEY fk_patients_user;
ALTER TABLE patients DROP INDEX uq_patients_user;
ALTER TABLE patients DROP COLUMN user_id;

-- ---------------------------------------------------------------
-- 4. Delete now-redundant patient login accounts
-- ---------------------------------------------------------------
DELETE FROM users WHERE role = 'patient';

-- ---------------------------------------------------------------
-- 5. Remove the patient role from the users enum (staff-only)
-- ---------------------------------------------------------------
ALTER TABLE users
    MODIFY role ENUM('admin','doctor','receptionist','nurse','pharmacist','lab_technician','accountant') NOT NULL;
