<?php
require __DIR__ . '/config.php';
if (!empty($_SESSION['user'])) redirect('dashboard.php');

$errors = [];
$email = $_COOKIE['remember_email'] ?? '';
$remember = isset($_COOKIE['remember_email']);
$flash = take_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (!csrf_ok()) {
        $errors['form'] = 'Your session expired. Please try again.';
    } else {
        if ($email === '') $errors['email'] = 'Email is required.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';

        if ($password === '') $errors['password'] = 'Password is required.';

        if (!$errors) {
            $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([strtolower($email)]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user'] = ['id' => $user['id'], 'name' => $user['full_name'], 'email' => $user['email']];
                if ($remember) setcookie('remember_email', $email, time() + 60*60*24*30, '/', '', false, true);
                else setcookie('remember_email', '', time() - 3600, '/');
                flash('success', 'Welcome back, ' . $user['full_name'] . '!');
                redirect('dashboard.php');
            }
            $errors['form'] = 'Incorrect email or password.';
        }
    }
}
$title = 'Sign-in';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>G Mall – Sign-in</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<main class="card">
  <img class="logo" src="assets/img/logo.png" alt="G Mall logo">
  <h1>Sign-in</h1>

  <?php if ($flash): ?><div class="alert <?= e($flash[0]) ?>" role="status"><?= e($flash[1]) ?></div><?php endif; ?>
  <?php if (isset($errors['form'])): ?><div class="alert error" role="alert"><?= e($errors['form']) ?></div><?php endif; ?>

  <form method="post" novalidate>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

    <div class="field <?= isset($errors['email']) ? 'invalid' : '' ?>">
      <svg class="icon" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
      <input type="email" name="email" placeholder="Email" value="<?= e($email) ?>" autocomplete="email" required>
    </div>
    <?php if (isset($errors['email'])): ?><p class="msg"><?= e($errors['email']) ?></p><?php endif; ?>

    <div class="field <?= isset($errors['password']) ? 'invalid' : '' ?>">
      <svg class="icon" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
      <input type="password" name="password" placeholder="Password" autocomplete="current-password" required>
      <button type="button" class="toggle" aria-label="Show password">
        <svg class="icon" viewBox="0 0 24 24"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
      </button>
    </div>
    <?php if (isset($errors['password'])): ?><p class="msg"><?= e($errors['password']) ?></p><?php endif; ?>

    <div class="row">
      <label class="check"><input type="checkbox" name="remember" <?= $remember ? 'checked' : '' ?>><span></span>Remember me</label>
      <a href="register.php">Sign-up now</a>
    </div>

    <button class="btn" type="submit">Sign in</button>
  </form>
</main>
<script src="assets/js/app.js"></script>
</body>
</html>
