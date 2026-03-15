-- Users Table (Global and Tenant users)
-- gym_id IS NULL for Global Staff (Super Admin, Sales, etc.)
-- gym_id IS NOT NULL for Gym Staff
CREATE TABLE gyms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    status ENUM('active', 'expiring', 'expired', 'suspended') DEFAULT 'active',
    license_start DATE NOT NULL,
    license_end DATE NOT NULL,
    branding_logo VARCHAR(255) DEFAULT NULL,
    branding_color VARCHAR(50) DEFAULT '#000000',
    contact_info TEXT DEFAULT NULL COMMENT 'JSON: nit, address, phone, email, message',

    -- Registration & Subscription
    registered_at DATETIME DEFAULT NULL,
    subscription_status VARCHAR(50) DEFAULT 'ACTIVE',
    activated_at DATETIME DEFAULT NULL,

    saas_plan_id INT DEFAULT NULL, -- Linked Plan ID
    subscription_period_months_snapshot INT DEFAULT 1,
    subscription_plan_code VARCHAR(20) DEFAULT NULL, -- Legacy/Snapshot
    subscription_price_snapshot DECIMAL(10, 2) DEFAULT 0.00,

    -- Config
    config_annual_days INT DEFAULT 360,
    config_deduct_session TINYINT(1) DEFAULT 1,
    config_renewal_mode ENUM('CONTINUE', 'TODAY') DEFAULT 'CONTINUE',
    config_warning_days INT DEFAULT 3,
    first_payment_id INT DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (saas_plan_id) REFERENCES saas_plans(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM(
        'SUPER_ADMIN', 'VENDEDOR', 'MARKETING', 'CALL_CENTER', 'FINANZAS',
        'SOPORTE', 'DEV', 'SEGURIDAD', -- Global
        'ADMIN_GYM', 'RECEPCION', 'ENTRENADOR', 'CONSULTA_REPORTES' -- Gym
    ) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Plans (Gym specific)
CREATE TABLE plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('TIME', 'SESSIONS') NOT NULL,
    duration_days INT DEFAULT 0,
    sessions_count INT DEFAULT 0,
    price DECIMAL(10, 2) NOT NULL,
    allow_freeze TINYINT(1) DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Clients (Gym specific)
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    identification VARCHAR(50) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    status ENUM('active', 'inactive', 'blocked', 'debtor') DEFAULT 'active',
    access_pin VARCHAR(20),
    qr_code VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_client_per_gym (gym_id, identification)
) ENGINE=InnoDB;

