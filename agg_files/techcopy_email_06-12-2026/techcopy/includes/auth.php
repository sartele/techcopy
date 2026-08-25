<?php
// ============================================================
//  TechCopy — Autenticazione, Sessione e Sicurezza
// ============================================================
require_once __DIR__ . '/config.php';

// ── SESSIONE SICURA ──────────────────────────────────────────
function session_init(): void {
    if (session_status() !== PHP_SESSION_NONE) return;

    // Cookie di sessione sicuro
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),  // true su HTTPS
        'httponly' => true,                       // non accessibile da JS
        'samesite' => 'Strict',                   // blocca CSRF cross-site
    ]);
    session_name('tc_sid');
    session_start();

    // Timeout inattività: forza logout dopo SESSION_LIFETIME secondi
    if (isset($_SESSION['_last_activity'])) {
        if (time() - $_SESSION['_last_activity'] > SESSION_LIFETIME) {
            session_unset();
            session_destroy();
            session_start();
        }
    }
    $_SESSION['_last_activity'] = time();
}

function current_user(): ?array {
    session_init();
    return $_SESSION['user'] ?? null;
}

function require_login(): void {
    if (!current_user()) {
        // Memorizza la pagina richiesta per redirect post-login
        $url = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '', ENT_QUOTES);
        header('Location: /techcopy/login.php' . ($url ? '?next='.urlencode($url) : ''));
        exit;
    }
}

function require_role(string ...$roles): void {
    require_login();
    $user = current_user();
    if (!in_array($user['role'], $roles)) {
        http_response_code(403);
        die('<h2 style="font-family:sans-serif;padding:40px">⛔ Accesso non autorizzato.</h2>');
    }
}

// ── CSRF ─────────────────────────────────────────────────────

/**
 * Genera (o recupera) il token CSRF per la sessione corrente.
 * Da includere in ogni form con: <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
 */
function csrf_token(): string {
    session_init();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica il token CSRF.
 * Da chiamare all'inizio di ogni handler POST prima di qualsiasi azione.
 */
function csrf_verify(): void {
    session_init();
    $token = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('<h2 style="font-family:sans-serif;padding:40px">⛔ Token di sicurezza non valido. Ricaricare la pagina.</h2>');
    }
}

// ── RATE LIMITING LOGIN ──────────────────────────────────────

/**
 * Controlla se l'IP è temporaneamente bloccato per troppi tentativi.
 * Usa la sessione come storage leggero (nessun DB extra necessario).
 * Per sistemi ad alto traffico sostituire con Redis/APCu.
 */
function login_check_rate_limit(string $ip): bool {
    session_init();
    $key = 'login_attempts_' . md5($ip);
    $data = $_SESSION[$key] ?? ['count' => 0, 'first' => time(), 'blocked_until' => 0];

    // Ancora bloccato?
    if ($data['blocked_until'] > time()) {
        $wait = ceil(($data['blocked_until'] - time()) / 60);
        return false; // bloccato
    }

    // Reset contatore se è passata la finestra temporale (es. 15 min)
    if (time() - $data['first'] > LOGIN_LOCKOUT_SECONDS) {
        $data = ['count' => 0, 'first' => time(), 'blocked_until' => 0];
    }

    // Troppi tentativi → blocca
    if ($data['count'] >= LOGIN_MAX_ATTEMPTS) {
        $data['blocked_until'] = time() + LOGIN_LOCKOUT_SECONDS;
        $_SESSION[$key] = $data;
        return false;
    }

    return true;
}

function login_record_failure(string $ip): void {
    session_init();
    $key  = 'login_attempts_' . md5($ip);
    $data = $_SESSION[$key] ?? ['count' => 0, 'first' => time(), 'blocked_until' => 0];
    $data['count']++;
    $_SESSION[$key] = $data;
}

function login_clear_rate_limit(string $ip): void {
    session_init();
    unset($_SESSION['login_attempts_' . md5($ip)]);
}

function login_remaining_time(string $ip): int {
    session_init();
    $key  = 'login_attempts_' . md5($ip);
    $data = $_SESSION[$key] ?? ['blocked_until' => 0];
    return max(0, $data['blocked_until'] - time());
}

// ── LOGIN / LOGOUT ────────────────────────────────────────────
function login_user(string $email, string $password): array|false {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // Rate limit
    if (!login_check_rate_limit($ip)) {
        return false;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE email=? AND active=1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // ── SESSION FIXATION FIX ──────────────────────────────
        // Rigenerare sempre l'ID di sessione al login per prevenire
        // che un attaccante inietti un session ID noto prima del login
        session_regenerate_id(true);

        unset($user['password']);
        session_init();
        $_SESSION['user']          = $user;
        $_SESSION['_login_time']   = time();
        $_SESSION['_login_ip']     = $ip;

        // Genera subito il token CSRF per questa sessione
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        login_clear_rate_limit($ip);
        return $user;
    }

    // Login fallito: registra il tentativo
    login_record_failure($ip);
    return false;
}

function logout_user(): void {
    session_init();
    // Rimuove tutti i dati di sessione e distrugge il cookie
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// ── CONTROLLI RUOLO ──────────────────────────────────────────
function is_admin(): bool {
    $u = current_user(); return $u && $u['role'] === 'admin';
}
function is_supervisor(): bool {
    $u = current_user(); return $u && $u['role'] === 'supervisor';
}
function is_tech(): bool {
    $u = current_user(); return $u && $u['role'] === 'tech';
}
function is_viewer(): bool {
    $u = current_user(); return $u && $u['role'] === 'viewer';
}
function is_admin_or_supervisor(): bool {
    $u = current_user();
    return $u && in_array($u['role'], ['admin','supervisor']);
}
function can_create_ticket(): bool {
    $u = current_user();
    return $u && in_array($u['role'], ['admin','supervisor','tech']);
}
function can_manage_consumables(): bool {
    $u = current_user();
    return $u && in_array($u['role'], ['admin','supervisor']);
}
function can_manage_clients(): bool {
    $u = current_user();
    return $u && in_array($u['role'], ['admin','supervisor']);
}
function can_manage_printers(): bool {
    $u = current_user();
    return $u && in_array($u['role'], ['admin','supervisor']);
}
function can_view_map(): bool {
    return current_user() !== null;
}
function ticket_tech_filter(): ?int {
    $u = current_user();
    if (!$u) return null;
    return ($u['role'] === 'tech') ? (int)$u['id'] : null;
}
function can_edit_ticket(int $techId, int $ticketId = 0): bool {
    $u = current_user();
    if (!$u) return false;
    if (in_array($u['role'], ['admin','supervisor'])) return true;
    if ($u['role'] === 'tech') {
        if ((int)$u['id'] === $techId) return true;
        if ($ticketId > 0) {
            $stmt = db()->prepare("SELECT id FROM ticket_users WHERE ticket_id=? AND user_id=?");
            $stmt->execute([$ticketId, $u['id']]);
            if ($stmt->fetch()) return true;
        }
    }
    return false;
}
function is_tech_or_admin(): bool { return can_create_ticket(); }
