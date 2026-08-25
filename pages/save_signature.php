<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_login();

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Payload non valido');
    }

    $ticketId = (int)($input['ticket_id'] ?? 0);
    $signature = $input['signature'] ?? null;

    if (!$ticketId || !$signature) {
        throw new Exception('Dati mancanti');
    }

    $db = db();

    $stmt = $db->prepare("SELECT id FROM tickets WHERE id=? LIMIT 1");
    $stmt->execute([$ticketId]);

    if (!$stmt->fetch()) {
        throw new Exception('Ticket non trovato');
    }

    if (!preg_match('/^data:image\/png;base64,/', $signature)) {
        throw new Exception('Formato firma non valido');
    }

    $signature = str_replace('data:image/png;base64,', '', $signature);
    $signature = str_replace(' ', '+', $signature);

    $binary = base64_decode($signature);

    if (!$binary) {
        throw new Exception('Errore decodifica firma');
    }

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/techcopy/uploads/signatures/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = 'ticket_' . $ticketId . '.png';
    $fullPath = $uploadDir . $filename;

    if (!file_put_contents($fullPath, $binary)) {
        throw new Exception('Impossibile salvare la firma');
    }

    $dbPath = '/techcopy/uploads/signatures/' . $filename;

    $update = $db->prepare("
        UPDATE tickets
        SET client_signature_path=?,
            signed_at=NOW()
        WHERE id=?
    ");

    $update->execute([$dbPath, $ticketId]);

    echo json_encode([
        'success' => true,
        'message' => '✅ Firma salvata correttamente'
    ]);
exit;
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '❌ ' . $e->getMessage()
    ]);
	exit;
}

if(!signed){
    alert("Inserire una firma");
    return;
}