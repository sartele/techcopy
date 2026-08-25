<?php
// ============================================================
//  TechCopy — Funzioni di utilità
// ============================================================

function h(mixed $v): string {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function json_response(mixed $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

function redirect(string $url): void {
    // Permette solo redirect relativi o verso lo stesso dominio
    if (!str_starts_with($url, '/') && !str_starts_with($url, 'http')) {
        $url = '/techcopy/';
    }
    header('Location: ' . $url);
    exit;
}

function flash(string $msg, string $type = 'success'): void {
    session_init();
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function get_flash(): ?array {
    session_init();
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/**
 * Emette un campo hidden con il token CSRF.
 * Usare in tutti i form: <?= csrf_field() ?>
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

function status_badge(string $s): string {
    return match($s) {
        'open'    => '<span class="badge badge-open">🟠 Aperto</span>',
        'pending' => '<span class="badge badge-pending">🟡 In attesa</span>',
        'closed'  => '<span class="badge badge-closed">🟢 Chiuso</span>',
        default   => '<span class="badge">' . h($s) . '</span>',
    };
}

function priority_badge(string $p): string {
    return match($p) {
        'urgent' => '<span class="badge badge-urgent">🔴 Urgente</span>',
        'high'   => '<span class="badge badge-open">🔶 Alta</span>',
        default  => '<span class="badge badge-viewer">Normale</span>',
    };
}

function log_ticket(int $ticketId, int $userId, string $userName, string $action): void {
    $stmt = db()->prepare('INSERT INTO ticket_history (ticket_id,user_id,user_name,action) VALUES (?,?,?,?)');
    $stmt->execute([$ticketId, $userId, $userName, $action]);
}

function format_minutes(int $min): string {
    if ($min <= 0) return '—';
    $h = intdiv($min, 60);
    $m = $min % 60;
    return $h > 0 ? "{$h}h {$m}min" : "{$m}min";
}

/**
 * Verifica il tipo MIME di un file caricato in modo sicuro.
 * NON fare affidamento solo sull'estensione o su $_FILES['type'].
 */
function allowed_upload(string $tmpPath): bool {
    if (!file_exists($tmpPath)) return false;
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($tmpPath);
    return in_array($mime, unserialize(ALLOWED_UPLOAD_MIMES));
}

/**
 * Salva un allegato in modo sicuro:
 * - nome file randomico (non indovinabile)
 * - estensione forzata da whitelist
 * - percorso fuori dalla webroot se possibile
 */
function save_upload(array $file, int $ticketId): array|false {
    $allowedExt = ['jpg','jpeg','png','gif','webp','pdf','doc','docx','txt'];
    $origExt    = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($origExt, $allowedExt)) return false;
    if (!allowed_upload($file['tmp_name']))  return false;
    if ($file['size'] > UPLOAD_MAX_MB * 1024 * 1024) return false;

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

    // Nome casuale: impossibile indovinare il percorso del file
    $newName = 'tc_' . $ticketId . '_' . bin2hex(random_bytes(8)) . '.' . $origExt;
    $dest    = UPLOAD_DIR . $newName;

    if (!move_uploaded_file($file['tmp_name'], $dest)) return false;

    return [
        'filename' => $file['name'],
        'filepath' => $dest,
        'filesize' => $file['size'],
        'mime'     => mime_content_type($dest),
    ];
}

function adf_label(string $adf): string {
    return match($adf) {
        'simple' => 'ADF Fronte',
        'duplex' => 'ADF Fronte/Retro',
        default  => 'Nessun ADF',
    };
}
