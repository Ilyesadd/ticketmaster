<?php
session_start();
require_once '../config/database.php';

// Vérification si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../auth/login.php');
    exit;
}

$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Filtrage par statut si demandé
$status_filter = $_GET['status'] ?? '';
$search_query = $_GET['search'] ?? '';

// Construction de la requête SQL avec filtres
$sql = 'SELECT t.*, u.username FROM tickets t LEFT JOIN users u ON t.user_id = u.id';
$params = [];
$where_clauses = [];

if ($status_filter && in_array($status_filter, ['National', 'International'])) {
    $where_clauses[] = 't.status = ?';
    $params[] = $status_filter;
}

if ($search_query) {
    $where_clauses[] = '(t.event_name LIKE ? OR t.location LIKE ? OR u.username LIKE ?)';
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($where_clauses)) {
    $sql .= ' WHERE ' . implode(' AND ', $where_clauses);
}

$sql .= ' ORDER BY t.event_date ASC';

// Récupération des tickets
try {
    $stmt = getPDO()->prepare($sql);
    $stmt->execute($params);
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
    <title>Gestion des tickets - Administration - TicketMaster</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-ticket-perforated"></i> Gestion des tickets</h2>
            <a href="dashboard.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Retour au tableau de bord</a>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Filtres</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-6">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Nom de l'événement, lieu ou vendeur">
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tous les statuts</option>
                            <option value="National" <?php echo $status_filter === 'National' ? 'selected' : ''; ?>>National</option>
                            <option value="International" <?php echo $status_filter === 'International' ? 'selected' : ''; ?>>International</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Liste des tickets</h5>
            </div>
            <div class="card-body">
                <?php if (empty($tickets)): ?>
                    <div class="alert alert-info">Aucun ticket trouvé.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Événement</th>
                                    <th>Date</th>
                                    <th>Lieu</th>
                                    <th>Prix</th>
                                    <th>Vendeur</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $ticket): ?>
                                    <tr>
                                        <td><?php echo $ticket['id']; ?></td>
                                        <td><?php echo htmlspecialchars($ticket['event_name']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($ticket['event_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($ticket['location']); ?></td>
                                        <td><?php echo number_format($ticket['price'], 2, ',', ' '); ?> €</td>
                                        <td><?php echo $ticket['username'] ? htmlspecialchars($ticket['username']) : 'Anonyme'; ?></td>
                                        <td>
                                            <span class="badge <?php echo $ticket['status'] === 'National' ? 'bg-primary' : 'bg-success'; ?>">
                                                <?php echo htmlspecialchars($ticket['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="../tickets/view.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-info">Voir</a>
                                                <a href="../tickets/edit.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                                                <a href="../tickets/delete.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce ticket ?');">Supprimer</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>