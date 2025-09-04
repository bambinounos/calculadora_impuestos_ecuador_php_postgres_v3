// --------------------------------------------------------------------------
// --- 3. SQL para PostgreSQL (MODIFICADO para v9) --------------------------
// --------------------------------------------------------------------------
-- Tabla de Usuarios (sin cambios)
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
COMMENT ON TABLE users IS 'Almacena la información de los usuarios registrados.';

-- Tabla de Partidas Arancelarias (tariff_codes)
CREATE TABLE IF NOT EXISTS tariff_codes (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL, 
    description TEXT NOT NULL,
    advalorem_rate NUMERIC(7, 6) DEFAULT 0.000000,
    ice_rate NUMERIC(7, 6) DEFAULT 0.000000,
    fodinfa_applies BOOLEAN DEFAULT TRUE,
    iva_rate NUMERIC(7, 6) DEFAULT 0.150000, -- Tasa de IVA base para esta partida
    specific_tax_value NUMERIC(10, 2) DEFAULT 0.00,
    specific_tax_unit VARCHAR(50), 
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_tariff_code_code ON tariff_codes(code);
CREATE INDEX IF NOT EXISTS idx_tariff_code_description ON tariff_codes(description text_pattern_ops);
COMMENT ON TABLE tariff_codes IS 'Almacena las partidas arancelarias y sus tasas impositivas asociadas.';

-- NUEVA: Tabla para registrar las importaciones de CSV
CREATE TABLE IF NOT EXISTS csv_imports (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    original_filename VARCHAR(255) NOT NULL,
    stored_filepath TEXT NOT NULL, 
    upload_timestamp TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    processing_status VARCHAR(50) DEFAULT 'pendiente', 
    total_lines INTEGER, 
    processed_lines INTEGER,
    error_count INTEGER,
    
    total_flete_internacional NUMERIC(14,2),
    total_seguro_internacional NUMERIC(14,2),
    total_agente_aduana NUMERIC(14,2), 
    tasa_isd_aplicada NUMERIC(5,2), -- Tasa ISD en % (ej. 5.00) que se USÓ para el embarque
    total_isd_pagado NUMERIC(14,2), -- Monto total de ISD calculado para el embarque
    total_bodega_aduana NUMERIC(14,2), 
    total_demoraje NUMERIC(14,2), 
    total_flete_terrestre NUMERIC(14,2), 
    total_gastos_varios NUMERIC(14,2), 
    proration_method_used VARCHAR(10),

    consolidated_summary_json JSONB, 
    CONSTRAINT fk_user_csv_import_v9 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);
COMMENT ON TABLE csv_imports IS 'Registra cada archivo CSV subido, incluyendo gastos generales, tasa ISD y método de prorrateo del embarque.';


-- Tabla de Cálculos Guardados (MODIFICADA para reflejar el ISD pagado por ítem y otros gastos)
CREATE TABLE IF NOT EXISTS calculations (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_sku VARCHAR(255) NULL,
    tariff_code_id INTEGER, 
    valor_fob_unitario NUMERIC(12, 2) NOT NULL,
    cantidad INTEGER DEFAULT 1,
    peso_unitario_kg NUMERIC(10, 3),
    costo_flete NUMERIC(12, 2), -- Flete INTERNACIONAL prorrateado o directo
    costo_seguro NUMERIC(12, 2), -- Seguro INTERNACIONAL prorrateado o directo
    agente_aduana_prorrateado_item NUMERIC(12,2), 
    isd_pagado_item NUMERIC(12,2), -- ISD calculado y pagado para esta línea
    otros_gastos_prorrateados_item NUMERIC(12,2), -- Suma de bodega, demoraje, flete terr., varios, prorrateados
    es_courier_4x4 BOOLEAN DEFAULT FALSE, 
    cif NUMERIC(14, 2),
    ad_valorem NUMERIC(14, 2),
    fodinfa NUMERIC(14, 2),
    ice NUMERIC(14, 2),
    specific_tax NUMERIC(14,2), 
    iva NUMERIC(14, 2),
    total_impuestos NUMERIC(14, 2), -- Suma de AdV,Fodinfa,ICE,IVA,Específicos
    costo_total_estimado_linea NUMERIC(16, 2), 
    profit_percentage_applied NUMERIC(5, 2), 
    cost_price_unit_after_import NUMERIC(14, 2), 
    profit_amount_unit NUMERIC(14, 2), 
    pvp_unit NUMERIC(14, 2), 
    pvp_total_line NUMERIC(16, 2), 
    csv_import_id INTEGER NULL,
    csv_import_line_number INTEGER NULL, 
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_calc_v9 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_tariff_code_calc_v9 FOREIGN KEY(tariff_code_id) REFERENCES tariff_codes(id) ON DELETE SET NULL,
    CONSTRAINT fk_csv_import_calc_v9 FOREIGN KEY(csv_import_id) REFERENCES csv_imports(id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS idx_calculations_user_id_v9 ON calculations(user_id);
CREATE INDEX IF NOT EXISTS idx_calculations_tariff_code_id_v9 ON calculations(tariff_code_id);
CREATE INDEX IF NOT EXISTS idx_calculations_csv_import_id_v9 ON calculations(csv_import_id);


-- Trigger para updated_at
DO $$
BEGIN
   IF NOT EXISTS (SELECT 1 FROM pg_proc WHERE proname = 'trigger_set_timestamp') THEN
      CREATE OR REPLACE FUNCTION trigger_set_timestamp()
      RETURNS TRIGGER AS $func$
      BEGIN NEW.updated_at = NOW(); RETURN NEW; END;
      $func$ LANGUAGE plpgsql;
      COMMENT ON FUNCTION trigger_set_timestamp() IS 'Función de trigger para actualizar automáticamente el campo updated_at.';
   END IF;
END $$;

DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'set_timestamp_tariff_codes' AND tgrelid = 'tariff_codes'::regclass) THEN
   CREATE TRIGGER set_timestamp_tariff_codes BEFORE UPDATE ON tariff_codes FOR EACH ROW EXECUTE PROCEDURE trigger_set_timestamp(); END IF; END $$;
DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'set_timestamp_calculations' AND tgrelid = 'calculations'::regclass) THEN
   CREATE TRIGGER set_timestamp_calculations BEFORE UPDATE ON calculations FOR EACH ROW EXECUTE PROCEDURE trigger_set_timestamp(); END IF; END $$;

-- Insertar algunas partidas de ejemplo (Ajustar iva_rate al valor actual de Ecuador, ej. 15% general):
INSERT INTO tariff_codes (code, description, advalorem_rate, iva_rate, ice_rate) VALUES 
('8703.23.90.10', 'Vehículos Gasolina >1500cc <=2000cc', 0.35, 0.15, 0.15) ON CONFLICT (code) DO NOTHING;
INSERT INTO tariff_codes (code, description, advalorem_rate, iva_rate, ice_rate) VALUES 
('8517.12.00.00', 'Teléfonos móviles (celulares)', 0.00, 0.15, 0.00) ON CONFLICT (code) DO NOTHING;
INSERT INTO tariff_codes (code, description, advalorem_rate, iva_rate, ice_rate) VALUES 
('8471.30.00.00', 'Laptops, notebooks', 0.00, 0.15, 0.00) ON CONFLICT (code) DO NOTHING;
INSERT INTO tariff_codes (code, description, advalorem_rate, iva_rate, ice_rate, specific_tax_value, specific_tax_unit) VALUES 
('6203.42.90.00', 'Pantalones de algodón, hombres', 0.10, 0.15, 0.00, 5.50, 'USD/Kg') ON CONFLICT (code) DO NOTHING;
INSERT INTO tariff_codes (code, description, advalorem_rate, iva_rate, ice_rate) VALUES 
('4901.99.90.00', 'Libros, folletos e impresos', 0.00, 0.00, 0.00) ON CONFLICT (code) DO NOTHING;

// --------------------------------------------------------------------------
// --- 4. Pasos para Implementación y Configuración (similares a v8) --------
// --------------------------------------------------------------------------
// ...
