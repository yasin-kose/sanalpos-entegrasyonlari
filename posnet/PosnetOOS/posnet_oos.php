<?php
/*
 * posnet_oos.php
 *
 */

if (!defined('POSNET_MODULES_DIR')) {
    define('POSNET_MODULES_DIR', dirname(__FILE__).'/..');
}

define('INTERVALTIMEFORPOSNETTRAN', 2 * 60 * 60 * 1000);

// Include posnet xml library
require_once 'posnet_oos_xml.php';
// Include posnet http library
require_once POSNET_MODULES_DIR.'/PosnetHTTP/posnet_http.php';
// Include posnet encryption library
require_once POSNET_MODULES_DIR.'/PosnetENC/posnet_enc.php';

class PosnetOOS
{
    /**
     * reference for MerchantInfo Class.
     */
    public $merchantInfo;

    /**
     * reference for PosnetOOSResponse Class.
     */
    public $posnetOOSResponse;

    /**
     * temporary reference for request XML data.
     */
    public $strRequestXMLData;

    /**
     * temporary reference for response XML data.
     */
    public $strResponseXMLData;

    /**
     * reference for PosnetResponseXML Array.
     */
    public $arrayPosnetResponseXML;

    /**
     * xml wenb service url.
     */
    public $url = '';

    /**
     * world point amount.
     */
    public $wpAmount = '0';

    /**
     * Used for debugging.
     */
    public $debug = 0;

    /**
     * Used for timezone setting.
     */
    public $GMT_TUR = 2;

    /**
     * Constructor.
     *
     * @param $posnetId
     * @param $mid
     * @param $tid
     * @param $username
     * @param $password
     * @param string $key
     */
    public function __construct(
        $posnetId,
        $mid,
        $tid,
        $username,
        $password,
        $key = '10,10,10,10,10,10,10,10',
        $Xid='',
        $Amount=0

    ) {
        $this->merchantInfo = new MerchantInfo();
        $this->strRequestXMLData = '';
        $this->strResponseXMLData = '';

        $this->SetPosnetID($posnetId);
        $this->SetMid($mid);
        $this->SetTid($tid);

        $this->SetUsername($username);
        if($Xid){
            $firstHash = $this->hashString($key . ";" . $tid);
            $MAC = $this->hashString($Xid . ";" . $Amount . ";TL;" . $mid . ";". $firstHash); 
            $this->SetMac($MAC);
        }
        //$this->SetUsername('USERNAME');
        $this->SetPassword($password);
        //$this->SetPassword('PASSWORD');
        $this->SetKey($key);
    }


    public function hashString($originalString){
        return base64_encode(hash('sha256',$originalString,true));
    } 

    /**
     * This function is used to set debug level.
     *
     * @param string $debuglevel
     */
    public function SetDebugLevel($debuglevel)
    {
        if ($debuglevel > 0) {
            $this->debug = 1;
        }
    }

