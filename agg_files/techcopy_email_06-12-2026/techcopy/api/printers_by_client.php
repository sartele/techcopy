<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
header('Content-Type: application/json');
$clientId = (int)($_GET['client_id'] ?? 0);
if (!$clientId) { echo json_encode([]); exit; }
$stmt = db()->prepare("SELECT id, brand, model, serial FROM printers WHERE client_id=? AND active=1 ORDER BY brand, model");
$stmt->execute([$clientId]);
echo json_encode($stmt->fetchAll());
