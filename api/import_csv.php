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
        
        // Este script ahora solo parsea el CSV y lo devuelve. No maneja otros costos.
        $lineasDelCsv = [];
        $parseErrors = [];

        if (($handle = fopen($storedFilePath, "r")) !== FALSE) {
            $header = array_map('trim', fgetcsv($handle));
            $csvLineCounter = 1;

            while (($row = fgetcsv($handle)) !== FALSE) {
                $csvLineCounter++;
                if (count($header) != count($row)) {
                    $parseErrors[] = "Línea CSV {$csvLineCounter}: El número de columnas no coincide con el encabezado.";
                    continue;
                }
                $rowData = array_combine($header, $row);

                // Validación básica de datos numéricos
                $cantidad = floatval($rowData['cantidad'] ?? 0);
                $peso_kg_unitario = floatval($rowData['peso_kg_unitario'] ?? 0);
                $fob_usd_unitario = floatval($rowData['fob_usd_unitario'] ?? 0);

                if ($cantidad <= 0 || $peso_kg_unitario < 0 || $fob_usd_unitario < 0) {
                     $parseErrors[] = "Línea CSV {$csvLineCounter}: Contiene datos numéricos inválidos o cero en cantidad.";
                }

                $lineasDelCsv[] = $rowData;
            }
            fclose($handle);
        } else {
            sendJsonResponse(['success' => false, 'message' => 'Error abriendo el archivo CSV guardado para procesar.'], 500);
        }
        
        // V10: Enviar solo los datos parseados al frontend para la etapa de 'staging'.
        // No se realiza ningún cálculo ni inserción en la base de datos aquí.
        sendJsonResponse([
            'success' => empty($parseErrors),
            'message' => empty($parseErrors) ? 'CSV parseado correctamente. Por favor, revise y complete los datos a continuación.' : 'Se encontraron errores en el CSV. Por favor, revise los mensajes.',
            'parsed_data' => $lineasDelCsv,
            'errors' => $parseErrors
        ]);

    } else { sendJsonResponse(['success' => false, 'message' => 'Error subiendo archivo CSV o archivo vacío/inválido.'], 400); }
} else { sendJsonResponse(['success' => false, 'message' => 'Método no permitido.'], 405); }
?>
