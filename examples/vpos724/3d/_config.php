<?php
session_start();

require '../../../vendor/autoload.php';

$host_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]";
$path = '/sanalpos-entegrasyonlari/examples/vpos724/3d/';
$base_url = $host_url . $path;

$success_url = $base_url . 'response.php';
$fail_url = $base_url . 'response.php';

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$ip = $request->getClientIp();

$account = [
    'bank'          => 'vakifbank',
    'model'         => '3d',
    'merchant_id'     => '000100000013506',
    'terminal_id'   => 'VP000579',
    'password'     => '123456',
    'env'           => 'test',
];

try {
    $pos = new \Ankapix\SanalPos\Pos($account);
} catch (\Ankapix\SanalPos\Exceptions\BankNotFoundException $e) {
    var_dump($e->getCode(), $e->getMessage());
} catch (\Ankapix\SanalPos\Exceptions\BankClassNullException $e) {
    var_dump($e->getCode(), $e->getMessage());
}


$template_title = '3D Model Payment';
