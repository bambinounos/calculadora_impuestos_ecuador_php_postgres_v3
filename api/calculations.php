*/

// --- api/calculations.php (Guardar/Cargar) --- (MODIFICADO para nuevos campos de ganancia)
/*
<?php
// api/calculations.php
// ... (require_once, session_start, requireLogin como en v2) ...
require_once '../config/db.php';
require_once '../includes/session_handler.php';
require_once '../includes/functions.php';

requireLogin();
$userId = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'save' || $action === 'update')) {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validar datos esenciales (productName, tariffCodeId, valorFOBUnitario, cantidad, y ahora los de ganancia)
    if (empty($data['productName']) || !isset($data['tariffCodeId']) || !isset($data['valorFOBUnitario']) ||
        !isset($data['cantidad']) || !isset($data['profit_percentage_applied']) || !isset($data['pvp_unit'])) {
        sendJsonResponse(['success' => false, 'message' => 'Faltan datos cruciales para guardar el cálculo.'], 400);
    }
    
    // Mapear datos del frontend a los nombres de columna de la BD
    $params = [
        'user_id' => $userId,
        'product_name' => $data['productName'],
        'tariff_code_id' => $data['tariffCodeId'],
        'valor_fob_unitario' => $data['valorFOBUnitario'],
        'cantidad' => $data['cantidad'],
        'peso_unitario_kg' => $data['pesoUnitarioKg'] ?? null,
        'costo_flete' => $data['costoFlete'] ?? null,
        'costo_seguro' => $data['costoSeguro'] ?? null,
        'es_courier_4x4' => $data['esCourier4x4'] ? 1 : 0,
        'cif' => $data['cif'],
        'ad_valorem' => $data['adValorem'],
        'fodinfa' => $data['fodinfa'],
        'ice' => $data['ice'],
        'specific_tax' => $data['specificTax'],
        'iva' => $data['iva'],
        'total_impuestos' => $data['totalImpuestos'],
        'costo_total_estimado_linea' => $data['costoTotalEstimadoLinea'], // Nombre ajustado
        // Nuevos campos de ganancia
        'profit_percentage_applied' => $data['profit_percentage_applied'],
        'cost_price_unit_after_import' => $data['cost_price_unit_after_import'],
        'profit_amount_unit' => $data['profit_amount_unit'],
        'pvp_unit' => $data['pvp_unit'],
        'pvp_total_line' => $data['pvp_total_line']
    ];

    try {
        if ($action === 'save') {
            $sql = "INSERT INTO calculations (user_id, product_name, tariff_code_id, valor_fob_unitario, cantidad, peso_unitario_kg, 
                        costo_flete, costo_seguro, es_courier_4x4, cif, ad_valorem, fodinfa, ice, specific_tax, iva, 
                        total_impuestos, costo_total_estimado_linea, profit_percentage_applied, cost_price_unit_after_import, 
                        profit_amount_unit, pvp_unit, pvp_total_line) 
                    VALUES (:user_id, :product_name, :tariff_code_id, :valor_fob_unitario, :cantidad, :peso_unitario_kg,
                        :costo_flete, :costo_seguro, :es_courier_4x4, :cif, :ad_valorem, :fodinfa, :ice, :specific_tax, :iva, 
                        :total_impuestos, :costo_total_estimado_linea, :profit_percentage_applied, :cost_price_unit_after_import,
                        :profit_amount_unit, :pvp_unit, :pvp_total_line)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $lastId = $pdo->lastInsertId();
            $message = 'Cálculo guardado.';
        } else { // action === 'update'
            if (empty($data['id'])) {
                sendJsonResponse(['success' => false, 'message' => 'ID de cálculo requerido para actualizar.'], 400);
            }
            $params['id'] = $data['id']; // Añadir ID para la cláusula WHERE
            $sql = "UPDATE calculations SET product_name = :product_name, tariff_code_id = :tariff_code_id, 
                        valor_fob_unitario = :valor_fob_unitario, cantidad = :cantidad, peso_unitario_kg = :peso_unitario_kg,
                        costo_flete = :costo_flete, costo_seguro = :costo_seguro, es_courier_4x4 = :es_courier_4x4, 
                        cif = :cif, ad_valorem = :ad_valorem, fodinfa = :fodinfa, ice = :ice, specific_tax = :specific_tax, iva = :iva, 
                        total_impuestos = :total_impuestos, costo_total_estimado_linea = :costo_total_estimado_linea, 
                        profit_percentage_applied = :profit_percentage_applied, cost_price_unit_after_import = :cost_price_unit_after_import,
                        profit_amount_unit = :profit_amount_unit, pvp_unit = :pvp_unit, pvp_total_line = :pvp_total_line,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id AND user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            if ($stmt->rowCount() === 0) {
                 sendJsonResponse(['success' => false, 'message' => 'Cálculo no encontrado o no se realizaron cambios.'], 404);
            }
            $lastId = $data['id'];
            $message = 'Cálculo actualizado.';
        }
        
        // Devolver el cálculo (nuevo o actualizado)
        $stmt_fetch = $pdo->prepare("SELECT c.*, tc.code as tariff_code_val, tc.description as tariff_description 
                                     FROM calculations c 
                                     LEFT JOIN tariff_codes tc ON c.tariff_code_id = tc.id
                                     WHERE c.id = :id AND c.user_id = :user_id");
        $stmt_fetch->execute(['id' => $lastId, 'user_id' => $userId]);
        $savedCalculation = $stmt_fetch->fetch(PDO::FETCH_ASSOC);
        sendJsonResponse(['success' => true, 'message' => $message, 'calculation' => $savedCalculation]);

    } catch (PDOException $e) {
        sendJsonResponse(['success' => false, 'message' => 'Error en base de datos: ' . $e->getMessage()], 500);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'load') {
    // ... (La consulta JOIN ya estaba bien, solo asegurar que los nuevos campos se muestren en el frontend) ...
     try {
        $stmt = $pdo->prepare("SELECT c.*, tc.code as tariff_code_val, tc.description as tariff_description 
                               FROM calculations c
                               LEFT JOIN tariff_codes tc ON c.tariff_code_id = tc.id
                               WHERE c.user_id = :user_id ORDER BY c.created_at DESC");
        $stmt->execute(['user_id' => $userId]);
        $calculations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendJsonResponse(['success' => true, 'calculations' => $calculations]);
    } catch (PDOException $e) {
        sendJsonResponse(['success' => false, 'message' => 'Error cargando: ' . $e->getMessage()], 500);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete') {
    // ... (sin cambios funcionales mayores, solo asegurar que se borra el correcto) ...
}
?>
