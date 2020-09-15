<?php

/**
 *
 * Bu yardımcı sınıf ile Paynet API'yi PHP projelerinizde kolayca kullanabilirsiniz
 * 
 * */
class PaynetClient
{
	private $ApiKey;
	private $PaynetUrl;
	private $json_result;
	
	//adreslerin sonu slaş ile bitmeli
	const testUrl = 'https://pts-api.paynet.com.tr/';
	const liveUrl = 'https://api.paynet.com.tr/';
	
	/**
	 * Yapıcı metod, secret keyi girmek için kullanılıyor
	 * @param string $ApiKey
	 * @param bool $isLive Canlı için true, test için false girilmeli
	 */
	public function __construct($ApiKey, $isLive = false)
	{
		$this->ApiKey = $ApiKey;
		
		if($isLive == false)
			$this->PaynetUrl = self::testUrl;
		else
			$this->PaynetUrl = self::liveUrl;
	}

	/**
	 * Paynet apisinden sorgu yapıp cevabı JSON olarak alan metod
	 * @param string $adres_eki 
	 * @param stdClass $data 
	 * @return mixed sunucudan alınan JSON
	 */
    private function LoadJson($adres_eki, $data)
    {
		$params = array_filter((array) $data);
		$options = array(
						'http' => array(
						'header'  =>"Accept: application/json; charset=UTF-8\r\n".
									"Content-type: application/json; charset=UTF-8\r\n".
									"Authorization: Basic ".$this->ApiKey,
						'method'  => 'POST',
						'content' => json_encode($params),
						'ignore_errors' => true
					),
		);
		
	    if (!function_exists('stream_context_create'))
	    {
	   		die("Sunucunuz stream_context_create() fonksiyonunu desteklememektedir...");
	    }
		
		$context  = stream_context_create($options);
		$sonuc = json_decode(@file_get_contents($this->PaynetUrl.$adres_eki, false, $context));

		
		if($sonuc == null)
		{
			Throw new PaynetException("Paynet sunucusuna bağlanılamadı...");
		}
		else
		{
			return $sonuc;
		}		
    }
	
	/**
	* Karttan para çekim işlemi yapan metod
	* @param Three3DPaymentParameters $param
	* @return There3DPaymentResponse
	*/
	public function There3DPaymentPost(Three3DPaymentParameters $param)
	{
		$this->json_result = $this->LoadJson('v2/transaction/tds_initial',$param);
		$sonuc = new There3DPaymentResponse();
		$sonuc->fillFromJson($this->json_result);
		return $sonuc;
	}
	
	/**
	* Karttan para çekim işlemi yapan metod
	* @param PaymentParameters $param
	* @return ChargeResponse
	*/
	public function PaymentPost(PaymentParameters $param)
	{
		$this->json_result = $this->LoadJson('v2/transaction/payment',$param);
		$sonuc = new ChargeResponse();
		$sonuc->fillFromJson($this->json_result);
		return $sonuc;
	}
	
	/**
	 * Karttan çekim işlemini yapan metod
	 * @param ChargeParameters $param
	 * @return ChargeResponse
	 */
	public function ChargePost(ChargeParameters $param)
	{
		$this->json_result = $this->LoadJson('v2/transaction/tds_charge',$param);
        $sonuc = new ChargeResponse();
		$sonuc->fillFromJson($this->json_result);
		return $sonuc;
	}
	
	/**
	 * 
	 * @param CheckTransactionParameters $param
	 * @return CheckTransactionResponse
	 */
	public function CheckTransaction(CheckTransactionParameters $param)
	{
		$this->json_result = $this->LoadJson("v1/transaction/check", $param);
		$this->json_result = $this->json_result->Data[0];
		$sonuc = new CheckTransactionResponse();
		$sonuc->fillFromJson($this->json_result);
		return $sonuc;
	}
	
	/**
	 * 
	 * @param TransactionDetailParameters $param
	 * @return TransactionDetailResponse
	 */
	public function GetTransactionDetail(TransactionDetailParameters $param)
	{
		$this->json_result = $this->LoadJson('v1/transaction/detail',$param);
		$sonuc = new TransactionDetailResponse();
		$sonuc->FillFromJson($this->json_result);
		return $sonuc;
	}
	
