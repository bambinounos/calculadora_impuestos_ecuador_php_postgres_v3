*/

// --- api/calculate.php --- (MODIFICADO para pasar la TASA ISD, no el ISD calculado, a la función central)
/*
<?php
// api/calculate.php
require_once '../config/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['tariffCodeId']) || !isset($input['valorFOB']) || !isset($input['cantidad'])) {
    sendJsonResponse(['success' => false, 'message' => 'Datos incompletos para calcular: Partida, FOB y Cantidad son requeridos.'], 400);
}
if (intval($input['cantidad']) <= 0) {
    sendJsonResponse(['success' => false, 'message' => 'La cantidad debe ser un número positivo.'], 400);
}
if (floatval($input['valorFOB']) < 0) {
    sendJsonResponse(['success' => false, 'message' => 'El valor FOB no puede ser negativo.'], 400);
}

$isConsidered4x4 = boolval($input['esCourier4x4'] ?? false); 
$costoFleteInternacionalItem = floatval($input['costoFlete'] ?? 0);
$costoSeguroInternacionalItem = floatval($input['costoSeguro'] ?? 0);
$costoAgenteAduanaItem = floatval($input['costoAgenteAduanaItem'] ?? 0); 
$tasaIsdInputPorcentaje = floatval($input['tasaIsdAplicableItem'] ?? 0); // Tasa ISD en %, ej 5
$otrosGastosItem = floatval($input['otrosGastosItem'] ?? 0); 

$results = calculateImportationDetails(
    $pdo,
    floatval($input['valorFOB'] ?? 0), 
    intval($input['cantidad'] ?? 1),
    floatval($input['pesoUnitarioKg'] ?? 0),
    $costoFleteInternacionalItem, 
    $costoSeguroInternacionalItem,
    $costoAgenteAduanaItem, 
    ($tasaIsdInputPorcentaje / 100), // Convertir tasa ISD a decimal para la función
    $otrosGastosItem, 
    $input['tariffCodeId'],
    $isConsidered4x4, 
    floatval($input['profitPercentage'] ?? 0)
);

sendJsonResponse($results);
?>
