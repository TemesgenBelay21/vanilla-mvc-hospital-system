# Vanilla PHP MVC Hospital Management System

A dependency-free MVC hospital management system (PHP 7.4 + MySQL/MariaDB + vanilla JS/CSS).

## Features

**Phase 1 — core operations**
- Role-based portal: Admin, Doctor, Receptionist, Patient
- Self-registration, bookings, doctor availability & appointment lifecycle (approve / reject / reschedule)
- Patient registration by receptionist, doctor directories

**Phase 2 — clinical care**
- Ward & bed management, admissions, discharge, and vitals tracking (Admin + Nurse)
- Nurse ward assignment, doctor "My Patients" care list
- Prescriptions against the medicine inventory; pharmacist dispensing with live stock control
- Medicine inventory CRUD, restocking and low-stock alerts (Admin + Pharmacist)
- Laboratory request workflow (doctor) → processing (lab technician) with result text/file upload
- Guarded result file download (admin / lab tech / doctor in care / patient self)
- Role dashboards with live stats; patient medical portal (admissions, vitals, medications, lab results)

## Setup

1. Copy this folder into `C:/xampp/htdocs/` (or any PHP web root).
2. Create and seed the database in one pass (`schema.sql` creates the database
   if it does not exist, then builds every table):

   ```bash
   mysql -u root < database/schema.sql
   mysql -u root hospital_management_db < database/seed.sql
   ```

   `schema.sql` is the single consolidated schema baseline (Phase 1 + Phase 2 tables);
   all demo data lives in `seed.sql`. `database/migrations/phase2.sql` is kept only as
   an upgrade migration for existing Phase 1 databases.
3. Check `config/config.php` / `config/database.php` for your DB credentials (default: `root`, no password).
4. Start PHP's built-in server from the project `public/` directory:

   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

5. Open `http://127.0.0.1:8000`.

## Demo accounts

| Role           | Email                        | Password           |
|----------------|------------------------------|--------------------|
| Admin          | `admin@hospital.com`         | `admin123`         |
| Doctor         | `doctor@hospital.com`        | `doctor123`        |
| Receptionist   | `receptionist@hospital.com`  | `reception123`     |
| Patient        | `patient@hospital.com`       | `patient123`       |
| Nurse          | `nurse@hospital.com`         | `nurse123`         |
| Pharmacist     | `pharmacist@hospital.com`    | `pharmacist123`    |
| Lab technician | `lab@hospital.com`           | `lab123`           |

## Routes

- Admin: `/admin`, patients, wards, admissions, medicines, staff management
- Doctor: `/doctor` dashboard, `/doctor/patients` (care list), appointments, availability, `/lab/files/{id}/download`
- Receptionist: `/receptionist/patients`, appointments booking
- Patient: `/patient` dashboard, `/patient/book`, `/patient/appointments`, `/patient/medical`
- Nurse: `/nurse` dashboard, `/nurse/wards`, `/nurse/admissions`
- Pharmacist: `/pharmacist/dispense`, `/pharmacist/medicines`
- Lab technician: `/lab/requests`

State-changing POST routes require a session CSRF token (rendered automatically by `csrf_field()`).
Lab result files are stored under `storage/lab/` (gitignored) and only reachable through the guarded
`/lab/files/{id}/download` route — never served statically.