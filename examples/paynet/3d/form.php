<?php

require '_config.php';

require '../../template/_header.php';

if ($request->getMethod() !== 'POST') {
    echo new \Symfony\Component\HttpFoundation\RedirectResponse($base_url);
    exit();
}

$order_id = date('Ymd') . strtoupper(substr(uniqid(sha1(time())),0,4));
$amount = (double) 100;

$success_url = $base_url . 'response.php';
$fail_url = $base_url . 'response.php';
$order = [
    'id'                => $order_id,
    'email'             => 'mail@customer.com', // optional
    'user_id'           => '12', // optional
    'amount'            => $amount,
    'installment'       => 1,
    'ip'                => "85.99.23.227",
    'success_url'       => $success_url,
    'fail_url'          => $fail_url,
    'currency'          => 'TRY',
    'transaction'       => 'pay', // pay => Sale
];
unset($_SESSION['order']);
$_SESSION['order'] = $order;

$card = [
    'name'      => $_POST['name'],
    'number'    => $_POST['number'],
    'month'     => $_POST['month'],
    'year'      => $_POST['year'],
    'cvv'       => $_POST['cvv'],
];

$pos->prepare($order, $card);

echo  $pos->get3dForm();
?>

<?php require '../../template/_footer.php'; ?>
