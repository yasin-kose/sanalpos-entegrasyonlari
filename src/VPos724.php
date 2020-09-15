<?php

namespace Ankapix\SanalPos;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Ankapix\SanalPos\Exceptions\UnsupportedPaymentModelException;
use Ankapix\SanalPos\Exceptions\UnsupportedTransactionTypeException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class VPos724
 * @package Ankapix\SanalPos
 */
class VPos724 implements PosInterface
{
    use PosHelpersTrait;

    /**
     * @const string
     */
    public const NAME = 'VPosPay';

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
        '0000'    => "Başarılı",
        '0001'    => "BANKANIZI ARAYIN",
        '0002'    => "BANKANIZI ARAYIN",
        '0003'    => "ÜYE KODU HATALI/TANIMSIZ",
        '0004'    => "KARTA EL KOYUNUZ",
        '0005'    => "İŞLEM ONAYLANMADI.",
        '0006'    => "HATALI İŞLEM",
        '0007'    => "KARTA EL KOYUNUZ",
        '0009'    => "TEKRAR DENEYİNİZ",
        '0010'    => "TEKRAR DENEYİNİZ",
        '0011'    => "TEKRAR DENEYİNİZ",
        '0012'    => "Geçersiz İşlem",
        '0013'    => "Geçersiz İşlem Tutarı",
        '0014'    => "Geçersiz Kart Numarası",
        '0015'    => "MÜŞTERİ YOK/BIN HATALI",
        '0021'    => "İŞLEM ONAYLANMADI",
        '0030'    => "MESAJ FORMATI HATALI (ÜYE İŞYERİ)",
        '0032'    => "DOSYASINA ULAŞILAMADI",
        '0033'    => "SÜRESİ BİTMİŞ/İPTAL KART",
        '0034'    => "SAHTE KART",
        '0036'    => "İŞLEM ONAYLANMADI",
        '0038'    => "ŞİFRE AŞIMI/KARTA EL KOY",
        '0041'    => "KAYIP KART- KARTA EL KOY",
        '0043'    => "ÇALINTI KART-KARTA EL KOY",
        '0051'    => "LIMIT YETERSIZ",
        '0052'    => "HESAP NOYU KONTROL EDİN",
        '0053'    => "HESAP YOK",
        '0054'    => "VADE SONU GEÇMİŞ KART",
        '0055'    => "Hatalı Kart Şifresi",
        '0056'    => "Kart Tanımlı Değil.",
        '0057'    => "KARTIN İŞLEM İZNİ YOK",
        '0058'    => "POS İŞLEM TİPİNE KAPALI",
        '0059'    => "SAHTEKARLIK ŞÜPHESİ",
        '0061'    => "Para çekme tutar limiti aşıldı",
        '0062'    => "YASAKLANMIŞ KART",
        '0063'    => "Güvenlik ihlali",
        '0065'    => "GÜNLÜK İŞLEM ADEDİ LİMİTİ AŞILDI",
        '0075'    => "Şifre Deneme Sayısı Aşıldı",
        '0077'    => "ŞİFRE SCRIPT TALEBİ REDDEDİLDİ",
        '0078'    => "ŞİFRE GÜVENİLİR BULUNMADI",
        '0089'    => "İŞLEM ONAYLANMADI",
        '0091'    => "KARTI VEREN BANKA HİZMET DIŞI",
        '0092'    => "BANKASI BİLİNMİYOR",
        '0093'    => "İŞLEM ONAYLANMADI",
        '0096'    => "BANKASININ SİSTEMİ ARIZALI",
        '0312'    => "KARTIN CVV2 DEĞERİ HATALI",
        '0315'    => "TEKRAR DENEYİNİZ",
        '0320'    => "ÖNPROVİZYON KAPATILAMADI",
        '0323'    => "ÖNPROVİZYON KAPATILAMADI",
        '0357'    => "İŞLEM ONAYLANMADI",
        '0358'    => "Kart Kapalı",
        '0381'    => "RED KARTA EL KOY",
        '0382'    => "SAHTE KART-KARTA EL KOYUNUZ",
        '0501'    => "GEÇERSİZ TAKSİT/İŞLEM TUTARI",
        '0503'    => "KART NUMARASI HATALI",
        '0504'    => "İŞLEM ONAYLANMADI",
        '0540'    => "İade Edilecek İşlemin Orijinali Bulunamadı",
        '0541'    => "Orj. İşlemin tamamı iade edildi",
        '0542'    => "İADE İŞLEMİ GERÇEKLEŞTİRİLEMEZ",
        '0550'    => "İŞLEM YKB POS UNDAN YAPILMALI",
        '0570'    => "YURTDIŞI KART İŞLEM İZNİ YOK",
        '0571'    => "İşyeri Amex İşlem İzni Yok",
        '0572'    => "İşyeri Amex Tanımları Eksik",
        '0574'    => "ÜYE İŞYERİ İŞLEM İZNİ YOK",
        '0575'    => "İŞLEM ONAYLANMADI",
        '0577'    => "TAKSİTLİ İŞLEM İZNİ YOK",
        '0580'    => "HATALI 3D GÜVENLİK BİLGİSİ",
        '0581'    => "ECI veya CAVV bilgisi eksik",
        '0582'    => "HATALI 3D GÜVENLİK BİLGİSİ",
        '0583'    => "TEKRAR DENEYİNİZ",
        '0961'    => "İŞLEM TİPİ GEÇERSİZ",
        '0962'    => "TerminalID Tanımısız",
        '0963'    => "Üye İşyeri Tanımlı Değil",
        '0966'    => "İŞLEM ONAYLANMADI",
        '0971'    => "Eşleşmiş bir işlem iptal edilemez",
        '0972'    => "Para Kodu Geçersiz",
        '0973'    => "İŞLEM ONAYLANMADI",
        '￼0974'    => "İŞLEM ONAYLANMADI",
        '0975'    => "ÜYE İŞYERİ İŞLEM İZNİ YOK",
        '0976'    => "İŞLEM ONAYLANMADI",
        '0978'    => "İŞLEM ONAYLANMADI",
        '0978'    => "KARTIN TAKSİTLİ İŞLEME İZNİ YOK",
        '0980'    => "İŞLEM ONAYLANMADI",
        '0981'    => "EKSİK GÜVENLİK BİLGİSİ",
        '0982'    => "İŞLEM İPTAL DURUMDA. İADE EDİLEMEZ",
        '0983'    => "İade edilemez,iptal",
        '0984'    => "İADE TUTAR HATASI",
        '0985'    => "İŞLEM ONAYLANMADI.",
        '0986'    => "GIB Taksit Hata",
        '0987'    => "İŞLEM ONAYLANMADI.",
        '8484'    => "Birden fazla hata olması durumunda geri dönülür. ResultDetail alanından detayları alınabilir.",
        '1001'    => "Sistem hatası.",
        '1006'    => "Bu transactionId ile daha önce başarılı bir işlem gerçekleştirilmiş",
        '1007'    => "Referans transaction alınamadı",
        '1046'    => "İade işleminde tutar hatalı.",
        '1047'    => "İşlem tutarı geçersizdir.",
        '1049'    => "Geçersiz tutar.",
        '1050'    => "CVV hatalı.",
        '1051'    => "Kredi kartı numarası hatalıdır.",
        '1052'    => "Kredi kartı son kullanma tarihi hatalı.",
        '1054'    => "İşlem numarası hatalıdır.",
        '1059'    => "Yeniden iade denemesi.",
        '1060'    => "Hatalı taksit sayısı.",
        '2200'    => "İş yerinin işlem için gerekli hakkı yok.",
        '2202'    => "İşlem iptal edilemez. ( Batch Kapalı )",
        '5001'    => "İş yeri şifresi yanlış.",
        '5002'    => "İş yeri aktif değil.",
        '1073'    => "Terminal üzerinde aktif olarak bir batch bulunamadı",
        '1074'    => "İşlem henüz sonlanmamış yada referans işlem henüz tamamlanmamış.",
        '1075'    => "Sadakat puan tutarı hatalı",
        '1076'    => "Sadakat puan kodu hatalı",
        '1077'    => "Para kodu hatalı",
        '1078'    => "Geçersiz sipariş numarası",
        '1079'    => "Geçersiz sipariş açıklaması",
        '1080'    => "Sadakat tutarı ve para tutarı gönderilmemiş.",
        '1061'    => "Aynı sipariş numarasıyla daha önceden başarılı işlem yapılmış",
        '1065'    => "Ön provizyon daha önceden kapatılmış",
        '1082'    => "Geçersiz işlem tipi",
        '1083'    => "Referans işlem daha önceden iptal edilmiş.",
        '1084'    => "Geçersiz poaş kart numarası",
        '7777'    => "Banka tarafında gün sonu yapıldığından işlem gerçekleştirilemedi",
        '1087'    => "Yabancı para birimiyle taksitli provizyon kapama işlemi yapılamaz",
        '1088'    => "Önprovizyon iptal edilmiş",
        '1089'    => "Referans işlem yapılmak istenen işlem için uygun değil",
        '1091'    => "Recurring işlemin toplam taksit sayısı hatalı",
        '1092'    => "Recurring işlemin tekrarlama aralığı hatalı",
        '1093'    => "Sadece Satış (Sale) işlemi recurring olarak işaretlenebilir",
        '1006'    => "Bu transactionId ile daha önce başarılı bir işlem gerçekleştirilmiş",
        '1095'    => "Lütfen geçerli bir email adresi giriniz",
        '1096'    => "Lütfen geçerli bir IP adresi giriniz",
        '1097'    => "Lütfen geçerli bir CAVV değeri giriniz",
        '1098'    => "Lütfen geçerli bir ECI değeri giriniz.",
        '1099'    => "Lütfen geçerli bir Kart Sahibi ismi giriniz.",
        '1100'    => "Lütfen geçerli bir brand girişi yapın.",
        '1105'    => "Üye işyeri IP si sistemde tanımlı değil",
        '1102'    => "Recurring işlem aralık tipi hatalı bir değere sahip",
        '1101'    => "Referans transaction reverse edilmiş.",
        '1111'    => "Bu üye işyeri Non Secure işlem yapamaz",
        '6000'    => "Talep mesajı okunamadı. (Mesajda yer alan parametrelerinizin formatlarını kontrol ediniz)"
    ];

    /**
     * Transaction Types
     *
     * @var array
     */
    public $types = [
        'pay'   => 'Sale',
        'pre'   => 'Auth'
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

        $this->url = isset($this->config['urls'][$this->account->env]) ?
            $this->config['urls'][$this->account->env] :
            $this->config['urls']['production'];

        $this->gateway = isset($this->config['urls']['gateway'][$this->account->env]) ?
            $this->config['urls']['gateway'][$this->account->env] :
            $this->config['urls']['gateway']['production'];

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
        return number_format((float) $amount, 2, '.', '');
    }

    /**
     * Create Regular Payment XML
     *
     * @return string
     */
    protected function createRegularPaymentXML()
    {
        $currency = (int) $this->order->currency;
        $nodes = [
            'VposRequest'   => [
                'MerchantId'            => $this->account->merchant_id,
                'Password'              => $this->account->password,
                'TerminalNo'            => $this->account->terminal_id,
                'TransactionType'       => $this->type,
                'TransactionId'         => $this->order->id,
                'OrderId'               => $this->order->id,
                'CurrencyAmount'        => $this->amountFormat($this->order->amount),
                'CurrencyCode'          => $currency,
                'Pan'                   => $this->card->number,
                'Cvv'                   => $this->card->cvv,
                'Expiry'                => '20'.$this->card->year . $this->card->month,
                'ClientIp'              => isset($this->order->ip) ?$this->order->ip:$this->getIpAdress(),
                'CardHoldersName'       => $this->card->name,
                'TransactionDeviceSource'=> '0'
            ]
        ];
        if($this->order->installment > 1){
            $nodes['VposRequest']['NumberOfInstallments'] = $this->order->installment;
        }

        return 'prmstr=' . $this->createXML($nodes);
    }
    /**
    * Card Type
    *
    * @param int $amount
    * @return string
    */
    public function getCardType()
    {
        $cardFirst   = (string) substr($this->card->number, 1) ;
        $cardFirst   = (string) substr($this->card->number, 1) ;
        if((int) substr($this->card->number, 1)==4){
            return 100;
        }else if((int) substr($this->card->number, 2)>=51 and (int) substr($this->card->number, 2)<=55){
            return 200;
        }
        return 300;
    }

    /**
     * Create 3D Payment XML
     * @return string
     */
    protected function create3DPaymentXML()
    {
        $nodes = [
            'VposRequest'   => [
                'MerchantId'            => $this->account->merchant_id,
                'Password'              => $this->account->password,
                'TerminalNo'            => $this->account->terminal_id,
                'Pan'                   => (string) $this->request->get('Pan'),
                'Expiry'                => '20'.$this->request->get('Expiry'),
                'CurrencyAmount'        => $this->amountFormat($this->request->get('PurchAmount')/100),
                'CurrencyCode'          => $this->request->get('PurchCurrency'),
                'TransactionType'       => $this->type,
                'TransactionId'         => (string) $this->order->id,
                'MpiTransactionId'      => (string) $this->request->get('VerifyEnrollmentRequestId'),
                'OrderId'               => (string) $this->order->id,
                'ECI'                   => (string) $this->request->get('Eci'),
                'CAVV'                  => (string) $this->request->get('Cavv'),
                'ClientIp'              => isset($this->order->ip) ?$this->order->ip:$this->getIpAdress(),
                'TransactionDeviceSource'=> '0'
            ]
        ];
        if($this->order->installment > 1){
            $nodes['VposRequest']['NumberOfInstallments'] = (int) $this->request->get('InstallmentCount');
        }
        if($this->request->get('Cvv')){
            $nodes['VposRequest']['Cvv'] = (string) $this->request->get('Cvv');
        }
        return 'prmstr=' .$this->createXML($nodes);
    }

    /**
     * Get ProcReturnCode
     *
     * @return string|null
     */
    protected function getProcReturnCode()
    {
        return isset($this->data->ResultCode) ? (string) $this->data->ResultCode : null;
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
     * Regular Payment
     *
     * @return $this
     * @throws GuzzleException
     */
    public function makeRegularPayment()
    {
        $contents = $this->createRegularPaymentXML();
        $this->send($contents);

        $status = 'declined';
        if ($this->getProcReturnCode() == '0000') {
            $status = 'approved';
        }

        $this->response = (object) [
            'response'          => isset($this->data->ResultDetail) ? $this->printData($this->data->ResultDetail) : null,
            'transaction_type'  => $this->type,
            'transaction'       => $this->order->transaction,
            'proc_return_code'  => $this->getProcReturnCode(),
            'code'              => $this->getProcReturnCode(),
            'status'            => $status,
            'status_detail'     => $this->getStatusDetail(),
            'error_code'        => isset($this->data->ResultCode) ? $this->printData($this->data->ResultCode) : null,
            'error_message'     => isset($this->data->ResultDetail) ? $this->printData($this->data->ResultDetail) : null,
            'host_date'         => isset($this->data->HostDate) ? $this->printData($this->data->HostDate) : null,
            'rnd'               => isset($this->data->Rrn) ? $this->printData($this->data->Rrn) : null,
            'auth_code'         => isset($this->data->AuthCode) ? $this->printData($this->data->AuthCode) : null,
            'extra'             => isset($this->data->Extra) ? $this->data->Extra : null,
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
        $response = 'Declined';
        $proc_return_code = '0005';
        $transaction_security = 'MPI fallback';
        if (in_array($this->request->get('Status'), ['Y', 'N'])) {
            if ($this->request->get('Status') == 'Y') {
                $transaction_security = 'Full 3D Secure';
            } elseif ($this->request->get('Status')=='N') {
                $transaction_security = 'Half 3D Secure';
            }
            $contents = $this->create3DPaymentXML();
            $this->send($contents);
            if ($this->data->ResultCode == '0000') {
                $response = 'Approved';
                $proc_return_code = $this->data->ResultCode;
                $status = 'approved';
            }
        }

        $this->response = (object) [
            'id'                    => isset($this->order->id) ? $this->printData($this->order->id) : null,
            'order_id'              => isset($this->order->id) ? $this->printData($this->order->id) : null,
            'trans_id'              => isset($this->data->TransactionId) ? $this->printData($this->data->TransactionId) : null,
            'response'              => $response,
            'transaction_type'      => $this->type,
            'transaction'           => $this->order->transaction,
            'transaction_security'  => $transaction_security,
            'proc_return_code'      => $proc_return_code,
            'batch_num'             => isset($this->data->BatchNo) ? $this->printData($this->data->BatchNo) : null,
            'code'                  => $proc_return_code,
            'status'                => $status,
            'status_detail'         => $this->getStatusDetail(),
            'error_code'            => isset($this->data->ResultCode) ? $this->printData($this->data->ResultCode) : null,
            'error_message'         => isset($this->data->ResultDetail) ? $this->printData($this->data->ResultDetail) : null,
            'auth_code'             => isset($this->data->AuthCode) ? $this->printData($this->data->AuthCode) : null,
            'host_date'             => isset($this->data->HostDate) ? $this->printData($this->data->HostDate) : null,
            'rrn'                   => isset($this->data->Rrn) ? $this->printData($this->data->Rrn) : null,
            'currency_amount'       => isset($this->data->CurrencyAmount) ? $this->printData($this->data->CurrencyAmount) : null,
            'currency_code'         => isset($this->data->CurrencyCode) ? $this->printData($this->data->CurrencyCode) : null,
            'threed_secure_type'    => isset($this->data->ThreeDSecureType) ? $this->printData($this->data->ThreeDSecureType) : null,
            'gained_point'          => isset($this->data->GainedPoint) ? $this->printData($this->data->GainedPoint) : null,
            'total_point'           => isset($this->data->TotalPoint) ? $this->printData($this->data->TotalPoint) : null,
            'batch_no'              => isset($this->data->BatchNo) ? $this->printData($this->data->BatchNo) : null,
            'tl_amount'             => isset($this->data->TLAmount) ? $this->printData($this->data->TLAmount) : null,
            'xid'                   => isset($this->data->Xid) ? $this->printData($this->data->Xid) : null,
            'extra'                 => null,
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
        $fields     = "";
        $nodes = [
            'Pan'                   => $this->card->number,
            'ExpiryDate'            => $this->card->year . $this->card->month,
            'Cvv'                   => $this->card->cvv,
            'PurchaseAmount'        => $this->amountFormat($this->order->amount),
            'CurrencyAmount'        => $this->amountFormat($this->order->amount),
            'Currency'              => $this->order->currency,
            'BrandName'             => $this->getCardType(),
            'VerifyEnrollmentRequestId'=> $this->order->id,
            'MerchantId'            => $this->account->merchant_id,
            'MerchantPassword'      => $this->account->password,
            'TerminalNo'            => $this->account->terminal_id,
            'SuccessUrl'            => $this->order->success_url,
            'FailureUrl'            => $this->order->fail_url,
            'ClientIp'              => isset($this->order->ip) ?$this->order->ip:$this->getIpAdress(),
            'TransactionType'       => $this->type
        ];
        if($this->order->installment > 1){
            $nodes['NumberOfInstallments'] = $this->order->installment;
        }
        foreach($nodes as $key=>$value){
            if($fields){
                $fields .="&";
            }
            $fields .=$key."=".$value;
        }
        return $fields;
    }
    /**
    * Get 3d Form 
    *
    * @return array
    */
    public function get3DForm()
    {
       $this->send3d($this->get3DFormData(), $this->gateway);
       if ($this->data->Message->VERes->Status != 'Y') {
            return isset($this->data->ResultDetail->ErrorMessage) ? $this->printData($this->data->ResultDetail->ErrorMessage):"İşlem Başarısız, Lütfen tekrar deneyiniz.";
       }
        $return = '<form method="post" action="'.$this->data->Message->VERes->ACSUrl.'"  name="3dForm" class="redirect-form" >';
        $return .= '<input type="hidden" name="PaReq" value="'.$this->data->Message->VERes->PaReq.'">';
        $return .= '<input type="hidden" name="TermUrl" value="'.$this->data->Message->VERes->TermUrl.'">';
        $return .= '<input type="hidden" name="MD" value="'.$this->data->Message->VERes->MD.'">';
        $return .= '<div class="text-center">Yönlendiriliyorsunuz...</div> <hr>
            <div class="form-group text-center">
                <button type="submit" class="btn btn-lg btn-block btn-success">Ödeme Doğrulaması Yap</button>
            </div>
            <SCRIPT LANGUAGE="Javascript">document.3dForm.submit();</SCRIPT>
        </form>';
        return $return;
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
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $contents);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type' => 'application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $this->data = $this->XMLStringToObject($response);

        return $this;
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
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $contents);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type' => 'application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $this->data = $this->XMLStringToObject($response);

        return $this;
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
    * Installment List
    *
    * @return $this
    */
    public function getInstallmentList(array $meta)
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
