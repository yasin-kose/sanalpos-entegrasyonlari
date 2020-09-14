<?php

require '../../../vendor/autoload.php';

$host_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]";
$path = '/sanalpos-entegrasyonlari/examples/vpos724/regular/';
$base_url = $host_url . $path;

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$ip = $request->getClientIp();

$account = [
    'bank'          => 'vakifbank',
    'model'         => 'regular',
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

$gateway = $base_url . 'response.php';

$template_title = 'Regular Payment';
