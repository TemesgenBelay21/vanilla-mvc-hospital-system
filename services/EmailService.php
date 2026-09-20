<?php

/**
 * EmailService — centralized PHPMailer (SMTP) mailer with reusable, hospital-
 * branded HTML templates in views/emails/. Sending is a no-op when SMTP
 * credentials are not configured yet (MAIL_USER empty), so development keeps
 * working without a mail account. The app never falls back to mail().
 */

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

class EmailService
{
    private static bool $triedAutoload = false;

    public static function configured(): bool
    {
        return MAIL_USER !== '' && MAIL_PASS !== '';
    }

    /**
     * Send a branded email to a user by id.
     * @return array{ok:bool,message:string}
     */
    public static function send(int $userId, string $subject, string $template, array $data = []): array
    {
        if (!self::configured()) {
            return ['ok' => false, 'message' => 'SMTP mail is not configured (set MAIL_* environment variables).'];
        }

        $user = User::findById($userId);
        if ($user === false || empty($user['email'])) {
            return ['ok' => false, 'message' => 'Recipient has no email address.'];
        }

        self::loadMailer();
        if (!class_exists(PHPMailer::class)) {
            return ['ok' => false, 'message' => 'PHPMailer is not installed (run composer install).'];
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USER;
            $mail->Password   = MAIL_PASS;
            $mail->SMTPSecure = MAIL_PORT === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            $mail->addAddress($user['email'], $user['name']);

            $mail->isHTML(true);
            $mail->Subject = $subject;

            $context = array_merge($data, [
                'app_name' => APP_NAME,
                'user'     => $user,
                'subject'  => $subject,
            ]);

            $content = render_partial('emails/' . $template, $context);
            $mail->msgHTML(render_partial('emails/layout', ['content' => $content, 'app_name' => APP_NAME, 'subject' => $subject]));

            $mail->send();
            return ['ok' => true, 'message' => 'Email sent.'];
        } catch (MailerException $e) {
            return ['ok' => false, 'message' => 'Mailer error: ' . $e->getMessage()];
        }
    }

    /** Make sure vendor/autoload.php is loaded once (kept out of config.php). */
    private static function loadMailer(): void
    {
        if (self::$triedAutoload) {
            return;
        }
        self::$triedAutoload = true;
        $autoload = APP_ROOT . '/vendor/autoload.php';
        if (is_file($autoload)) {
            require_once $autoload;
        }
    }
}