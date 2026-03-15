-- Inserción de Roles Básicos
INSERT INTO `roles` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Super Admin', 'Administrador del sistema SaaS'),
(2, 'Admin Gimnasio', 'Administrador de un tenant/gimnasio específico'),
(3, 'Recepcionista', 'Personal de recepción del gimnasio'),
(4, 'Entrenador', 'Personal de entrenamiento del gimnasio');

-- Inserción de Planes de Suscripción por defecto
INSERT INTO `plans` (`id`, `nombre`, `precio`, `max_miembros`, `descripcion`, `caracteristicas_json`, `estado`) VALUES
(1, 'Plan Básico', 50.00, 100, 'Ideal para gimnasios pequeños', '{"modulos": ["miembros", "accesos", "pos_basico"], "soporte": "email"}', 'activo'),
(2, 'Plan Pro', 100.00, 300, 'Para gimnasios en crecimiento', '{"modulos": ["miembros", "accesos", "pos_avanzado", "reportes"], "soporte": "prioritario"}', 'activo'),
(3, 'Plan Premium', 200.00, 999999, 'Ilimitado con todas las funciones', '{"modulos": ["miembros", "accesos", "pos_avanzado", "reportes", "contabilidad", "crm"], "soporte": "24_7"}', 'activo');

-- Inserción de Cuentas Contables (PUC Colombia)
INSERT INTO `accounting_accounts` (`codigo`, `nombre`, `tipo`, `naturaleza`) VALUES
('1105', 'Caja', 'activo', 'debito'),
('1110', 'Bancos', 'activo', 'debito'),
('1305', 'Clientes', 'activo', 'debito'),
('1435', 'Inventarios', 'activo', 'debito'),
('2205', 'Proveedores', 'pasivo', 'credito'),
('2335', 'Costos y gastos por pagar', 'pasivo', 'credito'),
('2408', 'Impuestos sobre las ventas por pagar', 'pasivo', 'credito'),
('3105', 'Capital', 'patrimonio', 'credito'),
('4135', 'Ingresos por servicios', 'ingreso', 'credito'),
('4140', 'Ingresos por ventas', 'ingreso', 'credito'),
('5105', 'Gastos de personal', 'gasto', 'debito'),
('5130', 'Seguros', 'gasto', 'debito'),
('5135', 'Servicios', 'gasto', 'debito'),
('5145', 'Mantenimiento y reparaciones', 'gasto', 'debito'),
('5205', 'Gastos generales', 'gasto', 'debito'),
('6135', 'Costo de ventas', 'costo', 'debito');
