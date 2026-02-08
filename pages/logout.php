<?php
require_once __DIR__ . '/../configs/functions.php';

session_start();
session_destroy();
$baseUrl = base_url();
header("Location: $baseUrl");
exit;
