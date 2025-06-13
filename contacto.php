<?php
session_start();

// Verificar si el usuario está logueado
$is_logged_in = isset($_SESSION['user_id']);
$user_email = $is_logged_in ? $_SESSION['user_email'] : '';
?>

<!DOCTYPE html>
<html lang="es">

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
                <a href="index.php"><div class="logo-section">
                <div class="logo-wrapper">
                    <img src="img/logo.png" alt="Logo de StarBillet" class="logo-img" />
                    <div class="gif-wrapper">
                        <img id="gif-logo" src="img/giflogos.gif" alt="Animación del logo" class="gif-logo" />
                        <img id="static-logo" src="img/Logotipo3.png" alt="Logotipo final" class="gif-logo static-logo"
                            style="display: none;" />
                    </div>
                </div>
            </div></a>
            </div>
            <div class="nav-links" role="menu" style="display: flex; align-items: center; gap: 1rem;">
                <a href="events.php" role="menuitem" tabindex="0">Eventos</a>
                <a href="contacto.php" role="menuitem" tabindex="0">Contactanos</a>
                <?php if ($is_logged_in): ?>
                    <span style="color: var(--color-text-muted); font-size: 0.9rem;">
                        Hola, <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?>
                    </span>
                    <a href="logout.php" role="menuitem" tabindex="0">Cerrar sesion</a>
                <?php else: ?>
                    <a href="login.php" role="menuitem" tabindex="0">Iniciar sesion</a>
                <?php endif; ?>
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
            <h2>Registra a tu organizacion</h2>
            <p class ="disclaimer">¿Eres organizador de eventos? Crea tu cuenta y comienza a publicar tus eventos hoy mismo.</p>

            <form action="#" method="POST" class="organizer-form">
                <label for="reg-email">Correo electronico</label>
                <input type="email" id="reg-email" name="reg-email" required>

                <label for="org-name">Nombre de la organizacion</label>
                <input type="text" id="org-name" name="org-name" required>

                <label for="reg-password">Contrasena</label>
                <input type="password" id="reg-password" name="reg-password" required>

                <label for="confirm-password">Confirmar contrasena</label>
                <input type="password" id="confirm-password" name="confirm-password" required>

                <button type="submit" class="btnprimarycontacto">Crear cuenta</button>
            </form>

            <div class="login-redirect">
                <p>¿Ya tienes una cuenta?</p>
                <a href="loginOrg.php"><button class="btnprimarycontacto">Inicia sesion aqui</button></a>
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

            <div class="video-contacto">
                    <video autoplay muted loop playsinline preload="auto" poster="poster.jpg">
                        <source src="vid/starbillet.mp4" type="video/mp4">
                        Tu navegador no soporta el video HTML5.
                    </video>
            </div>

        </div>
    </main>

    <footer>
        &copy; 2025 StarBillet. Todos los derechos reservados.
    </footer>

</body>

</html>
