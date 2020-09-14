<?php

namespace Ankapix\SanalPos;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Ankapix\SanalPos\Exceptions\UnsupportedPaymentModelException;
use Ankapix\SanalPos\Exceptions\UnsupportedTransactionTypeException;
use Ankapix\SanalPos\Exceptions\UnknownError;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class KuveytPos
 * @package Ankapix\SanalPos
 */
class KuveytPos implements PosInterface
{
    use PosHelpersTrait;

    /**
     * @const string
     */
    public const NAME = 'KuveytPay';

    /**
     * API URL
     *
     * @var string
     */
    public $url;

    /**
    * 3D Pay Gateway URL
     *
     * @var string
     */
    public $gateway;

    /**
     * Response Codes
     *
     * @var array
     */
    public $codes = [
        '00'    => "ONAYLANDI",
        '01'    => "BANKASINI ARAYINIZ",
        '02'    => "KATEGORI YOK",
        '03'    => "UYE KODU HATALI /TANIMSIZ",
        '04'    => "KARTA EL KOYUNUZ / SAKINCALI",
        '05'    => "RED / ONAYLANMADI/CVV HATALI",
        '06'    => "HATALI ISLEM",
        '07'    => "KARTA EL KOYUNUZ",
        '08'    => "KIMLIK KONTROLU / ONAYLANDI",
        '11'    => "V.I.P KODU / ONAYLANDI",
        '12'    => "HATALI ISLEM / RED",
        '13'    => "HATALI MIKTAR / RED",
        '14'    => "KART-HESAP NO HATALI",
        '15'    => "MUSTERI YOK",
        '19'    => "ISLEMI TEKRAR GIR",
        '21'    => "ISLEM YAPILAMADI",
        '24'    => "DOSYASINA ULASILAMADI",
        '25'    => "DOSYASINA ULASILAMADI",
        '26'    => "DOSYASINA ULASILAMADI",
        '27'    => "DOSYASINA ULASILAMADI",
        '28'    => "DOSYASINA ULASILAMADI",
        '30'    => "FORMAT HATASI (UYEISYERI)",
        '32'    => "DOSYASINA ULASILAMADI",
        '33'    => "SURESI BITMIS/IPTAL KART",
        '34'    => "SAHTE KART",
        '38'    => "ŞIFRE AŞIMI / ELKOY",
        '41'    => "KAYIP KART",
        '43'    => "CALINTI KART",
        '51'    => "YETERSIZ HESAP/DEBIT KART",
        '52'    => "HESAP NO YU KONTROL EDIN",
        '53'    => "HESAP YOK",
        '54'    => "SURESI BITMIS KART",
        '55'    => "SIFRE HATALI",
        '57'    => "HARCAMA RED/BLOKELI",
        '58'    => "TERM.TRANSEC. YOK",
        '61'    => "CEKME LIMIT ASIMI",
        '62'    => "YASAKLANMIS KART",
        '65'    => "LIMIT ASIMI/BORC BAKIYE VAR",
        '75'    => "SIFRE TEKRAR ASIMI",
        '76'    => "KEY SYN. HATASI",
        '82'    => "CVV HATALI / RED",
        '91'    => "BANKASININ SWICI ARIZALI",
        '92'    => "BANKASI BILINMIYOR",
        '96'    => "BANKASININ SISTEMI ARIZALI",
        'TO'    => "TIME OUT",
        'GP'    => "GECERSIZ POS",
        'TB'    => "TUTARI BÖLÜNÜZ",
        'UP'    => "UYUMSUZ POS",
        'IP'    => " IPTAL POS",
        'CS'    => "CICS SORUNU",
        'BG'    => "BİLGİ GİTMEDİ",
        'NA'    => "NO AMEX",
        'OI'    => "OKEY İPTAL OTOR.",
        'NI'    => "İPTAL İPTAL EDİLEMEDİ",
        'NS'    => "NO SESION(HAT YOK)"
    ];

    /**
    * Transaction Types
    *
    * @var array
    */
    public $types = [
        'pay'   => 'Sale'
    ];

    /**
     * Currencies
     *
     * @var array
     */
    public $currencies = [];


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
    protected $version = '1.0.0';

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

        $this->url = isset($this->config['urls'][$this->account->env]) ?
            $this->config['urls'][$this->account->env] :
            $this->config['urls']['production'];

        $this->gateway = isset($this->config['urls']['gateway'][$this->account->env]) ?
            $this->config['urls']['gateway'][$this->account->env] :
            $this->config['urls']['gateway']['production'];

