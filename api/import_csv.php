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
        $tasaIsdConfigurableEmbarquePorcentaje = isset($_POST['tasaIsdConfigurableCsv']) ? floatval($_POST['tasaIsdConfigurableCsv']) : 0; // Tasa ISD en %, ej 5
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
                // ... (Validaciones y parseo de cada línea como en v8, guardar en $lineasDelCsv) ...
            }
            fclose($handle);
        } else { /* ... error abriendo CSV ... */ }
        
        $isdTotalCalculadoEmbarque = $granTotalFOBEmbarque * ($tasaIsdConfigurableEmbarquePorcentaje / 100);

        // Crear registro inicial en csv_imports
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

        if (empty($lineasDelCsv) /* ... */) { /* ... manejo de errores de parseo o CSV vacío como en v8 ... */ }

        $isShipmentActually4x4 = ($granTotalFOBEmbarque <= 400 && $granTotalPesoEmbarqueKg <= 4.0);
        
        $processedItemsDetailsForResponse = []; $calculationErrors = [];
        $consolidatedTotals = [ /* ... como en v8, inicializar todos los campos ... */ ];
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
            // La TASA ISD es la misma para todos los ítems del embarque, el MONTO ISD se calcula por línea.
            $otrosGastosPostNacionalizacionItemProrrateado = $totalOtrosGastosPostNacionalizacion * $factorProrrateo;

            $stmt_tariff = $pdo->prepare("SELECT id FROM tariff_codes WHERE code = :code");
            $stmt_tariff->execute([':code' => $itemData['partida_codigo']]);
            $tariffRow = $stmt_tariff->fetch(PDO::FETCH_ASSOC);

            $itemCalculationResult = calculateImportationDetails(
                $pdo, $itemData['fob_unitario_usd'], $itemData['cantidad'], $itemData['peso_unitario_kg'], 
                $fleteInternacionalItemProrrateado, $seguroInternacionalItemProrrateado, 
                $agenteAduanaItemProrrateado, 
                ($tasaIsdConfigurableEmbarquePorcentaje / 100), // Pasar la TASA ISD (decimal)
                $otrosGastosPostNacionalizacionItemProrrateado,
                $tariffRow['id'], $isShipmentActually4x4, $itemData['profit_percentage_linea']
            );

            if ($itemCalculationResult['success']) {
                $processedLinesCount++;
                // GUARDAR este ítem en la tabla 'calculations'
                // ... (como en v8, asegurando que se guardan los campos correctos, incluyendo isd_pagado_item) ...
                $calcInput = $itemCalculationResult['calculoInput'];
                $params_save_item = [ /* ... mapear todos los campos ... */ ];
                // ... (ejecutar INSERT) ...

                $processedItemsDetailsForResponse[] = [ /* ... */ ];
                // ... (Sumar a $consolidatedTotals) ...
            } else { /* ... manejo de error de cálculo por línea ... */ }
        }
        
        // Actualizar el registro en csv_imports con el estado final y resumen
        // ... (como en v8) ...
        
        sendJsonResponse([ /* ... como en v8 ... */ ]);

    } else { /* ... error de subida ... */ }
} else { /* ... método no permitido ... */ }
?>
