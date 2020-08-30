<?php
namespace SanalPos;

/**
 * SanalPosBase
 */
class SanalPosBase
{
    protected $test     = false;
    protected $isThreeD = false;
    protected $card     = [];
    protected $order    = [];
    protected $currency = 949;
    protected $creditcardTypes = [
        [
            'Name' => 'American Express',
            'cardLength' => [15],
            'cardPrefix' => ['34', '37'],
        ], [
            'Name' => 'Maestro',
            'cardLength' => [12, 13, 14, 15, 16, 17, 18, 19],
            'cardPrefix' => ['5018', '5020', '5038', '6304', '6759', '6761', '6763'],
        ], [
            'Name' => 'Mastercard',
            'cardLength' => [16],
            'cardPrefix' => ['51', '52', '53', '54', '55'],
        ], [
            'Name' => 'Visa',
            'cardLength' => [13, 16],
            'cardPrefix' => ['4'],
        ], [
            'Name' => 'JCB',
            'cardLength' => [16],
            'cardPrefix' => ['3528', '3529', '353', '354', '355', '356', '357', '358'],
        ], [
            'Name' => 'Discover',
            'cardLength' => [16],
            'cardPrefix' => ['6011', '622126', '622127', '622128', '622129', '62213','62214', '62215', '62216', '62217', '62218', '62219','6222', '6223', '6224', '6225', '6226', '6227', '6228','62290', '62291', '622920', '622921', '622922', '622923','622924', '622925', '644', '645', '646', '647', '648','649', '65'],
        ], [
            'Name' => 'Solo',
            'cardLength' => [16, 18, 19],
            'cardPrefix' => ['6334', '6767'],
        ], [
            'Name' => 'Unionpay',
            'cardLength' => [16, 17, 18, 19],
            'cardPrefix' => ['622126', '622127', '622128', '622129', '62213', '62214','62215', '62216', '62217', '62218', '62219', '6222', '6223','6224', '6225', '6226', '6227', '6228', '62290', '62291','622920', '622921', '622922', '622923', '622924', '622925'],
        ], [
            'Name' => 'Diners Club',
            'cardLength' => [14],
            'cardPrefix' => ['300', '301', '302', '303', '304', '305', '36'],
        ], [
            'Name' => 'Diners Club US',
            'cardLength' => [16],
            'cardPrefix' => ['54', '55'],
        ], [
            'Name' => 'Diners Club Carte Blanche',
            'cardLength' => [14],
            'cardPrefix' => ['300', '305'],
        ], [
            'Name' => 'Laser',
            'cardLength' => [16, 17, 18, 19],
            'cardPrefix' => ['6304', '6706', '6771', '6709'],
        ],
    ];
    /**
     * setCard
     *
     * @param  mixed $number
     * @param  mixed $month
     * @param  mixed $year
     * @param  mixed $cvv
     * @return void
     */
    public function setCard($number, $month, $year, $cvv)
    {
        $this->card['number']   = $number;
        $this->card['month']    = str_pad($month, 2, 0, STR_PAD_LEFT);
        $this->card['year_pad'] = str_pad($year, 2, 0, STR_PAD_LEFT);
        $this->card['year']     = $year;
        $this->card['cvv']      = $cvv;
    }
    /**
     * checkCvv
     *
     * @return void
     */
    public function checkCvv()
    {
        // /^[0-9]{3,4}$/
        return preg_match('/^[0-9]{3,4}$/', $this->card['cvv']);
    }
    
    /**
     * setOrder
     *
     * @param  mixed $orderId
     * @param  mixed $email
     * @param  mixed $total
     * @param  mixed $installment
     * @param  mixed $extra
     * @return void
     */
    public function setOrder(string $orderId, string $email, $total, int $installment = 0, array $extra = [])
    {
        $this->order['orderId']     = $orderId;
        $this->order['email']       = $email;
        $this->order['total']       = $total;
        $this->order['installment'] = $installment>1?$installment:'';
        $this->order['extra']       = $extra;
    }
        
