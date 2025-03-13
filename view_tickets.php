<?php
// Initialiser la session
session_start();

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "ticketmaster";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer les tickets en vente
$sql = "SELECT tickets.*, users.username FROM tickets INNER JOIN users ON tickets.user_id = users.id ORDER BY tickets.event_date DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Erreur de la requête SQL : " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets en Vente - TicketMaster</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header>
        <h1>TicketMaster</h1>
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
        <h2>Tickets en Vente</h2>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='ticket'>";
                echo "<h3>" . htmlspecialchars($row['event_name']) . "</h3>";
                echo "<p><strong>Date:</strong> " . htmlspecialchars($row['event_date']) . "</p>";
                echo "<p><strong>Lieu:</strong> " . htmlspecialchars($row['location']) . "</p>";
                echo "<p><strong>Prix:</strong> $" . htmlspecialchars($row['price']) . "</p>";
                echo "<p><em>Mis en vente par: " . htmlspecialchars($row['username']) . " le " . htmlspecialchars($row['event_date']) . "</em></p>";
                echo "</div>";
            }
        } else {
            echo "<p>Aucun ticket en vente pour le moment.</p>";
        }
        ?>
    </main>

    <footer>
        <p>&copy; 2024 TicketMaster. All rights reserved.</p>
    </footer>
</body>

</html>

<?php
$conn->close();
?>