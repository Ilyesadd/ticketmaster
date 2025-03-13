<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Récupérer l'admin uniquement
    $sql = "SELECT id, username, password, is_admin FROM users WHERE username = ? AND is_admin = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['is_admin'] = 1;

            header('Location: admin_dashboard.php'); // Redirection vers l'espace admin
            exit();
        } else {
            echo "<p class='error'>Mot de passe incorrect.</p>";
        }
    } else {
        echo "<p class='error'>Accès refusé : vous n'êtes pas un administrateur.</p>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Connexion - TicketMaster</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header>
        <h1>Admin - TicketMaster</h1>
    </header>

    <main>
        <h2>Connexion Administrateur</h2>
        <form action="admin_login.php" method="POST">
            <label for="username">Nom d'utilisateur :</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Se connecter</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2024 TicketMaster. Tous droits réservés.</p>
    </footer>
</body>

</html>
