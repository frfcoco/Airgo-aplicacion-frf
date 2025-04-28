<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST allowed']);
    exit;
}

$payload = file_get_contents('php://input');
$data    = json_decode($payload, true);

if (!$data) { $data = $_POST; }

$required = ['nombre_comercial', 'razon_social', 'nit'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "$field is required"]);
        exit;
    }
}

require_once 'config.php';

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->prepare(
        "INSERT INTO empresa (
            nombre_comercial, razon_social, nit, telefono, sigla_empresa, banco, tipo_cuenta, numero_cuenta,
            servicio_cliente, reclamos_pasajeros, ciudad, activo, modalidad_1, modalidad_2, equipo_autorizado, especificaciones_operacion
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->execute([
        $data['nombre_comercial'],
        $data['razon_social'],
        $data['nit'],
        $data['telefono'] ?? null,
        $data['sigla_empresa'] ?? null,
        $data['banco'] ?? null,
        $data['tipo_cuenta'] ?? null,
        $data['numero_cuenta'] ?? null,
        $data['servicio_cliente'] ?? null,
        $data['reclamos_pasajeros'] ?? null,
        $data['ciudad'] ?? null,
        $data['activo'] ?? 1,
        $data['modalidad_1'] ?? null,
        $data['modalidad_2'] ?? null,
        $data['equipo_autorizado'] ?? null,
        $data['especificaciones_operacion'] ?? null
    ]);

    echo json_encode([
        'success' => true,
        'id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>