// --------------------------------------------------------------------------
// --- 1. Script SQL: setup_database.sql ------------------------------------
// --------------------------------------------------------------------------
-- Archivo: setup_database.sql
-- Script para crear la estructura de la base de datos PostgreSQL
-- para la Calculadora de Impuestos de Importación Ecuador V4.

-- Opcional: Descomentar para borrar tablas si existen (para desarrollo/reinstalación limpia)
-- SET client_min_messages TO WARNING; -- Suprime mensajes NOTICE de "does not exist"
-- DROP TRIGGER IF EXISTS set_timestamp_calculations ON calculations;
-- DROP TRIGGER IF EXISTS set_timestamp_tariff_codes ON tariff_codes;
-- DROP FUNCTION IF EXISTS trigger_set_timestamp();
-- DROP TABLE IF EXISTS calculations;
-- DROP TABLE IF EXISTS tariff_codes;
-- DROP TABLE IF EXISTS users;
-- SET client_min_messages TO NOTICE;

-- Tabla de Usuarios
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
COMMENT ON TABLE users IS 'Almacena la información de los usuarios registrados.';
COMMENT ON COLUMN users.email IS 'Email único del usuario, usado para login.';
COMMENT ON COLUMN users.password_hash IS 'Contraseña hasheada del usuario.';

