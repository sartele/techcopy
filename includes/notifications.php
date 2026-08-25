<?php
// ============================================================
//  TechCopy — Notifiche Email
//
//  Funzioni da chiamare nei punti di hook:
//    notify_ticket_created($ticketId, $createdByUser)
//    notify_ticket_updated($ticketId, $oldStatus, $newStatus, $updatedByUser)
//    notify_ticket_assigned($ticketId, $assignedUserId, $createdByUser)
// ============================================================
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mailer.php';

// ── RACCOLTA DESTINATARI ──────────────────────────────────────

/**
 * Restituisce array ['email' => 'nome'] di tutti gli utenti da notificare
 * per un dato ticket: tecnico responsabile + team + admin/supervisor attivi
 * che hanno le notifiche abilitate.
 * Esclude l'utente che ha fatto l'azione ($excludeUserId).
 */
function _get_ticket_recipients(int $ticketId, int $excludeUserId = 0): array {
    $db = db();

    // Tecnici assegnati al ticket (ticket_users + tech_id)
    $stmt = $db->prepare("
        SELECT DISTINCT u.id, u.name, u.email, u.role, u.notify_email
        FROM users u
        WHERE u.active = 1
          AND u.email IS NOT NULL AND u.email != ''
          AND u.notify_email = 1
          AND (
              u.id IN (SELECT user_id FROM ticket_users WHERE ticket_id = ?)
              OR u.id = (SELECT tech_id FROM tickets WHERE id = ?)
          )
    ");
    $stmt->execute([$ticketId, $ticketId]);
    $assigned = $stmt->fetchAll();

    // Admin e Supervisor: ricevono tutte le notifiche
    $stmt2 = $db->prepare("
        SELECT u.id, u.name, u.email, u.role, u.notify_email
        FROM users u
        WHERE u.active = 1
          AND u.role IN ('admin','supervisor')
          AND u.email IS NOT NULL AND u.email != ''
          AND u.notify_email = 1
    ");
    $stmt2->execute();
    $managers = $stmt2->fetchAll();

    // Unisce e deduplica per ID
    $all = [];
    foreach (array_merge($assigned, $managers) as $u) {
        if ((int)$u['id'] !== $excludeUserId) {
            $all[$u['email']] = $u['name'];
        }
    }
    return $all;
}

// ── TEMPLATE EMAIL ────────────────────────────────────────────

function _email_layout(string $title, string $body, string $ticketUrl = ''): string {
    $appName = defined('APP_NAME') ? APP_NAME : 'TechCopy';
    $btnHtml = $ticketUrl
        ? "<div style='text-align:center;margin:28px 0'>
             <a href='{$ticketUrl}' style='background:#0077bb;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:600;font-size:14px;display:inline-block'>
               → Apri Intervento
             </a>
           </div>"
        : '';

    return <<<HTML
<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f0f2f5;font-family:'Segoe UI',Arial,sans-serif">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f2f5;padding:32px 16px">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%">

      <!-- HEADER -->
      <tr>
        <td style="background:#1a2233;border-radius:10px 10px 0 0;padding:24px 32px">
          <span style="font-family:monospace;font-size:20px;color:#00d4ff;font-weight:700"><img src="/techcopy/assets/img/favicon.png" width="20" height="20"> Scrive & Riscrive S.R.L.</span>
          <span style="color:#8b98a8;font-size:13px;margin-left:12px">Gestione Assistenza</span>
        </td>
      </tr>

      <!-- TITOLO -->
      <tr>
        <td style="background:#ffffff;padding:28px 32px 0">
          <h1 style="margin:0;font-size:20px;color:#1a2233;font-weight:700">{$title}</h1>
        </td>
      </tr>

      <!-- CORPO -->
      <tr>
        <td style="background:#ffffff;padding:16px 32px 8px">
          {$body}
          {$btnHtml}
        </td>
      </tr>

      <!-- FOOTER -->
      <tr>
        <td style="background:#f8f9fb;border-top:1px solid #e4e7ec;border-radius:0 0 10px 10px;padding:16px 32px;font-size:12px;color:#8b98a8;text-align:center">
          Questa email è stata inviata automaticamente da <strong>Gestione Assistenza Scrive & Riscrive S.R.L.</strong>.<br>
          Per disabilitare le notifiche, accedi al tuo profilo e modifica le preferenze.
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body></html>
HTML;
}

function _info_row(string $label, string $value): string {
    return "<tr>
        <td style='padding:7px 12px;font-size:12px;color:#8b98a8;font-weight:600;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;width:130px'>{$label}</td>
        <td style='padding:7px 12px;font-size:14px;color:#1a2233'>{$value}</td>
    </tr>";
}

function _ticket_info_table(array $t): string {
    $statusColor = match($t['status'] ?? '') {
        'closed'  => '#1a7f37', 'pending' => '#8a6500', default => '#c25600'
    };
    $statusLabel = match($t['status'] ?? '') {
        'closed' => 'Chiuso', 'pending' => 'In attesa', default => 'Aperto'
    };
    $priorityLabel = match($t['priority'] ?? '') {
        'urgent' => '🔴 Urgente', 'high' => '🔶 Alta', default => '⚪ Normale'
    };

    $rows  = _info_row('Intervento', '#' . str_pad($t['id'], 4, '0', STR_PAD_LEFT) . ' — ' . htmlspecialchars($t['title'] ?? '', ENT_QUOTES, 'UTF-8'));
    $rows .= _info_row('Cliente',    htmlspecialchars($t['client_name'] ?? '—', ENT_QUOTES, 'UTF-8'));
    $rows .= _info_row('Stampante',  htmlspecialchars(($t['brand'] ?? '') . ' ' . ($t['model'] ?? ''), ENT_QUOTES, 'UTF-8'));
    $rows .= _info_row('Tipo',       htmlspecialchars(ucfirst($t['type'] ?? ''), ENT_QUOTES, 'UTF-8'));
    $rows .= _info_row('Priorità',   $priorityLabel);
    $rows .= _info_row('Stato',      "<span style='color:{$statusColor};font-weight:600'>{$statusLabel}</span>");
    if (!empty($t['tech_name'])) {
        $rows .= _info_row('Tecnico', htmlspecialchars($t['tech_name'], ENT_QUOTES, 'UTF-8'));
    }

    return "<table cellpadding='0' cellspacing='0' style='width:100%;background:#f8f9fb;border:1px solid #e4e7ec;border-radius:8px;margin:16px 0'>{$rows}</table>";
}

function _load_ticket(int $id): ?array {
    $stmt = db()->prepare("
        SELECT t.*, c.name AS client_name, p.brand, p.model, u.name AS tech_name
        FROM tickets t
        LEFT JOIN clients  c ON c.id = t.client_id
        LEFT JOIN printers p ON p.id = t.printer_id
        LEFT JOIN users    u ON u.id = t.tech_id
        WHERE t.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function _ticket_url(int $id): string {
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return "{$proto}://{$host}/techcopy/pages/tickets.php?id={$id}";
}

// ── EVENTI ────────────────────────────────────────────────────

/**
 * Notifica creazione nuovo intervento.
 * Invia a: tecnico assegnato + admin/supervisor con notify_email=1.
 */
function notify_ticket_created(int $ticketId, array $createdBy): void {
    if (!defined('MAIL_ENABLED') || !MAIL_ENABLED) return;

    $t = _load_ticket($ticketId);
    if (!$t) return;

    $recipients = _get_ticket_recipients($ticketId, (int)$createdBy['id']);
    if (empty($recipients)) return;

    $subject = "Nuovo intervento #{$ticketId}: " . ($t['title'] ?? '');
    $infoTable = _ticket_info_table($t);
    $by = htmlspecialchars($createdBy['name'], ENT_QUOTES, 'UTF-8');
    $desc = htmlspecialchars($t['description'] ?? '', ENT_QUOTES, 'UTF-8');
    $descBlock = $desc ? "<div style='background:#f0f7ff;border-left:3px solid #0077bb;padding:12px 16px;margin:12px 0;font-size:13px;line-height:1.6;color:#1a2233'>{$desc}</div>" : '';

    $body = "<p style='font-size:14px;color:#4a5568;margin:0 0 8px'>
               È stato aperto un nuovo intervento da <strong>{$by}</strong>:
             </p>
             {$infoTable}
             {$descBlock}";

    $html = _email_layout("🔧 Nuovo Intervento", $body, _ticket_url($ticketId));
    send_mail($recipients, $subject, $html);
}

/**
 * Notifica aggiornamento intervento (cambio stato, chiusura, riassegnazione, ecc.)
 * Invia a: tutti gli assegnati + admin/supervisor con notify_email=1.
 */
function notify_ticket_updated(int $ticketId, string $oldStatus, string $newStatus, array $updatedBy, string $changeDescription = ''): void {
    if (!defined('MAIL_ENABLED') || !MAIL_ENABLED) return;

    $t = _load_ticket($ticketId);
    if (!$t) return;

    // Non inviare se lo stato non è cambiato e non c'è una descrizione esplicita
    if ($oldStatus === $newStatus && empty($changeDescription)) return;

    $recipients = _get_ticket_recipients($ticketId, (int)$updatedBy['id']);
    if (empty($recipients)) return;

    // Titolo email in base all'evento principale
    if ($newStatus === 'closed' || $t['resolved']) {
        $emoji  = '✅';
        $title  = "Intervento Chiuso";
        $subject = "[TechCopy] ✅ Intervento #{$ticketId} chiuso: " . ($t['title'] ?? '');
    } elseif ($newStatus === 'pending') {
        $emoji  = '🟡';
        $title  = "Intervento In Attesa";
        $subject = "[TechCopy] 🟡 Intervento #{$ticketId} in attesa: " . ($t['title'] ?? '');
    } else {
        $emoji  = '🔄';
        $title  = "Intervento Aggiornato";
        $subject = "[TechCopy] 🔄 Intervento #{$ticketId} aggiornato: " . ($t['title'] ?? '');
    }

    $infoTable = _ticket_info_table($t);
    $by = htmlspecialchars($updatedBy['name'], ENT_QUOTES, 'UTF-8');

    // Blocco cambio stato
    $statusLabels = ['open' => 'Aperto', 'pending' => 'In attesa', 'closed' => 'Chiuso'];
    $oldLabel = $statusLabels[$oldStatus] ?? $oldStatus;
    $newLabel = $statusLabels[$newStatus] ?? $newStatus;

    $changeBlock = '';
    if ($oldStatus !== $newStatus) {
        $changeBlock = "<div style='background:#e8f5e9;border:1px solid #a5d6a7;border-radius:6px;padding:12px 16px;margin:12px 0;font-size:13px'>
            <strong>Cambio stato:</strong> {$oldLabel} → <strong>{$newLabel}</strong>
        </div>";
    }

    $noteBlock = '';
    if ($changeDescription) {
        $safeNote = htmlspecialchars($changeDescription, ENT_QUOTES, 'UTF-8');
        $noteBlock = "<div style='background:#f0f7ff;border-left:3px solid #0077bb;padding:12px 16px;margin:12px 0;font-size:13px;line-height:1.6;color:#1a2233'>
            <strong>Note:</strong> {$safeNote}
        </div>";
    }

    $workReport = '';
    if (!empty($t['work_report'])) {
        $safeReport = nl2br(htmlspecialchars($t['work_report'], ENT_QUOTES, 'UTF-8'));
        $workReport = "<div style='margin-top:12px'>
            <div style='font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#8b98a8;margin-bottom:6px'>📋 Lavoro svolto</div>
            <div style='background:#f0f7ff;border:1px solid #90caf9;border-radius:6px;padding:12px 16px;font-size:13px;line-height:1.7;color:#1a2233'>{$safeReport}</div>
        </div>";
    }

    $body = "<p style='font-size:14px;color:#4a5568;margin:0 0 8px'>
               L'intervento è stato aggiornato da <strong>{$by}</strong>:
             </p>
             {$infoTable}
             {$changeBlock}
             {$noteBlock}
             {$workReport}";

    $html = _email_layout("{$emoji} {$title}", $body, _ticket_url($ticketId));
    send_mail($recipients, $subject, $html);
}

/**
 * Notifica specifica di assegnazione: invia solo al tecnico appena assegnato.
 */
function notify_ticket_assigned(int $ticketId, int $assignedUserId, array $assignedBy): void {
    if (!defined('MAIL_ENABLED') || !MAIL_ENABLED) return;

    $t = _load_ticket($ticketId);
    if (!$t) return;

    // Carica il tecnico assegnato
    $stmt = db()->prepare("SELECT id, name, email, notify_email FROM users WHERE id=? AND active=1");
    $stmt->execute([$assignedUserId]);
    $assignee = $stmt->fetch();

    if (!$assignee || empty($assignee['email']) || !$assignee['notify_email']) return;
    if ((int)$assignee['id'] === (int)$assignedBy['id']) return; // non notificare se si auto-assegna

    $subject = "👤 Ti è stato assegnato l'intervento #{$ticketId}";
    $infoTable = _ticket_info_table($t);
    $by = htmlspecialchars($assignedBy['name'], ENT_QUOTES, 'UTF-8');
    $who = htmlspecialchars($assignee['name'], ENT_QUOTES, 'UTF-8');
    $desc = htmlspecialchars($t['description'] ?? '', ENT_QUOTES, 'UTF-8');
    $descBlock = $desc ? "<div style='background:#f0f7ff;border-left:3px solid #0077bb;padding:12px 16px;margin:12px 0;font-size:13px;line-height:1.6'>{$desc}</div>" : '';

    $body = "<p style='font-size:14px;color:#4a5568;margin:0 0 8px'>
               Ciao <strong>{$who}</strong>, ti è stato assegnato un intervento da <strong>{$by}</strong>:
             </p>
             {$infoTable}
             {$descBlock}";

    $html = _email_layout("👤 Intervento assegnato a te", $body, _ticket_url($ticketId));
    send_mail([$assignee['email'] => $assignee['name']], $subject, $html);
}

/**
 * Email di test — inviata dalla pagina impostazioni.
 */
function send_test_email(string $toEmail, string $toName): bool {
    $subject = "[TechCopy] ✅ Email di test";
    $body = "<p style='font-size:14px;color:#4a5568;line-height:1.7'>
               Ciao <strong>" . htmlspecialchars($toName, ENT_QUOTES, 'UTF-8') . "</strong>,<br><br>
               Questa è un'email di test inviata dal sistema <strong>" . APP_NAME . "</strong>.<br>
               Se la stai leggendo, la configurazione SMTP è corretta! ✅
             </p>
             <div style='background:#e8f5e9;border:1px solid #a5d6a7;border-radius:6px;padding:14px 18px;margin:16px 0;font-size:13px'>
               <strong>Configurazione attiva:</strong><br>
               Host: " . MAIL_SMTP_HOST . ":" . MAIL_SMTP_PORT . "<br>
               Modalità: " . strtoupper(MAIL_SMTP_ENC ?: 'nessuna') . "<br>
               Mittente: " . MAIL_FROM . "
             </div>";

    return send_mail(
        [$toEmail => $toName],
        $subject,
        _email_layout("📧 Email di Test", $body)
    );
}
