<?php
require_once 'database.php';

// Función para registrar un nuevo usuario
function registerUser($name, $email, $password)
{
    try {
        $pdo = getDBConnection();

        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Este email ya está registrado'];
        }

        // Encriptar la contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insertar el nuevo usuario
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $result = $stmt->execute([$name, $email, $hashedPassword]);

        if ($result) {
            return ['success' => true, 'message' => 'Usuario registrado exitosamente'];
        } else {
            return ['success' => false, 'message' => 'Error al registrar el usuario'];
        }

    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
    }
}

// Función para iniciar sesión
function loginUser($email, $password)
{
    try {
        $pdo = getDBConnection();

        // Buscar el usuario por email
        $stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Iniciar sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            return ['success' => true, 'message' => 'Sesión iniciada correctamente'];
        } else {
            return ['success' => false, 'message' => 'Credenciales incorrectas'];
        }

    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
    }
}

// Función para obtener información del usuario logueado
function getCurrentUser()
{
    if (isset($_SESSION['user_id'])) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email']
        ];
    }
    return null;
}

// Función para verificar si el usuario está logueado
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Función para cerrar sesión
function logoutUser()
{
    session_unset();
    session_destroy();
}

// Función para obtener todos los eventos
function getAllEvents()
{
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM events WHERE date >= CURDATE() ORDER BY date ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Función para obtener un evento por ID
function getEventById($id)
{
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

// Función para generar código de ticket único
function generateTicketCode()
{
    return 'SB-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
}

// Función para comprar tickets
function purchaseTicket($userId, $eventId, $quantity = 1)
{
    try {
        $pdo = getDBConnection();

        // Verificar disponibilidad de tickets
        $event = getEventById($eventId);
        if (!$event) {
            return ['success' => false, 'message' => 'Evento no encontrado'];
        }

        if ($event['available_tickets'] < $quantity) {
            return ['success' => false, 'message' => 'No hay suficientes tickets disponibles'];
        }

        // Calcular precio total
        $totalPrice = $event['price'] * $quantity;

        // Generar código de ticket
        $ticketCode = generateTicketCode();

        // Iniciar transacción
        $pdo->beginTransaction();

        try {
            // Insertar ticket
            $stmt = $pdo->prepare("INSERT INTO tickets (user_id, event_id, quantity, total_price, ticket_code) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $eventId, $quantity, $totalPrice, $ticketCode]);

            // Actualizar tickets disponibles
            $stmt = $pdo->prepare("UPDATE events SET available_tickets = available_tickets - ? WHERE id = ?");
            $stmt->execute([$quantity, $eventId]);

            // Confirmar transacción
            $pdo->commit();

            return [
                'success' => true,
                'message' => 'Compra realizada exitosamente',
                'ticket_code' => $ticketCode,
                'total_price' => $totalPrice
            ];

        } catch (Exception $e) {
            $pdo->rollback();
            return ['success' => false, 'message' => 'Error en la transacción: ' . $e->getMessage()];
        }

    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
    }
}

// Función para obtener tickets de un usuario
function getUserTickets($userId)
{
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT t.*, e.name as event_name, e.date, e.time, e.venue 
            FROM tickets t 
            JOIN events e ON t.event_id = e.id 
            WHERE t.user_id = ? 
            ORDER BY t.purchase_date DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}
?>