*/

// --- api/calculate.php ---
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


// Para un cálculo individual, el flag 'esCourier4x4' del ítem determina si SE CONSIDERA 4x4
$isConsidered4x4 = boolval($input['esCourier4x4'] ?? false); 
// Aquí, el flete y seguro son los totales para esta línea/ítem específico que el usuario ingresa.
$costoFleteItem = floatval($input['costoFlete'] ?? 0);
$costoSeguroItem = floatval($input['costoSeguro'] ?? 0);


$results = calculateImportationDetails(
    $pdo,
    floatval($input['valorFOB'] ?? 0), // FOB Unitario
    intval($input['cantidad'] ?? 1),
    floatval($input['pesoUnitarioKg'] ?? 0),
    $costoFleteItem, // Flete total para esta línea
    $costoSeguroItem, // Seguro total para esta línea
    $input['tariffCodeId'],
    $isConsidered4x4, // Para un cálculo individual, este flag del ítem es el que cuenta
    floatval($input['profitPercentage'] ?? 0)
);

sendJsonResponse($results);
?>
