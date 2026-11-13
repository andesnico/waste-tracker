<?php require __DIR__ . '/config.php'; $title = 'Register';
if (user()) redirect(home_for(user()['role']));
$zones = $pdo->query('SELECT * FROM zones ORDER BY name')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $n = trim($_POST['name'] ?? ''); $em = trim($_POST['email'] ?? ''); $pw = $_POST['password'] ?? '';
    $z = (int)($_POST['zone_id'] ?? 0); $ph = trim($_POST['phone'] ?? '');
    if ($n === '' || !filter_var($em, FILTER_VALIDATE_EMAIL) || strlen($pw) < 6 || !$z) {
        flash('Please complete all fields. Password needs at least 6 characters.', 'err'); redirect('register.php');
    }
    $c = $pdo->prepare('SELECT id FROM users WHERE email=?'); $c->execute([$em]);
    if ($c->fetch()) { flash('That email is already registered.', 'err'); redirect('register.php'); }
    $pdo->prepare("INSERT INTO users(name,email,password,role,zone_id,phone) VALUES(?,?,?,'resident',?,?)")
        ->execute([$n, $em, password_hash($pw, PASSWORD_DEFAULT), $z, $ph]);
    flash('Account created. You can now log in.'); redirect('login.php');
}
include __DIR__ . '/includes/header.php'; ?>
<form class="card form" method="post"><h1>Create resident account</h1><?= csrf() ?>
  <label>Full name</label><input name="name" required>
  <label>Email</label><input type="email" name="email" required>
  <label>Mobile number</label><input name="phone">
  <label>Your zone</label><select name="zone_id" required><option value="">Select zone</option>
    <?php foreach ($zones as $z): ?><option value="<?= $z['id'] ?>"><?= e($z['name']) ?></option><?php endforeach; ?></select>
  <label>Password</label><input type="password" name="password" minlength="6" required>
  <button class="btn">Register</button>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
