// Este es un archivo conceptual que agrupa el código completo de los archivos
// MODIFICADOS o NUEVOS en la Versión 9.
// Para los archivos que no cambiaron significativamente desde la v8 conceptual, se indicará.

// --------------------------------------------------------------------------
// --- 0. Estructura de Carpetas Sugerida (Recordatorio) --------------------
// --------------------------------------------------------------------------
/*
calculadora_importacion_php/
|-- config/
|   `-- db.php
|-- includes/
|   |-- functions.php
|   `-- session_handler.php
|-- public/ (o htdocs, www - Tu DocumentRoot)
|   |-- index.html
|   |-- assets/
|   |   |-- css/
|   |   |   `-- style.css
|   |   `-- js/
|   |       `-- main.js
|   |-- api/
|   |   |-- auth.php
|   |   |-- calculate.php
|   |   |-- calculations.php
|   |   |-- tariff_codes.php
|   |   |-- import_csv.php
|   |   `-- csv_imports_history.php
|-- uploads/  (FUERA de public, o protegido si está dentro)
|   `-- csv_files/
|-- .htaccess (en public/ si se usa para enrutar a api/ o proteger carpetas)
|-- install.php (opcional, como se vio antes)
|-- setup_database.sql (opcional, como se vio antes)
*/

// ==========================================================================
// === ARCHIVOS QUE NO CAMBIARON SIGNIFICATIVAMENTE DESDE LA VERSIÓN 8 ====
// ==========================================================================

// --- config/db.php ---
// (Sin cambios. Contiene la configuración de conexión a PostgreSQL con PDO.)
/*
<?php
// config/db.php
// Configuración y conexión a la base de datos PostgreSQL usando PDO.

$host = 'localhost'; // o la IP/host de tu servidor PostgreSQL
$port = '5432';      // Puerto por defecto de PostgreSQL
$dbname = 'nombre_tu_base_de_datos'; // Reemplaza con el nombre de tu base de datos
$user = 'tu_usuario_postgres'; // Reemplaza con tu usuario de PostgreSQL
$password = 'tu_contraseña_postgres'; // Reemplaza con tu contraseña

$dsn = "pgsql:host={$host};port={$port};dbname={$dbname};user={$user};password={$password}";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500); 
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos. Por favor, contacte al administrador.']);
    exit;
}
?>
*/

// --- includes/session_handler.php ---
// (Sin cambios. Maneja el inicio de sesión PHP y funciones como isLoggedIn(), requireLogin().)
/*
<?php
// includes/session_handler.php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 3600, 
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', 
        'httponly' => true, 
        'samesite' => 'Lax' 
    ]);
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        http_response_code(401); 
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Autenticación requerida. Por favor, inicie sesión.']);
        exit;
    }
}

function regenerateSessionAfterLogin() {
    if (isset($_SESSION['user_id'])) { 
        session_regenerate_id(true);
    }
}
?>
*/

// --- api/auth.php ---
// (Sin cambios funcionales mayores. Maneja registro, login, logout y status de sesión.)
/*
<?php
// api/auth.php
require_once '../config/db.php';
require_once '../includes/functions.php'; 
require_once '../includes/session_handler.php'; 

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'register') {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            sendJsonResponse(['success' => false, 'message' => 'Email y contraseña son requeridos.'], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendJsonResponse(['success' => false, 'message' => 'Formato de email inválido.'], 400);
        }
        if (strlen($password) < 6) { 
            sendJsonResponse(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.'], 400);
        }

        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                sendJsonResponse(['success' => false, 'message' => 'El email ya está registrado.'], 409);
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT); 
            $stmt = $pdo->prepare("INSERT INTO users (email, password_hash) VALUES (:email, :password_hash)");
            $stmt->execute(['email' => $email, 'password_hash' => $hashedPassword]);
            
            sendJsonResponse(['success' => true, 'message' => 'Usuario registrado exitosamente. Por favor, inicie sesión.']);

        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Error del servidor durante el registro.'], 500);
        }

    } elseif ($action === 'login') {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            sendJsonResponse(['success' => false, 'message' => 'Email y contraseña son requeridos.'], 400);
        }

        try {
            $stmt = $pdo->prepare("SELECT id, email, password_hash FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                
                regenerateSessionAfterLogin(); 

                sendJsonResponse([
                    'success' => true, 
                    'message' => 'Login exitoso.',
                    'user' => ['id' => $user['id'], 'email' => $user['email']]
                ]);
            } else {
                sendJsonResponse(['success' => false, 'message' => 'Credenciales inválidas.'], 401);
            }
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Error del servidor durante el login.'], 500);
        }
    } else {
        sendJsonResponse(['success' => false, 'message' => 'Acción POST no válida.'], 400);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'logout') {
        session_unset(); 
        session_destroy(); 
        sendJsonResponse(['success' => true, 'message' => 'Logout exitoso.']);
    } elseif ($action === 'status') {
        if (isLoggedIn()) {
            sendJsonResponse([
                'success' => true, 
                'loggedIn' => true, 
                'user' => ['id' => $_SESSION['user_id'], 'email' => $_SESSION['user_email']]
            ]);
        } else {
            sendJsonResponse(['success' => true, 'loggedIn' => false]);
        }
    } else {
        sendJsonResponse(['success' => false, 'message' => 'Acción GET no válida.'], 400);
    }
} else {
    sendJsonResponse(['success' => false, 'message' => 'Método HTTP no soportado.'], 405);
}
?>
*/

