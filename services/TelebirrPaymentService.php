<?php

/**
 * TelebirrPaymentService — isolated online-payment integration.
 *
 * Modes:
 *   - 'sandbox' (default): the full flow is simulated locally — the initiate
 *     step produces a local "gateway" page with a clearly-marked sandbox banner,
 *     and a simulated callback is POSTed to the SAME webhook endpoint that
 *     production Telebirr would call, so signature verification and the
 *     payment-recording path are exercised end to end. No network calls.
 *   - 'live': builds and signs a real Telebirr sendPay request (RSA signature
 *     over appId+timestamp+nonce+shortCode+subject+outTradeNo+totalAmount),
 *     POSTs it to TELEBIRR_API_BASE, parses toPayUrl, and verifies webhook
 *     callbacks with the Telebirr public key.
 *
 * All credentials are read from the environment (config/config.php) — never
 * hardcoded here.
 */

declare(strict_types=1);

class TelebirrPaymentService
{
    private const SANDBOX_SECRET = 'almaz_sandbox_dev_secret';

    public static function isSandbox(): bool
    {
        return TELEBIRR_MODE !== 'live';
    }

    /** The secret used to sign the local sandbox callback (mirrors appKey). */
    private static function secret(): string
    {
        return TELEBIRR_APP_KEY !== '' ? TELEBIRR_APP_KEY : self::SANDBOX_SECRET;
    }

    private static function nonce(int $len = 24): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $out = '';
        for ($i = 0; $i < $len; $i++) {
            $out .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $out;
    }

    // ------------------------------------------------------------------
    // Initiate
    // ------------------------------------------------------------------

    /**
     * Kick off payment for an invoice on behalf of a patient. Payment is
     * staff-mediated: the initiating accountant/receptionist becomes
     * payments.paid_by, never the patient (patients have no account).
     * @return array{ok:bool,redirect:string|null,out_trade_no:string|null,message:string}
     */
    public static function initiate(int $invoiceId, int $recordedByUserId, string $backPath): array
    {
        $invoice = Invoice::findById($invoiceId);
        if ($invoice === false) {
            return ['ok' => false, 'redirect' => null, 'out_trade_no' => null, 'message' => 'Invoice not found.'];
        }
        $due = (float) $invoice['due_amount'];
        if ($due <= 0) {
            return ['ok' => false, 'redirect' => null, 'out_trade_no' => null, 'message' => 'This invoice has no outstanding balance.'];
        }

        $outTradeNo = strtoupper('ALM' . date('ymdHis') . '-' . $invoiceId . '-' . random_int(1000, 9999));

        // Reserve a pending payment row; the webhook later marks it success.
        $paymentId = Payment::create([
            'invoice_id' => $invoiceId,
            'amount'     => $due,
            'method'     => 'telebirr',
            'status'     => 'pending',
            'reference'  => $outTradeNo,
            'paid_by'    => $recordedByUserId,
        ]);

        if (self::isSandbox()) {
            $token = hash_hmac('sha256', 'pay:' . $outTradeNo, self::secret());
            $redirect = url('/telebirr/pay?invoice=' . $invoiceId . '&o=' . urlencode($outTradeNo) . '&tok=' . $token);
        } else {
            $result = self::sendSendPayRequest($invoice, $outTradeNo, $backPath);
            if (!$result['ok']) {
                Payment::create([
                    'invoice_id' => $invoiceId, 'amount' => $due, 'method' => 'telebirr',
                    'status' => 'failed', 'reference' => $outTradeNo, 'paid_by' => $recordedByUserId,
                ]);
                return ['ok' => false, 'redirect' => null, 'out_trade_no' => $outTradeNo, 'message' => $result['message']];
            }
            $redirect = $result['toPayUrl'];
        }

        return ['ok' => true, 'redirect' => $redirect, 'out_trade_no' => $outTradeNo, 'message' => 'Payment initialized.'];
    }

