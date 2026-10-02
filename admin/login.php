<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Simple authentication - in production, use proper password hashing
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user_id'] = 1;
        $_SESSION['usuario'] = 'admin';
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Credenciales incorrectas';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Sandwich Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: 210 79% 46%;
            --danger-color: 0 65% 51%;
            --background: 210 11% 15%;
            --surface: 210 11% 20%;
            --text-color: 0 0% 100%;
            --border-color: 210 11% 30%;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, hsl(var(--background)) 0%, hsl(210 11% 10%) 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: hsl(var(--text-color));
        }

        .login-container {
            background: hsl(var(--surface));
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            border: 1px solid hsl(var(--border-color));
            width: 100%;
            max-width: 400px;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo i {
            font-size: 3rem;
            color: hsl(var(--primary-color));
            margin-bottom: 0.5rem;
        }

        h1 {
            color: hsl(var(--primary-color));
            text-align: center;
            margin: 0 0 2rem 0;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .credentials-info {
            background: hsl(var(--primary-color) / 0.1);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 1px solid hsl(var(--primary-color) / 0.3);
        }

        .credentials-info h3 {
            margin: 0 0 0.5rem 0;
            color: hsl(var(--primary-color));
            font-size: 1rem;
        }

        .credentials-info p {
            margin: 0.25rem 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: hsl(var(--text-color));
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid hsl(var(--border-color));
            border-radius: 8px;
            background: hsl(var(--background));
            color: hsl(var(--text-color));
            box-sizing: border-box;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        input:focus {
            outline: none;
            border-color: hsl(var(--primary-color));
            box-shadow: 0 0 0 3px hsl(var(--primary-color) / 0.1);
        }

        button {
            width: 100%;
            padding: 0.75rem;
            background: hsl(var(--primary-color));
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }

        button:hover {
            background: hsl(var(--primary-color) / 0.8);
            transform: translateY(-1px);
        }

        button:active {
            transform: translateY(0);
        }

        .error {
            color: hsl(var(--danger-color));
            text-align: center;
            margin-top: 1rem;
            font-weight: 500;
            background: hsl(var(--danger-color) / 0.1);
            padding: 0.75rem;
            border-radius: 8px;
            border: 1px solid hsl(var(--danger-color) / 0.3);
        }

        .footer {
            text-align: center;
            margin-top: 2rem;
            color: hsl(var(--text-color) / 0.6);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <i class="fas fa-utensils"></i>
            <h1>Panel de Administración</h1>
        </div>
        
        <div class="credentials-info">
            <h3><i class="fas fa-key"></i> Credenciales de Acceso:</h3>
            <p>Usuario: <strong>admin</strong></p>
            <p>Contraseña: <strong>admin123</strong></p>
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Usuario:</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>

            <?php if ($error): ?>
                <div class="error">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
        </form>

        <div class="footer">
            <p>Sandwich Shop Management System</p>
        </div>
    </div>
</body>
</html>
