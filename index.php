<?php
session_start(); // Inicia la sesión al principio de tu script

// --- Configuración e Inclusiones ---
// ¡IMPORTANTE! Asegúrate de que estas rutas sean correctas.
// 'auth_functions.php' debería contener funciones como getAllApprovedEvents().
// 'database.php' debería contener la función para obtener la conexión a la base de datos (e.g., getDBConnection()).
require_once 'auth_functions.php';
require_once 'database.php';

// --- Gestión de Sesión de Usuario para el Encabezado/Barra de Navegación ---
$is_logged_in = false; // Por defecto, no está logueado
$user_name = '';
$user_role = '';

// Verifica si hay una sesión de usuario normal
if (isset($_SESSION['user_id'])) {
    $is_logged_in = true;
    $user_name = htmlspecialchars($_SESSION['user_name'] ?? '');
    $user_role = htmlspecialchars($_SESSION['user_role'] ?? 'user'); // Asigna 'user' si el rol no está definido
}
// O verifica si hay una sesión de organizador
elseif (isset($_SESSION['org_id'])) {
    $is_logged_in = true;
    $user_name = htmlspecialchars($_SESSION['org_name'] ?? '');
    $user_role = 'organizer'; // Rol específico para organizadores
}

// Extrae el primer nombre para mostrarlo en el encabezado, si está disponible
$user_first_name = '';
if (!empty($user_name)) {
    $user_first_name = htmlspecialchars(explode(' ', $user_name)[0]);
}

// --- Recuperación de Eventos ---
// Obtiene solo los eventos APROBADOS usando tu función dedicada.
// Esta función (en auth_functions.php) debe encargarse de filtrar por 'status = 'approved''.
$events = getAllApprovedEvents();

// --- Manejo de Mensajes (Mensajes Flash) ---
// Obtiene mensajes y errores de la sesión (ej. después de un registro o compra exitosa)
$message = isset($_SESSION['message']) ? htmlspecialchars($_SESSION['message']) : '';
$error = isset($_SESSION['error']) ? htmlspecialchars($_SESSION['error']) : '';

// Limpia los mensajes de la sesión para que solo se muestren una vez
unset($_SESSION['message']);
unset($_SESSION['error']);

