// --------------------------------------------------------------------------
// --- 2. Script PHP de Instalación: install.php (Actualizado para v9) ------
// --------------------------------------------------------------------------
/*
<?php
// Archivo: install.php
// Script CLI para ayudar a configurar la base de datos para la V9.
// Ejecutar desde la línea de comandos: php install.php

echo "-------------------------------------------------\n";
echo "Asistente de Instalación de Base de Datos para\n";
echo "Calculadora de Impuestos de Importación Ecuador V9\n";
echo "-------------------------------------------------\n\n";

// --- Obtener detalles de conexión del usuario ---
$db_details = [];
$db_details['host'] = readline("Host de PostgreSQL (ej. localhost): ") ?: 'localhost';
$db_details['port'] = readline("Puerto de PostgreSQL (ej. 5432): ") ?: '5432';
$db_details['dbname_to_create'] = readline("Nombre de la base de datos a usar/crear (ej. calculadora_impuestos_v9): ");
if (empty($db_details['dbname_to_create'])) {
    echo "Error: El nombre de la base de datos es obligatorio.\n";
    exit(1);
}
$db_details['user'] = readline("Usuario de PostgreSQL (con permisos para crear tablas y, opcionalmente, DB): ");
if (empty($db_details['user'])) {
    echo "Error: El nombre de usuario de PostgreSQL es obligatorio.\n";
    exit(1);
}
echo "Contraseña para el usuario '{$db_details['user']}' de PostgreSQL: ";
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $db_details['password'] = readline();
} else {
    system('stty -echo');
    $db_details['password'] = trim(fgets(STDIN));
    system('stty echo');
    echo "\n";
}


// --- Paso 1: Intentar conectar al servidor PostgreSQL ---
echo "\nIntentando conectar al servidor PostgreSQL en {$db_details['host']}...\n";
$dsn_server = "pgsql:host={$db_details['host']};port={$db_details['port']}";
try {
    $pdo_server = new PDO($dsn_server, $db_details['user'], $db_details['password']);
    $pdo_server->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexión al servidor PostgreSQL exitosa.\n";
} catch (PDOException $e) {
    echo "Error: No se pudo conectar al servidor PostgreSQL.\n";
    echo "Detalles: " . $e->getMessage() . "\n";
    echo "Por favor, verifique los detalles de conexión y los permisos del usuario.\n";
    exit(1);
}

// --- Paso 2: Intentar crear la base de datos si no existe ---
$create_db_choice = strtolower(readline("¿Intentar crear la base de datos '{$db_details['dbname_to_create']}' si no existe? (s/N): ") ?: 'n');
if ($create_db_choice === 's') {
    try {
        $stmt_check_db = $pdo_server->prepare("SELECT 1 FROM pg_database WHERE datname = :dbname");
        $stmt_check_db->execute([':dbname' => $db_details['dbname_to_create']]);
        
        if ($stmt_check_db->fetch()) {
            echo "La base de datos '{$db_details['dbname_to_create']}' ya existe.\n";
        } else {
            echo "Intentando crear la base de datos '{$db_details['dbname_to_create']}'...\n";
            $pdo_server->exec("CREATE DATABASE \"{$db_details['dbname_to_create']}\"");
            echo "Base de datos '{$db_details['dbname_to_create']}' creada exitosamente.\n";
        }
    } catch (PDOException $e) {
        echo "Advertencia: No se pudo crear o verificar la base de datos '{$db_details['dbname_to_create']}'.\n";
        echo "Detalles: " . $e->getMessage() . "\n";
        echo "Esto puede ocurrir si el usuario '{$db_details['user']}' no tiene el privilegio CREATEDB.\n";
        echo "Si la base de datos ya existe y es accesible, puede continuar.\n";
        $continue_anyway = strtolower(readline("¿Continuar con la creación de tablas en '{$db_details['dbname_to_create']}' (asumiendo que existe)? (S/n): ") ?: 's');
        if ($continue_anyway !== 's') {
            exit(1);
        }
    }
}
$pdo_server = null; // Cerrar conexión al servidor general

// --- Paso 3: Conectar a la base de datos específica y ejecutar el script SQL ---
echo "\nIntentando conectar a la base de datos '{$db_details['dbname_to_create']}'...\n";
$dsn_db = "pgsql:host={$db_details['host']};port={$db_details['port']};dbname={$db_details['dbname_to_create']}";
try {
    $pdo_db = new PDO($dsn_db, $db_details['user'], $db_details['password']);
    $pdo_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexión a la base de datos '{$db_details['dbname_to_create']}' exitosa.\n";

    echo "Ejecutando script de creación de tablas V9 (setup_database.sql)...\n";
    $sql_script_path = __DIR__ . '/setup_database.sql';
    if (!file_exists($sql_script_path)) {
        echo "Error: No se encontró el archivo 'setup_database.sql' en la ruta: {$sql_script_path}\n";
        echo "Por favor, asegúrese de que el archivo SQL esté presente en el mismo directorio que este script de instalación.\n";
        exit(1);
    }
    $sql_commands = file_get_contents($sql_script_path);
    if ($sql_commands === false) {
        echo "Error: No se pudo leer el archivo 'setup_database.sql'.\n";
        exit(1);
    }

    $pdo_db->exec($sql_commands);
    echo "Script de creación de tablas V9 ejecutado.\n";
    echo "Las tablas 'users', 'tariff_codes', 'csv_imports' y 'calculations' deberían estar creadas/actualizadas.\n";

} catch (PDOException $e) {
    echo "Error: No se pudo conectar o ejecutar el script en la base de datos '{$db_details['dbname_to_create']}'.\n";
    echo "Detalles: " . $e->getMessage() . "\n";
    echo "Verifique que la base de datos exista, que los permisos del usuario '{$db_details['user']}' sean correctos y que el script SQL sea válido.\n";
    exit(1);
}

// --- Paso 4: Mostrar cómo crear el archivo config/db.php ---
echo "\n-------------------------------------------------\n";
echo "¡Instalación de la base de datos V9 completada!\n";
echo "-------------------------------------------------\n\n";
echo "Ahora, debe crear (o actualizar) el archivo de configuración 'config/db.php' en su proyecto.\n";
echo "Cree un archivo en la ruta: [RAIZ_DE_SU_PROYECTO]/config/db.php\n";
echo "Con el siguiente contenido, reemplazando los valores si es necesario:\n\n";

echo "<?php\n";
echo "// config/db.php\n";
echo "\$host = '{$db_details['host']}';\n";
echo "\$port = '{$db_details['port']}';\n";
echo "\$dbname = '{$db_details['dbname_to_create']}';\n";
echo "\$user = '{$db_details['user']}';\n";
echo "\$password = '{$db_details['password']}'; // ¡Considere usar variables de entorno para la contraseña en producción!\n\n";
echo "\$dsn = \"pgsql:host={\$host};port={\$port};dbname={\$dbname};user={\$user};password={\$password}\";\n\n";
echo "try {\n";
echo "    \$pdo = new PDO(\$dsn);\n";
echo "    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n";
echo "} catch (PDOException \$e) {\n";
echo "    header('Content-Type: application/json');\n";
echo "    http_response_code(500);\n";
echo "    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);\n";
echo "    exit;\n";
echo "}\n";
echo "?>\n\n";

echo "IMPORTANTE: Asegúrese de que 'config/db.php' esté protegido y no sea accesible públicamente.\n";
echo "Exclúyalo de su repositorio Git si contiene credenciales reales.\n\n";
echo "Siguientes pasos:\n";
echo "1. Verifique que las tablas se hayan creado correctamente en su base de datos PostgreSQL.\n";
echo "2. Asegúrese de que la tabla 'tariff_codes' esté poblada con las partidas arancelarias y tasas correctas para Ecuador.\n";
echo "3. Configure su servidor web (Apache) para que apunte al directorio 'public' de su proyecto.\n";
echo "4. ¡Pruebe la aplicación!\n";

exit(0);
?>
