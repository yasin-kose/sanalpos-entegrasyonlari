<?php

/**
 * It is used for a template class for transaction requests.
 */
class PosnetOOSRequest
{
    /**
     * Creditcard number (16-19 char).
     *
     * @var string
     */
    public $ccno; // min 16 char, max 21 char
    /**
     * Expire date of the credit card (4 chars). 2 Digits year, 2 digits month (YYMM).
     * e.g. 0712.
     *
     * @var string
     */
    public $expdate;
    /**
     * CVC(3 chars) of the credit card. "XXX" can be specified in test environment.
     *
     * @var string
     */
    public $cvc;
    /**
     * XID(20 chars). Distinguishes this transaction from the others.
     * Should be different in each authorization or sale transaction.
     * Can be alpha-numeric. To specify a distinct one in each authorization,
     * e.g. 67000000670104031615.
     *
     * @var string
     */
    public $xid;
    /**
     * Transaction amount (1-12 chars) in YKr (100 YTLs) (12 chars). Last 2 digits are always assumed to be YKr.
     * Should contain no thousands or decimal separator.
     * e.g. 10 YTL : 1000
     * e.g. 1.015,16 YTL : 101516.
     *
     * @var string
     */
    public $amount;
    /**
     * Curency Code (2 chars) for a transaction request
     * e.g. : YT.
     *
     * @var string
     */
    public $currency;
    /**
     * Instalment number (2 chars). Specifies number of instalments.
     * If no instalment will be made, 0 should be specified.
     * e.g. : 03.
     *
     * @var string
     */
    public $instnumber;
    /**
     * Type of a transaction.
     * e.g. : Auth or Sale.
     *
     * @var string
     */
    public $trantype;
    /**
     * CardHolder Name (1 - 50 chars)
     * e.g. : XXXX YYYYYYY.
     *
     * @var string
     */
    public $cardholdername;

    /**
     * BankData.
     *
     * @var string
     */
    public $bankData = '';
    /**
     * Merchant Data.
     *
     * @var string
     */
    public $merchantData = '';
    /**
     * Sign of parameters.
     *
     * @var string
     */
    public $sign = '';
}

/**
 * It is used for a template class for oos transaction responses.
 */
class PosnetOOSResponse
{
    /**
     * Result of transaction (1 char). Shows if the transaction was approved.
     *  '0�: Approved
     *  �1�: Not approved
     *  �2�: Approved just before a time.
     *
     * @var string
     */
    public $approved;
    /**
     * Response Code (1 - 4 chars). Error code if transaction is not approved.
     * It is recommended to check this parameter rather than Responsetext.
     * Because error codes don't change, but error descriptions do.
     * You can display your own error message for common errors
     * (such as wrong expire date) by checking this parameter.
     *
     * @var string
     */
    public $errorcode;
    /**
     * Response Text (1 - 50 chars). Short description of the error if transaction is not approved.
     *
     * @var string
     */
    public $errormessage;
    /**
     * data1 is used for redirecting to YKB Site.
     *
     * @var string
     */
    public $data1;
    /**
     * data2 is used for redirecting to YKB Site with CreditCard Parameters.
     *
     * @var string
     */
    public $data2;
    /**
     * sign.
     *
     * @var string
     */
    public $sign;

    /**
     * Merchant ID (10 char).
     *
     * @var string
     */
    public $mid = '';
    /**
     * Terminal ID (8 char).
     *
     * @var string
     */
    public $tid = '';
    /**
     * Hostlogkey (18 chars) returned from authorization or sale transaction.
     *
     * This value will be used in the reversal or capture of this transaction. * @var string
     * @var string
     */
    public $hostlogkey = '';
    /**
     * Authorization code (6 chars) returned from authorization or sale transaction.
     * This value will be used in the reversal or capture of this transaction.
     *
     * @var string
     */
    public $authcode = '';

    /**
     * Instalment number (2 chars).
     *
     * @var string
     */
    public $instcount = '';
    /**
     * Amount (1-12 chars) of each instalment.
     *
     * @var string
     */
    public $instamount = '';

    /**
     * WorldPoints (8 chars) gained from this transaction.
     *
     * @var string
     */
    public $point = '';
    /**
     * YTL equivalent (12 chars) of  WorldPoints gained from this transaction.
     *
     * @var string
     */
    public $pointAmount = '';
    /**
     * WorldPoints (8 chars) the card holder has.
     *
     * @var string
     */
    public $totalPoint = '';
    /**
     * YTL equivalent (12 chars) of  WorldPoints the card holder has.
     *
     * @var string
     */
    public $totalPointAmount = '';
    /**
     * XID(20 chars). Distinguishes this transaction from the others.
     * Should be different in each authorization, sale or bonus usage transaction.
     * Can be alpha-numeric.
     * e.g. YKB_0000050228175132.
     *
     * @var string
     */
    public $xid = '';
    /**
     * Transaction amount (1-12 chars) in YKr (100 YTLs) (12 chars). Last 2 digits are always assumed to be YKr.
     * Should contain no thousands or decimal separator.
     * e.g. 10 YTL : 1000
     * e.g. 1.015,16 YTL : 101516.
     *
     * @var string
     */
    public $amount = '';
    /**
     * Curency Code (2 chars) for a transaction request
     * e.g. : YT.
     *
     * @var string
     */
    public $currency = '';
    /**
     * Web URL of XML Service (not used).
     *
     * @var string
     */
    public $weburl;
    /**
     * Server IP  (not used).
     *
     * @var string
     */
    public $hostip;
    /**
     * Server port  (not used).
     *
     * @var string
     */
    public $port;
    /**
     * ThreeD Secure transaction status.
     *
     * @var string
     */
    public $tds_tx_status;
    /**
     * ThreeD Secure result code.
     *
     * @var string
     */
    public $tds_md_status;
    /**
     * ThreeD Secure error message.
     *
     * @var string
     */
    public $tds_md_errormessage;
    /**
     * Amount (1-12 chars) of vade applied to vft transaction.
     *
     * @var string
     */
    public $vft_amount;
    /**
     * Interest Rate (6 chars) for vft transaction.
     *
     * @var string
     */
    public $vft_rate;
    /**
     * VFT Day Count (4 chars) is the difference between first instalment payment date
     * and transaction date.
     *
     * @var string
     */
    public $vft_daycount;
    /**
     * Tran time.
     *
     * @var string
     */
    public $trantime;
    /**
     * Kontur amount (1-12 chars) in YKr (100 YTLs) (12 chars). Last 2 digits are always assumed to be YKr.
     * Should contain no thousands or decimal separator.
     * e.g. 10 YTL : 1000
     * e.g. 1.015,16 YTL : 101516.
     *
     * @var string
     */
    public $kontur_amount = '';

    public function __construct()
    {
        $this->Init();
    }

    /**
     * Initialize parameters.
     */
    public function Init()
    {
        $this->approved = '0';
        $this->errorcode = '';
        $this->errormessage = '';

        $this->data1 = '';
        $this->data2 = '';
        $this->sign = '';

        $this->weburl = '';
        $this->hostip = '';
        $this->port = '';

        $this->tds_tx_status = '';
        $this->tds_md_status = '';
        $this->tds_md_errormessage = '';

        $this->trantime = '';
    }
}
