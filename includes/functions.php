<?php
// includes/functions.php
function sendJsonResponse($data, $statusCode = 200) { /* ... */ }

// NUEVA FUNCIÓN DE CÁLCULO REUTILIZABLE (Conceptual)
function calculateImportationDetails($pdo, $valorFOBUnitario, $cantidad, $pesoUnitarioKg, $costoFleteLinea, $costoSeguroLinea, $tariffCodeId, $esCourier4x4, $profitPercentage) {
    // 1. Obtener datos de la partida arancelaria ($tariffData) desde $pdo y $tariffCodeId
    $stmt_tariff = $pdo->prepare("SELECT * FROM tariff_codes WHERE id = :id");
    $stmt_tariff->execute([':id' => $tariffCodeId]);
    $tariffData = $stmt_tariff->fetch(PDO::FETCH_ASSOC);
    if (!$tariffData) {
        return ['success' => false, 'message' => 'Partida arancelaria no encontrada en BD.'];
    }

    // 2. Cálculos base (FOB Total, Peso Total, CIF)
    $valorFOBTotal = $valorFOBUnitario * $cantidad;
    $pesoTotalKg = $pesoUnitarioKg * $cantidad;
    $cif = $valorFOBTotal + $costoFleteLinea + $costoSeguroLinea;

    // 3. Obtener tasas de $tariffData
    $adValoremRate = floatval($tariffData['advalorem_rate']);
    $iceRate = floatval($tariffData['ice_rate'] ?? 0);
    // IVA DESDE PARTIDA: Si la partida tiene un IVA específico (ej. 0.00 para libros, 0.08 para un ítem especial), se usa.
    // Si no, podría caer a un default global (ej. 0.15 actual en Ecuador, Mayo 2025, pero esta tasa cambia).
    // Por ahora, el default_iva_rate en la tabla tariff_codes es la fuente principal.
    $ivaRate = floatval($tariffData['iva_rate']); // Asumimos que la tabla tariff_codes SIEMPRE tendrá un valor (ej. 0.15, 0.08, 0.00)
    
    $fodinfaApplies = boolval($tariffData['fodinfa_applies'] ?? true);
    $specificTaxValue = floatval($tariffData['specific_tax_value'] ?? 0);
    $specificTaxUnit = $tariffData['specific_tax_unit'] ?? '';

    // 4. Lógica 4x4 (simplificada, como en v2)
    $aplicaExencion4x4 = false;
    if ($esCourier4x4 && $pesoTotalKg <= 4 && $valorFOBTotal <= 400) { // Estos límites también pueden cambiar
        $aplicaExencion4x4 = true;
    }

    // 5. Cálculo de Impuestos
    $adValoremCalculado = 0; $fodinfa = 0; $iceCalculado = 0; $ivaCalculado = 0; $specificTaxCalculado = 0;

    if ($aplicaExencion4x4) {
        // Exenciones del 4x4 (IVA puede o no ser exento, depende de la última regulación SENAE y tipo de producto)
        // Si la partida tiene iva_rate = 0 (ej. libros), se mantiene 0.
        // Si la partida tiene iva_rate > 0, y es 4x4, la práctica común es que no paga IVA.
        // Esta es una simplificación, la normativa exacta debe consultarse.
        if ($ivaRate > 0) $ivaCalculado = 0; // Asumimos 4x4 anula IVA si la partida no es 0% por defecto
    } else {
        $adValoremCalculado = $cif * $adValoremRate;
        if ($fodinfaApplies) $fodinfa = $cif * 0.005;
        $baseICE = $cif + $adValoremCalculado + $fodinfa;
        $iceCalculado = $baseICE * $iceRate;
        if ($specificTaxValue > 0) {
            if (stripos($specificTaxUnit, 'kg') !== false) $specificTaxCalculado = $pesoTotalKg * $specificTaxValue;
            elseif (stripos($specificTaxUnit, 'unidad') !== false) $specificTaxCalculado = $cantidad * $specificTaxValue;
        }
        $baseIVA = $cif + $adValoremCalculado + $fodinfa + $iceCalculado + $specificTaxCalculado;
        $ivaCalculado = $baseIVA * $ivaRate;
    }

    $totalImpuestos = $adValoremCalculado + $fodinfa + $iceCalculado + $ivaCalculado + $specificTaxCalculado;
    $costoTotalEstimadoConGastos = $valorFOBTotal + $costoFleteLinea + $costoSeguroLinea + $totalImpuestos; // Costo total del item/linea

    // 6. Cálculo de Ganancia y PVP
    $costPriceUnitAfterImport = ($cantidad > 0) ? ($costoTotalEstimadoConGastos / $cantidad) : 0;
    $profitAmountUnit = $costPriceUnitAfterImport * (floatval($profitPercentage) / 100);
    $pvpUnit = $costPriceUnitAfterImport + $profitAmountUnit;
    $pvpTotalLine = $pvpUnit * $cantidad;

    return [
        'success'                   => true,
        'calculoInput'              => [ /* ... como en v2, más profitPercentage ... */
            'valorFOBUnitario'      => $valorFOBUnitario,
            'cantidad'              => $cantidad,
            'valorFOBTotal'         => round($valorFOBTotal,2),
            'costoFlete'            => $costoFleteLinea,
            'costoSeguro'           => $costoSeguroLinea,
            'partidaArancelariaInfo'=> $tariffData, // Contiene code, description, y todas las tasas base
            'esCourier4x4'          => $esCourier4x4,
            'aplicaExencion4x4'     => $aplicaExencion4x4,
            'pesoUnitarioKg'        => $pesoUnitarioKg,
            'pesoTotalKg'           => round($pesoTotalKg,3),
            'profitPercentageApplied'=> $profitPercentage
        ],
        'cif'                       => round($cif, 2),
        'adValorem'                 => round($adValoremCalculado, 2),
        'fodinfa'                   => round($fodinfa, 2),
        'ice'                       => round($iceCalculado, 2),
        'specificTax'               => round($specificTaxCalculado, 2),
        'iva'                       => round($ivaCalculado, 2),
        'totalImpuestos'            => round($totalImpuestos, 2),
        'costoTotalEstimadoLinea'   => round($costoTotalEstimadoConGastos, 2), // Costo total de la línea
        // Nuevos campos de ganancia y PVP
        'cost_price_unit_after_import' => round($costPriceUnitAfterImport, 2),
        'profit_amount_unit'        => round($profitAmountUnit, 2),
        'pvp_unit'                  => round($pvpUnit, 2),
        'pvp_total_line'            => round($pvpTotalLine, 2)
    ];
}
?>
