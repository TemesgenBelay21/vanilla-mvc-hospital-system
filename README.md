# Almaz General Hospital — Vanilla PHP MVC System

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

**Phase 3 — billing, payments & communications**
- Accountant role: invoice generation, invoice ledger, cash payment, payment history
- Invoicing engine that bills outstanding services (consultations, lab tests, ward days) once admission is discharged
- Patient online payment via **Telebirr** (sandbox simulation for local development + live signing when configured)
- Signed webhook callback that credits invoices idempotently and notifies patient + accountant
- Email notifications via [PHPMailer](https://github.com/PHPMailer/PHPMailer) (account activation, appointment
  approvals/reschedules, admission/discharge, lab results, dispensings, payment confirmations)
- In-app notification centre accessible from every role's top bar (read / mark-all-read)
- Admin reports dashboard (revenue, patient/doctor/bed stats) rendered with Chart.js

## Setup

1. Copy this folder into `C:/xampp/htdocs/` (or any PHP web root).
2. Install PHP dependencies once (**PHPMailer**):

   ```bash
   composer install
   ```

3. Create and seed the database in one pass (`schema.sql` creates the database
   if it does not exist, then builds every table):

   ```bash
   mysql -u root < database/schema.sql
   mysql -u root hospital_management_db < database/seed.sql
   ```

   `schema.sql` is the single consolidated schema baseline (Phases 1–3 tables);
   all demo data lives in `seed.sql`. `database/migrations/phase2.sql` and
   `database/migrations/phase3.sql` are kept only as upgrade migrations for
   existing databases from earlier phases.
4. Check `config/config.php` / `config/database.php` for your DB credentials (default: `root`, no password).
5. Start PHP's built-in server from the project `public/` directory:

   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

6. Open `http://127.0.0.1:8000`.

## Email & Telebirr configuration

Both integrations are **optional in sandbox/local mode** — the app works out of the box with no extra config.

- **Email**: the app deliberately no-ops sending until `MAIL_HOST`, `MAIL_PORT`, `MAIL_USER` and `MAIL_PASS`
  are set in `config/config.php`. Use your SMTP credentials there (works with Gmail/Outlook app passwords).
- **Telebirr**: `TELEBIRR_MODE` defaults to `sandbox`, in which payments go through the bundled in-app
  simulation page (no real money, no external calls). For production set `TELEBIRR_MODE=live`,
  `TELEBIRR_APP_KEY`, `TELEBIRR_SECRET`, `TELEBIRR_APP_ID`, `TELEBIRR_SHORT_CODE` and `TELEBIRR_API_BASE`
  to your registered merchant values.

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
| Accountant     | `accountant@hospital.com`    | `accountant123`    |

## Routes

- Admin: `/admin`, patients, wards, admissions, medicines, staff management
- Doctor: `/doctor` dashboard, `/doctor/patients` (care list), appointments, availability, `/lab/files/{id}/download`
- Receptionist: `/receptionist/patients`, appointments booking
- Patient: `/patient` dashboard, `/patient/book`, `/patient/appointments`, `/patient/medical`
- Nurse: `/nurse` dashboard, `/nurse/wards`, `/nurse/admissions`
- Pharmacist: `/pharmacist/dispense`, `/pharmacist/medicines`
- Lab technician: `/lab/requests`
- Accountant: `/accountant` dashboard, `/accountant/invoices`, `/accountant/invoices/generate`,
  `/accountant/payments`
- Admin: `/admin` dashboard, `/admin/reports`
- Patient billing: `/patient/invoices` (view + pay outstanding invoices)
- Notifications: `/notifications` (JSON feed), `/notifications/mark-all-read`

State-changing POST routes require a session CSRF token (rendered automatically by `csrf_field()`).
The Telebirr callback `/payment/telebirr/webhook` is intentionally public — it is guarded by the
HMAC/RSA signature verification inside `services/TelebirrPaymentService.php`.
Lab result files are stored under `storage/lab/` (gitignored) and only reachable through the guarded
`/lab/files/{id}/download` route — never served statically.