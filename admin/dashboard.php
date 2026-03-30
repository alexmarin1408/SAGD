<?php
define('BASE_URL', '/SAGD/');
require_once __DIR__ . '/../includes/funciones.php';
requerirAuth();

$db = getDB();
$totalMiembros   = $db->query("SELECT COUNT(*) FROM miembros WHERE activo=1")->fetchColumn();
$totalAnuncios   = $db->query("SELECT COUNT(*) FROM anuncios WHERE activo=1")->fetchColumn();
$totalActividades= $db->query("SELECT COUNT(*) FROM actividades WHERE activo=1")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – SAGD</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        header { background: #1F3864; color: white; padding: 15px 30px;
            display: flex; justify-content: space-between; align-items: center; }
        header h1 { font-size: 20px; }
        header a { color: #BDD7EE; font-size: 13px; text-decoration: none; }
        header a:hover { color: white; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        h2 { color: #1F3864; margin-bottom: 20px; font-size: 18px; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat { background: white; border-radius: 8px; padding: 24px;
            text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-top: 4px solid #2E75B6; }
        .stat .num { font-size: 42px; font-weight: bold; color: #1F3864; }
        .stat .label { font-size: 13px; color: #777; margin-top: 5px; }
        .menu { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        .menu-item { background: white; border-radius: 8px; padding: 20px;
            text-decoration: none; color: #1F3864; font-weight: bold; font-size: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 5px solid #1F3864;
            display: flex; align-items: center; gap: 10px; }
        .menu-item:hover { background: #EBF3FB; }
    </style>
</head>
<body>
<header>
    <div style="display:flex;align-items:center;gap:12px">
    <img src="/SAGD/logo_iglesia.png" alt="Logo" style="height:45px;object-fit:contain">
    <h1>SAGD – Panel de Administraci&oacute;n</h1>
    </div>
    <div>
        Hola, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?> &nbsp;|&nbsp;
        <a href="../logout.php">Cerrar sesi&oacute;n</a> &nbsp;|&nbsp;
        <a href="../index.php" target="_blank">Ver p&aacute;gina p&uacute;blica</a>
    </div>
</header>

<div class="container">
    <h2>Resumen del sistema</h2>
    <div class="stats">
        <div class="stat"><div class="num"><?= $totalMiembros ?></div><div class="label">Miembros activos</div></div>
        <div class="stat"><div class="num"><?= $totalAnuncios ?></div><div class="label">Anuncios activos</div></div>
        <div class="stat"><div class="num"><?= $totalActividades ?></div><div class="label">Actividades pr&oacute;ximas</div></div>
    </div>

    <h2>M&oacute;dulos</h2>
    <div class="menu">
        <a href="miembros.php" class="menu-item">👥 Gesti&oacute;n de Miembros</a>
        <a href="anuncios.php" class="menu-item">📢 Anuncios</a>
        <a href="cronograma.php" class="menu-item">📅 Cronograma Semanal</a>
        <a href="actividades.php" class="menu-item">🎯 Actividades Especiales</a>
    </div>
</div>
</body>
</html>
