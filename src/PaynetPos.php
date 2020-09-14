<?php

namespace Ankapix\SanalPos;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Ankapix\SanalPos\Exceptions\UnsupportedPaymentModelException;
use Ankapix\SanalPos\Exceptions\UnsupportedTransactionTypeException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PaynetPos
 * @package Ankapix\SanalPos
 */
class PaynetPos implements PosInterface
{
    use PosHelpersTrait;

    /**
     * @const string
     */
    public const NAME = 'PaynetPay';

    /**
     * Response Codes
     *
     * @var array
     */
    public $codes = [
        '0'    => "success ful",
        '1'    => "unsuccess ful",
        '2'    => "company blocked",
        '3'    => "agent blocked",
        '4'    => "agent not found",
        '5'    => "duplicate data",
        '6'    => "no process",
        '7'    => "unauthorized",
        '8'    => "server error",
        '9'    => "not implemented",
        '10'   => "time out",
        '11'   => "bad request",
        '12'   => "no data",
        '13'   => "paynetj no session",
        '14'   => "paynetj wrong bin",
        '15'   => "paynetj unmatch tran",
        '16'   => "paynetj 3d error",
        '17'   => "paynetj used session",
        '18'   => "wrong card data",
        '19'   => "wrong transaction type",
        '20'   => "wrong pos type",
        '21'   => "wrong ratio get",
        '100'  => "old success ful"
    ];

    /**
     * Transaction Types
     *
     * @var array
     */
    public $types = [
        'pay'   => '1',
        'pre'   => '3'
    ];

    /**
     * Currencies
     *
     * @var array
     */
    public $currencies = [];

    /**
     * Transaction Type
     *
     * @var string
     */
    public $type;

    /**
     * API Account
     *
     * @var array
     */
    protected $account = [];

    /**
     * Order Details
     *
     * @var array
     */
    protected $order = [];

    /**
     * Credit Card
     *
     * @var object
     */
    protected $card;

    /**
     * Request
     *
     * @var Request
     */
    protected $request;

    /**
     * Response Raw Data
     *
     * @var object
     */
    protected $data;

    /**
     * Processed Response Data
     *
     * @var mixed
     */
    public $response;

    /**
     * Configuration
     *
     * @var array
     */
    protected $config = [];

    /**
     * Mode
     *
     * @var string
     */
    protected $mode = 'PROD';

    /**
     * API version
     * @var string
     */
    protected $version = 'V3.4';

    /**
     * GarantiPost constructor.
     *
     * @param array $config
     * @param array $account
     * @param array $currencies
     */
    public function __construct($config, $account, array $currencies)
    {
        $request = Request::createFromGlobals();
        $this->request = $request->request;

        $this->config = $config;
        $this->account = $account;
        $this->currencies = $currencies;

        if ($this->account->env == 'test') {
            $this->mode = 'TEST';
        }

        return $this;
    }

    /**
     * Amount Formatter
     *
     * @param double $amount
     * @return float
     */
    public function amountFormat($amount)
    {
        return number_format($amount, 2, ',', '');
    }

    /**
     * Create Regular Payment Post
     *
     * @return object
     */
    protected function createRegularPaymentPOST()
    {
        $IsLive     = $this->mode=='TEST'?false:true;
        $paynet     = new \PaynetClient($this->account->secret_key, $IsLive);
        
        $paymentParams 				    = new \PaymentParameters();
        $paymentParams->amount 	        = (string) $this->amountFormat($this->order->amount);
        $paymentParams->reference_no 	= (string) $this->order->id;
        $paymentParams->pan 	        = $this->card->number;
        $paymentParams->month 	        = $this->card->month;
        $paymentParams->year 	        = $this->card->year;
        $paymentParams->cvc 	        = (string) $this->card->cvv;
        $paymentParams->card_holder_mail= (string) filter_var($this->order->email, FILTER_VALIDATE_EMAIL)?$this->order->email:"msn@msn.com";
        $paymentParams->description 	= (string) isset($this->order->description)?$this->order->description:"";
        if($this->account->is_installment){
            $paymentParams->instalment 	= (int) $this->order->installment?:0;
        }
        $paymentParams->add_commission 	= $this->account->is_commission?true:false;

        if($this->account->ratio_code){
            $paymentParams->ratio_code 	= (string) $this->account->installment_code;
        }
        if($this->account->agent_id){
            $paymentParams->agent_id 	= (string) $this->account->agent_id;
        }
        $paymentParams->transaction_type= (int) $this->type;

        $result 			= $paynet->PaymentPost($paymentParams);
        $XactId 			= $result->xact_id;
        $this->data         = $result;
        if($result->is_succeed == true and filter_var($this->order->email, FILTER_VALIDATE_EMAIL) and $this->order->is_slip_post==true){
            $SlipParams 				= new \SlipParameters();
            $SlipParams->xact_id 	 	= (string) $XactId;
            $SlipParams->email 			= (string) $this->order->email;
            $SlipParams->send_mail		= true;
            $SlipResult		 			= $paynet->SlipPost($SlipParams);
        }
        return $this;
    }
    /**
     * Create 3D Payment POST
     * @return object
     */
    protected function create3DPaymentPOST()
    {
        $IsLive     = $this->mode=='TEST'?false:true;
        $paynet     = new \PaynetClient($this->account->secret_key, $IsLive);
        
        $chargeParams 				= new \ChargeParameters();
        $chargeParams->session_id 	= (string) $this->request->get('session_id');
        $chargeParams->token_id 	= (string) $this->request->get('token_id');

        //Charge işlemini çalıştırır
        $result 			= $paynet->ChargePost($chargeParams);
        $XactId			    = $result->xact_id;
        $BankaHata			= $result->bank_error_message;
        $EMail				= $result->email?$result->email:'';

        if($result->is_succeed == true and filter_var($EMail, FILTER_VALIDATE_EMAIL) and $this->order->is_slip_post==true){
            $SlipParams 				= new \SlipParameters();
            $SlipParams->xact_id 	 	= (string) $XactId;
            $SlipParams->email 			= (string) $EMail;
            $SlipParams->send_mail		= true;
            $SlipResult		 			= $paynet->SlipPost($SlipParams);
        }
        $this->data         = $result;
        return $this;
    }

