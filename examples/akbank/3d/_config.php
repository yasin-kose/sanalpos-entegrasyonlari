<?php

session_start();

require '../../../vendor/autoload.php';

$host_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]";
$path = '/pos/examples/akbank/3d/';
$base_url = $host_url . $path;

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$ip = $request->getClientIp();

$account = [
    'bank'          => 'akbank',
    'model'         => '3d',
    'client_id'     => 'XXXXXXX',
    'username'      => 'XXXXXXX',
    'password'      => 'XXXXXXX',
    'store_key'     => 'XXXXXXX',
    'env'           => 'test',
];

try {
    $pos = new \SanalPos\($account);
} catch (\SanalPos\Exceptions\BankNotFoundException $e) {
    var_dump($e->getCode(), $e->getMessage());
} catch (\SanalPos\Exceptions\BankClassNullException $e) {
    var_dump($e->getCode(), $e->getMessage());
}

$template_title = '3D Model Payment';
