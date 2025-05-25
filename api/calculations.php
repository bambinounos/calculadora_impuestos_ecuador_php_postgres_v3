*/

// --- api/calculations.php --- (MODIFICADO para asegurar que los nombres de los campos guardados coincidan con los devueltos por `calculateImportationDetails`)
/*
<?php
// api/calculations.php
require_once '../config/db.php';
require_once '../includes/session_handler.php';
require_once '../includes/functions.php';

requireLogin();
$userId = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'save' || $action === 'update')) {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validación de datos esenciales
    if (empty($data['productName']) || !isset($data['tariffCodeId']) || !is_numeric($data['tariffCodeId']) || 
        !isset($data['valorFOBUnitario']) || !is_numeric($data['valorFOBUnitario']) ||
        !isset($data['cantidad']) || !is_numeric($data['cantidad']) || intval($data['cantidad']) <= 0 ||
        !isset($data['profit_percentage_applied']) || !is_numeric($data['profit_percentage_applied']) ||
        !isset($data['pvp_unit']) || !is_numeric($data['pvp_unit'])) {
        sendJsonResponse(['success' => false, 'message' => 'Faltan datos cruciales o son inválidos para guardar el cálculo.'], 400);
    }
    
    $params = [
        'user_id' => $userId,
        'product_name' => trim($data['productName']),
        'tariff_code_id' => intval($data['tariffCodeId']),
        'valor_fob_unitario' => floatval($data['valorFOBUnitario']),
        'cantidad' => intval($data['cantidad']),
        'peso_unitario_kg' => isset($data['pesoUnitarioKg']) && is_numeric($data['pesoUnitarioKg']) ? floatval($data['pesoUnitarioKg']) : null,
        'costo_flete' => isset($data['costoFleteInternacionalItem']) && is_numeric($data['costoFleteInternacionalItem']) ? floatval($data['costoFleteInternacionalItem']) : null,
        'costo_seguro' => isset($data['costoSeguroInternacionalItem']) && is_numeric($data['costoSeguroInternacionalItem']) ? floatval($data['costoSeguroInternacionalItem']) : null,
        'agente_aduana_prorrateado_item' => isset($data['costoAgenteAduanaItem']) && is_numeric($data['costoAgenteAduanaItem']) ? floatval($data['costoAgenteAduanaItem']) : null,
        'isd_pagado_item' => isset($data['isdPagadoItem']) && is_numeric($data['isdPagadoItem']) ? floatval($data['isdPagadoItem']) : null,
        'otros_gastos_prorrateados_item' => isset($data['otrosGastosPostNacionalizacionItem']) && is_numeric($data['otrosGastosPostNacionalizacionItem']) ? floatval($data['otrosGastosPostNacionalizacionItem']) : null,
        'es_courier_4x4' => isset($data['isShipmentConsidered4x4']) ? boolval($data['isShipmentConsidered4x4']) : (isset($data['esCourier4x4']) ? boolval($data['esCourier4x4']) : false),
        
        'cif' => floatval($data['cif']),
        'ad_valorem' => floatval($data['adValorem']),
        'fodinfa' => floatval($data['fodinfa']),
        'ice' => floatval($data['ice']),
        'specific_tax' => floatval($data['specificTax']),
        'iva' => floatval($data['iva']),
        'total_impuestos' => floatval($data['totalImpuestos']),
        'costo_total_estimado_linea' => floatval($data['costoTotalEstimadoLinea']),
        'profit_percentage_applied' => floatval($data['profit_percentage_applied']),
        'cost_price_unit_after_import' => floatval($data['cost_price_unit_after_import']),
        'profit_amount_unit' => floatval($data['profit_amount_unit']),
        'pvp_unit' => floatval($data['pvp_unit']),
        'pvp_total_line' => floatval($data['pvp_total_line']),
        'csv_import_id' => isset($data['csv_import_id']) && is_numeric($data['csv_import_id']) ? intval($data['csv_import_id']) : null,
        'csv_import_line_number' => isset($data['csv_import_line_number']) && is_numeric($data['csv_import_line_number']) ? intval($data['csv_import_line_number']) : null
    ];

    try {
        if ($action === 'save') {
            $sql = "INSERT INTO calculations (user_id, product_name, tariff_code_id, valor_fob_unitario, cantidad, peso_unitario_kg, 
                        costo_flete, costo_seguro, agente_aduana_prorrateado_item, isd_pagado_item, otros_gastos_prorrateados_item, 
                        es_courier_4x4, cif, ad_valorem, fodinfa, ice, specific_tax, iva, 
                        total_impuestos, costo_total_estimado_linea, profit_percentage_applied, cost_price_unit_after_import, 
                        profit_amount_unit, pvp_unit, pvp_total_line, csv_import_id, csv_import_line_number) 
                    VALUES (:user_id, :product_name, :tariff_code_id, :valor_fob_unitario, :cantidad, :peso_unitario_kg,
                        :costo_flete, :costo_seguro, :agente_aduana_prorrateado_item, :isd_pagado_item, :otros_gastos_prorrateados_item, 
                        :es_courier_4x4, :cif, :ad_valorem, :fodinfa, :ice, :specific_tax, :iva, 
                        :total_impuestos, :costo_total_estimado_linea, :profit_percentage_applied, :cost_price_unit_after_import,
                        :profit_amount_unit, :pvp_unit, :pvp_total_line, :csv_import_id, :csv_import_line_number)";
        } else { // action === 'update'
            if (empty($data['id']) || !is_numeric($data['id'])) {
                sendJsonResponse(['success' => false, 'message' => 'ID de cálculo inválido para actualizar.'], 400);
            }
            $params['id'] = intval($data['id']);
            $sql = "UPDATE calculations SET product_name = :product_name, tariff_code_id = :tariff_code_id, 
                        valor_fob_unitario = :valor_fob_unitario, cantidad = :cantidad, peso_unitario_kg = :peso_unitario_kg,
                        costo_flete = :costo_flete, costo_seguro = :costo_seguro, 
                        agente_aduana_prorrateado_item = :agente_aduana_prorrateado_item, 
                        isd_pagado_item = :isd_pagado_item, 
                        otros_gastos_prorrateados_item = :otros_gastos_prorrateados_item, 
                        es_courier_4x4 = :es_courier_4x4, cif = :cif, ad_valorem = :ad_valorem, fodinfa = :fodinfa, ice = :ice, 
                        specific_tax = :specific_tax, iva = :iva, total_impuestos = :total_impuestos, 
                        costo_total_estimado_linea = :costo_total_estimado_linea, 
                        profit_percentage_applied = :profit_percentage_applied, 
                        cost_price_unit_after_import = :cost_price_unit_after_import,
                        profit_amount_unit = :profit_amount_unit, pvp_unit = :pvp_unit, pvp_total_line = :pvp_total_line,
                        csv_import_id = :csv_import_id, csv_import_line_number = :csv_import_line_number,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id AND user_id = :user_id";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        if ($action === 'update' && $stmt->rowCount() === 0) {
            sendJsonResponse(['success' => false, 'message' => 'Cálculo no encontrado para este usuario o no se realizaron cambios.'], 404);
        }

        $lastId = ($action === 'save') ? $pdo->lastInsertId() : $params['id'];
        $message = ($action === 'save') ? 'Cálculo guardado exitosamente.' : 'Cálculo actualizado exitosamente.';
        
        $stmt_fetch = $pdo->prepare("SELECT c.*, tc.code as tariff_code_val, tc.description as tariff_description 
                                     FROM calculations c 
                                     LEFT JOIN tariff_codes tc ON c.tariff_code_id = tc.id
                                     WHERE c.id = :id AND c.user_id = :user_id");
        $stmt_fetch->execute(['id' => $lastId, 'user_id' => $userId]);
        $savedCalculation = $stmt_fetch->fetch(PDO::FETCH_ASSOC);
        sendJsonResponse(['success' => true, 'message' => $message, 'calculation' => $savedCalculation]);

    } catch (PDOException $e) { 
        sendJsonResponse(['success' => false, 'message' => 'Error en la operación de base de datos: ' . $e->getMessage()], 500); 
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'load') {
     try {
        $csvImportIdFilter = isset($_GET['csv_import_id']) && is_numeric($_GET['csv_import_id']) ? intval($_GET['csv_import_id']) : null;
        
        $sql = "SELECT c.*, tc.code as tariff_code_val, tc.description as tariff_description 
                FROM calculations c
                LEFT JOIN tariff_codes tc ON c.tariff_code_id = tc.id
                WHERE c.user_id = :user_id ";
        $queryParams = ['user_id' => $userId];

        if ($csvImportIdFilter !== null) {
            $sql .= " AND c.csv_import_id = :csv_import_id ";
            $queryParams['csv_import_id'] = $csvImportIdFilter;
        }
        $sql .= " ORDER BY c.csv_import_line_number ASC, c.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($queryParams);
        $calculations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendJsonResponse(['success' => true, 'calculations' => $calculations]);
    } catch (PDOException $e) {
        sendJsonResponse(['success' => false, 'message' => 'Error cargando cálculos: ' . $e->getMessage()], 500);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete') {
    $data = json_decode(file_get_contents('php://input'), true);
    $calculationId = $data['id'] ?? null;

    if (!$calculationId || !is_numeric($calculationId)) {
        sendJsonResponse(['success' => false, 'message' => 'ID de cálculo inválido para eliminar.'], 400);
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM calculations WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => intval($calculationId), 'user_id' => $userId]);

        if ($stmt->rowCount() > 0) {
            sendJsonResponse(['success' => true, 'message' => 'Cálculo eliminado exitosamente.']);
        } else {
            sendJsonResponse(['success' => false, 'message' => 'Cálculo no encontrado o no autorizado para eliminar.'], 404);
        }
    } catch (PDOException $e) {
        sendJsonResponse(['success' => false, 'message' => 'Error eliminando el cálculo: ' . $e->getMessage()], 500);
    }
} else {
    sendJsonResponse(['success' => false, 'message' => 'Acción no válida o método no soportado para cálculos.'], 405);
}
?>
