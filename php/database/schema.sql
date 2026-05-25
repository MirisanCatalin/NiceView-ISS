-- MySQL Database Schema for NiceView
-- Run this script to create the database

CREATE DATABASE IF NOT EXISTS niceview_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE niceview_db;

-- Create dedicated user for security
CREATE USER IF NOT EXISTS 'niceview_user'@'localhost' IDENTIFIED BY 'secure_password_123';
GRANT SELECT, INSERT, UPDATE, DELETE ON niceview_db.* TO 'niceview_user'@'localhost';
FLUSH PRIVILEGES;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role ENUM('utilizator', 'administrator') DEFAULT 'utilizator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
);

-- Perspectives table
CREATE TABLE IF NOT EXISTS privelisti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titlu VARCHAR(100) NOT NULL,
    descriere TEXT,
    judet VARCHAR(50),
    localitate VARCHAR(50),
    tip ENUM('munte', 'mare', 'lac', 'oras') DEFAULT 'munte',
    altitudine INT,
    lat DECIMAL(10, 8),
    lng DECIMAL(11, 8),
    website VARCHAR(255),
    status ENUM('aprobat', 'in_asteptare', 'respins') DEFAULT 'in_asteptare',
    user_id INT,
    imagine VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Remember tokens for persistent login
CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
);

-- File uploads table
CREATE TABLE IF NOT EXISTS uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default users (password is 'password')
INSERT IGNORE INTO users (username, password, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@niceview.ro', 'administrator'),
('user1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user1@niceview.ro', 'utilizator'),
('user2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user2@niceview.ro', 'utilizator'),
('user3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user3@niceview.ro', 'utilizator'),
('user4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user4@niceview.ro', 'utilizator'),
('user5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user5@niceview.ro', 'utilizator'),
('user6', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user6@niceview.ro', 'utilizator'),
('user7', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user7@niceview.ro', 'utilizator'),
('user8', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user8@niceview.ro', 'utilizator'),
('user9', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user9@niceview.ro', 'utilizator'),
('user10', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user10@niceview.ro', 'utilizator'),
('user11', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user11@niceview.ro', 'utilizator'),
('user12', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user12@niceview.ro', 'utilizator'),
('user13', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user13@niceview.ro', 'utilizator'),
('user14', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user14@niceview.ro', 'utilizator'),
('user15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user15@niceview.ro', 'utilizator'),
('user16', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user16@niceview.ro', 'utilizator'),
('user17', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user17@niceview.ro', 'utilizator'),
('user18', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user18@niceview.ro', 'utilizator'),
('user19', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user19@niceview.ro', 'utilizator'),
('user20', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user20@niceview.ro', 'utilizator');

-- Insert sample data
INSERT IGNORE INTO privelisti (titlu, descriere, judet, localitate, tip, altitudine, lat, lng, user_id, status) VALUES
('Muntele Negri', 'Un loc minunat pentru drumetii', 'BV', 'Zărnești', 'munte', 1200, 45.5600, 25.3200, 1, 'aprobat'),
('Lacul Roșu', 'Cel mai frumos lac din Harghita', 'HR', 'Gheorghe Doja', 'lac', 983, 46.7800, 25.7800, 2, 'aprobat'),
('Marea Neagră', 'Litoralul Romaniei', 'CT', 'Constanța', 'mare', 0, 44.1700, 28.6500, 1, 'aprobat');