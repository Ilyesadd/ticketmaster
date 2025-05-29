<?php
session_start();
require_once '../config/database.php';

// Vérification que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Vérification des données du formulaire
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['ticket_id']) || empty($_POST['message'])) {
    $_SESSION['error_message'] = 'Données invalides. Veuillez réessayer.';
    header('Location: ../tickets/index.php');
    exit;
}

$ticket_id = (int)$_POST['ticket_id'];
$message_content = trim($_POST['message']);
$sender_id = $_SESSION['user_id'];

// Récupération des informations du ticket et du vendeur
try {
    $stmt = getPDO()->prepare('SELECT t.*, u.username, u.id as vendor_id FROM tickets t LEFT JOIN users u ON t.user_id = u.id WHERE t.id = ?');
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        $_SESSION['error_message'] = 'Ticket introuvable.';
        header('Location: ../tickets/index.php');
        exit;
    }
    
    // Vérification que l'utilisateur n'est pas le vendeur lui-même
    if ($sender_id == $ticket['user_id']) {
        $_SESSION['error_message'] = 'Vous ne pouvez pas vous envoyer un message à vous-même.';
        header('Location: ../tickets/view.php?id=' . $ticket_id);
        exit;
    }
    
    // Insertion du message dans la nouvelle table ticket_messages
    $stmt = getPDO()->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, receiver_id, message, status) VALUES (?, ?, ?, ?, 'unread')");
    
    // Insertion du message
    $stmt->execute([
        $ticket_id,
        $sender_id,
        $ticket['user_id'],
        $message_content
    ]);
    
    // Conserver également dans la table messages pour la compatibilité avec l'admin
    $stmt_user = getPDO()->prepare('SELECT username, email FROM users WHERE id = ?');
    $stmt_user->execute([$sender_id]);
    $sender = $stmt_user->fetch();
    
    // Construction du message avec les informations du ticket
    $message_with_context = "Message concernant le ticket: " . htmlspecialchars($ticket['event_name']) . " (ID: {$ticket_id})\n\n";
    $message_with_context .= $message_content;
    
    // Insertion dans la table messages originale pour l'admin
    $stmt = getPDO()->prepare("INSERT INTO messages (user_id, name, email, message, status) VALUES (?, ?, ?, ?, 'unread')");
    $stmt->execute([
        $sender_id,
        $sender['username'],
        $sender['email'],
        $message_with_context
    ]);
    
    $_SESSION['success_message'] = 'Votre message a été envoyé au vendeur avec succès.';
    header('Location: ../tickets/view.php?id=' . $ticket_id);
    exit;
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erreur lors de l\'envoi du message: ' . $e->getMessage();
    header('Location: ../tickets/view.php?id=' . $ticket_id);
    exit;
}