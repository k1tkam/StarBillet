<?php
// ¡¡¡ASEGÚRATE DE QUE NO HAYA NADA ANTES DE ESTA ETIQUETA PHP!!!
// Esto incluye espacios en blanco, líneas vacías o cualquier carácter,
// ya que cualquier salida antes de session_start() o header() causará errores.
session_start(); // Inicia la sesión al principio de tu script, ¡solo una vez!

// --- INICIO: CÓDIGO DE DEPURACIÓN (eliminar cuando todo funcione correctamente) ---
// Estas líneas son útiles para ver errores, pero quítalas en producción.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- FIN: CÓDIGO DE DEPURACIÓN ---

// Asegúrate de que los paths a tus archivos 'auth_functions.php' y 'database.php' sean correctos
require_once 'auth_functions.php';
require_once 'database.php';

// --- LÓGICA DE REDIRECCIÓN PARA USUARIOS LOGUEADOS ---
// Esta es la parte CLAVE para evitar que los usuarios logueados (admins, organizadores) accedan al index.
// La prioridad de redirección es importante: de mayor a menor privilegio.

// 1. Si es un ADMINISTRADOR logueado, redirige a su panel.
// Se asume que el rol 'admin' se guarda en $_SESSION['user_role'] mediante la función loginUser().
if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'admin') {
    header('Location: admin-dashboard.php'); // Redirige al panel de administrador
    exit(); // Detiene la ejecución del script para asegurar la redirección
}

// 2. Si es un ORGANIZADOR logueado, redirige a su panel.
// Esta lógica utiliza la función isOrganizerLoggedIn() que verifica la existencia de $_SESSION['org_id'].
if (isOrganizerLoggedIn()) {
    header('Location: orgView.php'); // Redirige al panel del organizador
    exit(); // Detiene la ejecución del script para asegurar la redirección
}

// 3. Si es un USUARIO NORMAL logueado, decide si lo rediriges o lo dejas en el index.
// Esta lógica utiliza isLoggedIn() que verifica la existencia de $_SESSION['user_id'].
if (isLoggedIn()) {
    // Si quieres que los usuarios normales sean redirigidos a su propio dashboard, descomenta las líneas siguientes:
    // header('Location: userDashboard.php'); // Por ejemplo, a un dashboard específico para usuarios
    // exit(); // Detiene la ejecución para la redirección
    // Si los usuarios normales pueden ver el index (comportamiento actual), simplemente no hagas nada en este bloque.
}

// --- Fin de la LÓGICA DE REDIRECCIÓN ---


// --- Gestión de Sesión de Usuario para el Encabezado/Barra de Navegación ---
// Este bloque se encarga de preparar las variables para mostrar información de usuario en el encabezado,
// solo si el usuario no fue redirigido por la lógica anterior.
$is_logged_in = false; // Por defecto, el usuario no está logueado
$user_name = '';
$user_role = '';
$user_first_name = '';

// Verifica si hay una sesión de usuario regular (incluyendo administradores, ya que ambos usan 'user_id' en la sesión)
if (isset($_SESSION['user_id'])) {
    $is_logged_in = true;
    $user_name = htmlspecialchars($_SESSION['user_name'] ?? '');
    $user_role = htmlspecialchars($_SESSION['user_role'] ?? 'user'); // Asigna 'user' si el rol no está definido (fallback)
}
// O verifica si hay una sesión de organizador (como un caso secundario, si la redirección inicial no ocurrió o como fallback)
elseif (isset($_SESSION['org_id'])) {
    $is_logged_in = true;
    $user_name = htmlspecialchars($_SESSION['org_name'] ?? '');
    $user_role = 'organizer'; // Asigna el rol específico para organizadores
}

// Extrae el primer nombre del usuario para mostrarlo en el encabezado, si el nombre de usuario está disponible.
if (!empty($user_name)) {
    $user_first_name = htmlspecialchars(explode(' ', $user_name)[0]);
}

// --- Recuperación de Eventos ---
// Obtiene solo los eventos APROBADOS desde la base de datos utilizando la función dedicada de 'auth_functions.php'.
$events = getAllApprovedEvents();

// --- Manejo de Mensajes (Mensajes Flash) ---
// Recupera mensajes y errores almacenados en la sesión (por ejemplo, después de una operación exitosa como el registro o una compra).
$message = isset($_SESSION['message']) ? htmlspecialchars($_SESSION['message']) : '';
// Corregido: Asegura que la variable $error tome el valor de $_SESSION['error'] si está seteada.
$error = isset($_SESSION['error']) ? htmlspecialchars($_SESSION['error']) : '';

// Limpia los mensajes de la sesión para que se muestren solo una vez al recargar la página.
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
        }
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
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
                    <a href="myTickets.php" role="menuitem" tabindex="0">Mis tickets</a>
                <?php endif; ?>   
                <?php if ($is_logged_in): ?>
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
                    <a href="events.php " class="btn-primary">Explorar Eventos</a>
                    <br>
                    <a href="changePassword.php" class="btn-primary">Cambiar contraseña</a>
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
                        <div class="event-card" onclick="window.location.href='detailsEvent.php?id=<?= $event['id'] ?>'">
                            <img src="<?= htmlspecialchars($event['image_url']) ?>"
                                alt="<?= htmlspecialchars($event['name']) ?>">
                            <div class="event-card-content">
                                <h3><?= htmlspecialchars($event['name']) ?></h3>
                                <p><strong>Fecha:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($event['date']))) ?></p>
                                <p><strong>Hora:</strong> <?= htmlspecialchars(date('H:i', strtotime($event['time']))) ?>h</p>
                                <p><strong>Lugar:</strong> <?= htmlspecialchars($event['venue']) ?></p>
                                <p class="price">Precio: $<?= number_format(htmlspecialchars($event['price']), 2) ?></p>
                                <?php if ($is_logged_in): ?>
                                    <button class="btn-secondary" style="padding: 0.6rem 1rem; font-size: 0.6rem; font-weight: 500;
                                            background-color: #000; color: #fff; border: none; border-radius: 5px;
                                            display: block; margin: 0 auto; margin-top: auto; margin-bottom: 0.75rem;
                                            transition: background-color 0.3s, color 0.3s;"
                                        onmouseover="this.style.backgroundColor='#fff'; this.style.color='#000';"
                                        onmouseout="this.style.backgroundColor='#000'; this.style.color='#fff';">
                                        Comprar ahora
                                    </button>
                                <?php else: ?>
                                    <button class="btn-secondary" onclick="window.location.href='login.php'" style="padding: 0.6rem 1rem; font-size: 0.6rem; font-weight: 500;
                                            background-color: #000; color: #fff; border: none; border-radius: 5px;
                                            display: block; margin: 0 auto; margin-top: auto; margin-bottom: 0.75rem;
                                            transition: background-color 0.3s, color 0.3s;"
                                        onmouseover="this.style.backgroundColor='#fff'; this.style.color='#000';"
                                        onmouseout="this.style.backgroundColor='#000'; this.style.color='#fff';">
                                        Inicia sesión para comprar
                                    </button>
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
