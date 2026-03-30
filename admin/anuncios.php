<?php
define('BASE_URL', '/SAGD/');
require_once __DIR__ . '/../includes/funciones.php';
requerirAuth();

$db = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['id'] ?? 0);
    $titulo   = limpiar($_POST['titulo']   ?? '');
    $contenido= limpiar($_POST['contenido']?? '');
    $fecha    = limpiar($_POST['fecha_evento'] ?? '') ?: null;

    if ($titulo && $contenido) {
        if ($id) {
            $db->prepare("UPDATE anuncios SET titulo=?,contenido=?,fecha_evento=? WHERE id=?")
               ->execute([$titulo,$contenido,$fecha,$id]);
            $msg = 'Anuncio actualizado.';
        } else {
            $db->prepare("INSERT INTO anuncios(titulo,contenido,fecha_evento) VALUES(?,?,?)")
               ->execute([$titulo,$contenido,$fecha]);
            $msg = 'Anuncio creado.';
        }
    }
}

if (isset($_GET['eliminar'])) {
    $db->prepare("UPDATE anuncios SET activo=0 WHERE id=?")->execute([(int)$_GET['eliminar']]);
    $msg = 'Anuncio eliminado.';
}

$editando = null;
if (isset($_GET['editar'])) {
    $s = $db->prepare("SELECT * FROM anuncios WHERE id=?");
    $s->execute([(int)$_GET['editar']]);
    $editando = $s->fetch();
}

$anuncios = $db->query("SELECT * FROM anuncios WHERE activo=1 ORDER BY creado_en DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Anuncios – SAGD</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}body{font-family:Arial,sans-serif;background:#f5f5f5}
        header{background:#1F3864;color:white;padding:14px 30px;display:flex;justify-content:space-between;align-items:center}
        header h1{font-size:18px}header a{color:#BDD7EE;font-size:13px;text-decoration:none}
        .container{max-width:900px;margin:25px auto;padding:0 20px}
        .msg{background:#e2efda;color:#375623;padding:10px 16px;border-radius:6px;margin-bottom:16px;font-size:13px}
        form.card{background:white;border-radius:8px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,.08);margin-bottom:24px}
        label{font-size:12px;font-weight:bold;color:#333;display:block;margin-bottom:3px;margin-top:10px}
        input,textarea{width:100%;padding:9px 12px;border:1px solid #ccc;border-radius:5px;font-size:13px}
        textarea{min-height:80px;resize:vertical}
        .btn{padding:9px 20px;border:none;border-radius:5px;cursor:pointer;font-size:13px;font-weight:bold;text-decoration:none;display:inline-block}
        .btn-primary{background:#1F3864;color:white}.btn-danger{background:#c00;color:white;font-size:12px;padding:5px 10px}
        .btn-edit{background:#2E75B6;color:white;font-size:12px;padding:5px 10px}
        table{width:100%;border-collapse:collapse;background:white;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08)}
        th{background:#1F3864;color:white;padding:11px 12px;text-align:left;font-size:13px}
        td{padding:10px 12px;font-size:13px;border-bottom:1px solid #eee}tr:hover td{background:#f0f7ff}
    </style>
</head>
<body>
<header>
    <div style="display:flex;align-items:center;gap:12px">
    <img src="/SAGD/logo_iglesia.png" alt="Logo" style="height:45px;object-fit:contain">
    <h1>SAGD – Anuncios</h1>
    </div>
    <div><a href="dashboard.php">← Dashboard</a> &nbsp;|&nbsp; <a href="../logout.php">Cerrar sesión</a></div>
</header>
<div class="container">
    <?php if ($msg): ?><div class="msg"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <h2 style="color:#1F3864;margin-bottom:14px"><?= $editando?'Editar Anuncio':'Nuevo Anuncio' ?></h2>
    <form class="card" method="POST">
        <?php if ($editando): ?><input type="hidden" name="id" value="<?= $editando['id'] ?>"><?php endif; ?>
        <label>Título *</label>
        <input name="titulo" value="<?= htmlspecialchars($editando['titulo']??'') ?>" required>
        <label>Contenido *</label>
        <textarea name="contenido"><?= htmlspecialchars($editando['contenido']??'') ?></textarea>
        <label>Fecha del evento</label>
        <input type="date" name="fecha_evento" value="<?= htmlspecialchars($editando['fecha_evento']??'') ?>">
        <div style="margin-top:14px;display:flex;gap:10px">
            <button class="btn btn-primary"><?= $editando?'Actualizar':'Crear anuncio' ?></button>
            <?php if ($editando): ?><a href="anuncios.php" class="btn btn-primary">Cancelar</a><?php endif; ?>
        </div>
    </form>
    <table>
        <thead><tr><th>ID</th><th>Título</th><th>Fecha evento</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($anuncios as $a): ?>
        <tr>
            <td><?= $a['id'] ?></td>
            <td><?= htmlspecialchars($a['titulo']) ?></td>
            <td><?= htmlspecialchars($a['fecha_evento']??'—') ?></td>
            <td>
                <a href="?editar=<?= $a['id'] ?>" class="btn btn-edit">Editar</a>
                <a href="?eliminar=<?= $a['id'] ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar?')">Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
