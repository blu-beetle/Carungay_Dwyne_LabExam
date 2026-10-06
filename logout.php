<?php
require __DIR__ . '/config.php';
$_SESSION = [];
session_destroy();
session_start();
flash('success', 'You have been signed out.');
redirect('login.php');
