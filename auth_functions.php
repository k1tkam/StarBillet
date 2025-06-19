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

function getTicketsByUserId($user_id)
{
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT 
                t.*, 
                e.name AS event_name, 
                e.date, 
                e.time, 
                e.venue, 
                e.city, 
                e.image_url
            FROM tickets t
            INNER JOIN events e ON t.event_id = e.id
            WHERE t.user_id = :user_id
            ORDER BY t.purchase_date DESC
        ");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener tickets por usuario: " . $e->getMessage());
        return [];
    }
}

function changeUserPassword($user_id, $new_password) {
    try {
        $pdo = getDBConnection();
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $result = $stmt->execute([$hashed, $user_id]);
        if ($result) {
            return ['success' => true, 'message' => 'Contraseña actualizada correctamente.'];
        } else {
            return ['success' => false, 'message' => 'No se pudo actualizar la contraseña.'];
        }
    } catch (PDOException $e) {
        error_log("Error al cambiar contraseña: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error de base de datos al cambiar contraseña.'];
    }
}

// Función para iniciar sesion de usuario
function loginUser($email, $password)
{
    try {
        $pdo = getDBConnection(); // Asume que esta función está definida en database.php y devuelve una conexión PDO

        // Prepara la consulta para seleccionar al usuario por email, incluyendo su rol.
        // Asegúrate de que la tabla 'users' tiene columnas 'id', 'name', 'email', 'password', y 'role'.
        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC); // Obtiene los datos del usuario como un array asociativo

        // Verifica si se encontró un usuario y si la contraseña coincide con el hash almacenado.
        if ($user && password_verify($password, $user['password'])) {
            // Si la autenticación es exitosa, guarda los datos del usuario en la sesión.
            // Esto es crucial para mantener el estado de login y el rol del usuario a través de las páginas.
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role']; // <-- ¡Esta línea es fundamental para tu lógica de roles!

            // Retorna un array indicando éxito y los datos del usuario (opcional, pero útil).
            return [
                'success' => true,
                'user' => $user
            ];
        } else {
            // Si las credenciales son incorrectas, retorna un mensaje de error.
            return ['success' => false, 'message' => 'Credenciales incorrectas'];
        }

    } catch (PDOException $e) {
        // En caso de un error de base de datos, registra el error y retorna un mensaje general.
        error_log("Error de base de datos en loginUser: " . $e->getMessage()); // Para depuración en logs del servidor
        return ['success' => false, 'message' => 'Error de base de datos al intentar iniciar sesión.'];
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
        $stmt = $pdo->prepare("SELECT * FROM events WHERE status = 'approved' AND date >= CURDATE() AND available_tickets > 0 ORDER BY date ASC, time ASC");
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
function getEventById($event_id)
{
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND status = 'approved'");
        $stmt->execute([$event_id]);
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

function getRandomEvents($limit = 5, $exclude_id = null)
{
    try {
        $pdo = getDBConnection();
        // Construir la consulta base
        $query = "SELECT * FROM events WHERE status = 'approved' AND date >= CURDATE()";
        // Excluir evento específico si se proporciona
        if ($exclude_id) {
            $query .= " AND id != " . intval($exclude_id);
        }
        // Orden aleatorio y límite
        $query .= " ORDER BY RAND() LIMIT " . intval($limit);
        $stmt = $pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error al obtener eventos aleatorios: " . $e->getMessage());
        return [];
    }
}

// Sección de eventos para el organizador
function registerEvent($name, $description, $date, $time, $venue, $city, $price, $available_tickets, $image_url, $org_id)
{
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO events (name, description, date, time, venue, city, price, available_tickets, image_url, org_id, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
        );
        $result = $stmt->execute([$name, $description, $date, $time, $venue, $city, $price, $available_tickets, $image_url, $org_id]);

        if ($result) {
            return ['success' => true, 'message' => 'Evento registrado exitosamente y pendiente de aprobación.'];
        } else {
            return ['success' => false, 'message' => 'Error al registrar el evento.'];
        }
    } catch (PDOException $e) {
        error_log("Error al registrar evento: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error de base de datos al registrar evento: ' . $e->getMessage()];
    }
    // auth_functions.php (Añadir estas funciones al final del archivo)

    // Función para obtener todos los usuarios
    function getAllUsers()
    {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, name, email, role FROM users ORDER BY name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener todos los usuarios: " . $e->getMessage());
            return [];
        }
    }
    

    // Función para obtener todos los organizadores
    function getAllOrganizers()
    {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, name, email FROM org ORDER BY name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener todos los organizadores: " . $e->getMessage());
            return [];
        }
    }

    // Función para obtener un usuario por ID
    function getUserById($id)
    {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener usuario por ID: " . $e->getMessage());
            return null;
        }
    }

    // Función para obtener un organizador por ID
    function getOrganizerById($id)
    {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, name, email FROM org WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener organizador por ID: " . $e->getMessage());
            return null;
        }
    }

    // Función para actualizar la información de un usuario
