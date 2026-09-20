<?php

/**
 * AccountantController — billing records, invoice generation and
 * offline (cash) payment reconciliation for the accountant role.
 */

declare(strict_types=1);

class AccountantController
{
    public function index(): void
    {
        require_role('accountant');

        $status = trim($_GET['status'] ?? '');
        $q      = trim($_GET['q'] ?? '');
        $from   = trim($_GET['from'] ?? '');
        $to     = trim($_GET['to'] ?? '');

        view('accountant/invoices/index', [
            'invoices'   => Invoice::all($status, $q, $from, $to),
            'statuses'   => Invoice::STATUSES,
            'status'     => $status,
            'q'          => $q,
            'from'       => $from,
            'to'         => $to,
            'stats'      => [
                'total'     => Invoice::countOf(),
                'unpaid'    => Invoice::countByStatus('unpaid'),
                'partial'   => Invoice::countByStatus('partially_paid'),
                'paid'      => Invoice::countByStatus('paid'),
                'outstanding' => Invoice::outstandingTotal(),
            ],
        ]);
    }

    public function show(array $params): void
    {
        require_role('accountant');

        $invoice = Invoice::findById((int) $params['id']);
        if ($invoice === false) {
            flash('error', 'Invoice not found.');
            redirect('/accountant/invoices');
        }

        view('accountant/invoices/show', [
            'invoice'  => $invoice,
            'items'    => Invoice::items((int) $invoice['id']),
            'payments' => Invoice::payments((int) $invoice['id']),
        ]);
    }

    public function generateForm(): void
    {
        require_role('accountant');
        view('accountant/invoices/generate', [
            'patients' => Patient::search(''),
        ]);
    }

    public function generate(): void
    {
        $user = require_role('accountant');
        csrf_check();

        $patientId = (int) ($_POST['patient_id'] ?? 0);
        $notes     = trim($_POST['notes'] ?? '') ?: null;

        $result = BillingService::createInvoiceForPatient($patientId, (int) $user['id'], $notes);

        if (!$result['ok']) {
            flash('error', $result['message']);
            redirect('/accountant/invoices/generate');
        }

        flash('success', 'Invoice generated. Outstanding services for this patient have been billed.');
        redirect('/accountant/invoices/' . $result['invoice_id']);
    }

    public function payCash(array $params): void
    {
        $user = require_role('accountant');
        csrf_check();

        $invoiceId = (int) $params['id'];
        $amount    = (float) ($_POST['amount'] ?? 0);
        $reference = trim($_POST['reference'] ?? '') ?: null;

        $invoice   = Invoice::findById($invoiceId);
        $result = BillingService::recordPayment($invoiceId, $amount, 'cash', (int) $user['id'], $reference);

        if (!$result['ok']) {
            flash('error', $result['message']);
            redirect('/accountant/invoices/' . $invoiceId);
        }

        if ($invoice !== false) {
            $patient = Patient::findById((int) $invoice['patient_id']);
            if ($patient !== false) {
                notify_and_mail((int) $patient['user_id'], 'Payment received',
                    'Your payment of ' . number_format($amount, 2) . ' ETB for invoice ' . $invoice['invoice_number'] . ' was received at the cashier.',
                    '/patient/invoices', 'payment-confirmed', [
                        'invoice_number' => $invoice['invoice_number'],
                        'amount'         => number_format($amount, 2),
                        'reference'      => $reference ?: 'Cash receipt',
                    ]);
            }
        }

        flash('success', 'Cash payment of ' . number_format($amount, 2) . ' ETB recorded.');
        redirect('/accountant/invoices/' . $invoiceId);
    }

    public function payments(): void
    {
        require_role('accountant');
        view('accountant/payments/index', [
            'payments' => Payment::recent(50),
        ]);
    }
}