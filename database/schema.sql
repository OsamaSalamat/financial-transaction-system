CREATE DATABASE IF NOT EXISTS ledgerflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ledgerflow;

CREATE TABLE users (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL,email VARCHAR(190) NOT NULL UNIQUE,password VARCHAR(255) NOT NULL,role ENUM('admin','accountant','viewer') NOT NULL DEFAULT 'viewer',status ENUM('active','disabled') NOT NULL DEFAULT 'active',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE customers (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(160) NOT NULL,email VARCHAR(190),phone VARCHAR(50),status ENUM('active','inactive') NOT NULL DEFAULT 'active',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE funding_sources (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(160) NOT NULL,type VARCHAR(80) NOT NULL,status ENUM('active','inactive') NOT NULL DEFAULT 'active',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE accounts (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,account_code VARCHAR(30) NOT NULL UNIQUE,name VARCHAR(160) NOT NULL,account_type ENUM('asset','liability','equity','revenue','expense') NOT NULL,status ENUM('active','inactive') NOT NULL DEFAULT 'active',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE transactions (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,transaction_reference VARCHAR(80) NOT NULL UNIQUE,customer_id BIGINT UNSIGNED NULL,funding_source_id BIGINT UNSIGNED NULL,transaction_type VARCHAR(60) NOT NULL,amount DECIMAL(20,4) NOT NULL,currency CHAR(3) NOT NULL DEFAULT 'USD',status ENUM('pending','posted','reversed','failed') NOT NULL DEFAULT 'pending',description VARCHAR(500),idempotency_key VARCHAR(190) NOT NULL UNIQUE,created_by BIGINT UNSIGNED NULL,reversal_of BIGINT UNSIGNED NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX idx_tx_created(created_at),INDEX idx_tx_customer(customer_id),INDEX idx_tx_type(transaction_type),CONSTRAINT fk_tx_customer FOREIGN KEY(customer_id) REFERENCES customers(id),CONSTRAINT fk_tx_source FOREIGN KEY(funding_source_id) REFERENCES funding_sources(id),CONSTRAINT fk_tx_user FOREIGN KEY(created_by) REFERENCES users(id),CONSTRAINT fk_tx_reversal FOREIGN KEY(reversal_of) REFERENCES transactions(id)) ENGINE=InnoDB;
CREATE TABLE journal_entries (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,transaction_id BIGINT UNSIGNED NOT NULL,account_id BIGINT UNSIGNED NOT NULL,debit DECIMAL(20,4) NOT NULL DEFAULT 0,credit DECIMAL(20,4) NOT NULL DEFAULT 0,description VARCHAR(500),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX idx_je_tx(transaction_id),CONSTRAINT fk_je_tx FOREIGN KEY(transaction_id) REFERENCES transactions(id),CONSTRAINT fk_je_account FOREIGN KEY(account_id) REFERENCES accounts(id)) ENGINE=InnoDB;

CREATE TABLE api_tokens (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 token_hash CHAR(64) NOT NULL UNIQUE,
 expires_at DATETIME NOT NULL,
 last_used_at DATETIME NULL,
 revoked_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_api_user(user_id),
 CONSTRAINT fk_api_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE audit_logs (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED NULL,action VARCHAR(80) NOT NULL,entity_type VARCHAR(80) NOT NULL,entity_id BIGINT UNSIGNED NULL,old_values JSON NULL,new_values JSON NULL,ip_address VARCHAR(45),user_agent VARCHAR(500),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX idx_audit_entity(entity_type,entity_id),CONSTRAINT fk_audit_user FOREIGN KEY(user_id) REFERENCES users(id)) ENGINE=InnoDB;

INSERT INTO users (name,email,password,role) VALUES ('System Administrator','admin@example.com','$2y$12$L5975K08E23i7GLgiA2eMOIqwbPVKelJh0r.WQCeZpM9qPlaDzvA2','admin');
-- Demo password: password (change immediately after first login)
INSERT INTO accounts (account_code,name,account_type) VALUES ('1000','Cash / Bank','asset'),('1100','Accounts Receivable','asset'),('4000','Revenue','revenue'),('5000','Operating Expense','expense');
INSERT INTO customers (name,email,phone) VALUES ('Demo Customer','customer@example.com','+00 000 000000');
INSERT INTO funding_sources (name,type) VALUES ('Bank Transfer','bank'),('Card Processor','card'),('Cash','cash');
