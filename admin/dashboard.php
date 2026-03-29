<?php
require_once __DIR__ . '/../includes/funciones.php';
requerirAuth();

$db = getDB();

// Totales para las tarjetas
$totalMiembros   = $db->query("SELECT COUNT(*) FROM miembros WHERE activo=1")->fetchColumn();
$totalAsistentes = $db->query("SELECT COUNT(*) FROM miembros WHERE activo=1 AND tipo='asistente'")->fetchColumn();
$totalAnuncios   = $db->query("SELECT COUNT(*) FROM anuncios WHERE activo=1")->fetchColumn();
$totalActiv      = $db->query("SELECT COUNT(*) FROM actividades WHERE activo=1")->fetchColumn();

// Últimos miembros
$ultimosMiembros = $db->query("SELECT nombre, apellido, tipo, creado_en FROM miembros ORDER BY creado_en DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard – SAGD</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --azul:#0d2a4e; --azul-m:#1a4a82; --azul-c:#2e7bc4;
      --dorado:#c9a84c; --blanco:#fff; --gris:#f4f7fb;
      --sidebar-w:240px;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Lato',sans-serif; background:var(--gris); display:flex; min-height:100vh; }

    /* ── SIDEBAR ── */
    .sidebar {
      width:var(--sidebar-w); background:var(--azul);
      display:flex; flex-direction:column; min-height:100vh;
      position:fixed; top:0; left:0; z-index:50;
    }
    .sidebar-logo {
      padding:1.4rem 1.2rem; border-bottom:1px solid rgba(255,255,255,0.1);
      text-align:center;
    }
    .sidebar-logo img  { height:44px; object-fit:contain; }
    .sidebar-logo span { display:block; color:var(--dorado); font-family:'Cinzel',serif; font-size:0.78rem; margin-top:0.4rem; letter-spacing:0.08em; }
    nav.sidebar-nav { flex:1; padding:1rem 0; }
    .nav-section { font-size:0.65rem; letter-spacing:0.15em; text-transform:uppercase; color:rgba(255,255,255,0.35); padding:1rem 1.2rem 0.4rem; }
    .nav-link {
      display:flex; align-items:center; gap:0.8rem;
      padding:0.7rem 1.2rem; color:rgba(255,255,255,0.75);
      text-decoration:none; font-size:0.88rem; font-weight:700;
      transition:background 0.2s, color 0.2s;
    }
    .nav-link:hover, .nav-link.active { background:rgba(255,255,255,0.1); color:var(--blanco); }
    .nav-link.active { border-left:3px solid var(--dorado); }
    .nav-icon { font-size:1.1rem; width:20px; text-align:center; }
    .sidebar-footer { padding:1rem 1.2rem; border-top:1px solid rgba(255,255,255,0.1); }
    .sidebar-user { color:rgba(255,255,255,0.7); font-size:0.82rem; margin-bottom:0.6rem; }
    .sidebar-user strong { color:var(--blanco); display:block; }
    .btn-logout {
      display:block; text-align:center; background:rgba(255,255,255,0.1);
      color:rgba(255,255,255,0.8); text-decoration:none; padding:0.5rem;
      border-radius:5px; font-size:0.82rem; font-weight:700;
      transition:background 0.2s;
    }
    .btn-logout:hover { background:rgba(255,0,0,0.2); color:var(--blanco); }

    /* ── MAIN ── */
    .main { margin-left:var(--sidebar-w); flex:1; display:flex; flex-direction:column; }
    .topbar {
      background:var(--blanco); padding:1rem 2rem;
      display:flex; align-items:center; justify-content:space-between;
      box-shadow:0 2px 8px rgba(0,0,0,0.06);
    }
    .topbar h1 { font-family:'Cinzel',serif; font-size:1.1rem; color:var(--azul); }
    .topbar-meta { font-size:0.82rem; color:#888; }

    .content { padding:2rem; }

    /* ── STATS ── */
    .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1.2rem; margin-bottom:2rem; }
    .stat-card {
      background:var(--blanco); border-radius:8px; padding:1.4rem 1.6rem;
      box-shadow:0 2px 12px rgba(13,42,78,0.07);
      border-left:4px solid var(--azul-c);
      display:flex; align-items:center; gap:1rem;
    }
    .stat-card.dorado { border-left-color:var(--dorado); }
    .stat-card.verde  { border-left-color:#27ae60; }
    .stat-card.rojo   { border-left-color:#e74c3c; }
    .stat-icon { font-size:2rem; }
    .stat-info span { font-size:0.78rem; color:#888; text-transform:uppercase; letter-spacing:0.08em; display:block; }
    .stat-info strong { font-size:1.8rem; color:var(--azul); font-weight:700; }

    /* ── TABLE ── */
    .section-card {
      background:var(--blanco); border-radius:8px; padding:1.5rem;
      box-shadow:0 2px 12px rgba(13,42,78,0.07); margin-bottom:1.5rem;
    }
    .section-card h2 { font-family:'Cinzel',serif; font-size:1rem; color:var(--azul); margin-bottom:1.2rem; display:flex; align-items:center; gap:0.5rem; }
    table { width:100%; border-collapse:collapse; font-size:0.88rem; }
    thead th { background:var(--gris); color:var(--azul); font-weight:700; padding:0.6rem 0.9rem; text-align:left; font-size:0.8rem; letter-spacing:0.05em; }
    tbody td { padding:0.65rem 0.9rem; border-bottom:1px solid #f0f3f8; color:#444; }
    tbody tr:last-child td { border-bottom:none; }
    tbody tr:hover td { background:#f8fafd; }
    .badge {
      display:inline-block; padding:0.2rem 0.6rem; border-radius:20px;
      font-size:0.72rem; font-weight:700; text-transform:uppercase;
    }
    .badge-miembro   { background:#e8f4fd; color:#1a4a82; }
    .badge-asistente { background:#fef9e7; color:#c9a84c; }
    .btn-sm {
      padding:0.3rem 0.8rem; border-radius:4px; font-size:0.78rem;
      font-weight:700; text-decoration:none; cursor:pointer; border:none;
      font-family:'Lato',sans-serif;
    }
    .btn-primary { background:var(--azul); color:var(--blanco); }
    .btn-primary:hover { background:var(--azul-c); }
  </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <img src="../logo_iglesia.png" alt="Emanuel"/>
    <span>SAGD Panel</span>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section">Principal</div>
    <a href="dashboard.php" class="nav-link active"><span class="nav-icon">🏠</span> Dashboard</a>

    <div class="nav-section">Gestión</div>
    <a href="miembros.php" class="nav-link"><span class="nav-icon">👥</span> Miembros</a>
    <a href="anuncios.php" class="nav-link"><span class="nav-icon">📢</span> Anuncios</a>
    <a href="cronograma.php" class="nav-link"><span class="nav-icon">📅</span> Cronograma</a>
    <a href="actividades.php" class="nav-link"><span class="nav-icon">⭐</span> Actividades</a>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong>
      <?= htmlspecialchars($_SESSION['usuario_rol']) ?>
    </div>
    <a href="../logout.php" class="btn-logout">Cerrar sesión</a>
  </div>
</aside>

<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <h1>Dashboard</h1>
    <span class="topbar-meta"><?= date('l, d \d\e F \d\e Y') ?></span>
  </div>

  <div class="content">
    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-info"><span>Total miembros</span><strong><?= $totalMiembros ?></strong></div>
      </div>
      <div class="stat-card dorado">
        <div class="stat-icon">🙋</div>
        <div class="stat-info"><span>Asistentes</span><strong><?= $totalAsistentes ?></strong></div>
      </div>
      <div class="stat-card verde">
        <div class="stat-icon">📢</div>
        <div class="stat-info"><span>Anuncios activos</span><strong><?= $totalAnuncios ?></strong></div>
      </div>
      <div class="stat-card rojo">
        <div class="stat-icon">⭐</div>
        <div class="stat-info"><span>Actividades</span><strong><?= $totalActiv ?></strong></div>
      </div>
    </div>

    <!-- ÚLTIMOS REGISTROS -->
    <div class="section-card">
      <h2>👥 Últimos registros</h2>
      <?php if (empty($ultimosMiembros)): ?>
        <p style="color:#888;font-size:0.9rem;">No hay miembros registrados aún.</p>
      <?php else: ?>
      <table>
        <thead>
          <tr><th>Nombre</th><th>Tipo</th><th>Fecha registro</th></tr>
        </thead>
        <tbody>
          <?php foreach ($ultimosMiembros as $m): ?>
          <tr>
            <td><?= htmlspecialchars($m['nombre'] . ' ' . $m['apellido']) ?></td>
            <td><span class="badge badge-<?= $m['tipo'] ?>"><?= $m['tipo'] ?></span></td>
            <td><?= date('d/m/Y', strtotime($m['creado_en'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
      <div style="margin-top:1rem;">
        <a href="miembros.php" class="btn-sm btn-primary">Ver todos los miembros</a>
      </div>
    </div>
  </div>
</div>

</body>
</html>
