<?php
$host = 'localhost'; 
$db   = 'ticketmaster'; 
$user = 'ilyes'; 
$pass = 'TonMotDePasseFort'; 

try { 
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass); 
    // succès 
} catch (PDOException $e) { 
    die("Erreur de connexion à la base de données : " . $e->getMessage()); 
}