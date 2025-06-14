<?php
session_start(); // Inicia la sesión para poder acceder a sus parámetros y destruirla

// Desmonta todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la cookie de sesión, también es necesario borrarla.
// Esto es vital para que el navegador "olvide" la sesión actual.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000, // Establece la expiración en el pasado
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Finalmente, destruye la sesión del servidor
session_destroy();

// Redirige al usuario a la página de inicio o a la página de login.
// Es buena práctica redirigir a una página que no requiere autenticación.
header("Location: index.php");
exit(); // Es crucial llamar a exit() después de un header() para asegurar la redirección
?>