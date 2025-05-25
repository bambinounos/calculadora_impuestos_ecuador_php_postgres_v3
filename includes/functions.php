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
// (El código completo ya fue proporcionado en artefactos anteriores.)

// --- includes/session_handler.php ---
// (Sin cambios. Maneja el inicio de sesión PHP y funciones como isLoggedIn(), requireLogin().)
// (El código completo ya fue proporcionado en artefactos anteriores.)

// --- api/auth.php ---
// (Sin cambios funcionales mayores. Maneja registro, login, logout y status de sesión.)
// (El código completo ya fue proporcionado en artefactos anteriores.)

// --- api/tariff_codes.php ---
// (Sin cambios funcionales mayores. Maneja el CRUD para las partidas arancelarias.)
// (El código completo ya fue proporcionado en artefactos anteriores.)

// --- api/csv_imports_history.php ---
// (Sin cambios funcionales mayores. Lista el historial de importaciones CSV.)
// (El código completo ya fue proporcionado en artefactos anteriores.)

// --- public/assets/css/style.css ---
// (Sin cambios funcionales mayores. Los estilos de impresión y generales se mantienen.)
// (El código completo ya fue proporcionado en artefactos anteriores.)


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
