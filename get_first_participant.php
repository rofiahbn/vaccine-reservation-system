<?php
session_start();

$first = $_SESSION['participants'][0] ?? null;

echo json_encode([
    'email' => $first['emails'][0] ?? '',
    'phone' => $first['phones'][0] ?? '',
    'alamat' => $first['alamat'] ?? '',
    'provinsi' => $first['provinsi'] ?? '',
    'kota' => $first['kota'] ?? ''
]);
