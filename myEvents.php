<?php
session_start();
require_once 'auth_functions.php';
require_once 'database.php';

// Verificación robusta del organizador
if (!isOrganizerLoggedIn()) {
    header('Location: loginOrg.php');
    exit();
}

// Obtener datos del organizador con validación
$org_id = isset($_SESSION['org_id']) ? (int)$_SESSION['org_id'] : 0;
$org_name = isset($_SESSION['org_name']) ? htmlspecialchars($_SESSION['org_name']) : 'Organizador';

// Obtener eventos con manejo de errores
try {
    $events = getOrganizerEvents($org_id);
} catch (Exception $e) {
    $error = "Error al cargar eventos. Por favor intenta más tarde.";
    $events = ['future' => [], 'past' => []];
}

// Mensajes flash mejorados
$message = isset($_SESSION['message']) ? htmlspecialchars($_SESSION['message']) : '';
$error = isset($_SESSION['error']) ? htmlspecialchars($_SESSION['error']) : (isset($error) ? $error : '');
unset($_SESSION['message']);
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Eventos - StarBillet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="img/logoblanco.png">
    <style>
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .event-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s;
        }
        
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
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
            color: #000;
            font-size: 1.3rem;
            margin-bottom: 10px;
        }
        
        .event-card-content p {
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 5px;
        }
        
        .price {
            font-weight: 700;
            color: #000;
            font-size: 1.1rem;
            margin-top: auto;
        }
        
        .event-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-edit, .btn-delete {
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }
        
        .btn-edit {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-edit:hover {
            background-color: #3e8e41;
        }
        
        .btn-delete {
            background-color: #f44336;
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #d32f2f;
        }
        
        .section-title {
            margin-top: 40px;
            margin-bottom: 20px;
            color: #000;
            font-size: 1.8rem;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .no-events {
            text-align: center;
            color: #777;
            margin: 40px 0;
        }
        
        .create-event-btn {
            display: inline-block;
            background-color: #000;
            color: white;
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s;
        }
        
        .create-event-btn:hover {
            background-color: #333;
            transform: translateY(-2px);
        }

        .events-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .events-title {
            font-size: 2rem;
            color: #000;
            font-family: 'Moderniz', sans-serif;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
            position: relative;
            display: inline-block;
        }

        .events-title::after {
            content: '';
            display: block;
            width: 50%;
            height: 3px;
            background: var(--color-accent);
            margin: 0.5rem auto 0;
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
                <a href="newEvent.php" role="menuitem" tabindex="0">Nuevo evento</a>
                <a href="myEvents.php" role="menuitem" tabindex="0">Mis eventos</a>
                <a href="logout.php" role="menuitem" tabindex="0">Cerrar sesión</a>
            </div>
        </nav>
    </header>

    <main class="container">
        <div class="events-header">
            <h1 class="events-title">MIS EVENTOS</h1>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Eventos Futuros -->
        <?php if (!empty($events['future'])): ?>
            <div class="events-grid">
                <?php foreach ($events['future'] as $event): ?>
                    <?php 
                        // Formateo de fechas con manejo de errores
                        $event_date = !empty($event['date']) ? date('d/m/Y', strtotime($event['date'])) : 'Fecha no definida';
                        $event_time = !empty($event['time']) ? date('H:i', strtotime($event['time'])) . 'h' : 'Hora no definida';
                        $event_price = isset($event['price']) ? number_format((float)$event['price'], 2) : '0.00';
                        $event_image = !empty($event['image_url']) ? htmlspecialchars($event['image_url']) : 'img/evento-default.jpg';
                    ?>
                    <div class="event-card">
                        <img src="<?= $event_image ?>" alt="<?= htmlspecialchars($event['name']) ?>">
                        <div class="event-card-content">
                            <h3><?= htmlspecialchars($event['name']) ?></h3>
                            <p><strong>Fecha:</strong> <?= $event_date ?></p>
                            <p><strong>Hora:</strong> <?= $event_time ?></p>
                            <p><strong>Lugar:</strong> <?= htmlspecialchars($event['venue']) ?></p>
                            <p class="price">$<?= $event_price ?></p>
                            <div class="event-actions">
                                <div class="event-actions" style="padding: 0.6rem 1rem; font-size: 1rem; font-weight: 500;
                                            background-color: #000; color: #fff; border: none; border-radius: 5px;
                                            display: block; margin: 0 auto; margin-top: auto; margin-bottom: 0.75rem;
                                            transition: background-color 0.3s, color 0.3s;">
                                    <a href="editEvent.php?id=<?= $event['id'] ?>">Editar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-events">No tienes eventos futuros programados.</p>
            <a href="newEvent.php" class="create-event-btn">Crear nuevo evento</a>
        <?php endif; ?>
        
        <h2 class="section-title">Eventos Pasados últimos 3 meses</h2>
        <?php if (!empty($events['past'])): ?>
            <div class="events-grid">
                <?php foreach ($events['past'] as $event): ?>
                    <div class="event-card">
                        <img src="<?php echo htmlspecialchars($event['image_url']); ?>" alt="<?php echo htmlspecialchars($event['name']); ?>">
                        <div class="event-card-content">
                            <h3><?php echo htmlspecialchars($event['name']); ?></h3>
                            <p><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($event['date'])); ?></p>
                            <p><strong>Hora:</strong> <?php echo date('H:i', strtotime($event['time'])); ?>h</p>
                            <p><strong>Lugar:</strong> <?php echo htmlspecialchars($event['venue']); ?></p>
                            <p class="price">$<?php echo number_format($event['price'], 2); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-events">No tienes eventos en los últimos 3 meses.</p>
        <?php endif; ?>
    </main>

    <footer>
        &copy; 2025 StarBillet. Todos los derechos reservados.
    </footer>

    <script>
        const gif = document.getElementById("gif-logo");
        const staticLogo = document.getElementById("static-logo");
        setTimeout(() => {
            gif.style.display = "none";
            staticLogo.style.display = "block";
        }, 2000);
    </script>
</body>
</html>