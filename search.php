<?php
require 'config.php';
header('Content-Type: application/json; charset=utf-8');
$q = $_GET['q'] ?? '';
$results = search_persons_by_name($q);
echo json_encode(array_values($results));
exit;
