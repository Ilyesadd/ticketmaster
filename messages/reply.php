<?php
session_start();
require_once '../config/database.php';

// Vérification que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Vérification des données du formulaire
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['message_id']) || empty($_POST['ticket_id']) || empty($_POST['receiver_id']) || empty($_POST['message'])) {
    $_SESSION['error_message'] = 'Données invalides. Veuillez réessayer.';
    header('Location: ../tickets/my_messages.php');
    exit;
}

$message_id = (int)$_POST['message_id'];
$ticket_id = (int)$_POST['ticket_id'];
$receiver_id = (int)$_POST['receiver_id'];
$message_content = trim($_POST['message']);
$sender_id = $_SESSION['user_id'];

// Vérification que l'utilisateur est bien le propriétaire du ticket ou l'expéditeur du message original
try {
    $stmt = getPDO()->prepare('SELECT t.*, tm.sender_id as original_sender_id 
                              FROM tickets t 
                              LEFT JOIN ticket_messages tm ON tm.id = ? 
                              WHERE t.id = ?');
    $stmt->execute([$message_id, $ticket_id]);
    $ticket_info = $stmt->fetch();
    
    if (!$ticket_info) {
        $_SESSION['error_message'] = 'Ticket introuvable.';
        header('Location: ../tickets/my_messages.php');
        exit;
    }
    
    // Vérification que l'utilisateur est soit le propriétaire du ticket, soit l'expéditeur du message original
    if ($sender_id != $ticket_info['user_id'] && $sender_id != $ticket_info['original_sender_id']) {
        $_SESSION['error_message'] = 'Vous n\'êtes pas autorisé à répondre à ce message.';
        header('Location: ../tickets/my_messages.php');
        exit;
    }
    
    // Insertion de la réponse dans la table ticket_messages
    $stmt = getPDO()->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, receiver_id, message, status) VALUES (?, ?, ?, ?, 'unread')");
    $stmt->execute([
        $ticket_id,
        $sender_id,
        $receiver_id,
        $message_content
    ]);
    
    // Récupération des informations de l'expéditeur pour la table messages (admin)
    $stmt_user = getPDO()->prepare('SELECT username, email FROM users WHERE id = ?');
    $stmt_user->execute([$sender_id]);
    $sender = $stmt_user->fetch();
    
    // Construction du message avec les informations du ticket pour l'admin
    $message_with_context = "Réponse concernant le ticket: " . htmlspecialchars($ticket_info['event_name']) . " (ID: {$ticket_id})\n\n";
    $message_with_context .= $message_content;
    
    // Insertion dans la table messages originale pour l'admin
    $stmt = getPDO()->prepare("INSERT INTO messages (user_id, name, email, message, status) VALUES (?, ?, ?, ?, 'unread')");
    $stmt->execute([
        $sender_id,
        $sender['username'],
        $sender['email'],
        $message_with_context
    ]);
    
    $_SESSION['success_message'] = 'Votre réponse a été envoyée avec succès.';
    header('Location: ../tickets/my_messages.php');
    exit;
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erreur lors de l\'envoi de la réponse: ' . $e->getMessage();
    header('Location: ../tickets/my_messages.php');
    exit;
}