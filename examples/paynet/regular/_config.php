<?php

require '../../../vendor/autoload.php';

$host_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]";
$path = '/sanalpos-entegrasyonlari/examples/paynet/regular/';
$base_url = $host_url . $path;

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$ip = $request->getClientIp();

$account = [
    'bank'          => 'paynet',
    'model'         => 'regular',
    'secret_key'     => 'sck_IDlnRlfPMo9CMAb4yBTM54c2XVBE',
    'is_commission'     => false,
    'is_slip_post'     => false,
    'is_installment'   => true,
    'ratio_code'     => '',
    'agent_id'     => '',
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
