*/

// --- api/import_csv.php ---
/*
<?php
// api/import_csv.php
require_once '../config/db.php';
require_once '../includes/session_handler.php';
require_once '../includes/functions.php'; // Donde está calculateImportationDetails

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csvFile']) && $_FILES['csvFile']['error'] == UPLOAD_ERR_OK && $_FILES['csvFile']['size'] > 0) {
        $csvFile = $_FILES['csvFile']['tmp_name'];
        
        // Obtener valores globales del formulario
        $fleteGeneralTotalEmbarque = isset($_POST['fleteGeneralCsv']) ? floatval($_POST['fleteGeneralCsv']) : 0;
        $seguroGeneralTotalEmbarque = isset($_POST['seguroGeneralCsv']) ? floatval($_POST['seguroGeneralCsv']) : 0;
        $profitPercentageGeneral = isset($_POST['profitPercentageCsv']) ? floatval($_POST['profitPercentageCsv']) : 0;

        $lineasDelCsv = [];
        $granTotalFOBEmbarque = 0;
        $granTotalPesoEmbarqueKg = 0;
        $parseErrors = [];
        $fileHandle = fopen($csvFile, "r");

        if ($fileHandle === FALSE) {
            sendJsonResponse(['success' => false, 'message' => 'No se pudo abrir el archivo CSV.'], 500);
        }

        $header = fgetcsv($fileHandle, 2000, ","); // Leer cabecera
        // Validar cabecera si es necesario. Columnas esperadas:
        // partida_codigo, cantidad, peso_kg_unitario, fob_usd_unitario, [descripcion_producto], [profit_percentage_linea]
        // Ejemplo de validación simple:
        if (!$header || count($header) < 4) {
             fclose($fileHandle);
             sendJsonResponse(['success' => false, 'message' => 'Formato de cabecera CSV inválido. Se esperan al menos 4 columnas: partida_codigo, cantidad, peso_kg_unitario, fob_usd_unitario.'], 400);
        }
        
        $lineNum = 1; // Para mensajes de error
        while (($row = fgetcsv($fileHandle, 2000, ",")) !== FALSE) {
            $lineNum++;
            if (count($row) < 4) { // Asegurar que hay suficientes columnas para los datos obligatorios
                $parseErrors[] = "Línea CSV {$lineNum}: Número de columnas insuficiente.";
                continue;
            }

            $partidaCodigoCsv = trim($row[0] ?? '');
            $cantidadCsv = intval($row[1] ?? 0);
            $pesoUnitarioCsv = floatval($row[2] ?? 0);
            $fobUnitarioCsv = floatval($row[3] ?? 0);
            $descripcionCsv = trim($row[4] ?? ('Ítem CSV ' . $partidaCodigoCsv));
            $profitLinea = (isset($row[5]) && is_numeric($row[5])) ? floatval($row[5]) : $profitPercentageGeneral;

            if (empty($partidaCodigoCsv) || $cantidadCsv <= 0 || $fobUnitarioCsv < 0 || $pesoUnitarioCsv < 0) {
                $parseErrors[] = "Línea CSV {$lineNum}: Datos básicos inválidos (Partida: '{$partidaCodigoCsv}', Cant: {$cantidadCsv}, FOB U: {$fobUnitarioCsv}, Peso U: {$pesoUnitarioCsv}).";
                continue;
            }
            
            $stmt_check_tariff = $pdo->prepare("SELECT id FROM tariff_codes WHERE code = :code");
            $stmt_check_tariff->execute([':code' => $partidaCodigoCsv]);
            if (!$stmt_check_tariff->fetch()) {
                 $parseErrors[] = "Línea CSV {$lineNum}: Partida Arancelaria '{$partidaCodigoCsv}' no existe en la base de datos.";
                 continue;
            }

            $fobTotalLinea = $fobUnitarioCsv * $cantidadCsv;
            $pesoTotalLinea = $pesoUnitarioCsv * $cantidadCsv;

            $lineasDelCsv[] = [
                'line_csv_num' => $lineNum, 'partida_codigo' => $partidaCodigoCsv,
                'cantidad' => $cantidadCsv, 'peso_unitario_kg' => $pesoUnitarioCsv,
                'fob_unitario_usd' => $fobUnitarioCsv, 'descripcion' => $descripcionCsv,
                'profit_percentage_linea' => $profitLinea,
                'fob_total_linea' => $fobTotalLinea, 'peso_total_linea' => $pesoTotalLinea
            ];
            $granTotalFOBEmbarque += $fobTotalLinea;
            $granTotalPesoEmbarqueKg += $pesoTotalLinea;
        }
        fclose($fileHandle);

        if (empty($lineasDelCsv) && !empty($parseErrors)) { // Si solo hubo errores de parseo y ninguna línea válida
            sendJsonResponse(['success' => false, 'message' => 'No se encontraron ítems válidos para procesar en el CSV.', 'errors_list' => $parseErrors], 400);
        }
         if (empty($lineasDelCsv) && empty($parseErrors)) { // Si el archivo estaba vacío después de la cabecera
            sendJsonResponse(['success' => false, 'message' => 'El archivo CSV no contiene datos de ítems válidos después de la cabecera.'], 400);
        }


        $isShipmentActually4x4 = ($granTotalFOBEmbarque <= 400 && $granTotalPesoEmbarqueKg <= 4.0);
        
        $processedItems = [];
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

        foreach ($lineasDelCsv as $itemData) {
            $factorProrrateoFOB = ($granTotalFOBEmbarque > 0) ? ($itemData['fob_total_linea'] / $granTotalFOBEmbarque) : (1 / count($lineasDelCsv));
            $fleteItemProrrateado = $fleteGeneralTotalEmbarque * $factorProrrateoFOB;
            $seguroItemProrrateado = $seguroGeneralTotalEmbarque * $factorProrrateoFOB;

            $stmt_tariff = $pdo->prepare("SELECT id FROM tariff_codes WHERE code = :code");
            $stmt_tariff->execute([':code' => $itemData['partida_codigo']]);
            $tariffRow = $stmt_tariff->fetch(PDO::FETCH_ASSOC);

            if (!$tariffRow) { // Debería haber sido capturado antes, pero por si acaso.
                 $calculationErrors[] = "Línea CSV {$itemData['line_csv_num']}: Partida '{$itemData['partida_codigo']}' no encontrada (inesperado).";
                 continue;
            }

            $itemCalculationResult = calculateImportationDetails(
                $pdo, $itemData['fob_unitario_usd'], $itemData['cantidad'], $itemData['peso_unitario_kg'], 
                $fleteItemProrrateado, $seguroItemProrrateado, $tariffRow['id'], 
                $isShipmentActually4x4, $itemData['profit_percentage_linea']
            );

            if ($itemCalculationResult['success']) {
                $processedItems[] = [
                    'line_csv_num' => $itemData['line_csv_num'], 'description' => $itemData['descripcion'], 
                    'partida_code_csv' => $itemData['partida_codigo'], 'calculation' => $itemCalculationResult
                ];
                foreach(['cif', 'adValorem', 'fodinfa', 'ice', 'specificTax', 'iva', 'totalImpuestos', 'costoTotalEstimadoLinea', 'pvp_total_line'] as $key) {
                    $consolidatedTotals['sum_' . strtolower($key) . '_lineas'] += $itemCalculationResult[$key === 'pvp_total_line' ? 'pvp_total_line' : ($key === 'costoTotalEstimadoLinea' ? 'costoTotalEstimadoLinea' : $key)];
                }
            } else {
                $calculationErrors[] = "Línea CSV {$itemData['line_csv_num']} ({$itemData['partida_codigo']}): " . $itemCalculationResult['message'];
            }
        }
        
        $finalErrors = array_merge($parseErrors, $calculationErrors);
        sendJsonResponse([
            'success' => empty($finalErrors), 
            'message' => empty($finalErrors) ? 'CSV procesado y calculado exitosamente.' : 'CSV procesado con algunos errores.', 
            'items_processed_details' => $processedItems, 
            'consolidated_results' => $consolidatedTotals, 
            'errors_list' => $finalErrors
        ]);

    } else { sendJsonResponse(['success' => false, 'message' => 'Error subiendo archivo CSV o archivo vacío/inválido.'], 400); }
} else { sendJsonResponse(['success' => false, 'message' => 'Método no permitido.'], 405); }
?>
