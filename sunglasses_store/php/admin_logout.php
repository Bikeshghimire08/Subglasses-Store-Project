<?php
session_start();
unset($_SESSION['is_admin']);
header('Location: ../admin.php');
exit;
