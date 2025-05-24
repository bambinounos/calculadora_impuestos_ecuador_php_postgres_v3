*/

// --- api/import_csv.php --- (MODIFICADO SIGNIFICATIVAMENTE)
/*
<?php
// api/import_csv.php
require_once '../config/db.php';
require_once '../includes/session_handler.php';
require_once '../includes/functions.php'; 

requireLogin();
$userId = $_SESSION['user_id'];

// Directorio de subida FUERA de public_html o protegido.
// Ejemplo: si 'api' está en 'public_html/api/', entonces '../../uploads/' está un nivel arriba de 'public_html'.
$uploadBaseDir = realpath(__DIR__ . '/../../uploads/csv_files/'); 
if ($uploadBaseDir === false) { // Fallback si realpath falla (ej. dir no existe aun)
    $uploadBaseDir = __DIR__ . '/../../uploads/csv_files/';
}
if (!is_dir($uploadBaseDir)) {
    if (!mkdir($uploadBaseDir, 0775, true)) { // Usar 0775 para que el grupo del servidor web pueda escribir si es necesario
        sendJsonResponse(['success' => false, 'message' => 'Error crítico: No se pudo crear el directorio de subidas en: ' . $uploadBaseDir], 500);
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csvFile']) && $_FILES['csvFile']['error'] == UPLOAD_ERR_OK && $_FILES['csvFile']['size'] > 0) {
        $tempFilePath = $_FILES['csvFile']['tmp_name'];
        $originalFileName = basename($_FILES['csvFile']['name']);
        $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

        if ($fileExtension !== 'csv') {
            sendJsonResponse(['success' => false, 'message' => 'Error: Solo se permiten archivos CSV.'], 400);
        }
        if ($_FILES['csvFile']['size'] > 5 * 1024 * 1024) { // Límite de 5MB
            sendJsonResponse(['success' => false, 'message' => 'Error: El archivo CSV es demasiado grande (máx 5MB).'], 400);
        }

        $sanitizedOriginalName = preg_replace("/[^a-zA-Z0-9._-]/", "", pathinfo($originalFileName, PATHINFO_FILENAME));
        if(empty($sanitizedOriginalName)) $sanitizedOriginalName = "import"; // Nombre por defecto si el original no tiene caracteres válidos
        $storedFileName = $userId . "_" . time() . "_" . uniqid() . "_" . $sanitizedOriginalName . ".csv";
        $storedFilePath = $uploadBaseDir . '/' . $storedFileName;

        if (!move_uploaded_file($tempFilePath, $storedFilePath)) {
            sendJsonResponse(['success' => false, 'message' => 'Error: No se pudo guardar el archivo CSV subido en el servidor. Verifique permisos en: ' . $uploadBaseDir], 500);
        }

        $processingStatus = "procesando";
        $stmt_import = $pdo->prepare("INSERT INTO csv_imports (user_id, original_filename, stored_filepath, processing_status) 
                                      VALUES (:user_id, :original_filename, :stored_filepath, :processing_status) RETURNING id");
        $stmt_import->execute([
            ':user_id' => $userId,
            ':original_filename' => $originalFileName,
            ':stored_filepath' => $storedFilePath, 
            ':processing_status' => $processingStatus
        ]);
        $csvImportId = $stmt_import->fetchColumn();

        if (!$csvImportId) {
            unlink($storedFilePath); 
            sendJsonResponse(['success' => false, 'message' => 'Error creando registro de importación en la base de datos.'], 500);
        }

        $fleteGeneralTotalEmbarque = isset($_POST['fleteGeneralCsv']) ? floatval($_POST['fleteGeneralCsv']) : 0;
        $seguroGeneralTotalEmbarque = isset($_POST['seguroGeneralCsv']) ? floatval($_POST['seguroGeneralCsv']) : 0;
        $profitPercentageGeneral = isset($_POST['profitPercentageCsv']) ? floatval($_POST['profitPercentageCsv']) : 0;

        $lineasDelCsv = [];
        $granTotalFOBEmbarque = 0;
        $granTotalPesoEmbarqueKg = 0;
        $parseErrors = [];
        $totalLinesInCsvFile = 0; // Total de líneas de datos leídas

        if (($handle = fopen($storedFilePath, "r")) !== FALSE) {
            $header = fgetcsv($handle); $csvLineCounter = 1; 
            while (($row = fgetcsv($handle)) !== FALSE) {
                $csvLineCounter++; // Número de línea en el archivo CSV original
                $totalLinesInCsvFile++;
                
                if (count($row) < 4) {
                    $parseErrors[] = "Línea CSV {$csvLineCounter}: Número de columnas insuficiente. Se esperan al menos 4.";
                    continue;
                }

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
        } else {
             sendJsonResponse(['success' => false, 'message' => 'Error abriendo el archivo CSV guardado para procesar.'], 500);
        }
        
        $stmt_update_lines = $pdo->prepare("UPDATE csv_imports SET total_lines = :total_lines WHERE id = :id");
        $stmt_update_lines->execute([':total_lines' => $totalLinesInCsvFile, ':id' => $csvImportId]);

        if (empty($lineasDelCsv) && !empty($parseErrors)) {
            $finalStatus = "fallido_parseo";
            $stmt_final_update = $pdo->prepare("UPDATE csv_imports SET processing_status = :status, error_count = :ecount WHERE id = :id");
            $stmt_final_update->execute([':status' => $finalStatus, ':ecount' => count($parseErrors), ':id' => $csvImportId]);
            sendJsonResponse(['success' => false, 'message' => 'No se procesaron ítems. Errores de formato en CSV.', 'errors_list' => $parseErrors, 'csv_import_id' => $csvImportId], 400);
        }
         if (empty($lineasDelCsv) && empty($parseErrors)) {
            $finalStatus = "vacío";
            $stmt_final_update = $pdo->prepare("UPDATE csv_imports SET processing_status = :status WHERE id = :id");
            $stmt_final_update->execute([':status' => $finalStatus, ':id' => $csvImportId]);
            sendJsonResponse(['success' => false, 'message' => 'El CSV no contiene datos de ítems válidos después de la cabecera.', 'csv_import_id' => $csvImportId], 400);
        }

        $isShipmentActually4x4 = ($granTotalFOBEmbarque <= 400 && $granTotalPesoEmbarqueKg <= 4.0);
        
        $processedItemsDetailsForResponse = [];
        $calculationErrors = [];
        $consolidatedTotals = [
            'total_items_csv' => count($lineasDelCsv),
            'gran_total_fob_embarque' => round($granTotalFOBEmbarque, 2),
            'gran_total_peso_embarque_kg' => round($granTotalPesoEmbarqueKg, 3),
            'flete_general_aplicado_total' => round($fleteGeneralTotalEmbarque, 2),
            'seguro_general_aplicado_total' => round($seguroGeneralTotalEmbarque, 2),
            'embarque_califica_4x4' => $isShipmentActually4x4,
            'sum_cif_lineas' => 0, 'sum_advalorem_lineas' => 0, 'sum_fodinfa_lineas' => 0,
            'sum_ice_lineas' => 0, 'sum_specific_tax_lineas' => 0, 'sum_iva_lineas' => 0,
            'sum_total_impuestos_lineas' => 0, 'sum_costo_total_estimado_lineas' => 0,
            'sum_pvp_total_lineas' => 0
        ];
        $processedLinesCount = 0;

        foreach ($lineasDelCsv as $itemData) {
            $factorProrrateoFOB = ($granTotalFOBEmbarque > 0) ? ($itemData['fob_total_linea'] / $granTotalFOBEmbarque) : (1 / count($lineasDelCsv));
            $fleteItemProrrateado = $fleteGeneralTotalEmbarque * $factorProrrateoFOB;
            $seguroItemProrrateado = $seguroGeneralTotalEmbarque * $factorProrrateoFOB;

            $stmt_tariff = $pdo->prepare("SELECT id FROM tariff_codes WHERE code = :code");
            $stmt_tariff->execute([':code' => $itemData['partida_codigo']]);
            $tariffRow = $stmt_tariff->fetch(PDO::FETCH_ASSOC);

            $itemCalculationResult = calculateImportationDetails(
                $pdo, $itemData['fob_unitario_usd'], $itemData['cantidad'], $itemData['peso_unitario_kg'], 
                $fleteItemProrrateado, $seguroItemProrrateado, $tariffRow['id'], 
                $isShipmentActually4x4, $itemData['profit_percentage_linea']
            );

            if ($itemCalculationResult['success']) {
                $processedLinesCount++;
                $calcInput = $itemCalculationResult['calculoInput'];
                $stmt_save_item = $pdo->prepare("INSERT INTO calculations (
                    user_id, product_name, tariff_code_id, valor_fob_unitario, cantidad, peso_unitario_kg,
                    costo_flete, costo_seguro, es_courier_4x4, 
                    cif, ad_valorem, fodinfa, ice, specific_tax, iva, total_impuestos, costo_total_estimado_linea,
                    profit_percentage_applied, cost_price_unit_after_import, profit_amount_unit, pvp_unit, pvp_total_line,
                    csv_import_id, csv_import_line_number
                ) VALUES (
                    :user_id, :product_name, :tariff_code_id, :valor_fob_unitario, :cantidad, :peso_unitario_kg,
                    :costo_flete, :costo_seguro, :es_courier_4x4, 
                    :cif, :ad_valorem, :fodinfa, :ice, :specific_tax, :iva, :total_impuestos, :costo_total_estimado_linea,
                    :profit_percentage_applied, :cost_price_unit_after_import, :profit_amount_unit, :pvp_unit, :pvp_total_line,
                    :csv_import_id, :csv_import_line_number
                )");
                $stmt_save_item->execute([
                    ':user_id' => $userId, ':product_name' => $itemData['descripcion'],
                    ':tariff_code_id' => $tariffRow['id'], ':valor_fob_unitario' => $calcInput['valorFOBUnitario'],
                    ':cantidad' => $calcInput['cantidad'], ':peso_unitario_kg' => $calcInput['pesoUnitarioKg'],
                    ':costo_flete' => $calcInput['costoFleteItem'], ':costo_seguro' => $calcInput['costoSeguroItem'],
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
                ]);

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
