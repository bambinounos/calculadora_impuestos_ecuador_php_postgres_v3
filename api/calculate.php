*/

// --- api/calculate.php --- (MODIFICADO para usar la función reutilizable y aceptar profitPercentage)
/*
<?php
// api/calculate.php
require_once '../config/db.php';
require_once '../includes/functions.php'; // Asumimos que calculateImportationDetails está aquí o incluida

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

// Validar entradas básicas
if (empty($input['tariffCodeId']) || !isset($input['valorFOB']) || !isset($input['cantidad'])) {
    sendJsonResponse(['success' => false, 'message' => 'Datos incompletos para calcular.'], 400);
}

$results = calculateImportationDetails(
    $pdo, // Pasar la conexión PDO
    floatval($input['valorFOB'] ?? 0),
    intval($input['cantidad'] ?? 1),
    floatval($input['pesoUnitarioKg'] ?? 0),
    floatval($input['costoFlete'] ?? 0),
    floatval($input['costoSeguro'] ?? 0),
    $input['tariffCodeId'],
    boolval($input['esCourier4x4'] ?? false),
    floatval($input['profitPercentage'] ?? 0) // NUEVO: Porcentaje de ganancia
);

sendJsonResponse($results);
?>