// --- api/tariff_codes.php ---
// (Sin cambios funcionales mayores. Maneja el CRUD para las partidas arancelarias.)
// (El código completo ya fue proporcionado en artefactos anteriores, incluyendo validaciones y manejo de errores.)
/*
<?php
// api/tariff_codes.php
require_once '../config/db.php';
require_once '../includes/session_handler.php'; 
require_once '../includes/functions.php';

// requireLogin(); // Descomentar si se requiere login para todas las acciones
// O implementar lógica de roles para acciones de creación/edición/borrado.

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
    foreach(['advalorem_rate', 'ice_rate', 'iva_rate', 'specific_tax_value'] as $rate_key) {
        if (isset($data[$rate_key]) && $data[$rate_key] !== null && !is_numeric($data[$rate_key])) {
            sendJsonResponse(['success' => false, 'message' => "La tasa '$rate_key' debe ser numérica o nula."], 400);
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
            ':ice_rate' => isset($data['ice_rate']) && $data['ice_rate'] !== '' ? floatval($data['ice_rate']) : null,
            ':fodinfa_applies' => isset($data['fodinfa_applies']) ? boolval($data['fodinfa_applies']) : true,
            ':iva_rate' => floatval($data['iva_rate']),
            ':specific_tax_value' => isset($data['specific_tax_value']) && $data['specific_tax_value'] !== '' ? floatval($data['specific_tax_value']) : null,
            ':specific_tax_unit' => isset($data['specific_tax_unit']) ? trim($data['specific_tax_unit']) : null,
            ':notes' => isset($data['notes']) ? trim($data['notes']) : null
        ]);
        $lastId = $pdo->lastInsertId();
        sendJsonResponse(['success' => true, 'message' => 'Partida arancelaria creada exitosamente.', 'id' => $lastId]);

    } catch (PDOException $e) {
        if ($e->getCode() == '23505') { 
            sendJsonResponse(['success' => false, 'message' => 'Error: El código de partida arancelaria ya existe.'], 409);
        } else {
            sendJsonResponse(['success' => false, 'message' => 'Error del servidor al crear partida: ' . $e->getMessage()], 500);
        }
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'read') {
    $searchTerm = trim($_GET['term'] ?? '');
    try {
        if (!empty($searchTerm)) {
            $stmt = $pdo->prepare("SELECT id, code, description FROM tariff_codes WHERE code ILIKE :term OR description ILIKE :term ORDER BY code LIMIT 20");
            $stmt->execute([':term' => "%{$searchTerm}%"]);
        } else {
            $stmt = $pdo->query("SELECT id, code, description FROM tariff_codes ORDER BY code LIMIT 50");
        }
        $codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendJsonResponse(['success' => true, 'tariff_codes' => $codes]);
    } catch (PDOException $e) {
        sendJsonResponse(['success' => false, 'message' => 'Error leyendo partidas arancelarias: ' . $e->getMessage()], 500);
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
            sendJsonResponse(['success' => true, 'tariff_code' => $tariff_code]);
        } else {
            sendJsonResponse(['success' => false, 'message' => 'Partida arancelaria no encontrada.'], 404);
        }
    } catch (PDOException $e) {
        sendJsonResponse(['success' => false, 'message' => 'Error obteniendo partida: ' . $e->getMessage()], 500);
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
            code = :code, description = :description, advalorem_rate = :advalorem_rate, 
            ice_rate = :ice_rate, fodinfa_applies = :fodinfa_applies, iva_rate = :iva_rate, 
            specific_tax_value = :specific_tax_value, specific_tax_unit = :specific_tax_unit, 
            notes = :notes, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id");

        $stmt->execute([
            ':id' => intval($tariff_id),
            ':code' => trim($data['code']),
            ':description' => trim($data['description']),
            ':advalorem_rate' => floatval($data['advalorem_rate']),
            ':ice_rate' => isset($data['ice_rate']) && $data['ice_rate'] !== '' ? floatval($data['ice_rate']) : null,
            ':fodinfa_applies' => isset($data['fodinfa_applies']) ? boolval($data['fodinfa_applies']) : true,
            ':iva_rate' => floatval($data['iva_rate']),
            ':specific_tax_value' => isset($data['specific_tax_value']) && $data['specific_tax_value'] !== '' ? floatval($data['specific_tax_value']) : null,
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
            sendJsonResponse(['success' => false, 'message' => 'Error del servidor al actualizar partida: ' . $e->getMessage()], 500);
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
        sendJsonResponse(['success' => false, 'message' => 'Error del servidor al eliminar partida: ' . $e->getMessage()], 500);
    }
} else {
    sendJsonResponse(['success' => false, 'message' => 'Acción no válida o método no soportado para gestión de partidas arancelarias.'], 405);
}
?>
*/

