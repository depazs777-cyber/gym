-- ESQUEMA DE BASE DE DATOS FINAL - SaaS Gimnasios (Multi-tenant)
-- Motor: MySQL / MariaDB

SET FOREIGN_KEY_CHECKS=0;

-- ==========================================
-- SECCIÓN SUPER ADMIN / SaaS MANAGEMENT
-- ==========================================

-- 1. SaaS Planes
CREATE TABLE IF NOT EXISTS saas_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(15, 2) NOT NULL,
    max_members INT DEFAULT 500,
    status ENUM('active', 'inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tenants (Gimnasios / Clientes del SaaS)
CREATE TABLE IF NOT EXISTS tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    domain VARCHAR(255),
    logo_url VARCHAR(255),
    status ENUM('active', 'inactive', 'suspended', 'cancelled') DEFAULT 'active',
    plan_id INT, -- Referencia a saas_plans
    valid_until DATE, -- Fecha de vencimiento del pago del SaaS
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES saas_plans(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. CRM Leads (Prospectos para el equipo de ventas del SaaS)
CREATE TABLE IF NOT EXISTS leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    contact_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    status ENUM('new', 'contacted', 'demo_scheduled', 'won', 'lost') DEFAULT 'new',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Facturación del SaaS (Pagos de los Gimnasios al Creador del Software)
CREATE TABLE IF NOT EXISTS saas_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('paid', 'pending', 'overdue') DEFAULT 'pending',
    payment_method VARCHAR(50),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- SECCIÓN TENANT (GIMNASIO INDIVIDUAL)
-- ==========================================

-- 5. Usuarios y Roles (RBAC) - Mezcla Super Admins (gym_id=null) y Gym Admins
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NULL, -- NULL significa que es un empleado de la empresa creadora del software (Super Admin, Vendedor)
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'sales', 'gym_admin', 'trainer', 'receptionist', 'accountant') NOT NULL DEFAULT 'gym_admin',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Terceros (Clientes, Proveedores, Empleados del Gym)
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
    vat_responsible BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_doc (gym_id, doc_type, doc_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Afiliados / Miembros (Extensión de Third Parties para lógica de Gym)
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    third_party_id INT NOT NULL,
    code VARCHAR(50),
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    membership_expiry DATE,
    photo_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (third_party_id) REFERENCES third_parties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Control de Acceso (QR Seguro)
CREATE TABLE IF NOT EXISTS access_tokens (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    member_id INT NOT NULL,
    token_uuid VARCHAR(36) NOT NULL UNIQUE,
    status ENUM('valid', 'used', 'expired', 'revoked') DEFAULT 'valid',
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    device_id VARCHAR(100),
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

-- 9. Contabilidad - Plan Único de Cuentas (PUC)
CREATE TABLE IF NOT EXISTS gl_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('asset', 'liability', 'equity', 'revenue', 'expense', 'cost') NOT NULL,
    is_auxiliary BOOLEAN DEFAULT TRUE,
    parent_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_code (gym_id, code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Contabilidad - Encabezado de Asiento (Journal Entry)
CREATE TABLE IF NOT EXISTS journal_entries (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    entry_date DATE NOT NULL,
    description TEXT,
    reference VARCHAR(100),
    source_module VARCHAR(50),
    source_id INT,
    status ENUM('draft', 'posted', 'voided') DEFAULT 'draft',
    created_by INT,
    posted_at TIMESTAMP NULL,
    voided_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Contabilidad - Líneas de Asiento
CREATE TABLE IF NOT EXISTS journal_lines (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    entry_id BIGINT NOT NULL,
    account_id INT NOT NULL,
    third_party_id INT NULL,
    description VARCHAR(255),
    debit DECIMAL(15, 2) DEFAULT 0.00,
    credit DECIMAL(15, 2) DEFAULT 0.00,
    FOREIGN KEY (entry_id) REFERENCES journal_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES gl_accounts(id),
    FOREIGN KEY (third_party_id) REFERENCES third_parties(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Documentos Contables (RC, CE, FV, NC)
CREATE TABLE IF NOT EXISTS accounting_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    doc_type ENUM('RC', 'CE', 'FV', 'NC', 'ND') NOT NULL,
    prefix VARCHAR(10) DEFAULT '',
    consecutive INT NOT NULL,
    date DATE NOT NULL,
    third_party_id INT NOT NULL,
    subtotal DECIMAL(15, 2) DEFAULT 0,
    tax_total DECIMAL(15, 2) DEFAULT 0,
    total DECIMAL(15, 2) DEFAULT 0,
    status ENUM('draft', 'posted', 'voided') DEFAULT 'draft',
    notes TEXT,
    journal_entry_id BIGINT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (third_party_id) REFERENCES third_parties(id),
    UNIQUE KEY unique_doc (gym_id, doc_type, prefix, consecutive)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Secuencias de Documentos
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
