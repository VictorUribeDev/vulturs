<?php
session_start();

$page_title = "Inicio";
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
                <img src="assets/images/logo.png" alt="Sandwich Shop" class="logo-nav">
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">
                            <img src="assets/images/logo.png" alt="Sandwich Shop" class="logo-img">
                        </h1>

                        <p class="hero-subtitle">
                            Descubre los sabores más auténticos en nuestras hamburguesas gourmet y hotdogs únicos. 
                            Cada bocado es una experiencia inolvidable.
                        </p>
                        <div class="hero-buttons">
                            <a href="menu.php" class="btn btn-primary btn-lg me-3">
                                <i class="fas fa-utensils"></i> Ver Menú
                            </a>
                            <a href="sandwich_builder.php" class="btn btn-warning btn-lg me-3">
                                <i class="fas fa-hamburger"></i> Arma tu Sandwich
                            </a>
                            <a href="contact.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-map-marker-alt"></i> Ubicación
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <svg width="500" height="400" viewBox="0 0 500 400" class="img-fluid">
                            <!-- Restaurant illustration -->
                            <rect x="50" y="100" width="400" height="250" fill="#2c3e50" rx="10"/>
                            <rect x="70" y="120" width="80" height="100" fill="#3498db" rx="5"/>
                            <rect x="170" y="120" width="80" height="100" fill="#3498db" rx="5"/>
                            <rect x="270" y="120" width="80" height="100" fill="#3498db" rx="5"/>
                            <rect x="370" y="120" width="60" height="100" fill="#e74c3c" rx="5"/>
                            <text x="250" y="180" text-anchor="middle" fill="#ffffff" font-size="20" font-weight="bold">SANDWICH SHOP</text>
                            <text x="250" y="200" text-anchor="middle" fill="#ffffff" font-size="14">Restaurant</text>
                            <!-- Burger icon -->
                            <circle cx="380" cy="60" r="30" fill="#f39c12"/>
                            <rect x="365" y="45" width="30" height="8" fill="#8b4513" rx="4"/>
                            <rect x="365" y="55" width="30" height="4" fill="#27ae60" rx="2"/>
                            <rect x="365" y="61" width="30" height="6" fill="#e74c3c" rx="3"/>
                            <rect x="365" y="69" width="30" height="8" fill="#8b4513" rx="4"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">¿Por qué elegirnos?</h2>
                    <p class="section-subtitle">Conoce lo que nos hace únicos</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <h4>Calidad Premium</h4>
                        <p>Ingredientes frescos y de la más alta calidad en cada preparación.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4>Servicio Rápido</h4>
                        <p>Tu pedido listo en tiempo récord, sin comprometer el sabor.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h4>Delivery Gratis</h4>
                        <p>Llevamos tus platillos favoritos directamente a tu puerta.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Menu Preview Section -->
    <section class="menu-preview-section py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">Nuestros Favoritos</h2>
                    <p class="section-subtitle">Una muestra de nuestro delicioso menú</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="menu-preview-card">
                        <div class="menu-preview-image">
                            <svg width="100%" height="200" viewBox="0 0 300 200">
                                <!-- Burger illustration -->
                                <rect x="50" y="150" width="200" height="20" fill="#8b4513" rx="10"/>
                                <rect x="60" y="130" width="180" height="8" fill="#27ae60" rx="4"/>
                                <rect x="55" y="115" width="190" height="15" fill="#e74c3c" rx="7"/>
                                <circle cx="150" cy="107" r="7" fill="#ffff00"/>
                                <rect x="50" y="90" width="200" height="20" fill="#8b4513" rx="10"/>
                                <text x="150" y="60" text-anchor="middle" fill="#333" font-size="16" font-weight="bold">Burger Premium</text>
                            </svg>
                        </div>
                        <div class="menu-preview-content">
                            <h5>Burger's Gourmet</h5>
                            <p>Deliciosas hamburguesas artesanales con los mejores ingredientes.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="menu-preview-card">
                        <div class="menu-preview-image">
                            <svg width="100%" height="200" viewBox="0 0 300 200">
                                <!-- Hotdog illustration -->
                                <rect x="40" y="110" width="220" height="30" fill="#8b4513" rx="15"/>
                                <rect x="50" y="115" width="200" height="20" fill="#d2691e" rx="10"/>
                                <text x="150" y="60" text-anchor="middle" fill="#333" font-size="16" font-weight="bold">Hotdog Especial</text>
                            </svg>
                        </div>
                        <div class="menu-preview-content">
                            <h5>VULDOGG'S</h5>
                            <p>Hotdogs únicos con combinaciones de sabores sorprendentes.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="menu-preview-card">
                        <div class="menu-preview-image">
                            <svg width="100%" height="200" viewBox="0 0 300 200">
                                <!-- Fries illustration -->
                                <rect x="100" y="80" width="8" height="60" fill="#ffd700" transform="rotate(-10 104 110)"/>
                                <rect x="120" y="75" width="8" height="70" fill="#ffd700" transform="rotate(5 124 110)"/>
                                <rect x="140" y="70" width="8" height="75" fill="#ffd700"/>
                                <rect x="160" y="75" width="8" height="70" fill="#ffd700" transform="rotate(-5 164 110)"/>
                                <rect x="180" y="80" width="8" height="60" fill="#ffd700" transform="rotate(10 184 110)"/>
                                <text x="150" y="60" text-anchor="middle" fill="#333" font-size="16" font-weight="bold">Acompañamientos</text>
                            </svg>
                        </div>
                        <div class="menu-preview-content">
                            <h5>Extras & Bebidas</h5>
                            <p>Complementa tu comida con nuestras papas crujientes y bebidas refrescantes.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12 text-center">
                    <a href="menu.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-utensils"></i> Ver Menú Completo
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact CTA Section -->
    <section class="contact-cta-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3>¿Listo para ordenar?</h3>
                    <p class="mb-0">Contáctanos o visita nuestro local para disfrutar de la mejor comida.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="contact.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-phone"></i> Contactar
                    </a>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/cart.js"></script>
    
    <script>
        // Update cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });
    </script>
</body>
</html>
