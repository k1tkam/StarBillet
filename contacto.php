<?php
session_start(); // Solo necesitas iniciar la sesión

// Ya no destruyas ni reinicies la sesión aquí.
// Esto permite que el usuario mantenga su sesión si ya estaba logueado.

require_once 'auth_functions.php'; // Asegúrate de que esta ruta sea correcta
$error = '';
$success = '';

// Lógica para verificar si el usuario está logueado para mostrar información relevante en el header o navbar
$is_logged_in = isset($_SESSION['user_id']);
$user_email = $is_logged_in ? htmlspecialchars($_SESSION['user_email']) : '';
$user_first_name = $is_logged_in && isset($_SESSION['user_name']) ? htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aquí tu lógica para procesar el formulario de contacto o registro
    // Es CRÍTICO asegurarse de que registerOrg() sea para REGISTRAR, no para LOGUEAR
    // Si este formulario es de CONTACTO, esta lógica de registerOrg() no debería estar aquí.
    // Asumo que este archivo se usa para REGISTRO de ORGANIZACIONES.

    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validaciones
    if (empty($email) || empty($name) || empty($password) || empty($confirm_password)) {
        $error = 'Por favor completa todos los campos';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email no válido';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } else {
        // Asegúrate de que registerOrg() es una función para registrar NUEVAS cuentas,
        // no para manejar el inicio de sesión.
        $result = registerOrg($email, $name, $password);
        if ($result['success']) {
            $success = 'Cuenta creada exitosamente. ¡Ahora puedes iniciar sesión!';
            // Opcional: Redirigir al login después del registro exitoso
            // header('Location: login.php?registered=true');
            // exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - StarBillet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" type="image/png" href="img/logoblanco.png">
    <link rel="stylesheet" href="

<!DOCTYPE html>
<html lang=" es">

    <style>
        .error-message {
            background: #fee;
            color: #c53030;
            padding: 0.75rem;
            border-radius: var(--radius);
            margin-bottom: 1rem;
            font-size: 0.9rem;
            border: 1px solid #fed7d7;
        }

        .success-message {
            background: #f0fff4;
            color: #38a169;
            padding: 0.75rem;
            border-radius: var(--radius);
            margin-bottom: 1rem;
            font-size: 0.9rem;
            border: 1px solid #c6f6d5;
            text-align: center;
        }

        .success-message a {
            color: #2d7738;
            font-weight: 600;
        }

        .auth-button {
            width: 100%;
            background-color: var(--color-accent);
            color: #fff;
            padding: 0.875rem;
            border: none;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 600;
            font-family: 'Moderniz';
            cursor: pointer;
            transition: background-color var(--transition), transform var(--transition);
            margin-bottom: 1.5rem;
        }

        .auth-button:hover {
            color: #333;
            background-color: var(--color-accent-hover);
            transform: translateY(-1px);
        }

        .auth-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .auth-links {
            text-align: center;
        }

        .auth-links a {
            color: var(--color-primary);
            font-weight: 600;
            text-decoration: none;
            font-family: 'Moderniz';
            font-size: 0.9rem;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }

        .back-home {
            position: absolute;
            top: 2rem;
            left: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--color-primary);
            font-family: 'Moderniz';
            font-weight: 600;
            text-decoration: none;
            transition: color var(--transition);
            z-index: 2;
        }

        .back-home:hover {
            color: var(--color-text-muted);
        }

        .password-requirements {
            font-size: 0.8rem;
            color: var(--color-text-muted);
            margin-top: 0.25rem;
            font-family: 'Inter';
        }
    </style>

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>StarBillet</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@700&display=swap"
            rel="stylesheet" />
        <link rel="stylesheet" href="style.css" />
        <link rel="icon" type="image/png" href="img/logoblanco.png">

    </head>

<body>
    <header>
        <nav role="navigation" aria-label="Main navigation">
            <div class="logo-section">
                <a href="index.php">
                    <div class="logo-section">
                        <div class="logo-wrapper">
                            <img src="img/logo.png" alt="Logo de StarBillet" class="logo-img" />
                            <div class="gif-wrapper">
                                <img id="gif-logo" src="img/giflogos.gif" alt="Animación del logo" class="gif-logo" />
                                <img id="static-logo" src="img/Logotipo3.png" alt="Logotipo final"
                                    class="gif-logo static-logo" style="display: none;" />
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="nav-links" role="menu" style="display: flex; align-items: center; gap: 1rem;">
                <a href="events.php" role="menuitem" tabindex="0">Eventos</a>
                <a href="contacto.php" role="menuitem" tabindex="0">Contactanos</a>
                <a href="login.php" role="menuitem" tabindex="0">Iniciar sesión</a>
            </div>
        </nav>
    </header>

    <script>
        const gif = document.getElementById("gif-logo");
        const staticLogo = document.getElementById("static-logo");

        // Cambiar después de duración total del gif (ejemplo: 3 segundos por ciclo x 2)
        const gifDurationSeconds = 2; // cambia esto según la duración de UNA reproducción
        const timesToPlay = 2;

        setTimeout(() => {
            gif.style.display = "none";
            staticLogo.style.display = "block";
        }, gifDurationSeconds * timesToPlay * 1000); // Tiempo en milisegundos
    </script>

    <main class="two-column-section">
        <div class="left-panel">
            <h2>Registra a tu organización</h2>
            <p class="disclaimer">¿Eres organizador de eventos? Crea tu cuenta y comienza a publicar tus eventos hoy
                mismo.</p>

            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form action="" method="POST" class="organizer-form">
                    <label for="reg-email">Correo electrónico</label>
                    <input type="email" id="reg-email" name="email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>

                    <label for="org-name">Nombre de la organización</label>
                    <input type="text" id="org-name" name="name"
                        value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>

                    <label for="reg-password">Contraseña</label>
                    <input type="password" id="reg-password" name="password" required>

                    <label for="confirm-password">Confirmar contraseña</label>
                    <input type="password" id="confirm-password" name="confirm_password" required>

                    <button type="submit" class="btnprimarycontacto">Crear cuenta</button>
                </form>
            <?php endif; ?>

            <div class="login-redirect">
                <p>¿Ya tienes una cuenta?</p>
                <a href="loginOrg.php"><button class="btnprimarycontacto">Inicia sesión aquí</button></a>
            </div>
        </div>

        <div class="right-panel">
            <h2>¿Quieres trabajar con nosotros?</h2>
            <p>¡Estamos buscando talento! Si te apasionan los eventos y quieres formar parte de nuestro equipo:</p>
            <ul>
                <li><strong>Correo:</strong> talento@starbillet.com</li>
                <li><strong>WhatsApp:</strong> +57 300 123 4567</li>
            </ul>
            <p>Envíanos tu hoja de vida y únete a nuestro equipo.</p>

            <!--<div class="video-contacto">
                    <video autoplay muted loop playsinline preload="auto" poster="poster.jpg">
                        <source src="vid/starbillet.mp4" type="video/mp4">
                        Tu navegador no soporta el video HTML5.
                    </video>
            </div>-->

        </div>
    </main>

    <footer>
        &copy; 2025 StarBillet. Todos los derechos reservados.
    </footer>

</body>

</html>