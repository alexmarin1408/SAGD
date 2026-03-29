<?php
require_once __DIR__ . '/../includes/funciones.php';
requerirAuth();

$db  = getDB();
$msg = '';
$err = '';

// ── ELIMINAR ────────────────────────────────────────────────
if (isset($_GET['eliminar'])) {
    $id   = (int)$_GET['eliminar'];
    $stmt = $db->prepare("UPDATE miembros SET activo=0 WHERE id=?");
    $stmt->execute([$id]);
    $msg = 'Miembro eliminado correctamente.';
}

// ── GUARDAR (crear o editar) ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['id'] ?? 0);
    $nombre   = limpiar($_POST['nombre']   ?? '');
    $apellido = limpiar($_POST['apellido'] ?? '');
    $tipo_doc = limpiar($_POST['tipo_doc'] ?? 'CC');
    $documento= limpiar($_POST['documento']?? '');
    $telefono = limpiar($_POST['telefono'] ?? '');
    $direccion= limpiar($_POST['direccion']?? '');
    $correo   = limpiar($_POST['correo']   ?? '');
    $tipo     = limpiar($_POST['tipo']     ?? 'asistente');
    $fecha    = limpiar($_POST['fecha_ingreso'] ?? '');

    if (!$nombre || !$apellido || !$documento) {
        $err = 'Nombre, apellido y documento son obligatorios.';
    } else {
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE miembros SET nombre=?,apellido=?,tipo_doc=?,documento=?,telefono=?,direccion=?,correo=?,tipo=?,fecha_ingreso=? WHERE id=?");
            $stmt->execute([$nombre,$apellido,$tipo_doc,$documento,$telefono,$direccion,$correo,$tipo,$fecha?:null,$id]);
            $msg = 'Miembro actualizado correctamente.';
        } else {
            $stmt = $db->prepare("INSERT INTO miembros (nombre,apellido,tipo_doc,documento,telefono,direccion,correo,tipo,fecha_ingreso) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$nombre,$apellido,$tipo_doc,$documento,$telefono,$direccion,$correo,$tipo,$fecha?:null]);
            $msg = 'Miembro registrado correctamente.';
        }
    }
}

// ── BUSCAR ───────────────────────────────────────────────────
$busqueda = limpiar($_GET['q'] ?? '');
$pagina   = max(1,(int)($_GET['p'] ?? 1));
$porPag   = 10;

$where = "WHERE m.activo=1";
$params = [];
if ($busqueda) {
    $where  .= " AND (m.nombre LIKE ? OR m.apellido LIKE ? OR m.documento LIKE ?)";
    $params  = ["%$busqueda%","%$busqueda%","%$busqueda%"];
}

$total  = $db->prepare("SELECT COUNT(*) FROM miembros m $where");
$total->execute($params);
$total  = (int)$total->fetchColumn();
$pag    = paginar($total, $porPag, $pagina);

$stmt   = $db->prepare("SELECT * FROM miembros m $where ORDER BY creado_en DESC LIMIT $porPag OFFSET {$pag['offset']}");
$stmt->execute($params);
$miembros = $stmt->fetchAll();

