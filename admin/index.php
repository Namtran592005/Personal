<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Location: ' . BASE_PATH . '/admin/dashboard.php');
exit;
