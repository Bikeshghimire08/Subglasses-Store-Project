<?php
session_start();
require_once __DIR__ . '/admin_config.php';

$pass = $_POST['pass'] ?? '';
if (hash_equals(ADMIN_PASSCODE, $pass)) {
    $_SESSION['is_admin'] = true;
    header('Location: ../admin.php');
    exit;
}
header('Location: ../admin.php?error=no');
exit;
