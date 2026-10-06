<?php
require __DIR__ . '/config.php';
if (empty($_SESSION['user'])) redirect('login.php');
$user = $_SESSION['user'];
$flash = take_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>G Mall – Welcome</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<main class="card">
  <img class="logo" src="assets/img/logo.png" alt="G Mall logo">
  <h1>Welcome</h1>
  <?php if ($flash): ?><div class="alert <?= e($flash[0]) ?>" role="status"><?= e($flash[1]) ?></div><?php endif; ?>
  <p class="profile"><strong><?= e($user['name']) ?></strong><br><?= e($user['email']) ?></p>
  <a class="btn" href="logout.php">Sign out</a>
</main>
</body>
</html>