	public function ListTransaction(TransactionListParameters $param)
	{
		$this->json_result = $this->LoadJson('v1/transaction/list', $param);
		$sonuc = new TransactionListResponse();
		$sonuc->fillFromJson($this->json_result);
		return $sonuc;
	}
	
	/**
	 * Mail veya sms ile ödeme seçemekleri için link üreten servis...
	 * @param MailOrderParameters $params
	 * @return MailOrderResult
	 */
	public function CreateMailOrder(MailOrderParameters $params)
	{
		$this->json_result = $this->LoadJson('v1/mailorder/create', $params);
		$sonuc = new MailOrderResult();
		$sonuc->fillFromJson($this->json_result);
		return $sonuc;
	}
	
	/**
	 * Oran tablosunu getiren servis
	 * @param RatioParameters $params
	 * @return RatioResponse
	 */
	public function GetRatios(RatioParameters $params)
	{
		$this->json_result = $this->LoadJson("v1/ratio/Get", $params);
		$sonuc = new RatioResponse();
		$sonuc->fillFromJson($this->json_result);
		return $sonuc;
	}
	
	/**
	 * İşlemi işaretleyen servis
	 * @param MarkTransferParameters $params
	 * @return boolean
	 */
	public function MarkTransferred(MarkTransferParameters $params)
	{
		$this->json_result = $this->LoadJson("v1/transaction/mark_transferred", $params);
		return $this->json_result->code == "1";
	}
	
	/**
	 * 
	 * @param ReversedRequestParameters $params
	 * @return ReversedRequestResponse
	 */
	public function ReversedRequest(ReversedRequestParameters $params)
	{
		$this->json_result = $this->LoadJson("v1/transaction/reversed_request", $params);
		$sonuc = new ReversedRequestResponse();
		$sonuc->fillFromJson($this->json_result);
		return $sonuc;
	}
	
	/**
	* 
	* @param CaptureRequestParameters $params
	* @return CaptureRequestResponse
	*/
	public function CaptureRequest(CaptureRequestParameters $params)
	{
		$this->json_result = $this->LoadJson("v1/transaction/capture_reversal", $params);
		$sonuc = new CaptureRequestResponse();
		$sonuc->fillFromJson($this->json_result);
		return $sonuc;
	}
	
	/**
	 * 
	 * @param AutologinParameters $params
	 * @return AutologinResult
	 */
	public function AutoLogin(AutologinParameters $params)
	{
		$this->json_result = $this->LoadJson("v1/agent/autologin", $params);
		$sonuc = new AutologinResult();
		$sonuc->fillFromJson($this->json_result);
		return $sonuc;
	}
	
	/**
	 * Bir işlem sonucunda sunucudan alınan JSON nesnesini table olarak ekrana yazdırır
	 */
	public function PrintResult()
	{
		if($this->json_result!=null)
		{
			
			echo "<hr>SUNUCU YANITI<br><table>";
			foreach($this->json_result as $property => $value)
			{
				echo "<tr>\r\n";
					echo "<td>".$property."</td>\r\n";
					echo "<td>".$value."</td>\r\n";
				echo "</tr>\r\n";
			}
			echo "<table>";
		}
		else
			echo "Sonuç değişkeni boş";
	}
	
	/**
	 *Bir servisin cevap olarak gönderdiği Json nesnesini düz yazı olarak ekrana yazar. 
	 */
	public function PrintJson()
	{
		echo json_encode($this->json_result);
	}
}

/**
 * Suncudan dönen cevaplar için base sınıfı
 * @author proje
 */
class Result extends fillFromJson_
{
	public $object_name;
	public $code;
	public $message;
}

/**
 * FillFromJson metodunu diğer sınıflara eklemek için 
 * @author proje
 *
 */
class fillFromJson_
{
	/**
	 * Json olarak alınan bilgileri oluşturulmuş nesneye yükler.
	 * @param jsonObject $json
	 */
	function fillFromJson($json)
	{
		foreach($this as $property=>$value)
		{
			if(isset($json->$property))
			{
				//Eğer property bir dizi ise ve ilk elemanı sınıf ismi olarak tanımlanmışsa
				if(is_array($this->$property))
				{
					$array = $this->$property;
					if(count($array) && is_string($array[0]))
					{
						//Çocuk sınıfın adını döndür ve diziyi temizle
						$child_class_name = array_pop($this->$property);
	
						//Json'daki herbir dizi elemenı için ayrı nesne oluşturulacak
						foreach($json->$property as $data)
						{
							//Çocuk nesneyi oluştur ve içeriğini doldur
							$child_obj = new $child_class_name;
							$child_obj->fillFromJson($data);
							array_push($this->$property, $child_obj);
						}
					}
				}
				//dizi değilse değer ataması yapmak yeterli
				else
				{
					$this->$property = $json->$property;
				}
			}
		}
	}
}

