<?php
require_once __DIR__ . '/includes/funciones.php';
iniciarSesionSegura();

// Si ya está autenticado, redirigir al dashboard
if (estaAutenticado()) {
    header('Location: admin/dashboard.php');
    exit;
}

$error = '';
$msg   = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo   = limpiar($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($correo) || empty($password)) {
        $error = 'Por favor completa todos los campos.';
    } else {
        $resultado = login($correo, $password);
        if ($resultado['ok']) {
            header('Location: admin/dashboard.php');
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
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Ingresar – SAGD</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --azul: #0d2a4e; --azul-m: #1a4a82; --azul-c: #2e7bc4;
      --dorado: #c9a84c; --dorado-c: #f0d080; --blanco: #fff;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family:'Lato',sans-serif;
      min-height:100vh;
      display:flex; align-items:center; justify-content:center;
      background: linear-gradient(135deg, var(--azul) 0%, var(--azul-m) 60%, var(--azul-c) 100%);
    }
    .card {
      background:var(--blanco); border-radius:12px;
      padding:2.8rem 2.4rem; width:100%; max-width:400px;
      box-shadow:0 24px 64px rgba(0,0,0,0.3);
    }
    .logo { text-align:center; margin-bottom:1.8rem; }
    .logo img { height:60px; object-fit:contain; }
    .logo h1 { font-family:'Cinzel',serif; font-size:1.2rem; color:var(--azul); margin-top:0.6rem; }
    .logo p  { font-size:0.78rem; color:#888; margin-top:0.2rem; }
    h2 { font-family:'Cinzel',serif; color:var(--azul); font-size:1.05rem; margin-bottom:1.5rem; text-align:center; }
    .form-group { margin-bottom:1.1rem; }
    label { display:block; font-size:0.85rem; font-weight:700; color:var(--azul); margin-bottom:0.4rem; }
    input[type=email], input[type=password] {
      width:100%; padding:0.7rem 0.9rem;
      border:1.5px solid #d0d8e8; border-radius:6px;
      font-size:0.9rem; font-family:'Lato',sans-serif;
      outline:none; transition:border-color 0.2s;
    }
    input:focus { border-color:var(--azul-c); }
    .btn {
      width:100%; background:var(--azul); color:var(--blanco);
      border:none; padding:0.8rem; border-radius:6px;
      font-size:0.95rem; font-weight:700; cursor:pointer;
      font-family:'Lato',sans-serif; transition:background 0.2s; margin-top:0.5rem;
    }
    .btn:hover { background:var(--azul-c); }
    .error { background:#fff0f0; border:1px solid #f5c6cb; color:#c0392b; padding:0.7rem 1rem; border-radius:6px; font-size:0.85rem; margin-bottom:1rem; }
    .info  { background:#e8f4fd; border:1px solid #bee5fc; color:#1a5276; padding:0.7rem 1rem; border-radius:6px; font-size:0.85rem; margin-bottom:1rem; }
    .back  { text-align:center; margin-top:1.2rem; font-size:0.85rem; }
    .back a { color:var(--azul-c); text-decoration:none; font-weight:700; }
    .divider { border:none; border-top:1px solid #e8edf3; margin:1.2rem 0; }
  </style>
</head>
<body>
<div class="card">
  <div class="logo">
    <img src="logo_iglesia.png" alt="Iglesia Cristiana Emanuel"/>
    <h1>Iglesia Cristiana Emanuel</h1>
    <p>Sistema SAGD</p>
  </div>
  <hr class="divider"/>
  <h2>Inicio de sesión</h2>

  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($msg === 'sesion_expirada'): ?>
    <div class="info">Tu sesión ha expirado. Por favor ingresa de nuevo.</div>
  <?php endif; ?>

  <form method="POST" action="login.php" novalidate>
    <div class="form-group">
      <label for="correo">Correo electrónico</label>
      <input type="email" id="correo" name="correo"
             placeholder="ejemplo@correo.com"
             value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>" required/>
    </div>
    <div class="form-group">
      <label for="password">Contraseña</label>
      <input type="password" id="password" name="password" placeholder="••••••••" required/>
    </div>
    <button type="submit" class="btn">Iniciar sesión</button>
  </form>

  <div class="back">
    <a href="index.php">← Volver a la página principal</a>
  </div>
</div>
</body>
</html>
