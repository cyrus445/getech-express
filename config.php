<?php
// ============================================================
// FICHIER : config.php
// Rôle     : Connexion à la base de données MySQL
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // À adapter selon votre environnement
define('DB_PASS', '');           // À adapter selon votre environnement
define('DB_NAME', 'getech_express');

// Création de la connexion PDO (plus sécurisée que mysqli)
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('<div class="alert alert-danger m-4">Erreur de connexion à la base de données : ' . htmlspecialchars($e->getMessage()) . '</div>');
}