/**
* Three3DPayment servisi için request parametreleri
* @author proje
*
*/
class Three3DPaymentParameters
{
	public $amount=0;
	public $reference_no="";
	public $domain = "";
	public $card_holder;
	public $pan;
	public $month;
	public $year;
	public $cvc;
	public $card_holder_phone;
	public $card_holder_mail;
	public $description;
	public $instalment;
	public $add_commission=FALSE;
	public $transaction_type=1;
	public $ratio_code;
	public $pos_type=5;
	public $agent_id;
}

/**
* There3DPaymentPost metodundan dönecek sonuç nesne için
* @author proje
*
*/
class There3DPaymentResponse extends Result
{
	public $token_id;
	public $session_id;
	public $post_url;
	public $html_content;
	public $object_name;
	public $code;
	public $message;
}

/**
* Payment servisi için request parametreleri
* @author proje
*
*/
class PaymentParameters
{
	public $amount=0;
	public $reference_no="";
	public $domain = "";
	public $card_holder;
	public $pan;
	public $month;
	public $year;
	public $cvc;
	public $card_holder_phone;
	public $card_holder_mail;
	public $description;
	public $instalment;
	public $add_commission=FALSE;
	public $transaction_type=1;
	public $ratio_code;
	public $pos_type=5;
	public $agent_id;
	public $return_url;
}


/**
 * Charge servisi için request parametreleri
 * @author proje
 *
 */
class ChargeParameters
{
	public $session_id;
	public $token_id;
	public $reference_no = "";
	public $transaction_type = 1;
}

/**
 * ChargePost metodundan dönecek sonuç nesne için
 * @author proje
 *
 */
class ChargeResponse extends Result
{
	public $xact_id;
	public $xact_date;
	public $transaction_type;
	public $pos_type;
	public $is_tds;
	public $agent_id;
	public $user_id;
	public $email;
	public $phone;
	public $bank_id;
	public $instalment;
	public $card_no_masked;
	public $card_holder;
	public $amount;
	public $net_amount;
	public $comission;
	public $comission_tax;
	public $currency;
	public $authorization_code;
	public $reference_code;
	public $order_id;
	public $is_succeed;
	public $paynet_error_id;
	public $paynet_error_message;
	public $bank_error_id;
	public $bank_error_message;
	public $bank_error_short_desc;
	public $bank_error_long_desc;
	public $agent_reference_no;
	public $ratio;
}

/**
 * GetRatios() metodunun parametreleri
 * @author proje
 * 
 */
class RatioParameters
{
	public $pos_type = 5;
	public $bin;
	public $amount;
	public $agent_id;
	public $ratio_code;
	public $addcomission_to_amount = false;
}

/**
 * GetRatios() metodunun dönüş sınıfı
 * @author proje
 *
 */
//Ratio servisinin cevabı
class RatioResponse extends Result
{
    public $data = array('Banks');//banka  listesi
}

/**
 * Banks sınıfının ratio dizinin elemanları
 * @author proje
 *
 */

class Ratios extends fillFromJson_
{
    public $ratio;
    public $instalment_key;
    public $instalment;
    public $instalment_amount;
    public $total_net_amount;
    public $total_amount;
    public $commision;
    public $commision_tax;
    public $desc;
}

/**
 * RatioResponse sınıfındaki data dizisinin elemanları
 * @author proje
 *
 */
class Banks extends fillFromJson_
{
    public $bank_id;
    public $bank_logo;
    public $bank_name;
    public $ratio = array('Ratios');
}

/**
 * CheckTransaction metoduna gönderilecek paramatre
 * @author proje
 * 
 */
class CheckTransactionParameters
{
	public $xact_id;
	public $reference_no;
}

/**
 * CheckTransaction metodunun sonucunda dönecek sınıf
 * @author proje
 *
 */
