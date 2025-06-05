<?php
session_start();
require_once '../config/database.php';

// Vérification si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../auth/login.php');
    exit;
}

// Récupération des statistiques
try {
    // Nombre total de tickets
    $stmt = getPDO()->query('SELECT COUNT(*) FROM tickets');
    $total_tickets = $stmt->fetchColumn();
    
    // Nombre total d'utilisateurs
    $stmt = getPDO()->query('SELECT COUNT(*) FROM users');
    $total_users = $stmt->fetchColumn();
    

    // Nombre de messages non lus
    $stmt = getPDO()->query("SELECT COUNT(*) FROM messages WHERE status = 'unread'");
    $unread_messages = $stmt->fetchColumn();
    
    // Nombre total de messages
    $stmt = getPDO()->query('SELECT COUNT(*) FROM messages');
    $total_messages = $stmt->fetchColumn();
    
    // Nombre de tickets par statut
    $stmt = getPDO()->query('SELECT status, COUNT(*) as count FROM tickets GROUP BY status');
    $tickets_by_status = $stmt->fetchAll();
    
    // Tickets récemment ajoutés
    $stmt = getPDO()->query('SELECT t.*, u.username FROM tickets t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.id DESC LIMIT 5');
    $recent_tickets = $stmt->fetchAll();
    
} catch (PDOException $e) {
    die('Erreur lors de la récupération des statistiques: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Administration - TicketMaster</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-5 admin-dashboard">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-speedometer2"></i> Tableau de bord administrateur</h2>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-ticket-perforated"></i> Tickets</h5>
                        <p class="card-text display-4"><?php echo $total_tickets; ?></p>
                        <a href="tickets.php" class="btn btn-primary">Gérer les tickets</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center mb-3 users">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-people"></i> Utilisateurs</h5>
                        <p class="card-text display-4"><?php echo $total_users; ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center mb-3 messages">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-envelope"></i> Messages</h5>
                        <p class="card-text display-4"><?php echo $total_messages; ?>
                            <?php if ($unread_messages > 0): ?>
                                <span class="badge bg-danger"><?php echo $unread_messages; ?> non lu(s)</span>
                            <?php endif; ?>
                        </p>
                        <a href="messages.php" class="btn btn-info">Gérer les messages</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-globe"></i> Statuts</h5>
                        <div class="mt-3">
                            <?php foreach ($tickets_by_status as $status): ?>
                                <div class="mb-2">
                                    <span class="badge <?php echo $status['status'] === 'National' ? 'bg-primary' : 'bg-success'; ?>">
                                        <?php echo $status['status']; ?>
                                    </span>
                                    <span class="ms-2"><?php echo $status['count']; ?> tickets</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-ticket-perforated"></i> Tickets récemment ajoutés</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_tickets)): ?>
                            <p class="text-center">Aucun ticket récent.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Événement</th>
                                            <th>Date</th>
                                            <th>Prix</th>
                                            <th>Vendeur</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_tickets as $ticket): ?>
                                            <tr>
                                                <td><?php echo $ticket['id']; ?></td>
                                                <td><?php echo htmlspecialchars($ticket['event_name']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($ticket['event_date'])); ?></td>
                                                <td><?php echo number_format($ticket['price'], 2, ',', ' '); ?> €</td>
                                                <td><?php echo $ticket['username'] ? htmlspecialchars($ticket['username']) : 'Anonyme'; ?></td>
                                                <td>
                                                    <span class="badge <?php echo $ticket['status'] === 'National' ? 'bg-primary' : 'bg-success'; ?>">
                                                        <?php echo htmlspecialchars($ticket['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="../tickets/view.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-info">Voir</a>
                                                    <a href="../tickets/edit.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                                                    <a href="../tickets/delete.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce ticket ?');">Supprimer</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="tickets.php" class="btn btn-primary">Voir tous les tickets</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>