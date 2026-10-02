<?php

session_start();
include '../includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Update order status
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        header("Location: orders.php?success=updated");
        exit;
    } catch (Exception $e) {
        header("Location: orders.php?error=update_failed");
        exit;
    }
}

// Delete order - FIXED
if (isset($_POST['delete_order'])) {
    $order_id = (int)$_POST['order_id'];
    
    try {
        $pdo->beginTransaction();
        
        // Delete order details first
        $stmt = $pdo->prepare("DELETE FROM order_details WHERE order_id = ?");
        $stmt->execute([$order_id]);
        
        // Delete the order
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        
        $pdo->commit();
        header("Location: orders.php?success=deleted");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: orders.php?error=delete_failed");
        exit;
    }
}

// Get filters
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';
$search = $_GET['search'] ?? '';

// Build query with filters
$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if ($date_filter) {
    $where_conditions[] = "DATE(created_at) = ?";
    $params[] = $date_filter;
}

if ($search) {
    $where_conditions[] = "(customer_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

$stmt = $pdo->prepare("SELECT * FROM orders {$where_clause} ORDER BY created_at DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stats_stmt = $pdo->query("
    SELECT 
        status,
        COUNT(*) as count,
        SUM(total) as revenue
    FROM orders 
    GROUP BY status
");
$stats = $stats_stmt->fetchAll(PDO::FETCH_ASSOC);
$status_counts = [];
$status_revenue = [];
foreach ($stats as $stat) {
    $status_counts[$stat['status']] = $stat['count'];
    $status_revenue[$stat['status']] = $stat['revenue'];
}
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-shopping-bag"></i> Gestión de Pedidos
            </h1>
            <div class="d-flex gap-2">
                <a href="reports.php" class="btn btn-info">
                    <i class="fas fa-chart-bar"></i> Ver Reportes
                </a>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                <?php if ($_GET['success'] == 'updated'): ?>
                    Estado del pedido actualizado correctamente.
                <?php elseif ($_GET['success'] == 'deleted'): ?>
                    Pedido eliminado correctamente.
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                Error al procesar la solicitud.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= array_sum($status_counts) ?></div>
                    <div class="stat-label">Total Pedidos</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $status_counts['pendiente'] ?? 0 ?></div>
                    <div class="stat-label">Pendientes</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= ($status_counts['en preparación'] ?? 0) + ($status_counts['en camino'] ?? 0) ?></div>
                    <div class="stat-label">En Proceso</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $status_counts['completado'] ?? 0 ?></div>
                    <div class="stat-label">Completados</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Buscar:</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?= htmlspecialchars($search) ?>" placeholder="Nombre, email o teléfono">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Estado:</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" <?= $status_filter === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="en preparación" <?= $status_filter === 'en preparación' ? 'selected' : '' ?>>En preparación</option>
                            <option value="en camino" <?= $status_filter === 'en camino' ? 'selected' : '' ?>>En camino</option>
                            <option value="completado" <?= $status_filter === 'completado' ? 'selected' : '' ?>>Completado</option>
                            <option value="cancelado" <?= $status_filter === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date" class="form-label">Fecha:</label>
                        <input type="date" name="date" id="date" class="form-control" value="<?= htmlspecialchars($date_filter) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="orders.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Orders List -->
        <?php if (empty($orders)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5>No hay pedidos</h5>
                    <p class="text-muted">No se encontraron pedidos con los filtros seleccionados.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Contacto</th>
                                    <th>Tipo</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><strong>#<?= $order['id'] ?></strong></td>
                                    <td>
                                        <strong><?= htmlspecialchars($order['customer_name']) ?: 'Sin nombre' ?></strong>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <?php if ($order['email']): ?>
                                                <div><i class="fas fa-envelope"></i> <?= htmlspecialchars($order['email']) ?></div>
                                            <?php endif; ?>
                                            <?php if ($order['phone']): ?>
                                                <div><i class="fas fa-phone"></i> <?= htmlspecialchars($order['phone']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $order['order_type'] === 'delivery' ? 'info' : 'warning' ?>">
                                            <?= ucfirst($order['order_type']) ?>
                                        </span>
                                    </td>
                                    <td><strong>$<?= number_format($order['total'], 0, ',', '.') ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php
                                            echo match($order['status']) {
                                                'pendiente' => 'warning',
                                                'en preparación' => 'info',
                                                'en camino' => 'primary',
                                                'completado' => 'success',
                                                'cancelado' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewOrder(<?= $order['id'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            
                                            
                                            
                                            
                                            <form method="post" class="d-inline" 
                                                  onsubmit="return confirm('¿Estás seguro de eliminar este pedido?');">
                                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                <button type="submit" name="delete_order" class="btn btn-sm btn-outline-danger">
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
function viewOrder(orderId) {
    window.location.href = 'dashboard.php#order-' + orderId;
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
