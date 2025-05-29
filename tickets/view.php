<?php
session_start();
require_once '../config/database.php';

// Vérification de l'ID du ticket
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$ticket_id = (int)$_GET['id'];

// Récupération des informations du ticket
try {
    $stmt = getPDO()->prepare('SELECT t.*, u.username FROM tickets t LEFT JOIN users u ON t.user_id = u.id WHERE t.id = ?');
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        // Ticket non trouvé
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    die('Erreur lors de la récupération du ticket: ' . $e->getMessage());
}

// Récupération des messages de succès ou d'erreur
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($ticket['event_name']); ?> - TicketMaster</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-5">
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center"><?php echo htmlspecialchars($ticket['event_name']); ?></h2>
                    </div>
                    <div class="card-body">
                        <div class="position-relative mb-4">
                            <span class="ticket-status <?php echo strtolower($ticket['status']); ?>">
                                <?php echo htmlspecialchars($ticket['status']); ?>
                            </span>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Détails de l'événement</h5>
                                <p><strong>Date et heure:</strong> <?php echo date('d/m/Y H:i', strtotime($ticket['event_date'])); ?></p>
                                <p><strong>Lieu:</strong> <?php echo htmlspecialchars($ticket['location']); ?></p>
                                <p><strong>Prix:</strong> <span class="ticket-price"><?php echo number_format($ticket['price'], 2, ',', ' '); ?> €</span></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Informations sur le vendeur</h5>
                                <p><strong>Vendeur:</strong> <?php echo $ticket['username'] ? htmlspecialchars($ticket['username']) : 'Anonyme'; ?></p>
                                <p><strong>Ticket mis en vente le:</strong> <?php echo date('d/m/Y', strtotime($ticket['event_date'])); ?></p>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $ticket['user_id']): ?>
                                <div class="alert alert-info">C'est votre ticket.</div>
                                <a href="edit.php?id=<?php echo $ticket['id']; ?>" class="btn btn-warning">Modifier ce ticket</a>
                                <a href="delete.php?id=<?php echo $ticket['id']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce ticket ?');">Supprimer ce ticket</a>
                            <?php elseif (isset($_SESSION['user_id'])): ?>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#contactModal">Contacter le vendeur</button>
                            <?php else: ?>
                                <a href="../auth/login.php" class="btn btn-primary">Connectez-vous pour contacter le vendeur</a>
                            <?php endif; ?>
                            <a href="index.php" class="btn btn-secondary">Retour à la liste des tickets</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $ticket['user_id']): ?>
    <!-- Modal de contact -->
    <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactModalLabel">Contacter le vendeur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="contactForm" action="../messages/send.php" method="POST">
                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                        <div class="mb-3">
                            <label for="message" class="form-label">Votre message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Envoyer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>