-- ============================================================
--  HaulPro — Database schema
--  Database name expected by db.php: webtech_project
--
--  Usage:
--    mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS webtech_project CHARACTER SET utf8mb4;"
--    mysql -u root -p webtech_project < schema.sql
--
--  Notes:
--   * The app auto-creates some of these tables on first visit
--     (users, customers, invoices, invoice_items, payments,
--      payment_methods, payment_prefs). They are included here so a
--      single import sets up everything.
--   * lorry_owners, drivers, trucks and trips are NOT auto-created by
--     the PHP code, so importing this file is required for the
--     fleet / trips / analytics pages to work.
-- ============================================================

CREATE TABLE IF NOT EXISTS users (
  id            BIGINT PRIMARY KEY AUTO_INCREMENT,
  email         VARCHAR(190) NOT NULL UNIQUE,
  full_name     VARCHAR(140) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customers (
  id         BIGINT PRIMARY KEY AUTO_INCREMENT,
  name       VARCHAR(140) NOT NULL,
  contact    VARCHAR(80),
  phone      VARCHAR(30),
  address    VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS invoices (
  id           BIGINT PRIMARY KEY AUTO_INCREMENT,
  customer_id  BIGINT NOT NULL,
  invoice_no   VARCHAR(40) NOT NULL,
  invoice_date DATE NOT NULL,
  due_date     DATE NULL,
  status       ENUM('Open','Paid','Cancelled') DEFAULT 'Open',
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS invoice_items (
  id          BIGINT PRIMARY KEY AUTO_INCREMENT,
  invoice_id  BIGINT NOT NULL,
  description VARCHAR(255) NOT NULL,
  qty         DECIMAL(10,2) DEFAULT 1,
  amount_bdt  DECIMAL(12,2) NOT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payments (
  id         BIGINT PRIMARY KEY AUTO_INCREMENT,
  invoice_id BIGINT NOT NULL,
  paid_date  DATE NOT NULL,
  method     VARCHAR(120),
  amount_bdt DECIMAL(12,2) NOT NULL,
  reference  VARCHAR(80),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payment_methods (
  id          BIGINT PRIMARY KEY AUTO_INCREMENT,
  customer_id BIGINT NOT NULL,
  type        ENUM('card','wallet','bank') NOT NULL,
  label       VARCHAR(120),
  provider    VARCHAR(80),
  brand       VARCHAR(40),
  exp         VARCHAR(7),
  bank        VARCHAR(120),
  is_default  TINYINT(1) DEFAULT 0,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payment_prefs (
  customer_id BIGINT PRIMARY KEY,
  currency    VARCHAR(8) DEFAULT 'BDT',
  auto        ENUM('No','Yes') DEFAULT 'No',
  email       VARCHAR(160) DEFAULT NULL,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --- Fleet / operations (required: not auto-created by the app) ---

CREATE TABLE IF NOT EXISTS lorry_owners (
  id         BIGINT PRIMARY KEY AUTO_INCREMENT,
  vehicle_no VARCHAR(40),
  owner_type VARCHAR(40) DEFAULT 'Company',
  owner_name VARCHAR(120),
  truck_type VARCHAR(60),
  status     VARCHAR(30) DEFAULT 'Active',
  driver_id  BIGINT NULL,
  contact    VARCHAR(60),
  address    VARCHAR(255),
  capacity   VARCHAR(40),
  notes      TEXT,
  user_id    BIGINT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS drivers (
  id         BIGINT PRIMARY KEY AUTO_INCREMENT,
  name       VARCHAR(120),
  phone      VARCHAR(40),
  license_no VARCHAR(60),
  status     VARCHAR(30) DEFAULT 'Active',
  user_id    BIGINT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS trucks (
  id         BIGINT PRIMARY KEY AUTO_INCREMENT,
  vehicle_no VARCHAR(40),
  truck_type VARCHAR(60),
  status     VARCHAR(30) DEFAULT 'Active',
  user_id    BIGINT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS trips (
  id          BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id     BIGINT NULL,
  truck_id    BIGINT NULL,
  driver_id   BIGINT NULL,
  trip_status VARCHAR(30) DEFAULT 'Pending',  -- Pending / Accepted / Pickup / Completed / Cancelled
  trip_date   DATE NULL,
  amount      DECIMAL(12,2) DEFAULT 0,
  origin      VARCHAR(120),
  destination VARCHAR(120),
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
