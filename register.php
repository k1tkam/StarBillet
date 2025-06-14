<?php
session_start(); // Inicia la sesión al principio de tu script

require_once 'auth_functions.php'; // Asegúrate de que isLoggedIn() y registerUser() estén aquí.

// --- Redirección si el usuario ya está logueado ---
// Si un usuario ya tiene una sesión activa (sea de usuario normal o de organizador/admin),
// lo redirigimos para evitar que intente registrarse de nuevo.
if (isset($_SESSION['user_id']) || isset($_SESSION['org_id'])) {
    header('Location: index.php'); // Redirige a la página principal
    exit(); // Crucial para detener la ejecución del script después de la redirección.
}

$error = '';    // Para mensajes de error
$success = '';  // Para mensajes de éxito

// --- Procesamiento del Formulario de Registro ---
// Solo procesa el formulario si la solicitud es de tipo POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recopila y sanitiza los datos del formulario.
    // Usamos el operador de fusión de null (??) para evitar errores si la clave POST no existe.
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // --- Validaciones de Entrada ---
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Por favor, completa todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del correo electrónico no es válido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        // --- Intentar Registrar al Usuario ---
        // Llama a la función 'registerUser()' que debe estar definida en 'auth_functions.php'.
        // Se espera que esta función devuelva un array con 'success' (booleano) y 'message' (string).
        $result = registerUser($name, $email, $password);

        if ($result['success']) {
            // Si el registro fue exitoso, muestra un mensaje de éxito.
            // Considera usar mensajes flash de sesión aquí si rediriges inmediatamente.
            $_SESSION['message'] = '¡Cuenta creada exitosamente! Ahora puedes iniciar sesión.';
            header('Location: login.php'); // Redirige al usuario directamente a la página de login.
            exit();
        } else {
            // Si el registro falló (ej. email ya existe), muestra el mensaje de error.
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Registrarse - StarBillet</title>
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
            max-width: 450px;
            text-align: center;
            backdrop-filter: blur(5px);
        }

        .auth-header {
            margin-bottom: 2rem;
        }

        .auth-header h1 {
            font-family: 'Moderniz';
            font-size: 1.8rem;
            color: var(--color-primary);
            margin-bottom: 0.5rem;
        }

        .auth-header p {
            color: var(--color-text-muted);
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-family: 'Moderniz';
            font-size: 0.9rem;
            color: var(--color-primary);
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: var(--radius);
            font-family: var(--font-body);
            font-size: 1rem;
            transition: border-color var(--transition);
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--color-primary);
        }

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
                z-index: 3;
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
                <h1>Crear Cuenta</h1>
                <p>Únete a StarBillet y disfruta de los mejores eventos</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Nombre completo</label>
                        <input type="text" id="name" name="name" class="form-control"
                            value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control"
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Contrasena</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <div class="password-requirements">Minimo 6 caracteres</div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmar contrasena</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>

                    <button type="submit" class="auth-button">Crear Cuenta</button>
                </form>
            <?php endif; ?>

            <div class="auth-links">
                <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesion aqui</a></p>
            </div>
        </div>
    </div>

    <script>
        // Validación en tiempo real de contraseñas
        document.getElementById('confirm_password').addEventListener('input', function () {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;

            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = '#ef4444';
            } else {
                this.style.borderColor = '#e5e7eb';
            }
        });
    </script>
</body>

</html>