    /**
     * Live: build a signed sendPay request and POST it to Telebirr.
     * @return array{ok:bool,toPayUrl:string|null,message:string}
     */
    private static function sendSendPayRequest(array $invoice, string $outTradeNo, string $backPath): array
    {
        if (TELEBIRR_APP_ID === '' || TELEBIRR_SHORT_CODE === '' || TELEBIRR_PRIVATE_KEY === '') {
            return ['ok' => false, 'toPayUrl' => null, 'message' => 'Telebirr live credentials (appId, shortCode, private key) are not configured.'];
        }

        $timestamp = date('YmdHis');
        $nonce     = self::nonce();
        $amount    = (string) round((float) $invoice['due_amount'] * 100); // cents

        // Canonical string signed per Telebirr merchant integration docs.
        $canonical = TELEBIRR_APP_ID . $timestamp . $nonce . TELEBIRR_SHORT_CODE . 'Invoice ' . $invoice['invoice_number'] . $outTradeNo . $amount;
        $signature = self::rsaSign($canonical, TELEBIRR_PRIVATE_KEY);
        if ($signature === null) {
            return ['ok' => false, 'toPayUrl' => null, 'message' => 'Could not sign the Telebirr request. Check the merchant private key.'];
        }

        $body = json_encode([
            'appId'        => TELEBIRR_APP_ID,
            'timestamp'    => $timestamp,
            'nonce'        => $nonce,
            'shortCode'    => TELEBIRR_SHORT_CODE,
            'subject'      => 'Invoice ' . $invoice['invoice_number'],
            'outTradeNo'   => $outTradeNo,
            'totalAmount'  => $amount,
            'notifyUrl'    => url('/payment/telebirr/webhook'),
            'returnUrl'    => url($backPath),
            'signature'    => $signature,
        ], JSON_UNESCAPED_SLASHES);

        $payload = base64_encode($body);
        $response = self::httpPost(TELEBIRR_API_BASE . '/api/epay-telebirr/api/epay/sendPay', $payload);
        if ($response === null) {
            return ['ok' => false, 'toPayUrl' => null, 'message' => 'Could not reach the Telebirr payment API.'];
        }

        $data = json_decode($response, true);
        if (!is_array($data) || empty($data['toPayUrl'])) {
            return ['ok' => false, 'toPayUrl' => null, 'message' => 'Telebirr did not return a payment URL.'];
        }
        return ['ok' => true, 'toPayUrl' => (string) $data['toPayUrl'], 'message' => 'ready'];
    }

    // ------------------------------------------------------------------
    // Webhook (callback) handling
    // ------------------------------------------------------------------

    /**
     * Verify and process a Telebirr callback. Returns a plain array for the
     * controller to emit; the signature is ALWAYS verified before any state
     * change and unverified callbacks are refused.
     *
     * @return array{ok:bool,code:int,message:string}
     */
    public static function handleWebhook(array $payload): array
    {
        $appId       = (string) ($payload['appId'] ?? '');
        $nounce      = (string) ($payload['nounce'] ?? $payload['nonce'] ?? '');
        $outTradeNo  = (string) ($payload['outTradeNo'] ?? '');
        $payTime     = (string) ($payload['payTime'] ?? '');
        $totalAmount = (string) ($payload['totalAmount'] ?? '');
        $status      = (string) ($payload['status'] ?? '');
        $sign        = (string) ($payload['sign'] ?? '');
        $hmac        = (string) ($payload['hmac'] ?? $payload['hmacs'] ?? '');

        if ($nounce === '' || $outTradeNo === '' || $sign === '') {
            return ['ok' => false, 'code' => 400, 'message' => 'Malformed callback payload.'];
        }

        if (!self::verifyCallbackSignature($nounce, $outTradeNo, $payTime, $totalAmount, $status, $sign, $hmac)) {
            return ['ok' => false, 'code' => 401, 'message' => 'Signature verification failed.'];
        }

        $payment = self::findPendingPayment($outTradeNo);
        if ($payment === false) {
            return ['ok' => false, 'code' => 404, 'message' => 'Unknown payment reference.'];
        }

        if ((string) $status !== 'success' && (string) $status !== '2' && (string) $status !== '0000') {
            Payment::create([
                'invoice_id' => (int) $payment['invoice_id'],
                'amount'     => (float) $payment['amount'],
                'method'     => 'telebirr',
                'status'     => 'failed',
                'reference'  => $outTradeNo,
                'paid_by'    => (int) $payment['paid_by'],
                'telebirr_txn_no' => $payload['txnNo'] ?? null,
                'callback_payload' => json_encode($payload),
            ]);
            return ['ok' => false, 'code' => 200, 'message' => 'Payment reported as not completed.'];
        }

        $amount = $totalAmount !== '' ? ((float) $totalAmount) / 100 : (float) $payment['amount'];

        $result = BillingService::recordPayment(
            (int) $payment['invoice_id'],
            $amount,
            'telebirr',
            (int) $payment['paid_by'],
            $outTradeNo,
            (string) ($payload['txnNo'] ?? $outTradeNo),
            json_encode($payload)
        );
        if (!$result['ok']) {
            return ['ok' => false, 'code' => 409, 'message' => $result['message']];
        }

        // Notify the patient (email only — patients have no portal) and the accountant.
        $invoice = Invoice::findById((int) $payment['invoice_id']);
        if ($invoice !== false) {
            $patient = Patient::findById((int) $invoice['patient_id']);
            if ($patient !== false) {
                mail_patient((int) $patient['id'], 'Payment confirmed',
                    'Your payment of ' . number_format($amount, 2) . ' ETB for invoice ' . $invoice['invoice_number'] . ' was received.',
                    'payment-confirmed', [
                        'invoice_number' => $invoice['invoice_number'],
                        'amount'         => number_format($amount, 2),
                        'reference'      => (string) ($payload['txnNo'] ?? $outTradeNo),
                    ]);
            }
            notify_user((int) $invoice['generated_by'], 'Payment received',
                'Invoice ' . $invoice['invoice_number'] . ' received a Telebirr payment of ' . number_format($amount, 2) . ' ETB.',
                '/accountant/invoices/' . (int) $invoice['id']);
        }

        return ['ok' => true, 'code' => 200, 'message' => 'Payment recorded.'];
    }

