<?php
$u = user(); $f = flash(); $cur = basename($_SERVER['PHP_SELF']);
$nav = [];
if ($u) {
    if ($u['role'] === 'resident') $nav = [['resident/dashboard.php','Dashboard'],['resident/log.php','Log Waste'],['resident/report.php','Reports']];
    if ($u['role'] === 'collector') $nav = [['collector/routes.php','My Routes']];
    if ($u['role'] === 'admin') $nav = [['admin/dashboard.php','Overview'],['admin/routes.php','Zones & Routes'],['admin/reports.php','Reports'],['admin/users.php','Users']];
}
?><!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title ?? 'EcoRoute') ?> | EcoRoute</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<script src="<?= BASE_URL ?>/assets/js/app.js" defer></script></head><body>
<header class="nav">
  <a class="brand" href="<?= BASE_URL ?>/index.php">♻ EcoRoute</a>
  <button class="menu-btn" aria-label="Menu">☰</button>
  <nav class="nav-links">
    <?php foreach ($nav as $n): ?><a class="<?= basename($n[0]) === $cur ? 'on' : '' ?>" href="<?= BASE_URL . '/' . $n[0] ?>"><?= $n[1] ?></a><?php endforeach; ?>
    <?php if ($u): ?><a href="<?= BASE_URL ?>/logout.php">Logout (<?= e(explode(' ', $u['name'])[0]) ?>)</a>
    <?php else: ?><a href="<?= BASE_URL ?>/login.php">Login</a><a href="<?= BASE_URL ?>/register.php">Register</a><?php endif; ?>
  </nav>
</header>
<main class="wrap">
<?php if ($f): ?><div class="flash <?= $f[1] ?>"><?= e($f[0]) ?></div><?php endif; ?>
