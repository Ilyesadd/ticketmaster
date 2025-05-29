<?php
session_start();
require_once '../config/database.php';

// Vérification que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Traitement de la mise à jour du statut du message (marquer comme lu)
if (isset($_GET['action']) && $_GET['action'] == 'mark_read' && isset($_GET['id'])) {
    $message_id = (int)$_GET['id'];
    
    try {
        // Vérifier que le message appartient bien à l'utilisateur
        $check = getPDO()->prepare("SELECT tm.* FROM ticket_messages tm 
                                  JOIN tickets t ON tm.ticket_id = t.id 
                                  WHERE tm.id = ? AND t.user_id = ?");
        $check->execute([$message_id, $user_id]);
        
        if ($check->rowCount() > 0) {
            // Mettre à jour le statut
            $update = getPDO()->prepare("UPDATE ticket_messages SET status = 'read' WHERE id = ?");
            $update->execute([$message_id]);
            
            $_SESSION['success_message'] = 'Message marqué comme lu.';
        } else {
            $_SESSION['error_message'] = 'Vous n\'êtes pas autorisé à modifier ce message.';
        }
        
        // Redirection pour éviter les soumissions multiples
        header('Location: my_messages.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la mise à jour du statut: " . $e->getMessage();
    }
}

// Récupération des messages pour les tickets de l'utilisateur
try {
    $stmt = getPDO()->prepare("SELECT tm.*, t.event_name, t.id as ticket_id, u.username as sender_name 
                             FROM ticket_messages tm 
                             JOIN tickets t ON tm.ticket_id = t.id 
                             JOIN users u ON tm.sender_id = u.id 
                             WHERE t.user_id = ? 
                             ORDER BY tm.sent_at DESC");
    $stmt->execute([$user_id]);
    $messages = $stmt->fetchAll();
    
    // Comptage des messages non lus
    $stmt = getPDO()->prepare("SELECT COUNT(*) as unread_count 
                             FROM ticket_messages tm 
                             JOIN tickets t ON tm.ticket_id = t.id 
                             WHERE t.user_id = ? AND tm.status = 'unread'");
    $stmt->execute([$user_id]);
    $unread_count = $stmt->fetch()['unread_count'];
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la récupération des messages: " . $e->getMessage();
    $messages = [];
    $unread_count = 0;
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
    <title>Mes Messages - TicketMaster</title>
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
                <h2>Mes Messages</h2>
                <p>Vous avez <span class="badge bg-primary"><?php echo $unread_count; ?></span> message(s) non lu(s).</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="my_tickets.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Mes Tickets</a>
            </div>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if (empty($messages)): ?>
            <div class="alert alert-info">Vous n'avez reçu aucun message concernant vos tickets.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Ticket</th>
                            <th>Expéditeur</th>
                            <th>Message</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $msg): ?>
                            <tr class="<?php echo $msg['status'] == 'unread' ? 'unread' : ''; ?>">
                                <td><?php echo date('d/m/Y H:i', strtotime($msg['sent_at'])); ?></td>
                                <td>
                                    <a href="view.php?id=<?php echo $msg['ticket_id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($msg['event_name']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($msg['sender_name']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($msg['message'])); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($msg['status'] == 'unread'): ?>
                                            <a href="my_messages.php?action=mark_read&id=<?php echo $msg['id']; ?>" class="btn btn-outline-primary" title="Marquer comme lu">
                                                <i class="bi bi-envelope-open"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-outline-secondary" disabled title="Message lu">
                                                <i class="bi bi-envelope-open-fill"></i>
                                            </button>
                                        <?php endif; ?>
                                        <a href="view.php?id=<?php echo $msg['ticket_id']; ?>" class="btn btn-outline-info" title="Voir le ticket">
                                            <i class="bi bi-ticket-detailed"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#replyModal<?php echo $msg['id']; ?>" title="Répondre">
                                            <i class="bi bi-reply"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Modals de réponse pour chaque message -->
    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $msg): ?>
            <div class="modal fade" id="replyModal<?php echo $msg['id']; ?>" tabindex="-1" aria-labelledby="replyModalLabel<?php echo $msg['id']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="replyModalLabel<?php echo $msg['id']; ?>">Répondre à <?php echo htmlspecialchars($msg['sender_name']); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p><strong>Message original:</strong></p>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                </div>
                            </div>
                            <form action="../messages/reply.php" method="POST">
                                <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                <input type="hidden" name="ticket_id" value="<?php echo $msg['ticket_id']; ?>">
                                <input type="hidden" name="receiver_id" value="<?php echo $msg['sender_id']; ?>">
                                <div class="mb-3">
                                    <label for="message<?php echo $msg['id']; ?>" class="form-label">Votre réponse</label>
                                    <textarea class="form-control" id="message<?php echo $msg['id']; ?>" name="message" rows="5" required></textarea>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Envoyer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>