-- Tabla de Partidas Arancelarias
CREATE TABLE IF NOT EXISTS tariff_codes (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL, 
    description TEXT NOT NULL,
    advalorem_rate NUMERIC(7, 6) DEFAULT 0.000000, -- Permite hasta 6 decimales, ej 0.123456 para 12.3456%
    ice_rate NUMERIC(7, 6) DEFAULT 0.000000,
    fodinfa_applies BOOLEAN DEFAULT TRUE,
    iva_rate NUMERIC(7, 6) DEFAULT 0.150000, -- Tasa de IVA base para esta partida (ej. 0.15 para 15%)
    specific_tax_value NUMERIC(10, 2) DEFAULT 0.00,
    specific_tax_unit VARCHAR(50), 
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_tariff_code_code ON tariff_codes(code);
-- Usar GIN o GIST index para búsquedas de texto completo si la tabla crece mucho y se usa ILIKE extensivamente.
-- Para ILIKE '%term%', un índice btree en description puede no ser tan efectivo como se espera.
-- Considerar `CREATE INDEX idx_tariff_code_description_gin ON tariff_codes USING gin (to_tsvector('spanish', description));` si se usa búsqueda full-text.
-- Por ahora, un índice btree estándar para búsquedas exactas o con prefijo.
CREATE INDEX IF NOT EXISTS idx_tariff_code_description ON tariff_codes(description text_pattern_ops);
COMMENT ON TABLE tariff_codes IS 'Almacena las partidas arancelarias y sus tasas impositivas asociadas.';
COMMENT ON COLUMN tariff_codes.code IS 'Código oficial de la partida arancelaria (ej. 8517.12.00.00).';
COMMENT ON COLUMN tariff_codes.advalorem_rate IS 'Tasa AdValorem como decimal (ej. 0.10 para 10%).';
COMMENT ON COLUMN tariff_codes.iva_rate IS 'Tasa de IVA como decimal (ej. 0.15 para 15%, 0.00 para 0%).';
COMMENT ON COLUMN tariff_codes.specific_tax_unit IS 'Unidad para el impuesto específico (ej. USD/Kg, USD/Unidad).';


-- Tabla de Cálculos Guardados
CREATE TABLE IF NOT EXISTS calculations (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    product_name VARCHAR(255) NOT NULL,
    
    tariff_code_id INTEGER REFERENCES tariff_codes(id) ON DELETE SET NULL, -- Si se borra una partida, el cálculo la pierde pero no se borra. Opcional: ON DELETE RESTRICT

    valor_fob_unitario NUMERIC(12, 2) NOT NULL,
    cantidad INTEGER DEFAULT 1,
    peso_unitario_kg NUMERIC(10, 3),

    costo_flete NUMERIC(12, 2), 
    costo_seguro NUMERIC(12, 2), 
    es_courier_4x4 BOOLEAN DEFAULT FALSE, 
    
    -- Resultados del cálculo de importación
    cif NUMERIC(14, 2), -- Aumentado para sumas
    ad_valorem NUMERIC(14, 2),
    fodinfa NUMERIC(14, 2),
    ice NUMERIC(14, 2),
    specific_tax NUMERIC(14,2), 
    iva NUMERIC(14, 2),
    total_impuestos NUMERIC(14, 2),
    costo_total_estimado_linea NUMERIC(16, 2), 

    -- Campos para ganancia y PVP
    profit_percentage_applied NUMERIC(5, 2), 
    cost_price_unit_after_import NUMERIC(14, 2), 
    profit_amount_unit NUMERIC(14, 2), 
    pvp_unit NUMERIC(14, 2), 
    pvp_total_line NUMERIC(16, 2), 
    
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
COMMENT ON TABLE calculations IS 'Almacena los cálculos de importación realizados y guardados por los usuarios.';
COMMENT ON COLUMN calculations.costo_flete IS 'Flete para esta línea/cálculo (ya sea directo o prorrateado).';
COMMENT ON COLUMN calculations.costo_seguro IS 'Seguro para esta línea/cálculo (ya sea directo o prorrateado).';
COMMENT ON COLUMN calculations.es_courier_4x4 IS 'Si este cálculo individual se consideró 4x4 en el momento del cálculo.';


-- Trigger para actualizar 'updated_at' automáticamente
CREATE OR REPLACE FUNCTION trigger_set_timestamp()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Aplicar trigger a tariff_codes si no existe
DO $$
BEGIN
   IF NOT EXISTS (
       SELECT 1
       FROM   pg_trigger
       WHERE  tgname = 'set_timestamp_tariff_codes' AND
              tgrelid = 'tariff_codes'::regclass
   ) THEN
      CREATE TRIGGER set_timestamp_tariff_codes
      BEFORE UPDATE ON tariff_codes
      FOR EACH ROW EXECUTE PROCEDURE trigger_set_timestamp();
   END IF;
END
$$;

-- Aplicar trigger a calculations si no existe
DO $$
BEGIN
   IF NOT EXISTS (
       SELECT 1
       FROM   pg_trigger
       WHERE  tgname = 'set_timestamp_calculations' AND
              tgrelid = 'calculations'::regclass
   ) THEN
      CREATE TRIGGER set_timestamp_calculations
      BEFORE UPDATE ON calculations
      FOR EACH ROW EXECUTE PROCEDURE trigger_set_timestamp();
   END IF;
END
$$;

-- Insertar algunas partidas de ejemplo (Ajustar iva_rate al valor actual de Ecuador, ej. 15% general)
-- Es importante que estos códigos y tasas sean precisos y se mantengan actualizados.
INSERT INTO tariff_codes (code, description, advalorem_rate, iva_rate, ice_rate, fodinfa_applies, specific_tax_value, specific_tax_unit) VALUES 
('8703.23.90.10', 'Vehículos Gasolina >1500cc <=2000cc', 0.35, 0.15, 0.15, TRUE, NULL, NULL)
ON CONFLICT (code) DO NOTHING;

INSERT INTO tariff_codes (code, description, advalorem_rate, iva_rate, ice_rate, fodinfa_applies) VALUES 
('8517.12.00.00', 'Teléfonos móviles (celulares) y los de otras redes inalámbricas', 0.00, 0.15, 0.00, TRUE)
ON CONFLICT (code) DO NOTHING;

INSERT INTO tariff_codes (code, description, advalorem_rate, iva_rate, ice_rate, fodinfa_applies) VALUES 
('8471.30.00.00', 'Máquinas automáticas para tratamiento o procesamiento de datos, portátiles (laptops, notebooks, etc.)', 0.00, 0.15, 0.00, TRUE)
ON CONFLICT (code) DO NOTHING;

INSERT INTO tariff_codes (code, description, advalorem_rate, iva_rate, ice_rate, fodinfa_applies, specific_tax_value, specific_tax_unit) VALUES 
('6203.42.90.00', 'Pantalones largos y pantalones con peto, de algodón, para hombres o niños (excepto de punto)', 0.10, 0.15, 0.00, TRUE, 5.50, 'USD/Kg') -- Ejemplo con impuesto específico
ON CONFLICT (code) DO NOTHING;

INSERT INTO tariff_codes (code, description, advalorem_rate, iva_rate, ice_rate, fodinfa_applies) VALUES 
('4901.99.90.00', 'Los demás libros, folletos e impresos similares, incluso en hojas sueltas', 0.00, 0.00, 0.00, TRUE) -- IVA 0% para libros
ON CONFLICT (code) DO NOTHING;

INSERT INTO tariff_codes (code, description, advalorem_rate, iva_rate, ice_rate, fodinfa_applies) VALUES 
('2208.30.00.00', 'Whisky', 0.20, 0.15, 0.75, TRUE) -- Ejemplo con ICE alto
ON CONFLICT (code) DO NOTHING;

SELECT 'Script de configuración de base de datos ejecutado.';