// Editar: cargar datos
$editando = null;
if (isset($_GET['editar'])) {
    $editando = $db->prepare("SELECT * FROM miembros WHERE id=? AND activo=1");
    $editando->execute([(int)$_GET['editar']]);
    $editando = $editando->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Miembros – SAGD</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet"/>
  <style>
    :root{--azul:#0d2a4e;--azul-m:#1a4a82;--azul-c:#2e7bc4;--dorado:#c9a84c;--blanco:#fff;--gris:#f4f7fb;--sidebar-w:240px;}
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:'Lato',sans-serif;background:var(--gris);display:flex;min-height:100vh;}
    .sidebar{width:var(--sidebar-w);background:var(--azul);display:flex;flex-direction:column;min-height:100vh;position:fixed;top:0;left:0;z-index:50;}
    .sidebar-logo{padding:1.4rem 1.2rem;border-bottom:1px solid rgba(255,255,255,0.1);text-align:center;}
    .sidebar-logo img{height:44px;object-fit:contain;}
    .sidebar-logo span{display:block;color:var(--dorado);font-family:'Cinzel',serif;font-size:0.78rem;margin-top:0.4rem;}
    nav.sidebar-nav{flex:1;padding:1rem 0;}
    .nav-section{font-size:0.65rem;letter-spacing:0.15em;text-transform:uppercase;color:rgba(255,255,255,0.35);padding:1rem 1.2rem 0.4rem;}
    .nav-link{display:flex;align-items:center;gap:0.8rem;padding:0.7rem 1.2rem;color:rgba(255,255,255,0.75);text-decoration:none;font-size:0.88rem;font-weight:700;transition:background 0.2s,color 0.2s;}
    .nav-link:hover,.nav-link.active{background:rgba(255,255,255,0.1);color:var(--blanco);}
    .nav-link.active{border-left:3px solid var(--dorado);}
    .nav-icon{font-size:1.1rem;width:20px;text-align:center;}
    .sidebar-footer{padding:1rem 1.2rem;border-top:1px solid rgba(255,255,255,0.1);}
    .sidebar-user{color:rgba(255,255,255,0.7);font-size:0.82rem;margin-bottom:0.6rem;}
    .sidebar-user strong{color:var(--blanco);display:block;}
    .btn-logout{display:block;text-align:center;background:rgba(255,255,255,0.1);color:rgba(255,255,255,0.8);text-decoration:none;padding:0.5rem;border-radius:5px;font-size:0.82rem;font-weight:700;transition:background 0.2s;}
    .btn-logout:hover{background:rgba(255,0,0,0.2);color:var(--blanco);}
    .main{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;}
    .topbar{background:var(--blanco);padding:1rem 2rem;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 8px rgba(0,0,0,0.06);}
    .topbar h1{font-family:'Cinzel',serif;font-size:1.1rem;color:var(--azul);}
    .content{padding:2rem;}
    .card{background:var(--blanco);border-radius:8px;padding:1.5rem;box-shadow:0 2px 12px rgba(13,42,78,0.07);margin-bottom:1.5rem;}
    .card h2{font-family:'Cinzel',serif;font-size:1rem;color:var(--azul);margin-bottom:1.2rem;}
    .form-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1rem;}
    .form-group label{display:block;font-size:0.82rem;font-weight:700;color:var(--azul);margin-bottom:0.35rem;}
    .form-group input,.form-group select{width:100%;padding:0.6rem 0.8rem;border:1.5px solid #d0d8e8;border-radius:5px;font-size:0.88rem;font-family:'Lato',sans-serif;outline:none;transition:border-color 0.2s;}
    .form-group input:focus,.form-group select:focus{border-color:var(--azul-c);}
    .btn{padding:0.55rem 1.3rem;border-radius:5px;font-size:0.88rem;font-weight:700;cursor:pointer;border:none;font-family:'Lato',sans-serif;transition:background 0.2s;text-decoration:none;display:inline-block;}
    .btn-primary{background:var(--azul);color:var(--blanco);}
    .btn-primary:hover{background:var(--azul-c);}
    .btn-danger{background:#e74c3c;color:var(--blanco);}
    .btn-danger:hover{background:#c0392b;}
    .btn-warning{background:var(--dorado);color:var(--azul);}
    .btn-warning:hover{background:#b8960e;}
    .btn-sm{padding:0.28rem 0.7rem;font-size:0.78rem;}
    .search-bar{display:flex;gap:0.8rem;margin-bottom:1.2rem;}
    .search-bar input{flex:1;padding:0.6rem 0.9rem;border:1.5px solid #d0d8e8;border-radius:5px;font-size:0.88rem;font-family:'Lato',sans-serif;outline:none;}
    .search-bar input:focus{border-color:var(--azul-c);}
    table{width:100%;border-collapse:collapse;font-size:0.87rem;}
    thead th{background:var(--gris);color:var(--azul);font-weight:700;padding:0.6rem 0.9rem;text-align:left;font-size:0.78rem;letter-spacing:0.05em;}
    tbody td{padding:0.6rem 0.9rem;border-bottom:1px solid #f0f3f8;color:#444;}
    tbody tr:hover td{background:#f8fafd;}
    .badge{display:inline-block;padding:0.18rem 0.55rem;border-radius:20px;font-size:0.7rem;font-weight:700;text-transform:uppercase;}
    .badge-miembro{background:#e8f4fd;color:#1a4a82;}
    .badge-asistente{background:#fef9e7;color:#c9a84c;}
    .alert{padding:0.7rem 1rem;border-radius:6px;font-size:0.85rem;margin-bottom:1rem;}
    .alert-ok{background:#eafaf1;border:1px solid #a9dfbf;color:#1e8449;}
    .alert-err{background:#fff0f0;border:1px solid #f5c6cb;color:#c0392b;}
    .pagination{display:flex;gap:0.4rem;margin-top:1rem;flex-wrap:wrap;}
    .pagination a,.pagination span{padding:0.35rem 0.75rem;border-radius:4px;text-decoration:none;font-size:0.82rem;font-weight:700;}
    .pagination a{background:var(--gris);color:var(--azul);}
    .pagination a:hover{background:var(--azul-c);color:var(--blanco);}
    .pagination span{background:var(--azul);color:var(--blanco);}
  </style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">
    <img src="../logo_iglesia.png" alt="Emanuel"/>
    <span>SAGD Panel</span>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section">Principal</div>
    <a href="dashboard.php" class="nav-link"><span class="nav-icon">🏠</span> Dashboard</a>
    <div class="nav-section">Gestión</div>
    <a href="miembros.php" class="nav-link active"><span class="nav-icon">👥</span> Miembros</a>
    <a href="anuncios.php" class="nav-link"><span class="nav-icon">📢</span> Anuncios</a>
    <a href="cronograma.php" class="nav-link"><span class="nav-icon">📅</span> Cronograma</a>
    <a href="actividades.php" class="nav-link"><span class="nav-icon">⭐</span> Actividades</a>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-user"><strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong><?= $_SESSION['usuario_rol'] ?></div>
    <a href="../logout.php" class="btn-logout">Cerrar sesión</a>
  </div>
</aside>

<div class="main">
  <div class="topbar">
    <h1>👥 Gestión de Miembros</h1>
    <a href="miembros.php" class="btn btn-primary btn-sm">+ Nuevo miembro</a>
  </div>
  <div class="content">

    <?php if ($msg): ?><div class="alert alert-ok"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-err"><?= htmlspecialchars($err) ?></div><?php endif; ?>

    <!-- FORMULARIO -->
    <div class="card">
      <h2><?= $editando ? '✏️ Editar miembro' : '➕ Registrar nuevo miembro' ?></h2>
      <form method="POST" action="miembros.php">
        <?php if ($editando): ?><input type="hidden" name="id" value="<?= $editando['id'] ?>"/><?php endif; ?>
        <div class="form-row">
          <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" value="<?= htmlspecialchars($editando['nombre'] ?? '') ?>" required/></div>
          <div class="form-group"><label>Apellido *</label><input type="text" name="apellido" value="<?= htmlspecialchars($editando['apellido'] ?? '') ?>" required/></div>
          <div class="form-group"><label>Tipo doc.</label>
            <select name="tipo_doc">
              <?php foreach(['CC','TI','CE','PP'] as $t): ?>
              <option value="<?=$t?>" <?= ($editando['tipo_doc']??'CC')===$t?'selected':'' ?>><?=$t?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Documento *</label><input type="text" name="documento" value="<?= htmlspecialchars($editando['documento'] ?? '') ?>" required/></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Teléfono</label><input type="text" name="telefono" value="<?= htmlspecialchars($editando['telefono'] ?? '') ?>"/></div>
          <div class="form-group"><label>Correo</label><input type="email" name="correo" value="<?= htmlspecialchars($editando['correo'] ?? '') ?>"/></div>
          <div class="form-group"><label>Dirección</label><input type="text" name="direccion" value="<?= htmlspecialchars($editando['direccion'] ?? '') ?>"/></div>
          <div class="form-group"><label>Tipo</label>
            <select name="tipo">
              <option value="miembro"   <?= ($editando['tipo']??'')==='miembro'?'selected':'' ?>>Miembro</option>
              <option value="asistente" <?= ($editando['tipo']??'asistente')==='asistente'?'selected':'' ?>>Asistente</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Fecha de ingreso</label><input type="date" name="fecha_ingreso" value="<?= htmlspecialchars($editando['fecha_ingreso'] ?? '') ?>"/></div>
        </div>
        <button type="submit" class="btn btn-primary"><?= $editando ? 'Actualizar' : 'Registrar' ?></button>
        <?php if ($editando): ?><a href="miembros.php" class="btn" style="background:#eee;color:#333;margin-left:0.5rem;">Cancelar</a><?php endif; ?>
      </form>
    </div>

    <!-- LISTADO -->
    <div class="card">
      <h2>📋 Listado de miembros (<?= $total ?>)</h2>
      <form method="GET" action="miembros.php" class="search-bar">
        <input type="text" name="q" placeholder="Buscar por nombre, apellido o documento..." value="<?= htmlspecialchars($busqueda) ?>"/>
        <button type="submit" class="btn btn-primary">Buscar</button>
        <?php if ($busqueda): ?><a href="miembros.php" class="btn" style="background:#eee;color:#333;">Limpiar</a><?php endif; ?>
      </form>

      <?php if (empty($miembros)): ?>
        <p style="color:#888;font-size:0.9rem;">No se encontraron resultados.</p>
      <?php else: ?>
      <table>
        <thead><tr><th>#</th><th>Nombre completo</th><th>Documento</th><th>Teléfono</th><th>Tipo</th><th>Fecha ingreso</th><th>Acciones</th></tr></thead>
        <tbody>
          <?php foreach ($miembros as $i => $m): ?>
          <tr>
            <td><?= $pag['offset'] + $i + 1 ?></td>
            <td><?= htmlspecialchars($m['nombre'] . ' ' . $m['apellido']) ?></td>
            <td><?= htmlspecialchars($m['tipo_doc'] . ' ' . $m['documento']) ?></td>
            <td><?= htmlspecialchars($m['telefono'] ?: '—') ?></td>
            <td><span class="badge badge-<?= $m['tipo'] ?>"><?= $m['tipo'] ?></span></td>
            <td><?= $m['fecha_ingreso'] ? date('d/m/Y', strtotime($m['fecha_ingreso'])) : '—' ?></td>
            <td style="white-space:nowrap;">
              <a href="miembros.php?editar=<?= $m['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
              <a href="miembros.php?eliminar=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este miembro?')">Eliminar</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- PAGINACIÓN -->
      <?php if ($pag['total_paginas'] > 1): ?>
      <div class="pagination">
        <?php for ($p = 1; $p <= $pag['total_paginas']; $p++): ?>
          <?php if ($p === $pagina): ?>
            <span><?= $p ?></span>
          <?php else: ?>
            <a href="miembros.php?p=<?= $p ?>&q=<?= urlencode($busqueda) ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

</body>
</html>
