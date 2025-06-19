<?php
session_start();
require_once 'auth_functions.php';
require_once 'database.php';

// Verificar que el usuario esté logueado y sea admin
if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? 'user') !== "admin") {
    header("Location: index.php");
    exit();
}

$user = null;
$message = '';
$error = '';

// Si se recibe un ID de usuario para editar
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $user = getUserById($user_id);

    if (!$user) {
        $_SESSION['error'] = 'Usuario no encontrado.';
        header('Location: manage_users_orgs.php');
        exit();
    }
}

// Lógica para procesar la actualización del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);

    // Validaciones básicas (puedes añadir más)
    if (empty($name) || empty($email) || empty($role)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Formato de email inválido.';
    } else {
        $result = updateUser($user_id, $name, $email, $role);
        if ($result['success']) {
            $_SESSION['message'] = $result['message'];
            header('Location: manage_users_orgs.php'); // Redirige de vuelta a la lista
            exit();
        } else {
            $error = $result['message'];
        }
    }
    // Si hay un error, recarga los datos del usuario para mostrar el formulario con los datos actuales
    $user = getUserById($user_id);
}

// Recuperar mensajes flash si existen
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - StarBillet</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@700&display=swap&subset=latin-ext"
        rel="stylesheet">
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" type="image/png" href="img/logoblanco.png">
    <style>
        /* Puedes reutilizar algunos estilos de manage_users_orgs.php o definirlos aquí */
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--color-text);
            background-color: var(--color-light-gray);
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: var(--color-white);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: var(--color-primary);
            text-align: center;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group select {
            width: calc(100% - 20px);
            /* Ajusta padding */
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            /* Incluye padding en el ancho */
        }

        .form-group button {
            padding: 10px 20px;
            background-color: var(--color-accent);
            /* O un color de botón primario */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .form-group button:hover {
            opacity: 0.9;
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
    </style>
</head>

<body>
    <div class="container">
        <h1>Editar Usuario</h1>

        <?php if (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($user): ?>
            <form action="edit_user.php" method="POST">
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']); ?>">

                <div class="form-group">
                    <label for="name">Nombre:</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="role">Rol:</label>
                    <select id="role" name="role" required>
                        <option value="user" <?= ($user['role'] === 'user') ? 'selected' : ''; ?>>Usuario</option>
                        <option value="admin" <?= ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit">Actualizar Usuario</button>
                    <a href="manage_users_orgs.php" class="edit-btn"
                        style="background-color: #6c757d; margin-left: 10px;">Cancelar</a>
                </div>
            </form>
        <?php else: ?>
            <p>No se pudo cargar la información del usuario.</p>
        <?php endif; ?>
    </div>
</body>

</html>