// --- Fin de la Lógica PHP ---
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>StarBillet - Tu boletería de eventos</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" type="image/png" href="img/logoblanco.png">
    <style>
        /* Estilos adicionales para los eventos si necesitas */
        .event-card {
            background-color: var(--color-white);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s;
            cursor: pointer;
        }

        .event-card:hover {
            transform: translateY(-5px);
        }

        .event-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }

        .event-card-content {
            padding: 15px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .event-card-content h3 {
            margin-top: 0;
            color: var(--color-primary);
            font-size: 1.3rem;
            margin-bottom: 10px;
        }

        .event-card-content p {
            font-size: 0.9rem;
            color: var(--color-text-muted);
            margin-bottom: 5px;
        }

        .event-card-content .price {
            font-weight: 700;
            color: var(--color-accent);
            font-size: 1.1rem;
            margin-top: auto;
            /* Empuja el precio al final */
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .message,
        .error {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }

        .message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
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
                <?php if (!$is_logged_in): ?>
                    <a href="contacto.php" role="menuitem" tabindex="0">Contáctanos</a>
                <?php endif; ?>
                <?php if ($is_logged_in): ?>
                    <span style="color: var(--color-text-muted); font-size: 0.9rem;">
                        Hola, <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?>
                    </span>
                    <?php if ($user_role === 'admin'): ?>
                        <a href="admin.php" role="menuitem" tabindex="0">Panel Admin</a>
                    <?php endif; ?>
                    <?php if ($user_role === 'organizer'): ?>
                        <a href="organizer_dashboard.php" role="menuitem" tabindex="0">Mis Eventos</a>
                    <?php endif; ?>
                    <a href="logout.php" role="menuitem" tabindex="0">Cerrar sesión</a>
                <?php else: ?>
                    <a href="login.php" role="menuitem" tabindex="0">Iniciar sesión</a>
                    <a href="register.php" class="btn-secondary" role="menuitem" tabindex="0">Registrarse</a>
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
                    <h1>Tu Pasaporte a Eventos Inolvidables</h1>
                    <p>Descubre y reserva entradas para conciertos, festivales, deportes y más. Vive la experiencia
                        StarBillet.</p>
                    <a href="#events" class="btn-primary">Explorar Eventos</a>
                </div>
                <div class="hero-video">
                    <video autoplay muted loop playsinline preload="auto" poster="poster.jpg">
                        <source src="vid/starbillet.mp4" type="video/mp4">
                        Tu navegador no soporta el video HTML5.
                    </video>
                </div>
            </div>
        </section>

        <section id="features" class="container">
            <h2>¿Por qué elegir StarBillet?</h2>
            <div class="features">
                <div class="feature-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.001 12.001 0 002.928 12c0 3.072 1.05 5.927 2.825 8.204a12.002 12.002 0 008.064 2.894 12.002 12.002 0 008.064-2.894A12.001 12.001 0 0021.072 12c0-3.072-1.05-5.927-2.825-8.204z" />
                    </svg>
                    <h3>Seguridad Garantizada</h3>
                    <p>Compra con confianza. Tus transacciones están protegidas.</p>
                </div>
                <div class="feature-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <h3>Entradas Instantáneas</h3>
                    <p>Recibe tus boletos directamente en tu email o app.</p>
                </div>
                <div class="feature-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V3m0 18v-5M19 10h3m-3 4h3M1 10h3m-3 4h3m7-8v2m0 8v2M9 3h6a2 2 0 012 2v2a2 2 0 01-2 2H9a2 2 0 01-2-2V5a2 2 0 012-2z" />
                    </svg>
                    <h3>Variedad de Eventos</h3>
                    <p>Desde conciertos hasta obras de teatro, tenemos algo para todos.</p>
                </div>
            </div>
        </section>

        <section id="events" class="container">
            <h2>Próximos Eventos</h2>
            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($events)): ?>
                <div class="events-grid">
                    <?php foreach ($events as $event): ?>
                        <div class="event-card" onclick="window.location.href='event_detail.php?id=<?= $event['id'] ?>'">
                            <img src="<?= htmlspecialchars($event['image_url']) ?>"
                                alt="<?= htmlspecialchars($event['name']) ?>">
                            <div class="event-card-content">
                                <h3><?= htmlspecialchars($event['name']) ?></h3>
                                <p><strong>Fecha:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($event['date']))) ?></p>
                                <p><strong>Hora:</strong> <?= htmlspecialchars(date('H:i', strtotime($event['time']))) ?>h</p>
                                <p><strong>Lugar:</strong> <?= htmlspecialchars($event['venue']) ?></p>
                                <p class="price">Precio: $<?= number_format(htmlspecialchars($event['price']), 2) ?></p>
                                <?php if ($is_logged_in): ?>
                                    <a href="event_detail.php?id=<?= $event['id'] ?>" class="btn-secondary">Ver detalles</a>
                                <?php else: ?>
                                    <button class="btn-secondary" onclick="window.location.href='login.php'">Inicia sesión para
                                        comprar</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No hay eventos disponibles en este momento. ¡Vuelve pronto!</p>
            <?php endif; ?>
        </section>

        <section id="contact" class="container">
            <h2>Contáctanos</h2>
            <address style="font-style: normal; color: var(--color-text-muted);">
                <span class="label">Email:</span> <a
                    href="mailto:contacto@starbillet.com">contacto@starbillet.com</a><br />
                <span class="label">Teléfono:</span> <a href="tel:+521234567890">+57 123 456 7890</a>
            </address>

        </section>
    </main>

    <footer>
        &copy; 2025 StarBillet. Todos los derechos reservados.
    </footer>
</body>

</html>