    /**
     * Get ProcReturnCode
     *
     * @return string|null
     */
    protected function getProcReturnCode()
    {
        return isset($this->data->code) ? (string) $this->data->code : null;
    }

    /**
     * Get Status Detail Text
     *
     * @return string|null
     */
    protected function getStatusDetail()
    {
        $proc_return_code = $this->getProcReturnCode();

        return $proc_return_code ? (isset($this->codes[$proc_return_code]) ? (string) $this->codes[$proc_return_code] : null) : null;
    }

    /**
     * Regular Payment
     *
     * @return $this
     * @throws GuzzleException
     */
    public function makeRegularPayment()
    {
        $this->createRegularPaymentPOST();

        $status = 'declined';
        if ($this->getProcReturnCode() == '0') {
            $status = 'approved';
        }

        $this->response = (object) [
            'id'                => isset($this->data->reference_no) ? $this->printData($this->data->reference_no) : null,
            'reference_no'      => isset($this->data->reference_no) ? $this->printData($this->data->reference_no) : null,
            'response'          => isset($this->data->message) ? $this->printData($this->data->message) : null,
            'transaction_type'  => $this->type,
            'transaction'       => $this->order->transaction,
            'proc_return_code'  => $this->getProcReturnCode(),
            'code'              => $this->getProcReturnCode(),
            'status'            => $status,
            'status_detail'     => $this->getStatusDetail(),
            'error_code'        => isset($this->data->paynet_error_id) ? $this->printData($this->data->paynet_error_id) : null,
            'error_message'     => isset($this->data->paynet_error_message) ? $this->printData($this->data->paynet_error_message) : null,
            'xact_id'           => isset($this->data->xact_id) ? $this->printData($this->data->xact_id) : null,
            'comission'         => isset($this->data->comission) ? $this->printData($this->data->comission) : null,
            'xact_date'         => isset($this->data->xact_date) ? $this->printData($this->data->xact_date) : null,
            'card_holder'       => isset($this->data->card_holder) ? $this->data->card_holder : null,
            'card_no_masked'    => isset($this->data->card_no_masked) ? $this->data->card_no_masked : null,
            'card_type'         => isset($this->data->card_type) ? $this->data->card_type : null,
            'currency'          => isset($this->data->currency) ? $this->data->currency : null,
            'instalment'        => isset($this->data->card_no_masked) ? $this->data->instalment : null,
            'agent_id'          => isset($this->data->agent_id) ? $this->data->agent_id : null,
            'amount'            => isset($this->data->amount) ? $this->data->amount : null,
            'all'               => $this->data,
            'original'          => $this->data
        ];

        return $this;
    }

    /**
     * Make 3D Payment
     *
     * @return $this
     * @throws GuzzleException
     */
    public function make3DPayment()
    
