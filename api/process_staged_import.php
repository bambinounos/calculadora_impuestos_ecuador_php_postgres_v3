<?php
// api/process_staged_import.php

/**
 * Endpoint para procesar los datos de importación que han sido
 * verificados y posiblemente editados por el usuario en la interfaz de 'staging'.
 * Recibe un objeto JSON con los datos de los ítems y los costos generales del embarque.
 */

require_once '../config/db.php';
require_once '../includes/session_handler.php';
require_once '../includes/functions.php';

requireLogin();
$userId = $_SESSION['user_id'];

// Leer y decodificar el cuerpo de la solicitud JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['items']) || !isset($input['general_costs'])) {
    sendJsonResponse(['success' => false, 'message' => 'Datos de entrada inválidos o incompletos.'], 400);
}

// --- Recolección de Datos de Entrada ---
$lineasDelCsv = $input['items'];
$costs = $input['general_costs'];

// Extraer costos generales
$fleteInternacionalGeneral = floatval($costs['fleteGeneralCsv'] ?? 0);
$seguroInternacionalGeneral = floatval($costs['seguroGeneralCsv'] ?? 0);
$profitPercentageGeneral = floatval($costs['profitPercentageCsv'] ?? 0);
$gastosAgenteAduanaTotal = floatval($costs['gastosAgenteAduanaCsv'] ?? 0);
$tasaIsdConfigurableEmbarquePorcentaje = floatval($costs['tasaIsdConfigurableCsv'] ?? 0);
$gastosBodegaAduanaTotal = floatval($costs['gastosBodegaAduanaCsv'] ?? 0);
$gastosDemorajeTotal = floatval($costs['gastosDemorajeCsv'] ?? 0);
$gastosFleteTerrestreTotal = floatval($costs['gastosFleteTerrestreCsv'] ?? 0);
$gastosDevolucionContenedorTotal = floatval($costs['gastosDevolucionContenedorCsv'] ?? 0);
$gastosDocFeeDestinoTotal = floatval($costs['gastosDocFeeDestinoCsv'] ?? 0);
$gastosFreetimeExtTotal = floatval($costs['gastosFreetimeExtCsv'] ?? 0);
$gastosHeavyLoadTotal = floatval($costs['gastosHeavyLoadCsv'] ?? 0);
$gastosContainerProtectTotal = floatval($costs['gastosContainerProtectCsv'] ?? 0);
$gastosDropOffTotal = floatval($costs['gastosDropOffCsv'] ?? 0);
$gastosServicioImportacionTotal = floatval($costs['gastosServicioImportacionCsv'] ?? 0);
$gastosManejoTerminalTotal = floatval($costs['gastosManejoTerminalCsv'] ?? 0);
$gastosEmisionDocumentoTransporteTotal = floatval($costs['gastosEmisionDocumentoTransporteCsv'] ?? 0);
$gastosBancariosTotal = floatval($costs['gastosBancariosCsv'] ?? 0);
$gastosVariosTotal = floatval($costs['gastosVariosCsv'] ?? 0);
$prorationMethod = isset($costs['prorationMethodCsv']) && in_array($costs['prorationMethodCsv'], ['fob', 'weight']) ? $costs['prorationMethodCsv'] : 'fob';

// --- Lógica de Cálculo (Movida desde import_csv.php) ---

$totalOtrosGastosPostNacionalizacion = $gastosBodegaAduanaTotal + $gastosDemorajeTotal + $gastosFleteTerrestreTotal + $gastosVariosTotal + $gastosDevolucionContenedorTotal + $gastosDocFeeDestinoTotal + $gastosFreetimeExtTotal + $gastosHeavyLoadTotal + $gastosContainerProtectTotal + $gastosDropOffTotal + $gastosServicioImportacionTotal + $gastosManejoTerminalTotal + $gastosEmisionDocumentoTransporteTotal + $gastosBancariosTotal;

$granTotalFOBEmbarque = 0;
$granTotalPesoEmbarqueKg = 0;
foreach ($lineasDelCsv as $item) {
    $granTotalFOBEmbarque += floatval($item['fob_unitario_usd']) * intval($item['cantidad']);
    $granTotalPesoEmbarqueKg += floatval($item['peso_unitario_kg']) * intval($item['cantidad']);
}

