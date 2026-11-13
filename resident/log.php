<?php require __DIR__ . '/../config.php'; require_role('resident'); $title = 'Log Waste';
$u = user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $t = $_POST['waste_type'] ?? ''; $w = (float)($_POST['weight_kg'] ?? 0); $ok = ($_POST['seg'] ?? '1') === '1' ? 1 : 0;
    $d = $_POST['log_date'] ?? date('Y-m-d');
    if (!in_array($t, TYPES) || $w <= 0 || $w > 500 || $d > date('Y-m-d')) { flash('Please enter a valid waste type, weight, and date.', 'err'); redirect('resident/log.php'); }
    $pts = $ok ? 10 + (int)round($w) : 2;
    $pdo->prepare('INSERT INTO segregation_logs(user_id,waste_type,weight_kg,properly_segregated,points,notes,log_date) VALUES(?,?,?,?,?,?,?)')
        ->execute([$u['id'], $t, $w, $ok, $pts, trim($_POST['notes'] ?? ''), $d]);
    flash("Logged! You earned $pts eco-points."); redirect('resident/log.php');
}
$h = $pdo->prepare('SELECT * FROM segregation_logs WHERE user_id=? ORDER BY log_date DESC, id DESC LIMIT 20');
$h->execute([$u['id']]); $hist = $h->fetchAll();
include __DIR__ . '/../includes/header.php'; ?>
<h1>Log your waste</h1>
<div class="grid two">
<form class="card" method="post"><?= csrf() ?>
  <label>Waste type</label><select name="waste_type" required>
    <option value="biodegradable">Biodegradable (food, leaves)</option><option value="recyclable">Recyclable (paper, plastic, glass)</option>
    <option value="residual">Residual (non-recyclable)</option><option value="special">Special (batteries, e-waste)</option></select>
  <label>Weight (kg)</label><input type="number" id="weight" name="weight_kg" step="0.1" min="0.1" max="500" required>
  <label>Was it properly segregated?</label><select id="seg" name="seg"><option value="1">Yes, properly separated</option><option value="0">No, mixed</option></select>
  <label>Date</label><input type="date" name="log_date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
  <label>Notes (optional)</label><input name="notes" maxlength="200">
  <p class="mut">You will earn <b id="pts">10</b> eco-points.</p>
  <button class="btn">Save entry</button>
</form>
<div class="card"><h2>Recent entries</h2><div class="tbl"><table><thead><tr><th>Date</th><th>Type</th><th>kg</th><th>Seg.</th><th>Pts</th></tr></thead><tbody>
<?php foreach ($hist as $r): ?><tr><td><?= date('M j', strtotime($r['log_date'])) ?></td><td><?= badge($r['waste_type']) ?></td><td><?= $r['weight_kg'] ?></td><td><?= $r['properly_segregated'] ? '✔' : '✖' ?></td><td><?= $r['points'] ?></td></tr><?php endforeach;
if (!$hist) echo '<tr><td colspan="5" class="mut">No entries yet.</td></tr>'; ?></tbody></table></div></div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
