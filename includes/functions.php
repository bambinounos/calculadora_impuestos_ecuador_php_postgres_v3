<?php
// Este es un archivo conceptual que agrupa las diferentes partes del proyecto.
// VERSIÓN 5: Guardado permanente de CSVs subidos, registro de importaciones en BD,
// y guardado de cada línea CSV procesada en la tabla 'calculations'.

// --------------------------------------------------------------------------
// --- 0. Estructura de Carpetas Sugerida (con nuevo dir uploads fuera de public) ---
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
|   `-- api/
|       |-- auth.php
|       |-- calculate.php
|       |-- calculations.php
|       |-- tariff_codes.php
|       |-- import_csv.php
|       `-- csv_imports_history.php (NUEVO - para listar historial)
|-- uploads/  (FUERA de public, o protegido si está dentro)
|   `-- csv_files/ (Aquí se guardarán los CSVs subidos)
|-- .htaccess (en public/ si se usa para enrutar a api/ o proteger carpetas)
|-- install.php (opcional, como se vio antes)
|-- setup_database.sql (opcional, como se vio antes)
*/

// --------------------------------------------------------------------------
// --- 1. Backend: PHP (Modificaciones Clave) -------------------------------
// --------------------------------------------------------------------------

// --- config/db.php --- (Sin cambios)
// --- includes/session_handler.php --- (Sin cambios)
// --- includes/functions.php (calculateImportationDetails) --- (Sin cambios funcionales mayores respecto a v4)
/*
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
    // Descomentar la siguiente línea para probar la conexión al configurar:
    // if ($pdo) { echo "Conexión a PostgreSQL exitosa!"; }
} catch (PDOException $e) {
    // En un entorno de producción, no mostrarías este error directamente al usuario.
    // Lo ideal es registrar el error y mostrar un mensaje genérico.
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos. Por favor, contacte al administrador.']);
    // Para depuración, puedes dejar el mensaje original:
    // die("Error de conexión a la base de datos: " . $e->getMessage());
    exit;
}
?>
*/

// --- includes/session_handler.php ---
/*
<?php
// includes/session_handler.php
if (session_status() === PHP_SESSION_NONE) {
    // Configuración de la cookie de sesión para mayor seguridad
    session_set_cookie_params([
        'lifetime' => 3600, // 1 hora
        'path' => '/',
        // 'domain' => '.tu_dominio.com', // Descomentar y ajustar en producción
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // Solo enviar sobre HTTPS
        'httponly' => true, // Prevenir acceso a la cookie vía JavaScript
        'samesite' => 'Lax' // Mitiga ataques CSRF
    ]);
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        http_response_code(401); // Unauthorized
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Autenticación requerida. Por favor, inicie sesión.']);
        exit;
    }
}

// Regenerar ID de sesión después del login para prevenir fijación de sesión
function regenerateSessionAfterLogin() {
    if (isset($_SESSION['user_id'])) { // Asegurarse que el usuario ya está "logueado" en la sesión actual
        session_regenerate_id(true);
    }
}
// ==========================================================================
// === ARCHIVOS QUE NO CAMBIARON SIGNIFICATIVAMENTE DESDE LA VERSIÓN 4 ====
// ==========================================================================

// --- config/db.php ---
// (Sin cambios. Contiene la configuración de conexión a PostgreSQL con PDO.)
// (El código completo ya fue proporcionado en el artefacto v4/v5 general.)

// --- includes/session_handler.php ---
// (Sin cambios. Maneja el inicio de sesión PHP y funciones como isLoggedIn(), requireLogin().)
// (El código completo ya fue proporcionado en el artefacto v4/v5 general.)

// --- api/auth.php ---
// (Sin cambios funcionales mayores. Maneja registro, login, logout y status de sesión.)
// (El código completo ya fue proporcionado en el artefacto v4/v5 general.)

// --- api/calculate.php ---
// (Sin cambios funcionales mayores. Delega el cálculo principal a la función 
//  `calculateImportationDetails` en `includes/functions.php`.)
// (El código completo ya fue proporcionado en el artefacto v4/v5 general.)

// --- api/tariff_codes.php ---
// (Sin cambios funcionales mayores. Maneja el CRUD para las partidas arancelarias.)
// (El código completo ya fue proporcionado en el artefacto v4/v5 general.)


// ==========================================================================
// === ARCHIVOS MODIFICADOS O NUEVOS EN LA VERSIÓN 5 ========================
// ==========================================================================

// --- includes/functions.php --- (La función `calculateImportationDetails` es crucial y se incluye completa)
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
 * Recibe el flete y seguro ya asignados/prorrateados para ESTE ítem.
 * Recibe un flag que indica si el EMBARQUE COMPLETO (o el ítem individual) califica como 4x4.
 */
function calculateImportationDetails(
    $pdo, 
    $valorFOBUnitario, 
    $cantidad, 
    $pesoUnitarioKg, 
    $costoFleteItem,  // Flete ya asignado/prorrateado para esta línea/cantidad de ítems
    $costoSeguroItem, // Seguro ya asignado/prorrateado para esta línea/cantidad de ítems
    $tariffCodeId, 
    $isShipmentConsidered4x4, // Booleano: ¿El embarque/ítem es 4x4?
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
    
    $cif = $valorFOBTotalLinea + floatval($costoFleteItem) + floatval($costoSeguroItem);

    $adValoremRate = floatval($tariffData['advalorem_rate']);
    $iceRate = floatval($tariffData['ice_rate'] ?? 0);
    $ivaRate = floatval($tariffData['iva_rate']); 
    $fodinfaApplies = boolval($tariffData['fodinfa_applies'] ?? true);
    $specificTaxValue = floatval($tariffData['specific_tax_value'] ?? 0);
    $specificTaxUnit = $tariffData['specific_tax_unit'] ?? '';

    $adValoremCalculado = 0; $fodinfa = 0; $iceCalculado = 0; $ivaCalculado = 0; $specificTaxCalculado = 0;

    if ($isShipmentConsidered4x4) {
        if ($ivaRate > 0) $ivaCalculado = 0; 
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
        
        $baseIVA = $cif + $adValoremCalculado + $fodinfa + $iceCalculado + $specificTaxCalculado;
        $ivaCalculado = $baseIVA * $ivaRate;
    }

    $totalImpuestos = $adValoremCalculado + $fodinfa + $iceCalculado + $ivaCalculado + $specificTaxCalculado;
    $costoTotalEstimadoLinea = $valorFOBTotalLinea + floatval($costoFleteItem) + floatval($costoSeguroItem) + $totalImpuestos;

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
            'costoFleteItem'        => round(floatval($costoFleteItem),2),
            'costoSeguroItem'       => round(floatval($costoSeguroItem),2),
            'partidaArancelariaInfo'=> $tariffData,
            'isShipmentConsidered4x4'=> $isShipmentConsidered4x4,
            'profitPercentageApplied'=> floatval($profitPercentage)
        ],
        'cif'                       => round($cif, 2),
        'adValorem'                 => round($adValoremCalculado, 2),
        'fodinfa'                   => round($fodinfa, 2),
        'ice'                       => round($iceCalculado, 2),
        'specificTax'               => round($specificTaxCalculado, 2),
        'iva'                       => round($ivaCalculado, 2),
        'totalImpuestos'            => round($totalImpuestos, 2),
        'costoTotalEstimadoLinea'   => round($costoTotalEstimadoLinea, 2),
        'cost_price_unit_after_import' => round($costPriceUnitAfterImport, 2),
        'profit_amount_unit'        => round($profitAmountUnit, 2),
        'pvp_unit'                  => round($pvpUnit, 2),
        'pvp_total_line'            => round($pvpTotalLinea, 2)
    ];
}
?>
?>
