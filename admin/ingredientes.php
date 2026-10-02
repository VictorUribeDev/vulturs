<?php

session_start();
include 'includes/db.php';
include 'includes/header.php';

// Agregar ingrediente
if (isset($_POST['add_ingredient'])) {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $category = $_POST['category'];

    if ($name && $category) {
        $stmt = $pdo->prepare("INSERT INTO sandwich_components (name, price, category) VALUES (?, ?, ?)");
        $stmt->execute([$name, $price, $category]);
    }
}

// Eliminar ingrediente
if (isset($_POST['delete_ingredient'])) {
    $id = intval($_POST['ingredient_id']);
    $stmt = $pdo->prepare("DELETE FROM sandwich_components WHERE id = ?");
    $stmt->execute([$id]);
}

// Obtener todos los ingredientes
$ingredients = $pdo->query("SELECT * FROM sandwich_components ORDER BY category, name")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <h2>Gestión de Ingredientes</h2>

    <form method="POST" class="mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="name" class="form-label">Nombre del Ingrediente</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="col-md-2">
                <label for="price" class="form-label">Precio</label>
                <input type="number" step="0" name="price" class="form-control" value="0" required>
            </div>

            <div class="col-md-3">
                <label for="category" class="form-label">Categoría</label>
                <select name="category" class="form-select" required>
                    <option value="carne">Carne</option>
                    <option value="gramaje">Gramaje</option>
                    <option value="ingrediente">Ingrediente</option>
                </select>
            </div>

            <div class="col-md-3">
                <button type="submit" name="add_ingredient" class="btn btn-success w-100">Agregar Ingrediente</button>
            </div>
        </div>
    </form>

    <h4>Lista de Ingredientes</h4>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Categoría</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ingredients as $ing): ?>
                <tr>
                    <td><?= htmlspecialchars($ing['name']) ?></td>
                    <td>$<?= number_format($ing['price'], 0, ',', '.') ?></td>
                    <td><?= ucfirst($ing['category']) ?></td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="ingredient_id" value="<?= $ing['id'] ?>">
                            <button type="submit" name="delete_ingredient" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este ingrediente?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>


