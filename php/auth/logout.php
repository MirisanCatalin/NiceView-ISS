<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';

logout();
header('Location: login.php?msg=logged_out');
exit;