<?php
session_start();

// Incluir funciones necesarias
require_once 'auth_functions.php';

// Verificar que el usuario esté logueado y sea admin
if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? 'user') !== "admin") {
    // Si no está logueado o no es admin, redirige al index
    header("Location: index.php");
    exit();
}

// Lógica para aceptar, rechazar o eliminar eventos
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['event_id']) && isset($_POST['action'])) {
    $event_id = intval($_POST['event_id']);
    $action = $_POST['action'];
    $result = ['success' => false, 'message' => 'Acción no válida.']; // Default result

    // Llama a las funciones definidas en auth_functions.php
    if ($action === 'accept') {
        $result = updateEventStatus($event_id, 'approved');
    } elseif ($action === 'reject') {
        $result = updateEventStatus($event_id, 'rejected');
    } elseif ($action === 'delete') {
        $result = deleteEventSoft($event_id); // Llama a la función de eliminación lógica
    }

    if ($result['success']) {
        $_SESSION['message'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    // Redirigir a la misma página para evitar reenvío del formulario al recargar
    header("Location: view_all_events.php"); // Redirigimos a esta misma página
    exit();
}

// Obtener eventos pendientes de aprobación
$pending_events = getPendingEvents();

// Obtener todos los eventos para gestión general (incluyendo los eliminados lógicamente)
$all_events = getAllEventsAdmin(); // Asume que esta función trae todos los eventos con su estado

// Mensajes de feedback (éxito/error) desde la sesión
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['message']); // Limpiar el mensaje después de mostrarlo
unset($_SESSION['error']);   // Limpiar el error después de mostrarlo
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestión de Eventos - StarBillet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" type="image/png" href="img/logoblanco.png">
    <style>
        /* Estilos específicos para la tabla de eventos */
        .events-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .events-table th,
        .events-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: middle;
        }

        .events-table th {
            background-color: var(--color-primary);
            color: white;
        }

        .events-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .events-table tr:hover {
            background-color: #ddd;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            /* Permite que los botones se envuelvan si hay muchos */
            gap: 5px;
            /* Espacio entre botones */
        }

        .action-buttons button {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            white-space: nowrap;
            /* Evita que el texto del botón se rompa */
        }

        .action-buttons .accept-btn {
            background-color: var(--color-accent);
            /* Tu color de acento, quizás verde si quieres */
            color: white;
        }

        .action-buttons .reject-btn {
            background-color: #dc3545;
            /* Rojo */
            color: white;
        }

        .action-buttons .delete-btn {
            background-color: #6c757d;
            /* Gris */
            color: white;
        }

        .events-table img {
            max-width: 100px;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        /* Estilos generales para esta página, si no están en style.css */
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--color-text);
            background-color: var(--color-light-gray);
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background-color: var(--color-white);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1,
        h2 {
            color: var(--color-primary);
            text-align: center;
            margin-bottom: 30px;
        }

        /* Estilo para los estados de evento */
        .status-approved {
            color: green;
            font-weight: bold;
        }

        .status-pending {
            color: orange;
            font-weight: bold;
        }

        .status-rejected {
            color: red;
            font-weight: bold;
        }

        .status-deleted {
            color: #6c757d;
            font-weight: bold;
            text-decoration: line-through;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <h2 style="margin-top: 40px;">Todos los Eventos</h2>
        <?php if (!empty($all_events)): ?>
            <table class="events-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Fecha</th>
                        <th>Lugar</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_events as $event): ?>
                        <tr>
                            <td><?= htmlspecialchars($event['id']) ?></td>
                            <td><?= htmlspecialchars($event['name']) ?></td>
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($event['date']))) ?></td>
                            <td><?= htmlspecialchars($event['venue']) ?></td>
                            <td>$<?= number_format(htmlspecialchars($event['price']), 2) ?></td>
                            <td class="status-<?= htmlspecialchars($event['status']) ?>">
                                <?= htmlspecialchars(ucfirst($event['status'])) ?>
                            </td>
                            <td class="action-buttons">
                                <?php if ($event['status'] !== 'approved'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                        <button type="submit" name="action" value="accept" class="accept-btn">Aprobar</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($event['status'] !== 'rejected'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                        <button type="submit" name="action" value="reject" class="reject-btn">Rechazar</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($event['status'] !== 'deleted'): ?>
                                    <form method="POST" style="display:inline;"
                                        onsubmit="return confirm('¿Estás seguro de que quieres eliminar este evento?');">
                                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                        <button type="submit" name="action" value="delete" class="delete-btn">Eliminar</button>
                                    </form>
                                <?php else: /* Si el estado es 'deleted', ofrece la opción de restaurar (aprobar) */ ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                        <button type="submit" name="action" value="accept" class="accept-btn">Restaurar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center;">No hay eventos registrados.</p>
        <?php endif; ?>
    </div>
</body>

</html>