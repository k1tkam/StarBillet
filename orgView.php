<?php
session_start(); // Inicia la sesión al principio de tu script

// Verifica si el organizador NO ha iniciado sesión.
// Si no hay un 'org_id' en la sesión, redirige a la página de login de organizadores.
if (!isset($_SESSION['org_id'])) {
    header('Location: loginOrg.php'); // Redirige a la página de inicio de sesión del organizador
    exit(); // Es crucial usar exit() después de un header() para detener la ejecución del script
}

// Si el código llega hasta aquí, significa que el organizador está logueado.
$is_org_logged_in = true; // Establecemos esta variable como verdadera
// Obtenemos el email del organizador desde la sesión y lo sanitizamos para mostrarlo de forma segura.
$org_email = htmlspecialchars($_SESSION['org_email']);

// A partir de aquí, puedes incluir el resto de la lógica PHP y el HTML
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Panel de organización</title>
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
                <a href="newEvent.php" role="menuitem" tabindex="0">Nuevo evento</a>
                <a href="myEvents.php" role="menuitem" tabindex="0">mis eventos</a>
                <?php if ($is_org_logged_in): ?>
                    <a href="logout.php" role="menuitem" tabindex="0">Cerrar sesion</a>
                <?php else: ?>
                    <a href="loginOrg.php" role="menuitem" tabindex="0">Iniciar sesion</a>
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
        <h1>Organizacion <?php echo htmlspecialchars(explode(' ', $_SESSION['org_name'])[0]); ?></h1>
        <p>Desde aquí puedes gestionar tus eventos y tickets.</p>
    </main>

    <div class="hero-video-organizer">
        <video autoplay muted loop playsinline preload="auto" poster="poster.jpg">
            <source src="vid/organizador.mp4" type="video/mp4">
            Tu navegador no soporta el video HTML5.
        </video>
    </div>

    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@700&display=swap");

        h1 {
            font-family: 'Moderniz';
            font-size: 2.5rem;
            color: #000000;
            text-align: center;
            margin-top: 2rem;
        }

        p {
            font-size: 1.2rem;
            color: #555;
            text-align: center;
            margin-top: -2rem;
        }

        .hero-video-organizer {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
            margin: 2rem auto;
            margin-top: 1rem;
            max-width: 35rem;
            width: 100%;
        }

        .hero-video-organizer video {
            width: 100%;
            max-width: 35rem;
            height: auto;
            display: block;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        @media screen and (max-width: 768px) {
            .hero-video-organizer {
                padding: 1rem;
            }

            .hero-video-organizer video {
                max-width: 90%;
            }
        }
    </style>

    <footer>
        &copy; 2025 StarBillet. Todos los derechos reservados.
    </footer>

</body>

</html>