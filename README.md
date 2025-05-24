# calculadora_impuestos_ecuador_php_postgres_v4
It helps you to calculate taxes and fees for any import process
// Este es un archivo conceptual que agrupa las diferentes partes del proyecto.
// VERSIÓN 3: Incluye lógica de ganancia e impresión de resúmenes.

// --------------------------------------------------------------------------
// --- 0. Estructura de Carpetas Sugerida (sin cambios mayores respecto a v2) ---
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
|   `-- templates/ (NUEVO, para plantillas de impresión HTML)
|       `-- print_summary_template.php
|-- config/
|   `-- db.php
|-- includes/
|   |-- functions.php
|   `-- session_handler.php
|-- uploads/ (asegurar permisos de escritura para el servidor web)
|-- .htaccess (opcional)
*/

// --------------------------------------------------------------------------
// --- 1. Backend: PHP (Modificaciones) -------------------------------------
// --------------------------------------------------------------------------

// --- config/db.php --- (Sin cambios)
// --- includes/session_handler.php --- (Sin cambios)
// --- includes/functions.php --- (Podría añadirse una función de cálculo reutilizable)
/*
Resumen de Mejoras Clave en esta Versión 4 Conceptual:

Función calculateImportationDetails Centralizada y Mejorada (en includes/functions.php):

Ahora es el núcleo del cálculo.
Recibe flete y seguro ya asignados/prorrateados para el ítem específico.
Recibe un flag isShipmentConsidered4x4 para aplicar la lógica 4x4 correcta basada en el estado del embarque completo (especialmente relevante para CSV).
Calcula todos los costos, impuestos, ganancia y PVP para la línea de producto.
api/calculate.php (Cálculo de Ítem Individual):

Llama a calculateImportationDetails.
Para un ítem individual, el flete y seguro ingresados por el usuario se consideran los totales para esa línea.
El flag esCourier4x4 del formulario se pasa como isShipmentConsidered4x4.
api/import_csv.php (Importación Masiva):

Primera Pasada: Lee todo el CSV para obtener el FOB total y peso total del embarque. Esto permite:
Determinar si el embarque completo califica para el régimen 4x4.
Calcular la base para el prorrateo del flete y seguro generales.
Segunda Pasada: Para cada ítem del CSV:
Calcula su porción de flete y seguro prorrateados (ej., basado en su valor FOB sobre el FOB total).
Llama a calculateImportationDetails con estos costos prorrateados y el flag 4x4 del embarque.
Devuelve resultados detallados por ítem (incluyendo su flete/seguro prorrateado y PVP) y un resumen consolidado del embarque.
Frontend:

La interfaz para el cálculo de ítem individual no cambia drásticamente en cuanto a entradas, pero la lógica de envío al backend y la visualización de resultados se adaptan.
La interfaz de importación CSV sigue pidiendo flete y seguro generales. Los resultados mostrados ahora pueden ser más detallados, reflejando el prorrateo por ítem.
Las funciones de impresión se adaptarán para mostrar esta información más granular y precisa.
Puntos Importantes Adicionales:

Método de Prorrateo: El ejemplo usa prorrateo por valor FOB. Podrías querer ofrecer opciones o usar prorrateo por peso para el flete si es más apropiado para tu caso de uso.
Lógica 4x4 para Embarques: La determinación de si un embarque completo es 4x4 es crucial. La versión 4 ahora hace esta determinación en api/import_csv.php y pasa el resultado a la función de cálculo para cada ítem. Ten en cuenta que las regulaciones 4x4 de SENAE pueden tener detalles adicionales (como límites por tipo de producto) que esta lógica simplificada no cubre.
Actualización de Tasas: Sigue siendo fundamental mantener actualizada tu tabla tariff_codes con las tasas de AdValorem, ICE, IVA (0%, 8%, 15% según corresponda por partida en Ecuador), etc.
Esta versión 4 se acerca mucho más a una herramienta profesional capaz de manejar escenarios de importación más complejos y realistas. Recuerda probar exhaustivamente, especialmente la lógica de prorrateo y la aplicación del régimen 4x4 en los CSV.
