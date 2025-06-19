<?php
session_start(); // Inicia la sesión al principio de tu script

require_once 'auth_functions.php'; // <-- Agrega esta línea

// Verifica si el organizador NO ha iniciado sesión.
// Si no hay un 'org_id' en la sesión, redirige a la página de login de organizadores.
if (!isset($_SESSION['org_id'])) {
    header('Location: loginOrg.php'); // Redirige a la página de inicio de sesión del organizador
    exit(); // Es crucial usar exit() después de un header() para detener la ejecución del script
}

$error = '';    // Para mensajes de error
$success = '';  // Para mensajes de éxito

$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$date = trim($_POST['date'] ?? '');
$time = trim($_POST['time'] ?? '');
$venue = trim($_POST['venue'] ?? '');
$city = trim($_POST['city'] ?? '');
$price = trim($_POST['price'] ?? '');
$tickets = trim($_POST['tickets'] ?? '');
$image = trim($_POST['image'] ?? '');

if($name && $description && $date && $time && $venue && $city && $price && $tickets && $image) {

    $org_id = $_SESSION['org_id']; // <-- Obtén el ID del organizador de la sesión
    $result = registerEvent($name, $description, $date, $time, $venue, $city, $price, $tickets, $image, $org_id);

    $success = 'Evento creado exitosamente.';
} else {

    $error = 'Por favor, completa todos los campos.';
}

// Si el código llega hasta aquí, significa que el organizador está logueado.
$is_org_logged_in = true; // Establecemos esta variable como verdadera
// Obtenemos el email del organizador desde la sesión y lo sanitizamos para mostrarlo de forma segura.
$org_email = htmlspecialchars($_SESSION['org_email']);

// A partir de aquí, puedes incluir el resto de la lógica PHP y el HTML
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Nuevo evento</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" type="image/png" href="img/logoblanco.png">
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

    <style>
        h1 {
            font-size: 1rem;
            font-family: 'Moderniz';
            color: #000;
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

        .success-message a {
            color: #2d7738;
            font-weight: 600;
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

        .auth-links {
            text-align: center;
        }

        .auth-links a {
            color: var(--color-primary);
            font-weight: 600;
            text-decoration: none;
            font-family: 'Moderniz';
            font-size: 0.9rem;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }

        .back-home {
            position: absolute;
            top: 2rem;
            left: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--color-primary);
            font-family: 'Moderniz';
            font-weight: 600;
            text-decoration: none;
            transition: color var(--transition);
            z-index: 2;
        }

        .back-home:hover {
            color: var(--color-text-muted);
        }

        .password-requirements {
            font-size: 0.8rem;
            color: var(--color-text-muted);
            margin-top: 0.25rem;
            font-family: 'Inter';
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
            /* Alinea el label a la izquierda del form */
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
<<<<<<< HEAD

        .page-title {
            text-align: center;
            font-size: 2.5rem;
            margin: 1rem 0 2rem;
            color: #000;
            font-family: 'Moderniz', sans-serif;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
=======
>>>>>>> 9bb875744ab856626d59d94827162d4ada9323aa
    </style>

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
 
        <h1 class="page-title">NUEVO EVENTO</h1>
        <div class="event-layout">
            <form class="event-form" method="POST" action="">
                <label for="event-name">Nombre del evento</label>
                <input type="text" id="event-name" name="name" required>

                <label for="event-description">Breve descripción y detalles</label>
                <input type="text" id="event-description" name="description" required>

                <label for="event-date">Fecha</label>
                <input type="date" id="event-date" name="date" required>

                <label for="event-time">Hora</label>
                <input type="time" id="event-time" name="time" required>

                <label for="event-venue">Lugar</label>
                <input type="text" id="event-venue" name="venue" required>

                <label for="event-city">Ciudad</label>
                <input type="text" id="event-city" name="city" required>

                <label for="event-price">Precio unitario de boleta</label>
                <input type="number" id="event-price" name="price" required>

                <label for="event-tickets">Cantidad de boletas disponibles</label>
                <input type="number" id="event-tickets" name="tickets" required>

                <label for="event-image">URL de la imagen del evento</label>
                <input type="url" id="event-image" name="image" required>

                <button type="submit" class="btnprimarycontacto">Crear evento</button>
            </form>

            <div class="event-preview">
                <h1>Preview</h1>
                <h2 id="preview-name">Nombre del evento</h2>
                <p id="preview-description">Descripción del evento</p>
                <p><strong>Fecha:</strong> <span id="preview-date">Fecha del evento</span></p>
                <p><strong>Hora:</strong> <span id="preview-time">Hora del evento</span></p>
                <p><strong>Lugar:</strong> <span id="preview-venue">Lugar del evento</span></p>
                <p><strong>Precio:</strong> $<span id="preview-price">Precio del evento</span></p>
                <p><strong>Cantidad de boletas disponibles:</strong> <span id="preview-tickets">Cantidad de
                        boletas</span></p>
                <img id="preview-image" src="default-image.jpg" alt="Imagen del evento"
                    style="max-width: 100%; height: auto;">
            </div>
        </div>
    </main>


</body>

<script>
    const fields = {
        'event-name': 'preview-name',
        'event-description': 'preview-description',
        'event-date': 'preview-date',
        'event-time': 'preview-time',
        'event-venue': 'preview-venue',
        'event-price': 'preview-price',
        'event-tickets': 'preview-tickets',
        'event-image': 'preview-image'
    };

    for (const [inputId, previewId] of Object.entries(fields)) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);

        if (inputId === 'event-image') {
            input.addEventListener('input', () => {
                preview.src = input.value || 'default-image.jpg';
            });
        } else {
            input.addEventListener('input', () => {
                preview.textContent = input.value || preview.dataset.default || '';
            });
        }
    }
</script>
</html>
