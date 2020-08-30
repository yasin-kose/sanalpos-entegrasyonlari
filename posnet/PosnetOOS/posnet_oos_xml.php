<?php
/*
 * posnet_oos_xml.php
 *
 */

if (!defined('POSNET_MODULES_DIR')) {
    define('POSNET_MODULES_DIR', dirname(__FILE__).'/..');
}

// Include posnet helper library
require_once 'posnet_oos_struct.php';
// Include the xml library
require_once POSNET_MODULES_DIR.'/XML/xml.php';

class PosnetOOSXML extends XML
{
    /**
     * Error message for XML parsing.
     */
    public $error;

    /**
     * Constructor.
     *
     * @param string $error
     */
    public function __construct()
    {
        parent::__construct();
        $this->error = '';
    }

    /**
     * This function is used to set errors like XML parser errors.
     *
     * @param string $error
     */
    public function SetError($error)
    {
        $this->error = $error;
    }

    /**
     * This function is used to create POSNET XML Header Nodes.
     *
     * @param MerchantInfo $merchantInfo
     * @param XMLNode      &$node_posnetRequest
     */
    public function CreateXMLForHeader($merchantInfo, &$node_posnetRequest)
    {
        $this->xmlDecl = '<?xml version="1.0" encoding="ISO-8859-9"?>';

        $node_posnetRequest = $this->createElement('posnetRequest');
        $this->appendChild($node_posnetRequest);

        $mid = $this->createElement('mid');
        $midTextNode = $this->createTextNode($merchantInfo->mid);
        $mid->appendChild($midTextNode);
        $node_posnetRequest->appendChild($mid);

        $tid = $this->createElement('tid');
        $tidTextNode = $this->createTextNode($merchantInfo->tid);
        $tid->appendChild($tidTextNode);
        $node_posnetRequest->appendChild($tid);

        $username = $this->createElement('username');
        $usernameTextNode = $this->createTextNode($merchantInfo->username);
        $username->appendChild($usernameTextNode);
        $node_posnetRequest->appendChild($username);

        $password = $this->createElement('password');
        $passwordTextNode = $this->createTextNode($merchantInfo->password);
        $password->appendChild($passwordTextNode);
        $node_posnetRequest->appendChild($password);
    }