// NOTA: No permitir cambiar la contraseña directamente desde aquí, solo otros datos.
// Para contraseña, sería otra función o un formulario específico con verificación.
    function updateUser($id, $name, $email, $role)
    {
        try {
            $pdo = getDBConnection();
            // Opcional: Validar que el rol sea uno permitido, ej: 'user', 'admin'
            if (!in_array($role, ['user', 'admin'])) {
                return ['success' => false, 'message' => 'Rol de usuario no válido.'];
            }
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
            $result = $stmt->execute([$name, $email, $role, $id]);
            if ($result) {
                return ['success' => true, 'message' => 'Usuario actualizado exitosamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar el usuario.'];
            }
        } catch (PDOException $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos al actualizar usuario: ' . $e->getMessage()];
        }
    }

    // Función para actualizar la información de un organizador
    function updateOrganizer($id, $name, $email)
    {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("UPDATE org SET name = ?, email = ? WHERE id = ?");
            $result = $stmt->execute([$name, $email, $id]);
            if ($result) {
                return ['success' => true, 'message' => 'Organización actualizada exitosamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar la organización.'];
            }
        } catch (PDOException $e) {
            error_log("Error al actualizar organizador: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos al actualizar organización: ' . $e->getMessage()];
        }
    }

    // Opcional: Funciones para eliminación lógica (cambiar estado 'active' a 'inactive' o similar)
// Si tu tabla users/org tiene una columna 'status' (ej. 'active', 'inactive', 'deleted')
    function deleteUserSoft($userId)
    {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ?"); // Asume columna 'status'
            $result = $stmt->execute([$userId]);
            if ($result) {
                return ['success' => true, 'message' => 'Usuario marcado como inactivo.'];
            } else {
                return ['success' => false, 'message' => 'Error al inactivar usuario.'];
            }
        } catch (PDOException $e) {
            error_log("Error al inactivar usuario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos al inactivar usuario.'];
        }
    }

    function deleteOrganizerSoft($orgId)
    {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("UPDATE org SET status = 'inactive' WHERE id = ?"); // Asume columna 'status'
            $result = $stmt->execute([$orgId]);
            if ($result) {
                return ['success' => true, 'message' => 'Organización marcada como inactiva.'];
            } else {
                return ['success' => false, 'message' => 'Error al inactivar organización.'];
            }
        } catch (PDOException $e) {
            error_log("Error al inactivar organización: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos al inactivar organización.'];
        }
    }

    // Función para eliminar un usuario de forma permanente (usar con EXTREMA PRECAUCIÓN)
    function deleteUserPermanent($userId)
    {
        try {
            $pdo = getDBConnection();
            // Considera eliminar tickets asociados u otras dependencias para evitar errores de FK
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $pdo->commit();
            return ['success' => true, 'message' => 'Usuario eliminado permanentemente.'];
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error al eliminar usuario permanentemente: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos al eliminar usuario.'];
        }
    }

    // Función para eliminar un organizador de forma permanente (usar con EXTREMA PRECAUCIÓN)
    function deleteOrganizerPermanent($orgId)
    {
        try {
            $pdo = getDBConnection();
            // Considera eliminar eventos asociados u otras dependencias para evitar errores de FK
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("DELETE FROM org WHERE id = ?");
            $stmt->execute([$orgId]);
            $pdo->commit();
            return ['success' => true, 'message' => 'Organización eliminada permanentemente.'];
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error al eliminar organización permanentemente: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos al eliminar organización.'];
        }
    }
}
// auth_functions.php (Añadir estas funciones al final del archivo)

