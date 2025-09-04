# Informe Detallado del Cálculo de Costos de Importación

Este documento proporciona un desglose completo de los campos y procesos utilizados en la calculadora para determinar el costo final de los productos importados, incluyendo el prorrateo de gastos y el cálculo de impuestos.

---

## 1. Flujo del Cálculo de Costos

El costo de un producto se determina en tres fases principales. Los gastos generales se prorratean entre todos los artículos (ya sea por valor FOB o por peso) a menos que se especifique un costo directo para un artículo en el CSV.

### Fase 1: Cálculo del Valor CIF

El valor CIF (Costo, Seguro y Flete) es la base para la mayoría de los impuestos de importación.

| Campo | Origen | Descripción | Cálculo |
| :--- | :--- | :--- | :--- |
| **Valor FOB Unitario** | Entrada Manual / CSV | El costo del producto sin incluir envío ni seguro. | `fob_unitario_usd` |
| **Cantidad** | Entrada Manual / CSV | Número de unidades del producto. | `cantidad` |
| **Flete Prorrateado** | Calculado | La porción del flete internacional total asignada a este artículo. | `Flete Total * Factor de Prorrateo` |
| **Seguro Prorrateado** | Calculado | La porción del seguro internacional total asignada a este artículo. | `Seguro Total * Factor de Prorrateo` |
| **Valor CIF** | **Calculado** | **El costo total del producto incluyendo flete y seguro hasta el puerto de destino.** | **`(FOB Unitario * Cantidad) + Flete Prorrateado + Seguro Prorrateado`** |

*Nota: Si se proporciona `flete_item_directo` o `seguro_item_directo` en el CSV, estos valores se usan en lugar del costo prorrateado.*

### Fase 2: Cálculo de Impuestos y Tributos

Una vez calculado el CIF, se determinan los siguientes impuestos.

| Impuesto/Tributo | Base de Cálculo | Tasa | Notas |
| :--- | :--- | :--- | :--- |
| **Ad Valorem** | Valor CIF | Variable (según partida arancelaria) | `CIF * Tasa Ad Valorem` |
| **FODINFA** | Valor CIF | 0.5% | `CIF * 0.005` |
| **ICE** | `CIF + Ad Valorem + FODINFA` | Variable (según partida arancelaria) | `(Base ICE) * Tasa ICE` |
| **Imp. Específicos**| Cantidad o Peso | Variable (según partida arancelaria) | Ej: `Peso Total * Tasa Específica` |
| **Base Imponible IVA**| `CIF + Ad Valorem + FODINFA + ICE + Imp. Específicos` | - | Es la suma de todos los costos y tributos pre-IVA. |
| **IVA** | Base Imponible IVA | Variable (generalmente 15% o 0%) | `Base Imponible IVA * Tasa IVA` |
| **Total Impuestos** | **Suma de todo lo anterior** | - | **Suma de Ad Valorem, FODINFA, ICE, Imp. Específicos e IVA.** |

#### Cálculo Especial: ISD

| Impuesto | Base de Cálculo | Tasa | Notas |
| :--- | :--- | :--- | :--- |
| **ISD** | Valor FOB Total de la línea | Variable (definida por el usuario, ej. 5%) | `(FOB Unitario * Cantidad) * Tasa ISD`. **Importante:** El ISD es un costo y **NO** forma parte de la base para el IVA. |

### Fase 3: Cálculo del Costo Total Final

El costo final del producto en bodega se obtiene sumando los gastos post-nacionalización al valor CIF y los impuestos.

| Campo | Origen | Descripción |
| :--- | :--- | :--- |
| **CIF** | Calculado | Valor del producto, flete y seguro. |
| **Total Impuestos** | Calculado | Suma de todos los tributos de importación. |
| **ISD Pagado** | Calculado | El monto del ISD para la línea de producto. |
| **Agente Aduana Prorrateado** | Calculado | La porción de los honorarios del agente de aduana. |
| **Otros Gastos Prorrateados** | Calculado | Suma prorrateada de: Bodega, Demoraje, Flete Terrestre y Gastos Varios. |
| **Costo Total Estimado** | **Calculado** | **La suma de todos los campos anteriores. Representa el costo final del producto.** |

---

## 2. Especificación del Formato de Importación CSV

El sistema ahora utiliza la primera fila del CSV como cabecera, lo que significa que **el orden de las columnas es flexible**. Asegúrese de que los nombres de las columnas en su archivo coincidan con los especificados a continuación.

### Columnas Requeridas

Estas columnas deben estar siempre presentes en el archivo CSV.

| Nombre de Columna | Descripción | Ejemplo |
| :--- | :--- | :--- |
| **partida_codigo** | El código de la partida arancelaria del producto. Debe existir en la base de datos. | `8517.12.00.00` |
| **cantidad** | El número de unidades del producto. Debe ser un número entero positivo. | `150` |
| **peso_kg_unitario** | El peso de una sola unidad del producto en kilogramos. | `0.185` |
| **fob_usd_unitario**| El valor FOB de una sola unidad del producto en USD. | `250.75` |

### Columnas Opcionales

Estas columnas pueden ser incluidas para mayor detalle y control.

| Nombre de Columna | Descripción | Ejemplo |
| :--- | :--- | :--- |
| **descripcion** | Nombre o descripción del producto. Si se omite, se genera uno automáticamente. | `Teléfono Móvil 128GB` |
| **profit_percent** | El porcentaje de ganancia deseado para este producto. Si se omite, se usa el valor general ingresado en el formulario. | `30` |
| **sku** | **(Nuevo)** Un código de producto único (Stock Keeping Unit) para su control de inventario. | `TM-BLK-128-V2` |
| **flete_item_directo** | **(Nuevo)** El costo de flete internacional **específico para este artículo**. Si se proporciona, este valor se usará en lugar de prorratear el flete general. | `15.50` |
| **seguro_item_directo** | **(Nuevo)** El costo de seguro internacional **específico para este artículo**. Si se proporciona, anula el valor prorrateado. | `3.25` |

### Ejemplo de Archivo CSV

```csv
partida_codigo,cantidad,peso_kg_unitario,fob_usd_unitario,descripcion,sku,flete_item_directo
8517.12.00.00,10,0.2,300,Celular Gama Alta,CEL-GA-001,
8471.30.00.00,5,1.5,800,Laptop Profesional,LAP-PRO-005,25.00
6203.42.90.00,50,0.8,25,Pantalón de Algodón,PANT-ALG-M,
```

En este ejemplo:
- El **Celular Gama Alta** usará el flete y seguro prorrateados.
- La **Laptop Profesional** tiene un flete directo de $25.00, por lo que este valor se usará para el cálculo y no se le asignará flete prorrateado. Usará el seguro prorrateado.
- El **Pantalón de Algodón** usará el flete y seguro prorrateados.
