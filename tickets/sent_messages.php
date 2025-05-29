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
        $check = getPDO()->prepare("SELECT * FROM ticket_messages WHERE id = ? AND receiver_id = ?");
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
        header('Location: sent_messages.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur lors de la mise à jour du statut: " . $e->getMessage();
    }
}

// Récupération des messages envoyés par l'utilisateur et des réponses reçues
try {
    // Messages envoyés
    $stmt = getPDO()->prepare("SELECT tm.*, t.event_name, t.id as ticket_id, u.username as receiver_name 
                             FROM ticket_messages tm 
                             JOIN tickets t ON tm.ticket_id = t.id 
                             JOIN users u ON tm.receiver_id = u.id 
                             WHERE tm.sender_id = ? 
                             ORDER BY tm.sent_at DESC");
    $stmt->execute([$user_id]);
    $sent_messages = $stmt->fetchAll();
    
    // Messages reçus (réponses)
    $stmt = getPDO()->prepare("SELECT tm.*, t.event_name, t.id as ticket_id, u.username as sender_name 
                             FROM ticket_messages tm 
                             JOIN tickets t ON tm.ticket_id = t.id 
                             JOIN users u ON tm.sender_id = u.id 
                             WHERE tm.receiver_id = ? 
                             ORDER BY tm.sent_at DESC");
    $stmt->execute([$user_id]);
    $received_messages = $stmt->fetchAll();
    
    // Comptage des messages non lus
    $stmt = getPDO()->prepare("SELECT COUNT(*) as unread_count 
                             FROM ticket_messages 
                             WHERE receiver_id = ? AND status = 'unread'");
    $stmt->execute([$user_id]);
    $unread_count = $stmt->fetch()['unread_count'];
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la récupération des messages: " . $e->getMessage();
    $sent_messages = [];
    $received_messages = [];
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
        .nav-tabs .nav-link {
            color: #0d6efd;
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            color: #000;
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
                <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Retour aux tickets</a>
            </div>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <ul class="nav nav-tabs mb-4" id="messagesTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="received-tab" data-bs-toggle="tab" data-bs-target="#received" type="button" role="tab" aria-controls="received" aria-selected="true">
                    Messages reçus <span class="badge bg-primary"><?php echo $unread_count; ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="sent-tab" data-bs-toggle="tab" data-bs-target="#sent" type="button" role="tab" aria-controls="sent" aria-selected="false">
                    Messages envoyés
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="messagesTabContent">
            <!-- Onglet Messages reçus -->
            <div class="tab-pane fade show active" id="received" role="tabpanel" aria-labelledby="received-tab">
                <?php if (empty($received_messages)): ?>
                    <div class="alert alert-info">Vous n'avez reçu aucun message.</div>
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
                                <?php foreach ($received_messages as $msg): ?>
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
                                                    <a href="sent_messages.php?action=mark_read&id=<?php echo $msg['id']; ?>" class="btn btn-outline-primary" title="Marquer comme lu">
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
            
            <!-- Onglet Messages envoyés -->
            <div class="tab-pane fade" id="sent" role="tabpanel" aria-labelledby="sent-tab">
                <?php if (empty($sent_messages)): ?>
                    <div class="alert alert-info">Vous n'avez envoyé aucun message.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Ticket</th>
                                    <th>Destinataire</th>
                                    <th>Message</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sent_messages as $msg): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($msg['sent_at'])); ?></td>
                                        <td>
                                            <a href="view.php?id=<?php echo $msg['ticket_id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($msg['event_name']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($msg['receiver_name']); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars($msg['message'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="view.php?id=<?php echo $msg['ticket_id']; ?>" class="btn btn-outline-info" title="Voir le ticket">
                                                    <i class="bi bi-ticket-detailed"></i>
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
        </div>
    </div>
    
    <!-- Modals de réponse pour chaque message reçu -->
    <?php if (!empty($received_messages)): ?>
        <?php foreach ($received_messages as $msg): ?>
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