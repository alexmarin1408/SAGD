<?php
require_once __DIR__ . '/../includes/funciones.php';
requerirAuth();

$db  = getDB();
$msg = '';
$err = '';

// ── ELIMINAR ────────────────────────────────────────────────
if (isset($_GET['eliminar'])) {
    $db->prepare("UPDATE cronograma SET activo=0 WHERE id=?")->execute([(int)$_GET['eliminar']]);
    $msg = 'Actividad eliminada correctamente.';
}

// ── GUARDAR ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = (int)($_POST['id'] ?? 0);
    $dia        = limpiar($_POST['dia_semana']  ?? '');
    $actividad  = limpiar($_POST['actividad']   ?? '');
    $hora_ini   = limpiar($_POST['hora_inicio'] ?? '');
    $hora_fin   = limpiar($_POST['hora_fin']    ?? '');
    $lugar      = limpiar($_POST['lugar']       ?? '');

    if (!$dia || !$actividad || !$hora_ini) {
        $err = 'Día, actividad y hora de inicio son obligatorios.';
    } else {
        if ($id > 0) {
            $db->prepare("UPDATE cronograma SET dia_semana=?,actividad=?,hora_inicio=?,hora_fin=?,lugar=? WHERE id=?")
               ->execute([$dia,$actividad,$hora_ini,$hora_fin?:null,$lugar,$id]);
            $msg = 'Actividad actualizada correctamente.';
        } else {
            $db->prepare("INSERT INTO cronograma (dia_semana,actividad,hora_inicio,hora_fin,lugar) VALUES (?,?,?,?,?)")
               ->execute([$dia,$actividad,$hora_ini,$hora_fin?:null,$lugar]);
            $msg = 'Actividad registrada correctamente.';
        }
    }
}

// ── LISTAR ───────────────────────────────────────────────────
$orden = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
$lista = $db->query("SELECT * FROM cronograma WHERE activo=1 ORDER BY FIELD(dia_semana,'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo')")->fetchAll();

