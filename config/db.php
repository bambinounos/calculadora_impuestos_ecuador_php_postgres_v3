// --------------------------------------------------------------------------
// --- 1. Backend: PHP ------------------------------------------------------
// --------------------------------------------------------------------------

// --- config/db.php ---
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
