<?php
session_start(); // Inicia la sesión al principio

require_once 'auth_functions.php'; // Asegúrate de que loginUser() y isLoggedIn() estén aquí

// --- Lógica de Protección contra Fuerza Bruta ---
// Inicializa o actualiza el contador de intentos y el tiempo del último intento
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

$bloqueado = false;
$tiempo_bloqueo = 60; // segundos
$max_intentos = 5;

// Verifica si el usuario está actualmente bloqueado
if ($_SESSION['login_attempts'] >= $max_intentos) {
    $elapsed = time() - $_SESSION['last_attempt_time'];
    if ($elapsed < $tiempo_bloqueo) {
        $bloqueado = true;
        // Calcula el tiempo restante para mostrarlo al usuario
        $tiempo_restante = $tiempo_bloqueo - $elapsed;
        $error = 'Demasiados intentos fallidos. Por favor, espera ' . $tiempo_restante . ' segundos antes de intentar de nuevo.';
    } else {
        // El tiempo de bloqueo ha pasado, reinicia el contador
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = time(); // Reinicia el tiempo también
    }
}

// --- Redirección de Usuarios Ya Logueados ---
// Esta es la primera verificación real, antes de procesar cualquier POST
if (isset($_SESSION['user_id'])) {
    // Si es un usuario regular o admin
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        header('Location: admin-dashboard.php');
    } else {
        header('Location: index.php'); // O a dashboard.php si tienes uno para usuarios normales
    }
    exit();
} elseif (isset($_SESSION['org_id'])) {
    // Si es un organizador
    header('Location: organizer-dashboard.php'); // Asume que tienes un dashboard para organizadores
    exit();
}

$error = ''; // Inicializa la variable de error antes del bloque POST

// --- Procesamiento del Formulario de Login ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($bloqueado) {
        // Si está bloqueado, no procesa el login, solo muestra el error de bloqueo.
        // El mensaje de error ya se estableció arriba.
    } elseif (empty($email) || empty($password)) {
        $error = 'Por favor completa todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del email no es válido.';
    } else {
        // Intenta loguear al usuario
        // Asegúrate de que loginUser() devuelva un array con 'success', 'message', y 'user'/'organizer' data
        $result = loginUser($email, $password);

        if ($result['success']) {
            // Reinicia los intentos de login al éxito
            $_SESSION['login_attempts'] = 0;
            $_SESSION['last_attempt_time'] = time();

            // Guarda los datos en sesión de forma granular
            if (isset($result['user'])) { // Esto sería para usuarios normales y administradores
                $_SESSION['user_id'] = $result['user']['id'];
                $_SESSION['user_name'] = $result['user']['name'];
                $_SESSION['user_email'] = $result['user']['email'];
                $_SESSION['user_role'] = $result['user']['role'];

                // Redirigir según el rol
                if ($result['user']['role'] === 'admin') {
                    header('Location: admin-dashboard.php');
                } else {
                    header('Location: index.php');
                }
            } elseif (isset($result['organizer'])) { // Esto sería para organizadores
                $_SESSION['org_id'] = $result['organizer']['id'];
                $_SESSION['org_name'] = $result['organizer']['name'];
                $_SESSION['org_email'] = $result['organizer']['email'];
                $_SESSION['user_role'] = 'organizer'; // Define explícitamente el rol de organizador

                header('Location: organizer-dashboard.php');
            }
            exit();
        } else {
            // El login falló, incrementa el contador de intentos
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();
            $error = $result['message'] ?? 'Credenciales incorrectas.'; // Mensaje de error general para seguridad
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Iniciar Sesión - StarBillet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" type="image/png" href="img/logoblanco.png">
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Inter', sans-serif;
            overflow: auto;
        }

        .background-gif {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            object-fit: contain;
            transform: translate(-50%, -50%);
            z-index: -1;
        }


        .auth-container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            z-index: 1;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: var(--radius, 12px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            width: 100%;
            max-width: 400px;
            text-align: center;
            backdrop-filter: blur(5px);
        }

        .auth-header h1 {
            font-family: 'Moderniz';
            font-size: 1.8rem;
            color: var(--color-primary, #333);
            margin-bottom: 0.5rem;
        }

        .auth-header p {
            color: var(--color-text-muted, #666);
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .form-group label {
            font-family: 'Moderniz';
            font-size: 0.9rem;
            color: var(--color-primary, #333);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: var(--radius, 8px);
            font-size: 1rem;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--color-primary, #333);
        }

        .error-message {
            background: #fee;
            color: #c53030;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            border: 1px solid #fed7d7;
        }

        .auth-button {
            width: 100%;
            background-color: var(--color-accent, #5c67f2);
            color: #fff;
            padding: 0.875rem;
            border: none;
            border-radius: var(--radius, 8px);
            font-size: 1rem;
            font-weight: 600;
            font-family: 'Moderniz';
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            margin-bottom: 1.5rem;
        }

        .auth-button:hover {
            background-color: var(--color-accent-hover, #3f47d6);
            color: #333;
            transform: translateY(-1px);
        }

        .auth-links p {
            font-family: 'Moderniz';
            font-size: 0.9rem;
        }

        .auth-links a {
            color: var(--color-primary, #333);
            font-weight: 600;
            text-decoration: none;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }

        .back-home {
            position: absolute;
            top: 2rem;
            left: 2rem;
            color: var(--color-primary, #fff);
            font-family: 'Moderniz';
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            z-index: 2;
        }

        .back-home:hover {
            color: var(--color-text-muted, #ccc);
        }

        @media (max-width: 480px) {
            .auth-card {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }

            .back-home {
                position: relative;
                top: auto;
                left: auto;
                margin-bottom: 1rem;
                justify-content: center;
            }
        }
    </style>

</head>

<body>
    <img src="img/logo.png" alt="Fondo animado" class="background-gif" />
    <a href="index.php" class="back-home">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Volver al inicio
    </a>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Iniciar Sesion</h1>
                <p>Accede a tu cuenta de StarBillet</p>
            </div>
            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Contrasena</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="auth-button">Iniciar Sesion</button>
            </form>

            <div class="auth-links">
                <p>¿No tienes cuenta? <a href="register.php">Registrate aqui</a></p>
            </div>
        </div>
    </div>
</body>

</html>