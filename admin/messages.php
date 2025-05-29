<?php
session_start();
require_once '../config/database.php';

// Vérification que l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../auth/login.php');
    exit;
}

// Traitement de la mise à jour du statut du message (marquer comme lu/non lu)
if (isset($_GET['action']) && $_GET['action'] == 'toggle_status' && isset($_GET['id'])) {
    $message_id = (int)$_GET['id'];
    
    try {
        // Récupérer le statut actuel
        $stmt = $pdo->prepare("SELECT status FROM messages WHERE id = ?");
        $stmt->execute([$message_id]);
        $message = $stmt->fetch();
        
        if ($message) {
            // Inverser le statut
            $new_status = ($message['status'] == 'read') ? 'unread' : 'read';
            
            // Mettre à jour le statut
            $update = $pdo->prepare("UPDATE messages SET status = ? WHERE id = ?");
            $update->execute([$new_status, $message_id]);
            
            // Redirection pour éviter les soumissions multiples
            header('Location: messages.php');
            exit;
        }
    } catch (PDOException $e) {
        $error = "Erreur lors de la mise à jour du statut: " . $e->getMessage();
    }
}

// Suppression d'un message
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $message_id = (int)$_GET['id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([$message_id]);
        
        // Redirection pour éviter les soumissions multiples
        header('Location: messages.php');
        exit;
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression du message: " . $e->getMessage();
    }
}

// Récupération de tous les messages
try {
    $stmt = $pdo->query("SELECT m.*, u.username 
                         FROM messages m 
                         LEFT JOIN users u ON m.user_id = u.id 
                         ORDER BY m.sent_at DESC");
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des messages: " . $e->getMessage();
    $messages = [];
}

// Comptage des messages non lus
try {
    $stmt = $pdo->query("SELECT COUNT(*) as unread_count FROM messages WHERE status = 'unread'");
    $unread_count = $stmt->fetch()['unread_count'];
} catch (PDOException $e) {
    $unread_count = 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Messages - Administration TicketMaster</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .unread {
            font-weight: bold;
            background-color: rgba(13, 110, 253, 0.1);
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Gestion des Messages</h2>
                <p>Vous avez <span class="badge bg-primary"><?php echo $unread_count; ?></span> message(s) non lu(s).</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Retour au tableau de bord</a>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (empty($messages)): ?>
            <div class="alert alert-info">Aucun message n'a été reçu pour le moment.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Utilisateur</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $msg): ?>
                            <tr class="<?php echo $msg['status'] == 'unread' ? 'unread' : ''; ?>">
                                <td><?php echo date('d/m/Y H:i', strtotime($msg['sent_at'])); ?></td>
                                <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                <td><a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>"><?php echo htmlspecialchars($msg['email']); ?></a></td>
                                <td><?php echo nl2br(htmlspecialchars($msg['message'])); ?></td>
                                <td>
                                    <?php if ($msg['user_id']): ?>
                                        <a href="#" class="badge bg-info text-decoration-none"><?php echo htmlspecialchars($msg['username']); ?></a>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Visiteur</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="messages.php?action=toggle_status&id=<?php echo $msg['id']; ?>" class="btn btn-outline-primary" title="<?php echo $msg['status'] == 'read' ? 'Marquer comme non lu' : 'Marquer comme lu'; ?>">
                                            <i class="bi <?php echo $msg['status'] == 'read' ? 'bi-envelope' : 'bi-envelope-open'; ?>"></i>
                                        </a>
                                        <a href="messages.php?action=delete&id=<?php echo $msg['id']; ?>" class="btn btn-outline-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
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