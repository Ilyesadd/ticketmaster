<?php
session_start();

// Destruction de toutes les variables de session
$_SESSION = [];

// Destruction du cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruction de la session
session_destroy();

// Redirection vers la page d'accueil
header('Location: ../tickets/index.php');
exit;