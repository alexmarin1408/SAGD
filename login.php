<?php
define('BASE_URL', '/SAGD/');
require_once __DIR__ . '/includes/funciones.php';

iniciarSesion();
if (!empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo   = limpiar($_POST['correo']   ?? '');
    $password = limpiar($_POST['password'] ?? '');

    if (empty($correo) || empty($password)) {
        $error = 'Por favor complete todos los campos.';
    } else {
        $resultado = login($correo, $password);
        if ($resultado['ok']) {
            header('Location: ' . BASE_URL . 'admin/dashboard.php');
            exit;
        } else {
            $error = $resultado['msg'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesi&oacute;n – SAGD</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #1F3864;
            display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: white; border-radius: 12px; padding: 40px;
            width: 100%; max-width: 420px; box-shadow: 0 8px 32px rgba(0,0,0,0.3); }
        .card h1 { color: #1F3864; font-size: 26px; text-align: center; margin-bottom: 6px; }
        .card p.sub { text-align: center; color: #777; font-size: 13px; margin-bottom: 28px; }
        label { display: block; font-size: 13px; font-weight: bold; color: #333; margin-bottom: 5px; }
        input { width: 100%; padding: 11px 14px; border: 1px solid #ccc;
            border-radius: 6px; font-size: 14px; margin-bottom: 16px; outline: none; }
        input:focus { border-color: #2E75B6; }
        button { width: 100%; padding: 13px; background: #1F3864; color: white;
            border: none; border-radius: 6px; font-size: 15px; cursor: pointer; font-weight: bold; }
        button:hover { background: #2E75B6; }
        .error { background: #fce4d6; color: #843c0c; padding: 10px 14px;
            border-radius: 6px; font-size: 13px; margin-bottom: 16px; border-left: 4px solid #c00; }
        .back { text-align: center; margin-top: 18px; }
        .back a { color: #2E75B6; font-size: 13px; text-decoration: none; }
        .back a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="card">
    <div style="text-align:center;margin-bottom:10px"><img src="/SAGD/logo_iglesia.png" alt="Logo" style="height:55px;object-fit:contain"></div>
    <h1>SAGD</h1>
    <p class="sub">Sistema de Almacenamiento y Gesti&oacute;n de Datos<br>Iglesia Cristiana Emanuel</p>

    <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="correo">Correo electr&oacute;nico</label>
        <input type="email" id="correo" name="correo"
               value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
               placeholder="correo@ejemplo.com" required>

        <label for="password">Contrase&ntilde;a</label>
        <input type="password" id="password" name="password"
               placeholder="Ingrese su contrase&ntilde;a" required>

        <button type="submit">Ingresar</button>
    </form>

    <div class="back"><a href="index.php">&larr; Volver al inicio</a></div>
</div>
</body>
</html>
