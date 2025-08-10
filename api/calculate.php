*/

// --- api/calculate.php --- (MODIFICADO para pasar la TASA ISD, no el ISD calculado, a la función central)
/*
<?php
// api/calculate.php

/**
 * Endpoint para calcular los impuestos de importación para un solo ítem.
 * Recibe los datos del ítem en formato JSON, los valida, y llama a la función
 * de cálculo principal `calculateImportationDetails`.
 */

require_once '../config/db.php';
require_once '../includes/functions.php';

// Establecer la cabecera de respuesta a JSON
header('Content-Type: application/json');

// Leer y decodificar el cuerpo de la solicitud JSON
$input = json_decode(file_get_contents('php://input'), true);

// --- Validación de Entrada ---
// Validar que los campos requeridos no estén vacíos
if (empty($input['tariffCodeId']) || !isset($input['valorFOB']) || !isset($input['cantidad'])) {
    sendJsonResponse(['success' => false, 'message' => 'Datos incompletos para calcular: Partida, FOB y Cantidad son requeridos.'], 400);
}
// Validar que la cantidad sea un número positivo
if (intval($input['cantidad']) <= 0) {
    sendJsonResponse(['success' => false, 'message' => 'La cantidad debe ser un número positivo.'], 400);
}
// Validar que el valor FOB no sea negativo
if (floatval($input['valorFOB']) < 0) {
    sendJsonResponse(['success' => false, 'message' => 'El valor FOB no puede ser negativo.'], 400);
}

// --- Recolección y Casteo de Datos ---
// Asignar valores de entrada a variables, con valores por defecto si no existen
$isConsidered4x4 = boolval($input['esCourier4x4'] ?? false); 
$costoFleteInternacionalItem = floatval($input['costoFlete'] ?? 0);
$costoSeguroInternacionalItem = floatval($input['costoSeguro'] ?? 0);
$costoAgenteAduanaItem = floatval($input['costoAgenteAduanaItem'] ?? 0); 
$tasaIsdInputPorcentaje = floatval($input['tasaIsdAplicableItem'] ?? 0); // Tasa ISD en %, ej 5
$otrosGastosItem = floatval($input['otrosGastosItem'] ?? 0); 

// --- Llamada a la Lógica de Negocio ---
// Invocar la función central de cálculo con los datos procesados
$results = calculateImportationDetails(
    $pdo,
    floatval($input['valorFOB'] ?? 0), 
    intval($input['cantidad'] ?? 1),
    floatval($input['pesoUnitarioKg'] ?? 0),
    $costoFleteInternacionalItem, 
    $costoSeguroInternacionalItem,
    $costoAgenteAduanaItem, 
    ($tasaIsdInputPorcentaje / 100), // Convertir tasa ISD de porcentaje a decimal para la función
    $otrosGastosItem, 
    $input['tariffCodeId'],
    $isConsidered4x4, 
    floatval($input['profitPercentage'] ?? 0)
);

// --- Envío de Respuesta ---
// Enviar los resultados del cálculo como una respuesta JSON
sendJsonResponse($results);
?>
