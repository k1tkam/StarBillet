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

// Script SQL para crear las tablas necesarias
/*
CREATE DATABASE IF NOT EXISTS starbillet;
USE starbillet;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,                      
    email VARCHAR(100) UNIQUE NOT NULL,              
    password VARCHAR(100) NOT NULL,                  
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de organizadores (quienes crean eventos)
CREATE TABLE IF NOT EXISTS organizers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,                     
    email VARCHAR(100) UNIQUE NOT NULL,              
    password VARCHAR(100) NOT NULL,                 
    phone VARCHAR(20),                               
    company_name VARCHAR(150), 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de eventos (creados por los organizadores)
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organizer_id INT NOT NULL,                        Organizador que publica el evento
    name VARCHAR(200) NOT NULL,                       Nombre del evento
    description TEXT,                                 Descripción del evento
    date DATE NOT NULL,                               Fecha del evento
    time TIME NOT NULL,                               Hora del evento
    venue VARCHAR(200) NOT NULL,                      Lugar donde se realizará
    price DECIMAL(10,2) NOT NULL,                     Precio por entrada
    available_tickets INT NOT NULL DEFAULT 100,       Entradas disponibles
    image_url VARCHAR(500),                           Imagen del evento
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organizer_id) REFERENCES organizers(id) ON DELETE CASCADE
);

-- Tabla de compras (una por transacción del usuario)
CREATE TABLE IF NOT EXISTS purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,                             Usuario que realiza la compra
    event_id INT NOT NULL,                            Evento asociado a la compra
    quantity INT NOT NULL DEFAULT 1,                  Número de tickets comprados
    total_price DECIMAL(10,2) NOT NULL,               Precio total de la compra
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- Tabla de tickets (una por cada entrada individual generada)
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    purchase_id INT NOT NULL,                         Compra a la que pertenece el ticket
    ticket_code VARCHAR(50) UNIQUE NOT NULL,          Código único del ticket
    status ENUM('active', 'used', 'cancelled') DEFAULT 'active',  Estado del ticket
    FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- Insertar eventos de ejemplo (con organizador_id temporal = 1)
INSERT IGNORE INTO events (organizer_id, name, description, date, time, venue, price, available_tickets) VALUES
(1, 'Concierto de Rock', 'Una noche épica de rock con las mejores bandas locales', '2025-10-15', '20:00:00', 'Auditorio Nacional', 50000.00, 500),
(1, 'Festival de Jazz', 'Disfruta del mejor jazz en vivo en un ambiente único', '2025-11-22', '18:00:00', 'Parque Central', 35000.00, 300),
(1, 'Obra de Teatro', 'Una producción teatral que no te puedes perder', '2025-12-05', '19:30:00', 'Teatro de la Ciudad', 25000.00, 150);

-- Insertar usuario de prueba
INSERT IGNORE INTO users (name, email, password) VALUES ('Admin Test', 'admin@starbillet.com', '123456');

-- Insertar organizador de prueba
INSERT IGNORE INTO organizers (name, email, password, company_name, verified) VALUES ('Organizador Test', 'org@starbillet.com', '123456', 'Empresa Eventos S.A.', TRUE);
*/

?>