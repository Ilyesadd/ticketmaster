<?php
// Initialiser la session
session_start();

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "ticketmaster";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Traitement du formulaire de contact
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    $sql = "INSERT INTO messages (name, email, message) VALUES ('$name', '$email', '$message')";
    if ($conn->query($sql) === TRUE) {
        $success = "Votre message a été envoyé avec succès !";
    } else {
        $error = "Erreur : " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - TicketMaster</title>
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
        <h2>Contactez-nous</h2>
        <?php if (isset($success)): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php elseif (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="contact.php" method="POST">
            <label for="name">Nom:</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="message">Message:</label>
            <textarea id="message" name="message" required></textarea>

            <button type="submit">Envoyer</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2024 TicketMaster. All rights reserved.</p>
    </footer>
</body>

</html>