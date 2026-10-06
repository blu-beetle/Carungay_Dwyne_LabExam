<?php
require __DIR__ . '/config.php';
if (!empty($_SESSION['user'])) redirect('dashboard.php');

$errors = [];
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($_POST['full_name'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!csrf_ok()) {
        $errors['form'] = 'Your session expired. Please try again.';
    } else {
        if ($name === '') $errors['full_name'] = 'Full name is required.';
        elseif (!preg_match("/^[\p{L}][\p{L}\s.'-]{1,59}$/u", $name)) $errors['full_name'] = 'Use letters only (2–60 characters).';

        if ($email === '') $errors['email'] = 'Email is required.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';

        if ($password === '') $errors['password'] = 'Password is required.';
        elseif (strlen($password) < 8) $errors['password'] = 'Use at least 8 characters.';
        elseif (!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password) || !preg_match('/\d/', $password))
            $errors['password'] = 'Include an uppercase letter, a lowercase letter, and a number.';

        if ($confirm === '') $errors['confirm_password'] = 'Please confirm your password.';
        elseif ($password !== $confirm) $errors['confirm_password'] = 'Passwords do not match.';

        if (!$errors) {
            $stmt = $db->prepare('SELECT 1 FROM users WHERE email = ?');
            $stmt->execute([strtolower($email)]);
            if ($stmt->fetch()) {
                $errors['email'] = 'This email is already registered. Try signing in.';
            } else {
                $ins = $db->prepare('INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)');
                $ins->execute([$name, strtolower($email), password_hash($password, PASSWORD_DEFAULT)]);
                flash('success', 'Account created! You can now sign in.');
                redirect('login.php');
            }
        }
    }
}

function err(array $errors, string $k): string {
    return isset($errors[$k]) ? '<p class="msg">' . e($errors[$k]) . '</p>' : '';
}
function inv(array $errors, string $k): string { return isset($errors[$k]) ? 'invalid' : ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>G Mall – Sign-up</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<main class="card">
  <img class="logo" src="assets/img/logo.png" alt="G Mall logo">
  <h1>Sign-up</h1>

  <?php if (isset($errors['form'])): ?><div class="alert error" role="alert"><?= e($errors['form']) ?></div><?php endif; ?>

  <form method="post" novalidate>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

    <div class="field <?= inv($errors, 'full_name') ?>">
      <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="10" r="3"/><path d="M6.5 18.5c1.5-2.5 3.2-3.5 5.5-3.5s4 1 5.5 3.5"/></svg>
      <input type="text" name="full_name" placeholder="Full Name" value="<?= e($name) ?>" autocomplete="name" required>
    </div>
    <?= err($errors, 'full_name') ?>

    <div class="field <?= inv($errors, 'email') ?>">
      <svg class="icon" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
      <input type="email" name="email" placeholder="Email" value="<?= e($email) ?>" autocomplete="email" required>
    </div>
    <?= err($errors, 'email') ?>

    <div class="field <?= inv($errors, 'password') ?>">
      <svg class="icon" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
      <input type="password" name="password" placeholder="Password" autocomplete="new-password" required>
      <button type="button" class="toggle" aria-label="Show password"><svg class="icon" viewBox="0 0 24 24"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button>
    </div>
    <?= err($errors, 'password') ?>

    <div class="field <?= inv($errors, 'confirm_password') ?>">
      <svg class="icon" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
      <input type="password" name="confirm_password" placeholder="Confirm Password" autocomplete="new-password" required>
      <button type="button" class="toggle" aria-label="Show password"><svg class="icon" viewBox="0 0 24 24"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button>
    </div>
    <?= err($errors, 'confirm_password') ?>

    <button class="btn" type="submit">Register</button>
    <p class="switch">Already have an account? <a href="login.php">Sign in</a></p>
  </form>
</main>
<script src="assets/js/app.js"></script>
</body>
</html>
