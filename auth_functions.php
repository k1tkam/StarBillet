<?php
// Asegúrate de que este path sea correcto para tu archivo database.php
require_once 'database.php'; // Este archivo debe contener la función getDBConnection()

// Función para registrar un nuevo usuario
function registerUser($name, $email, $password)
{
    try {
        $pdo = getDBConnection();

        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Este email ya esta registrado'];
        }

        // Encriptar la contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insertar el nuevo usuario
        // Asegúrate de que la columna 'role' exista en tu tabla 'users' y tenga un valor por defecto 'user'
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

function registerOrg($email, $name, $password)
{
    try {
        $pdo = getDBConnection();

        // Verificar si el email ya existe en organizaciones
        $stmt = $pdo->prepare("SELECT id FROM org WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Este email ya esta registrado para una organizacion'];
        }

        // Encriptar la contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insertar la nueva organizacion
        $stmt = $pdo->prepare("INSERT INTO org (name, email, password) VALUES (?, ?, ?)");
        $result = $stmt->execute([$name, $email, $hashedPassword]);

        if ($result) {
            return ['success' => true, 'message' => 'Organizacion registrada exitosamente'];
        } else {
            return ['success' => false, 'message' => 'Error al registrar la organizacion'];
        }

    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
    }
}

// Función para iniciar sesion de usuario
function loginUser($email, $password)
{
    try {
        $pdo = getDBConnection();

        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role']; // Guardar el rol en la sesion

            return [
                'success' => true,
                'user' => $user
            ];
        } else {
            return ['success' => false, 'message' => 'Credenciales incorrectas'];
        }

    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
    }
}


function loginOrganizer(string $email, string $password): array
{
    try {
        $pdo = getDBConnection();

        // Asegúrate que esta consulta sea correcta para tu tabla 'org'
        $stmt = $pdo->prepare("SELECT id, name, email, password FROM org WHERE email = ?");
        $stmt->execute([$email]);
        $organizer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$organizer) {
            return ['success' => false, 'message' => 'Email o contraseña incorrectos.'];
        }

        // ¡CLAVE!: Verifica que la contraseña en la BD esté hasheada con password_hash()
        if (password_verify($password, $organizer['password'])) {
            return [
                'success' => true,
                'organizer' => [
                    'id' => $organizer['id'],
                    'name' => $organizer['name'],
                    'email' => $organizer['email']
                ]
            ];
        } else {
            return ['success' => false, 'message' => 'Email o contraseña incorrectos.'];
        }

    } catch (PDOException $e) {
        // MUY IMPORTANTE para ver errores:
        error_log("Error de login de organizador: " . $e->getMessage());
        return ['success' => false, 'message' => 'Ocurrió un error al intentar iniciar sesión.'];
    }
}

// Y tu función isOrganizerLoggedIn():
function isOrganizerLoggedIn(): bool
{
    return isset($_SESSION['org_id']);
}

// Función para obtener informacion del usuario logueado
function getCurrentUser()
{
    if (isset($_SESSION['user_id'])) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'] ?? 'user' // Asegurar que el rol este presente
        ];
    }
    return null;
}

// Función para verificar si el usuario esta logueado
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Función para cerrar sesion
function logoutUser()
{
    session_unset();
    session_destroy();
}

### **Funciones para la gestión de Eventos y Aprobación**


// Función para obtener todos los eventos PUBLICOS (APROBADOS)
function getAllApprovedEvents()
{
    try {
        $pdo = getDBConnection();
        // Solo selecciona eventos con estado 'approved' y que no hayan pasado
        $stmt = $pdo->prepare("SELECT * FROM events WHERE status = 'approved' AND date >= CURDATE() ORDER BY date ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener eventos aprobados: " . $e->getMessage()); // Log del error
        return [];
    }
}

// Función para obtener eventos PENDIENTES DE APROBACION (para el admin)
function getPendingEvents()
{
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM events WHERE status = 'pending' ORDER BY created_at ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener eventos pendientes: " . $e->getMessage()); // Log del error
        return [];
    }
}

// Función para obtener todos los eventos, sin importar el estado (para el admin)
function getAllEventsAdmin()
{
    try {
        $pdo = getDBConnection();
        // Esta función debe obtener todos los eventos para la gestión administrativa, incluyendo los "eliminados"
        $stmt = $pdo->prepare("SELECT * FROM events ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener todos los eventos para admin: " . $e->getMessage()); // Log del error
        return [];
    }
}

// Función para actualizar el estado de un evento
function updateEventStatus($eventId, $newStatus)
{
    try {
        $pdo = getDBConnection();
        // Validar que el nuevo estado sea uno de los permitidos
        if (!in_array($newStatus, ['pending', 'approved', 'rejected', 'inactive', 'deleted'])) { // Agregamos 'deleted' aquí
            return ['success' => false, 'message' => 'Estado no valido.'];
        }

        $stmt = $pdo->prepare("UPDATE events SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $eventId]);

        if ($stmt->rowCount() > 0) {
            return ['success' => true, 'message' => 'Estado del evento actualizado.'];
        } else {
            return ['success' => false, 'message' => 'No se encontro el evento o el estado ya era el mismo.'];
        }
    } catch (PDOException $e) {
        error_log("Error al actualizar el estado del evento: " . $e->getMessage()); // Log del error
        return ['success' => false, 'message' => 'Error de base de datos al actualizar el estado: ' . $e->getMessage()];
    }
}

/**
 * Realiza una "eliminación lógica" de un evento actualizando su estado a 'deleted'.
 *
 * @param int $event_id El ID del evento a eliminar lógicamente.
 * @return array Un array asociativo con 'success' (bool) y 'message' (string).
 */
function deleteEventSoft($event_id)
{
    return updateEventStatus($event_id, 'deleted'); // Reutilizamos updateEventStatus
}


// Función para crear un nuevo evento
function createEvent($name, $description, $date, $time, $venue, $price, $available_tickets, $image_url)
{
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO events (name, description, date, time, venue, price, available_tickets, image_url, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')" // Por defecto, 'pending'
        );
        $result = $stmt->execute([$name, $description, $date, $time, $venue, $price, $available_tickets, $image_url]);

        if ($result) {
            return ['success' => true, 'message' => 'Evento creado exitosamente y pendiente de aprobación.'];
        } else {
            return ['success' => false, 'message' => 'Error al crear el evento.'];
        }
    } catch (PDOException $e) {
        error_log("Error al crear evento: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error de base de datos al crear evento: ' . $e->getMessage()];
    }
}


// Función para obtener un evento por ID
function getEventById($id)
{
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener evento por ID: " . $e->getMessage());
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

        // Asegurarse de que el evento esté aprobado antes de permitir la compra
        if ($event['status'] !== 'approved') {
            return ['success' => false, 'message' => 'Este evento aún no está disponible para compra.'];
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
            return ['success' => false, 'message' => 'Error en la transaccion: ' . $e->getMessage()];
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener tickets de usuario: " . $e->getMessage());
        return [];
    }
}

?>