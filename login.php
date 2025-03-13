<?php
session_start();

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Récupérer l'utilisateur avec son rôle admin
    $sql = "SELECT id, username, password, is_admin FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Vérifier le mot de passe avec password_verify()
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin']; // Stocker is_admin dans la session

            // Redirection selon le rôle
            if ($user['is_admin'] == 1) {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit();
        } else {
            echo "Mot de passe incorrect.";
        }
    } else {
        echo "Nom d'utilisateur incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TicketMaster</title>
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
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </nav>
    </header>

    <main>
        <h2>Login</h2>
        <form action="login.php" method="POST">
            <label for="username">Nom d'utilisateur :</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Connexion</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2024 TicketMaster. Tous droits réservés.</p>
    </footer>
</body>
</html>
