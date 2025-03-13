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

$user_id = $_SESSION['user_id'];

// Récupérer les commandes de l'utilisateur
$sql = "SELECT tickets.event_name, tickets.event_date, orders.purchase_date
        FROM orders
        JOIN tickets ON orders.ticket_id = tickets.id
        WHERE orders.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - TicketMaster</title>
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
            <a href="account.php">My Account</a>
            <a href="add_ticket.php">Add Ticket</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <h2>My Account</h2>
        <h3>My Orders</h3>
        <?php if (!empty($orders)): ?>
            <ul>
                <?php foreach ($orders as $order): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($order['event_name']); ?></strong><br>
                        <?php echo htmlspecialchars($order['event_date']); ?><br>
                        Purchased on: <?php echo htmlspecialchars($order['purchase_date']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 TicketMaster. All rights reserved.</p>
    </footer>
</body>

</html>