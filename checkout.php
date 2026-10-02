
<?php
session_start();

// Redirect to cart if empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Calculate cart total
$cart_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
    
    // Add extras cost
    if (!empty($item['extras'])) {
        foreach ($item['extras'] as $extra) {
            $cart_total += $extra['price'] * $item['quantity'];
        }
    }
    
    // Add components cost
    if (!empty($item['components'])) {
        foreach ($item['components'] as $component) {
            $cart_total += $component['price'] * $item['quantity'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - Vultur Restaurant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-utensils"></i> Vultur Restaurant
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="menu.php">Menú</a>
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

    <main class="checkout-main">
        <div class="container">
            <h1 class="page-title">Finalizar Compra</h1>
            
            <div class="checkout-content">
                <div class="checkout-items">
                    <h2><i class="fas fa-receipt"></i> Resumen de tu Pedido</h2>
                    
                    <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                        <?php if (!is_array($item)) continue; ?>
                        <div class="checkout-item">
                            <div class="item-info">
                                <h3><?= htmlspecialchars($item['name'] ?? 'Producto sin nombre') ?></h3>
                                <?php if (isset($item['size'])): ?>
                                    <span class="item-size"><?= htmlspecialchars($item['size']) ?></span>
                                <?php endif; ?>
                                
                                <!-- Mostrar componentes con nombres -->
                                <?php if (!empty($item['components'])): ?>
    <strong>Componentes:</strong>
    <ul>
        <?php foreach ($item['components'] as $component): ?>
            <li><?= htmlspecialchars($component['name']) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if (!empty($item['extras'])): ?>
    <strong>Extras:</strong>
    <ul>
        <?php foreach ($item['extras'] as $extra): ?>
            <li><?= htmlspecialchars($extra['name']) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
                                
                                <div class="item-quantity">Cantidad: <?= $item['quantity'] ?? 1 ?></div>
                            </div>
                            
                            <div class="item-price">
                                $<?= number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 0, ',', '.') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="checkout-total">
                        <div class="total-line">
                            <span>Subtotal:</span>
                            <span>$<?= number_format($cart_total, 0, ',', '.') ?></span>
                        </div>
                        <div class="total-line">
                            <span>Envío:</span>
                            <span class="shipping-cost">$0</span>
                        </div>
                        <div class="total-line grand-total">
                            <span>Total:</span>
                            <span>$<?= number_format($cart_total, 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="checkout-form">
                    <h2><i class="fas fa-user"></i> Información de Contacto</h2>
                    
                    <form id="checkout-form">
                        <div class="form-group">
                            <label for="customer_name">Nombre Completo *</label>
                            <input type="text" id="customer_name" name="customer_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Correo Electrónico *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Teléfono *</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Tipo de Pedido *</label>
                            <div class="order-type">
                                <label>
                                    <input type="radio" name="order_type" value="pickup" checked>
                                    <i class="fas fa-store"></i> Recoger en Tienda
                                </label>
                                <label>
                                    <input type="radio" name="order_type" value="delivery">
                                    <i class="fas fa-truck"></i> Delivery
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group delivery-address" style="display: none;">
                            <label for="address">Dirección de Envío *</label>
                            <textarea id="address" name="address" rows="3"></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <a href="cart.php" class="btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver al Carrito
                            </a>
                            <button type="submit" class="btn-primary" id="place-order-btn">
                                <i class="fas fa-check"></i> Confirmar Pedido
                            </button>
                        </div>
                    </form>
                    
                    <div id="order-response" class="order-response"></div>
                </div>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
    const radios = document.querySelectorAll('input[name="order_type"]');
    const direccionField = document.querySelector('.delivery-address');

    function toggleDireccion() {
        const selected = document.querySelector('input[name="order_type"]:checked');
        if (selected && selected.value === 'delivery') {
            direccionField.style.display = 'block';
        } else {
            direccionField.style.display = 'none';
        }
    }

    radios.forEach(radio => {
        radio.addEventListener('change', toggleDireccion);
    });

    // Ejecutar al cargar
    toggleDireccion();
});
    document.getElementById('checkout-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('place-order-btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    
    const responseDiv = document.getElementById('order-response');
    responseDiv.innerHTML = '';
    responseDiv.className = 'order-response';
    
    const formData = {
        customer_name: document.getElementById('customer_name').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        order_type: document.querySelector('input[name="order_type"]:checked').value,
        address: document.getElementById('address').value
    };
    
    try {
        const response = await fetch('process_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            responseDiv.className = 'order-response success';
            responseDiv.innerHTML = `
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3>¡Pedido Confirmado!</h3>
                <p>Tu pedido #${result.order_id} ha sido creado exitosamente.</p>
                <p>Te hemos enviado un correo de confirmación a <strong>${formData.email}</strong>.</p>
                <a href="index.php" class="btn-primary">
                    <i class="fas fa-home"></i> Volver al Inicio
                </a>
            `;
            document.getElementById('checkout-form').reset();
        } else {
            responseDiv.className = 'order-response error';
            let errorMessage = result.message || 'Por favor, inténtalo de nuevo más tarde.';
            if (result.debug) {
                errorMessage += '<br><small>${result.debug.message}</small>';
            }
            responseDiv.innerHTML = `
                <div class="error-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h3>Error al procesar el pedido</h3>
                <p>${errorMessage}</p>
                <button type="button" class="btn-secondary" onclick="resetOrderButton()">
                    Reintentar
                </button>
            `;
        }
    } catch (error) {
        console.error("Error completo:", error);
        responseDiv.className = 'order-response error';
        responseDiv.innerHTML = `
            <div class="error-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h3>Error de Conexión</h3>
            <p>${error.message}</p>
            <button type="button" class="btn-secondary" onclick="resetOrderButton()">
                Reintentar
            </button>
        `;
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check"></i> Confirmar Pedido';
    }
});
    </script>
    
    <style>
        .checkout-main {
            padding: 2rem 0;
        }
        
        .checkout-content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        
        @media (min-width: 992px) {
            .checkout-content {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        .checkout-items, .checkout-form {
            background: hsl(var(--card-background));
            border: 1px solid hsl(var(--border-color));
            border-radius: 15px;
            padding: 2rem;
        }
        
        .checkout-item {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid hsl(var(--border-color));
            padding: 1.5rem 0;
        }
        
        .checkout-item:last-child {
            border-bottom: none;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-size, .item-variant {
            display: inline-block;
            background: hsl(var(--primary-color) / 0.1);
            color: hsl(var(--primary-color));
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }
        
        .item-quantity {
            color: hsl(var(--text-color) / 0.7);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .item-price {
            font-weight: bold;
            font-size: 1.1rem;
            min-width: 100px;
            text-align: right;
        }
        
        .checkout-total {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid hsl(var(--border-color));
        }
        
        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        
        .grand-total {
            font-size: 1.2rem;
            font-weight: bold;
            color: hsl(var(--primary-color));
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid hsl(var(--border-color));
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        input, textarea, select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid hsl(var(--border-color));
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .order-type {
            display: flex;
            gap: 1.5rem;
            margin-top: 0.5rem;
        }
        
        .order-type label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            font-weight: normal;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn-primary, .btn-secondary {
            flex: 1;
            text-align: center;
        }
        
        .order-response {
            margin-top: 2rem;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }
        
        .order-response.success {
            background: hsl(var(--success-color) / 0.1);
            border: 1px solid hsl(var(--success-color));
        }
        
        .order-response.error {
            background: hsl(var(--error-color) / 0.1);
            border: 1px solid hsl(var(--error-color));
        }
        
        .success-icon, .error-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .success-icon {
            color: hsl(var(--success-color));
        }
        
        .error-icon {
            color: hsl(var(--error-color));
        }
    </style>
</body>
</html>