    {
        $status = 'declined';
        $proc_return_code = '1';
        $transaction_security = 'MPI fallback';
        $contents = $this->create3DPaymentPOST();
        if (in_array($this->data->md_status, [1, 2, 3, 4])) {
            if ($this->data->md_status == 1) {
                $transaction_security = 'Full 3D Secure';
            } elseif (in_array($this->data->md_status, [2, 3, 4])) {
                $transaction_security = 'Half 3D Secure';
            }

            if ($this->getProcReturnCode() == '0') {
                $proc_return_code = $this->getProcReturnCode();
                $status = 'approved';
            }
        }

        $this->response = (object) [
            'id'                => isset($this->data->reference_no) ? $this->printData($this->data->reference_no) : null,
            'reference_no'      => isset($this->data->reference_no) ? $this->printData($this->data->reference_no) : null,
            'response'          => isset($this->data->message) ? $this->printData($this->data->message) : null,
            'transaction_type'  => $this->type,
            'transaction'       => $this->order->transaction,
            'proc_return_code'  => $this->getProcReturnCode(),
            'code'              => $this->getProcReturnCode(),
            'status'            => $status,
            'status_detail'     => $this->getStatusDetail(),
            'error_code'        => isset($this->data->paynet_error_id) ? $this->printData($this->data->paynet_error_id) : null,
            'error_message'     => isset($this->data->paynet_error_message) ? $this->printData($this->data->paynet_error_message) : null,
            'xact_id'           => isset($this->data->xact_id) ? $this->printData($this->data->xact_id) : null,
            'comission'         => isset($this->data->comission) ? $this->printData($this->data->comission) : null,
            'xact_date'         => isset($this->data->xact_date) ? $this->printData($this->data->xact_date) : null,
            'card_holder'       => isset($this->data->card_holder) ? $this->data->card_holder : null,
            'card_no_masked'    => isset($this->data->card_no_masked) ? $this->data->card_no_masked : null,
            'card_type'         => isset($this->data->card_type) ? $this->data->card_type : null,
            'currency'          => isset($this->data->currency) ? $this->data->currency : null,
            'instalment'        => isset($this->data->card_no_masked) ? $this->data->instalment : null,
            'agent_id'          => isset($this->data->agent_id) ? $this->data->agent_id : null,
            'amount'            => isset($this->data->amount) ? $this->data->amount : null,
            'is_tds'            => isset($this->data->is_tds) ? $this->data->is_tds : null,
            'md_status'         => isset($this->data->is_tds) ? $this->data->md_status : null,
            'all'               => $this->data,
            'original'          => $this->data
        ];

        return $this;
    }

    /**
    * Get 3d Form Data
    *
    * @return array
    */
    public function get3DFormData()
    {
        $IsLive     = $this->mode=='TEST'?false:true;
        $paynet     = new \PaynetClient($this->account->secret_key, $IsLive);
        
        $paymentParams 				    = new \Three3DPaymentParameters();
        $paymentParams->amount 	        = (string) $this->amountFormat($this->order->amount);
        $paymentParams->reference_no 	= (string) $this->order->id;
        $paymentParams->pan 	        = $this->card->number;
        $paymentParams->month 	        = $this->card->month;
        $paymentParams->year 	        = $this->card->year;
        $paymentParams->cvc 	        = (string) $this->card->cvv;
        $paymentParams->card_holder_mail= (string) filter_var($this->order->email, FILTER_VALIDATE_EMAIL)?$this->order->email:"msn@msn.com";
        $paymentParams->description 	= (string) isset($this->order->description)?$this->order->description:"";
        if($this->account->is_installment){
            $paymentParams->instalment 	= (int) $this->order->installment?:0;
        }
        $paymentParams->add_commission 	= $this->account->is_commission?true:false;

        if($this->account->ratio_code){
            $paymentParams->ratio_code 	= (string) $this->account->installment_code;
        }
        if($this->account->agent_id){
            $paymentParams->agent_id 	= (string) $this->account->agent_id;
        }
        $paymentParams->transaction_type= (int) $this->type;

        $result 			= $paynet->There3DPaymentPost($paymentParams);
        $inputs             = [
            'token_id'      => $result->token_id,
            'session_id'    => $result->session_id
        ];

        return [
            'code'          => $result->code,
            'message'       => $result->message,
            'gateway'       => $result->post_url,
            'inputs'        => $inputs,
        ];
    }
    /**
    * Get 3d Form 
    *
    * @return array
    */
    public function get3DForm()
    {
       $form_data = (array) $this->get3DFormData();
       if ($data['code'] != '0') {
            return isset($data['message']) ? $this->printData($data['message']):"İşlem Başarısız, Lütfen tekrar deneyiniz.";
       }
       $return = '<form method="post" action="'.$form_data['gateway'].'"  name="3dForm" class="redirect-form" role="form">';
       foreach ($form_data['inputs'] as $key => $value){
           $return .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
       }
       $return .= '<div class="text-center">Yönlendiriliyorsunuz...</div> <hr>
           <div class="form-group text-center">
               <button type="submit" class="btn btn-lg btn-block btn-success">Ödeme Doğrulaması Yap</button>
           </div>
           <SCRIPT LANGUAGE="Javascript">document.3dForm.submit();</SCRIPT>
       </form>';
       return $return;
    }

