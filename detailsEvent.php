<?php
session_start();

// --- Configuración e Inclusiones ---
require_once 'auth_functions.php';
require_once 'database.php';

// --- Gestión de Sesión de Usuario ---
$is_logged_in = false;
$user_name = '';
$user_role = '';

if (isset($_SESSION['user_id'])) {
    $is_logged_in = true;
    $user_name = htmlspecialchars($_SESSION['user_name'] ?? '');
    $user_role = htmlspecialchars($_SESSION['user_role'] ?? 'user');
} elseif (isset($_SESSION['org_id'])) {
    $is_logged_in = true;
    $user_name = htmlspecialchars($_SESSION['org_name'] ?? '');
    $user_role = 'organizer';
}

$user_first_name = '';
if (!empty($user_name)) {
    $user_first_name = htmlspecialchars(explode(' ', $user_name)[0]);
}

// --- Obtener el ID del evento de la URL ---
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($event_id <= 0) {
    header("Location: events.php");
    exit();
}

$event_id = $_GET['id'] ?? 0;
$recommended_events = getRandomEvents(5, $event_id);

// --- Obtener los detalles del evento específico ---
$event = getEventById($event_id);

if (!$event) {
    header("Location: events.php");
    exit();
}

// --- Manejo de Mensajes ---
$message = isset($_SESSION['message']) ? htmlspecialchars($_SESSION['message']) : '';
$error = isset($_SESSION['error']) ? htmlspecialchars($_SESSION['error']) : '';

unset($_SESSION['message']);
unset($_SESSION['error']);

