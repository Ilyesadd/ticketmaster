<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est bien un admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}



// Suppression d'un ticket
if (isset($_GET['delete_ticket'])) {
    $ticket_id = $_GET['delete_ticket'];
    $sql = "DELETE FROM tickets WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
}

// Suppression d'un utilisateur
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    $sql = "DELETE FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}

// Récupérer tous les tickets
$tickets = $conn->query("SELECT * FROM tickets");

// Récupérer tous les utilisateurs
$users = $conn->query("SELECT * FROM users");

$messages = $conn->query("SELECT * FROM messages");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Admin - Dashboard</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <h2>Gestion des Tickets</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Nom de l'événement</th>
                <th>Date</th>
                <th>Lieu</th>
                <th>Prix</th>
                <th>Action</th>
            </tr>
            <?php while ($ticket = $tickets->fetch_assoc()): ?>
                <tr>
                    <td><?= $ticket['id'] ?></td>
                    <td><?= $ticket['event_name'] ?></td>
                    <td><?= $ticket['event_date'] ?></td>
                    <td><?= $ticket['location'] ?></td>
                    <td><?= $ticket['price'] ?>€</td>
                    <td>
                        <a href="admin_dashboard.php?delete_ticket=<?= $ticket['id'] ?>" class="delete-btn">Supprimer</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <h2>Gestion des Utilisateurs</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Nom d'utilisateur</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
            <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= $user['username'] ?></td>
                    <td><?= $user['email'] ?></td>
                    <td>
                        <a href="admin_dashboard.php?delete_user=<?= $user['id'] ?>" class="delete-btn">Supprimer</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
        <h2>Gestion des tickets support</h2>
        <table>
            <tr>
                <th>
        
    </main>

    
</body>
</html>
