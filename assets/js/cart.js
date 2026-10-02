/**
 * Cart functionality for Vultur Restaurant
 * Handles cart operations, updates, and checkout process
 */

// Global cart state
let cartState = {
    items: [],
    total: 0,
    count: 0
};

/**
 * Update cart count in navigation
 */
function updateCartCount() {
    fetch('get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cartCountElements = document.querySelectorAll('.cart-count');
                cartCountElements.forEach(element => {
                    element.textContent = data.count;
                    
                    // Add animation for count change
                    element.classList.add('scale-in');
                    setTimeout(() => {
                        element.classList.remove('scale-in');
                    }, 300);
                });
            }
        })
        .catch(error => {
            console.error('Error updating cart count:', error);
        });
}

/**
 * Load cart items and display them
 */
function loadCart() {
    fetch('api/get_cart.php')
        .then(response => response.json())
        .then(data => {
            const cartContent = document.getElementById('cart-content');
            const emptyCart = document.getElementById('empty-cart');

            if (data.success && Object.keys(data.items).length > 0) {
                emptyCart.classList.add('d-none');
                cartContent.innerHTML = '';

                Object.values(data.items).forEach(item => {
                    const itemHtml = `
                        <div class="card mb-3">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5>${item.name} (${item.size})</h5>
                                    <p class="mb-0">Cantidad: ${item.quantity}</p>
                                    <p class="mb-0">Precio: $${item.price}</p>
                                </div>
                                <div>
                                    <strong>Total: $${item.price * item.quantity}</strong>
                                </div>
                            </div>
                        </div>
                    `;
                    cartContent.innerHTML += itemHtml;
                });
            } else {
                cartContent.innerHTML = '';
                emptyCart.classList.remove('d-none');
            }
        });
}

/**
 * Display cart items in the cart page
 */
function displayCartItems() {
    const cartContent = document.getElementById('cart-content');
    const emptyCart = document.getElementById('empty-cart');
    
    if (!cartContent) return;
    
    if (cartState.items.length === 0) {
        showEmptyCart();
        return;
    }
    
    emptyCart.classList.add('d-none');
    
    let html = '<div class="row">';
    html += '<div class="col-lg-8">';
    
    cartState.items.forEach(item => {
        html += createCartItemHTML(item);
    });
    
    html += '</div>';
    html += '<div class="col-lg-4">';
    html += '<div id="cart-summary-container"></div>';
    html += '</div>';
    html += '</div>';
    
    cartContent.innerHTML = html;
    cartContent.classList.remove('d-none');
}

/**
 * Create HTML for a single cart item
 */
