<?php
define('BASE_URL', '/SAGD/');
require_once __DIR__ . '/config/database.php';

$db = getDB();

$anuncios = $db->query(
    "SELECT * FROM anuncios WHERE activo=1 ORDER BY creado_en DESC LIMIT 6"
)->fetchAll();

$cronograma = $db->query(
    "SELECT * FROM cronograma WHERE activo=1 ORDER BY FIELD(dia_semana,'Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo')"
)->fetchAll();

$actividades = $db->query(
    "SELECT * FROM actividades WHERE activo=1 AND fecha_inicio >= CURDATE() ORDER BY fecha_inicio ASC LIMIT 5"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iglesia Cristiana Emanuel – SAGD</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; color: #333; }
        header { background: #1F3864; color: white; padding: 20px; text-align: center; }
        header h1 { font-size: 28px; margin-bottom: 5px; }
        header p  { font-size: 14px; opacity: 0.85; }
        nav { background: #2E75B6; padding: 10px; text-align: center; }
        nav a { color: white; text-decoration: none; margin: 0 15px; font-size: 14px; }
        nav a:hover { text-decoration: underline; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .section-title { font-size: 20px; font-weight: bold; color: #1F3864;
            border-bottom: 3px solid #2E75B6; padding-bottom: 8px; margin-bottom: 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 20px; border-left: 4px solid #2E75B6; }
        .card h3 { color: #1F3864; margin-bottom: 8px; font-size: 16px; }
        .card p  { font-size: 14px; color: #555; line-height: 1.5; }
        .card .fecha { font-size: 12px; color: #2E75B6; margin-top: 8px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; background: white;
            border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 40px; }
        th { background: #1F3864; color: white; padding: 12px; text-align: left; font-size: 14px; }
        td { padding: 11px 12px; font-size: 14px; border-bottom: 1px solid #eee; }
        tr:hover td { background: #f0f7ff; }
        .btn-admin { display: inline-block; background: #1F3864; color: white;
            padding: 10px 24px; border-radius: 6px; text-decoration: none; font-size: 14px; }
        .btn-admin:hover { background: #2E75B6; }
        footer { background: #1F3864; color: white; text-align: center; padding: 20px; font-size: 13px; margin-top: 20px; }
    </style>
</head>
<body>

<header>
    <div style="display:flex;align-items:center;gap:16px;justify-content:center;flex-wrap:wrap">
        <img src="/SAGD/logo_iglesia.png" alt="Iglesia Cristiana Emanuel" style="height:60px;object-fit:contain">
        <div>
            <h1>Iglesia Cristiana Emanuel</h1>
            <p>La Gabriela, Medell&iacute;n &bull; Sistema SAGD v1.0</p>
        </div>
    </div>
</header>

<nav>
    <a href="index.php">Inicio</a>
    <a href="#anuncios">Anuncios</a>
    <a href="#cronograma">Cronograma</a>
    <a href="#actividades">Actividades</a>
    <a href="login.php">Administrar</a>
</nav>

<div class="container">

    <!-- ANUNCIOS -->
    <div id="anuncios">
        <h2 class="section-title">Anuncios</h2>
        <?php if (empty($anuncios)): ?>
            <p>No hay anuncios disponibles.</p>
        <?php else: ?>
        <div class="grid">
            <?php foreach ($anuncios as $a): ?>
            <div class="card">
                <h3><?= htmlspecialchars($a['titulo']) ?></h3>
                <p><?= htmlspecialchars($a['contenido']) ?></p>
                <?php if ($a['fecha_evento']): ?>
                <p class="fecha">Fecha: <?= htmlspecialchars($a['fecha_evento']) ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- CRONOGRAMA -->
    <div id="cronograma">
        <h2 class="section-title">Cronograma Semanal</h2>
        <table>
            <thead>
                <tr><th>D&iacute;a</th><th>Actividad</th><th>Horario</th><th>Lugar</th></tr>
            </thead>
            <tbody>
            <?php foreach ($cronograma as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['dia_semana']) ?></td>
                    <td><?= htmlspecialchars($c['actividad']) ?></td>
                    <td><?= htmlspecialchars(substr($c['hora_inicio'],0,5)) ?> – <?= htmlspecialchars(substr($c['hora_fin'],0,5)) ?></td>
                    <td><?= htmlspecialchars($c['lugar']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ACTIVIDADES -->
    <div id="actividades">
        <h2 class="section-title">Actividades Especiales</h2>
        <?php if (empty($actividades)): ?>
            <p>No hay actividades pr&oacute;ximas.</p>
        <?php else: ?>
        <div class="grid">
            <?php foreach ($actividades as $a): ?>
            <div class="card">
                <h3><?= htmlspecialchars($a['titulo']) ?></h3>
                <p><?= htmlspecialchars($a['descripcion']) ?></p>
                <p class="fecha"><?= htmlspecialchars($a['fecha_inicio']) ?> al <?= htmlspecialchars($a['fecha_fin']) ?></p>
                <p class="fecha">Lugar: <?= htmlspecialchars($a['lugar']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <p style="text-align:center; margin-bottom:30px;">
        <a href="login.php" class="btn-admin">Panel de Administraci&oacute;n</a>
    </p>

</div>

<footer>
    <p>Sistema SAGD v1.0 &bull; Iglesia Cristiana Emanuel &bull; La Gabriela, Medell&iacute;n</p>
    <p>Desarrollado por Alexander De Jes&uacute;s Mar&iacute;n Mu&ntilde;oz &bull; SENA Ficha 2977457</p>
</footer>

</body>
</html>
