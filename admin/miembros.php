<?php
define('BASE_URL', '/SAGD/');
require_once __DIR__ . '/../includes/funciones.php';
requerirAuth();

$db  = getDB();
$msg = '';

// Crear o editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['id'] ?? 0);
    $nombre   = limpiar($_POST['nombre']   ?? '');
    $apellido = limpiar($_POST['apellido'] ?? '');
    $tipo_doc = limpiar($_POST['tipo_doc'] ?? 'CC');
    $documento= limpiar($_POST['documento']?? '');
    $telefono = limpiar($_POST['telefono'] ?? '');
    $tipo     = limpiar($_POST['tipo']     ?? 'asistente');

    if ($nombre && $apellido && $documento) {
        if ($id) {
            $db->prepare("UPDATE miembros SET nombre=?,apellido=?,tipo_doc=?,documento=?,telefono=?,tipo=? WHERE id=?")
               ->execute([$nombre,$apellido,$tipo_doc,$documento,$telefono,$tipo,$id]);
            $msg = 'Miembro actualizado correctamente.';
        } else {
            $db->prepare("INSERT INTO miembros(nombre,apellido,tipo_doc,documento,telefono,tipo) VALUES(?,?,?,?,?,?)")
               ->execute([$nombre,$apellido,$tipo_doc,$documento,$telefono,$tipo]);
            $msg = 'Miembro registrado correctamente.';
        }
    }
}

// Eliminar
if (isset($_GET['eliminar'])) {
    $db->prepare("UPDATE miembros SET activo=0 WHERE id=?")->execute([(int)$_GET['eliminar']]);
    $msg = 'Miembro eliminado.';
}

// Editar
$editando = null;
if (isset($_GET['editar'])) {
    $editando = $db->prepare("SELECT * FROM miembros WHERE id=?");
    $editando->execute([(int)$_GET['editar']]);
    $editando = $editando->fetch();
}

