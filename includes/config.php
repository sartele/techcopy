<?php
// ============================================================
//  TechCopy — Configurazione
// ============================================================
define('DB_HOST',       'localhost');
define('DB_USER',       'root');
define('DB_PASS',       '');
define('DB_NAME',       'techcopy');
define('DB_CHARSET',    'utf8mb4');
define('APP_NAME',      'TechCopy');
define('UPLOAD_DIR',    __DIR__ . '/../uploads/');
define('UPLOAD_MAX_MB', 10);

// ── SICUREZZA ────────────────────────────────────────────────

// In produzione impostare a false — non mostrare mai errori PHP all'utente
define('APP_DEBUG', true);

if (!APP_DEBUG) {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    // In produzione impostare un percorso di log reale:
    // ini_set('error_log', '/var/log/techcopy_errors.log');
}

// Durata sessione: 8 ore di inattività
define('SESSION_LIFETIME', 28800);

// Max tentativi login prima del blocco temporaneo
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_SECONDS', 900); // 15 minuti

// Tipi MIME ammessi per gli allegati
define('ALLOWED_UPLOAD_MIMES', serialize([
    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'text/plain',
]));

// ── CONNESSIONE DB ───────────────────────────────────────────
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            // Non esporre mai i dettagli dell'errore all'utente
            if (APP_DEBUG) {
                die('DB error: ' . htmlspecialchars($e->getMessage()));
            } else {
                die('Errore di connessione. Contattare l\'amministratore.');
            }
        }
    }
    return $pdo;
}

// ── EMAIL / SMTP ─────────────────────────────────────────────
// Modalità invio: 'smtp' oppure 'mail' (funzione PHP nativa, più semplice ma meno affidabile)
// Per Gmail usare 'smtp' con le impostazioni sotto.
define('MAIL_MODE',       'smtp');         // 'smtp' | 'mail'
define('MAIL_FROM',       'noreply@riscrive.it');
define('MAIL_FROM_NAME',  'TechCopy Assistenza');
define('MAIL_SMTP_HOST',  'smtps.axera.it');
define('MAIL_SMTP_PORT',  587);            // 587 = STARTTLS | 465 = SSL
define('MAIL_SMTP_ENC',   'tls');          // 'tls' | 'ssl' | '' (nessuno)
define('MAIL_SMTP_USER',  'int7589');             // email mittente SMTP
define('MAIL_SMTP_PASS',  'Si!Via1985!!');             // password / App Password
define('MAIL_SMTP_DEBUG', 0);             // 0=off 1=errori 2=verboso (solo dev)

// Abilita/disabilita l'intero sistema di notifiche email
define('MAIL_ENABLED',    true);          // impostare true dopo aver configurato SMTP