<?php
session_start();
require_once '../config/database.php';

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Redirection vers la page de connexion
    header('Location: ../auth/login.php');
    exit;
}

$error = '';
$success = '';

// Traitement du formulaire d'ajout de ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_name = trim($_POST['event_name'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $price = $_POST['price'] ?? '';
    $status = $_POST['status'] ?? 'National';
    
    // Validation des champs
    if (empty($event_name) || empty($event_date) || empty($location) || empty($price)) {
        $error = 'Veuillez remplir tous les champs';
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = 'Le prix doit être un nombre positif';
    } elseif (!in_array($status, ['National', 'International'])) {
        $error = 'Statut invalide';
    } else {
        try {
            // Formatage de la date
            $formatted_date = date('Y-m-d H:i:s', strtotime($event_date));
            
            // Insertion du nouveau ticket
            $stmt = getPDO()->prepare('INSERT INTO tickets (user_id, event_name, event_date, location, price, status) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$_SESSION['user_id'], $event_name, $formatted_date, $location, $price, $status]);
            
            $success = 'Votre ticket a été ajouté avec succès !';
            
            // Réinitialisation des champs du formulaire après succès
            $event_name = $event_date = $location = $price = '';
            $status = 'National';
        } catch (PDOException $e) {
            $error = 'Erreur lors de l\'ajout du ticket: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un ticket - TicketMaster</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center">Ajouter un ticket</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="event_name" class="form-label">Nom de l'événement</label>
                                <input type="text" class="form-control" id="event_name" name="event_name" value="<?php echo htmlspecialchars($event_name ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="event_date" class="form-label">Date et heure de l'événement</label>
                                <input type="datetime-local" class="form-control" id="event_date" name="event_date" value="<?php echo htmlspecialchars($event_date ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="location" class="form-label">Lieu</label>
                                <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($location ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="price" class="form-label">Prix (€)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($price ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Statut</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="status_national" value="National" <?php echo (!isset($status) || $status === 'National') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status_national">National</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="status_international" value="International" <?php echo (isset($status) && $status === 'International') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status_international">International</label>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Ajouter le ticket</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>