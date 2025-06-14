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

-- Tabla de organizaciones/promotores
CREATE TABLE IF NOT EXISTS org (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
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
INSERT INTO events (id, name, description, date, time, venue, price, available_tickets, image_url, created_at, updated_at) VALUES
(11, 'Concierto de Rock', 'Un evento lleno de energía y buena música con las ...', '2025-10-15', '20:00:00', 'Auditorio Nacional', 50000.00, 100, 'https://colectivosonoro.com/wp-content/uploads/2025/05/ROCKALPARQUE-2025-768x428.jpg', '2025-06-13 11:53:34', '2025-06-13 12:44:39'),
(12, 'Festival de Jazz', 'Un recorrido por el jazz tradicional y contemporán...', '2025-11-22', '18:00:00', 'Parque Central', 40000.00, 150, 'https://facartes.uniandes.edu.co/wp-content/uploads/2023/09/Festival-Jazz-al-Parque.jpeg', '2025-06-13 11:53:34', '2025-06-13 12:44:39'),
(13, 'Obra de Teatro', 'Una emocionante obra que te hará reflexionar sobre...', '2025-12-05', '19:30:00', 'Teatro de la Ciudad', 30000.00, 80, 'https://www.luismaram.com/wp-content/uploads/2013/04/Posters-de-teatro-21.jpg', '2025-06-13 11:53:34', '2025-06-13 12:44:39'),
(14, 'Feria Gastronómica', 'Disfruta de lo mejor de la comida local e internac...', '2025-09-10', '12:00:00', 'Plaza Mayor', 15000.00, 200, 'https://i.pinimg.com/564x/ee/4c/84/ee4c84dd20f1f4766e3ada5a0d5a88eb.jpg', '2025-06-13 11:53:34', '2025-06-13 12:44:39'),
(15, 'Carrera 10K', 'Evento deportivo para toda la familia.', '2025-08-20', '07:00:00', 'Parque Metropolitano', 10000.00, 300, 'https://marketplace.canva.com/EAGKbbTdkXw/2/0/1131w/canva-p%C3%B3ster-evento-club-deportivo-ilustrativo-azul-y-rosado-Y6JHQDnV3nQ.jpg', '2025-06-13 11:53:34', '2025-06-13 12:44:39'),
(16, 'Coldplay - Music of the Spheres Tour', 'La banda británica regresa con un show lleno de lu...', '2025-10-10', '21:00:00', 'Estadio El Campín, Bogotá', 320000.00, 500, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQCuJ1793npq5JgRram0g1Sd5IYQszRJ3HZmw&s', '2025-06-13 12:47:52', '2025-06-13 12:52:03'),
(17, 'Lollapalooza Argentina 2025', 'El festival icónico con los mejores artistas inter...', '2025-03-29', '14:00:00', 'Hipódromo de San Isidro, Buenos Aires', 450000.00, 1000, 'https://www.cronista.com/files/image/939/939237/66d70db5e6bdf.jpg', '2025-06-13 12:47:52', '2025-06-13 12:52:03'),
(18, 'Cirque du Soleil - Kooza', 'Un espectáculo que combina acrobacia tradicional y...', '2025-08-15', '19:30:00', 'Movistar Arena, Bogotá', 200000.00, 300, 'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/2d/09/ea/95/caption.jpg?w=1200&h=-1&s=1', '2025-06-13 12:47:52', '2025-06-13 12:52:03'),
(19, 'Rock al Parque 2025', 'El festival gratuito más grande de rock en Latinoa...', '2025-07-20', '12:00:00', 'Parque Simón Bolívar, Bogotá', 0.00, 2000, 'https://rockalparque.gov.co/sites/default/files/memory-cover/Rock%20al%20Parque%202024.jpeg', '2025-06-13 12:47:52', '2025-06-13 12:52:03'),
(20, 'Maratón de Medellín 2025', 'Competencia atlética en las principales calles de ...', '2025-09-07', '06:30:00', 'Medellín, Colombia', 80000.00, 800, 'https://maratonmedellin.com/cdn/shop/files/Imagen_1200x628_82408f0d-ea94-4898-8aa3-c27740958ea9.png?v=1734472641', '2025-06-13 12:47:52', '2025-06-13 12:52:03'),
(21, 'Me bebí lo del mercado world tour', 'Pa las que sea', '2025-09-07', '06:30:00', 'Bogotá, Colombia', 80000.00, 800, 'https://www.elnuevodia.com.co/nuevodia/sites/default/files/imagenes/2024/08/foto%20enlace%20facebook%20(30)_0.jpg', '2025-06-13 12:47:52', '2025-06-13 12:52:03');

-- Insertar usuario administrador (la contraseña debe ser hasheada en producción)
INSERT IGNORE INTO users (name, email, password, role) 
VALUES ('Administrador', 'admin@starbillet.com', '$2y$10$OuP936B6NQkZ82MzXVvX3emDqmFXqlxuR78L16JpUgKMC7l4bu0z2', 'admin');
*/

?>