class CheckTransactionResponse extends Result
{
	public $xact_id;
	public $xact_date;
	public $transaction_type;
	public $pos_type;
	public $agent_id;
	public $is_tds;
	public $bank_id;
	public $instalment;
	public $card_no;
	public $card_holder;
	public $card_type;
	public $ratio;
	public $amount;
	public $netAmount;
	public $comission;
	public $comission_tax;
	public $currency;
	public $authorization_code;
	public $reference_code;
	public $order_id;
	public $is_succeed;
	public $xact_transaction_id;
	public $email;
	public $phone;
	public $note;
	public $agent_reference;
}

/**
 * GetTransactionDetail metodunun parametreleri
 * @author proje
 *
 */
class TransactionDetailParameters
{
	public $xact_id;
	public $reference_no;
}

/**
 * GetTransactionDetail metodunun dönüşü
 * @author proje
 *
 */
class TransactionDetailResponse extends Result
{
	public $Data = array('TransactionDetail');
}

/**
 * GetTransactionDetail metodunun dönüşündeki satırlar
 * @author proje
 *
 */
class TransactionDetail extends Result
{
	public $xact_id;
	public $xact_date;
	public $transaction_type;
	public $pos_type;
	public $agent_id;
	public $is_tds;
	public $bank_id;
	public $instalment;
	public $card_no;
	public $card_holder;
	public $card_type;
	public $ratio;
	public $amount;
	public $netAmount;
	public $comission;
	public $comission_tax;
	public $currency;
	public $authorization_code;
	public $reference_code;
	public $order_id;
	public $is_succeed;
	public $reversed;
	public $reversed_xact_id;
	public $xact_transaction_id;
	public $email;
	public $phone;
	public $note;
	public $agent_reference;
	public $company_amount;
	public $company_commission;
	public $company_commission_with_tax;
	public $company_net_amount;
	public $agent_amount;
	public $agent_commission;
	public $agent_commission_with_tax;
	public $agent_net_amount;
	public $company_cost_ratio;
	public $company_pay_ratio;
	public $xact_type_desc;
	public $bank_name;
	public $payment_string;
	public $pos_type_desc;
	public $agent_name;
	public $company_name;
	public $instalment_text;
	public $ipaddress;
	public $client_id;
	
}

/**
 * ListTransaction metodunun parametreleri
 * @author proje
 *
 */
class TransactionListParameters
{
	public $agent_id;
	public $bank_id;
	public $datab;
	public $datbi;
	public $show_unsucceed;
	public $limit;
	public $ending_before;
	public $starting_after;
	
	public function __construct()
	{
		$this->agent_id = "";
		$this->bank_id = "";
		$this->show_unsucceed = true;
		$this->limit = 1000;
		$this->ending_before = 0;
		$this->starting_after = 0;
		$this->datab = date('Y-m-d', strtotime('-10 days', strtotime(date("Y-m-d"))));
		$this->datbi = date('Y-m-d', strtotime('+1 days', strtotime(date("Y-m-d"))));
	}
	
}

/**
 * ListTransaction sonucunda dönecek nesne
 * @author proje
 *
 */
class TransactionListResponse extends fillFromJson_
{
	public $companyCode;
	public $companyName;
	
	public $total;
	public $total_count;
	
	public $limit;
	public $ending_before;
	public $starting_after;
	public $object_name;
	public $has_more;

	public $Data = array('TransactionListData');
}

/**
 * TransactionListResponse'daki data dizisinin satırlar
 * @author proje
 *
 */
class TransactionListData extends fillFromJson_
{
	public $companyCode;
	public $companyName;
	public $agent_id;
	public $agent_referans_no;
	public $agent_name;
	public $xact_id;
	public $xact_date;
	public $is_tds;
	public $bank_id;
	public $bank_name;
	public $card_no;
	public $card_holder;
	public $card_type;
	public $card_type_name;
	public $authorization_code;
	public $reference_code;
	public $order_id;
	public $postype_desc;
	public $xact_type;
	public $xacttype_desc;
	public $fiscal_period_id;
	public $sector_id;
	public $sectorid_desc;
	public $merchant_id;
	public $channel_name;
	public $ipaddress;
	public $client_id;
	public $xact_transaction_id;
	public $terminal_id;

