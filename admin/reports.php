<?php
session_start();
include '../includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Get date filters
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // Today

// Get orders statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(total) as total_revenue,
        AVG(total) as avg_order_value,
        COUNT(DISTINCT customer_name) as unique_customers,
        SUM(CASE WHEN status = 'completado' THEN 1 ELSE 0 END) as completed_orders,
        SUM(CASE WHEN status = 'cancelado' THEN 1 ELSE 0 END) as cancelled_orders,
        SUM(CASE WHEN order_type = 'delivery' THEN 1 ELSE 0 END) as delivery_orders,
        SUM(CASE WHEN order_type = 'pickup' THEN 1 ELSE 0 END) as pickup_orders
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$start_date, $end_date]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get top customers
$stmt = $pdo->prepare("
    SELECT 
        customer_name,
        email,
        phone,
        COUNT(*) as order_count,
        SUM(total) as total_spent,
        AVG(total) as avg_spent,
        MAX(created_at) as last_order
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY customer_name, email, phone
    ORDER BY total_spent DESC
    LIMIT 10
");
$stmt->execute([$start_date, $end_date]);
$top_customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get popular products
$stmt = $pdo->prepare("
    SELECT 
        CASE 
            WHEN od.is_extra = 1 THEN e.name
            WHEN od.is_component = 1 THEN od.component_name
            ELSE mi.name
        END AS product_name,
        SUM(od.quantity) as total_quantity,
        SUM(od.price * od.quantity) as total_revenue
    FROM order_details od
    LEFT JOIN menu_items mi ON od.item_id = mi.id AND od.is_extra = 0 AND od.is_component = 0
    LEFT JOIN extras e ON od.item_id = e.id AND od.is_extra = 1
    JOIN orders o ON od.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    AND od.is_component = 0
    GROUP BY product_name
    ORDER BY total_quantity DESC
    LIMIT 10
");
$stmt->execute([$start_date, $end_date]);
$popular_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get daily sales
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as sale_date,
        COUNT(*) as orders_count,
        SUM(total) as daily_revenue
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY sale_date ASC
");
$stmt->execute([$start_date, $end_date]);
$daily_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-bar"></i> Reportes y Estadísticas
            </h1>
            <button class="btn btn-primary" onclick="exportToCSV()">
                    <i class="fas fa-file-export"></i> Exportar CSV
                </button>
        </div>

        <!-- Date Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Fecha Inicio:</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $start_date ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">Fecha Fin:</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $end_date ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics Overview -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= number_format($stats['total_orders']) ?></div>
                    <div class="stat-label">Total Pedidos</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">$<?= number_format($stats['total_revenue'], 0, ',', '.') ?></div>
                    <div class="stat-label">Ingresos Totales</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= number_format($stats['unique_customers']) ?></div>
                    <div class="stat-label">Clientes Únicos</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">$<?= number_format($stats['avg_order_value'], 0, ',', '.') ?></div>
                    <div class="stat-label">Valor Promedio</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Order Status Breakdown -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-pie-chart"></i> Estado de Pedidos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3">
                                    <h4 class="text-success"><?= $stats['completed_orders'] ?></h4>
                                    <small>Completados</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3">
                                    <h4 class="text-danger"><?= $stats['cancelled_orders'] ?></h4>
                                    <small>Cancelados</small>
                                </div>
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3">
                                    <h4 class="text-info"><?= $stats['delivery_orders'] ?></h4>
                                    <small>Delivery</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3">
                                    <h4 class="text-warning"><?= $stats['pickup_orders'] ?></h4>
                                    <small>Pickup</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Sales Chart -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-line"></i> Ventas Diarias
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="dailySalesChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Customers -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-star"></i> Top 10 Clientes
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($top_customers)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5>No hay datos de clientes</h5>
                        <p class="text-muted">No se encontraron clientes en el período seleccionado.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Pedidos</th>
                                    <th>Total Gastado</th>
                                    <th>Promedio</th>
                                    <th>Último Pedido</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_customers as $customer): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($customer['customer_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($customer['email']) ?></td>
                                    <td><?= htmlspecialchars($customer['phone']) ?></td>
                                    <td><span class="badge bg-primary"><?= $customer['order_count'] ?></span></td>
                                    <td><strong>$<?= number_format($customer['total_spent'], 0, ',', '.') ?></strong></td>
                                    <td>$<?= number_format($customer['avg_spent'], 0, ',', '.') ?></td>
                                    <td><?= date('d/m/Y', strtotime($customer['last_order'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Popular Products -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-trophy"></i> Productos Más Vendidos
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($popular_products)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                        <h5>No hay datos de productos</h5>
                        <p class="text-muted">No se encontraron ventas en el período seleccionado.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Posición</th>
                                    <th>Producto</th>
                                    <th>Cantidad Vendida</th>
                                    <th>Ingresos Generados</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($popular_products as $index => $product): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-<?= $index < 3 ? 'warning' : 'secondary' ?>">
                                            #<?= $index + 1 ?>
                                        </span>
                                    </td>
                                    <td><strong><?= htmlspecialchars($product['product_name']) ?></strong></td>
                                    <td><span class="badge bg-success"><?= $product['total_quantity'] ?> unidades</span></td>
                                    <td><strong>$<?= number_format($product['total_revenue'], 0, ',', '.') ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Daily Sales Chart
    const ctx = document.getElementById('dailySalesChart').getContext('2d');
    const dailySalesData = <?= json_encode($daily_sales) ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dailySalesData.map(day => {
                const date = new Date(day.sale_date);
                return date.toLocaleDateString('es-ES', { month: 'short', day: 'numeric' });
            }),
            datasets: [{
                label: 'Ingresos Diarios',
                data: dailySalesData.map(day => day.daily_revenue),
                borderColor: 'rgb(30, 125, 216)',
                backgroundColor: 'rgba(30, 125, 216, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
function exportToCSV() {
            const rows = document.querySelectorAll('#customersTableBody tr');
            let csvContent = "data:text/csv;charset=utf-8,";
            
            // Encabezados
            csvContent += "Nombre,Email,Teléfono,Pedidos,Último Pedido\n";
            
            // Datos
            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    const name = row.querySelector('.customer-name').textContent.replace(/,/g, '');
                    const email = row.querySelector('.customer-email')?.textContent.replace(/,/g, '') || '';
                    const phone = row.querySelector('.customer-phone')?.textContent.replace(/,/g, '') || '';
                    const orders = row.querySelector('.stat-value').textContent;
                    const lastOrder = row.querySelector('.last-order strong').textContent;
                    
                    csvContent += `"${name}","${email}","${phone}","${orders}","${lastOrder}"\n`;
                }
            });
            
            // Crear y descargar archivo
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "reporte_clientes.csv");
            document.body.appendChild(link);
            link.click();
        }
</script>

</body>
</html>
