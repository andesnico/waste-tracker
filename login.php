<?php require __DIR__ . '/config.php'; $title = 'Login';
if (user()) redirect(home_for(user()['role']));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $st = $pdo->prepare('SELECT * FROM users WHERE email=?'); $st->execute([trim($_POST['email'] ?? '')]);
    $u = $st->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id'=>$u['id'],'name'=>$u['name'],'role'=>$u['role'],'zone_id'=>$u['zone_id']];
        flash('Welcome back, ' . $u['name'] . '!'); redirect(home_for($u['role']));
    }
    flash('Incorrect email or password.', 'err'); redirect('login.php');
}
include __DIR__ . '/includes/header.php'; ?>
<form class="card form" method="post"><h1>Login</h1><?= csrf() ?>
  <label>Email</label><input type="email" name="email" required autofocus>
  <label>Password</label><input type="password" name="password" required>
  <button class="btn">Login</button>
  <p class="mut">No account? <a href="register.php">Register as a resident</a></p>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
