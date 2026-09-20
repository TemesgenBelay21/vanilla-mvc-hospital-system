-- ==============================================================
-- Phase 3 migration — schema only: accountant role, billing /
-- invoicing, payments (Telebirr), in-app notifications.
--
-- NOTE: The full consolidated baseline lives in database/schema.sql;
-- this migration upgrades an existing Phase 1+2 database. Demo data
-- for Phase 3 lives in database/seed.sql.
-- ==============================================================

-- ---------------------------------------------------------------
-- Extend the users role enum with the accountant role
-- ---------------------------------------------------------------
ALTER TABLE users
    MODIFY role ENUM('admin','doctor','receptionist','patient','nurse','pharmacist','lab_technician','accountant') NOT NULL;

-- ---------------------------------------------------------------
-- Invoices
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS invoices (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(30) NOT NULL,
    patient_id     INT UNSIGNED NOT NULL,
    status         ENUM('unpaid','partially_paid','paid') NOT NULL DEFAULT 'unpaid',
    subtotal       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_amount   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    amount_paid    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    due_amount     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    generated_by   INT UNSIGNED NOT NULL,
    notes          TEXT DEFAULT NULL,
    generated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_invoices_number (invoice_number),
    KEY idx_invoices_patient (patient_id),
    KEY idx_invoices_status (status),
    CONSTRAINT fk_invoices_patient      FOREIGN KEY (patient_id)    REFERENCES patients(id) ON DELETE CASCADE,
    CONSTRAINT fk_invoices_generated_by FOREIGN KEY (generated_by)  REFERENCES users(id)    ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Invoice line items
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS invoice_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id  INT UNSIGNED NOT NULL,
    description VARCHAR(255) NOT NULL,
    quantity    DECIMAL(8,2) NOT NULL DEFAULT 1.00,
    unit_price  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    line_total  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    source_type ENUM('consultation','lab','medicine','ward') NOT NULL,
    source_id   INT UNSIGNED DEFAULT NULL,
    UNIQUE KEY uq_invoice_item_source (source_type, source_id),
    KEY idx_items_invoice (invoice_id),
    CONSTRAINT fk_items_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Payments
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payments (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id      INT UNSIGNED NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    method          ENUM('cash','telebirr') NOT NULL,
    status          ENUM('pending','success','failed') NOT NULL DEFAULT 'success',
    reference       VARCHAR(120) DEFAULT NULL,
    paid_by         INT UNSIGNED DEFAULT NULL,
    transaction_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    telebirr_txn_no VARCHAR(120) DEFAULT NULL,
    callback_payload TEXT DEFAULT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_payments_invoice (invoice_id),
    KEY idx_payments_method (method),
    CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    CONSTRAINT fk_payments_paid_by FOREIGN KEY (paid_by)    REFERENCES users(id)     ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- In-app notifications
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    title      VARCHAR(150) NOT NULL,
    message    TEXT DEFAULT NULL,
    link       VARCHAR(255) DEFAULT NULL,
    is_read    TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_notifications_user (user_id, is_read),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;