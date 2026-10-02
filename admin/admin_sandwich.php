<?php
session_start();
include 'includes/header.php';
include '../includes/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Add new component
if (isset($_POST['add_component'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO sandwich_components (name, price, category) VALUES (?, ?, ?)");
        $stmt->execute([
            trim($_POST['name']), 
            (int)$_POST['price'], 
            $_POST['category']
        ]);
        header('Location: admin_sandwich.php?success=added');
        exit;
    } catch (Exception $e) {
        header('Location: admin_sandwich.php?error=add_failed');
        exit;
    }
}

// Delete component
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM sandwich_components WHERE id = ?");
        $stmt->execute([(int)$_GET['delete']]);
        header('Location: admin_sandwich.php?success=deleted');
        exit;
    } catch (Exception $e) {
        header('Location: admin_sandwich.php?error=delete_failed');
        exit;
    }
}

// Get components
$components = $pdo->query("SELECT * FROM sandwich_components ORDER BY category, name")->fetchAll();

// Group components by category
$grouped_components = [];
foreach ($components as $component) {
    $grouped_components[$component['category']][] = $component;
}
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-hamburger"></i> Gestionar Componentes de Sándwich
            </h1>
        </div>

        <!-- Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                <?php if ($_GET['success'] === 'added'): ?>
                    Componente agregado correctamente.
                <?php elseif ($_GET['success'] === 'deleted'): ?>
                    Componente eliminado correctamente.
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

        <!-- Add Component Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-plus"></i> Agregar Nuevo Componente
                </h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="name" class="form-label">Nombre del componente *</label>
                            <input type="text" name="name" id="name" class="form-control" 
                                   placeholder="Ej: Carne de res, Lechuga, 150g" required>
                        </div>
                        <div class="col-md-3">
                            <label for="price" class="form-label">Precio adicional</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="price" id="price" class="form-control" 
                                       value="0" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">Categoría *</label>
                            <select name="category" id="category" class="form-select" required>
                                <option value="carne">Carne</option>
                                <option value="gramaje">Gramaje</option>
                                <option value="ingrediente">Ingrediente</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" name="add_component" class="btn btn-primary d-block w-100">
                                <i class="fas fa-plus"></i> Agregar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Existing Components -->
        <?php if (empty($components)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-hamburger fa-3x text-muted mb-3"></i>
                    <h5>No hay componentes registrados</h5>
                    <p class="text-muted">Agrega tu primer componente usando el formulario anterior.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach (['carne', 'gramaje', 'ingrediente'] as $category): ?>
                <?php if (isset($grouped_components[$category])): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-<?= $category === 'carne' ? 'meat' : ($category === 'gramaje' ? 'weight' : 'leaf') ?>"></i>
                                <?= ucfirst($category) ?>s (<?= count($grouped_components[$category]) ?>)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Precio Adicional</th>
                                            <th width="100">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($grouped_components[$category] as $component): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($component['name']) ?></strong>
                                                </td>
                                                <td>
                                                    <?php if ($component['price'] > 0): ?>
                                                        <span class="badge bg-warning">+$<?= number_format($component['price'], 0, ',', '.') ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Incluido</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="?delete=<?= $component['id'] ?>" 
                                                       class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirm('¿Eliminar este componente?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
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
