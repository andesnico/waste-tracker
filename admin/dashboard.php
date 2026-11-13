<?php require __DIR__ . '/../config.php'; require_role('admin'); $title = 'Admin Overview';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'add' && trim($_POST['title'] ?? '') !== '') {
        $pdo->prepare('INSERT INTO announcements(title,body) VALUES(?,?)')->execute([trim($_POST['title']), trim($_POST['body'] ?? '')]); flash('Announcement posted.');
    } elseif (($_POST['action'] ?? '') === 'del') {
        $pdo->prepare('DELETE FROM announcements WHERE id=?')->execute([(int)$_POST['id']]); flash('Announcement removed.');
    }
    redirect('admin/dashboard.php');
}
$k = $pdo->query("SELECT (SELECT COUNT(*) FROM users WHERE role='resident') res, (SELECT COUNT(*) FROM reports WHERE status<>'resolved') open_r,
  (SELECT COALESCE(SUM(weight_kg),0) FROM segregation_logs WHERE log_date>=CURDATE()-INTERVAL 30 DAY) kg,
  (SELECT COALESCE(ROUND(AVG(properly_segregated)*100),0) FROM segregation_logs WHERE log_date>=CURDATE()-INTERVAL 30 DAY) acc")->fetch();
$bt = $pdo->query("SELECT waste_type, SUM(weight_kg) kg FROM segregation_logs WHERE log_date>=CURDATE()-INTERVAL 30 DAY GROUP BY waste_type")->fetchAll(PDO::FETCH_KEY_PAIR);
$max = max(1, ...array_values($bt ?: [1]));
$zc = $pdo->query("SELECT z.name, COALESCE(SUM(l.weight_kg),0) kg, COALESCE(ROUND(AVG(l.properly_segregated)*100),0) acc, COUNT(DISTINCT u.id) hh
  FROM zones z LEFT JOIN users u ON u.zone_id=z.id AND u.role='resident' LEFT JOIN segregation_logs l ON l.user_id=u.id GROUP BY z.id ORDER BY acc DESC")->fetchAll();
$cl = $pdo->query("SELECT COUNT(*) total, SUM(status='collected') ok FROM collection_logs WHERE log_date=CURDATE()")->fetch();
$ann = $pdo->query('SELECT * FROM announcements ORDER BY id DESC')->fetchAll();
include __DIR__ . '/../includes/header.php'; ?>
<h1>Barangay overview</h1>
<div class="grid">
  <div class="card stat"><b><?= $k['res'] ?></b><span>Registered residents</span></div>
  <div class="card stat"><b><?= number_format($k['kg'], 1) ?> kg</b><span>Logged (last 30 days)</span></div>
  <div class="card stat"><b><?= $k['acc'] ?>%</b><span>Segregation accuracy</span></div>
  <div class="card stat"><b><?= $k['open_r'] ?></b><span><a href="reports.php">Open reports</a></span></div>
  <div class="card stat"><b><?= (int)$cl['ok'] ?>/<?= (int)$cl['total'] ?></b><span>Stops collected today</span></div>
</div>
<div class="grid two">
  <div class="card"><h2>Waste by type (30 days)</h2>
  <?php foreach (TYPES as $t): $v = $bt[$t] ?? 0; ?><div class="barrow"><span><?= badge($t) ?></span><div class="bar"><i style="width:<?= round($v / $max * 100) ?>%"></i></div><b><?= number_format($v, 1) ?> kg</b></div><?php endforeach; ?></div>
  <div class="card"><h2>Zone compliance</h2><div class="tbl"><table><thead><tr><th>Zone</th><th>Households</th><th>kg</th><th>Accuracy</th></tr></thead><tbody>
  <?php foreach ($zc as $z): ?><tr><td><?= e($z['name']) ?></td><td><?= $z['hh'] ?></td><td><?= number_format($z['kg'], 1) ?></td><td><?= $z['acc'] ?>%</td></tr><?php endforeach; ?></tbody></table></div></div>
</div>
<div class="card"><h2>Announcements</h2>
  <form method="post" class="row"><?= csrf() ?><input type="hidden" name="action" value="add"><div><label>Title</label><input name="title" required></div><div style="flex:2"><label>Message</label><input name="body"></div><button class="btn">Post</button></form>
  <?php foreach ($ann as $a): ?><form method="post" style="margin-top:.7rem"><?= csrf() ?><input type="hidden" name="action" value="del"><input type="hidden" name="id" value="<?= $a['id'] ?>">
    <b><?= e($a['title']) ?></b> <span class="mut"><?= e($a['body']) ?></span> <button class="btn sm red" data-confirm="Delete this announcement?">Delete</button></form><?php endforeach; ?></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
