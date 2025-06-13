<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'starbillet');

// Función para conectar a la base de datos
function getDBConnection()
{
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}


/*
CREATE DATABASE IF NOT EXISTS starbillet;
USE starbillet;

-- Tabla de usuarios con campo 'role'
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user', -- 'user' o 'admin'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de eventos
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    time TIME NOT NULL,
    venue VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    available_tickets INT NOT NULL DEFAULT 100,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de tickets
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    total_price DECIMAL(10,2) NOT NULL,
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ticket_code VARCHAR(50) UNIQUE NOT NULL,
    status ENUM('active', 'used', 'cancelled') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- Insertar eventos de ejemplo
INSERT IGNORE INTO events (name, description, date, time, venue, price, available_tickets) VALUES
('Concierto de Rock', 'Una noche épica de rock con las mejores bandas locales', '2025-10-15', '20:00:00', 'Auditorio Nacional', 50000.00, 500),
('Festival de Jazz', 'Disfruta del mejor jazz en vivo en un ambiente único', '2025-11-22', '18:00:00', 'Parque Central', 35000.00, 300),
('Obra de Teatro', 'Una producción teatral que no te puedes perder', '2025-12-05', '19:30:00', 'Teatro de la Ciudad', 25000.00, 150);

-- Insertar usuario administrador (la contraseña debe ser hasheada en producción)
INSERT IGNORE INTO users (name, email, password, role) 
VALUES ('Administrador', 'admin@starbillet.com', '$2y$10$8e9Uo1avSYsJtEBboETIKe0c7xfP8krA6PbkhVXaUgG4PbHxlFZ7G', 'admin');
*/


?>