-- Memberships (Subscriptions of clients to plans)
CREATE TABLE memberships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    client_id INT NOT NULL,
    plan_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE DEFAULT NULL,
    sessions_total INT DEFAULT 0,
    sessions_used INT DEFAULT 0,
    status ENUM('active', 'expired', 'cancelled', 'frozen') DEFAULT 'active',
    price_at_purchase DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Payments
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    client_id INT NOT NULL,
    membership_id INT DEFAULT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    discount DECIMAL(10, 2) DEFAULT 0.00,
    notes TEXT DEFAULT NULL,
    payment_method VARCHAR(50) NOT NULL, -- cash, card, transfer
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by_user_id INT NOT NULL,
    consecutive_number INT NOT NULL, -- Gym specific consecutive
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (membership_id) REFERENCES memberships(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Receipts (Snapshot)
CREATE TABLE receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    payment_id INT NOT NULL,
    receipt_number VARCHAR(50) NOT NULL,
    snapshot_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Attendance / Access Logs
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    client_id INT NOT NULL,
    access_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    method ENUM('QR', 'PIN', 'MANUAL') NOT NULL,
    access_granted TINYINT(1) NOT NULL, -- 1=Yes, 0=No
    rejection_reason VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Client Tokens (QR)
CREATE TABLE client_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    client_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    status ENUM('active', 'revoked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    UNIQUE KEY unique_token (token)
) ENGINE=InnoDB;

-- === SAAS MODULES ===

-- SaaS Plans
CREATE TABLE saas_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    code VARCHAR(50) UNIQUE DEFAULT NULL,
    period_months INT NOT NULL DEFAULT 1,
    current_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    currency VARCHAR(10) DEFAULT 'COP',
    is_active TINYINT(1) DEFAULT 1,
    is_archived TINYINT(1) DEFAULT 0,
    merged_into_plan_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (merged_into_plan_id) REFERENCES saas_plans(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO saas_plans (name, code, period_months, current_price, currency) VALUES
('Mensual', 'MENSUAL', 1, 50000.00, 'COP'),
('Anual', 'ANUAL', 12, 500000.00, 'COP');

-- SaaS Plan Price Changes
CREATE TABLE saas_plan_price_changes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    saas_plan_id INT NOT NULL,
    old_price DECIMAL(10, 2) NOT NULL,
    new_price DECIMAL(10, 2) NOT NULL,
    effective_date DATE NOT NULL,
    notify_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'SCHEDULED', -- SCHEDULED, APPLIED, CANCELLED
    created_by_user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (saas_plan_id) REFERENCES saas_plans(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- SaaS Payments
CREATE TABLE saas_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    period_type VARCHAR(20) NOT NULL,
    period_months INT DEFAULT 12,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'COP',
    method VARCHAR(20) NOT NULL,
    reference VARCHAR(255) NULL,
    notes TEXT NULL,
    created_by_user_id INT NOT NULL,

    -- Sales Data
    saas_plan_id INT DEFAULT NULL,
    plan_code VARCHAR(20) DEFAULT NULL,
    plan_name_snapshot VARCHAR(255) DEFAULT NULL,
    years_paid INT DEFAULT NULL,
    unit_price DECIMAL(10, 2) DEFAULT 0.00,
    discount_type VARCHAR(20) DEFAULT NULL,
    discount_value DECIMAL(10, 2) DEFAULT 0.00,
    discount_reason TEXT DEFAULT NULL,
    discount_approved_by INT DEFAULT NULL,
    amount_total DECIMAL(10, 2) DEFAULT 0.00,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- SaaS License Renewals
CREATE TABLE saas_license_renewals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    old_end_date DATE,
    new_end_date DATE,
    renewed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    renewed_by_user_id INT NOT NULL,
    payment_id INT NULL,
    notes TEXT NULL,
    FOREIGN KEY (payment_id) REFERENCES saas_payments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Call Center: Leads
CREATE TABLE leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255),
    customer_type VARCHAR(50) DEFAULT 'SMALL_GYM',
    status VARCHAR(50) DEFAULT 'NEW',
    next_followup DATETIME NULL,
    notes TEXT,

    city VARCHAR(100),
    gym_name VARCHAR(255),
    owner_name VARCHAR(255),
    last_call_at DATETIME,
    assigned_to_user_id INT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Call Center: Scripts
CREATE TABLE call_scripts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    customer_type VARCHAR(50) NOT NULL,
    objective VARCHAR(100) NOT NULL,
    script_body TEXT NOT NULL,
    objections_json TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_by_user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Call Center: Logs
CREATE TABLE call_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    user_id INT NOT NULL,
    call_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    call_end TIMESTAMP NULL,
    duration_seconds INT DEFAULT 0,
    outcome VARCHAR(50),
    notes TEXT,
    script_id INT NULL,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (script_id) REFERENCES call_scripts(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Motivation
CREATE TABLE motivation_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    image_url VARCHAR(255),
    quote_text TEXT,
    show_date DATE NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- SaaS Settings
CREATE TABLE saas_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO saas_settings (setting_key, setting_value) VALUES
('call_center_start_time', '08:00'),
('call_center_end_time', '18:00');

-- Notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT DEFAULT NULL, -- NULL for global/SaaS
    user_id INT DEFAULT NULL, -- NULL for all
    title VARCHAR(255) NULL,
    message TEXT NOT NULL,
    target_role VARCHAR(50) DEFAULT NULL,
    type VARCHAR(50) DEFAULT 'INFO',
    is_read TINYINT(1) DEFAULT 0, -- Simple flag if user_id set
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Notification Reads (for global/role based notifs)
CREATE TABLE notification_reads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insert Default Super Admin
-- Password: admin (hash needs to be generated properly in PHP, placeholder here)
-- Password: admin
INSERT INTO users (name, email, password_hash, role, status) VALUES
('Super Admin', 'admin@promptmaestro.com', '$2y$10$F932Ku3FQKMbU4SdbUdAxO6kd1nWcqXxSUjQwG9h2PW3xHLZW2bzq', 'SUPER_ADMIN', 'active');
