<?php
session_start();
require_once '../config/database.php';

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Redirection vers la page de connexion
    header('Location: ../auth/login.php');
    exit;
}

$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

// Récupération des tickets de l'utilisateur
try {
    $stmt = getPDO()->prepare('SELECT * FROM tickets WHERE user_id = ? ORDER BY event_date ASC');
    $stmt->execute([$_SESSION['user_id']]);
    $tickets = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Erreur lors de la récupération des tickets: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes tickets - TicketMaster</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Mes tickets</h2>
            <a href="add.php" class="btn btn-primary">Ajouter un ticket</a>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (empty($tickets)): ?>
            <div class="alert alert-info">Vous n'avez pas encore ajouté de tickets.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Événement</th>
                            <th>Date</th>
                            <th>Lieu</th>
                            <th>Prix</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ticket['event_name']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($ticket['event_date'])); ?></td>
                                <td><?php echo htmlspecialchars($ticket['location']); ?></td>
                                <td><?php echo number_format($ticket['price'], 2, ',', ' '); ?> €</td>
                                <td>
                                    <span class="badge <?php echo $ticket['status'] === 'National' ? 'bg-primary' : 'bg-success'; ?>">
                                        <?php echo htmlspecialchars($ticket['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                                    <a href="delete.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce ticket ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>