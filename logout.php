<?php
define('BASE_URL', '/SAGD/');
require_once __DIR__ . '/includes/funciones.php';
logout();
header('Location: ' . BASE_URL . 'login.php');
exit;