// Buscar
$busqueda = limpiar($_GET['q'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$limit    = 10;
$offset   = ($page - 1) * $limit;
$where    = "WHERE activo=1";
$params   = [];
if ($busqueda) {
    $where .= " AND (nombre LIKE ? OR apellido LIKE ? OR documento LIKE ?)";
    $params = ["%$busqueda%","%$busqueda%","%$busqueda%"];
}
$total    = $db->prepare("SELECT COUNT(*) FROM miembros $where");
$total->execute($params);
$total    = $total->fetchColumn();
$pages    = ceil($total / $limit);

$stmt = $db->prepare("SELECT * FROM miembros $where ORDER BY nombre ASC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$miembros = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Miembros – SAGD</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}body{font-family:Arial,sans-serif;background:#f5f5f5}
        header{background:#1F3864;color:white;padding:14px 30px;display:flex;justify-content:space-between;align-items:center}
        header h1{font-size:18px}header a{color:#BDD7EE;font-size:13px;text-decoration:none}
        .container{max-width:1100px;margin:25px auto;padding:0 20px}
        h2{color:#1F3864;margin-bottom:16px;font-size:17px}
        .msg{background:#e2efda;color:#375623;padding:10px 16px;border-radius:6px;margin-bottom:16px;font-size:13px}
        form.inline{background:white;border-radius:8px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,.08);margin-bottom:24px}
        .grid2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .grid3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
        label{font-size:12px;font-weight:bold;color:#333;display:block;margin-bottom:3px}
        input,select{width:100%;padding:9px 12px;border:1px solid #ccc;border-radius:5px;font-size:13px}
        .btn{padding:9px 20px;border:none;border-radius:5px;cursor:pointer;font-size:13px;font-weight:bold}
        .btn-primary{background:#1F3864;color:white}.btn-primary:hover{background:#2E75B6}
        .btn-danger{background:#c00;color:white;font-size:12px;padding:5px 10px}
        .btn-edit{background:#2E75B6;color:white;font-size:12px;padding:5px 10px}
        .search{display:flex;gap:10px;margin-bottom:16px}
        .search input{max-width:320px}
        table{width:100%;border-collapse:collapse;background:white;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08)}
        th{background:#1F3864;color:white;padding:11px 12px;text-align:left;font-size:13px}
        td{padding:10px 12px;font-size:13px;border-bottom:1px solid #eee}
        tr:hover td{background:#f0f7ff}
        .badge{padding:3px 8px;border-radius:12px;font-size:11px;font-weight:bold}
        .badge-miembro{background:#1F3864;color:white}.badge-asistente{background:#2E75B6;color:white}
        .pagination{margin-top:16px;display:flex;gap:8px}
        .pagination a{padding:6px 12px;background:white;border:1px solid #ccc;border-radius:4px;font-size:13px;text-decoration:none;color:#1F3864}
        .pagination a.active{background:#1F3864;color:white;border-color:#1F3864}
    </style>
</head>
<body>
<header>
    <div style="display:flex;align-items:center;gap:12px">
    <img src="/SAGD/logo_iglesia.png" alt="Logo" style="height:45px;object-fit:contain">
    <h1>SAGD – Miembros y Asistentes</h1>
    </div>
    <div><a href="dashboard.php">← Dashboard</a> &nbsp;|&nbsp; <a href="../logout.php">Cerrar sesión</a></div>
</header>
<div class="container">
    <?php if ($msg): ?><div class="msg"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <h2><?= $editando ? 'Editar Miembro' : 'Registrar Nuevo Miembro' ?></h2>
    <form class="inline" method="POST">
        <?php if ($editando): ?><input type="hidden" name="id" value="<?= $editando['id'] ?>"><?php endif; ?>
        <div class="grid2" style="margin-bottom:12px">
            <div><label>Nombre *</label><input name="nombre" value="<?= htmlspecialchars($editando['nombre']??'') ?>" required></div>
            <div><label>Apellido *</label><input name="apellido" value="<?= htmlspecialchars($editando['apellido']??'') ?>" required></div>
        </div>
        <div class="grid3" style="margin-bottom:16px">
            <div><label>Tipo doc</label>
                <select name="tipo_doc">
                    <?php foreach(['CC','TI','CE','PP'] as $t): ?>
                    <option <?= ($editando['tipo_doc']??'CC')===$t?'selected':'' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div><label>Documento *</label><input name="documento" value="<?= htmlspecialchars($editando['documento']??'') ?>" required></div>
            <div><label>Teléfono</label><input name="telefono" value="<?= htmlspecialchars($editando['telefono']??'') ?>"></div>
        </div>
        <div style="display:flex;align-items:center;gap:16px">
            <div><label>Tipo</label>
                <select name="tipo">
                    <option <?= ($editando['tipo']??'asistente')==='asistente'?'selected':'' ?>>asistente</option>
                    <option <?= ($editando['tipo']??'')==='miembro'?'selected':'' ?>>miembro</option>
                </select>
            </div>
            <div style="margin-top:16px"><button class="btn btn-primary"><?= $editando ? 'Actualizar' : 'Registrar' ?></button></div>
            <?php if ($editando): ?><div style="margin-top:16px"><a href="miembros.php" class="btn btn-primary">Cancelar</a></div><?php endif; ?>
        </div>
    </form>

    <div class="search">
        <form method="GET" style="display:flex;gap:10px;width:100%">
            <input name="q" placeholder="Buscar por nombre, apellido o documento..." value="<?= htmlspecialchars($busqueda) ?>">
            <button class="btn btn-primary">Buscar</button>
            <?php if ($busqueda): ?><a href="miembros.php" class="btn btn-primary">Limpiar</a><?php endif; ?>
        </form>
    </div>

    <table>
        <thead><tr><th>#</th><th>Nombre</th><th>Documento</th><th>Teléfono</th><th>Tipo</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($miembros as $m): ?>
        <tr>
            <td><?= $m['id'] ?></td>
            <td><?= htmlspecialchars($m['nombre'].' '.$m['apellido']) ?></td>
            <td><?= htmlspecialchars($m['tipo_doc'].': '.$m['documento']) ?></td>
            <td><?= htmlspecialchars($m['telefono']) ?></td>
            <td><span class="badge badge-<?= $m['tipo'] ?>"><?= strtoupper($m['tipo']) ?></span></td>
            <td>
                <a href="?editar=<?= $m['id'] ?>" class="btn btn-edit">Editar</a>
                <a href="?eliminar=<?= $m['id'] ?>" class="btn btn-danger"
                   onclick="return confirm('¿Eliminar este miembro?')">Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($miembros)): ?><tr><td colspan="6" style="text-align:center;color:#999">Sin resultados</td></tr><?php endif; ?>
        </tbody>
    </table>

    <?php if ($pages > 1): ?>
    <div class="pagination">
        <?php for ($i=1;$i<=$pages;$i++): ?>
        <a href="?q=<?= urlencode($busqueda) ?>&page=<?= $i ?>" class="<?= $i===$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