    /**
     * Prepare Order
     *
     * @param object $order
     * @param object null $card
     * @return mixed
     * @throws UnsupportedTransactionTypeException
     */
    public function prepare($order, $card = null)
    {
        $this->type = $this->types['pay'];
        if (isset($order->transaction)) {
            if (array_key_exists($order->transaction, $this->types)) {
                $this->type = $this->types[$order->transaction];
            } else {
                throw new UnsupportedTransactionTypeException('Unsupported transaction type!');
            }
        }

        $this->order = $order;
        $this->card = $card;

        if ($this->card) {
            $this->card->month = str_pad($this->card->month, 2, '0', STR_PAD_LEFT);
            $this->card->year  = "20".str_pad($this->card->month, 2, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Make Payment
     *
     * @param object $card
     * @return mixed
     * @throws UnsupportedPaymentModelException
     * @throws GuzzleException
     */
    public function payment($card)
    {
        $this->card = $card;

        $model = 'regular';
        if (isset($this->account->model) && $this->account->model) {
            $model = $this->account->model;
        }

        if ($model == 'regular') {
            $this->makeRegularPayment();
        } elseif ($model == '3d') {
            $this->make3DPayment();
        } else {
            throw new UnsupportedPaymentModelException();
        }

        return $this;
    }

    /**
     * Refund or Cancel Order
     *
     * @param array $meta
     * @param $type
     * @return $this
     * @throws GuzzleException
     */
    protected function refundOrCancel(array $meta, $type)
    {
        $this->order = (object) [
            'id'        => $meta['order_id'],
            'amount'    => isset($meta['amount']) ? $meta['amount'] : null,
            'ip'        => isset($meta['ip']) ? $meta['ip'] : null,
        ];

        $nodes = [
            'VposRequest'   => [
                'MerchantId'            => $this->account->merchant_id,
                'Password'              => $this->account->password,
                'TransactionType'       => $type,
                'ReferenceTransactionId'=> $this->order->id,
                'CurrencyAmount'        => $this->amountFormat($this->order->amount),
                'ClientIp'              => isset($this->order->ip) ?$this->order->ip:$this->getIpAdress()
            ]
        ];

        $xml = $this->createXML($nodes);
        $this->send($xml);

        $status = 'declined';
        if ($this->getProcReturnCode() == '0000') {
            $status = 'approved';
        }

        $this->response = (object) [
            'id'                => isset($this->data->VposResponse->AuthCode) ? $this->printData($this->data->VposResponse->AuthCode) : null,
            'response'          => isset($this->data->VposResponse->ResultDetail) ? $this->printData($this->data->VposResponse->ResultDetail) : null,
            'transaction_type'  => $this->type,
            'transaction'       => $this->order->transaction,
            'proc_return_code'  => $this->getProcReturnCode(),
            'code'              => $this->getProcReturnCode(),
            'status'            => $status,
            'status_detail'     => $this->getStatusDetail(),
            'error_code'        => isset($this->data->VposResponse->ResultCode) ? $this->printData($this->data->VposResponse->ResultCode) : null,
            'error_message'     => isset($this->data->VposResponse->ResultDetail) ? $this->printData($this->data->VposResponse->ResultDetail) : null,
            'host_date'         => isset($this->data->VposResponse->HostDate) ? $this->printData($this->data->VposResponse->HostDate) : null,
            'rnd'               => isset($this->data->VposResponse->Rrn) ? $this->printData($this->data->VposResponse->Rrn) : null,
            'auth_code'         => isset($this->data->VposResponse->AuthCode) ? $this->printData($this->data->VposResponse->AuthCode) : null,
            'extra'             => isset($this->data->VposResponse->Extra) ? $this->data->VposResponse->Extra : null,
            'all'               => $this->data,
            'original'          => $this->data
        ];

        return $this;
    }

    /**
    * Refund Order
    *
    * @param $meta
    * @return $this
    * @throws GuzzleException
    */
    public function refund(array $meta)
    {
        return $this->refundOrCancel($meta, 'Refund');
    }

    /**
    * Cancel Order
    *
    * @param array $meta
    * @return $this
    * @throws GuzzleException
    */
    public function cancel(array $meta)
    {
        return $this->refundOrCancel($meta, 'Cancel');
    }

    /**
    * Make 3D Pay Payment
    *
    * @return $this
    */
    public function make3DPayPayment()
    {
    }

    /**
    * Order Status
    *
    * @param array $meta
    * @return $this
    * @throws GuzzleException
    */
    public function status(array $meta)
    {
    }

    /**
    * Order History
    *
    * @param array $meta
    * @return $this
    * @throws GuzzleException
    */
    public function history(array $meta)
    {
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return mixed
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @return array
     */
    public function getCurrencies()
    {
        return $this->currencies;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return mixed
     */
    public function getCard()
    {
        return $this->card;
    }
}
