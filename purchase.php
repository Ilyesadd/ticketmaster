<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = $_POST['ticket_id'];
    $user_id = $_SESSION['user_id'];

    // Vérifier si le billet existe
    $sql = "SELECT * FROM tickets WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Erreur lors de la préparation de la requête : " . $conn->error);
    }
    $stmt->bind_param('i', $ticket_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Le billet n'existe pas.");
    }

    // Insérer l'achat dans la table orders
    $sql = "INSERT INTO orders (user_id, ticket_id, purchase_date) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Erreur lors de la préparation de la requête d'insertion : " . $conn->error);
    }
    $stmt->bind_param('ii', $user_id, $ticket_id);

    if ($stmt->execute()) {
        header('Location: account.php');
        exit();
    } else {
        die("Erreur lors de l'insertion de l'achat : " . $stmt->error);
    }
} else {
    header('Location: index.php');
    exit();
}
?>