// --- api/csv_imports_history.php ---
/*
<?php
// api/csv_imports_history.php
require_once '../config/db.php';
require_once '../includes/session_handler.php';
require_once '../includes/functions.php';

requireLogin();
$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT id, original_filename, upload_timestamp, processing_status, total_lines, processed_lines, error_count, proration_method_used 
                           FROM csv_imports 
                           WHERE user_id = :user_id 
                           ORDER BY upload_timestamp DESC LIMIT 50"); 
    $stmt->execute([':user_id' => $userId]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    sendJsonResponse(['success' => true, 'history' => $history]);
} catch (PDOException $e) {
    sendJsonResponse(['success' => false, 'message' => 'Error cargando historial de importaciones CSV: ' . $e->getMessage()], 500);
}
?>
*/

// ==========================================================================
// === ARCHIVOS MODIFICADOS COMPLETOS PARA LA VERSIÓN 9 =====================
// ==========================================================================

// --- includes/functions.php --- (MODIFICADO `calculateImportationDetails` para la nueva base del IVA)
/*
<?php
// includes/functions.php
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Calcula los detalles de importación para un ítem.
 * NUEVA LÓGICA V9: ISD y Agente Aduana NO forman parte de la base del IVA.
 * Se suman al costo total después de todos los impuestos.
 */
