<?php require __DIR__ . '/../config.php'; require_role('admin'); $title = 'Users';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    if ($id !== user()['id'] && in_array($_POST['role'] ?? '', ['resident','collector','admin'])) {
        $pdo->prepare('UPDATE users SET role=?, zone_id=? WHERE id=?')->execute([$_POST['role'], (int)$_POST['zone_id'] ?: null, $id]); flash('User updated.');
    } else flash('You cannot change your own role.', 'err');
    redirect('admin/users.php');
}
$zones = $pdo->query('SELECT * FROM zones ORDER BY name')->fetchAll();
$us = $pdo->query('SELECT * FROM users ORDER BY role, name')->fetchAll();
include __DIR__ . '/../includes/header.php'; ?>
<h1>Users</h1>
<div class="card"><input data-filter="#usr" placeholder="Search name, email, role..."></div>
<div class="card tbl"><table id="usr"><thead><tr><th>Name</th><th>Email</th><th>Role & zone</th></tr></thead><tbody>
<?php foreach ($us as $x): ?><tr><td><?= e($x['name']) ?></td><td><?= e($x['email']) ?></td><td>
  <form method="post" class="row"><?= csrf() ?><input type="hidden" name="id" value="<?= $x['id'] ?>">
  <select name="role"><?php foreach (['resident','collector','admin'] as $r): ?><option <?= $r === $x['role'] ? 'selected' : '' ?>><?= $r ?></option><?php endforeach; ?></select>
  <select name="zone_id"><option value="0">No zone</option><?php foreach ($zones as $z): ?><option value="<?= $z['id'] ?>" <?= $z['id'] == $x['zone_id'] ? 'selected' : '' ?>><?= e($z['name']) ?></option><?php endforeach; ?></select>
  <button class="btn sm">Save</button></form></td></tr><?php endforeach; ?></tbody></table></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