$isdTotalCalculadoEmbarque = $granTotalFOBEmbarque * ($tasaIsdConfigurableEmbarquePorcentaje / 100);

// Crear un registro de importación (similar a como se hacía antes)
$stmt_import = $pdo->prepare("INSERT INTO csv_imports (user_id, original_filename, stored_filepath, processing_status, total_flete_internacional, total_seguro_internacional, total_agente_aduana, tasa_isd_aplicada, total_isd_pagado, total_bodega_aduana, total_demoraje, total_flete_terrestre, total_gastos_varios, proration_method_used, total_lines) VALUES (:uid, :fname, :fpath, 'procesando', :flete, :seguro, :agente, :isd_tasa, :isd_paid, :bodega, :demoraje, :fterrestre, :gvarios, :pmethod, :tlines) RETURNING id");
$stmt_import->execute([
    ':uid' => $userId,
    ':fname' => $input['original_filename'] ?? 'staged_import.json',
    ':fpath' => $input['stored_filepath'] ?? 'N/A',
    ':flete' => $fleteInternacionalGeneral, ':seguro' => $seguroInternacionalGeneral,
    ':agente' => $gastosAgenteAduanaTotal, ':isd_tasa' => $tasaIsdConfigurableEmbarquePorcentaje,
    ':isd_paid' => $isdTotalCalculadoEmbarque, ':bodega' => $gastosBodegaAduanaTotal,
    ':demoraje' => $gastosDemorajeTotal, ':fterrestre' => $gastosFleteTerrestreTotal,
    ':gvarios' => $gastosVariosTotal, // Simplificado para el log, el total ya está en otra variable
    ':pmethod' => $prorationMethod, ':tlines' => count($lineasDelCsv)
]);
$csvImportId = $stmt_import->fetchColumn();

if (!$csvImportId) {
    sendJsonResponse(['success' => false, 'message' => 'Error creando registro de importación para el cálculo final.'], 500);
}

$isShipmentActually4x4 = ($granTotalFOBEmbarque <= 400 && $granTotalPesoEmbarqueKg <= 4.0);
$processedItemsDetailsForResponse = [];
$calculationErrors = [];
$processedLinesCount = 0;

$consolidatedTotals = [
    'total_items_csv' => count($lineasDelCsv),
    'gran_total_fob_embarque' => round($granTotalFOBEmbarque, 2),
    'gran_total_peso_embarque_kg' => round($granTotalPesoEmbarqueKg, 3),
    'sum_cif_lineas' => 0, 'sum_advalorem_lineas' => 0, 'sum_fodinfa_lineas' => 0,
    'sum_ice_lineas' => 0, 'sum_specific_tax_lineas' => 0, 'sum_iva_lineas' => 0,
    'sum_total_impuestos_lineas' => 0, 'sum_costo_total_estimado_lineas' => 0,
    'sum_pvp_total_lineas' => 0,
    'sum_isd_pagado_lineas' => 0, 'sum_agente_aduana_lineas' => 0,
    'sum_otros_gastos_post_nacionalizacion_lineas' => 0
];

$tariffCodes = array_unique(array_column($lineasDelCsv, 'partida_codigo'));
$tariffMap = [];

if (!empty($tariffCodes)) {
    $placeholders = str_repeat('?,', count($tariffCodes) - 1) . '?';
    $stmt_batch_tariff = $pdo->prepare("SELECT id, code FROM tariff_codes WHERE code IN ($placeholders)");
    $stmt_batch_tariff->execute($tariffCodes);
    while ($row = $stmt_batch_tariff->fetch(PDO::FETCH_ASSOC)) {
        $tariffMap[$row['code']] = $row['id'];
    }
}

