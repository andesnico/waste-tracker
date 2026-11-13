<?php require __DIR__ . '/../config.php'; require_role('collector'); $title = 'My Routes';
$u = user(); $today = date('Y-m-d'); $day = date('l');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = (int)($_POST['stop_id'] ?? 0); $stt = $_POST['status'] ?? '';
    $chk = $pdo->prepare('SELECT s.id FROM route_stops s JOIN routes r ON r.id=s.route_id WHERE s.id=? AND r.collector_id=?');
    $chk->execute([$sid, $u['id']]);
    if ($chk->fetch() && in_array($stt, ['collected','skipped'])) {
        $pdo->prepare('INSERT INTO collection_logs(stop_id,collector_id,log_date,status,note) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status),note=VALUES(note),collector_id=VALUES(collector_id)')
            ->execute([$sid, $u['id'], $today, $stt, trim($_POST['note'] ?? '')]);
        flash('Stop marked as ' . $stt . '.');
    } else flash('Invalid stop.', 'err');
    redirect('collector/routes.php');
}
$rt = $pdo->prepare('SELECT r.*, z.name zone FROM routes r JOIN zones z ON z.id=r.zone_id WHERE r.collector_id=? ORDER BY (r.collection_day=?) DESC, r.id');
$rt->execute([$u['id'], $day]); $routes = $rt->fetchAll();
include __DIR__ . '/../includes/header.php'; ?>
<h1>My assigned routes</h1><p class="mut">Today is <?= $day ?>, <?= date('M j, Y') ?></p>
<?php foreach ($routes as $r):
    $sp = $pdo->prepare('SELECT s.*, l.status, l.note FROM route_stops s LEFT JOIN collection_logs l ON l.stop_id=s.id AND l.log_date=? WHERE s.route_id=? ORDER BY s.stop_order');
    $sp->execute([$today, $r['id']]); $stops = $sp->fetchAll();
    $done = count(array_filter($stops, fn($x) => $x['status'])); $isToday = $r['collection_day'] === $day; ?>
<div class="card"><h2><?= e($r['name']) ?> <?= badge($r['waste_type']) ?> <?= $isToday ? '<span class="badge collected">Today</span>' : '' ?></h2>
  <p class="mut"><?= e($r['zone']) ?> &middot; <?= $r['collection_day'] ?> at <?= date('g:i A', strtotime($r['start_time'])) ?></p>
  <?php if ($isToday): ?><div class="bar"><i style="width:<?= $stops ? round($done / count($stops) * 100) : 0 ?>%"></i></div><p class="mut"><?= $done ?> of <?= count($stops) ?> stops done</p><?php endif; ?>
  <div class="tbl"><table><thead><tr><th>#</th><th>Stop</th><th>Status</th><?php if ($isToday): ?><th>Action</th><?php endif; ?></tr></thead><tbody>
  <?php foreach ($stops as $s): ?><tr><td><?= $s['stop_order'] ?></td><td><?= e($s['location_name']) ?></td><td><?= $isToday ? ($s['status'] ? badge($s['status']) : badge('pending')) : '<span class="mut">-</span>' ?></td>
    <?php if ($isToday): ?><td><form method="post" class="row"><?= csrf() ?><input type="hidden" name="stop_id" value="<?= $s['id'] ?>">
      <input name="note" placeholder="Note (optional)" value="<?= e($s['note'] ?? '') ?>">
      <button class="btn sm" name="status" value="collected">Collected</button><button class="btn sm gray" name="status" value="skipped">Skip</button></form></td><?php endif; ?></tr>
  <?php endforeach; ?></tbody></table></div></div>
<?php endforeach; if (!$routes) echo '<div class="card mut">No routes assigned yet. Ask the admin to assign you.</div>'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
