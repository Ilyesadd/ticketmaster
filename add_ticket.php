<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Récupérer l'ID de l'utilisateur connecté

// Traitement du formulaire d'ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $location = $_POST['location'];
    $price = $_POST['price'];

    if (!empty($event_name) && !empty($event_date) && !empty($location) && !empty($price)) {
        // Insérer les données dans la base
        $sql = "INSERT INTO tickets (user_id, event_name, event_date, location, price) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssd", $user_id, $event_name, $event_date, $location, $price);

        if ($stmt->execute()) {
            echo "✅ Ticket ajouté avec succès.";
        } else {
            echo "❌ Erreur lors de l'ajout du ticket.";
        }
    } else {
        echo "❌ Tous les champs sont obligatoires.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Ticket</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Ajouter un Ticket</h1>
        <nav>
        <a href="index.php">Home</a>
            <a href="view_tickets.php">Tickets</a>
            <a href="search.php">Search</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
            <?php if (isset($_SESSION['username'])): ?>
                <a href="account.php">My Account</a>
                <a href="add_ticket.php">Add Ticket</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <form action="add_ticket.php" method="POST">
            <label for="event_name">Nom de l'événement :</label>
            <input type="text" id="event_name" name="event_name" required>

            <label for="event_date">Date de l'événement :</label>
            <input type="date" id="event_date" name="event_date" required>

            <label for="location">Lieu :</label>
            <input type="text" id="location" name="location" required>

            <label for="price">Prix :</label>
            <input type="number" id="price" name="price" step="0.01" required>

            <button type="submit">Ajouter le Ticket</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2024 TicketMaster. Tous droits réservés.</p>
    </footer>
</body>
</html>
