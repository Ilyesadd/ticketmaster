<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer les informations de l'utilisateur
$user_id = $_SESSION['user_id'];
$sql = "SELECT is_admin FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - TicketMaster</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header>


        <h1>TicketMaster</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="view_tickets.php">Events</a>
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
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                <a href="admin_dashboard.php" class="admin-btn">Accéder au Tableau de Bord Admin</a>
            <?php endif; ?>

            
        </nav>
    </header>

    <main>
        <h2>Welcome to TicketMaster</h2>
        <p>Your go-to platform for buying and selling event tickets.</p>

        <!-- Liste des événements -->
        <div class="events-list">
            <?php if (!empty($events)): ?>
                <?php foreach ($events as $event): ?>
                    <div class="event-item">
                        <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>
                        <p><?php echo htmlspecialchars($event['event_description']); ?></p>
                        <em><?php echo htmlspecialchars($event['event_date']); ?></em>
                        <?php if (isset($_SESSION['username'])): ?>
                            <form action="purchase.php" method="POST">
                                <input type="hidden" name="ticket_id" value="<?php echo $event['id']; ?>">
                                <button type="submit">Acheter</button>
                            </form>
                        <?php else: ?>
                            <p><a href="login.php">Connectez-vous</a> pour acheter des billets</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun événement disponible pour le moment.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 TicketMaster. All rights reserved.</p>
    </footer>
</body>

</html>