foreach ($lineasDelCsv as $itemData) {
    if (!isset($tariffMap[$itemData['partida_codigo']])) {
        $calculationErrors[] = "Ítem '{$itemData['descripcion']}': La partida arancelaria '{$itemData['partida_codigo']}' no es válida o no fue seleccionada.";
        continue;
    }
    $tariffId = $tariffMap[$itemData['partida_codigo']];

    $fobTotalLinea = floatval($itemData['fob_unitario_usd']) * intval($itemData['cantidad']);
    $pesoTotalLinea = floatval($itemData['peso_unitario_kg']) * intval($itemData['cantidad']);

    $factorProrrateo = 0;
    if ($prorationMethod === 'fob' && $granTotalFOBEmbarque > 0) {
        $factorProrrateo = $fobTotalLinea / $granTotalFOBEmbarque;
    } elseif ($prorationMethod === 'weight' && $granTotalPesoEmbarqueKg > 0) {
        $factorProrrateo = $pesoTotalLinea / $granTotalPesoEmbarqueKg;
    }

    $fleteInternacionalItem = $fleteInternacionalGeneral * $factorProrrateo;
    $seguroInternacionalItem = $seguroInternacionalGeneral * $factorProrrateo;
    $agenteAduanaItemProrrateado = $gastosAgenteAduanaTotal * $factorProrrateo;
    $otrosGastosPostNacionalizacionItemProrrateado = $totalOtrosGastosPostNacionalizacion * $factorProrrateo;

    $profitLinea = isset($itemData['profit_percentage_linea']) ? floatval($itemData['profit_percentage_linea']) : $profitPercentageGeneral;

    $itemCalculationResult = calculateImportationDetails(
        $pdo, $itemData['fob_unitario_usd'], $itemData['cantidad'], $itemData['peso_unitario_kg'],
        $fleteInternacionalItem, $seguroInternacionalItem,
        $agenteAduanaItemProrrateado,
        ($tasaIsdConfigurableEmbarquePorcentaje / 100),
        $otrosGastosPostNacionalizacionItemProrrateado,
        $tariffId, $isShipmentActually4x4, $profitLinea
    );

    if ($itemCalculationResult['success']) {
        $processedLinesCount++;
        // Guardar en la tabla 'calculations'
        $calcInput = $itemCalculationResult['calculoInput'];
        $params_save_item = [
            ':user_id' => $userId, ':product_name' => $itemData['descripcion'],
            ':product_sku' => $itemData['sku'] ?? null,
            ':tariff_code_id' => $tariffId, ':valor_fob_unitario' => $calcInput['valorFOBUnitario'],
            ':cantidad' => $calcInput['cantidad'], ':peso_unitario_kg' => $calcInput['pesoUnitarioKg'],
            ':costo_flete' => $calcInput['costoFleteInternacionalItem'],
            ':costo_seguro' => $calcInput['costoSeguroInternacionalItem'],
            ':agente_aduana_prorrateado_item' => $calcInput['costoAgenteAduanaItem'],
            ':isd_pagado_item' => $calcInput['isdPagadoItem'],
            ':otros_gastos_prorrateados_item' => $calcInput['otrosGastosPostNacionalizacionItem'],
            ':es_courier_4x4' => $calcInput['isShipmentConsidered4x4'] ? 1:0,
            ':cif' => $itemCalculationResult['cif'], ':ad_valorem' => $itemCalculationResult['adValorem'],
            ':fodinfa' => $itemCalculationResult['fodinfa'], ':ice' => $itemCalculationResult['ice'],
            ':specific_tax' => $itemCalculationResult['specificTax'], ':iva' => $itemCalculationResult['iva'],
            ':total_impuestos' => $itemCalculationResult['totalImpuestos'],
            ':costo_total_estimado_linea' => $itemCalculationResult['costoTotalEstimadoLinea'],
            ':profit_percentage_applied' => $calcInput['profitPercentageApplied'],
            ':cost_price_unit_after_import' => $itemCalculationResult['cost_price_unit_after_import'],
            ':profit_amount_unit' => $itemCalculationResult['profit_amount_unit'],
            ':pvp_unit' => $itemCalculationResult['pvp_unit'],
            ':pvp_total_line' => $itemCalculationResult['pvp_total_line'],
            ':csv_import_id' => $csvImportId,
            ':csv_import_line_number' => $itemData['line_csv_num'] ?? null
        ];
        $stmt_save_item = $pdo->prepare("INSERT INTO calculations (user_id, product_name, product_sku, tariff_code_id, valor_fob_unitario, cantidad, peso_unitario_kg, costo_flete, costo_seguro, agente_aduana_prorrateado_item, isd_pagado_item, otros_gastos_prorrateados_item, es_courier_4x4, cif, ad_valorem, fodinfa, ice, specific_tax, iva, total_impuestos, costo_total_estimado_linea, profit_percentage_applied, cost_price_unit_after_import, profit_amount_unit, pvp_unit, pvp_total_line, csv_import_id, csv_import_line_number) VALUES (:user_id, :product_name, :product_sku, :tariff_code_id, :valor_fob_unitario, :cantidad, :peso_unitario_kg, :costo_flete, :costo_seguro, :agente_aduana_prorrateado_item, :isd_pagado_item, :otros_gastos_prorrateados_item, :es_courier_4x4, :cif, :ad_valorem, :fodinfa, :ice, :specific_tax, :iva, :total_impuestos, :costo_total_estimado_linea, :profit_percentage_applied, :cost_price_unit_after_import, :profit_amount_unit, :pvp_unit, :pvp_total_line, :csv_import_id, :csv_import_line_number)");
        $stmt_save_item->execute($params_save_item);

        $processedItemsDetailsForResponse[] = [
            'line_csv_num' => $itemData['line_csv_num'] ?? 'N/A',
            'description' => $itemData['descripcion'],
            'calculation' => $itemCalculationResult
        ];

        // Sumar a los totales consolidados
        $consolidatedTotals['sum_cif_lineas'] += $itemCalculationResult['cif'];
        $consolidatedTotals['sum_advalorem_lineas'] += $itemCalculationResult['adValorem'];
        $consolidatedTotals['sum_fodinfa_lineas'] += $itemCalculationResult['fodinfa'];
        $consolidatedTotals['sum_ice_lineas'] += $itemCalculationResult['ice'];
        $consolidatedTotals['sum_specific_tax_lineas'] += $itemCalculationResult['specificTax'];
        $consolidatedTotals['sum_iva_lineas'] += $itemCalculationResult['iva'];
        $consolidatedTotals['sum_total_impuestos_lineas'] += $itemCalculationResult['totalImpuestos'];
        $consolidatedTotals['sum_costo_total_estimado_lineas'] += $itemCalculationResult['costoTotalEstimadoLinea'];
        $consolidatedTotals['sum_pvp_total_lineas'] += $itemCalculationResult['pvp_total_line'];
        $consolidatedTotals['sum_isd_pagado_lineas'] += $calcInput['isdPagadoItem'];
        $consolidatedTotals['sum_agente_aduana_lineas'] += $calcInput['costoAgenteAduanaItem'];
        $consolidatedTotals['sum_otros_gastos_post_nacionalizacion_lineas'] += $calcInput['otrosGastosPostNacionalizacionItem'];

    } else {
        $calculationErrors[] = "Ítem '{$itemData['descripcion']}': " . $itemCalculationResult['message'];
    }
}

// Actualizar el estado final del registro de importación
$finalStatus = !empty($calculationErrors) ? "completado_con_errores" : "completado";
$stmt_update_import = $pdo->prepare("UPDATE csv_imports SET processing_status = :status, processed_lines = :processed, error_count = :errors, consolidated_summary_json = :summary WHERE id = :id");
$stmt_update_import->execute([
    ':status' => $finalStatus,
    ':processed' => $processedLinesCount,
    ':errors' => count($calculationErrors),
    ':summary' => json_encode($consolidatedTotals),
    ':id' => $csvImportId
]);

sendJsonResponse([
    'success' => empty($calculationErrors),
    'message' => empty($calculationErrors) ? 'Cálculo finalizado y guardado correctamente.' : 'Proceso finalizado con errores. Revise los detalles.',
    'csv_import_id' => $csvImportId,
    'items_processed_details' => $processedItemsDetailsForResponse,
    'consolidated_results' => $consolidatedTotals,
    'errors_list' => $calculationErrors
]);

?>
