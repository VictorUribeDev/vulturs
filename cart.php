<?php
session_start();
include 'includes/db.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_quantity':
                $index = (int)$_POST['index'];
                $quantity = max(1, (int)$_POST['quantity']);
                if (isset($_SESSION['cart'][$index])) {
                    $_SESSION['cart'][$index]['quantity'] = $quantity;
                }
                break;
                
            case 'remove_item':
                $index = (int)$_POST['index'];
                if (isset($_SESSION['cart'][$index])) {
                    unset($_SESSION['cart'][$index]);
                    $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
                }
                break;
                
            case 'clear_cart':
                $_SESSION['cart'] = [];
                break;
        }
        header('Location: cart.php');
        exit;
    }
}

// Calculate totals
$cart_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito - Sandwich Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">

</head>
<body>
    <!-- Navigation - Mismo que en index.php -->
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

    <main class="cart-main">
        <div class="container">
            <h1 class="page-title">Carrito de Compras</h1>

            <?php if (empty($_SESSION['cart'])): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h2>Tu carrito está vacío</h2>
                    <p>¡Agrega algunos deliciosos productos de nuestro menú!</p>
                    <a href="menu.php" class="btn-primary">Ver Menú</a>
                </div>
            <?php else: ?>
                <div class="cart-content">
                    <div class="cart-items">
                        <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                            <div class="cart-item">
                                <div class="item-info">
                                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                                    <?php if (isset($item['variant'])): ?>
                                        <span class="item-variant"><?= htmlspecialchars($item['variant']) ?></span>
                                    <?php endif; ?>
                                    
                                    <!-- Mostrar componentes del sándwich -->
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
            <li><?= htmlspecialchars($extra['name']) ?> - $<?= number_format($extra['price'], 0, ',', '.') ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

                                </div>
                                
                                <div class="item-controls">
                                    <form method="post" class="quantity-form">
                                        <input type="hidden" name="action" value="update_quantity">
                                        <input type="hidden" name="index" value="<?= $index ?>">
                                        <div class="quantity-controls">
                                            <button type="button" onclick="updateQuantity(<?= $index ?>, -1)">-</button>
                                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" onchange="this.form.submit()">
                                            <button type="button" onclick="updateQuantity(<?= $index ?>, 1)">+</button>
                                        </div>
                                    </form>
                                    
                                    <div class="item-price">
                                        $<?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>
                                    </div>
                                    
                                    <form method="post" class="remove-form">
                                        <input type="hidden" name="action" value="remove_item">
                                        <input type="hidden" name="index" value="<?= $index ?>">
                                        <button type="submit" class="remove-btn" onclick="return confirm('¿Eliminar este producto?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-summary">
                        <div class="summary-card">
                            <h3>Resumen del Pedido</h3>
                            <div class="summary-line">
                                <span>Subtotal:</span>
                                <span>$<?= number_format($cart_total, 0, ',', '.') ?></span>
                            </div>
                            <div class="summary-line total">
                                <span>Total:</span>
                                <span>$<?= number_format($cart_total, 0, ',', '.') ?></span>
                            </div>
                            
                            <div class="cart-actions">
                                <a href="menu.php" class="btn-secondary">Seguir Comprando</a>
                                <a href="checkout.php" class="btn-primary">Realizar Pedido</a>
                            </div>
                            
                            <form method="post" class="clear-cart-form">
                                <input type="hidden" name="action" value="clear_cart">
                                <button type="submit" class="btn-danger" onclick="return confirm('¿Vaciar todo el carrito?')">
                                    Vaciar Carrito
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="assets/js/cart.js"></script>

    <script>
        function updateQuantity(index, change) {
            const form = document.querySelector(`input[name="index"][value="${index}"]`).closest('form');
            const quantityInput = form.querySelector('input[name="quantity"]');
            const newQuantity = parseInt(quantityInput.value) + change;
            
            if (newQuantity >= 1) {
                quantityInput.value = newQuantity;
                form.submit();
            }
        }
        
        // Actualizar contador del carrito
        document.addEventListener('DOMContentLoaded', function() {
            const cartCount = document.querySelector('.cart-count');
            cartCount.textContent = <?= count($_SESSION['cart']) ?>;
        });
    </script>
</body>
</html>