function calculateImportationDetails(
    $pdo, 
    $valorFOBUnitario, 
    $cantidad, 
    $pesoUnitarioKg, 
    $costoFleteInternacionalItem,
    $costoSeguroInternacionalItem,
    $costoAgenteAduanaItem,      // Gasto de Agente de Aduana prorrateado para esta línea
    $tasaISDAplicableAlFOB,      // Tasa ISD (ej. 0.05 para 5%) a aplicar sobre el FOB de esta línea
    $otrosGastosPostNacionalizacionItem, // Suma de bodega, demoraje, flete terrestre, varios YA PRORRATEADOS
    $tariffCodeId, 
    $isShipmentConsidered4x4, 
    $profitPercentage
) {
    $stmt_tariff = $pdo->prepare("SELECT * FROM tariff_codes WHERE id = :id");
    $stmt_tariff->execute([':id' => $tariffCodeId]);
    $tariffData = $stmt_tariff->fetch(PDO::FETCH_ASSOC);

    if (!$tariffData) {
        return ['success' => false, 'message' => 'Partida arancelaria (' . htmlspecialchars($tariffCodeId) . ') no encontrada en BD.'];
    }

    $valorFOBTotalLinea = floatval($valorFOBUnitario) * intval($cantidad);
    $pesoTotalLineaKg = floatval($pesoUnitarioKg) * intval($cantidad);
    
    // 1. Calcular Valor CIF por Ítem
    $cif = $valorFOBTotalLinea + floatval($costoFleteInternacionalItem) + floatval($costoSeguroInternacionalItem);

    // 2. Calcular ISD por Ítem (sobre el FOB de esta línea) - Este es un COSTO, no va a base IVA según nueva regla
    $isdPagadoItem = $valorFOBTotalLinea * floatval($tasaISDAplicableAlFOB); // $tasaISDAplicableAlFOB debe ser decimal (ej. 0.05)

    // Obtener tasas de la partida
    $adValoremRate = floatval($tariffData['advalorem_rate']);
    $iceRate = floatval($tariffData['ice_rate'] ?? 0);
    $ivaRate = floatval($tariffData['iva_rate']); 
    $fodinfaApplies = boolval($tariffData['fodinfa_applies'] ?? true);
    $specificTaxValue = floatval($tariffData['specific_tax_value'] ?? 0);
    $specificTaxUnit = $tariffData['specific_tax_unit'] ?? '';

    $adValoremCalculado = 0; $fodinfa = 0; $iceCalculado = 0; $ivaCalculado = 0; $specificTaxCalculado = 0;

    // 3. Calcular Base Imponible para IVA por Ítem (AJUSTADA V9)
    // Base IVA = CIF + AdValorem + FODINFA + ICE + Imp. Específicos
    // (ISD y Agente Aduana NO se incluyen aquí según la nueva especificación)
    $baseImponibleIVA = $cif; 

    if ($isShipmentConsidered4x4) {
        // Para 4x4, AdValorem, FODINFA, ICE, Específicos son 0.
        if ($ivaRate > 0) $ivaCalculado = 0; // Asumimos 4x4 anula IVA si la partida no es 0% por defecto
    } else { 
        $adValoremCalculado = $cif * $adValoremRate;
        if ($fodinfaApplies) $fodinfa = $cif * 0.005; // 0.5%
        
        $baseICE = $cif + $adValoremCalculado + $fodinfa; 
        $iceCalculado = $baseICE * $iceRate;
        
        if ($specificTaxValue > 0 && !empty($specificTaxUnit)) {
            if (stripos($specificTaxUnit, 'kg') !== false) {
                $specificTaxCalculado = $pesoTotalLineaKg * $specificTaxValue;
            } elseif (stripos($specificTaxUnit, 'unidad') !== false) {
                $specificTaxCalculado = $cantidad * $specificTaxValue;
            }
        }
        
        // Sumar a la base del IVA los tributos calculados (SIN ISD NI AGENTE ADUANA)
        $baseImponibleIVA += $adValoremCalculado + $fodinfa + $iceCalculado + $specificTaxCalculado;
        // 4. Calcular IVA por Ítem
        $ivaCalculado = $baseImponibleIVA * $ivaRate;
    }

    // 5. Calcular Total de Impuestos de Importación por Ítem (AdV, FODINFA, ICE, IVA, Específicos)
    $totalImpuestos = $adValoremCalculado + $fodinfa + $iceCalculado + $ivaCalculado + $specificTaxCalculado;
    
    // 6. Calcular Costo Total del Ítem
    $costoTotalEstimadoLinea = $cif + // Incluye FOB, Flete Int, Seguro Int
                               $totalImpuestos + // Suma de AdV, FODINFA, ICE, IVA, Específicos
                               floatval($isdPagadoItem) + // ISD se suma aquí al costo
                               floatval($costoAgenteAduanaItem) + // Agente Aduana se suma aquí al costo
                               floatval($otrosGastosPostNacionalizacionItem); // Bodega, demoraje, etc.

    // 7. Aplicar Ganancia y Calcular PVP por Ítem
    $costPriceUnitAfterImport = ($cantidad > 0) ? ($costoTotalEstimadoLinea / $cantidad) : 0;
    $profitAmountUnit = $costPriceUnitAfterImport * (floatval($profitPercentage) / 100);
    $pvpUnit = $costPriceUnitAfterImport + $profitAmountUnit;
    $pvpTotalLinea = $pvpUnit * $cantidad;

    return [
        'success'                   => true,
        'calculoInput'              => [
            'valorFOBUnitario'      => floatval($valorFOBUnitario),
            'cantidad'              => intval($cantidad),
            'valorFOBTotalLinea'    => round($valorFOBTotalLinea,2),
            'pesoUnitarioKg'        => floatval($pesoUnitarioKg),
            'pesoTotalLineaKg'      => round($pesoTotalLineaKg,3),
            'costoFleteInternacionalItem' => round(floatval($costoFleteInternacionalItem),2),
            'costoSeguroInternacionalItem'=> round(floatval($costoSeguroInternacionalItem),2),
            'costoAgenteAduanaItem' => round(floatval($costoAgenteAduanaItem),2),
            'tasaISDAplicableAlFOB' => floatval($tasaISDAplicableAlFOB) * 100, // Devolver como % para mostrar
            'isdPagadoItem'         => round(floatval($isdPagadoItem),2),
            'otrosGastosPostNacionalizacionItem' => round(floatval($otrosGastosPostNacionalizacionItem),2),
            'partidaArancelariaInfo'=> $tariffData,
            'isShipmentConsidered4x4'=> $isShipmentConsidered4x4,
            'profitPercentageApplied'=> floatval($profitPercentage)
        ],
        'cif'                       => round($cif, 2),
        'baseImponibleIVA'          => round($baseImponibleIVA, 2), // Para transparencia
        'adValorem'                 => round($adValoremCalculado, 2),
        'fodinfa'                   => round($fodinfa, 2),
        'ice'                       => round($iceCalculado, 2),
        'specificTax'               => round($specificTaxCalculado, 2),
        'iva'                       => round($ivaCalculado, 2),
        'totalImpuestos'            => round($totalImpuestos, 2), // Suma de AdV,Fodinfa,ICE,IVA,Espec
        'costoTotalEstimadoLinea'   => round($costoTotalEstimadoLinea, 2),
        'cost_price_unit_after_import' => round($costPriceUnitAfterImport, 2),
        'profit_amount_unit'        => round($profitAmountUnit, 2),
        'pvp_unit'                  => round($pvpUnit, 2),
        'pvp_total_line'            => round($pvpTotalLinea, 2)
    ];
}
?>
