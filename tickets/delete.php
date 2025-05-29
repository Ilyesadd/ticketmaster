<?php
session_start();
require_once '../config/database.php';

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Vérification de l'ID du ticket
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: my_tickets.php');
    exit;
}

$ticket_id = (int)$_GET['id'];
$redirect_to_admin = false;

// Récupération des informations du ticket
try {
    $stmt = getPDO()->prepare('SELECT * FROM tickets WHERE id = ?');
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        // Ticket non trouvé
        header('Location: my_tickets.php');
        exit;
    }
    
    // Vérification que l'utilisateur est bien le propriétaire du ticket
    // ou un administrateur
    if ($ticket['user_id'] != $_SESSION['user_id'] && $_SESSION['is_admin'] != 1) {
        header('Location: my_tickets.php');
        exit;
    }
    
    // Si c'est un admin qui supprime et qu'il n'est pas le propriétaire
    if ($_SESSION['is_admin'] == 1 && $ticket['user_id'] != $_SESSION['user_id']) {
        $redirect_to_admin = true;
    }
    
    // Vérification s'il y a des commandes liées à ce ticket
    $stmt = getPDO()->prepare('SELECT COUNT(*) FROM orders WHERE ticket_id = ?');
    $stmt->execute([$ticket_id]);
    $order_count = $stmt->fetchColumn();
    
    if ($order_count > 0) {
        // Il y a des commandes liées, on ne peut pas supprimer
        $_SESSION['error_message'] = 'Ce ticket ne peut pas être supprimé car il est lié à des commandes.';
        
        if ($redirect_to_admin) {
            header('Location: ../admin/tickets.php');
        } else {
            header('Location: my_tickets.php');
        }
        exit;
    }
    
    // Suppression du ticket
    $stmt = getPDO()->prepare('DELETE FROM tickets WHERE id = ?');
    $stmt->execute([$ticket_id]);
    
    $_SESSION['success_message'] = 'Le ticket a été supprimé avec succès.';
    
    if ($redirect_to_admin) {
        header('Location: ../admin/tickets.php');
    } else {
        header('Location: my_tickets.php');
    }
    exit;
    
} catch (PDOException $e) {
    die('Erreur lors de la suppression du ticket: ' . $e->getMessage());
}