<?php
session_start();
require_once '../config/database.php';

// Récupération de tous les tickets disponibles
try {
    $stmt = getPDO()->query('SELECT t.*, u.username FROM tickets t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.event_date ASC');
    $tickets = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Erreur lors de la récupération des tickets: ' . $e->getMessage());
}

// Filtrage par statut si demandé
$status_filter = $_GET['status'] ?? '';
if ($status_filter && in_array($status_filter, ['National', 'International'])) {
    try {
        $stmt = getPDO()->prepare('SELECT t.*, u.username FROM tickets t LEFT JOIN users u ON t.user_id = u.id WHERE t.status = ? ORDER BY t.event_date ASC');
        $stmt->execute([$status_filter]);
        $tickets = $stmt->fetchAll();
    } catch (PDOException $e) {
        die('Erreur lors du filtrage des tickets: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - TicketMaster</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Tickets disponiblesssss</h2>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group">
                    <a href="index.php" class="btn <?php echo empty($status_filter) ? 'btn-primary' : 'btn-outline-primary'; ?>">Tous</a>
                    <a href="index.php?status=National" class="btn <?php echo $status_filter === 'National' ? 'btn-primary' : 'btn-outline-primary'; ?>">National</a>
                    <a href="index.php?status=International" class="btn <?php echo $status_filter === 'International' ? 'btn-primary' : 'btn-outline-primary'; ?>">International</a>
                </div>
            </div>
        </div>
        
        <?php if (empty($tickets)): ?>
            <div class="alert alert-info">Aucun ticket disponible pour le moment.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($tickets as $ticket): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card ticket-card h-100">
                            <div class="card-body position-relative">
                                <span class="ticket-status <?php echo strtolower($ticket['status']); ?>">
                                    <?php echo htmlspecialchars($ticket['status']); ?>
                                </span>
                                <h5 class="card-title"><?php echo htmlspecialchars($ticket['event_name']); ?></h5>
                                <p class="ticket-date">
                                    <i class="bi bi-calendar"></i> 
                                    <?php echo date('d/m/Y H:i', strtotime($ticket['event_date'])); ?>
                                </p>
                                <p class="ticket-location">
                                    <i class="bi bi-geo-alt"></i> 
                                    <?php echo htmlspecialchars($ticket['location']); ?>
                                </p>
                                <p class="ticket-price"><?php echo number_format($ticket['price'], 2, ',', ' '); ?> €</p>
                                <p class="text-muted">Vendeur: <?php echo $ticket['username'] ? htmlspecialchars($ticket['username']) : 'Anonyme'; ?></p>
                            </div>
                            <div class="card-footer">
                                <a href="view.php?id=<?php echo $ticket['id']; ?>" class="btn btn-primary w-100">Voir détails</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css"></script>
</body>
</html>