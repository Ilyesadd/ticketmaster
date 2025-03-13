<?php
// Initialiser la session
session_start();

// Vérifier si l'utilisateur est connecté (ajouter une vérification d'admin si nécessaire)
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ticketmaster";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer les messages de contact
$sql = "SELECT * FROM messages ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages de Contact - TicketMaster</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header>
        <h1>TicketMaster</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="#">Events</a>
            <a href="#">About</a>
            <a href="contact.php">Contact</a>
            <?php if (isset($_SESSION['username'])): ?>
                <a href="account.php">My Account</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <h2>Messages de Contact</h2>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='message'>";
                echo "<p><strong>Nom:</strong> " . $row['name'] . "</p>";
                echo "<p><strong>Email:</strong> " . $row['email'] . "</p>";
                echo "<p><strong>Message:</strong> " . $row['message'] . "</p>";
                echo "<p><em>Envoyé le: " . $row['created_at'] . "</em></p>";
                echo "</div>";
            }
        } else {
            echo "<p>Aucun message trouvé.</p>";
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