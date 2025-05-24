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
?>