function createCartItemHTML(item) {
    let extrasText = '';
    if (item.extras && item.extras.length > 0) {
        extrasText = '<div class="cart-item-extras"><i class="fas fa-plus-circle"></i> Extras incluidos</div>';
    }
    
    return `
        <div class="cart-item" data-key="${item.key}">
            <div class="d-flex align-items-center">
                <div class="cart-item-image me-3">
                    <i class="fas fa-utensils text-muted"></i>
                </div>
                <div class="cart-item-details flex-grow-1">
                    <h5>${item.name}</h5>
                    <div class="text-muted">Tamaño: ${item.size}</div>
                    ${extrasText}
                </div>
                <div class="cart-item-controls me-3">
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantity('${item.key}', ${item.quantity - 1})">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" class="quantity-input" value="${item.quantity}" 
                               onchange="updateQuantity('${item.key}', this.value)" min="1" max="10">
                        <button class="quantity-btn" onclick="updateQuantity('${item.key}', ${item.quantity + 1})">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="cart-item-price me-3">
                    <div class="fw-bold">$${item.subtotal.toLocaleString()}</div>
                </div>
                <div class="cart-item-actions">
                    <button class="btn btn-outline-danger btn-sm" onclick="removeFromCart('${item.key}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
}

/**
 * Display cart summary
 */
function displayCartSummary() {
    const container = document.getElementById('cart-summary-container');
    if (!container) return;
    
    const html = `
        <div class="cart-summary">
            <h5><i class="fas fa-receipt"></i> Resumen del Pedido</h5>
            <div class="summary-line">
                <span>Subtotal:</span>
                <span>$${cartState.total.toLocaleString()}</span>
            </div>
            <div class="summary-line">
                <span>Total:</span>
                <span>$${cartState.total.toLocaleString()}</span>
            </div>
            <button class="btn btn-primary w-100 mt-3" onclick="showCheckoutModal()">
                <i class="fas fa-credit-card"></i> Proceder al Checkout
            </button>
            <a href="menu.php" class="btn btn-outline-secondary w-100 mt-2">
                <i class="fas fa-arrow-left"></i> Seguir Comprando
            </a>
        </div>
    `;
    
    container.innerHTML = html;
}

/**
 * Show empty cart message
 */
function showEmptyCart() {
    const cartContent = document.getElementById('cart-content');
    const emptyCart = document.getElementById('empty-cart');
    
    if (cartContent) cartContent.classList.add('d-none');
    if (emptyCart) emptyCart.classList.remove('d-none');
}

/**
 * Update item quantity in cart
 */
function updateQuantity(cartKey, newQuantity) {
    newQuantity = parseInt(newQuantity);
    
    if (newQuantity < 1) {
        removeFromCart(cartKey);
        return;
    }
    
    if (newQuantity > 10) {
        showAlert('warning', 'Cantidad máxima por producto: 10');
        return;
    }
    
    fetch('api/update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            key: cartKey,
            quantity: newQuantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCart();
            updateCartCount();
            showAlert('success', data.message);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error updating quantity:', error);
        showAlert('danger', 'Error al actualizar la cantidad');
    });
}

/**
 * Remove item from cart
 */
function removeFromCart(cartKey) {
    if (!confirm('¿Estás seguro de eliminar este producto del carrito?')) {
        return;
    }
    
    fetch('api/remove_from_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            key: cartKey
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCart();
            updateCartCount();
            showAlert('success', data.message);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error removing item:', error);
        showAlert('danger', 'Error al eliminar el producto');
    });
}

/**
 * Show checkout modal
 */
function showCheckoutModal() {
    if (cartState.items.length === 0) {
        showAlert('warning', 'El carrito está vacío');
        return;
    }
    
    // Populate checkout summary
    const checkoutSummary = document.getElementById('checkout-summary');
    if (checkoutSummary) {
        let html = '<div class="table-responsive"><table class="table table-sm">';
        
        cartState.items.forEach(item => {
            html += `
                <tr>
                    <td>${item.name} (${item.size})</td>
                    <td class="text-center">${item.quantity}x</td>
                    <td class="text-end">$${item.subtotal.toLocaleString()}</td>
                </tr>
            `;
        });
        
        html += `
            <tr class="table-primary">
                <td colspan="2"><strong>Total:</strong></td>
                <td class="text-end"><strong>$${cartState.total.toLocaleString()}</strong></td>
            </tr>
        `;
        html += '</table></div>';
        
        checkoutSummary.innerHTML = html;
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('checkoutModal'));
    modal.show();
}

/**
 * Process checkout
 */
function processCheckout() {
    const form = document.getElementById('checkout-form');
    const formData = new FormData(form);
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Prepare data
    const orderData = {
        customer_name: formData.get('customer_name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        order_type: formData.get('order_type'),
        address: formData.get('address')
    };
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="loading-spinner"></span> Procesando...';
    submitBtn.disabled = true;
    
    // Submit order
    fetch('process_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();
            
            // Show success message
            showAlert('success', `¡Pedido #${data.order_id} creado exitosamente! Te contactaremos pronto.`);
            
            // Clear cart and redirect
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 3000);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error processing checkout:', error);
        showAlert('danger', 'Error al procesar el pedido. Por favor, inténtelo de nuevo.');
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

/**
 * Show alert message
 */
function showAlert(type, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.dynamic-alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show dynamic-alert position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }
    }, 5000);
}

/**
 * Add animation class to element
 */
function addAnimation(element, animationClass) {
    element.classList.add(animationClass);
    setTimeout(() => {
        element.classList.remove(animationClass);
    }, 300);
}

/**
 * Format price for display
 */
function formatPrice(price) {
    return '$' + parseInt(price).toLocaleString();
}

/**
 * Validate email format
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Validate phone format
 */
function validatePhone(phone) {
    const re = /^[\d\s\-\+\(\)]+$/;
    return re.test(phone) && phone.replace(/\D/g, '').length >= 10;
}

/**
 * Initialize cart functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Update cart count on all pages
    updateCartCount();
    
    // Initialize cart page if present
    if (document.getElementById('cart-content')) {
        loadCart();
    }
    
    // Handle checkout form validation
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Additional validation
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            
            if (!validateEmail(email)) {
                showAlert('danger', 'Por favor, ingrese un email válido');
                return;
            }
            
            if (!validatePhone(phone)) {
                showAlert('danger', 'Por favor, ingrese un teléfono válido');
                return;
            }
            
            processCheckout();
        });
    }
    
    // Handle delivery/pickup selection
    const orderTypeRadios = document.querySelectorAll('input[name="order_type"]');
    orderTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const addressField = document.getElementById('address-field');
            const addressInput = document.getElementById('address');
            
            if (this.value === 'delivery') {
                addressField.classList.remove('d-none');
                addressInput.required = true;
            } else {
                addressField.classList.add('d-none');
                addressInput.required = false;
                addressInput.value = '';
            }
        });
    });
});

// Export functions for use in other scripts
window.cartFunctions = {
    updateCartCount,
    loadCart,
    updateQuantity,
    removeFromCart,
    showCheckoutModal,
    processCheckout,
    showAlert
};
