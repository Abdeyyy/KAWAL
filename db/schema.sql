-- Create database if not exists
CREATE DATABASE IF NOT EXISTS kawal_db;
USE kawal_db;

-- Table for storing users who interact with the bot
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    whatsapp_number VARCHAR(20) UNIQUE NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for storing incoming texts and their AI analyses
CREATE TABLE IF NOT EXISTS wacana_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    text_input TEXT NOT NULL,
    ai_analysis TEXT NULL,
    status ENUM('proses', 'hoaks', 'fakta', 'meragukan') DEFAULT 'proses',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert a default user for testing/simulator purposes
INSERT IGNORE INTO users (whatsapp_number, role) VALUES ('6281234567890', 'user');
INSERT IGNORE INTO users (whatsapp_number, role) VALUES ('simulator_user', 'user');