        return $this;
    }

    /**
     * Make Security Data
     *
     * @param bool $refund
     * @return string
     */
    protected function makeSecurityData($refund = false)
    {
        $map = [
            $this->account->password
        ];

        return base64_encode(sha1(implode('', $map), 'ISO-8859-9'));
    }

    /**
     * Make 3d Hash Data
     *
     * @param $security_data
     * @return string
     */
    protected function make3dHashData($security_data)
    {
        $map = [
            $this->account->merchant_id,
            $this->order->id,
            $this->amountFormat($this->order->amount),
            $this->order->success_url,
            $this->order->fail_url,
            $this->order->name,
            $security_data,
        ];

        return base64_encode(sha1(implode('', $map),"ISO-8859-9"));
    }

    /**
     * Amount Formatter
     *
     * @param double $amount
     * @return int
     */
    public function amountFormat($amount)
    {
        return (int) ((float) $amount * 100);
    }

    /**
    * Card Type
    *
    * @param int $amount
    * @return string
    */
    public function getCardType()
    {
        return (string) substr($this->card->number, 1) == "4" ? "Visa" : "MasterCard";
    }
    /**
    * Create 3D Payment XML
    * @return string
    */
    protected function create3DPaymentXML($Request)
    {
        $hash_data      = $this->create3DHash();
        $nodes = [
            'KuveytTurkVPosMessage'   => [
                'APIVersion' =>$this->version,
                'HashData' =>$hash_data,
                'MerchantId' =>$this->account->merchant_id,
                'CustomerId' => $this->account->client_id,
                'UserName' => $this->account->username,
                'TransactionType' => $this->type,
                'InstallmentCount' => $this->order->installment > 1 ? $this->order->installment : '0',
                'Amount' => isset($Request->VPosMessage->Amount) ? $this->printData($Request->VPosMessage->Amount) : null,
                'CurrencyCode' => $this->order->currency,
                'MerchantOrderId' => $Request->VPosMessage->MerchantOrderId,
                'TransactionSecurity' => '3',
                'KuveytTurkVPosAdditionalData' => [
                    'AdditionalData' => [
                        'Key' => 'MD',
                        'Data' => $Request->MD
                    ]
                ]
            ]
        ];
        return $this->createXML($nodes);
    }

    /**
     * Get ProcReturnCode
     *
     * @return string|null
     */
    protected function getProcReturnCode()
    {
        return isset($this->data->ResponseCode) ? (string) $this->data->ResponseCode : null;
    }

    /**
     * Get Status Detail Text
     *
     * @return string|null
     */
    protected function getStatusDetail()
    {
        $proc_return_code =  $this->getProcReturnCode();

        return $proc_return_code ? (isset($this->codes[$proc_return_code]) ? (string) $this->codes[$proc_return_code] : null) : null;
    }

    /**
     * Create 3D Hash
     *
     * @return string
     */
    public function create3DHash()
    {
        $hash_str = '';
        if ($this->account->model == '3d') {
            $map = [
                $this->account->merchant_id,
                $this->order->id,
                $this->amountFormat($this->order->amount),
                $this->order->name,
                $this->makeSecurityData()
            ];
        }

        return base64_encode(sha1(implode('', $map),"ISO-8859-9"));
    }

    /**
     * Send 3D contents to WebService
     *
     * @param $contents
     * @return $this
     * @throws GuzzleException
     */
    public function send3d($contents, string $url )
    {
        if(!$url){
            $url = $this->url;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/xml', 'Content-length: '.strlen($xml)) );
        curl_setopt($ch, CURLOPT_POST, true); 
        curl_setopt($ch, CURLOPT_HEADER, false); 
        curl_setopt($ch,CURLOPT_URL,$url); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $contents);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        $response = curl_exec($ch);
        curl_close($ch);

        $this->data = $this->XMLStringToObject($response);

        return $this;
    }

    /**
    * Send contents to WebService
    *
    * @param $contents
    * @return $this
    * @throws GuzzleException
    */
    public function send($contents)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/xml', 'Content-length: '.strlen($xml)) );
        curl_setopt($ch, CURLOPT_POST, true); 
        curl_setopt($ch, CURLOPT_HEADER, false); 
        curl_setopt($ch,CURLOPT_URL,$url); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $contents);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
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
        }
        if ($this->order) {
            $this->order->currency = str_pad($this->order->currency, 4, '0', STR_PAD_LEFT);
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

        $model = '3d';
        if (isset($this->account->model) && $this->account->model) {
            $model = $this->account->model; 
        }

        if ($model == '3d') {
            $this->make3DPayment();
        } else {
            throw new UnsupportedPaymentModelException();
        }

        return $this;
    }

    /**
    * Provision 3D
    *
    * @return mixed
    * @throws UnsupportedPaymentModelException
    * @throws GuzzleException
    */
    public function make3DPayment()
    {
        $status             = 'declined';
        $response           = 'Declined';
        $proc_return_code   = '21';
        $Request            = $this->request->get('AuthenticationResponse'); 
        $RequestContent     = urldecode($AuthenticationResponse); 
        try {
            $this->data     = $this->XMLStringToObject($RequestContent);
        }catch (\UnknownError $exception) {
            $this->response = (object) [
                'order_id'              => $this->order->id,
                'rand'                  => $this->order->rand,
                'name'                  => $this->order->name,
                'amount'                => $this->order->amount,
                'installment'           => $this->order->installment,
                'currency'              => $this->order->currency,
                'error_code'            => "99",
                'error_message'         => $response,
                'response'              => $response,
                'status'                => $status
            ];

            return $this;
        }
        if($this->getProcReturnCode() != '00'){
            $this->response = (object) [
                'order_id'              => $this->order->id,
                'rand'                  => $this->order->rand,
                'name'                  => $this->order->name,
                'amount'                => $this->order->amount,
                'installment'           => $this->order->installment,
                'currency'              => $this->order->currency,
                'error_code'            => $this->getProcReturnCode(),
                'error_message'         => $this->data->ResponseMessage,
                'response'              => $this->data->ResponseMessage,
                'status'                => $status
            ];
            return $this;
        }
        $contents = $this->create3DPaymentXML($Request);
        $this->send3d($contents, $this->gateway);
        
        if($this->getProcReturnCode() != '00'){
            $response           = 'Approved';
            $proc_return_code   = $this->data->ResponseCode;
            $status             = 'approved';
        }

        $this->response = (object) [
            'id'                    => isset($obj->order->id) ? $this->printData($obj->order->id) : null,
            'order_id'              => isset($this->order->id) ? $this->printData($this->order->id) : null,
            'card_number'           => isset($this->data->VPosMessage->CardNumber) ? $this->printData($this->data->VPosMessage->CardNumber) : null,
            'response'              => isset($this->data->ResponseMessage) ? $this->printData($this->data->ResponseMessage) : $response,
            'transaction_type'      => $this->type,
            'transaction'           => $this->order->transaction,
            'proc_return_code'      => $this->getProcReturnCode(),
            'code'                  => $this->getProcReturnCode(),
            'status'                => $status,
            'status_detail'         => $this->getStatusDetail(),
            'error_code'            => isset($this->data->ResponseCode) ? $this->printData($this->data->ResponseCode) : null,
            'error_message'         => isset($this->data->ResponseMessage) ? $this->printData($this->data->ResponseMessage) : null,
            'is_enrolled'           => isset($this->data->IsEnrolled) ? $this->printData($this->data->IsEnrolled) : null,
            'is_virtual'            => isset($this->data->IsVirtual) ? $this->printData($this->data->IsVirtual) : null,
            'transaction_time'      => isset($this->data->TransactionTime) ? $this->printData($this->data->TransactionTime) : null,
            'referance_id'          => isset($this->data->ReferenceId) ? $this->printData($this->data->ReferenceId) : null,
            'business_key'          => isset($this->data->BusinessKey) ? $this->printData($this->data->BusinessKey) : null,
            'authentication'        => (string) $this->request->get('AuthenticationResponse'),
            'all'                   => $this->data,
            '3d_all'                => $this->request->all()
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

    }
    /**
    * Get 3d Form 
    *
    * @return array
    */
    public function get3DForm()
    {
        $security_data  = $this->makeSecurityData();
        $hash_data      = $this->make3dHashData($security_data);
        $nodes = [
            'KuveytTurkVPosMessage'   => [
                'APIVersion' =>$this->version,
                'OkUrl' =>$this->order->success_url,
                'FailUrl' =>$this->order->fail_url,
                'HashData' =>$hash_data,
                'MerchantId' =>$this->account->merchant_id,
                'CustomerId' => $this->account->client_id,
                'UserName' => $this->account->username,
                'CardNumber' => $this->card->number,
                'CardExpireDateYear' => $this->card->year,
                'CardExpireDateMonth' => $this->card->month,
                'CardCVV2' => $this->card->cvv,
                'CardHolderName' => $this->card->name,
                'CardType' => $this->getCardType(),
                'TransactionType' => $this->type,
                'InstallmentCount' => $this->order->installment > 1 ? $this->order->installment : '0',
                'Amount' => $this->amountFormat($this->order->amount),
                'DisplayAmount' => $this->amountFormat($this->order->amount),
                'CurrencyCode' => $this->order->currency,
                'MerchantOrderId' => $this->order->id,
                'TransactionSecurity' => '3'
            ]
        ];
        $Xml =  $this->createXML($nodes);
        return $this->send($Xml);
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
