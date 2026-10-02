<?php
session_start();
include '../includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Add new extra
if (isset($_POST['add_extra'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO extras (name, price, type) VALUES (?, ?, ?)");
        $stmt->execute([
            trim($_POST['name']),
            (int) $_POST['price'],
            $_POST['type']
        ]);
        header('Location: admin_extras.php?success=added');
        exit;
    } catch (Exception $e) {
        header('Location: admin_extras.php?error=add_failed');
        exit;
    }
}

// Delete extra
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM extras WHERE id = ?");
        $stmt->execute([(int)$_GET['delete']]);
        header('Location: admin_extras.php?success=deleted');
        exit;
    } catch (Exception $e) {
        header('Location: admin_extras.php?error=delete_failed');
        exit;
    }
}

// Get all extras
$extras = $pdo->query("SELECT * FROM extras ORDER BY type, name")->fetchAll();

// Group extras by type
$grouped_extras = [];
foreach ($extras as $extra) {
    $grouped_extras[$extra['type']][] = $extra;
}
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-plus-circle"></i> Gestionar Extras
            </h1>
        </div>

        <!-- Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                <?php if ($_GET['success'] === 'added'): ?>
                    Extra agregado correctamente.
                <?php elseif ($_GET['success'] === 'deleted'): ?>
                    Extra eliminado correctamente.
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                Error al procesar la solicitud.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Add Extra Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-plus"></i> Agregar Nuevo Extra
                </h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="name" class="form-label">Nombre del extra *</label>
                            <input type="text" name="name" id="name" class="form-control" 
                                   placeholder="Ej: Coca Cola, Papas Grandes" required>
                        </div>
                        <div class="col-md-2">
                            <label for="price" class="form-label">Precio</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="price" id="price" class="form-control" 
                                       value="0" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="type" class="form-label">Tipo *</label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="bebida">Bebida</option>
                                <option value="papas">Papas</option>
                                <option value="salsa">Salsas (gratis)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" name="add_extra" class="btn btn-primary d-block w-100">
                                <i class="fas fa-plus"></i> Agregar Extra
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Existing Extras -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list"></i> Extras Registrados (<?= count($extras) ?>)
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($extras)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-plus-circle fa-3x text-muted mb-3"></i>
                        <h5>No hay extras registrados</h5>
                        <p class="text-muted">Agrega tu primer extra usando el formulario anterior.</p>
                    </div>
                <?php else: ?>
                    <?php foreach (['bebida', 'papas', 'salsa'] as $type): ?>
                        <?php if (isset($grouped_extras[$type])): ?>
                            <div class="mb-4">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-<?= $type === 'bebida' ? 'tint' : ($type === 'papas' ? 'leaf' : 'pepper-hot') ?>"></i>
                                    <?= ucfirst($type) ?>s (<?= count($grouped_extras[$type]) ?>)
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Precio</th>
                                                <th width="100">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($grouped_extras[$type] as $extra): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($extra['name']) ?></strong>
                                                    </td>
                                                    <td>
                                                        <?php if ($extra['price'] > 0): ?>
                                                            <span class="badge bg-success">$<?= number_format($extra['price'], 0, ',', '.') ?></span>
                                                        <?php else: ?>
                                                            <span class="badge bg-info">Gratis</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="?delete=<?= $extra['id'] ?>" 
                                                           class="btn btn-sm btn-outline-danger" 
                                                           onclick="return confirm('¿Eliminar este extra?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
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