    /**
     * getCardType
     *
     * @param  mixed $include_sub_types
     * @return void
     */
    public function getCardType($include_sub_types = false)
    {
        $pan = trim($this->card['number']);
        //visa
        $visa_regex = "/^4[0-9]{0,}$/";
        $vpreca_regex = "/^428485[0-9]{0,}$/";
        $postepay_regex = "/^(402360|402361|403035|417631|529948){0,}$/";
        $cartasi_regex = "/^(432917|432930|453998)[0-9]{0,}$/";
        $entropay_regex = "/^(406742|410162|431380|459061|533844|522093)[0-9]{0,}$/";
        $o2money_regex = "/^(422793|475743)[0-9]{0,}$/";

        // MasterCard
        $mastercard_regex = "/^(5[1-5]|222[1-9]|22[3-9]|2[3-6]|27[01]|2720)[0-9]{0,}$/";
        $maestro_regex = "/^(5[06789]|6)[0-9]{0,}$/";
        $kukuruza_regex = "/^525477[0-9]{0,}$/";
        $yunacard_regex = "/^541275[0-9]{0,}$/";

        // American Express
        $amex_regex = "/^3[47][0-9]{0,}$/";

        // Diners Club
        $diners_regex = "/^3(?:0[0-59]{1}|[689])[0-9]{0,}$/";

        //Discover
        $discover_regex = "/^(6011|65|64[4-9]|62212[6-9]|6221[3-9]|622[2-8]|6229[01]|62292[0-5])[0-9]{0,}$/";

        //JCB
        $jcb_regex = "/^(?:2131|1800|35)[0-9]{0,}$/";

        //ordering matter in detection, otherwise can give false results in rare cases
        if (preg_match($jcb_regex, $pan)) {
            return "jcb";
        }

        if (preg_match($amex_regex, $pan)) {
            return "amex";
        }

        if (preg_match($diners_regex, $pan)) {
            return "diners_club";
        }

        //sub visa/mastercard cards
        if ($include_sub_types) {
            if (preg_match($vpreca_regex, $pan)) {
                return "v-preca";
            }
            if (preg_match($postepay_regex, $pan)) {
                return "postepay";
            }
            if (preg_match($cartasi_regex, $pan)) {
                return "cartasi";
            }
            if (preg_match($entropay_regex, $pan)) {
                return "entropay";
            }
            if (preg_match($o2money_regex, $pan)) {
                return "o2money";
            }
            if (preg_match($kukuruza_regex, $pan)) {
                return "kukuruza";
            }
            if (preg_match($yunacard_regex, $pan)) {
                return "yunacard";
            }
        }

        if (preg_match($visa_regex, $pan)) {
            return "visa";
        }

        if (preg_match($mastercard_regex, $pan)) {
            return "mastercard";
        }

        if (preg_match($discover_regex, $pan)) {
            return "discover";
        }

        if (preg_match($maestro_regex, $pan)) {
            if ($pan[0] == '5') { //started 5 must be mastercard
                return "mastercard";
            }
            return "maestro"; //maestro is all 60-69 which is not something else, thats why this condition in the end

        }
        return "unknown"; //unknown for this system
    }

    /**
     * Gets the operation mode
     * TEST for test mode.
     *
     * @return mixed
     */
    public function getTest()
    {
        return $this->test;
    }

    /**
     * Gets the operation mode
     * TEST for test mode everything else is production mode.
     *
     * @param $mode
     *
     * @return mixed
     */
    public function setTest($statu)
    {
        $this->test = $statu;
        return $this->test;
    }    
    /**
     * isThreeD
     *
     * @return void
     */
    public function isThreeD()
    {
        return $this->isThreeD;
    }    
    /**
     * setThreeD
     *
     * @param  mixed $statu
     * @return void
     */
    public function setThreeD($statu)
    {
        $this->isThreeD = $statu;
        return $this->isThreeD;
    }
    
    /**
     * getCurrency
     *
     * @return void
     */
    public function getCurrency()
    {
        return $this->currency;
    }    
    /**
     * setCurrency
     *
     * @param  mixed $currency
     * @return void
     */
    public function setCurrency($currency)
    {
        // 949 TL, 840 USD, 978 EURO, 826 GBP, 392 JPY, 643 RUB
        $availableCurrencies = [949, 840, 978, 826, 392, 643];
        if (!in_array($currency, $availableCurrencies)) {
            throw new \Exception('Currency not found!');
        }
        $this->currency = $currency;
        return $this->getCurrency();
    }
    /**
     * getIpAddress
     *
     * @return void
     */
    public function getIpAddress()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED']) && $_SERVER['HTTP_X_FORWARDED']) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR']) && $_SERVER['HTTP_FORWARDED_FOR']) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED']) && $_SERVER['HTTP_FORWARDED']) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }
}
