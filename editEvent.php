<?php
session_start();
require_once 'auth_functions.php';
require_once 'database.php';

// Verificar sesión de organizador
if (!isset($_SESSION['org_id'])) {
    header('Location: loginOrg.php');
    exit();
}

// Validar ID de evento
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: myEvents.php');
    exit();
}

$event_id = (int)$_GET['id'];
$org_id = (int)$_SESSION['org_id'];

// Verificar propiedad del evento
if (!isEventOrganizer($event_id, $org_id)) {
    header('Location: myEvents.php');
    exit();
}

// Obtener datos del evento
$event = getEventById($event_id);
if (!$event) {
    header('Location: myEvents.php');
    exit();
}

// Procesar formulario
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $tickets = trim($_POST['available_tickets'] ?? '');
    $image = trim($_POST['image_url'] ?? '');

    if ($name && $description && $date && $time && $venue && $city && $price && $tickets && $image) {
        try {
            $success = updateEvent($event_id, $name, $description, $date, $time, $venue, $city, $price, $tickets, $image);
            
            if ($success) {
                $success = 'Evento actualizado exitosamente.';
                $event = getEventById($event_id);
            } else {
                $error = 'Error al actualizar el evento. Por favor, intenta nuevamente.';
            }
        } catch (PDOException $e) {
            $error = 'Error de base de datos: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor, completa todos los campos.';
    }
}

// Datos para el formulario
$name = htmlspecialchars($event['name']);
$description = htmlspecialchars($event['description']);
$date = htmlspecialchars($event['date']);
$time = htmlspecialchars($event['time']);
$venue = htmlspecialchars($event['venue']);
$city = htmlspecialchars($event['city']);
$price = htmlspecialchars($event['price']);
$tickets = htmlspecialchars($event['available_tickets']);
$image = htmlspecialchars($event['image_url']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Editar evento</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" type="image/png" href="img/logoblanco.png">
    <style>
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
            text-align: center;
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

        .event-layout {
            display: flex;
            gap: 2rem;
            padding: 2rem;
            align-items: flex-start;
            justify-content: center;
        }

        .event-form {
            flex: 1;
            max-width: 50%;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            background-color: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .event-preview {
            flex: 1;
            max-width: 50%;
            background-color: #f9f9f9;
            padding: 2rem;
            border-radius: 12px;
            border: 1px solid #ccc;
            font-family: 'Inter';
        }

        .event-form label {
            align-self: flex-start;
            font-weight: 500;
            font-size: 0.95rem;
            margin-bottom: -0.5rem;
        }

        .event-form input {
            width: 100%;
            max-width: fill;
            padding: 0.6rem 1rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .event-form button {
            margin-top: 1.5rem;
            padding: 0.8rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            background-color: var(--color-accent);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color var(--transition), transform var(--transition), color var(--transition);
        }

        .event-form button:hover {
            background-color: var(--color-accent-hover);
            color: black;
            transform: scale(1.05);
        }

        .page-title {
            text-align: center;
            font-size: 2.5rem;
            margin: 1rem 0 2rem;
            color: #000;
            font-family: 'Moderniz', sans-serif;
            text-transform: uppercase;
            letter-spacing: 2px;
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
            text-align: center;
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
                <a href="orgView.php" role="menuitem" tabindex="0">Volver</a>
                <a href="myEvents.php" role="menuitem" tabindex="0">mis eventos</a>
                <a href="logout.php" role="menuitem" tabindex="0">Cerrar sesion</a>
            </div>
        </nav>
    </header>

        <main>
            <h1 class="page-title">EDITAR EVENTO</h1>
            
            <div class="event-layout">
                <form class="event-form" method="POST" action="">
                    <?php if ($error): ?>
                        <div class="error-message"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="success-message"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <label for="event-name">Nombre del evento</label>
                    <input type="text" id="event-name" name="name" value="<?php echo $name; ?>" required>

                    <label for="event-description">Breve descripción y detalles</label>
                    <input type="text" id="event-description" name="description" value="<?php echo $description; ?>" required>

                    <label for="event-date">Fecha</label>
                    <input type="date" id="event-date" name="date" value="<?php echo $date; ?>" required>

                    <label for="event-time">Hora</label>
                    <input type="time" id="event-time" name="time" value="<?php echo $time; ?>" required>

                    <label for="event-venue">Lugar</label>
                    <input type="text" id="event-venue" name="venue" value="<?php echo $venue; ?>" required>

                    <label for="event-city">Ciudad</label>
                    <input type="text" id="event-city" name="city" value="<?php echo $city; ?>" required>

                    <label for="event-price">Precio unitario de boleta </label>
                    <input type="number" id="event-price" name="price" value="<?php echo $price; ?>" required>

                    <label for="event-tickets">Cantidad de boletas disponibles</label>
                    <input type="number" id="event-tickets" name="available_tickets" value="<?php echo $tickets; ?>" required>

                    <label for="event-image">URL de la imagen del evento</label>
                    <input type="url" id="event-image" name="image_url" value="<?php echo $image; ?>" required>

                    <button type="submit" class="btnprimarycontacto">ACTUALIZAR EVENTO</button>
                </form>

                <div class="event-preview">
                    <h1>Vista Previa</h1>
                    <h2 id="preview-name"><?php echo $name; ?></h2>
                    <p id="preview-description"><?php echo $description; ?></p>
                    <p><strong>Fecha:</strong> <span id="preview-date"><?php echo $date; ?></span></p>
                    <p><strong>Hora:</strong> <span id="preview-time"><?php echo $time; ?></span></p>
                    <p><strong>Lugar:</strong> <span id="preview-venue"><?php echo $venue; ?></span></p>
                    <p><strong>Precio:</strong> $<span id="preview-price"><?php echo number_format($price, 2); ?></span></p>
                    <p><strong>Boletas disponibles:</strong> <span id="preview-tickets"><?php echo $tickets; ?></span></p>
                    <img id="preview-image" src="<?php echo $image; ?>" alt="Imagen del evento" style="max-width: 100%; height: auto; border-radius: 8px;">
                </div>
            </div>
        </main>

        <script>
            // Script para actualizar la vista previa en tiempo real
            const fields = {
                'event-name': {target: 'preview-name', type: 'text'},
                'event-description': {target: 'preview-description', type: 'text'},
                'event-date': {target: 'preview-date', type: 'text'},
                'event-time': {target: 'preview-time', type: 'text'},
                'event-venue': {target: 'preview-venue', type: 'text'},
                'event-price': {target: 'preview-price', type: 'price'},
                'event-tickets': {target: 'preview-tickets', type: 'text'},
                'event-image': {target: 'preview-image', type: 'image'}
            };

            document.querySelectorAll('input, textarea').forEach(element => {
                const fieldId = element.id;
                if (fields[fieldId]) {
                    element.addEventListener('input', updatePreview);
                }
            });

            function updatePreview(e) {
                const fieldId = e.target.id;
                const config = fields[fieldId];
                const target = document.getElementById(config.target);
                
                if (config.type === 'image') {
                    target.src = e.target.value || 'img/evento-default.jpg';
                } else if (config.type === 'price') {
                    target.textContent = parseFloat(e.target.value || 0).toFixed(2);
                } else {
                    target.textContent = e.target.value;
                }
            }

            // Animación del logo
            const gif = document.getElementById("gif-logo");
            const staticLogo = document.getElementById("static-logo");
            setTimeout(() => {
                gif.style.display = "none";
                staticLogo.style.display = "block";
            }, 2000);
        </script>
    </body>
</html>