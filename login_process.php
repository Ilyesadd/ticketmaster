<?php
session_start();

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ticketmaster";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Vérifier si les champs sont remplis
if (!isset($_POST['username'], $_POST['password'])) {
    die("Veuillez remplir tous les champs.");
}

$username = trim($_POST['username']);
$password = $_POST['password'];

// Préparer et exécuter la requête
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Vérifier le mot de passe
    if (password_verify($password, $row['password'])) {
        $_SESSION['username'] = $row['username'];
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['is_admin'] = $row['is_admin']; // OK

        // Rediriger selon le rôle
        if ($row['is_admin'] == 1) {
            header("Location: admin_dashboard.php"); // Page admin
        } else {
            header("Location: account.php"); // Page utilisateur normal
        }
        exit();
    } else {
        echo "Mot de passe incorrect.";
    }
} else {
    echo "Aucun utilisateur trouvé avec ce nom.";
}

// Fermer la connexion
$stmt->close();
$conn->close();

?>
