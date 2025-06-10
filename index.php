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
                <div class="logo-wrapper">
                    <img src="img/logo.png" alt="Logo de StarBillet" class="logo-img" />
                    <div class="gif-wrapper">
                        <img id="gif-logo" src="img/giflogos.gif" alt="Animación del logo" class="gif-logo" />
                        <img id="static-logo" src="img/Logotipo3.png" alt="Logotipo final" class="gif-logo static-logo"
                            style="display: none;" />
                    </div>
                </div>
            </div>
            <div class="nav-links" role="menu" style="display: flex; align-items: center; gap: 1rem;">
                <a href="#events" role="menuitem" tabindex="0">Eventos</a>
                <a href="#contact" role="menuitem" tabindex="0">Contactanos</a>
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


    <main>
        <section class="hero">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Compra tus boletos para los mejores eventos</h1>
                    <p>Fácil, rapido y seguro. Explora los eventos disponibles y asegura tu lugar hoy mismo.</p>
                    <button class="btn-primary"><a href="#events">Comprar boletos</a></button>
                </div>
                <div class="hero-video">
                    <video autoplay muted loop playsinline preload="auto" poster="poster.jpg">
                        <source src="vid/starbillet.mp4" type="video/mp4">
                        Tu navegador no soporta el video HTML5.
                    </video>
                </div>
            </div>
        </section>

        <section id="events" class="container">
            <h2>Eventos Destacados</h2>
            <div class="events">
                <article class="card">
                    <img src="" alt="Concierto de Rock" />
                    <div class="card-content">
                        <h3>Concierto de Rock</h3>
                        <div class="date-location">15 Octubre 2025 - Auditorio Nacional</div>
                        <?php if ($is_logged_in): ?>
                            <button class="btn-secondary">Comprar ahora</button>
                        <?php else: ?>
                            <button class="btn-secondary" onclick="window.location.href='login.php'">Inicia sesion para
                                comprar</button>
                        <?php endif; ?>
                    </div>
                </article>
                <article class="card">
                    <img src="" alt="Festival de Jazz" />
                    <div class="card-content">
                        <h3>Festival de Jazz</h3>
                        <div class="date-location">22 Noviembre 2025 - Parque Central</div>
                        <?php if ($is_logged_in): ?>
                            <button class="btn-secondary">Comprar ahora</button>
                        <?php else: ?>
                            <button class="btn-secondary" onclick="window.location.href='login.php'">Inicia sesion para
                                comprar</button>
                        <?php endif; ?>
                    </div>
                </article>
                <article class="card">
                    <img src="" alt="Obra de Teatro" />
                    <div class="card-content">
                        <h3>Obra de Teatro</h3>
                        <div class="date-location">5 Diciembre 2025 - Teatro de la Ciudad</div>
                        <?php if ($is_logged_in): ?>
                            <button class="btn-secondary">Comprar ahora</button>
                        <?php else: ?>
                            <button class="btn-secondary" onclick="window.location.href='login.php'">Inicia sesión para
                                comprar</button>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
        </section>

        <section id="features" class="container">
            <h2>Por que comprar con nosotros</h2>
            <div class="features">
                <div class="feature-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3>Compra segura</h3>
                    <p>Protegemos tu información para que compres con total tranquilidad.</p>
                </div>
                <div class="feature-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 7v10a4 4 0 004 4h10a4 4 0 004-4V7M16 3v4M8 3v4m0 0h8" />
                    </svg>
                    <h3>Variedad de eventos</h3>
                    <p>Todo tipo de eventos, cubrimos todos tus gustos y preferencias.</p>
                </div>
                <div class="feature-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4l3 3m6-9v6a9 9 0 11-18 0V7a3 3 0 013 3z" />
                    </svg>
                    <h3>Compra rapida</h3>
                    <p>Un proceso sin complicaciones para que no pierdas tiempo.</p>
                </div>
            </div>
        </section>

        <section id="contact" class="container">
            <h2>Contactanos</h2>
            <address style="font-style: normal; color: var(--color-text-muted);">
                <span class="label">Email:</span> <a
                    href="mailto:contacto@starbillet.com">contacto@starbillet.com</a><br />
                <span class="label">Telefono:</span> <a href="tel:+521234567890">+57 123 456 7890</a>
            </address>

        </section>
    </main>

    <footer>
        &copy; 2025 StarBillet. Todos los derechos reservados.
    </footer>
</body>

</html>