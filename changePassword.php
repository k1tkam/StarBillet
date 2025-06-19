<?php
session_start();
require_once 'database.php';
require_once 'auth_functions.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_email = $is_logged_in ? htmlspecialchars($_SESSION['user_email']) : '';
$user_first_name = $is_logged_in && isset($_SESSION['user_name']) ? htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) : '';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_logged_in) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($current) || empty($new) || empty($confirm)) {
        $error = 'Completa todos los campos.';
    } elseif ($new !== $confirm) {
        $error = 'Las contraseñas nuevas no coinciden.';
    } else {
        // Verifica la contraseña actual
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($current, $user['password'])) {
            $error = 'La contraseña actual es incorrecta.';
        } else {
            $result = changeUserPassword($_SESSION['user_id'], $new);
            if ($result['success']) {
                $message = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cambiar Contraseña - StarBillet</title>
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
            background: #f6f7fb;
            overflow: auto;
        }

        label{
            text-align: center;
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
            max-width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            align-items: center;
            z-index: 1;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.10);
            padding: 2rem 2.5rem;
            width: 100%;
            max-width: 30rem;
            text-align: center;
            backdrop-filter: blur(5px);
        }

        .auth-header h1 {
            font-family: 'Moderniz';
            font-size: 1.7rem;
            color: var(--color-primary, #333);
            margin-bottom: -0.5rem;
            padding-bottom: 1.5rem;
        }

        .auth-header p {
            color: var(--color-text-muted, #666);
            font-size: 1rem;
            align-items: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
            align-items: center;
        }

        .form-group label {
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            color: var(--color-primary, #333);
            margin-bottom: 0.5rem;
            align-items: center;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
            background: #f9fafb;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--color-accent, #5c67f2);
        }

        .error-message {
            background: #fee;
            color: #c53030;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            border: 1px solid #fed7d7;
        }

        .success-message {
            background: #e6ffed;
            color: #276749;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            border: 1px solid #c6f6d5;
        }

        .auth-button {
            width: 100%;
            background-color: var(--color-accent, #5c67f2);
            color: #fff;
            padding: 0.875rem;
            border: none;
            border-radius: 8px;
            font-size: 1.05rem;
            font-weight: 600;
            font-family: 'Moderniz';
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            margin-bottom: 1.5rem;
        }

        .auth-button:hover {
            background-color: var(--color-accent-hover, #3f47d6);
            color: #fff;
            transform: translateY(-1px);
        }

        .auth-links p {
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
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
            color: var(--color-primary, #333);
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            z-index: 2;
        }

        .back-home:hover {
            color: var(--color-accent, #5c67f2);
        }

        @media (max-width: 480px) {
            .auth-card {
                padding: 2rem 1rem;
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

    <header>
        <nav role="navigation" aria-label="Main navigation">
            <div class="logo-section">
                <a href="index.php">
                    <div class="logo-wrapper">
                        <img src="img/logo.png" alt="Logo de StarBillet" class="logo-img" />
                        <div class="gif-wrapper">
                            <img id="gif-logo" src="img/giflogos.gif" alt="Animación del logo" class="gif-logo" />
                            <img id="static-logo" src="img/Logotipo3.png" alt="Logotipo final"
                                class="gif-logo static-logo" style="display: none;" />
                        </div>
                    </div>
                </a>
            </div>
            <div class="nav-links" role="menu" style="display: flex; align-items: center; gap: 1rem;">
                <a href="index.php" role="menuitem" tabindex="0">volver</a>
                <?php if (!$is_logged_in): ?>
                    <a href="contacto.php" role="menuitem" tabindex="0">Contáctanos</a>
                <?php endif; ?>
                <?php if ($is_logged_in): ?>
                    <span style="color: var(--color-text-muted); font-size: 0.9rem;">
                        Hola, <?php echo $user_first_name; ?>
                    </span>
                    <a href="logout.php" role="menuitem" tabindex="0">Cerrar sesión</a>
                <?php else: ?>
                    <a href="login.php" role="menuitem" tabindex="0">Iniciar sesión</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

        <script>
            const gif = document.getElementById("gif-logo");
            const staticLogo = document.getElementById("static-logo");

            const gifDurationSeconds = 2;
            const timesToPlay = 2;

            setTimeout(() => {
                gif.style.display = "none";
                staticLogo.style.display = "block";
            }, gifDurationSeconds * timesToPlay * 1000);
        </script>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="titulo">Cambiar Contrasena</h1>
                <p>Actualiza la contraseña de tu cuenta StarBillet</p>
            </div>
            <?php if (!$is_logged_in): ?>
                <div class="error-message">Debes iniciar sesión para cambiar tu contraseña.</div>
            <?php else: ?>
                <?php if ($message): ?>
                    <div class="success-message"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="post" autocomplete="off">
                    <div class="form-group">
                        <label for="current_password">Contraseña actual</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">Nueva contraseña</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmar nueva contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn-primary">Cambiar contraseña</button>
                </form>
                <br>
                <a href="index.php"><button class="btn-primary">Volver al inicio</button></a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
