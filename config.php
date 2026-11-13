<?php
session_start();
define('BASE_URL', '/waste-tracker');   // change if you rename the folder
define('TYPES', ['biodegradable','recyclable','residual','special']);
define('DAYS', ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday']);
try {
    $pdo = new PDO('mysql:host=localhost;dbname=waste_tracker;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $ex) {
    die('<h2>Database not reachable</h2><p>Start Apache and MySQL in XAMPP, then import <code>database.sql</code> in phpMyAdmin.</p>');
}
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect($p) { header('Location: ' . BASE_URL . '/' . $p); exit; }
function user() { return $_SESSION['user'] ?? null; }
function home_for($r) { return ['resident'=>'resident/dashboard.php','collector'=>'collector/routes.php','admin'=>'admin/dashboard.php'][$r]; }
function require_role($r) {
    if (!user()) redirect('login.php');
    if (user()['role'] !== $r) redirect(home_for(user()['role']));
}
function flash($m = null, $t = 'ok') {
    if ($m) { $_SESSION['flash'] = [$m, $t]; return; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}
function csrf() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    http_response_code(400); die('Invalid or expired form. Go back and try again.');
}
function badge($s) { return '<span class="badge ' . e($s) . '">' . e(str_replace('_', ' ', $s)) . '</span>'; }
