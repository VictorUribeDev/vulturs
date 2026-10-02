<?php
session_start();

$page_title = "Menú";

try {
    include 'includes/db.php';
    
    // Get menu items grouped by category
    $stmt = $pdo->query("SELECT * FROM menu_items ORDER BY category, name");
    $menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group items by category
    $grouped_menu = [];
    foreach ($menu_items as $item) {
        $grouped_menu[$item['category']][] = $item;
    }
    
    // Get extras for modal
    $extras_stmt = $pdo->query("SELECT * FROM extras ORDER BY type, name");
    $extras = $extras_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group extras by type
    $grouped_extras = [];
    foreach ($extras as $extra) {
        $grouped_extras[$extra['type']][] = $extra;
    }
    
    // Get sandwich components
    $components_stmt = $pdo->query("SELECT * FROM sandwich_components ORDER BY category, name");
    $components = $components_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group components by category
    $grouped_components = [];
    foreach ($components as $component) {
        $grouped_components[$component['category']][] = $component;
    }
    
} catch (Exception $e) {
    $error_message = "Error al cargar el menú. Por favor, inténtelo más tarde.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Sandwich Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-utensils"></i> Sandwich Shop
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="menu.php">Menú</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sandwich_builder.php">Arma tu Sandwich</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contacto</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart"></i> Carrito
                            <span class="badge bg-warning text-dark cart-count">0</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Menu Content -->
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4">
                    <i class="fas fa-utensils"></i> Nuestro Menú
                </h1>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($grouped_menu)): ?>
            <?php foreach ($grouped_menu as $category => $items): ?>
                <div class="menu-category mb-5">
                    <h2 class="category-title">
                        <i class="fas fa-<?= $category === 'hamburguesas' ? 'hamburger' : ($category === 'hotdogs' ? 'hotdog' : ($category === 'bebidas' ? 'glass' : 'utensils')) ?>"></i>
                        <?= ucfirst($category) ?>
                    </h2>
                    
                    <div class="row">
                        <?php foreach ($items as $item): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="menu-item-card">
                                    <div class="menu-item-image">
                                        <?php if ($item['image']): ?>
                                            <img src="assets/images/menu/<?= htmlspecialchars($item['image']) ?>" 
                                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                                 class="img-fluid">
                                        <?php else: ?>
                                            <div class="no-image">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="menu-item-content">
                                        <h5 class="menu-item-title"><?= htmlspecialchars($item['name']) ?></h5>
                                        <p class="menu-item-description"><?= htmlspecialchars($item['description']) ?></p>
                                        
                                        <div class="menu-item-prices">
                                            <?php if ($item['price_simple']): ?>
                                                <div class="price-option">
                                                    <span class="price-label">Simple:</span>
                                                    <span class="price-value">$<?= number_format($item['price_simple'], 0, ',', '.') ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($item['price_doble']): ?>
                                                <div class="price-option">
                                                    <span class="price-label">Doble:</span>
                                                    <span class="price-value">$<?= number_format($item['price_doble'], 0, ',', '.') ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <button class="btn btn-primary w-100" onclick="showAddToCartModal(<?= htmlspecialchars(json_encode($item)) ?>)">
                                            <i class="fas fa-plus"></i> Agregar al Carrito
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                <h5>Menú no disponible</h5>
                <p class="text-muted">El menú se está actualizando. Por favor, inténtelo más tarde.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add to Cart Modal -->
    <div class="modal fade" id="addToCartModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i> Agregar al Carrito
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="modal-content">
                        <!-- Content will be loaded dynamically -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="addToCart()">
                        <i class="fas fa-shopping-cart"></i> Agregar al Carrito
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/cart.js"></script>
    
    <script>
        // Store data for modal
        const extrasData = <?= json_encode($grouped_extras) ?>;
        const componentsData = <?= json_encode($grouped_components) ?>;
        let currentItem = null;
        
        // Update cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });
        
        function showAddToCartModal(item) {
            currentItem = item;
            const modal = new bootstrap.Modal(document.getElementById('addToCartModal'));
            
            // Build modal content
            let content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>${item.name}</h6>
                        <p class="text-muted">${item.description || 'Sin descripción'}</p>
            `;
            
            // Size selection
            content += '<div class="mb-3">';
            content += '<label class="form-label">Tamaño:</label>';
            
            if (item.price_simple) {
                content += `
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="size" id="size_simple" value="simple" checked>
                        <label class="form-check-label" for="size_simple">
                            Simple - $${parseInt(item.price_simple).toLocaleString()}
                        </label>
                    </div>
                `;
            }
            
            if (item.price_doble) {
                content += `
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="size" id="size_doble" value="doble">
                        <label class="form-check-label" for="size_doble">
                            Doble - $${parseInt(item.price_doble).toLocaleString()}
                        </label>
                    </div>
                `;
            }
            content += '</div>';
            
            // Quantity
            content += `
                <div class="mb-3">
                    <label class="form-label">Cantidad:</label>
                    <input type="number" class="form-control" id="quantity" value="1" min="1" max="10">
                </div>
            `;
            
            content += '</div><div class="col-md-6">';
            
            // Extras section for hamburgers and hotdogs
            if (item.category === 'hamburguesas' || item.category === 'hotdogs') {
                content += '<h6>Extras:</h6>';
                
                // Free drink for combos
                if (extrasData.bebida && extrasData.bebida.length > 0) {
                    content += `
                        <div class="mb-3">
                            <label class="form-label text-success">Bebida Gratis (Incluida):</label>
                            <select class="form-select" id="free_drink">
                                <option value="">Sin bebida</option>
                    `;
                    
                    extrasData.bebida.forEach(drink => {
                        content += `<option value="${drink.id}">${drink.name}</option>`;
                    });
                    
                    content += '</select></div>';
                }
                
                // Additional extras
                if (extrasData.papas && extrasData.papas.length > 0) {
                    content += '<div class="mb-3"><label class="form-label">Papas Adicionales:</label>';
                    extrasData.papas.forEach(extra => {
                        content += `
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="extra_${extra.id}" value="${extra.id}">
                                <label class="form-check-label" for="extra_${extra.id}">
                                    ${extra.name} ${extra.price > 0 ? `(+$${parseInt(extra.price).toLocaleString()})` : '(Gratis)'}
                                </label>
                            </div>
                        `;
                    });
                    content += '</div>';
                }
                
                if (extrasData.salsa && extrasData.salsa.length > 0) {
                    content += '<div class="mb-3"><label class="form-label">Salsas:</label>';
                    extrasData.salsa.forEach(extra => {
                        content += `
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="extra_${extra.id}" value="${extra.id}">
                                <label class="form-check-label" for="extra_${extra.id}">
                                    ${extra.name}
                                </label>
                            </div>
                        `;
                    });
                    content += '</div>';
                }
            }
            
            // Custom sandwich components (if item_id = 17 or specific sandwich item)
            if (item.name.toLowerCase().includes('personalizado') || item.name.toLowerCase().includes('custom')) {
                content += '<h6>Personalizar Sándwich:</h6>';
                
                Object.keys(componentsData).forEach(category => {
                    content += `<div class="mb-3"><label class="form-label">${category.charAt(0).toUpperCase() + category.slice(1)}:</label>`;
                    componentsData[category].forEach(component => {
                        content += `
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="component_${component.id}" value="${component.id}">
                                <label class="form-check-label" for="component_${component.id}">
                                    ${component.name} ${component.price > 0 ? `(+$${parseInt(component.price).toLocaleString()})` : ''}
                                </label>
                            </div>
                        `;
                    });
                    content += '</div>';
                });
            }
            
            content += '</div></div>';
            
            document.getElementById('modal-content').innerHTML = content;
            modal.show();
        }
        
        function addToCart() {
            if (!currentItem) return;
            
            const size = document.querySelector('input[name="size"]:checked')?.value || 'simple';
            const quantity = parseInt(document.getElementById('quantity').value) || 1;
            
            // Get selected extras
            const extras = [];
            const freeDrink = document.getElementById('free_drink')?.value;
            if (freeDrink) {
                extras.push(parseInt(freeDrink));
            }
            
            // Get additional extras
            document.querySelectorAll('input[id^="extra_"]:checked').forEach(checkbox => {
                extras.push(parseInt(checkbox.value));
            });
            
            // Get selected components
            const components = [];
            document.querySelectorAll('input[id^="component_"]:checked').forEach(checkbox => {
                components.push(parseInt(checkbox.value));
            });
            
            // Add to cart via API
            fetch('api/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    item_id: currentItem.id,
                    size: size,
                    quantity: quantity,
                    extras: extras,
                    components: components
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('addToCartModal')).hide();
                    
                    // Update cart count
                    updateCartCount();
                    
                    // Show success message
                    showAlert('success', data.message);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Error al agregar el producto al carrito');
            });
        }
        
        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 3000);
        }
    </script>
</body>
</html>
