<?php
require_once __DIR__ . '/helpers/auth.php';
logout_user();
header('Location: login.php');
exit;
