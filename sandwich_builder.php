<?php
session_start();

$page_title = "Arma tu Sandwich";

try {
    include 'includes/db.php';
    if (!$pdo) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    $stmt = $pdo->query("SELECT * FROM sandwich_components ORDER BY category, name");
    if (!$stmt) {
        throw new Exception("Error en consulta: " . implode(" ", $pdo->errorInfo()));
    }

    $components = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($components)) {
        throw new Exception("No se encontraron componentes (tabla vacía o no existe)");
    }

    // Group components by category
    $grouped_components = [];
    foreach ($components as $component) {
        $grouped_components[$component['category']][] = $component;
    }
    
    // Get extras for drinks
    $extras_stmt = $pdo->query("SELECT * FROM extras WHERE type = 'bebida' ORDER BY name");
    $drinks = $extras_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} 
 catch (Exception $e) {
    $error_message = "Error: " . $e->getMessage();
    error_log($error_message); // Registrar error
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
                        <a class="nav-link" href="menu.php">Menú</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="sandwich_builder.php">Arma tu Sandwich</a>
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

    <!-- Sandwich Builder Content -->
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4">
                    <i class="fas fa-hamburger"></i> Arma tu Sandwich Personalizado
                </h1>
                <p class="text-muted mb-4">Crea tu sandwich perfecto eligiendo cada ingrediente</p>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($grouped_components)): ?>
            <form id="sandwich-builder-form">
                <div class="row">
                    <!-- Sandwich Builder Panel -->
                    <div class="col-lg-8">
                        <div class="sandwich-builder-panel">
                            <h4 class="mb-4">
                                <i class="fas fa-tools"></i> Selecciona tus Ingredientes
                            </h4>
                            
                            <?php foreach ($grouped_components as $category => $items): ?>
                                <div class="component-category mb-4">
                                    <h5 class="category-title text-primary">
                                        <i class="fas fa-<?= $category === 'carne' ? 'drumstick-bite' : ($category === 'queso' ? 'cheese' : ($category === 'verdura' ? 'leaf' : 'bread-slice')) ?>"></i>
                                        <?= ucfirst($category) ?>
                                        <?php if ($category === 'carne'): ?>
                                            <span class="text-warning">(Requerido)</span>
                                        <?php endif; ?>
                                    </h5>
                                    
                                    <div class="row">
                                        <?php foreach ($items as $component): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="component-card">
                                                    <div class="form-check">
                                                        <?php
                                                        // Determinar el tipo de input según la categoría
                                                        $inputType = ($category === 'carne' || $category === 'gramaje') ? 'radio' : 'checkbox';
                                                        // El nombre del input: para radios es la categoría, para checkboxes es categoría[]
                                                        $inputName = ($inputType === 'radio') ? $category : $category . '[]';
                                                        ?>
                                                        <input 
                                                            class="form-check-input component-checkbox" 
                                                            type="<?= $inputType ?>"
                                                            name="<?= $inputName ?>" 
                                                            id="component_<?= $component['id'] ?>" 
                                                            value="<?= $component['id'] ?>"
                                                            data-price="<?= $component['price'] ?>"
                                                            data-name="<?= htmlspecialchars($component['name']) ?>"
                                                            data-category="<?= $category ?>"
                                                            onchange="updateSandwich()"
                                                            <?= ($inputType === 'radio' && $component === reset($items)) ? 'checked' : '' ?>
                                                        />

                                                        <label class="form-check-label w-100" for="component_<?= $component['id'] ?>">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span><?= htmlspecialchars($component['name']) ?></span>
                                                                <?php if ($component['price'] > 0): ?>
                                                                    <span class="text-primary fw-bold">+$<?= number_format($component['price'], 0, ',', '.') ?></span>
                                                                <?php else: ?>
                                                                    <span class="text-success">Gratis</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- Free Drink Selection -->
                            <?php if (!empty($drinks)): ?>
                                <div class="component-category mb-4">
                                    <h5 class="category-title text-success">
                                        <i class="fas fa-glass"></i> Bebida gratis incluida
                                    </h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <select class="form-select" id="free_drink" name="free_drink">
                                                <option value="">Sin bebida</option>
                                                <?php foreach ($drinks as $drink): ?>
                                                    <option value="<?= $drink['id'] ?>"><?= htmlspecialchars($drink['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Quantity -->
                            <div class="component-category mb-4">
                                <h5 class="category-title">
                                    <i class="fas fa-sort-numeric-up"></i> Cantidad
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-3">
                                        <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" max="10" onchange="updateSandwich()">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sandwich Summary Panel -->
                    <div class="col-lg-4">
                        <div class="sandwich-summary sticky-top">
                            <h5 class="mb-3">
                                <i class="fas fa-receipt"></i> Tu Sandwich
                            </h5>
                            
                            <div id="sandwich-preview">
                                <div class="sandwich-visual mb-3">
                                    <div class="sandwich-layers">
                                        <div class="bread-top">🍞</div>
                                        <div id="selected-ingredients" class="ingredients-list">
                                            <p class="text-muted text-center py-3">
                                                <i class="fas fa-arrow-left"></i>
                                                Selecciona ingredientes
                                            </p>
                                        </div>
                                        <div class="bread-bottom">🍞</div>
                                    </div>
                                </div>
                                
                                <div id="price-breakdown" class="price-breakdown">
                                    <div class="price-line">
                                        <span>Precio base:</span>
                                        <span id="base-price">$0</span>
                                    </div>
                                    <div class="price-line" id="extras-price-line" style="display: none;">
                                        <span>Ingredientes extras:</span>
                                        <span id="extras-price">$0</span>
                                    </div>
                                    <hr>
                                    <div class="price-line total-price">
                                        <span><strong>Total:</strong></span>
                                        <span id="total-price"><strong>$0</strong></span>
                                    </div>
                                </div>
                                
                                <button type="button" class="btn btn-primary w-100 mt-3" id="add-to-cart-btn" onclick="addCustomSandwichToCart()" disabled>
                                    <i class="fas fa-shopping-cart"></i> Agregar al Carrito
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                <h5>Constructor no disponible</h5>
                <p class="text-muted">El constructor de sandwiches no está disponible en este momento.</p>
                <a href="menu.php" class="btn btn-primary">Ver Menú Regular</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/cart.js"></script>
    
    <script>
    // Sandwich builder state
    let sandwichComponents = <?= json_encode($grouped_components) ?>;
    let basePrice = 5000; // Base price for custom sandwich
    
    // Update cart count on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateCartCount();
        updateSandwich(); // Inicializar el estado
    });
    
    function updateSandwich() {
        let totalPrice = basePrice;
        let extrasPrice = 0;
        let ingredientsList = [];
        
        // Get all selected components
        const checkboxes = document.querySelectorAll('.component-checkbox:checked');
        checkboxes.forEach(checkbox => {
            const price = parseFloat(checkbox.dataset.price);
            const name = checkbox.dataset.name;
            
            if (price > 0) {
                extrasPrice += price;
            }
            totalPrice += price;
            
            ingredientsList.push(name);
        });
        
        // Get quantity
        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        totalPrice *= quantity;
        extrasPrice *= quantity;
        
        // Update visual representation
        updateSandwichVisual(ingredientsList);
        
        // Update price breakdown
        document.getElementById('base-price').textContent = '$' + (basePrice * quantity).toLocaleString();
        
        if (extrasPrice > 0) {
            document.getElementById('extras-price-line').style.display = 'flex';
            document.getElementById('extras-price').textContent = '$' + extrasPrice.toLocaleString();
        } else {
            document.getElementById('extras-price-line').style.display = 'none';
        }
        
        document.getElementById('total-price').innerHTML = '<strong>$' + totalPrice.toLocaleString() + '</strong>';
        
        // CORRECCIÓN: Detección de proteína seleccionada (categoría 'carne')
        const hasProtein = document.querySelector('input[name="carne"]:checked') !== null;
        document.getElementById('add-to-cart-btn').disabled = !hasProtein;
    }
    
    function updateSandwichVisual(ingredients) {
        const container = document.getElementById('selected-ingredients');
        
        if (ingredients.length === 0) {
            container.innerHTML = `
                <p class="text-muted text-center py-3">
                    <i class="fas fa-arrow-left"></i>
                    Selecciona ingredientes
                </p>
            `;
        } else {
            container.innerHTML = ingredients.map(ingredient => 
                `<div class="ingredient-item">${ingredient}</div>`
            ).join('');
        }
    }
    
    function addCustomSandwichToCart() {
        console.log("Función addCustomSandwichToCart ejecutada");
        
        // Detectar proteína seleccionada (categoría 'carne')
        const hasProtein = document.querySelector('input[name="carne"]:checked') !== null;
        
        if (!hasProtein) {
            showAlert('warning', 'Debes seleccionar al menos una proteína');
            return;
        }

        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        const freeDrink = document.getElementById('free_drink').value;

        // Preparar componentes
        const components = [];
        document.querySelectorAll('.component-checkbox:checked').forEach(checkbox => {
            components.push({
                id: parseInt(checkbox.value),
                name: checkbox.dataset.name,
                price: parseFloat(checkbox.dataset.price),
                category: checkbox.dataset.category
            });
        });

        // Preparar extras
        const extras = [];
        if (freeDrink) {
            const drinkOption = document.querySelector(`#free_drink option[value="${freeDrink}"]`);
            extras.push({
                id: parseInt(freeDrink),
                name: drinkOption.textContent,
                price: 0
            });
        }

        console.log("Enviando datos:", { components, extras, quantity });

        fetch('api/add_custom_sandwich.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                components: components.map(c => c.id),
                extras: extras.map(e => e.id),
                quantity: quantity,
                component_details: components,
                extra_details: extras
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                updateCartCount();
                
                // Resetear formulario
                document.getElementById('sandwich-builder-form').reset();
                // Volver a marcar el primer radio de cada grupo de radio (carne y gramaje)
                document.querySelectorAll('input[type="radio"]').forEach(radio => {
                    if (radio.parentElement.parentElement.parentElement.querySelector('input[type="radio"]:checked') === null) {
                        // Si no hay ninguno seleccionado en este grupo, marcamos el primero
                        radio.checked = true;
                    }
                });
                updateSandwich(); // Actualizar vista
            } else {
                showAlert('danger', data.message || 'Error al agregar el sandwich');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Error de conexión');
        });
    }
    
    // Función para mostrar alertas
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 3000);
    }