	public $is_succeed;
	public $reversed;
	public $is_reconcile;
	public $is_payup;
	public $is_onchargeback;
	public $is_transferred;

	public $reversed_xact_id;
	public $channel_id;
	public $pos_type;
	public $instalment;

	public $amount;
	public $net_amount;
	public $comission;
	public $comission_tax;
	public $currency;
	public $ratio;

	public $user_id;
	public $xact_time;
	public $xact_note;
	public $xact_agent_reference;
	public $company_pay_ratio;

	public $company_cost_ratio;

	public $ana_firma_brut_alacak;
	public $ana_firma_komisyonu;
	public $ana_firma_komisyonu_kdv_dahil;
	public $ana_firma_odenecek_net_tutar;
	public $bayi_brut_alacak;
	public $bayi_komisyonu;
	public $bayi_komisyonu_kdv_dahil;
	public $bayiye_odenecek_net_tutar;

	public $cp_mfi_vdate;
	public $cp_mfi_vdate_day;
	public $ap_mfi_vdate;
	public $ap_mfi_vdate_day;

}

/**
 * MarkTransferred() metodu için parametreler
 * @author proje
 *
 */
class MarkTransferParameters
{
	public $xact_id;
	public $document_no;
	public $amount;
	public $currency;
	public $exchange_rate;
}

/**
 * CreateMailOrder() metodunun parametreleri
 * @author proje
 *
 */

class MailOrderParameters
{
	public $pos_type;
	public $addcomission_to_amount;
	public $agent_id;
	public $name_surname;
	public $user_name;
	public $amount;
	public $email;
	public $send_mail;
	public $phone;
	public $send_sms;
	public $expire_date;
	public $note;
	public $agent_note;
	public $reference_no;
	public $succeed_url;
	public $error_url;
	public $confirmation_url;
	public $send_confirmation_mail;
	public $multi_payment;
	
	public function __construct()
	{
		$this->pos_type = 5;
		$this->addcomission_to_amount = false;
		$this->multi_payment = true;
		$this->send_confirmation_email = true;
		$this->send_mail = false;
		$this->send_sms = false;
		$this->phone = "";
		$this->email = "";
		$this->succeed_url = "";
		$this->error_url = "";
		$this->confirmation_url = "";
		$this->expire_date = 24;
	}
}

/**
 * CreateMailOrder() metodunun sonucu
 * @author proje
 *
 */
class MailOrderResult extends Result
{
	public $url;
}

/**
 * ReversedRequest() için parametreler
 * @author agitk
 *
 */
class ReversedRequestParameters
{
	public $xact_id;
	public $amount;
	public $succeedUrl;
}
/**
* CaptureRequest() için parametreler
* @author agitk
*
*/
class CaptureRequestParameters
{
	public $xact_id;
}
/**
 * ReversedRequest() metodundan dönecek nesne, bu nesne Result ile aynı içerikli olduğu için (şimdilik) ekleme yapmaya gerek yok
 * @author proje
 *
 */
class ReversedRequestResponse extends Result
{
}

/**
* CaptureRequest() metodundan dönecek nesne
* @author proje
*
*/
class CaptureRequestResponse extends Result
{
	public $xact_id;
	public $xact_date;
	public $transaction_type;
	public $pos_type;
	public $is_tds;
	public $agent_id;
	public $user_id;
	public $email;
	public $phone;
	public $bank_id;
	public $instalment;
	public $card_no_masked;
	public $card_holder;
	public $amount;
	public $net_amount;
	public $comission;
	public $comission_tax;
	public $currency;
	public $authorization_code;
	public $reference_code;
	public $order_id;
	public $is_succeed;
	public $paynet_error_id;
	public $paynet_error_message;
	public $bank_error_id;
	public $bank_error_message;
	public $bank_error_short_desc;
	public $bank_error_long_desc;
	public $agent_reference_no;
	public $ratio;
	public $code;
	public $message;
}
/**
 * Autologin() metodunun parametresi
 * @author proje
 *
 */
class AutologinParameters
{
	public $userName;
	public $agentID;
}

/**
 * Autologin() metodunun dönüş nesnesi
 * @author proje
 *
 */
class AutologinResult extends Result
{
	public $url;
}

/**
 * Hata oluştuğunda dönecek nesne
 * @author proje
 *
 */
class PaynetException extends Exception
{
}

?>