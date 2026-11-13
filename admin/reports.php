<?php require __DIR__ . '/../config.php'; require_role('admin'); $title = 'Reports';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['status'] ?? '', ['pending','in_progress','resolved'])) {
    $pdo->prepare('UPDATE reports SET status=?, admin_note=? WHERE id=?')->execute([$_POST['status'], trim($_POST['admin_note'] ?? ''), (int)$_POST['id']]);
    flash('Report updated.'); redirect('admin/reports.php');
}
$rows = $pdo->query("SELECT r.*, u.name, z.name zone FROM reports r JOIN users u ON u.id=r.user_id LEFT JOIN zones z ON z.id=u.zone_id ORDER BY (r.status='resolved'), r.id DESC")->fetchAll();
include __DIR__ . '/../includes/header.php'; ?>
<h1>Community reports</h1>
<div class="card"><input data-filter="#rep" placeholder="Search reports..."></div>
<div class="card tbl"><table id="rep"><thead><tr><th>Resident</th><th>Report</th><th>Status & response</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['name']) ?><br><span class="mut"><?= e($r['zone']) ?><br><?= date('M j', strtotime($r['created_at'])) ?></span></td>
  <td><b><?= e(str_replace('_', ' ', $r['type'])) ?></b> @ <?= e($r['location']) ?><br><span class="mut"><?= e($r['description']) ?></span></td>
  <td><form method="post" class="row"><?= csrf() ?><input type="hidden" name="id" value="<?= $r['id'] ?>">
    <select name="status"><?php foreach (['pending','in_progress','resolved'] as $s): ?><option value="<?= $s ?>" <?= $s === $r['status'] ? 'selected' : '' ?>><?= str_replace('_', ' ', $s) ?></option><?php endforeach; ?></select>
    <input name="admin_note" placeholder="Note to resident" value="<?= e($r['admin_note']) ?>"><button class="btn sm">Save</button></form></td></tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="3" class="mut">No reports.</td></tr>'; ?></tbody></table></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
