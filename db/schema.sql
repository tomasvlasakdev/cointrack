-- ===============================================
--  CoinTrack Database Schema
--  Author: Tomáš Vlasák
--  Description: Core tables for CoinTrack crypto portfolio tracker
-- ===============================================

-- USERS TABLE
CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    email VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    password_hash VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- TRANSACTIONS TABLE
CREATE TABLE IF NOT EXISTS transactions (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    symbol VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    amount DECIMAL(18,8) NOT NULL,
    price DECIMAL(18,2) NOT NULL,
    type ENUM('BUY', 'SELL') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX (user_id),
    CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
