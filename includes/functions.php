<?php
// Este es un archivo conceptual que agrupa las diferentes partes del proyecto.
// VERSIÓN 4: Prorrateo de flete/seguro para CSV, lógica 4x4 a nivel de embarque CSV,
// ganancia e impresión de resúmenes.

// --------------------------------------------------------------------------
// --- 0. Estructura de Carpetas Sugerida (sin cambios respecto a v3) -------
// --------------------------------------------------------------------------
/*
calculadora_importacion_php/
|-- public/ (o htdocs, www)
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
|   |   `-- import_csv.php
|   `-- templates/
|       `-- print_summary_template.php (o la lógica de generación de HTML para impresión en main.js)
|-- config/
|   `-- db.php
|-- includes/
|   |-- functions.php
|   `-- session_handler.php
|-- uploads/ (asegurar permisos de escritura)
|-- .htaccess (opcional)
*/

// --------------------------------------------------------------------------
// --- 1. Backend: PHP (Modificaciones Clave) -------------------------------
// --------------------------------------------------------------------------

// --- config/db.php --- (Sin cambios)
// --- includes/session_handler.php --- (Sin cambios)

// --- includes/functions.php --- (MODIFICADO para la función de cálculo)
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
 * Recibe un flag que indica si el EMBARQUE COMPLETO califica como 4x4.
 */
function calculateImportationDetails(
    $pdo, 
    $valorFOBUnitario, 
    $cantidad, 
    $pesoUnitarioKg, 
    $costoFleteItem,  // Flete ya asignado/prorrateado para esta línea/cantidad de ítems
    $costoSeguroItem, // Seguro ya asignado/prorrateado para esta línea/cantidad de ítems
    $tariffCodeId, 
    $isShipmentConsidered4x4, // Booleano: ¿El embarque completo es 4x4?
    $profitPercentage
) {
    $stmt_tariff = $pdo->prepare("SELECT * FROM tariff_codes WHERE id = :id");
    $stmt_tariff->execute([':id' => $tariffCodeId]);
    $tariffData = $stmt_tariff->fetch(PDO::FETCH_ASSOC);

    if (!$tariffData) {
        return ['success' => false, 'message' => 'Partida arancelaria no encontrada en BD para el cálculo.'];
    }

    $valorFOBTotalLinea = $valorFOBUnitario * $cantidad;
    $pesoTotalLineaKg = $pesoUnitarioKg * $cantidad;
    
    // El CIF se calcula con el flete y seguro YA ASIGNADOS a esta línea.
    $cif = $valorFOBTotalLinea + $costoFleteItem + $costoSeguroItem;

    $adValoremRate = floatval($tariffData['advalorem_rate']);
    $iceRate = floatval($tariffData['ice_rate'] ?? 0);
    $ivaRate = floatval($tariffData['iva_rate']); // Asumir que la tabla siempre tiene un valor (0.00, 0.08, 0.15 etc.)
    $fodinfaApplies = boolval($tariffData['fodinfa_applies'] ?? true);
    $specificTaxValue = floatval($tariffData['specific_tax_value'] ?? 0);
    $specificTaxUnit = $tariffData['specific_tax_unit'] ?? '';

    $adValoremCalculado = 0; $fodinfa = 0; $iceCalculado = 0; $ivaCalculado = 0; $specificTaxCalculado = 0;

    if ($isShipmentConsidered4x4) { // Aplicar exenciones 4x4
        // AdValorem, FODINFA, ICE suelen ser 0.
        // IVA: Si la partida tiene iva_rate = 0 (ej. libros), es 0.
        // Si la partida tiene iva_rate > 0, y el envío es 4x4, usualmente no paga IVA.
        // Esta es una simplificación y debe verificarse con la normativa SENAE vigente.
        if ($ivaRate > 0) $ivaCalculado = 0; 
        // Los demás (AdV, FODINFA, ICE, Específico) son 0 para 4x4
    } else { // No es 4x4 o no califica el embarque
        $adValoremCalculado = $cif * $adValoremRate;
        if ($fodinfaApplies) $fodinfa = $cif * 0.005;
        
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
    $costoTotalEstimadoLinea = $valorFOBTotalLinea + $costoFleteItem + $costoSeguroItem + $totalImpuestos;

    $costPriceUnitAfterImport = ($cantidad > 0) ? ($costoTotalEstimadoLinea / $cantidad) : 0;
    $profitAmountUnit = $costPriceUnitAfterImport * (floatval($profitPercentage) / 100);
    $pvpUnit = $costPriceUnitAfterImport + $profitAmountUnit;
    $pvpTotalLinea = $pvpUnit * $cantidad;

    return [
        'success'                   => true,
        'calculoInput'              => [
            'valorFOBUnitario'      => $valorFOBUnitario,
            'cantidad'              => $cantidad,
            'valorFOBTotalLinea'    => round($valorFOBTotalLinea,2),
            'pesoUnitarioKg'        => $pesoUnitarioKg,
            'pesoTotalLineaKg'      => round($pesoTotalLineaKg,3),
            'costoFleteItem'        => round($costoFleteItem,2), // Flete asignado a esta línea
            'costoSeguroItem'       => round($costoSeguroItem,2), // Seguro asignado a esta línea
            'partidaArancelariaInfo'=> $tariffData,
            'isShipmentConsidered4x4'=> $isShipmentConsidered4x4,
            'profitPercentageApplied'=> $profitPercentage
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
