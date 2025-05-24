*/

// --------------------------------------------------------------------------
// --- 3. SQL para PostgreSQL (Tabla `calculations` sin cambios para v4) ----
// --------------------------------------------------------------------------
// La tabla `calculations` de la v3 ya está preparada para almacenar los costos por línea
// (ej. `costo_flete`, `costo_seguro` serían los asignados a esa línea después del prorrateo si viene de un CSV,
// o los ingresados directamente si es un cálculo individual).
// Los campos de ganancia y PVP ya estaban definidos.
/*
-- Tabla de Usuarios (sin cambios)
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Partidas Arancelarias (tariff_codes)
CREATE TABLE tariff_codes (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL, 
    description TEXT NOT NULL,
    advalorem_rate NUMERIC(5, 4) DEFAULT 0.0000, 
    ice_rate NUMERIC(5, 4) DEFAULT 0.0000,
    fodinfa_applies BOOLEAN DEFAULT TRUE,
    iva_rate NUMERIC(5, 4) DEFAULT 0.1500, -- Tasa de IVA base para esta partida (ej. 0.15, 0.08, 0.00)
    specific_tax_value NUMERIC(10, 2) DEFAULT 0.00,
    specific_tax_unit VARCHAR(50), 
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_tariff_code_code ON tariff_codes(code);
CREATE INDEX idx_tariff_code_description ON tariff_codes(description text_pattern_ops); -- Para búsquedas ILIKE más rápidas


-- Tabla de Cálculos Guardados
CREATE TABLE calculations (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    product_name VARCHAR(255) NOT NULL,
    
    tariff_code_id INTEGER REFERENCES tariff_codes(id),

    valor_fob_unitario NUMERIC(12, 2) NOT NULL,
    cantidad INTEGER DEFAULT 1,
    peso_unitario_kg NUMERIC(10, 3),

    costo_flete NUMERIC(12, 2), -- Flete para esta línea/cálculo (ya sea directo o prorrateado)
    costo_seguro NUMERIC(12, 2), -- Seguro para esta línea/cálculo (ya sea directo o prorrateado)
    es_courier_4x4 BOOLEAN DEFAULT FALSE, -- Si este cálculo individual se consideró 4x4
    
    -- Resultados del cálculo de importación
    cif NUMERIC(12, 2),
    ad_valorem NUMERIC(12, 2),
    fodinfa NUMERIC(12, 2),
    ice NUMERIC(12, 2),
    specific_tax NUMERIC(12,2), 
    iva NUMERIC(12, 2),
    total_impuestos NUMERIC(12, 2),
    costo_total_estimado_linea NUMERIC(14, 2), 

    -- Campos para ganancia y PVP
    profit_percentage_applied NUMERIC(5, 2), 
    cost_price_unit_after_import NUMERIC(12, 2), 
    profit_amount_unit NUMERIC(12, 2), 
    pvp_unit NUMERIC(12, 2), 
    pvp_total_line NUMERIC(14, 2), 
    
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Trigger para updated_at
CREATE OR REPLACE FUNCTION trigger_set_timestamp()
RETURNS TRIGGER AS $$ BEGIN NEW.updated_at = NOW(); RETURN NEW; END; $$ LANGUAGE plpgsql;

CREATE TRIGGER set_timestamp_tariff_codes
BEFORE UPDATE ON tariff_codes FOR EACH ROW EXECUTE PROCEDURE trigger_set_timestamp();

CREATE TRIGGER set_timestamp_calculations
BEFORE UPDATE ON calculations FOR EACH ROW EXECUTE PROCEDURE trigger_set_timestamp();

-- Insertar algunas partidas de ejemplo (Ajustar iva_rate al valor actual de Ecuador, ej. 15% general):
INSERT INTO tariff_codes (code, description, advalorem_rate, iva_rate, ice_rate) VALUES 
('8703.23.90.10', 'Vehículos Gasolina >1500cc <=2000cc', 0.35, 0.15, 0.15),
('8517.12.00.00', 'Teléfonos móviles (celulares)', 0.00, 0.15, 0.00),
('8471.30.00.00', 'Laptops, notebooks', 0.00, 0.15, 0.00),
('6203.42.90.00', 'Pantalones de algodón, hombres', 0.10, 0.15, 0.00),
('4901.99.90.00', 'Libros, folletos e impresos', 0.00, 0.00, 0.00); -- IVA 0% para libros
*/

// --------------------------------------------------------------------------
// --- 4. Pasos para Implementación y Configuración (como en v3) -----------
// --------------------------------------------------------------------------
// Los pasos generales de configuración del servidor, base de datos, PHP, Apache, y GitHub
// son los mismos que los detallados en la versión 3.
// La diferencia principal es la lógica actualizada en los archivos PHP y JS.
// Asegúrate de que la función `calculateImportationDetails` esté bien definida en `includes/functions.php`
// y sea llamada correctamente desde `api/calculate.php` y `api/import_csv.php`.
// **CRÍTICO:** Poblar la tabla `tariff_codes` con datos precisos y actualizados del arancel ecuatoriano.
?>
