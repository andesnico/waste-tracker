<?php require __DIR__ . '/../config.php'; require_role('resident'); $title = 'Dashboard';
$u = user();
$st = $pdo->prepare("SELECT COALESCE(SUM(points),0) pts, COALESCE(SUM(weight_kg),0) kg, COALESCE(ROUND(AVG(properly_segregated)*100),0) acc, COUNT(*) n FROM segregation_logs WHERE user_id=?");
$st->execute([$u['id']]); $s = $st->fetch();
$zn = $pdo->prepare('SELECT name FROM zones WHERE id=?'); $zn->execute([$u['zone_id']]); $zone = $zn->fetchColumn();
$sc = $pdo->prepare("SELECT r.*, c.name collector FROM routes r LEFT JOIN users c ON c.id=r.collector_id WHERE r.zone_id=? ORDER BY FIELD(r.collection_day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')");
$sc->execute([$u['zone_id']]); $sched = $sc->fetchAll();
$lb = $pdo->query("SELECT u.name, SUM(l.points) pts FROM segregation_logs l JOIN users u ON u.id=l.user_id GROUP BY u.id ORDER BY pts DESC LIMIT 5")->fetchAll();
$ann = $pdo->query('SELECT * FROM announcements ORDER BY id DESC LIMIT 2')->fetchAll();
include __DIR__ . '/../includes/header.php'; ?>
<h1>Hello, <?= e(explode(' ', $u['name'])[0]) ?> 👋</h1>
<p class="mut"><?= e($zone) ?></p>
<div class="grid">
  <div class="card stat"><b><?= (int)$s['pts'] ?></b><span>Eco-points</span></div>
  <div class="card stat"><b><?= number_format($s['kg'], 1) ?> kg</b><span>Total logged</span></div>
  <div class="card stat"><b><?= (int)$s['acc'] ?>%</b><span>Segregation accuracy</span></div>
  <div class="card stat"><a class="btn" href="log.php">+ Log waste</a><br><span>Takes 10 seconds</span></div>
</div>
<div class="grid two">
  <div class="card"><h2>Collection schedule for your zone</h2><div class="tbl"><table>
    <thead><tr><th>Day</th><th>Waste</th><th>Time</th><th>Collector</th></tr></thead><tbody>
    <?php foreach ($sched as $r): ?><tr class="<?= $r['collection_day'] === date('l') ? 'today' : '' ?>">
      <td><?= $r['collection_day'] ?></td><td><?= badge($r['waste_type']) ?></td><td><?= date('g:i A', strtotime($r['start_time'])) ?></td><td><?= e($r['collector'] ?? 'Unassigned') ?></td></tr>
    <?php endforeach; if (!$sched) echo '<tr><td colspan="4" class="mut">No routes for your zone yet.</td></tr>'; ?></tbody></table></div>
    <p class="mut">Highlighted row = collection today.</p></div>
  <div class="card"><h2>🏆 Community leaderboard</h2>
    <?php foreach ($lb as $i => $l): ?><div class="barrow" style="grid-template-columns:30px 1fr 70px"><span>#<?= $i + 1 ?></span><span><?= e($l['name']) ?></span><b><?= (int)$l['pts'] ?> pts</b></div><?php endforeach; ?>
    <h2 style="margin-top:1rem">Announcements</h2>
    <?php foreach ($ann as $a): ?><p><b><?= e($a['title']) ?></b><br><span class="mut"><?= e($a['body']) ?></span></p><?php endforeach; ?></div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
