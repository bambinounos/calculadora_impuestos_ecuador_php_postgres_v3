*/

// --- api/tariff_codes.php ---
/*
<?php
// api/tariff_codes.php
require_once '../config/db.php';
require_once '../includes/session_handler.php'; 
require_once '../includes/functions.php';

// Para CRUD de partidas, podría requerirse un rol de administrador.
// Por ahora, si se requiere login, cualquier usuario logueado podría (descomentar si es necesario).
// requireLogin(); 

$action = $_REQUEST['action'] ?? ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    // requireLogin(); // O un rol específico
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['code']) || empty($data['description']) || !isset($data['advalorem_rate']) || !isset($data['iva_rate'])) {
        sendJsonResponse(['success' => false, 'message' => 'Código, descripción, tasa AdValorem y tasa IVA son requeridos.'], 400);
    }
    if (strlen($data['code']) > 50) {
         sendJsonResponse(['success' => false, 'message' => 'El código de partida no debe exceder los 50 caracteres.'], 400);
    }
    // Validar que las tasas sean numéricas y estén en un rango esperado (ej. 0 a 1 para porcentajes)
    foreach(['advalorem_rate', 'ice_rate', 'iva_rate', 'specific_tax_value'] as $rate_key) {
        if (isset($data[$rate_key]) && !is_numeric($data[$rate_key])) {
            sendJsonResponse(['success' => false, 'message' => "La tasa '$rate_key' debe ser numérica."], 400);
        }
    }


    try {
        $stmt = $pdo->prepare("INSERT INTO tariff_codes 
            (code, description, advalorem_rate, ice_rate, fodinfa_applies, iva_rate, specific_tax_value, specific_tax_unit, notes) 
            VALUES (:code, :description, :advalorem_rate, :ice_rate, :fodinfa_applies, :iva_rate, :specific_tax_value, :specific_tax_unit, :notes)");
        
        $stmt->execute([
            ':code' => trim($data['code']),
            ':description' => trim($data['description']),
            ':advalorem_rate' => floatval($data['advalorem_rate']),
            ':ice_rate' => isset($data['ice_rate']) ? floatval($data['ice_rate']) : null,
            ':fodinfa_applies' => isset($data['fodinfa_applies']) ? boolval($data['fodinfa_applies']) : true,
            ':iva_rate' => floatval($data['iva_rate']),
            ':specific_tax_value' => isset($data['specific_tax_value']) ? floatval($data['specific_tax_value']) : null,
            ':specific_tax_unit' => isset($data['specific_tax_unit']) ? trim($data['specific_tax_unit']) : null,
            ':notes' => isset($data['notes']) ? trim($data['notes']) : null
        ]);
        $lastId = $pdo->lastInsertId();
        sendJsonResponse(['success' => true, 'message' => 'Partida arancelaria creada exitosamente.', 'id' => $lastId]);

    } catch (PDOException $e) {
        if ($e->getCode() == '23505') { 
            sendJsonResponse(['success' => false, 'message' => 'Error: El código de partida arancelaria ya existe.'], 409);
        } else {
            // Log $e->getMessage()
            sendJsonResponse(['success' => false, 'message' => 'Error del servidor al crear partida.'], 500);
        }
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'read') {
    $searchTerm = trim($_GET['term'] ?? '');
    try {
        if (!empty($searchTerm)) {
            $stmt = $pdo->prepare("SELECT id, code, description FROM tariff_codes WHERE code ILIKE :term OR description ILIKE :term ORDER BY code LIMIT 20");
            $stmt->execute([':term' => "%{$searchTerm}%"]);
        } else {
            $stmt = $pdo->prepare("SELECT id, code, description FROM tariff_codes ORDER BY code LIMIT 50");
            $stmt->execute();
        }
        $codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Basic output encoding to prevent XSS. A library or helper function would be better for a real app.
        foreach ($codes as &$code) {
            $code['code'] = htmlspecialchars($code['code'], ENT_QUOTES, 'UTF-8');
            $code['description'] = htmlspecialchars($code['description'], ENT_QUOTES, 'UTF-8');
        }
        sendJsonResponse(['success' => true, 'tariff_codes' => $codes]);
    } catch (PDOException $e) {
        // Log $e->getMessage()
        sendJsonResponse(['success' => false, 'message' => 'Error leyendo partidas arancelarias.'], 500);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_one') {
    $tariff_id = $_GET['id'] ?? null;
    if (!$tariff_id || !is_numeric($tariff_id)) {
        sendJsonResponse(['success' => false, 'message' => 'ID de partida no válido o no proporcionado.'], 400);
    }
    try {
        $stmt = $pdo->prepare("SELECT * FROM tariff_codes WHERE id = :id");
        $stmt->execute([':id' => intval($tariff_id)]);
        $tariff_code = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($tariff_code) {
            // Basic output encoding
            foreach ($tariff_code as $key => $value) {
                if (is_string($value)) {
                    $tariff_code[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }
            }
            sendJsonResponse(['success' => true, 'tariff_code' => $tariff_code]);
        } else {
            sendJsonResponse(['success' => false, 'message' => 'Partida arancelaria no encontrada.'], 404);
        }
    } catch (PDOException $e) {
        // Log $e->getMessage()
        sendJsonResponse(['success' => false, 'message' => 'Error obteniendo partida.'], 500);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    // requireLogin(); // O permisos de admin
    $data = json_decode(file_get_contents('php://input'), true);
    $tariff_id = $data['id'] ?? null;

    if (!$tariff_id || !is_numeric($tariff_id)) {
        sendJsonResponse(['success' => false, 'message' => 'ID de partida no válido para actualizar.'], 400);
    }
    if (empty($data['code']) || empty($data['description']) || !isset($data['advalorem_rate']) || !isset($data['iva_rate'])) {
        sendJsonResponse(['success' => false, 'message' => 'Datos incompletos para actualizar partida.'], 400);
    }
    // Más validaciones como en 'create'

    try {
        $stmt = $pdo->prepare("UPDATE tariff_codes SET 
            code = :code, 
            description = :description, 
            advalorem_rate = :advalorem_rate, 
            ice_rate = :ice_rate, 
            fodinfa_applies = :fodinfa_applies, 
            iva_rate = :iva_rate, 
            specific_tax_value = :specific_tax_value, 
            specific_tax_unit = :specific_tax_unit, 
            notes = :notes,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = :id");

        $stmt->execute([
            ':id' => intval($tariff_id),
            ':code' => trim($data['code']),
            ':description' => trim($data['description']),
            ':advalorem_rate' => floatval($data['advalorem_rate']),
            ':ice_rate' => isset($data['ice_rate']) ? floatval($data['ice_rate']) : null,
            ':fodinfa_applies' => isset($data['fodinfa_applies']) ? boolval($data['fodinfa_applies']) : true,
            ':iva_rate' => floatval($data['iva_rate']),
            ':specific_tax_value' => isset($data['specific_tax_value']) ? floatval($data['specific_tax_value']) : null,
            ':specific_tax_unit' => isset($data['specific_tax_unit']) ? trim($data['specific_tax_unit']) : null,
            ':notes' => isset($data['notes']) ? trim($data['notes']) : null
        ]);

        if ($stmt->rowCount() > 0) {
            sendJsonResponse(['success' => true, 'message' => 'Partida arancelaria actualizada exitosamente.']);
        } else {
            sendJsonResponse(['success' => false, 'message' => 'Partida no encontrada o no se realizaron cambios.'], 404);
        }
    } catch (PDOException $e) {
         if ($e->getCode() == '23505') { 
            sendJsonResponse(['success' => false, 'message' => 'Error: El nuevo código de partida arancelaria ya existe para otro registro.'], 409);
        } else {
            // Log $e->getMessage()
            sendJsonResponse(['success' => false, 'message' => 'Error del servidor al actualizar partida.'], 500);
        }
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete') {
    // requireLogin(); // O permisos de admin
    $data = json_decode(file_get_contents('php://input'), true);
    $tariff_id = $data['id'] ?? null;

    if (!$tariff_id || !is_numeric($tariff_id)) {
        sendJsonResponse(['success' => false, 'message' => 'ID de partida no válido para eliminar.'], 400);
    }

    try {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM calculations WHERE tariff_code_id = :id");
        $stmt_check->execute([':id' => intval($tariff_id)]);
        if ($stmt_check->fetchColumn() > 0) {
            sendJsonResponse(['success' => false, 'message' => 'No se puede eliminar: La partida está asignada a cálculos guardados. Considere desactivarla o reasignar los cálculos.'], 409);
        }

        $stmt = $pdo->prepare("DELETE FROM tariff_codes WHERE id = :id");
        $stmt->execute([':id' => intval($tariff_id)]);

        if ($stmt->rowCount() > 0) {
            sendJsonResponse(['success' => true, 'message' => 'Partida arancelaria eliminada exitosamente.']);
        } else {
            sendJsonResponse(['success' => false, 'message' => 'Partida arancelaria no encontrada.'], 404);
        }
    } catch (PDOException $e) {
        // Log $e->getMessage()
        sendJsonResponse(['success' => false, 'message' => 'Error del servidor al eliminar partida.'], 500);
    }
} else {
    sendJsonResponse(['success' => false, 'message' => 'Acción no válida o método no soportado para gestión de partidas arancelarias.'], 405);
}
?>
