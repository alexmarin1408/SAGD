<?php
define('BASE_URL', '/SAGD/');
require_once __DIR__ . '/../includes/funciones.php';
requerirAuth();

$db = getDB(); $msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id  = (int)($_POST['id']??0);
    $dia = limpiar($_POST['dia_semana']??'');
    $act = limpiar($_POST['actividad']??'');
    $hi  = limpiar($_POST['hora_inicio']??'') ?: null;
    $hf  = limpiar($_POST['hora_fin']??'') ?: null;
    $lug = limpiar($_POST['lugar']??'');
    if ($dia && $act) {
        if ($id) { $db->prepare("UPDATE cronograma SET dia_semana=?,actividad=?,hora_inicio=?,hora_fin=?,lugar=? WHERE id=?")->execute([$dia,$act,$hi,$hf,$lug,$id]); $msg='Actualizado.'; }
        else      { $db->prepare("INSERT INTO cronograma(dia_semana,actividad,hora_inicio,hora_fin,lugar) VALUES(?,?,?,?,?)")->execute([$dia,$act,$hi,$hf,$lug]); $msg='Creado.'; }
    }
}
if (isset($_GET['eliminar'])) { $db->prepare("UPDATE cronograma SET activo=0 WHERE id=?")->execute([(int)$_GET['eliminar']]); $msg='Eliminado.'; }
$editando = null;
if (isset($_GET['editar'])) { $s=$db->prepare("SELECT * FROM cronograma WHERE id=?"); $s->execute([(int)$_GET['editar']]); $editando=$s->fetch(); }
$lista = $db->query("SELECT * FROM cronograma WHERE activo=1 ORDER BY FIELD(dia_semana,'Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo')")->fetchAll();
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Cronograma – SAGD</title>
<style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:Arial,sans-serif;background:#f5f5f5}header{background:#1F3864;color:white;padding:14px 30px;display:flex;justify-content:space-between;align-items:center}header h1{font-size:18px}header a{color:#BDD7EE;font-size:13px;text-decoration:none}.container{max-width:900px;margin:25px auto;padding:0 20px}.msg{background:#e2efda;color:#375623;padding:10px 16px;border-radius:6px;margin-bottom:16px;font-size:13px}form.card{background:white;border-radius:8px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,.08);margin-bottom:24px}.grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}label{font-size:12px;font-weight:bold;color:#333;display:block;margin-bottom:3px;margin-top:8px}input{width:100%;padding:9px 12px;border:1px solid #ccc;border-radius:5px;font-size:13px}.btn{padding:9px 20px;border:none;border-radius:5px;cursor:pointer;font-size:13px;font-weight:bold;text-decoration:none;display:inline-block}.btn-primary{background:#1F3864;color:white}.btn-danger{background:#c00;color:white;font-size:12px;padding:5px 10px}.btn-edit{background:#2E75B6;color:white;font-size:12px;padding:5px 10px}table{width:100%;border-collapse:collapse;background:white;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08)}th{background:#1F3864;color:white;padding:11px 12px;text-align:left;font-size:13px}td{padding:10px 12px;font-size:13px;border-bottom:1px solid #eee}tr:hover td{background:#f0f7ff}</style>
</head><body>
<header>
    <img src="/SAGD/logo_iglesia.png" alt="Logo" style="height:45px;object-fit:contain;margin-right:12px;vertical-align:middle"><h1>SAGD – Cronograma Semanal</h1><div><a href="dashboard.php">← Dashboard</a> &nbsp;|&nbsp; <a href="../logout.php">Cerrar sesión</a></div></header>
<div class="container">
<?php if($msg):?><div class="msg"><?=htmlspecialchars($msg)?></div><?php endif;?>
<h2 style="color:#1F3864;margin-bottom:14px"><?=$editando?'Editar entrada':'Nueva entrada'?></h2>
<form class="card" method="POST">
<?php if($editando):?><input type="hidden" name="id" value="<?=$editando['id']?>"><?php endif;?>
<div class="grid">
<div><label>Día *</label><input name="dia_semana" value="<?=htmlspecialchars($editando['dia_semana']??'')?>" placeholder="Ej: Domingo" required></div>
<div><label>Actividad *</label><input name="actividad" value="<?=htmlspecialchars($editando['actividad']??'')?>" required></div>
<div><label>Lugar</label><input name="lugar" value="<?=htmlspecialchars($editando['lugar']??'')?>"></div>
<div><label>Hora inicio</label><input type="time" name="hora_inicio" value="<?=htmlspecialchars($editando['hora_inicio']??'')?>"></div>
<div><label>Hora fin</label><input type="time" name="hora_fin" value="<?=htmlspecialchars($editando['hora_fin']??'')?>"></div>
</div>
<div style="margin-top:14px;display:flex;gap:10px">
<button class="btn btn-primary"><?=$editando?'Actualizar':'Crear'?></button>
<?php if($editando):?><a href="cronograma.php" class="btn btn-primary">Cancelar</a><?php endif;?>
</div>
</form>
<table><thead><tr><th>Día</th><th>Actividad</th><th>Horario</th><th>Lugar</th><th>Acciones</th></tr></thead><tbody>
<?php foreach($lista as $c):?>
<tr><td><?=htmlspecialchars($c['dia_semana'])?></td><td><?=htmlspecialchars($c['actividad'])?></td>
<td><?=htmlspecialchars(substr($c['hora_inicio']??'',0,5))?> – <?=htmlspecialchars(substr($c['hora_fin']??'',0,5))?></td>
<td><?=htmlspecialchars($c['lugar']??'')?></td>
<td><a href="?editar=<?=$c['id']?>" class="btn btn-edit">Editar</a> <a href="?eliminar=<?=$c['id']?>" class="btn btn-danger" onclick="return confirm('¿Eliminar?')">Eliminar</a></td>
</tr><?php endforeach;?>
</tbody></table>
</div></body></html>
