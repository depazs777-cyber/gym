-- ESQUEMA DE BASE DE DATOS FINAL - SaaS Gimnasios (Multi-tenant)
-- Motor: MySQL / MariaDB

SET FOREIGN_KEY_CHECKS=0;

-- 1. Tenants (Gimnasios)
CREATE TABLE IF NOT EXISTS tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    domain VARCHAR(255),
    logo_url VARCHAR(255),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    plan_id INT, -- Referencia a plan SaaS (opcional)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Usuarios y Roles (RBAC)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NULL, -- NULL para Super Admin
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'gym_admin', 'trainer', 'receptionist', 'accountant') NOT NULL DEFAULT 'gym_admin',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Terceros (Clientes, Proveedores, Empleados)
CREATE TABLE IF NOT EXISTS third_parties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    doc_type ENUM('CC', 'NIT', 'CE', 'TI', 'PAS') DEFAULT 'CC',
    doc_number VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    address VARCHAR(255),
    city VARCHAR(100),
    type ENUM('customer', 'supplier', 'employee', 'other') DEFAULT 'customer',
    vat_responsible BOOLEAN DEFAULT FALSE, -- Responsable de IVA
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_doc (gym_id, doc_type, doc_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Afiliados / Miembros (Extensión de Third Parties para lógica de Gym)
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    third_party_id INT NOT NULL, -- Vinculación contable
    code VARCHAR(50), -- Código interno o de carnet
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    membership_expiry DATE,
    photo_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (third_party_id) REFERENCES third_parties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Control de Acceso (QR Seguro)
CREATE TABLE IF NOT EXISTS access_tokens (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    member_id INT NOT NULL,
    token_uuid VARCHAR(36) NOT NULL UNIQUE, -- UUIDv4
    status ENUM('valid', 'used', 'expired', 'revoked') DEFAULT 'valid',
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    device_id VARCHAR(100), -- ID del dispositivo que escaneó
    FOREIGN KEY (gym_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS access_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    member_id INT NOT NULL,
    direction ENUM('in', 'out') DEFAULT 'in',
    access_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('granted', 'denied') DEFAULT 'granted',
    denial_reason VARCHAR(255),
    FOREIGN KEY (gym_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Contabilidad - Plan Único de Cuentas (PUC)
CREATE TABLE IF NOT EXISTS gl_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    code VARCHAR(20) NOT NULL, -- Ej: 110505
    name VARCHAR(255) NOT NULL,
    type ENUM('asset', 'liability', 'equity', 'revenue', 'expense', 'cost') NOT NULL,
    is_auxiliary BOOLEAN DEFAULT TRUE, -- Si recibe movimientos
    parent_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_code (gym_id, code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Contabilidad - Encabezado de Asiento (Journal Entry)
CREATE TABLE IF NOT EXISTS journal_entries (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    entry_date DATE NOT NULL,
    description TEXT,
    reference VARCHAR(100), -- Nro Factura, RC, etc.
    source_module VARCHAR(50), -- 'billing', 'payroll', 'manual'
    source_id INT, -- ID en la tabla origen
    status ENUM('draft', 'posted', 'voided') DEFAULT 'draft',
    created_by INT,
    posted_at TIMESTAMP NULL,
    voided_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Contabilidad - Líneas de Asiento
CREATE TABLE IF NOT EXISTS journal_lines (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    entry_id BIGINT NOT NULL,
    account_id INT NOT NULL,
    third_party_id INT NULL, -- Obligatorio para cuentas de balance e impuestos
    description VARCHAR(255),
    debit DECIMAL(15, 2) DEFAULT 0.00,
    credit DECIMAL(15, 2) DEFAULT 0.00,
    FOREIGN KEY (entry_id) REFERENCES journal_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES gl_accounts(id),
    FOREIGN KEY (third_party_id) REFERENCES third_parties(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Documentos Contables (RC, CE, FV, NC)
CREATE TABLE IF NOT EXISTS accounting_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    doc_type ENUM('RC', 'CE', 'FV', 'NC', 'ND') NOT NULL, -- Recibo Caja, Compra Egreso, Factura Venta...
    prefix VARCHAR(10) DEFAULT '',
    consecutive INT NOT NULL,
    date DATE NOT NULL,
    third_party_id INT NOT NULL,
    subtotal DECIMAL(15, 2) DEFAULT 0,
    tax_total DECIMAL(15, 2) DEFAULT 0,
    total DECIMAL(15, 2) DEFAULT 0,
    status ENUM('draft', 'posted', 'voided') DEFAULT 'draft',
    notes TEXT,
    journal_entry_id BIGINT NULL, -- Enlace al asiento
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (third_party_id) REFERENCES third_parties(id),
    UNIQUE KEY unique_doc (gym_id, doc_type, prefix, consecutive)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Secuencias de Documentos
CREATE TABLE IF NOT EXISTS document_sequences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    doc_type ENUM('RC', 'CE', 'FV', 'NC', 'ND') NOT NULL,
    prefix VARCHAR(10) DEFAULT '',
    current_value INT DEFAULT 0,
    FOREIGN KEY (gym_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_seq (gym_id, doc_type, prefix)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
