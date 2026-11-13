<?php require __DIR__ . '/config.php'; $title = 'Home';
$s = $pdo->query("SELECT COALESCE(SUM(weight_kg),0) kg, COUNT(DISTINCT user_id) hh, COALESCE(ROUND(AVG(properly_segregated)*100),0) acc FROM segregation_logs")->fetch();
$ann = $pdo->query("SELECT * FROM announcements ORDER BY id DESC LIMIT 3")->fetchAll();
include __DIR__ . '/includes/header.php'; ?>
<section class="hero">
  <h1>Segregate. Track. Collect. Together.</h1>
  <p>A community platform where residents log their waste segregation, collectors follow coordinated routes, and the barangay monitors results in real time.</p>
  <?php if (user()): ?><a class="btn light" href="<?= BASE_URL . '/' . home_for(user()['role']) ?>">Go to my dashboard</a>
  <?php else: ?><a class="btn light" href="<?= BASE_URL ?>/register.php">Join as Resident</a> <a class="btn gray" href="<?= BASE_URL ?>/login.php">Login</a><?php endif; ?>
</section>
<div class="grid">
  <div class="card stat"><b><?= number_format($s['kg'], 1) ?> kg</b><span>Waste logged by the community</span></div>
  <div class="card stat"><b><?= (int)$s['hh'] ?></b><span>Active households</span></div>
  <div class="card stat"><b><?= (int)$s['acc'] ?>%</b><span>Properly segregated</span></div>
</div>
<h2>How it works</h2>
<div class="steps grid two">
  <div class="card"><b>Residents log</b><br><span class="mut">Record daily waste by type and weight, and earn eco-points for proper segregation.</span></div>
  <div class="card"><b>Admin coordinates</b><br><span class="mut">Barangay staff set zones, collection days, routes, stops, and assign collectors.</span></div>
  <div class="card"><b>Collectors serve routes</b><br><span class="mut">Collectors open today's route and mark each stop as collected or skipped.</span></div>
  <div class="card"><b>Everyone sees results</b><br><span class="mut">Reports, missed pickups, and compliance per zone are tracked in one place.</span></div>
</div>
<h2>Announcements</h2>
<?php foreach ($ann as $a): ?><div class="card"><b><?= e($a['title']) ?></b> <span class="mut">&middot; <?= date('M j, Y', strtotime($a['created_at'])) ?></span><p><?= nl2br(e($a['body'])) ?></p></div><?php endforeach; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