    /**
     * This function is used to create POSNET XML Transaction Nodes for each transaction type.
     *
     * @param MerchantInfo $merchantInfo
     * @param $posnetOOSRequest
     * @param $reqcode
     *
     * @return string
     *
     * @internal param PosnetRequest $posnetRequest
     * @internal param string $trantype
     */
    public function CreateXMLForPosnetOOSTransaction($merchantInfo, $posnetOOSRequest, $reqcode)
    {
        //Create Header
        $this->CreateXMLForHeader($merchantInfo, $node_posnetOOSRequest);

        //Create Transaction XML Packet
        switch (strtolower($reqcode)) {
            case '0': {
                $node_tran = $this->createElement('oosRequestData');
                $node_posnetOOSRequest->appendChild($node_tran);

                $posnetid = $this->createElement('posnetid');
                $posnetidTextNode = $this->createTextNode($merchantInfo->posnetid);
                $posnetid->appendChild($posnetidTextNode);
                $node_tran->appendChild($posnetid);

                $node_ccno = $this->createElement('ccno');
                $node_ccnoTextNode = $this->createTextNode($posnetOOSRequest->ccno);
                $node_ccno->appendChild($node_ccnoTextNode);
                $node_tran->appendChild($node_ccno);

                $node_expDate = $this->createElement('expDate');
                $node_expDateTextNode = $this->createTextNode($posnetOOSRequest->expdate);
                $node_expDate->appendChild($node_expDateTextNode);
                $node_tran->appendChild($node_expDate);

                $node_cvc = $this->createElement('cvc');
                $node_cvcTextNode = $this->createTextNode($posnetOOSRequest->cvc);
                $node_cvc->appendChild($node_cvcTextNode);
                $node_tran->appendChild($node_cvc);

                $node_amount = $this->createElement('amount');
                $node_amountTextNode = $this->createTextNode($posnetOOSRequest->amount);
                $node_amount->appendChild($node_amountTextNode);
                $node_tran->appendChild($node_amount);

                $node_currency = $this->createElement('currencyCode');
                $node_currencyTextNode = $this->createTextNode($posnetOOSRequest->currency);
                $node_currency->appendChild($node_currencyTextNode);
                $node_tran->appendChild($node_currency);

                $node_instnumber = $this->createElement('installment');
                $node_instnumberTextNode = $this->createTextNode($posnetOOSRequest->instnumber);
                $node_instnumber->appendChild($node_instnumberTextNode);
                $node_tran->appendChild($node_instnumber);

                $node_xid = $this->createElement('XID');
                $node_xidTextNode = $this->createTextNode($posnetOOSRequest->xid);
                $node_xid->appendChild($node_xidTextNode);
                $node_tran->appendChild($node_xid);

                $node_cardholdername = $this->createElement('cardHolderName');
                $node_cardholdernameTextNode = $this->createTextNode($posnetOOSRequest->cardholdername);
                $node_cardholdername->appendChild($node_cardholdernameTextNode);
                $node_tran->appendChild($node_cardholdername);

                $node_trantype = $this->createElement('tranType');
                $node_trantypeTextNode = $this->createTextNode($posnetOOSRequest->tranType);
                $node_trantype->appendChild($node_trantypeTextNode);
                $node_tran->appendChild($node_trantype);

                break;
            }
            case '1': {
                $node_tran = $this->createElement('oosResolveMerchantData');
                $node_posnetOOSRequest->appendChild($node_tran);

                $node_bankData = $this->createElement('bankData');
                $node_bankDataTextNode = $this->createTextNode($posnetOOSRequest->bankData);
                $node_bankData->appendChild($node_bankDataTextNode);
                $node_tran->appendChild($node_bankData);

                $node_merchantData = $this->createElement('merchantData');
                $node_merchantDataTextNode = $this->createTextNode($posnetOOSRequest->merchantData);
                $node_merchantData->appendChild($node_merchantDataTextNode);
                $node_tran->appendChild($node_merchantData);

                $node_sign = $this->createElement('sign');
                $node_signTextNode = $this->createTextNode($posnetOOSRequest->sign);
                $node_sign->appendChild($node_signTextNode);
                $node_tran->appendChild($node_sign);

                break;
            }
            case '2': {
                $node_tran = $this->createElement('oosTranData');
                $node_posnetOOSRequest->appendChild($node_tran);

                $node_bankData = $this->createElement('bankData');
                $node_bankDataTextNode = $this->createTextNode($posnetOOSRequest->bankData);
                $node_bankData->appendChild($node_bankDataTextNode);
                $node_tran->appendChild($node_bankData);

                $node_merchantData = $this->createElement('merchantData');
                $node_merchantDataTextNode = $this->createTextNode($posnetOOSRequest->merchantData);
                $node_merchantData->appendChild($node_merchantDataTextNode);
                $node_tran->appendChild($node_merchantData);

                $node_sign = $this->createElement('sign');
                $node_signTextNode = $this->createTextNode($posnetOOSRequest->sign);
                $node_sign->appendChild($node_signTextNode);
                $node_tran->appendChild($node_sign);

                $node_mac = $this->createElement('mac');
                $node_macTextNode = $this->createTextNode($merchantInfo->mac);
                $node_mac->appendChild($node_macTextNode);
                $node_tran->appendChild($node_mac);

                $node_wpAmount = $this->createElement('wpAmount');
                $node_wpAmountTextNode = $this->createTextNode($posnetOOSRequest->wpAmount);
                $node_wpAmount->appendChild($node_wpAmountTextNode);
                $node_tran->appendChild($node_wpAmount);

                break;
            }
            default:
                $this->SetError('Invalid trantype');

                return '';
        }

        return $this->toString();
    }

    /**
     * This function is used to parse POSNET XML Response.
     *
     * @param string $strXMLData
     *
     * @return string
     */
    public function ParseXMLForPosnetOOSTransaction($strXMLData)
    {
        return $this->parseXML($strXMLData);
    }
}
