-- SEEDS INICIALES
-- Insertar datos básicos para pruebas

-- 1. Crear un Tenant (Gimnasio Demo)
INSERT INTO tenants (id, name, domain, status) VALUES (1, 'Gimnasio Demo', 'demo.gym.com', 'active');

-- 2. Crear un Usuario Admin para ese Tenant
-- Password: password123 (hash MD5 o BCRYPT según implementación, aquí placeholder)
INSERT INTO users (gym_id, name, email, password_hash, role)
VALUES (1, 'Admin Demo', 'admin@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gym_admin');

-- 3. Plan de Cuentas Básico (PUC Colombia resumido)
-- Activos
INSERT INTO gl_accounts (gym_id, code, name, type) VALUES
(1, '1105', 'Caja', 'asset'),
(1, '1110', 'Bancos', 'asset'),
(1, '1305', 'Clientes (CxC)', 'asset');

-- Pasivos
INSERT INTO gl_accounts (gym_id, code, name, type) VALUES
(1, '2335', 'Costos y Gastos por Pagar', 'liability'),
(1, '2408', 'Impuesto sobre las Ventas por Pagar (IVA)', 'liability');

-- Ingresos
INSERT INTO gl_accounts (gym_id, code, name, type) VALUES
(1, '4135', 'Ingresos por Servicios de Gimnasio', 'revenue');

-- Gastos
INSERT INTO gl_accounts (gym_id, code, name, type) VALUES
(1, '5105', 'Gastos de Personal', 'expense'),
(1, '5135', 'Servicios', 'expense');

-- 4. Secuencias de Documentos Iniciales
INSERT INTO document_sequences (gym_id, doc_type, prefix, current_value) VALUES
(1, 'RC', 'MAIN', 0),
(1, 'FV', 'MAIN', 0),
(1, 'CE', 'MAIN', 0);

-- 5. Tercero de Prueba (Cliente)
INSERT INTO third_parties (id, gym_id, doc_type, doc_number, name, email, phone, type) VALUES
(1, 1, 'CC', '10101010', 'Juan Perez', 'juan@test.com', '3001234567', 'customer');

-- 6. Miembro Activo
INSERT INTO members (id, gym_id, third_party_id, code, status, membership_expiry) VALUES
(1, 1, 1, '10101010', 'active', DATE_ADD(CURDATE(), INTERVAL 30 DAY));

-- 7. Token de Acceso de Prueba (UUID: 550e8400-e29b-41d4-a716-446655440000)
INSERT INTO access_tokens (gym_id, member_id, token_uuid, status, expires_at) VALUES
(1, 1, '550e8400-e29b-41d4-a716-446655440000', 'valid', DATE_ADD(NOW(), INTERVAL 24 HOUR));