$editando = null;
if (isset($_GET['editar'])) {
    $s = $db->prepare("SELECT * FROM cronograma WHERE id=? AND activo=1");
    $s->execute([(int)$_GET['editar']]);
    $editando = $s->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Cronograma – SAGD</title>
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
    .nav-link{display:flex;align-items:center;gap:0.8rem;padding:0.7rem 1.2rem;color:rgba(255,255,255,0.75);text-decoration:none;font-size:0.88rem;font-weight:700;transition:background 0.2s;}
    .nav-link:hover,.nav-link.active{background:rgba(255,255,255,0.1);color:var(--blanco);}
    .nav-link.active{border-left:3px solid var(--dorado);}
    .nav-icon{font-size:1.1rem;width:20px;text-align:center;}
    .sidebar-footer{padding:1rem 1.2rem;border-top:1px solid rgba(255,255,255,0.1);}
    .sidebar-user{color:rgba(255,255,255,0.7);font-size:0.82rem;margin-bottom:0.6rem;}
    .sidebar-user strong{color:var(--blanco);display:block;}
    .btn-logout{display:block;text-align:center;background:rgba(255,255,255,0.1);color:rgba(255,255,255,0.8);text-decoration:none;padding:0.5rem;border-radius:5px;font-size:0.82rem;font-weight:700;}
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
    .btn-warning{background:var(--dorado);color:var(--azul);}
    .btn-sm{padding:0.28rem 0.7rem;font-size:0.78rem;}
    table{width:100%;border-collapse:collapse;font-size:0.87rem;}
    thead th{background:var(--gris);color:var(--azul);font-weight:700;padding:0.6rem 0.9rem;text-align:left;font-size:0.78rem;letter-spacing:0.05em;}
    tbody td{padding:0.6rem 0.9rem;border-bottom:1px solid #f0f3f8;color:#444;}
    tbody tr:hover td{background:#f8fafd;}
    .alert{padding:0.7rem 1rem;border-radius:6px;font-size:0.85rem;margin-bottom:1rem;}
    .alert-ok{background:#eafaf1;border:1px solid #a9dfbf;color:#1e8449;}
    .alert-err{background:#fff0f0;border:1px solid #f5c6cb;color:#c0392b;}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo"><img src="../logo_iglesia.png" alt="Emanuel"/><span>SAGD Panel</span></div>
  <nav class="sidebar-nav">
    <div class="nav-section">Principal</div>
    <a href="dashboard.php" class="nav-link"><span class="nav-icon">🏠</span> Dashboard</a>
    <div class="nav-section">Gestión</div>
    <a href="miembros.php"    class="nav-link"><span class="nav-icon">👥</span> Miembros</a>
    <a href="anuncios.php"    class="nav-link"><span class="nav-icon">📢</span> Anuncios</a>
    <a href="cronograma.php"  class="nav-link active"><span class="nav-icon">📅</span> Cronograma</a>
    <a href="actividades.php" class="nav-link"><span class="nav-icon">⭐</span> Actividades</a>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-user"><strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong><?= $_SESSION['usuario_rol'] ?></div>
    <a href="../logout.php" class="btn-logout">Cerrar sesión</a>
  </div>
</aside>

<div class="main">
  <div class="topbar"><h1>📅 Cronograma Semanal</h1></div>
  <div class="content">

    <?php if ($msg): ?><div class="alert alert-ok"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-err"><?= htmlspecialchars($err) ?></div><?php endif; ?>

    <div class="card">
      <h2><?= $editando ? '✏️ Editar actividad' : '➕ Nueva actividad' ?></h2>
      <form method="POST" action="cronograma.php">
        <?php if ($editando): ?><input type="hidden" name="id" value="<?= $editando['id'] ?>"/><?php endif; ?>
        <div class="form-row">
          <div class="form-group">
            <label>Día de la semana *</label>
            <select name="dia_semana" required>
              <?php foreach(['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'] as $d): ?>
              <option value="<?=$d?>" <?= ($editando['dia_semana']??'')===$d?'selected':'' ?>><?=$d?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Actividad *</label><input type="text" name="actividad" value="<?= htmlspecialchars($editando['actividad'] ?? '') ?>" required/></div>
          <div class="form-group"><label>Hora inicio *</label><input type="time" name="hora_inicio" value="<?= htmlspecialchars($editando['hora_inicio'] ?? '') ?>" required/></div>
          <div class="form-group"><label>Hora fin</label><input type="time" name="hora_fin" value="<?= htmlspecialchars($editando['hora_fin'] ?? '') ?>"/></div>
          <div class="form-group"><label>Lugar</label><input type="text" name="lugar" value="<?= htmlspecialchars($editando['lugar'] ?? '') ?>"/></div>
        </div>
        <button type="submit" class="btn btn-primary"><?= $editando ? 'Actualizar' : 'Agregar' ?></button>
        <?php if ($editando): ?><a href="cronograma.php" class="btn" style="background:#eee;color:#333;margin-left:0.5rem;">Cancelar</a><?php endif; ?>
      </form>
    </div>

    <div class="card">
      <h2>📋 Actividades del cronograma</h2>
      <?php if (empty($lista)): ?>
        <p style="color:#888;font-size:0.9rem;">No hay actividades registradas.</p>
      <?php else: ?>
      <table>
        <thead><tr><th>Día</th><th>Actividad</th><th>Hora inicio</th><th>Hora fin</th><th>Lugar</th><th>Acciones</th></tr></thead>
        <tbody>
          <?php foreach ($lista as $row): ?>
          <tr>
            <td><strong><?= htmlspecialchars($row['dia_semana']) ?></strong></td>
            <td><?= htmlspecialchars($row['actividad']) ?></td>
            <td><?= date('h:i A', strtotime($row['hora_inicio'])) ?></td>
            <td><?= $row['hora_fin'] ? date('h:i A', strtotime($row['hora_fin'])) : '—' ?></td>
            <td><?= htmlspecialchars($row['lugar'] ?: '—') ?></td>
            <td style="white-space:nowrap;">
              <a href="cronograma.php?editar=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
              <a href="cronograma.php?eliminar=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta actividad?')">Eliminar</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

  </div>
</div>
</body>
</html>