</script>
    
    <style>
        .sandwich-builder-panel {
            background: hsl(var(--card-background));
            border: 1px solid hsl(var(--border-color));
            border-radius: 15px;
            padding: 2rem;
        }
        
        .component-category {
            border-bottom: 1px solid hsl(var(--border-color));
            padding-bottom: 1.5rem;
        }
        
        .component-category:last-child {
            border-bottom: none;
        }
        
        .component-card {
            background: hsl(var(--background));
            border: 1px solid hsl(var(--border-color));
            border-radius: 8px;
            padding: 1rem;
            transition: all 0.3s ease;
        }
        
        .component-card:hover {
            border-color: hsl(var(--primary-color));
            transform: translateY(-2px);
        }
        
        .component-checkbox:checked + label .component-card {
            border-color: hsl(var(--primary-color));
            background: hsl(var(--primary-color) / 0.1);
        }
        
        .sandwich-summary {
            background: hsl(var(--card-background));
            border: 1px solid hsl(var(--border-color));
            border-radius: 15px;
            padding: 2rem;
            top: 2rem;
        }
        
        .sandwich-visual {
            text-align: center;
            padding: 1rem;
            background: hsl(var(--background));
            border-radius: 10px;
            border: 2px dashed hsl(var(--border-color));
        }
        
        .sandwich-layers {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        
        .bread-top, .bread-bottom {
            font-size: 2rem;
        }
        
        .ingredient-item {
            background: hsl(var(--primary-color) / 0.1);
            color: hsl(var(--primary-color));
            padding: 0.25rem 0.5rem;
            margin: 0.25rem;
            border-radius: 15px;
            font-size: 0.9rem;
            display: inline-block;
        }
        
        .price-breakdown {
            background: hsl(var(--background));
            padding: 1rem;
            border-radius: 8px;
        }
        
        .price-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .total-price {
            font-size: 1.1rem;
            color: hsl(var(--primary-color));
        }
    </style>
</body>
</html>