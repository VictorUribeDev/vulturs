<?php
session_start();
include '../includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$success = false;
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price_simple = !empty($_POST['price_simple']) ? (float)$_POST['price_simple'] : null;
    $price_doble = !empty($_POST['price_doble']) ? (float)$_POST['price_doble'] : null;
    $category = $_POST['category'] ?? '';
    
    // Validation
    if (empty($name)) $errors[] = 'El nombre es requerido';
    if (empty($category)) $errors[] = 'La categoría es requerida';
    if (empty($price_simple) && empty($price_doble)) $errors[] = 'Al menos un precio es requerido';
    
    // Handle file upload
    $image_name = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/menu/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $image_name = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $image_name;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $errors[] = 'Error al subir la imagen';
            }
        } else {
            $errors[] = 'Formato de imagen no válido';
        }
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO menu_items (name, description, price_simple, price_doble, category, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price_simple, $price_doble, $category, $image_name]);
            $success = true;
            
            // Reset form
            $_POST = [];
        } catch (Exception $e) {
            $errors[] = 'Error al guardar el item: ' . $e->getMessage();
        }
    }
}
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-plus"></i> Agregar Nuevo Item al Menú
            </h1>
            <a href="menu_manager.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Menú
            </a>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                Item agregado exitosamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre del Item *</label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Descripción</label>
                                <textarea id="description" name="description" class="form-control" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="price_simple" class="form-label">Precio Simple</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" id="price_simple" name="price_simple" class="form-control" 
                                                   step="1" min="0" value="<?= htmlspecialchars($_POST['price_simple'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="price_doble" class="form-label">Precio Doble/Grande</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" id="price_doble" name="price_doble" class="form-control" 
                                                   step="1" min="0" value="<?= htmlspecialchars($_POST['price_doble'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="category" class="form-label">Categoría *</label>
                                <select id="category" name="category" class="form-select" required>
                                    <option value="">Seleccione una categoría</option>
                                    <option value="hamburguesas" <?= ($_POST['category'] ?? '') === 'hamburguesas' ? 'selected' : '' ?>>Burger's</option>
                                    <option value="hotdogs" <?= ($_POST['category'] ?? '') === 'hotdogs' ? 'selected' : '' ?>>VULDOGG'S</option>
                                    <option value="acompañamientos" <?= ($_POST['category'] ?? '') === 'acompañamientos' ? 'selected' : '' ?>>Acompañamientos</option>
                                    <option value="bebidas" <?= ($_POST['category'] ?? '') === 'bebidas' ? 'selected' : '' ?>>Bebidas</option>
                                    <option value="postres" <?= ($_POST['category'] ?? '') === 'postres' ? 'selected' : '' ?>>Postres</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="image" class="form-label">Imagen del Item</label>
                                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                                <div class="form-text">Formatos soportados: JPG, PNG, GIF, WebP</div>
                            </div>
                            
                            <div class="border rounded p-3 text-center bg-light">
                                <i class="fas fa-image fa-3x text-muted mb-2"></i>
                                <p class="mb-0 small text-muted">Vista previa de la imagen</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Item
                        </button>
                        <a href="menu_manager.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Image preview
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.querySelector('.bg-light');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" class="img-fluid" style="max-height: 200px;">`;
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = `
            <i class="fas fa-image fa-3x text-muted mb-2"></i>
            <p class="mb-0 small text-muted">Vista previa de la imagen</p>
        `;
    }
});

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
