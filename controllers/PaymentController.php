<?php

/**
 * PaymentController — patient-side invoice payment via Telebirr,
 * the sandbox gateway simulation page, the status-poll fallback and the
 * public signature-verified webhook.
 */

declare(strict_types=1);

class PaymentController
{
    public function patientInvoices(): void
    {
        $user    = require_role('patient');
        $patient = Patient::findByUserId((int) $user['id']);

        $invoices = $patient === false ? [] : Invoice::forPatient((int) $user['id']);

        view('patient/invoices', [
            'invoices' => $invoices,
            'outstanding' => Invoice::outstandingFor((int) $user['id']),
        ]);
    }

    /** POST: begin a Telebirr payment for one of the patient's invoices. */
    public function initiate(array $params): void
    {
        $user = require_role('patient');
        csrf_check();

        $invoiceId = (int) $params['id'];
        $result = TelebirrPaymentService::initiate($invoiceId, (int) $user['id']);

        if (!$result['ok']) {
            flash('error', $result['message']);
            redirect('/patient/invoices');
        }

        if ($result['redirect'] !== null) {
            redirect($result['redirect']);
        }
        flash('info', 'Payment initialized.');
        redirect('/patient/invoices');
    }

    /**
     * GET: Telebirr redirect destination. In sandbox mode this is our local
     * "gateway" page that simulates the Telebirr payment app; in live mode
     * the user is redirected straight to the real toPayUrl before reaching here.
     */
    public function gateway(array $params = []): void
    {
        $user    = require_role('patient');
        $patient = Patient::findByUserId((int) $user['id']);

        $invoiceId = (int) ($_GET['invoice'] ?? 0);
        $outTradeNo = trim((string) ($_GET['o'] ?? ''));
        $token      = trim((string) ($_GET['tok'] ?? ''));

        $invoice = Invoice::findById($invoiceId);
        if (
            TelebirrPaymentService::isSandbox()
            && ($invoice === false
                || $patient === false || (int) $invoice['patient_id'] !== (int) $patient['id']
                || !TelebirrPaymentService::sandboxTokenValid($outTradeNo, $token))
        ) {
            flash('error', 'Invalid payment link.');
            redirect('/patient/invoices');
        }

        view('patient/telebirr-pay', [
            'invoice'   => $invoice,
            'isSandbox' => TelebirrPaymentService::isSandbox(),
            'sandbox'   => TelebirrPaymentService::isSandbox() ? $this->sandboxCallback($invoice, $outTradeNo) : null,
            'verifyUrl' => '/patient/telebirr/verify?invoice=' . $invoiceId,
        ]);
    }

    /** GET: manual status-pull fallback when the webhook did not arrive. */
    public function verifyStatus(): void
    {
        require_role('patient');

        $invoiceId = (int) ($_GET['invoice'] ?? 0);
        $invoice   = Invoice::findById($invoiceId);
        $patient   = Patient::findByUserId((int) current_user()['id']);
        if ($invoice === false || $patient === false || (int) $invoice['patient_id'] !== (int) $patient['id']) {
            flash('error', 'Invoice not found.');
            redirect('/patient/invoices');
        }

        $result = TelebirrPaymentService::verifyStatus($invoiceId);
        flash($result['paid'] ? 'success' : 'info', $result['message']);
        redirect('/patient/invoices');
    }

    /**
     * POST: public Telebirr callback. Real Telebirr posts a signed JSON payload;
     * our sandbox gateway posts an equally signed simulation payload. The
     * signature is ALWAYS verified before any state change.
     */
    public function webhook(): void
    {
        $payload = $this->readCallbackPayload();
        $result  = TelebirrPaymentService::handleWebhook($payload);

        http_response_code($result['code']);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['code' => (string) $result['code'], 'message' => $result['message']]);
        exit;
    }

    /** Sandbox-only: signed simulation callback the gateway page submits. */
    private function sandboxCallback(array $invoice, string $outTradeNo): array
    {
        $key = TelebirrPaymentService::sandboxKey();

        $nonce      = bin2hex(random_bytes(16));
        $payTime    = date('Y-m-d H:i:s');
        $totalAmount = (string) round((float) $invoice['due_amount'] * 100);
        $status     = 'success';

        $payload = [
            'appId'       => TELEBIRR_APP_ID,
            'nounce'      => $nonce,
            'outTradeNo'  => $outTradeNo,
            'payTime'     => $payTime,
            'totalAmount' => $totalAmount,
            'status'      => $status,
            'txnNo'       => 'SANDBOX_' . bin2hex(random_bytes(6)),
        ];
        $payload['hmacs'] = hash_hmac('sha256', $nonce, $key);
        $payload['sign']  = hash_hmac('sha256', $nonce . '|' . $outTradeNo . '|' . $payTime . '|' . $totalAmount . '|' . $status, $key);

        return $payload;
    }

    /** Normalizes Telebirr / sandbox callback bodies into a flat array. */
    private function readCallbackPayload(): array
    {
        $raw = file_get_contents('php://input');

        if (!isset($_POST['data']) && strlen((string) $raw) > 0) {
            $decoded = json_decode((string) $raw, true);
            if (is_array($decoded)) {
                $_POST['data'] = $decoded;
            }
        }

        if (isset($_POST['data']) && is_string($_POST['data'])) {
            $decoded = json_decode($_POST['data'], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        if (isset($_POST['data']) && is_array($_POST['data'])) {
            return $_POST['data'];
        }
        return $_POST;
    }
}