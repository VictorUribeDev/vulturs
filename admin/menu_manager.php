<?php
session_start();
include '../includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Process deletion
if (isset($_POST['delete_item'])) {
    $id = (int)$_POST['id'];
    
    try {
        // Get the image before deleting to remove it from server
        $stmt = $pdo->prepare("SELECT image FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        
        if ($item && $item['image']) {
            $image_path = '../assets/images/menu/' . $item['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: menu_manager.php?success=deleted");
        exit;
    } catch (Exception $e) {
        header("Location: menu_manager.php?error=delete_failed");
        exit;
    }
}

// Get menu items
$category_filter = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

$where_conditions = [];
$params = [];

if ($category_filter) {
    $where_conditions[] = "category = ?";
    $params[] = $category_filter;
}

if ($search) {
    $where_conditions[] = "(name LIKE ? OR description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

$stmt = $pdo->prepare("SELECT * FROM menu_items {$where_clause} ORDER BY category, name");
$stmt->execute($params);
$menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$categories = $pdo->query("SELECT DISTINCT category FROM menu_items ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-utensils"></i> Gestión del Menú
            </h1>
            <a href="add_item.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Agregar Nuevo Item
            </a>
        </div>

        <!-- Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                Item eliminado correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                Error al eliminar el item.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Buscar:</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?= htmlspecialchars($search) ?>" placeholder="Nombre o descripción">
                    </div>
                    <div class="col-md-4">
                        <label for="category" class="form-label">Categoría:</label>
                        <select name="category" id="category" class="form-select">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category) ?>" 
                                        <?= $category_filter === $category ? 'selected' : '' ?>>
                                    <?= ucfirst(htmlspecialchars($category)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="menu_manager.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Menu Items -->
        <?php if (empty($menu_items)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                    <h5>No hay items en el menú</h5>
                    <p class="text-muted">Comienza agregando tu primer producto</p>
                    <a href="add_item.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Agregar Item
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($menu_items as $item): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-img-top position-relative" style="height: 200px; overflow: hidden;">
                            <?php if ($item['image']): ?>
                                <img src="../assets/images/menu/<?= htmlspecialchars($item['image']) ?>" 
                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                     class="w-100 h-100" style="object-fit: cover;">
                            <?php else: ?>
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light">
                                    <div class="text-center text-muted">
                                        <i class="fas fa-image fa-3x mb-2"></i>
                                        <p class="mb-0">Sin imagen</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-primary"><?= ucfirst($item['category']) ?></span>
                            </div>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                            <p class="card-text text-muted flex-grow-1">
                                <?= htmlspecialchars($item['description']) ?>
                            </p>
                            
                            <div class="mb-3">
                                <?php if ($item['price_simple']): ?>
                                    <div class="d-flex justify-content-between">
                                        <span>Simple:</span>
                                        <strong>$<?= number_format($item['price_simple'], 0, ',', '.') ?></strong>
                                    </div>
                                <?php endif; ?>
                                <?php if ($item['price_doble']): ?>
                                    <div class="d-flex justify-content-between">
                                        <span>Doble:</span>
                                        <strong>$<?= number_format($item['price_doble'], 0, ',', '.') ?></strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary btn-sm flex-fill" onclick="editItem(<?= $item['id'] ?>)">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <form method="post" class="flex-fill" 
                                      onsubmit="return confirm('¿Estás seguro de eliminar este item? Esta acción no se puede deshacer.');">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <button type="submit" name="delete_item" class="btn btn-outline-danger btn-sm w-100">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function editItem(id) {
    // Placeholder for edit functionality
    alert('Función de edición en desarrollo. ID: ' + id);
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