    private static function findPendingPayment(string $outTradeNo)
    {
        $stmt = db()->prepare(
            'SELECT * FROM payments WHERE reference = ? AND method = "telebirr" ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$outTradeNo]);
        return $stmt->fetch() ?: false;
    }

    private static function verifyCallbackSignature(string $nounce, string $outTradeNo, string $payTime, string $totalAmount, string $status, string $sign, string $hmac): bool
    {
        if (self::isSandbox()) {
            // Sandbox callback is signed with an HMAC of the shared secret.
            $expectedHmac  = hash_hmac('sha256', $nounce, self::secret());
            $expectedSign  = hash_hmac('sha256', $nounce . '|' . $outTradeNo . '|' . $payTime . '|' . $totalAmount . '|' . $status, self::secret());
            return hash_equals($expectedHmac, $hmac) && hash_equals($expectedSign, $sign);
        }

        // Live: Telebirr signs a canonical string with its private key; we
        // verify with the published Telebirr public key. The hmac param is
        // an HMAC-SHA256 of nounce keyed with the merchant appKey.
        if (TELEBIRR_PUBLIC_KEY === '') {
            return false;
        }
        $expectedHmac = hash_hmac('sha256', $nounce, self::secret());
        if (!hash_equals($expectedHmac, $hmac)) {
            return false;
        }
        $canonical = $nounce . $outTradeNo . $payTime . $totalAmount . $status;
        return self::rsaVerify($canonical, $sign, TELEBIRR_PUBLIC_KEY);
    }

    // ------------------------------------------------------------------
    // Transaction-status verification fallback (manual "poll")
    // ------------------------------------------------------------------

    /**
     * Manually check payment status with Telebirr (or the local sandbox).
     * @return array{ok:bool,paid:bool,message:string}
     */
    public static function verifyStatus(int $invoiceId): array
    {
        $invoice = Invoice::findById($invoiceId);
        if ($invoice === false) {
            return ['ok' => false, 'paid' => false, 'message' => 'Invoice not found.'];
        }

        $stmt = db()->prepare(
            'SELECT * FROM payments WHERE invoice_id = ? AND method = "telebirr" ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$invoiceId]);
        $pending = $stmt->fetch() ?: false;

        if ($pending === false || $pending['status'] !== 'pending') {
            return ['ok' => true, 'paid' => $pending !== false && $pending['status'] === 'success', 'message' => $pending['status'] ?? 'no payment'];
        }

        if (self::isSandbox()) {
            // Sandbox: nothing to poll — the callback is the only trigger.
            return ['ok' => true, 'paid' => false, 'message' => 'Sandbox mode: payment is still pending. Use the sandbox gateway to simulate completion.'];
        }

        if (TELEBIRR_APP_ID === '' || TELEBIRR_PRIVATE_KEY === '') {
            return ['ok' => false, 'paid' => false, 'message' => 'Live credentials are not configured.'];
        }

        $timestamp = date('YmdHis');
        $nonce     = self::nonce();
        $canonical = TELEBIRR_APP_ID . $timestamp . $nonce . $pending['reference'];
        $signature = self::rsaSign($canonical, TELEBIRR_PRIVATE_KEY);
        if ($signature === null) {
            return ['ok' => false, 'paid' => false, 'message' => 'Could not sign the verification request.'];
        }

        $body = base64_encode(json_encode([
            'appId'      => TELEBIRR_APP_ID,
            'timestamp'  => $timestamp,
            'nonce'      => $nonce,
            'outTradeNo' => $pending['reference'],
            'signature'  => $signature,
        ]));

        $response = self::httpPost(TELEBIRR_API_BASE . '/api/epay-telebirr/api/epay/checkMerchantPayment', $body);
        if ($response === null) {
            return ['ok' => false, 'paid' => false, 'message' => 'Could not reach the Telebirr status API.'];
        }
        $data = json_decode($response, true);
        $paid = is_array($data) && in_array((string) ($data['transaction_status'] ?? $data['status'] ?? ''), ['success', '2', '0000'], true);

        if ($paid) {
            BillingService::recordPayment((int) $invoiceId, (float) $pending['amount'], 'telebirr', (int) $pending['paid_by'], $pending['reference'], (string) ($data['transactionNo'] ?? ''), $response);
            $patient = Patient::findById((int) $invoice['patient_id']);
            if ($patient !== false) {
                mail_patient((int) $patient['id'], 'Payment confirmed',
                    'Your payment of ' . number_format((float) $pending['amount'], 2) . ' ETB for invoice ' . $invoice['invoice_number'] . ' was received.',
                    'payment-confirmed', [
                        'invoice_number' => $invoice['invoice_number'],
                        'amount'         => number_format((float) $pending['amount'], 2),
                        'reference'      => (string) ($data['transactionNo'] ?? $pending['reference']),
                    ]);
            }
            notify_user((int) $invoice['generated_by'], 'Payment received',
                'Invoice ' . $invoice['invoice_number'] . ' received a Telebirr payment of ' . number_format((float) $pending['amount'], 2) . ' ETB.',
                '/accountant/invoices/' . (int) $invoice['id']);
        }
        return ['ok' => true, 'paid' => $paid, 'message' => $paid ? 'Payment confirmed.' : 'Payment not yet received.'];
    }

    // ------------------------------------------------------------------
    // Crypto / HTTP helpers
    // ------------------------------------------------------------------

    private static function rsaSign(string $data, string $privateKeyPem): ?string
    {
        $key = openssl_pkey_get_private($privateKeyPem);
        if ($key === false) {
            return null;
        }
        $ok = openssl_sign($data, $sig, $key, OPENSSL_ALGO_SHA256);
        return $ok ? base64_encode($sig) : null;
    }

    private static function rsaVerify(string $data, string $base64Sig, string $publicKeyPem): bool
    {
        $key = openssl_pkey_get_public($publicKeyPem);
        if ($key === false) {
            return false;
        }
        return openssl_verify($data, base64_decode($base64Sig), $key, OPENSSL_ALGO_SHA256) === 1;
    }

    private static function httpPost(string $url, string $base64Body): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $base64Body,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'nonce: ' . self::nonce(),
            ],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch);
        if ($body === false) {
            curl_close($ch);
            return null;
        }
        curl_close($ch);
        return (string) $body;
    }

    /** Sandbox-only: the signing key used to build the simulated callback. */
    public static function sandboxKey(): string
    {
        return self::secret();
    }

    /** Signed token for the sandbox gateway page (to let it prove authenticity). */
    public static function sandboxToken(string $outTradeNo): string
    {
        return hash_hmac('sha256', 'pay:' . $outTradeNo, self::secret());
    }

    public static function sandboxTokenValid(string $outTradeNo, string $token): bool
    {
        return hash_equals(self::sandboxToken($outTradeNo), $token);
    }
}