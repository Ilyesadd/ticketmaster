<?php
session_start();
require_once 'config/database.php';

$message = '';
$error = '';

// Traitement du formulaire de contact
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et validation des données
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $msg = trim($_POST['message'] ?? '');
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Validation des champs
    if (empty($name)) {
        $error = "Veuillez entrer votre nom.";
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez entrer une adresse email valide.";
    } elseif (empty($msg)) {
        $error = "Veuillez entrer votre message.";
    } else {
        // Insertion du message dans la base de données
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (user_id, name, email, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $name, $email, $msg]);
            $message = "Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.";
            
            // Réinitialisation des champs après envoi réussi
            $name = $email = $msg = '';
        } catch (PDOException $e) {
            $error = "Une erreur est survenue lors de l'envoi du message: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - TicketMaster</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">Contactez-nous</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required><?php echo htmlspecialchars($msg ?? ''); ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Envoyer</button>
                        </form>
                    </div>
                </div>
                
                <div class="card mt-4 shadow">
                    <div class="card-header bg-secondary text-white">
                        <h3 class="mb-0">Informations de contact</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Adresse :</strong> 123 Rue du Commerce, 75001 Paris, France</p>
                        <p><strong>Email :</strong> contact@ticketmaster.fr</p>
                        <p><strong>Téléphone :</strong> +33 1 23 45 67 89</p>
                        <p><strong>Horaires :</strong> Du lundi au vendredi, de 9h à 18h</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>