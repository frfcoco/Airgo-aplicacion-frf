<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST allowed']);
    exit;
}

// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

    // Build the query dynamically to ensure parameters match_frf
    $fields = [
        'nombre_comercial',
        'razon_social',
        'nit',
        'telefono',
        'sigla_empresa',
        'banco',
        'tipo_cuenta',
        'numero_cuenta',
        'servicio_cliente',
        'reclamos_pasajeros',
        'ciudad',
        'activo',
        'modalidad_1',
        'modalidad_2',
        'equipo_autorizado',
        'especificaciones_operacion'
    ];

    $placeholders = str_repeat('?,', count($fields) - 1) . '?';
    
    $sql = "INSERT INTO empresa (" . implode(', ', $fields) . ") 
            VALUES (" . $placeholders . ")";

    $stmt = $pdo->prepare($sql);

    // Build the values array in the same order as fields
    $values = [];
    foreach ($fields as $field) {
        $values[] = $data[$field] ?? null;
    }

    $stmt->execute($values);

    echo json_encode([
        'success' => true,
        'id' => $pdo->lastInsertId(),
        'sql' => $sql,  // For debugging
        'values' => $values  // For debugging
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'sql' => $sql ?? null,  // For debugging
        'values' => $values ?? null  // For debugging
    ]);
}
?>