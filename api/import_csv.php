*/

// --- api/import_csv.php --- (MODIFICADO para el nuevo orden de prorrateo y cálculo de ISD/Agente Aduana)
/*
<?php
// api/import_csv.php
require_once '../config/db.php';
require_once '../includes/session_handler.php';
require_once '../includes/functions.php'; 

requireLogin();
$userId = $_SESSION['user_id'];
$uploadBaseDir = realpath(__DIR__ . '/../../uploads/csv_files/'); 
if ($uploadBaseDir === false) { $uploadBaseDir = __DIR__ . '/../../uploads/csv_files/'; }
if (!is_dir($uploadBaseDir)) {
    if (!mkdir($uploadBaseDir, 0775, true)) {
        sendJsonResponse(['success' => false, 'message' => 'Error crítico: No se pudo crear el directorio de subidas en: ' . $uploadBaseDir], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csvFile']) && $_FILES['csvFile']['error'] == UPLOAD_ERR_OK && $_FILES['csvFile']['size'] > 0) {
        $tempFilePath = $_FILES['csvFile']['tmp_name'];
        $originalFileName = basename($_FILES['csvFile']['name']);
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

        if ($fileExtension !== 'csv') { sendJsonResponse(['success' => false, 'message' => 'Error: Solo se permiten archivos CSV.'], 400); }
        if ($_FILES['csvFile']['size'] > 5 * 1024 * 1024) { sendJsonResponse(['success' => false, 'message' => 'Error: El archivo CSV es demasiado grande (máx 5MB).'], 400); }

        $sanitizedOriginalName = preg_replace("/[^a-zA-Z0-9._-]/", "", pathinfo($originalFileName, PATHINFO_FILENAME));
        if(empty($sanitizedOriginalName)) $sanitizedOriginalName = "import";
        $storedFileName = $userId . "_" . time() . "_" . uniqid() . "_" . $sanitizedOriginalName . ".csv";
        $storedFilePath = $uploadBaseDir . '/' . $storedFileName;

        if (!move_uploaded_file($tempFilePath, $storedFilePath)) {
            sendJsonResponse(['success' => false, 'message' => 'Error: No se pudo guardar el archivo CSV subido en el servidor. Verifique permisos en: ' . $uploadBaseDir], 500);
        }
        
        $fleteInternacionalGeneral = isset($_POST['fleteGeneralCsv']) ? floatval($_POST['fleteGeneralCsv']) : 0;
        $seguroInternacionalGeneral = isset($_POST['seguroGeneralCsv']) ? floatval($_POST['seguroGeneralCsv']) : 0;
        $profitPercentageGeneral = isset($_POST['profitPercentageCsv']) ? floatval($_POST['profitPercentageCsv']) : 0;
        $gastosAgenteAduanaTotal = isset($_POST['gastosAgenteAduanaCsv']) ? floatval($_POST['gastosAgenteAduanaCsv']) : 0;
        $tasaIsdConfigurableEmbarquePorcentaje = isset($_POST['tasaIsdConfigurableCsv']) ? floatval($_POST['tasaIsdConfigurableCsv']) : 0;
        $gastosBodegaAduanaTotal = isset($_POST['gastosBodegaAduanaCsv']) ? floatval($_POST['gastosBodegaAduanaCsv']) : 0;
        $gastosDemorajeTotal = isset($_POST['gastosDemorajeCsv']) ? floatval($_POST['gastosDemorajeCsv']) : 0;
        $gastosFleteTerrestreTotal = isset($_POST['gastosFleteTerrestreCsv']) ? floatval($_POST['gastosFleteTerrestreCsv']) : 0;
        $gastosVariosTotal = isset($_POST['gastosVariosCsv']) ? floatval($_POST['gastosVariosCsv']) : 0;
        $prorationMethod = isset($_POST['prorationMethodCsv']) && in_array($_POST['prorationMethodCsv'], ['fob', 'weight']) ? $_POST['prorationMethodCsv'] : 'fob';

        $totalOtrosGastosPostNacionalizacion = $gastosBodegaAduanaTotal + $gastosDemorajeTotal + $gastosFleteTerrestreTotal + $gastosVariosTotal;

        $lineasDelCsv = []; $granTotalFOBEmbarque = 0; $granTotalPesoEmbarqueKg = 0; $parseErrors = []; $totalLinesInCsvFile = 0;

        if (($handle = fopen($storedFilePath, "r")) !== FALSE) {
            $header = fgetcsv($handle); $csvLineCounter = 1; 
            while (($row = fgetcsv($handle)) !== FALSE) {
                $csvLineCounter++; $totalLinesInCsvFile++;
                if (count($row) < 4) { $parseErrors[] = "Línea CSV {$csvLineCounter}: Número de columnas insuficiente."; continue; }
                
                $partidaCodigoCsv = trim($row[0] ?? '');
                $cantidadCsv = intval($row[1] ?? 0);
                $pesoUnitarioCsv = floatval($row[2] ?? 0);
                $fobUnitarioCsv = floatval($row[3] ?? 0);
                $descripcionCsv = trim($row[4] ?? ('Ítem CSV ' . $partidaCodigoCsv . ' L' . $csvLineCounter));
                $profitLinea = (isset($row[5]) && is_numeric($row[5])) ? floatval($row[5]) : $profitPercentageGeneral;

                if (empty($partidaCodigoCsv) || $cantidadCsv <= 0 || $fobUnitarioCsv < 0 || $pesoUnitarioCsv < 0) {
                    $parseErrors[] = "Línea CSV {$csvLineCounter}: Datos básicos inválidos (Partida: '{$partidaCodigoCsv}', Cant: {$cantidadCsv}, FOB U: {$fobUnitarioCsv}, Peso U: {$pesoUnitarioCsv}).";
                    continue;
                }
                $stmt_check_tariff = $pdo->prepare("SELECT id FROM tariff_codes WHERE code = :code");
                $stmt_check_tariff->execute([':code' => $partidaCodigoCsv]);
                if (!$stmt_check_tariff->fetch()) {
                     $parseErrors[] = "Línea CSV {$csvLineCounter}: Partida Arancelaria '{$partidaCodigoCsv}' no existe en la base de datos.";
                     continue;
                }
                $fobTotalLinea = $fobUnitarioCsv * $cantidadCsv;
                $pesoTotalLinea = $pesoUnitarioCsv * $cantidadCsv;
                $lineasDelCsv[] = [
                    'line_csv_num' => $csvLineCounter, 'partida_codigo' => $partidaCodigoCsv,
                    'cantidad' => $cantidadCsv, 'peso_unitario_kg' => $pesoUnitarioCsv,
                    'fob_unitario_usd' => $fobUnitarioCsv, 'descripcion' => $descripcionCsv,
                    'profit_percentage_linea' => $profitLinea,
                    'fob_total_linea' => $fobTotalLinea, 'peso_total_linea' => $pesoTotalLinea
                ];
                $granTotalFOBEmbarque += $fobTotalLinea;
                $granTotalPesoEmbarqueKg += $pesoTotalLinea;
            }
            fclose($handle);
        } else { sendJsonResponse(['success' => false, 'message' => 'Error abriendo el archivo CSV guardado para procesar.'], 500); }
        
        $isdTotalCalculadoEmbarque = $granTotalFOBEmbarque * ($tasaIsdConfigurableEmbarquePorcentaje / 100);

        $stmt_import = $pdo->prepare("INSERT INTO csv_imports (user_id, original_filename, stored_filepath, processing_status,
                                      total_flete_internacional, total_seguro_internacional, 
                                      total_agente_aduana, tasa_isd_aplicada, total_isd_pagado,
                                      total_bodega_aduana, total_demoraje, total_flete_terrestre, total_gastos_varios, 
                                      proration_method_used, total_lines) 
                                      VALUES (:user_id, :original_filename, :stored_filepath, 'procesando',
                                      :total_flete_internacional, :total_seguro_internacional,
                                      :total_agente_aduana, :tasa_isd_aplicada, :total_isd_pagado,
                                      :total_bodega_aduana, :total_demoraje, :total_flete_terrestre, :total_gastos_varios, 
                                      :proration_method, :total_lines) RETURNING id");
        $stmt_import->execute([
            ':user_id' => $userId, ':original_filename' => $originalFileName, ':stored_filepath' => $storedFilePath, 
            ':total_flete_internacional' => $fleteInternacionalGeneral, ':total_seguro_internacional' => $seguroInternacionalGeneral,
            ':total_agente_aduana' => $gastosAgenteAduanaTotal, ':tasa_isd_aplicada' => $tasaIsdConfigurableEmbarquePorcentaje,
            ':total_isd_pagado' => $isdTotalCalculadoEmbarque,
            ':total_bodega_aduana' => $gastosBodegaAduanaTotal, ':total_demoraje' => $gastosDemorajeTotal,
            ':total_flete_terrestre' => $gastosFleteTerrestreTotal, ':total_gastos_varios' => $gastosVariosTotal,
            ':proration_method' => $prorationMethod, ':total_lines' => $totalLinesInCsvFile
        ]);
        $csvImportId = $stmt_import->fetchColumn();
        if (!$csvImportId) { unlink($storedFilePath); sendJsonResponse(['success' => false, 'message' => 'Error creando registro de importación.'], 500); }

        if (empty($lineasDelCsv) && !empty($parseErrors)) { /* ... */ }
        if (empty($lineasDelCsv) && empty($parseErrors)) { /* ... */ }

        $isShipmentActually4x4 = ($granTotalFOBEmbarque <= 400 && $granTotalPesoEmbarqueKg <= 4.0);
        
        $processedItemsDetailsForResponse = []; $calculationErrors = [];
        $consolidatedTotals = [
            'total_items_csv' => count($lineasDelCsv),
            'gran_total_fob_embarque' => round($granTotalFOBEmbarque, 2),
            'gran_total_peso_embarque_kg' => round($granTotalPesoEmbarqueKg, 3),
            'flete_general_aplicado_total' => round($fleteInternacionalGeneral, 2),
            'seguro_general_aplicado_total' => round($seguroInternacionalGeneral, 2),
            'gastos_agente_aduana_total_embarque' => round($gastosAgenteAduanaTotal, 2),
            'tasa_isd_aplicada_embarque' => round($tasaIsdConfigurableEmbarquePorcentaje, 2),
            'isd_total_pagado_embarque' => round($isdTotalCalculadoEmbarque, 2),
            'gastos_bodega_aduana_total_embarque' => round($gastosBodegaAduanaTotal, 2),
            'gastos_demoraje_total_embarque' => round($gastosDemorajeTotal, 2),
            'gastos_flete_terrestre_total_embarque' => round($gastosFleteTerrestreTotal, 2),
            'gastos_varios_total_embarque' => round($gastosVariosTotal, 2),
            'proration_method_used' => $prorationMethod,
            'embarque_califica_4x4' => $isShipmentActually4x4,
            'sum_cif_lineas' => 0, 'sum_advalorem_lineas' => 0, 'sum_fodinfa_lineas' => 0,
            'sum_ice_lineas' => 0, 'sum_specific_tax_lineas' => 0, 'sum_iva_lineas' => 0,
            'sum_total_impuestos_lineas' => 0, 'sum_costo_total_estimado_lineas' => 0,
            'sum_pvp_total_lineas' => 0,
            'sum_isd_pagado_lineas' => 0, 'sum_agente_aduana_lineas' => 0,
            'sum_otros_gastos_post_nacionalizacion_lineas' => 0
        ];
        $processedLinesCount = 0;

        foreach ($lineasDelCsv as $itemData) {
            $factorProrrateo = 0;
            if ($prorationMethod === 'fob' && $granTotalFOBEmbarque > 0) {
                $factorProrrateo = $itemData['fob_total_linea'] / $granTotalFOBEmbarque;
            } elseif ($prorationMethod === 'weight' && $granTotalPesoEmbarqueKg > 0) {
                $factorProrrateo = $itemData['peso_total_linea'] / $granTotalPesoEmbarqueKg;
            } elseif (count($lineasDelCsv) > 0) { $factorProrrateo = 1 / count($lineasDelCsv); }
            
            $fleteInternacionalItemProrrateado = $fleteInternacionalGeneral * $factorProrrateo;
            $seguroInternacionalItemProrrateado = $seguroInternacionalGeneral * $factorProrrateo;
            $agenteAduanaItemProrrateado = $gastosAgenteAduanaTotal * $factorProrrateo;
            $otrosGastosPostNacionalizacionItemProrrateado = $totalOtrosGastosPostNacionalizacion * $factorProrrateo;

            $stmt_tariff = $pdo->prepare("SELECT id FROM tariff_codes WHERE code = :code");
            $stmt_tariff->execute([':code' => $itemData['partida_codigo']]);
            $tariffRow = $stmt_tariff->fetch(PDO::FETCH_ASSOC);

            $itemCalculationResult = calculateImportationDetails(
                $pdo, $itemData['fob_unitario_usd'], $itemData['cantidad'], $itemData['peso_unitario_kg'], 
                $fleteInternacionalItemProrrateado, $seguroInternacionalItemProrrateado, 
                $agenteAduanaItemProrrateado, 
                ($tasaIsdConfigurableEmbarquePorcentaje / 100), 
                $otrosGastosPostNacionalizacionItemProrrateado,
                $tariffRow['id'], $isShipmentActually4x4, $itemData['profit_percentage_linea']
            );

            if ($itemCalculationResult['success']) {
                $processedLinesCount++;
                $calcInput = $itemCalculationResult['calculoInput'];
                $params_save_item = [
                    ':user_id' => $userId, ':product_name' => $itemData['descripcion'],
                    ':tariff_code_id' => $tariffRow['id'], ':valor_fob_unitario' => $calcInput['valorFOBUnitario'],
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
                    ':csv_import_id' => $csvImportId, ':csv_import_line_number' => $itemData['line_csv_num']
                ];
                $stmt_save_item = $pdo->prepare("INSERT INTO calculations (user_id, product_name, tariff_code_id, valor_fob_unitario, cantidad, peso_unitario_kg, costo_flete, costo_seguro, agente_aduana_prorrateado_item, isd_pagado_item, otros_gastos_prorrateados_item, es_courier_4x4, cif, ad_valorem, fodinfa, ice, specific_tax, iva, total_impuestos, costo_total_estimado_linea, profit_percentage_applied, cost_price_unit_after_import, profit_amount_unit, pvp_unit, pvp_total_line, csv_import_id, csv_import_line_number) VALUES (:user_id, :product_name, :tariff_code_id, :valor_fob_unitario, :cantidad, :peso_unitario_kg, :costo_flete, :costo_seguro, :agente_aduana_prorrateado_item, :isd_pagado_item, :otros_gastos_prorrateados_item, :es_courier_4x4, :cif, :ad_valorem, :fodinfa, :ice, :specific_tax, :iva, :total_impuestos, :costo_total_estimado_linea, :profit_percentage_applied, :cost_price_unit_after_import, :profit_amount_unit, :pvp_unit, :pvp_total_line, :csv_import_id, :csv_import_line_number)");
                $stmt_save_item->execute($params_save_item);

                $processedItemsDetailsForResponse[] = [
                    'line_csv_num' => $itemData['line_csv_num'], 'description' => $itemData['descripcion'], 
                    'partida_code_csv' => $itemData['partida_codigo'], 'calculation' => $itemCalculationResult
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
                $consolidatedTotals['sum_isd_pagado_lineas'] = ($consolidatedTotals['sum_isd_pagado_lineas'] ?? 0) + $calcInput['isdPagadoItem'];
                $consolidatedTotals['sum_agente_aduana_lineas'] = ($consolidatedTotals['sum_agente_aduana_lineas'] ?? 0) + $calcInput['costoAgenteAduanaItem'];
                $consolidatedTotals['sum_otros_gastos_post_nacionalizacion_lineas'] = ($consolidatedTotals['sum_otros_gastos_post_nacionalizacion_lineas'] ?? 0) + $calcInput['otrosGastosPostNacionalizacionItem'];

            } else {
                $calculationErrors[] = "Línea CSV {$itemData['line_csv_num']} ({$itemData['partida_codigo']}): " . $itemCalculationResult['message'];
            }
        }
        
        $finalStatus = !empty($calculationErrors) || !empty($parseErrors) ? "completado_con_errores" : "completado";
        $finalErrorsList = array_merge($parseErrors, $calculationErrors);

        $stmt_update_import = $pdo->prepare("UPDATE csv_imports SET 
            processing_status = :status, processed_lines = :processed_lines, 
            error_count = :error_count, consolidated_summary_json = :summary 
            WHERE id = :id");
        $stmt_update_import->execute([
            ':status' => $finalStatus, ':processed_lines' => $processedLinesCount,
            ':error_count' => count($finalErrorsList), ':summary' => json_encode($consolidatedTotals),
            ':id' => $csvImportId
        ]);
        
        sendJsonResponse([
            'success' => empty($finalErrorsList), 
            'message' => empty($finalErrorsList) ? 'CSV procesado, calculado y líneas guardadas.' : 'CSV procesado con algunos errores. Revise los detalles.', 
            'csv_import_id' => $csvImportId, 
            'items_processed_details' => $processedItemsDetailsForResponse, 
            'consolidated_results' => $consolidatedTotals, 
            'errors_list' => $finalErrorsList
        ]);

    } else { sendJsonResponse(['success' => false, 'message' => 'Error subiendo archivo CSV o archivo vacío/inválido.'], 400); }
} else { sendJsonResponse(['success' => false, 'message' => 'Método no permitido.'], 405); }
?>
