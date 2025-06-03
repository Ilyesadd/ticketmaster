<?php
// Paramètres de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_PORT', '8889'); // Port MAMP par défaut
define('DB_NAME', 'Ticket');
define('DB_USER', 'root');
define('DB_PASS', 'root'); // Mot de passe par défaut pour MAMP

try {
    // Connexion à la base de données avec PDO
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Variable globale pour la connexion à la base de données
    $GLOBALS['pdo'] = $pdo;
    
} catch (PDOException $e) {
    // Affichage d'une erreur en cas d'échec de connexion
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}

// Fonction pour obtenir la connexion PDO
function getPDO() {
    return $GLOBALS['pdo'];
}