Explicación de las Columnas:

partida_codigo (Obligatorio): Código de la partida arancelaria (Ej: 8517.12.00.00).
cantidad (Obligatorio): Número de unidades (Ej: 10).
peso_kg_unitario (Obligatorio): Peso unitario en Kg (Ej: 0.180).
fob_usd_unitario (Obligatorio): Valor FOB unitario en USD (Ej: 150.00).
descripcion_producto (Opcional pero recomendado): Descripción del ítem (Ej: "Teléfono Móvil Súper Modelo X1"). Si la descripción contiene comas, debe ir entre comillas dobles.
profit_percentage_linea (Opcional): Porcentaje de ganancia específico para esta línea (Ej: 25 para 25%). Si está vacío, se usará el porcentaje general ingresado en el formulario.
Consideraciones:

Cabecera: La primera línea debe ser exactamente como se muestra (los nombres de las columnas) para que el script PHP la pueda omitir correctamente.
Delimitador: Coma (,).
Codificación: UTF-8.
2. Ubicación y Manejo de Archivos CSV Subidos
En el script api/import_csv.php que te proporcioné en la Versión 4, el archivo CSV se procesa directamente desde la ubicación temporal donde PHP lo almacena al subirse. Específicamente, esta línea:

PHP

$csvFile = $_FILES['csvFile']['tmp_name'];
¿Qué significa esto?

No se guarda permanentemente en el servidor por defecto: Cuando un archivo se sube a través de un formulario PHP, PHP lo guarda en un directorio temporal del servidor (definido en la configuración de PHP, por ejemplo, /tmp en Linux). El nombre del archivo en este directorio temporal es aleatorio.
$_FILES['csvFile']['tmp_name'] te da la ruta completa a ese archivo temporal.
El script api/import_csv.php abre, lee y procesa este archivo temporal.
Una vez que el script PHP termina su ejecución, este archivo temporal generalmente es eliminado automáticamente por PHP.
Ventajas de este enfoque:

Simplicidad: No necesitas crear y gestionar permisos para un directorio de subidas específico para almacenar los CSVs permanentemente si no lo deseas.
Limpieza Automática: No te preocupas por borrar archivos CSV viejos del servidor.
Posibles Desventajas y Alternativas (Si Necesitas Guardar los CSVs):

Si quisieras guardar una copia del archivo CSV subido en el servidor por razones de auditoría, registro, o para reprocesarlo después, necesitarías modificar el script api/import_csv.php:

Crear un Directorio de Subidas:

Dentro de tu estructura de proyecto, por ejemplo: calculadora_importacion_php/uploads/csv/
Asegúrate de que el usuario con el que corre tu servidor web (ej. www-data en Apache sobre Debian/Ubuntu) tenga permisos de escritura en este directorio.
Bash

# Ejemplo en Linux, desde la raíz de tu proyecto
mkdir -p public/api/uploads/csv # O simplemente 'uploads/' en la raíz si prefieres
sudo chown www-data:www-data public/api/uploads/csv # O la ruta que elijas
sudo chmod 755 public/api/uploads/csv # O 775 si es necesario temporalmente
Modificar api/import_csv.php para Mover el Archivo:

PHP

// ... al inicio de api/import_csv.php ...
if (isset($_FILES['csvFile']) && $_FILES['csvFile']['error'] == UPLOAD_ERR_OK) {
    $tempFilePath = $_FILES['csvFile']['tmp_name'];
    $originalFileName = basename($_FILES['csvFile']['name']); // Nombre original para referencia

    // Crear un nombre de archivo único para evitar colisiones
    $sanitizedOriginalName = preg_replace("/[^a-zA-Z0-9._-]/", "", $originalFileName);
    $destinationFileName = time() . "_" . uniqid() . "_" . $sanitizedOriginalName;

    // Definir el directorio de subidas (relativo al script actual o una ruta absoluta)
    // Si api/import_csv.php está en public/api/, y uploads está en public/api/uploads/
    $uploadDir = __DIR__ . '/uploads/csv/'; // __DIR__ es el directorio del script actual
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true); // Intentar crear si no existe
    }
    $destinationPath = $uploadDir . $destinationFileName;

    if (move_uploaded_file($tempFilePath, $destinationPath)) {
        echo "Archivo CSV guardado en: " . htmlspecialchars($destinationPath) . "\n"; // Para depuración
        $csvFileToProcess = $destinationPath; // Usar este archivo guardado para procesar
    } else {
        // Error moviendo el archivo, podrías procesar desde $tempFilePath
        // o mostrar un error si el guardado es crítico.
        $parseErrors[] = "Error: No se pudo guardar el archivo CSV subido en el servidor. Procesando desde ubicación temporal.";
        $csvFileToProcess = $tempFilePath;
    }

    // ... el resto del script sigue igual, usando $csvFileToProcess para fopen() ...
    // if (($handle = fopen($csvFileToProcess, "r")) !== FALSE) { ... }

} else {
    // ... manejo de error de subida ...
}
Decisión sobre Guardar o no el CSV:

Para la funcionalidad básica de cálculo: No es estrictamente necesario guardar el CSV en el servidor. Procesarlo desde la ubicación temporal es suficiente.
Si necesitas un historial de los archivos importados: Entonces sí deberías implementar la lógica para mover el archivo a una carpeta permanente como uploads/csv/ y quizás guardar la ruta del archivo o su contenido en la base de datos si quieres asociarlo con un lote de importación específico.
En resumen, para la Versión 4 actual:

El script no guarda el CSV permanentemente. Lo procesa desde la ubicación temporal de PHP.
Si deseas cambiar esto, debes crear un directorio (ej. public/api/uploads/csv/), darle permisos de escritura al servidor web, y añadir la lógica en api/import_csv.php para mover el archivo subido (move_uploaded_file()) a ese directorio antes de procesarlo con fopen().
Espero que esto aclare el modelo del CSV y cómo se maneja el archivo en el servidor.
