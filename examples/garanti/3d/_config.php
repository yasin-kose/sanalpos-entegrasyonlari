<?php

session_start();

require '../../../vendor/autoload.php';

$host_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]";
$path = '/sanalpos-entegrasyonlari/examples/garanti/3d/';
$base_url = $host_url . $path;

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$ip = $request->getClientIp();

$account = [
    'bank'              => 'garanti',
    'model'             => '3d',
    'client_id'         => '7000679',
    'terminal_id'       => '30691298',
    'username'          => 'PROVAUT',
    'password'          => '123qweASD/',
    'store_key'         => '12345678',
    'env'               => 'test',
];

try {
    $pos = new \Ankapix\SanalPos\Pos($account);
} catch (\Ankapix\SanalPos\Exceptions\BankNotFoundException $e) {
    var_dump($e->getCode(), $e->getMessage());
} catch (\Ankapix\SanalPos\Exceptions\BankClassNullException $e) {
    var_dump($e->getCode(), $e->getMessage());
}

$template_title = '3D Model Payment';
