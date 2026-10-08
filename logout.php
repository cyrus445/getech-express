<?php
// ============================================================
// FICHIER : logout.php
// Rôle     : Déconnexion de l'utilisateur
//            Détruit la session et redirige vers index.php
// ============================================================

session_start();

// Suppression de toutes les variables de session
$_SESSION = [];

// Destruction du cookie de session (bonne pratique)
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destruction de la session côté serveur
session_destroy();

// Redirection vers la page de connexion
header('Location: index.php');
exit;
