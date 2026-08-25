<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_login();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}
if (!is_admin()) {
    json_response(['error' => 'Non autorizzato'], 403);
}

$data  = json_decode(file_get_contents('php://input'), true);
$id    = (int)($data['id'] ?? 0);
$delta = (int)($data['delta'] ?? 0);

if (!$id || $delta === 0) {
    json_response(['error' => 'Parametri non validi'], 400);
}

$db = db();
$db->prepare("UPDATE consumables SET stock = GREATEST(0, stock + ?) WHERE id=?")->execute([$delta, $id]);
$row = $db->prepare("SELECT stock, min_stock FROM consumables WHERE id=?");
$row->execute([$id]);
$row = $row->fetch();
if (!$row) json_response(['error' => 'Non trovato'], 404);

$pct = $row['min_stock'] > 0 ? min(100, ($row['stock'] / ($row['min_stock'] * 2)) * 100) : 50;

// Log movimento
$type = $delta > 0 ? 'carico' : 'scarico';
$db->prepare("INSERT INTO stock_movements (consumable_id, user_id, type, quantity) VALUES (?,?,?,?)")
   ->execute([$id, current_user()['id'], $type, abs($delta)]);

json_response(['success' => true, 'stock' => $row['stock'], 'pct' => round($pct)]);
