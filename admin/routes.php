<?php require __DIR__ . '/../config.php'; require_role('admin'); $title = 'Zones & Routes';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['action'] ?? '';
    if ($a === 'zone' && trim($_POST['name'] ?? '') !== '') {
        $pdo->prepare('INSERT INTO zones(name,description) VALUES(?,?)')->execute([trim($_POST['name']), trim($_POST['description'] ?? '')]); flash('Zone added.');
    } elseif ($a === 'route' && in_array($_POST['day'] ?? '', DAYS) && in_array($_POST['waste_type'] ?? '', TYPES) && trim($_POST['name'] ?? '') !== '') {
        $pdo->prepare('INSERT INTO routes(name,zone_id,collector_id,collection_day,waste_type,start_time) VALUES(?,?,?,?,?,?)')
            ->execute([trim($_POST['name']), (int)$_POST['zone_id'], (int)$_POST['collector_id'] ?: null, $_POST['day'], $_POST['waste_type'], $_POST['start_time'] ?: '07:00']); flash('Route created.');
    } elseif ($a === 'stop' && trim($_POST['location_name'] ?? '') !== '') {
        $rid = (int)$_POST['route_id'];
        $n = $pdo->prepare('SELECT COALESCE(MAX(stop_order),0)+1 FROM route_stops WHERE route_id=?'); $n->execute([$rid]);
        $pdo->prepare('INSERT INTO route_stops(route_id,stop_order,location_name) VALUES(?,?,?)')->execute([$rid, $n->fetchColumn(), trim($_POST['location_name'])]); flash('Stop added.');
    } elseif ($a === 'assign') {
        $pdo->prepare('UPDATE routes SET collector_id=? WHERE id=?')->execute([(int)$_POST['collector_id'] ?: null, (int)$_POST['route_id']]); flash('Collector updated.');
    } elseif ($a === 'del_route') { $pdo->prepare('DELETE FROM routes WHERE id=?')->execute([(int)$_POST['id']]); flash('Route deleted.');
    } elseif ($a === 'del_stop') { $pdo->prepare('DELETE FROM route_stops WHERE id=?')->execute([(int)$_POST['id']]); flash('Stop deleted.'); }
    redirect('admin/routes.php');
}
$zones = $pdo->query('SELECT * FROM zones ORDER BY name')->fetchAll();
$cols = $pdo->query("SELECT id,name FROM users WHERE role='collector' ORDER BY name")->fetchAll();
$routes = $pdo->query("SELECT r.*, z.name zone FROM routes r JOIN zones z ON z.id=r.zone_id ORDER BY z.name, FIELD(r.collection_day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")->fetchAll();
include __DIR__ . '/../includes/header.php'; ?>
<h1>Zones, routes & stops</h1>
<div class="grid two">
<form class="card" method="post"><?= csrf() ?><input type="hidden" name="action" value="zone"><h2>Add zone</h2>
  <label>Name</label><input name="name" required><label>Description</label><input name="description"><button class="btn" style="margin-top:1rem">Add zone</button></form>
<form class="card" method="post"><?= csrf() ?><input type="hidden" name="action" value="route"><h2>Create route</h2>
  <label>Route name</label><input name="name" required>
  <div class="row"><div><label>Zone</label><select name="zone_id"><?php foreach ($zones as $z): ?><option value="<?= $z['id'] ?>"><?= e($z['name']) ?></option><?php endforeach; ?></select></div>
  <div><label>Collector</label><select name="collector_id"><option value="0">Unassigned</option><?php foreach ($cols as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></div></div>
  <div class="row"><div><label>Day</label><select name="day"><?php foreach (DAYS as $d): ?><option><?= $d ?></option><?php endforeach; ?></select></div>
  <div><label>Waste type</label><select name="waste_type"><?php foreach (TYPES as $t): ?><option><?= $t ?></option><?php endforeach; ?></select></div>
  <div><label>Start time</label><input type="time" name="start_time" value="07:00"></div></div>
  <button class="btn" style="margin-top:1rem">Create route</button></form>
</div>
<?php foreach ($routes as $r):
    $sp = $pdo->prepare('SELECT * FROM route_stops WHERE route_id=? ORDER BY stop_order'); $sp->execute([$r['id']]); $stops = $sp->fetchAll(); ?>
<div class="card"><h2><?= e($r['name']) ?> <?= badge($r['waste_type']) ?></h2>
  <p class="mut"><?= e($r['zone']) ?> &middot; <?= $r['collection_day'] ?> <?= date('g:i A', strtotime($r['start_time'])) ?></p>
  <form method="post" class="row"><?= csrf() ?><input type="hidden" name="action" value="assign"><input type="hidden" name="route_id" value="<?= $r['id'] ?>">
    <select name="collector_id"><option value="0">Unassigned</option><?php foreach ($cols as $c): ?><option value="<?= $c['id'] ?>" <?= $c['id'] == $r['collector_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
    <button class="btn sm">Assign collector</button></form>
  <div class="tbl"><table><tbody><?php foreach ($stops as $s): ?><tr><td><?= $s['stop_order'] ?></td><td><?= e($s['location_name']) ?></td><td>
    <form method="post"><?= csrf() ?><input type="hidden" name="action" value="del_stop"><input type="hidden" name="id" value="<?= $s['id'] ?>"><button class="btn sm red" data-confirm="Delete this stop?">✕</button></form></td></tr><?php endforeach; ?></tbody></table></div>
  <form method="post" class="row" style="margin-top:.6rem"><?= csrf() ?><input type="hidden" name="action" value="stop"><input type="hidden" name="route_id" value="<?= $r['id'] ?>">
    <input name="location_name" placeholder="New stop location" required><button class="btn sm">Add stop</button></form>
  <form method="post" style="margin-top:.6rem"><?= csrf() ?><input type="hidden" name="action" value="del_route"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn sm red" data-confirm="Delete this route and its stops?">Delete route</button></form>
</div><?php endforeach; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
