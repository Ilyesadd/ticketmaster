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

// Traiter la recherche
$searchResults = [];
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['query'])) {
    $query = htmlspecialchars($_GET['query']);
    $sql = "SELECT * FROM tickets WHERE event_name LIKE '%$query%' OR location LIKE '%$query%'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $searchResults[] = $row;
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Events - TicketMaster</title>
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
        <h2>Search Events</h2>
        <form method="GET" action="search.php">
            <input type="text" name="query" placeholder="Search for events or locations" required>
            <button type="submit">Search</button>
        </form>

        <?php if (!empty($searchResults)): ?>
            <h3>Search Results:</h3>
            <?php foreach ($searchResults as $ticket): ?>
                <div class="ticket">
                    <h3><?php echo htmlspecialchars($ticket['event_name']); ?></h3>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($ticket['event_date']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($ticket['location']); ?></p>
                    <p><strong>Price:</strong> $<?php echo htmlspecialchars($ticket['price']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php elseif (isset($_GET['query'])): ?>
            <p>No results found for "<?php echo htmlspecialchars($_GET['query']); ?>".</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 TicketMaster. All rights reserved.</p>
    </footer>
</body>

</html>