    /**
     * Get & Set OOS Response parameters from XML array according to reqcode.
     *
     * @param string $reqcode
     */
    public function SetResponseParameters($reqcode)
    {
        if (array_key_exists('posnetResponse', $this->arrayPosnetResponseXML)) {
            if (array_key_exists('approved', $this->arrayPosnetResponseXML['posnetResponse'])) {
                $this->posnetOOSResponse->approved = $this->arrayPosnetResponseXML['posnetResponse']['approved'];
            }
            if (array_key_exists('respCode', $this->arrayPosnetResponseXML['posnetResponse'])) {
                $this->posnetOOSResponse->errorcode = $this->arrayPosnetResponseXML['posnetResponse']['respCode'];
            }
            if (array_key_exists('respText', $this->arrayPosnetResponseXML['posnetResponse'])) {
                $this->posnetOOSResponse->errormessage = $this->arrayPosnetResponseXML['posnetResponse']['respText'];
            }

            //OOS RequestDataResponse
            if ($reqcode == '0') {
                if (array_key_exists('oosRequestDataResponse', $this->arrayPosnetResponseXML['posnetResponse'])) {
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['oosRequestDataResponse']['data1'])) {
                        $this->posnetOOSResponse->data1 = $this->arrayPosnetResponseXML['posnetResponse']['oosRequestDataResponse']['data1'];
                    }
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['oosRequestDataResponse']['data2'])) {
                        $this->posnetOOSResponse->data2 = $this->arrayPosnetResponseXML['posnetResponse']['oosRequestDataResponse']['data2'];
                    }
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['oosRequestDataResponse']['sign'])) {
                        $this->posnetOOSResponse->sign = $this->arrayPosnetResponseXML['posnetResponse']['oosRequestDataResponse']['sign'];
                    }
                }
            } //OOS ResolveMerchantDataResponse
            elseif ($reqcode == '1') {
                if (array_key_exists('oosResolveMerchantDataResponse', $this->arrayPosnetResponseXML['posnetResponse'])) {
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['xid'])) {
                        $this->posnetOOSResponse->xid = $this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['xid'];
                    }
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['amount'])) {
                        $this->posnetOOSResponse->amount = $this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['amount'];
                    }
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['currency'])) {
                        $this->posnetOOSResponse->currency = $this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['currency'];
                    }
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['installment'])) {
                        $this->posnetOOSResponse->instcount = $this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['installment'];
                    }
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['point'])) {
                        $this->posnetOOSResponse->totalPoint = $this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['point'];
                    }
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['pointAmount'])) {
                        $this->posnetOOSResponse->totalPointAmount = $this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['pointAmount'];
                    }
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['txStatus'])) {
                        $this->posnetOOSResponse->tds_tx_status = $this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['txStatus'];
                    }
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['mdStatus'])) {
                        $this->posnetOOSResponse->tds_md_status = $this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['mdStatus'];
                    }
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['mdErrorMessage'])) {
                        $this->posnetOOSResponse->tds_md_errormessage = $this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['mdErrorMessage'];
                    }
                }
            } //OOS Transaction Response Data
            elseif ($reqcode == '2') {
                if (array_key_exists('authCode', $this->arrayPosnetResponseXML['posnetResponse'])) {
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['authCode'])) {
                        $this->posnetOOSResponse->authcode = $this->arrayPosnetResponseXML['posnetResponse']['authCode'];
                    }
                }
                if (array_key_exists('hostlogkey', $this->arrayPosnetResponseXML['posnetResponse'])) {
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['hostlogkey'])) {
                        $this->posnetOOSResponse->hostlogkey = $this->arrayPosnetResponseXML['posnetResponse']['hostlogkey'];
                    }
                }
                //Point Info
                if (array_key_exists('pointInfo', $this->arrayPosnetResponseXML['posnetResponse'])) {
                    if (array_key_exists('point', $this->arrayPosnetResponseXML['posnetResponse']['pointInfo'])) {
                        if (isset($this->arrayPosnetResponseXML['posnetResponse']['pointInfo']['point'])) {
                            $this->posnetOOSResponse->point = $this->arrayPosnetResponseXML['posnetResponse']['pointInfo']['point'];
                        }
                    }
                    if (array_key_exists('pointAmount', $this->arrayPosnetResponseXML['posnetResponse']['pointInfo'])) {
                        if (isset($this->arrayPosnetResponseXML['posnetResponse']['pointInfo']['pointAmount'])) {
                            $this->posnetOOSResponse->pointAmount = $this->arrayPosnetResponseXML['posnetResponse']['pointInfo']['pointAmount'];
                        }
                    }
                    if (array_key_exists('totalPoint', $this->arrayPosnetResponseXML['posnetResponse']['pointInfo'])) {
                        if (isset($this->arrayPosnetResponseXML['posnetResponse']['pointInfo']['totalPoint'])) {
                            $this->posnetOOSResponse->totalPoint = $this->arrayPosnetResponseXML['posnetResponse']['pointInfo']['totalPoint'];
                        }
                    }
                    if (array_key_exists('totalPointAmount', $this->arrayPosnetResponseXML['posnetResponse']['pointInfo'])) {
                        if (isset($this->arrayPosnetResponseXML['posnetResponse']['pointInfo']['totalPointAmount'])) {
                            $this->posnetOOSResponse->totalPointAmount = $this->arrayPosnetResponseXML['posnetResponse']['pointInfo']['totalPointAmount'];
                        }
                    }
                }
                //Instalment Info
                if (array_key_exists('instInfo', $this->arrayPosnetResponseXML['posnetResponse'])) {
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['instInfo']['inst1'])) {
                        $this->posnetOOSResponse->instcount = $this->arrayPosnetResponseXML['posnetResponse']['instInfo']['inst1'];
                    }
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['instInfo']['amnt1'])) {
                        $this->posnetOOSResponse->instamount = $this->arrayPosnetResponseXML['posnetResponse']['instInfo']['amnt1'];
                    }
                }
                //VFT Info
                if (array_key_exists('vftInfo', $this->arrayPosnetResponseXML['posnetResponse'])) {
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['vftInfo']['vftAmount'])) {
                        $this->posnetOOSResponse->vft_amount = $this->arrayPosnetResponseXML['posnetResponse']['vftInfo']['vftAmount'];
                    }
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['vftInfo']['vftRate'])) {
                        $this->posnetOOSResponse->vft_rate = $this->arrayPosnetResponseXML['posnetResponse']['vftInfo']['vftRate'];
                    }
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['vftInfo']['vftDayCount'])) {
                        $this->posnetOOSResponse->vft_daycount = $this->arrayPosnetResponseXML['posnetResponse']['vftInfo']['vftDayCount'];
                    }
                }
                //Kontur Info
                if (array_key_exists('konturInfo', $this->arrayPosnetResponseXML['posnetResponse'])) {
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['konturInfo']['konturAmount'])) {
                        $this->posnetOOSResponse->kontur_amount = $this->arrayPosnetResponseXML['posnetResponse']['konturInfo']['konturAmount'];
                    }
                }
                //OOS ResolveMerchantDataResponse
                if (array_key_exists('oosResolveMerchantDataResponse', $this->arrayPosnetResponseXML['posnetResponse'])) {
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['xid'])) {
                        $this->posnetOOSResponse->xid = $this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['xid'];
                    }
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['amount'])) {
                        $this->posnetOOSResponse->amount = $this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['amount'];
                    }
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['currency'])) {
                        $this->posnetOOSResponse->currency = $this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['currency'];
                    }
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['installment'])) {
                        $this->posnetOOSResponse->instcount = $this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['installment'];
                    }
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['txStatus'])) {
                        $this->posnetOOSResponse->tds_tx_status = $this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['txStatus'];
                    }
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['mdStatus'])) {
                        $this->posnetOOSResponse->tds_md_status = $this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['mdStatus'];
                    }
                    if (isset($this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['mdErrorMessage'])) {
                        $this->posnetOOSResponse->tds_md_errormessage = $this->arrayPosnetResponseXML['posnetResponse']['oosResolveMerchantDataResponse']['mdErrorMessage'];
                    }
                }
            }
        }
    }

    /**
     * Main function for PosnetOOS Transactions. Create XML, connect with HTTP(S),receive and parse XML response.
     *
     * @param PosnetOOSRequest $posnetOOSRequest
     * @param string           $reqcode
     */
    public function DoTran($posnetOOSRequest, $reqcode)
    {
        //set_time_limit(0);

        //Create Posnet Response Class
        $this->posnetOOSResponse = new PosnetOOSResponse();

        //Create Request XML
        $posnetOOSXML = new PosnetOOSXML();

        //Create PosnetHTTP Class
        $posnetHTTPConn = new PosnetHTTPConection($this->url);

        if ($this->debug > 0) {
            $posnetHTTPConn->SetDebugLevel($this->debug);
        }

        $this->strRequestXMLData = $posnetOOSXML->CreateXMLForPosnetOOSTransaction(
            $this->merchantInfo,
            $posnetOOSRequest,
            $reqcode
        );

        if ($this->strRequestXMLData == '') {
            $this->posnetOOSResponse->errorcode = '999';
            $this->posnetOOSResponse->errormessage = 'XML Create Error : '.$posnetOOSXML->error;

            return false;
        }

        // Show XML result
        if ($this->debug) {
            echo "<H2><LI>XML creation:</LI</H2>\n<PRE>\n";
            echo htmlspecialchars($this->strRequestXMLData);
            echo "</PRE>\n";
        }

        // Send and Receive Data with HTTP
        $this->strResponseXMLData = urldecode($posnetHTTPConn->SendDataAndGetResponse($this->strRequestXMLData));
        if ($this->strResponseXMLData == '') {
            $this->posnetOOSResponse->errorcode = '999';
            $this->posnetOOSResponse->errormessage = 'HTTP Connection Error : '.$posnetHTTPConn->error;

            return false;
        }

        if ($this->debug) {
            echo "<H2><LI>Response body:</LI</H2>\n<PRE>\n";
            echo htmlspecialchars($this->strResponseXMLData);
            echo "</PRE>\n";
        }

        //Parse Response XML
        $this->arrayPosnetResponseXML = $posnetOOSXML->ParseXMLForPosnetOOSTransaction($this->strResponseXMLData);

        if ($this->debug) {
            echo "<H2><LI>Response XML Array :</LI</H2>\n<PRE>\n";
            print_r($this->arrayPosnetResponseXML);
            echo '</pre>';
        }

        if (count($this->arrayPosnetResponseXML) == 0) {
            if ($this->debug) {
                echo "<H2><LI>Unable to parse XML !</LI</H2>\n<PRE>\n";
            }
            $this->posnetOOSResponse->errorcode = '999';
            $this->posnetOOSResponse->errormessage = 'XML Parse Error : '.$posnetOOSXML->error;

            return false;
        } else {
            $this->SetResponseParameters($reqcode);
            if ($this->GetApprovedCode() == '0') {
                return false;
            }
        }

        return true;
    }

    /**
     * Check sign for the response parameters from YKB Site.
     *
     * @param string $merchantData
     * @param string $bankData
     * @param string $sign
     * @param string $key
     *
     * @return bool
     */
    public function CheckSignForMercSite(
        $merchantData,
        $bankData,
        $sign,
        $key
    ) {
        if ($merchantData == '' || $bankData == '' || $sign == '') {
            $this->posnetOOSResponse->errorcode = '444';
            $this->posnetOOSResponse->errormessage = 'GECERSIZ DATA ( Merchant Data='.$merchantData.' - Bank Data='.$bankData.' - Sign='.$sign.' )';

            return false;
        }

        $data = $merchantData.$bankData.$key;
        $hash = strtoupper(md5($data));
        if ($hash === $sign) {
            return true;
        } else {
            $this->posnetOOSResponse->errorcode = '444';
            $this->posnetOOSResponse->errormessage = 'IMZA GECERLI DEGIL ('.$hash.')';
            if ($this->debug) {
                echo $this->posnetOOSResponse->errormessage;
            }

            return false;
        }
    }

    /**
     * Check merchant data that is sent from YKB Site.
     *
     * @return bool
     */
    public function CheckMerchantData()
    {
        //$timedif = $this->GMT_TUR*3600-(date("Z")-(date("I")*3600));
        //$now = (time()+$timedif) * 1000;
        $now = time() * 1000;
        $minTime = $now - INTERVALTIMEFORPOSNETTRAN;
        $maxTime = $now + INTERVALTIMEFORPOSNETTRAN;

        if ($this->posnetOOSResponse->trantime < $minTime || $this->posnetOOSResponse->trantime > $maxTime) {
            $this->posnetOOSResponse->errorcode = '444';
            $this->posnetOOSResponse->errormessage = 'ISLEM ZAMANI UYUMSUZ';
            if ($this->debug) {
                echo $this->posnetOOSResponse->errormessage;
            }

            return false;
        }
        if (strcmp($this->posnetOOSResponse->mid, $this->merchantInfo->mid) != 0 || strcmp($this->posnetOOSResponse->tid, $this->merchantInfo->tid) != 0) {
            $this->posnetOOSResponse->errorcode = '444';
            $this->posnetOOSResponse->errormessage = 'MID TID UYUMSUZ';
            if ($this->debug) {
                echo $this->posnetOOSResponse->errormessage;
            }

            return false;
        }

        return true;
    }

    /* Public methods */

    /**
     * It is used for creating OOS request datas.
     *
     * @param string $cardholdername
     * @param string $amount
     * @param string $currency
     * @param string $instnumber
     * @param string $xid
     * @param string $ccno
     * @param string $expdate
     * @param string $cvc
     *
     * @return bool
     */
    public function CreateTranRequestDatas($cardholdername,
                                           $amount,
                                           $currency,
                                           $instnumber,
                                           $xid,
                                           $trantype,
                                           $ccno = '',
                                           $expdate = '',
                                           $cvc = ''
    ) {
        $posnetOOSRequest = new PosnetOOSRequest();

        if ($ccno == null) {
            $posnetOOSRequest->ccno = '';
        } else {
            $posnetOOSRequest->ccno = $ccno;
        }

        if ($expdate == null) {
            $posnetOOSRequest->expdate = '';
        } else {
            $posnetOOSRequest->expdate = $expdate;
        }

        if ($cvc == null) {
            $posnetOOSRequest->cvc = '';
        } else {
            $posnetOOSRequest->cvc = $cvc;
        }

        $posnetOOSRequest->cardholdername = $cardholdername;
        $posnetOOSRequest->amount = $amount;
        $posnetOOSRequest->currency = $currency;
        $posnetOOSRequest->instnumber = $instnumber;
        $posnetOOSRequest->xid = $xid;
        $posnetOOSRequest->tranType = $trantype;

        return $this->DoTran($posnetOOSRequest, '0');
    }

    /**
     * It is used for creating OOS request datas for Hazirkart Transaction.
     *
     * @param string $cardholdername
     * @param string $kontur
     * @param string $currency
     * @param string $xid
     * @param string $ccno
     * @param string $expdate
     * @param string $cvc
     *
     * @return bool
     */
    public function CreateTranRequestDatas_ForHZ($cardholdername,
                                                 $kontur,
                                                 $currency,
                                                 $xid,
                                                 $ccno = '',
                                                 $expdate = '',
                                                 $cvc = '')
    {
        $posnetOOSRequest = new PosnetOOSRequest();

        if ($ccno == null) {
            $posnetOOSRequest->ccno = '';
        } else {
            $posnetOOSRequest->ccno = $ccno;
        }

        if ($expdate == null) {
            $posnetOOSRequest->expdate = '';
        } else {
            $posnetOOSRequest->expdate = $expdate;
        }

        if ($cvc == null) {
            $posnetOOSRequest->cvc = '';
        } else {
            $posnetOOSRequest->cvc = $cvc;
        }

        $posnetOOSRequest->cardholdername = $cardholdername;
        $posnetOOSRequest->amount = $kontur;
        $posnetOOSRequest->currency = $currency;
        $posnetOOSRequest->instnumber = '00';
        $posnetOOSRequest->xid = $xid;
        $posnetOOSRequest->tranType = 'HZKart';

        return $this->DoTran($posnetOOSRequest, '0');
    }

    /**
     * It is used for checking and resolving OOS response datas (merchantData and bankData) from YKB Site
     * By this function, merchant can acquire info about requested transaction by decrypting merchantData.
     *
     * @param string $merchantData
     * @param string $bankData
     * @param string $sign
     *
     * @return bool
     */
    public function CheckAndResolveMerchantData(
        $merchantData,
        $bankData,
        $sign
    ) {
        //Create Posnet Response Class
        $this->posnetOOSResponse = new PosnetOOSResponse();

        if (!$this->CheckSignForMercSite(
            $merchantData,
            $bankData,
            $sign,
            $this->merchantInfo->enckey
        )) {
            return false;
        }

        //Use MCrypt Library
        if ($USEMCRYPTLIBRARY = true) {
            if (!function_exists('mcrypt_module_open')) {
                $this->posnetOOSResponse->errormessage = 'MCRYPT MODULU YUKLU DEGIL';

                return false;
            }

            $posnetENC = new PosnetENC();
            //Decrypt Data
            $decryptedData = $posnetENC->Decrypt($merchantData, $this->merchantInfo->enckey);
            $posnetENC->DeInit();

            if ($this->debug) {
                echo '<br>Decryptyed Data : '.$decryptedData.'<BR>';
            }

            if ($decryptedData == '') {
                $this->posnetOOSResponse->errormessage = $posnetENC->error;

                return false;
            }

            //Seperate parameters
            list($this->posnetOOSResponse->mid,
                $this->posnetOOSResponse->tid,
                $this->posnetOOSResponse->amount,
                $this->posnetOOSResponse->instcount,
                $this->posnetOOSResponse->xid,
                $this->posnetOOSResponse->totalPoint,
                $this->posnetOOSResponse->totalPointAmount,
                $this->posnetOOSResponse->weburl,
                $this->posnetOOSResponse->hostip,
                $this->posnetOOSResponse->port,
                $this->posnetOOSResponse->tds_tx_status,
                $this->posnetOOSResponse->tds_md_status,
                $this->posnetOOSResponse->tds_md_errormessage,
                $this->posnetOOSResponse->trantime,
                $this->posnetOOSResponse->currency) = explode(';', $decryptedData);

            if (!$this->CheckMerchantData()) {
                return false;
            }

            //$this->posnetOOSResponse->errormessage = "SUCCESS";
            return true;
        } //Use XML Service
        else {
            $posnetOOSRequest = new PosnetOOSRequest();

            $posnetOOSRequest->bankData = $bankData;
            $posnetOOSRequest->merchantData = $merchantData;
            $posnetOOSRequest->sign = $sign;

            $result = $this->DoTran($posnetOOSRequest, '1');
            if ($result) {
                $this->posnetOOSResponse->errormessage = 'SUCCESS';
            }

            return $result;
        }
    }

    /**
     * It is used for completing OOS transaction.
     *
     * @param string $merchantData
     * @param string $bankData
     * @param string $sign
     *
     * @return bool
     */
    public function ConnectAndDoTDSTransaction($merchantData,
                                               $bankData,
                                               $sign)
    {
        if (!$this->CheckSignForMercSite($merchantData,
            $bankData,
            $sign,
            $this->merchantInfo->enckey)) {
            return false;
        }

        $posnetOOSRequest = new PosnetOOSRequest();

        $posnetOOSRequest->bankData = $bankData;
        $posnetOOSRequest->merchantData = $merchantData;
        $posnetOOSRequest->sign = $sign;
        $posnetOOSRequest->wpAmount = $this->wpAmount;

        return $this->DoTran($posnetOOSRequest, '2');
    }

    /**
     * This function is used to set remote URL of POSNET system.
     *
     * @param string $url
     */
    public function SetURL($url)
    {
        $this->url = $url;
    }

    /**
     * It is used for setting Posnet ID.
     *
     * @param string $strPosnetID
     */
    public function SetPosnetID($strPosnetID)
    {
        $this->merchantInfo->posnetid = $strPosnetID;
    }

    /**
     * It is used for setting merchant ID.
     *
     * @param string $strMid
     */
    public function SetMid($strMid)
    {
        $this->merchantInfo->mid = $strMid;
    }

    /**
     * It is used for setting terminal ID.
     *
     * @param string $strTid
     */
    public function SetTid($strTid)
    {
        $this->merchantInfo->tid = $strTid;
    }
    /**
    * It is used for setting mac.
    *
    * @param string $mac
    */
    public function SetMac($mac)
    {
        $this->merchantInfo->mac = $mac;
    }
    /**
     * It is used for setting username for login to posnet web service.
     *
     * @param string $strUsername
     */
    public function SetUsername($strUsername)
    {
        $this->merchantInfo->username = $strUsername;
    }

    /**
     * It is used for setting password for login to posnet web service.
     *
     * @param string $strPassword
     */
    public function SetPassword($strPassword)
    {
        $this->merchantInfo->password = $strPassword;
    }

    /**
     * It is used for setting enckey.
     *
     * @param string $strKey
     */
    public function SetKey($strKey)
    {
        $this->merchantInfo->enckey = $strKey;
    }

    /**
     * It is used for setting world point usage amount.
     *
     * @param string $strWPAmount
     */
    public function SetPointAmount($strWPAmount)
    {
        if ($strWPAmount > 0) {
            $this->wpAmount = $strWPAmount;
        }
    }

    //Get Methods

    /**
     * It is used for getting XML data for response.
     *
     * @return string
     */
    public function GetResponseXMLData()
    {
        return $this->strResponseXMLData;
    }

    /**
     * It is used for getting XML data for request.
     *
     * @return string
     */
    public function GetRequestXMLData()
    {
        return $this->strRequestXMLData;
    }

    //Response XML Parameters

    /**
     * It is used for getting Approved Code
     * 0 --> Not Approved
     * 1 --> Approved.
     *
     * @return string
     */
    public function GetApprovedCode()
    {
        return $this->posnetOOSResponse->approved;
    }

    /**
     * It is used for getting Response Code.
     *
     * @return string
     */
    public function GetResponseCode()
    {
        return $this->posnetOOSResponse->errorcode;
    }

    /**
     * It is used for getting Response Message.
     *
     * @return string
     */
    public function GetResponseText()
    {
        return $this->posnetOOSResponse->errormessage;
    }

    /**
     * It is used for getting Authorization Code for approved transactions.
     *
     * @return string
     */
    public function GetAuthcode()
    {
        return $this->posnetOOSResponse->authcode;
    }

    /**
     * It is used for getting Hostlogkey for approved transactions.
     *
     * @return string
     */
    public function GetHostlogkey()
    {
        return $this->posnetOOSResponse->hostlogkey;
    }

    //OOS Info

    /**
     * It is used for getting data1().
     *
     * @return string
     */
    public function GetData1()
    {
        return $this->posnetOOSResponse->data1;
    }

    /**
     * It is used for getting data2.
     *
     * @return string
     */
    public function GetData2()
    {
        return $this->posnetOOSResponse->data2;
    }

    /**
     * It is used for getting oos sign.
     *
     * @return string
     */
    public function GetSign()
    {
        return $this->posnetOOSResponse->sign;
    }

    //OOS Merchant Data Info

    /**
     * It is used for getting merchant id.
     *
     * @return string
     */
    public function GetMid()
    {
        return $this->posnetOOSResponse->mid;
    }

    /**
     * It is used for getting terminal id.
     *
     * @return string
     */
    public function GetTid()
    {
        return $this->posnetOOSResponse->tid;
    }

    /**
     * It is used for getting transaction id.
     *
     * @return string
     */
    public function GetXid()
    {
        return $this->posnetOOSResponse->xid;
    }

    /**
     * It is used for getting amount.
     *
     * @return string
     */
    public function GetAmount()
    {
        return $this->posnetOOSResponse->amount;
    }

    /**
     * It is used for getting Hazirkart Kontur.
     *
     * @return string
     */
    public function GetKontur()
    {
        return $this->posnetOOSResponse->amount;
    }

    /**
     * It is used for getting Hazirkart Kontur Amount.
     *
     * @return string
     */
    public function GetKonturAmount()
    {
        return $this->posnetOOSResponse->kontur_amount;
    }

    /**
     * It is used for getting currency.
     *
     * @return string
     */
    public function GetCurrency()
    {
        return $this->posnetOOSResponse->currency;
    }

    /**
     * It is used for getting instalment number.
     *
     * @return string
     */
    public function GetInstalmentNumber()
    {
        return $this->posnetOOSResponse->instcount;
    }

    /**
     * It is used for getting each instalment amount.
     *
     * @return string
     */
    public function GetInstalmentAmount()
    {
        return $this->posnetOOSResponse->instamount;
    }

    /**
     * It is used for getting Point for a success transaction.
     *
     * @return string
     */
    public function GetPoint()
    {
        return $this->posnetOOSResponse->point;
    }

    /**
     * It is used for getting Point Amount for a success transaction.
     *
     * @return string
     */
    public function GetPointAmount()
    {
        return $this->posnetOOSResponse->pointAmount;
    }

    /**
     * It is used for getting cardholder available Total Point.
     *
     * @return string
     */
    public function GetTotalPoint()
    {
        return $this->posnetOOSResponse->totalPoint;
    }

    /**
     * It is used for getting cardholder available Total Point Amount.
     *
     * @return string
     */
    public function GetTotalPointAmount()
    {
        return $this->posnetOOSResponse->totalPointAmount;
    }

    /**
     * It is used for getting Three D-Secure transaction status.
     *
     * @return string
     */
    public function GetTDSTXStatus()
    {
        return $this->posnetOOSResponse->tds_tx_status;
    }

    /**
     * It is used for getting Three D-Secure transaction code.
     *
     * @return string
     */
    public function GetTDSMDStatus()
    {
        return $this->posnetOOSResponse->tds_md_status;
    }

    /**
     * It is used for getting Three D-Secure transaction message.
     *
     * @return string
     */
    public function GetTDSMDErrorMessage()
    {
        return $this->posnetOOSResponse->tds_md_errormessage;
    }

    /**
     * It is used for getting last error message.
     *
     * @return string
     */
    public function GetLastErrorMessage()
    {
        return $this->posnetOOSResponse->errormessage;
    }

    //VFT Info

    /**
     * It is used for getting Forward Sale Amount.
     *
     * @return string
     */
    public function GetVftAmount()
    {
        return $this->posnetOOSResponse->vft_amount;
    }

    /**
     * It is used for getting Forward Sale Day Count.
     *
     * @return string
     */
    public function GetVftDayCount()
    {
        return $this->posnetOOSResponse->vft_daycount;
    }
}
