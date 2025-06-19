<?php
session_start();
require_once 'database.php';
require_once 'auth_functions.php'; 

$is_logged_in = isset($_SESSION['user_id']);
$user_email = $is_logged_in ? htmlspecialchars($_SESSION['user_email']) : '';
$user_first_name = $is_logged_in && isset($_SESSION['user_name']) ? htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) : '';

$tickets = [];
if ($is_logged_in) {
    $tickets = getTicketsByUserId($_SESSION['user_id']);
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mis Tickets - StarBillet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" type="image/png" href="img/logoblanco.png">
    <style>
        .ticket-card {
            background-color: var(--color-white);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s;
            cursor: pointer;
        }
        .ticket-card:hover {
            transform: translateY(-5px);
        }
        .ticket-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }
        .ticket-card-content {
            padding: 15px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .ticket-card-content h3 {
            margin-top: 0;
            color: var(--color-primary);
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        .ticket-card-content p {
            font-size: 0.9rem;
            color: var(--color-text-muted);
            margin-bottom: 5px;
        }
        .ticket-card-content .code {
            font-weight: 700;
            color: var(--color-accent);
            font-size: 0.95rem;
            margin-top: auto;
        }
        .tickets-grid {
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

        .status-active {
            color: #27ae60; /* verde */
            font-weight: bold;
        }
        .status-past {
            color: #e74c3c; /* rojo */
            font-weight: bold;
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
                <a href="events.php" role="menuitem" tabindex="0">Eventos</a>
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

    <main>
        <section class="container">
            <h2>Mis Tickets</h2>
            <?php if (!$is_logged_in): ?>
                <p>Debes iniciar sesión para ver tus tickets.</p>
            <?php elseif (empty($tickets)): ?>
                <p>No tienes tickets comprados aún.</p>
            <?php else: ?>
                <div class="tickets-grid">
                    <?php foreach ($tickets as $ticket): ?>
                    <?php
                        $event_date = $ticket['date'];
                        $is_past = (strtotime($event_date) < strtotime(date('Y-m-d')));
                        $status_class = $is_past ? 'status-past' : 'status-active';
                        $status_label = $is_past ? 'Evento finalizado' : 'Disponible';
                    ?>                        
                        <div class="ticket-card" onclick="window.location.href='detailsEvent.php?id=<?= $ticket['event_id'] ?>'">
                            <img src="<?= htmlspecialchars($ticket['image_url']) ?>"
                                alt="<?= htmlspecialchars($ticket['event_name']) ?>">
                            <div class="ticket-card-content">
                                <h3><?= htmlspecialchars($ticket['event_name']) ?></h3>
                                <p><strong>Fecha:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($ticket['date']))) ?></p>
                                <p><strong>Hora:</strong> <?= htmlspecialchars(date('H:i', strtotime($ticket['time']))) ?>h</p>
                                <p><strong>Lugar:</strong> <?= htmlspecialchars($ticket['venue']) ?></p>
                                <p><strong>Cantidad:</strong> <?= htmlspecialchars($ticket['quantity']) ?></p>
                                <p><strong>Total pagado:</strong> $<?= number_format($ticket['total_price'], 2) ?></p>
                                <p class="code"><strong>Código Ticket:</strong> <?= htmlspecialchars($ticket['ticket_code']) ?></p>
                                <p>Comprado el: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($ticket['purchase_date']))) ?></p>
                                <p>Estado: <span class="<?= $status_class ?>"><?= htmlspecialchars($status_label) ?></span></p>
                                
                                
                                
                                
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