// Función para obtener todos los usuarios
    function getAllUsers()
    {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, name, email, role FROM users ORDER BY name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener todos los usuarios: " . $e->getMessage());
            return [];
        }
    }

    // Función para obtener todos los organizadores
    function getAllOrganizers()
    {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, name, email FROM org ORDER BY name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener todos los organizadores: " . $e->getMessage());
            return [];
        }
    }

    // Función para obtener un usuario por ID
    function getUserById($id)
    {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener usuario por ID: " . $e->getMessage());
            return null;
        }
    }

    // Función para obtener un organizador por ID
    function getOrganizerById($id)
    {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, name, email FROM org WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener organizador por ID: " . $e->getMessage());
            return null;
        }
    }

    // Función para actualizar la información de un usuario
    // NOTA: No permitir cambiar la contraseña directamente desde aquí, solo otros datos.
    // Para contraseña, sería otra función o un formulario específico con verificación.
    function updateUser($id, $name, $email, $role)
    {
        try {
            $pdo = getDBConnection();
            // Opcional: Validar que el rol sea uno permitido, ej: 'user', 'admin'
            if (!in_array($role, ['user', 'admin'])) {
                return ['success' => false, 'message' => 'Rol de usuario no válido.'];
            }
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
            $result = $stmt->execute([$name, $email, $role, $id]);
            if ($result) {
                return ['success' => true, 'message' => 'Usuario actualizado exitosamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar el usuario.'];
            }
        } catch (PDOException $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos al actualizar usuario: ' . $e->getMessage()];
        }
    }

    // Función para actualizar la información de un organizador
    function updateOrganizer($id, $name, $email)
    {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("UPDATE org SET name = ?, email = ? WHERE id = ?");
            $result = $stmt->execute([$name, $email, $id]);
            if ($result) {
                return ['success' => true, 'message' => 'Organización actualizada exitosamente.'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar la organización.'];
            }
        } catch (PDOException $e) {
            error_log("Error al actualizar organizador: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos al actualizar organización: ' . $e->getMessage()];
        }
    }

    // Opcional: Funciones para eliminación lógica (cambiar estado 'active' a 'inactive' o similar)
    // Si tu tabla users/org tiene una columna 'status' (ej. 'active', 'inactive', 'deleted')
    function deleteUserSoft($userId)
    {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ?"); // Asume columna 'status'
            $result = $stmt->execute([$userId]);
            if ($result) {
                return ['success' => true, 'message' => 'Usuario marcado como inactivo.'];
            } else {
                return ['success' => false, 'message' => 'Error al inactivar usuario.'];
            }
        } catch (PDOException $e) {
            error_log("Error al inactivar usuario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos al inactivar usuario.'];
        }
    }

    function deleteOrganizerSoft($orgId)
    {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("UPDATE org SET status = 'inactive' WHERE id = ?"); // Asume columna 'status'
            $result = $stmt->execute([$orgId]);
            if ($result) {
                return ['success' => true, 'message' => 'Organización marcada como inactiva.'];
            } else {
                return ['success' => false, 'message' => 'Error al inactivar organización.'];
            }
        } catch (PDOException $e) {
            error_log("Error al inactivar organización: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos al inactivar organización.'];
        }
    }

    // Función para eliminar un usuario de forma permanente (usar con EXTREMA PRECAUCIÓN)
    function deleteUserPermanent($userId)
    {
        try {
            $pdo = getDBConnection();
            // Considera eliminar tickets asociados u otras dependencias para evitar errores de FK
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $pdo->commit();
            return ['success' => true, 'message' => 'Usuario eliminado permanentemente.'];
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error al eliminar usuario permanentemente: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos al eliminar usuario.'];
        }
    }

    // Función para eliminar un organizador de forma permanente (usar con EXTREMA PRECAUCIÓN)
    function deleteOrganizerPermanent($orgId)
    {
        try {
            $pdo = getDBConnection();
            // Considera eliminar eventos asociados u otras dependencias para evitar errores de FK
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("DELETE FROM org WHERE id = ?");
            $stmt->execute([$orgId]);
            $pdo->commit();
            return ['success' => true, 'message' => 'Organización eliminada permanentemente.'];
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error al eliminar organización permanentemente: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos al eliminar organización.'];
        }
    }

    // Función para obtener los eventos de un organizador
    function getOrganizerEvents($org_id) {
        try {
            $pdo = getDBConnection();
            
            // Consulta optimizada para eventos futuros
            $stmt = $pdo->prepare("
                SELECT id, name, date, time, venue, price, image_url, description 
                FROM events 
                WHERE org_id = :org_id 
                AND status = 'approved'
                AND date >= CURDATE()
                ORDER BY date ASC, time ASC
            ");
            $stmt->bindParam(':org_id', $org_id, PDO::PARAM_INT);
            $stmt->execute();
            $future_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Consulta optimizada para eventos pasados 3 meses
            $stmt = $pdo->prepare("
                SELECT id, name, date, time, venue, price, image_url, description 
                FROM events 
                WHERE org_id = :org_id 
                AND status = 'approved'
                AND date < CURDATE() 
                AND date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                ORDER BY date DESC, time DESC
            ");
            $stmt->bindParam(':org_id', $org_id, PDO::PARAM_INT);
            $stmt->execute();
            $past_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'future' => $future_events,
                'past' => $past_events
            ];
            
        } catch (PDOException $e) {
            error_log("Error al obtener eventos del organizador: " . $e->getMessage());
            return ['future' => [], 'past' => []];
        }
    }

    // Función para verificar si el usuario es el organizador del evento
    function isEventOrganizer($event_id, $org_id) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id FROM events WHERE id = ? AND org_id = ?");
            $stmt->execute([$event_id, $org_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al verificar organizador del evento: " . $e->getMessage());
            return false;
        }
    }

    // Función para obtener un evento por ID


    function updateEvent($event_id, $name, $description, $date, $time, $venue, $city, $price, $available_tickets, $image_url) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("
                UPDATE events 
                SET name = :name, 
                    description = :description, 
                    date = :date, 
                    time = :time, 
                    venue = :venue, 
                    city = :city, 
                    price = :price, 
                    available_tickets = :tickets, 
                    image_url = :image, 
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            return $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':date' => $date,
                ':time' => $time,
                ':venue' => $venue,
                ':city' => $city,
                ':price' => $price,
                ':tickets' => $available_tickets,
                ':image' => $image_url,
                ':id' => $event_id
            ]);
        } catch (PDOException $e) {
            error_log("Error al actualizar evento: " . $e->getMessage());
            return false;
        }
    }

?>