// --- Formatear datos del evento ---
$formatted_date = date('d/m/Y', strtotime($event['date']));
$formatted_time = date('H:i', strtotime($event['time']));
$formatted_price = number_format($event['price'], 2);
$tickets_available = $event['available_tickets'] > 0 ? $event['available_tickets'] : 'Agotado';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>StarBillet - <?= htmlspecialchars($event['name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" type="image/png" href="img/logoblanco.png">
    <style>
        .event-detail-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .event-image {
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            aspect-ratio: 1/1;
            object-fit: cover;
        }
        
        .event-info {
            display: flex;
            flex-direction: column;
        }
        
        .event-title {
            color: var(--color-primary);
            margin: 0 0 5px 0;
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1.2;
        }
        
        .event-venue {
            font-size: 1.8rem;
            color: #333;
            margin: 0 0 15px 0;
            font-weight: 600;
        }
        
        .event-date-time {
            font-size: 1.5rem;
            color: #444;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .event-location {
            font-size: 1.5rem;
            color: #555;
            display: flex;
        }
        
        .event-status {
            background-color: #f5f5f5;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.95rem;
            display: inline-block;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .price-section {
            margin: 15px 0;
        }
        
        .event-price {
            font-size: 2rem;
            font-weight: bold;
            margin: 5px 0;
            color: #000;
        }
        
        .price-note {
            font-size: 1.2rem;
            color: #666;
            margin: 5px 0 15px 0;
        }
        
        .btn-buy {
            background-color: var(--color-accent);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 6px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            margin: 10px 0 20px 0;
            text-transform: uppercase;
        }
        
        .btn-buy:hover {
            background-color: var(--color-accent-dark);
            color: black;
        }
        
        .btn-buy:disabled {
            background-color: #ddd;
            cursor: not-allowed;
        }
        
        .info-note {
            font-size: 1.1rem;
            color: #666;
            margin: 0 0 25px 0;
            line-height: 1.5;
            padding: 12px;
            background-color: #f9f9f9;
            border-radius: 6px;
        }
        
        .event-description {
            margin: 5px 0;
            line-height: 1.6;
        }
        
        .event-description h3 {
            font-size: 1.5rem;
            margin-bottom: 12px;
            color: #000;
        }
        
        .event-description p {
            font-size: 1.4rem;
            color: #555;
        }
        
        .free-price {
            color: #000;
            font-weight: bold;
        }

        .event-address {
            margin: 10px 0;
            color: #555;
            font-size: 1.2rem;
        }

        .door-opening {
            font-size: 1.2rem;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-map {
            background: none;
            border: none;
            color: var(--color-accent);
            text-decoration: underline;
            cursor: pointer;
            padding: 5px 0;
            font-size: 0.9rem;
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

        <script>
        function initMap() {
            // Solo se ejecutará si hay un elemento map
            if (document.getElementById('map')) {
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ address: "<?= $full_address ?>" }, (results, status) => {
                    if (status === "OK") {
                        new google.maps.Map(document.getElementById("map"), {
                            center: results[0].geometry.location,
                            zoom: 15
                        });
                    }
                });
            }
        }
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=TU_API_KEY&callback=initMap"></script>

    <main class="container">
        <div class="event-detail-container">
            <div>
                <img src="<?= htmlspecialchars($event['image_url']) ?>" alt="<?= htmlspecialchars($event['name']) ?>" class="event-image">
            </div>
            
            <div class="event-info">
                <h1 class="event-title"><?= htmlspecialchars($event['name']) ?></h1>
                <div class="event-venue"><?= htmlspecialchars($event['venue']) ?></div>
                
                <div class="event-date-time">
                    <?= date('D, j M, H:i', strtotime($event['date'] . ' ' . $event['time'])) ?>
                </div>
                
                <div class="event-location">
                    <?= htmlspecialchars($event['city']) ?>
                </div>
                
                <?php if ($event['available_tickets'] <= 0): ?>
                    <div class="event-status">Agotado</div>
                <?php endif; ?>
                
                <div class="price-section">
                    <div class="event-price <?= $event['price'] == 0 ? 'free-price' : '' ?>">
                        <?= $event['price'] == 0 ? 'GRATIS' : '$' . $formatted_price ?>
                    </div>
                    <div class="price-note">Este es el precio final. Sin cargos ocultos.</div>
                </div>
                
                <?php if ($is_logged_in): ?>
                    <button class="btn-buy" <?= $event['available_tickets'] <= 0 ? 'disabled' : '' ?>>
                        <?= $event['available_tickets'] > 0 ? 'Comprar entradas' : 'Entradas agotadas' ?>
                    </button>
                <?php else: ?>
                    <button class="btn-buy" onclick="window.location.href='login.php'">
                        Iniciar sesión para comprar
                    </button>
                <?php endif; ?>
                
                <div class="info-note">
                    StarBillet protege contra la reventa ilegal. Tus entradas están seguras con nosotros.
                </div>
                
                <div class="event-description">
                    <h3>Información del evento</h3>
                    <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                </div>
            </div>
        </div>

        <div class="location-details" style="margin: 40px auto 0; max-width: 800px; border-top: 1px solid #eee; padding-top: 30px;">
            <h2 style="font-size: 1.8rem; color: #333; margin-bottom: 20px; text-align: center;">📍 Detalles de ubicación</h2>
            
            <div style="text-align: center;">
                <h3 style="font-size: 1.7rem; margin: 0 0 10px 0; color: var(--color-primary);"><?= htmlspecialchars($event['venue']) ?></h3>
                <p style="color: #555; line-height: 1.6; margin-bottom: 15px; font-size: 1.3rem;">
                    <?= htmlspecialchars($event['full_address'] ?? $event['venue'].', '.$event['city']) ?>
                </p>
                
                <?php 
                // Calcular hora de apertura (2 horas antes del evento)
                $event_time = strtotime($event['date'] . ' ' . $event['time']);
                $door_opening_time = date('H:i', strtotime('-2 hours', $event_time));
                ?>
                
                <p style="display: flex; align-items: center; justify-content: center; color: #555; font-size: 1.3rem;">
                    Apertura de puertas: <?= $door_opening_time ?> (2 horas antes del evento)
                </p>
                
                <div style="margin-top: 25px; display: flex; justify-content: center; gap: 15px;">
                    <a href="https://maps.google.com/?q=<?= urlencode($event['full_address'] ?? $event['venue'].', '.$event['city']) ?>" 
                    target="_blank"
                    style="background-color: var(--color-accent); color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; min-width: 180px;">
                        Abrir en Google Maps
                    </a>
                </div>
            </div>
        </div>
        </div>
    </div>

    <?php
    $random_events = getRandomEvents(5, $event_id); // trae 5 eventos aleatorios para mostrar
    ?>

    <div class="recommended-events" style="margin: 80px auto; max-width: 1200px;">
        <h2 style="text-align: center; font-size: 1.8rem; margin-bottom: 30px; color: #333;">
            Eventos que te pueden interesar
        </h2>
        
        <div class="events-carousel" style="position: relative;">
            <div class="events-slider" style="display: flex; overflow-x: auto; scroll-snap-type: x mandatory; gap: 20px; padding: 10px 0; scrollbar-width: none;">
                <?php foreach ($random_events as $event): ?>
                    <div class="event-card" style="flex: 0 0 280px; scroll-snap-align: start; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                        <img src="<?= htmlspecialchars($event['image_url']) ?>" 
                            alt="<?= htmlspecialchars($event['name']) ?>" 
                            style="width: 100%; height: 180px; object-fit: cover;">
                        
                        <div style="padding: 15px;">
                            <h3 style="margin: 0 0 10px 0; font-size: 1.1rem; color: #333;">
                                <?= htmlspecialchars($event['name']) ?>
                            </h3>
                            
                            <p style="margin: 5px 0; font-size: 0.9rem; color: #555;">
                                <strong>Fecha:</strong> <?= date('d/m/Y', strtotime($event['date'])) ?>
                            </p>
                            
                            <p style="margin: 5px 0; font-size: 0.9rem; color: #555;">
                                <strong>Hora:</strong> <?= date('H:i', strtotime($event['time'])) ?>h
                            </p>
                            
                            <p style="margin: 5px 0; font-size: 0.9rem; color: #555;">
                                <strong>Lugar:</strong> <?= htmlspecialchars($event['venue']) ?>
                            </p>
                            
                            <p style="margin: 10px 0; font-weight: bold; color: #000;">
                                Precio: $<?= number_format($event['price'], 2) ?>
                            </p>
                            
                            <a href="detailsEvent.php?id=<?= $event['id'] ?>" 
                            style="display: block; text-align: center; padding: 8px 12px; 
                                    background-color: #000; color: white; border-radius: 4px; 
                                    text-decoration: none; font-size: 0.8rem; transition: all 0.3s;"
                            onmouseover="this.style.backgroundColor='#fff'; this.style.color='#000'; this.style.border='1px solid #000';"
                            onmouseout="this.style.backgroundColor='#000'; this.style.color='#fff'; this.style.border='none';">
                                Ver detalles
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>


    </main>


    <footer>
        &copy; 2025 StarBillet. Todos los derechos reservados.
    </footer>
</body>

</html>