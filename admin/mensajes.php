<?php
session_start();
include '../includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Process actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $id = (int)$_POST['id'];
        
        if ($_POST['action'] == 'mark_as_read') {
            $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: mensajes.php?success=marked");
            exit;
        }
        
        if ($_POST['action'] == 'delete') {
            $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: mensajes.php?success=deleted");
            exit;
        }
    }
}

// Get messages
$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$unread_count = count(array_filter($messages, fn($m) => !$m['is_read']));
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-envelope"></i> Mensajes de Contacto
                <?php if ($unread_count > 0): ?>
                    <span class="badge bg-danger"><?= $unread_count ?></span>
                <?php endif; ?>
            </h1>
        </div>

        <!-- Success Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                <?php if ($_GET['success'] == 'marked'): ?>
                    Mensaje marcado como leído correctamente.
                <?php elseif ($_GET['success'] == 'deleted'): ?>
                    Mensaje eliminado correctamente.
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Messages List -->
        <?php if (empty($messages)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5>No hay mensajes</h5>
                    <p class="text-muted">No hay mensajes de contacto pendientes.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Estado</th>
                                    <th>Remitente</th>
                                    <th>Correo</th>
                                    <th>Mensaje</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $message): ?>
                                <tr class="<?= $message['is_read'] ? '' : 'table-warning' ?>">
                                    <td>
                                        <?php if (!$message['is_read']): ?>
                                            <span class="badge bg-warning">Nuevo</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Leído</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($message['name']) ?></strong>
                                    </td>
                                    <td>
                                        <a href="mailto:<?= htmlspecialchars($message['email']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($message['email']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="message-preview" style="max-width: 300px;">
                                            <?= nl2br(htmlspecialchars(substr($message['message'], 0, 100))) ?>
                                            <?php if (strlen($message['message']) > 100): ?>
                                                <span class="text-muted">...</span>
                                                <button class="btn btn-sm btn-link p-0" onclick="toggleMessage(<?= $message['id'] ?>)">
                                                    Ver más
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                        <div id="full-message-<?= $message['id'] ?>" class="d-none">
                                            <?= nl2br(htmlspecialchars($message['message'])) ?>
                                            <button class="btn btn-sm btn-link p-0" onclick="toggleMessage(<?= $message['id'] ?>)">
                                                Ver menos
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <small>
                                            <?= date('d/m/Y H:i', strtotime($message['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if (!$message['is_read']): ?>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="mark_as_read">
                                                    <input type="hidden" name="id" value="<?= $message['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Marcar como leído">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <a href="mailto:<?= htmlspecialchars($message['email']) ?>?subject=Re: Contacto desde Vultur Restaurant" 
                                               class="btn btn-sm btn-outline-primary" title="Responder">
                                                <i class="fas fa-reply"></i>
                                            </a>
                                            
                                            <form method="post" class="d-inline" 
                                                  onsubmit="return confirm('¿Estás seguro de eliminar este mensaje?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $message['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleMessage(messageId) {
    const preview = document.querySelector(`tr:has(#full-message-${messageId}) .message-preview`);
    const fullMessage = document.getElementById(`full-message-${messageId}`);
    
    if (preview.classList.contains('d-none')) {
        preview.classList.remove('d-none');
        fullMessage.classList.add('d-none');
    } else {
        preview.classList.add('d-none');
        fullMessage.classList.remove('d-none');
    }
}

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.classList.contains('show')) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        }, 5000);
    });
});
</script>

</body>
</html>
