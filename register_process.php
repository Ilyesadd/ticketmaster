<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['validRegister'])) {
    // Connexion à la base de données
    $servername = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "ticketmaster";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Récupération des données du formulaire
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    // Vérifier si l'utilisateur existe déjà
    $sql = "SELECT * FROM users WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Nom d'utilisateur déjà pris.";
    } else {
        // Hacher le mot de passe avant de l'insérer
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insérer le nouvel utilisateur
        $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $username, $hashed_password, $email);

        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $stmt->insert_id;

            header("Location: account.php");
            exit();
        } else {
            echo "Erreur : " . $stmt->error;
        }
    }

    $conn->close();
}
?>
