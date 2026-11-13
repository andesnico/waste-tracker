<?php require __DIR__ . '/../config.php'; require_role('resident'); $title = 'Reports';
$u = user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $t = $_POST['type'] ?? ''; $loc = trim($_POST['location'] ?? ''); $d = trim($_POST['description'] ?? '');
    if (!in_array($t, ['missed_collection','illegal_dumping','pickup_request','other']) || $loc === '' || $d === '') { flash('Please fill in all fields.', 'err'); redirect('resident/report.php'); }
    $pdo->prepare('INSERT INTO reports(user_id,type,location,description) VALUES(?,?,?,?)')->execute([$u['id'], $t, $loc, $d]);
    flash('Report submitted. The barangay will review it.'); redirect('resident/report.php');
}
$r = $pdo->prepare('SELECT * FROM reports WHERE user_id=? ORDER BY id DESC'); $r->execute([$u['id']]); $rows = $r->fetchAll();
include __DIR__ . '/../includes/header.php'; ?>
<h1>Reports & pickup requests</h1>
<div class="grid two">
<form class="card" method="post"><?= csrf() ?><h2>New report</h2>
  <label>Type</label><select name="type"><option value="missed_collection">Missed collection</option><option value="illegal_dumping">Illegal dumping</option><option value="pickup_request">Special pickup request</option><option value="other">Other</option></select>
  <label>Location</label><input name="location" required>
  <label>Details</label><textarea name="description" rows="4" required></textarea>
  <button class="btn" style="margin-top:1rem">Submit report</button></form>
<div class="card"><h2>My reports</h2>
<?php foreach ($rows as $x): ?><p><?= badge($x['status']) ?> <b><?= e(str_replace('_', ' ', $x['type'])) ?></b> &middot; <?= e($x['location']) ?><br>
  <span class="mut"><?= e($x['description']) ?></span><?php if ($x['admin_note']): ?><br><i>Barangay: <?= e($x['admin_note']) ?></i><?php endif; ?></p><?php endforeach;
if (!$rows) echo '<p class="mut">No reports yet.</p>'; ?></div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
