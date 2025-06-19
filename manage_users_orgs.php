<?php
session_start();

// Incluir funciones necesarias
require_once 'auth_functions.php'; // Asegúrate de que este path sea correcto

// Verificar que el usuario esté logueado y sea admin
if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? 'user') !== "admin") {
    // Si no está logueado o no es admin, redirige al index
    header("Location: index.php");
    exit();
}

// --- Lógica para manejar acciones (eliminar) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['entity_id']) && isset($_POST['entity_type']) && isset($_POST['action'])) {
    $entity_id = intval($_POST['entity_id']);
    $entity_type = $_POST['entity_type']; // 'user' o 'organizer'
    $action = $_POST['action']; // 'delete_soft' o 'delete_permanent'
    $result = ['success' => false, 'message' => 'Acción no válida.'];

    if ($entity_type === 'user') {
        if ($action === 'delete_soft') {
            $result = deleteUserSoft($entity_id);
        } elseif ($action === 'delete_permanent') {
            // ¡ADVERTENCIA!: Usar con extrema precaución. Esto elimina el registro de la DB.
            $result = deleteUserPermanent($entity_id);
        }
    } elseif ($entity_type === 'organizer') {
        if ($action === 'delete_soft') {
            $result = deleteOrganizerSoft($entity_id);
        } elseif ($action === 'delete_permanent') {
            // ¡ADVERTENCIA!: Usar con extrema precaución. Esto elimina el registro de la DB.
            $result = deleteOrganizerPermanent($entity_id);
        }
    }

    if ($result['success']) {
        $_SESSION['message'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    // Redirigir a la misma página para evitar reenvío del formulario al recargar
    header("Location: manage_users_orgs.php");
    exit();
}

// --- Obtener datos para mostrar ---
$users = getAllUsers();
$organizers = getAllOrganizers();

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
    <title>Gestión de Usuarios y Organizaciones - StarBillet</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@700&display=swap&subset=latin-ext"
        rel="stylesheet">
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" type="image/png" href="img/logoblanco.png">
    <style>
        /* Estilos generales similares a tu página de eventos */
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

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: middle;
        }

        .data-table th {
            background-color: var(--color-primary);
            color: white;
        }

        .data-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .data-table tr:hover {
            background-color: #ddd;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .action-buttons button,
        .action-buttons a {
            /* Estilo para enlaces también si los usas como botones */
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            white-space: nowrap;
            text-decoration: none;
            /* Para enlaces */
            display: inline-block;
            /* Para enlaces */
            text-align: center;
            /* Para enlaces */
        }

        .edit-btn {
            background-color: #007bff;
            /* Azul */
            color: white;
        }

        .delete-soft-btn {
            background-color: #ffc107;
            /* Amarillo/Naranja */
            color: #212529;
            /* Negro */
        }

        .delete-permanent-btn {
            background-color: #dc3545;
            /* Rojo */
            color: white;
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

        .role-admin {
            color: #dc3545;
            font-weight: bold;
        }

        .role-user {
            color: #28a745;
        }

        /* Verde para usuarios normales */
        .status-inactive {
            color: #6c757d;
            text-decoration: line-through;
        }

        /* Gris con tachado */
    </style>
</head>

<body>
    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <h2 style="margin-top: 40px;">Usuarios Registrados</h2>
        <?php if (!empty($users)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td class="role-<?= htmlspecialchars($user['role']) ?>">
                                <?= htmlspecialchars(ucfirst($user['role'])) ?>
                            </td>
                            <td class="action-buttons">
                                <a href="edit_user.php?id=<?= $user['id'] ?>" class="edit-btn">Editar</a>
                                <?php if (($user['role'] ?? '') !== 'admin'): // No permitir eliminar al propio admin fácilmente ?>
                                    <form method="POST" style="display:inline;"
                                        onsubmit="return confirm('¿Estás seguro de que quieres INACTIVAR este usuario?');">
                                        <input type="hidden" name="entity_id" value="<?= $user['id'] ?>">
                                        <input type="hidden" name="entity_type" value="user">
                                        <button type="submit" name="action" value="delete_soft"
                                            class="delete-soft-btn">Inactivar</button>
                                    </form>
                                    <form method="POST" style="display:inline;"
                                        onsubmit="return confirm('¡ADVERTENCIA! ¿Estás seguro de que quieres ELIMINAR PERMANENTEMENTE este usuario y sus datos asociados? Esta acción es irreversible.');">
                                        <input type="hidden" name="entity_id" value="<?= $user['id'] ?>">
                                        <input type="hidden" name="entity_type" value="user">
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center;">No hay usuarios registrados.</p>
        <?php endif; ?>

        <h2 style="margin-top: 60px;">Organizaciones Registradas</h2>
        <?php if (!empty($organizers)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($organizers as $org): ?>
                        <tr>
                            <td><?= htmlspecialchars($org['id']) ?></td>
                            <td><?= htmlspecialchars($org['name']) ?></td>
                            <td><?= htmlspecialchars($org['email']) ?></td>
                            <td class="action-buttons">
                                <a href="edit_organizer.php?id=<?= $org['id'] ?>" class="edit-btn">Editar</a>
                                <form method="POST" style="display:inline;"
                                    onsubmit="return confirm('¿Estás seguro de que quieres INACTIVAR esta organización?');">
                                    <input type="hidden" name="entity_id" value="<?= $org['id'] ?>">
                                    <input type="hidden" name="entity_type" value="organizer">
                                    <button type="submit" name="action" value="delete_soft"
                                        class="delete-soft-btn">Inactivar</button>
                                </form>
                                <form method="POST" style="display:inline;"
                                    onsubmit="return confirm('¡ADVERTENCIA! ¿Estás seguro de que quieres ELIMINAR PERMANENTEMENTE esta organización y sus datos asociados (eventos, etc.)? Esta acción es irreversible.');">
                                    <input type="hidden" name="entity_id" value="<?= $org['id'] ?>">
                                    <input type="hidden" name="entity_type" value="organizer">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center;">No hay organizaciones registradas.</p>
        <?php endif; ?>

    </div>
</body>

</html>