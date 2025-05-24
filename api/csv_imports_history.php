*/

// --- api/csv_imports_history.php --- (NUEVO)
/*
<?php
// api/csv_imports_history.php
require_once '../config/db.php';
require_once '../includes/session_handler.php';
require_once '../includes/functions.php';

requireLogin();
$userId = $_SESSION['user_id'];

try {
    // El campo consolidated_summary_json puede ser grande, considera no traerlo siempre o usarlo para un "ver detalle"
    $stmt = $pdo->prepare("SELECT id, original_filename, upload_timestamp, processing_status, total_lines, processed_lines, error_count 
                           FROM csv_imports 
                           WHERE user_id = :user_id 
                           ORDER BY upload_timestamp DESC LIMIT 50"); // Limitar o paginar
    $stmt->execute([':user_id' => $userId]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    sendJsonResponse(['success' => true, 'history' => $history]);
} catch (PDOException $e) {
    sendJsonResponse(['success' => false, 'message' => 'Error cargando historial de importaciones CSV: ' . $e->getMessage()], 500);
}
?>
