<?php
session_start();

// Verificar que el usuario este logueado y sea admin
if (!isset($_SESSION["user_email"]) || $_SESSION["user_email"] !== "admin@starbillet.com") {
    header("Location: index.php");
    exit();
}

// Variables para el header, similar al index.php
$is_logged_in = isset($_SESSION['user_id']);
$user_email = $is_logged_in ? $_SESSION['user_email'] : '';
$user_name = $is_logged_in ? explode(' ', $_SESSION['user_name'])[0] : ''; // Obtener solo el primer nombre
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Panel de Administrador - StarBillet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" type="image/png" href="img/logoblanco.png">
</head>

<body>
    <header>
        <nav role="navigation" aria-label="Main navigation">
            <div class="logo-section">
                <a href="#admin-panel">
                    <div class="logo-section">
                        <div class="logo-wrapper">
                            <img src="img/logo.png" alt="Logo de StarBillet" class="logo-img" />
                            <div class="gif-wrapper">
                                <img id="gif-logo" src="img/giflogos.gif" alt="Animacion del logo" class="gif-logo" />
                                <img id="static-logo" src="img/Logotipo3.png" alt="Logotipo final"
                                    class="gif-logo static-logo" style="display: none;" />
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="nav-links" role="menu" style="display: flex; align-items: center; gap: 1rem;">
                <a href="#admin-panel" role="menuitem" tabindex="0">Panel de Administrador</a>
                <?php if ($is_logged_in): ?>
                    <span style="color: var(--color-text-muted); font-size: 0.9rem;">
                        Hola, <?php echo htmlspecialchars($user_name); ?>
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

        const gifDurationSeconds = 2;
        const timesToPlay = 2;

        setTimeout(() => {
            gif.style.display = "none";
            staticLogo.style.display = "block";
        }, gifDurationSeconds * timesToPlay * 1000);
    </script>

    <main>
        <section id="admin-panel" class="hero">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Bienvenido, Administrador</h1>
                    <p>Correo: <?= htmlspecialchars($_SESSION["user_email"]) ?></p>
                    <p>Desde aqui puedes gestionar los eventos, usuarios y contenido de tu plataforma StarBillet.</p>
                </div>
                <div class="hero-video">
                    <video autoplay muted loop playsinline preload="auto" poster="poster.jpg">
                        <source src="vid/starbillet.mp4" type="video/mp4">
                        Tu navegador no soporta el video HTML5.
                    </video>
                </div>
            </div>
        </section>

        <section id="management-options" class="container">
            <h2>Opciones de Gestion</h2>
            <div class="features">
                <div class="feature-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3>Gestionar Eventos</h3>
                    <p>Crea, edita o elimina eventos.</p>
                    <a href="#" class="btn-secondary">Ir a Eventos</a>
                </div>
                <div class="feature-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20h2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h2m0 0l4-4m4 4l4-4m-4 4v-9m-4 0h4" />
                    </svg>
                    <h3>Gestionar Usuarios</h3>
                    <p>Administra las cuentas de usuario.</p>
                    <a href="#" class="btn-secondary">Ir a Usuarios</a>
                </div>
                <div class="feature-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <h3>Reportes y Estadisticas</h3>
                    <p>Visualiza el rendimiento de la plataforma.</p>
                    <a href="#" class="btn-secondary">Ver Reportes</a>
                </div>
            </div>
        </section>

        <section id="contact" class="container">
            <h2>Necesitas ayuda?</h2>
            <address style="font-style: normal; color: var(--color-text-muted);">
                <span class="label">Email:</span> <a
                    href="mailto:soporte@starbillet.com">soporte@starbillet.com</a><br />
                <span class="label">Telefono:</span> <a href="tel:+521234567890">+57 123 456 7890</a>
            </address>
        </section>
    </main>

    <footer>
        &copy; 2025 StarBillet. Todos los derechos reservados.
    </footer>
</body>

</html>