<?php
// ============================================================
//  TechCopy — Mailer
//  Wrapper su PHPMailer con fallback a mail() nativa.
//  Non chiamare direttamente: usare notifications.php
// ============================================================
require_once __DIR__ . '/config.php';

// Carica PHPMailer (scaricato in vendor/phpmailer/)
require_once __DIR__ . '/../vendor/phpmailer/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailException;

/**
 * Invia un'email HTML.
 *
 * @param string|array $to     Destinatario: 'email@x.it' oppure ['email'=>'nome', ...]
 * @param string       $subject
 * @param string       $htmlBody  Corpo HTML completo
 * @param string       $textBody  Corpo testo puro (fallback per client senza HTML)
 * @return bool        true = inviata, false = errore
 */
function send_mail(string|array $to, string $subject, string $htmlBody, string $textBody = ''): bool {
    if (!defined('MAIL_ENABLED') || !MAIL_ENABLED) {
        return false; // Notifiche disabilitate in config.php
    }

    try {
        $mail = new PHPMailer(true);

        // ── Modalità invio ────────────────────────────────────
        if (MAIL_MODE === 'smtp') {
            $mail->isSMTP();
            $mail->Host       = MAIL_SMTP_HOST;
            $mail->Port       = MAIL_SMTP_PORT;
            $mail->SMTPDebug  = MAIL_SMTP_DEBUG;

            if (MAIL_SMTP_USER && MAIL_SMTP_PASS) {
                $mail->SMTPAuth   = true;
                $mail->Username   = MAIL_SMTP_USER;
                $mail->Password   = MAIL_SMTP_PASS;
            }
            if (MAIL_SMTP_ENC === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif (MAIL_SMTP_ENC === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }
		} else {
            $mail->isMail(); // funzione mail() nativa PHP
        }

        // ── Mittente ──────────────────────────────────────────
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;

        // ── Destinatari ───────────────────────────────────────
        if (is_string($to)) {
            $mail->addAddress($to);
        } else {
            foreach ($to as $email => $name) {
                if (is_int($email)) {
                    $mail->addAddress($name); // array semplice ['email@x.it', ...]
                } else {
                    $mail->addAddress($email, $name); // array associativo
                }
            }
        }

        // ── Corpo ─────────────────────────────────────────────
        $mail->isHTML(true);
        $mail->Body    = $htmlBody;
        $mail->AltBody = $textBody ?: strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        $mail->send();
        return true;

    } catch (MailException $e) {
        // Log errore senza esporre dettagli all'utente
        if (defined('APP_DEBUG') && APP_DEBUG) {
            error_log('TechCopy Mailer Error: ' . $e->getMessage());
        }
        return false;
    } catch (\Exception $e) {
        error_log('TechCopy Mailer Exception: ' . $e->getMessage());
        return false;
    }
}

/**
 * Testa la connessione SMTP senza inviare email.
 * Usata dalla pagina impostazioni admin.
 */
function test_smtp_connection(): array {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host      = MAIL_SMTP_HOST;
        $mail->Port      = MAIL_SMTP_PORT;
        $mail->SMTPAuth  = true;
        $mail->Username  = MAIL_SMTP_USER;
        $mail->Password  = MAIL_SMTP_PASS;
        if (MAIL_SMTP_ENC === 'tls')      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        elseif (MAIL_SMTP_ENC === 'ssl')  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->SMTPDebug = 0;

        $smtp = new SMTP();
        $smtp->do_debug = 0;
        $connected = $smtp->connect(MAIL_SMTP_HOST, MAIL_SMTP_PORT, 10);
        if ($connected) {
            $smtp->quit();
            return ['ok' => true, 'msg' => 'Connessione a ' . MAIL_SMTP_HOST . ':' . MAIL_SMTP_PORT . ' riuscita ✅'];
        }
        return ['ok' => false, 'msg' => 'Impossibile connettersi a ' . MAIL_SMTP_HOST . ':' . MAIL_SMTP_PORT];
    } catch (\Exception $e) {
        return ['ok' => false, 'msg' => $e->getMessage()];
    }
}
