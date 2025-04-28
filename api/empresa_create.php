<?php
// public_html/api/empresa_create.php
header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Only POST allowed']);
  exit;
}

// 1️⃣  Collect data  --------------------------------------------------------
$payload = file_get_contents('php://input');
$data    = json_decode($payload, true);        // Expecting JSON

// Fallback: traditional form-urlencoded
if (!$data) { $data = $_POST; }

$required = ['nombre', 'nit'];
foreach ($required as $field) {
  if (empty($data[$field])) {
    http_response_code(400);
    echo json_encode(['error' => "$field is required"]);
    exit;
  }
}

// 2️⃣  Connect to MySQL  ----------------------------------------------------
require_once 'config.php';

try {
  $pdo = new PDO(
    "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
    $db_user,
    $db_pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
  );

  // 3️⃣  Insert into DB  ----------------------------------------------------
  $stmt = $pdo->prepare(
    "INSERT INTO empresa (nombre, nit, direccion, telefono, email, estado)
     VALUES (?, ?, ?, ?, ?, ?)"
  );
  $stmt->execute([
    $data['nombre'],
    $data['nit'],
    $data['direccion'] ?? null,
    $data['telefono']  ?? null,
    $data['email']     ?? null,
    $data['estado']    ?? 1
  ]);

  // 4️⃣  Respond  -----------------------------------------------------------
  echo json_encode([
    'success' => true,
    'id'      => $pdo->lastInsertId()
  ]);

} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
?>