<?php


/**
 * It is used for encryption.
 */
class PosnetENC
{
    public $td;
    public $ks;
    public $block;
    /**
     * Error message for http connection.
     */
    public $error;

    public function __construct()
    {
        srand((float) microtime() * 10000000);
        $this->block = mcrypt_get_block_size(MCRYPT_TripleDES, MCRYPT_MODE_CBC);
        $this->td = mcrypt_module_open(MCRYPT_TripleDES, '', MCRYPT_MODE_CBC, '');
        $this->ks = mcrypt_enc_get_key_size($this->td);
        $this->error = '';
    }

    /**
     * This function is used to get encryption errors.
     *
     * @param string $error
     */
    public function GetLastError()
    {
        return $this->error;
    }

    public function CreateIV()
    {
        $temp = sprintf('%05d', rand());
        $temp .= sprintf('%05d', rand());
        $temp .= sprintf('%05d', rand());
        $temp .= sprintf('%05d', rand());

        return pack('H*', substr($temp, 0, 16));
        //return mcrypt_create_iv (mcrypt_enc_get_iv_size($this->td), MCRYPT_RAND);
    }

    public function Encrypt($data, $key)
    {
        //Create IV
        $iv = $this->CreateIV();

        //PKCS Padding
        $data = $this->DoPadding($data);

        //Initialize
        mcrypt_generic_init($this->td, $this->GetKey($key), $iv);

        //Encrypt Data
        $encrypted_data = mcrypt_generic($this->td, $data);

        //Add IV and Convert to HEX
        $hex_encrypted_data = strtoupper(bin2hex($iv)).strtoupper(bin2hex($encrypted_data));

        //Add CRC
        $hex_encrypted_data = $this->AddCRC($hex_encrypted_data);

        return $hex_encrypted_data;
    }

    public function Decrypt($data, $key)
    {
        if (strlen($data) < 16 + 8) {
            return false;
        }

        //Get IV
        $iv = pack('H*', substr($data, 0, 16));

        //Get Encrypted Data
        $encrypted_data = pack('H*', substr($data, 16, strlen($data) - 16 - 8));

        //Get CRC
        $crc = substr($data, -8);

        //Check CRC
        if (!$this->CheckCRC(substr($data, 0, strlen($data) - 8), $crc)) {
            $this->error = 'CRC is not valid ! ('.$crc.')';

            return '';
        }
        //Initialize
        mcrypt_generic_init($this->td, $this->GetKey($key), $iv);

        //Decrypt Data
        $decrypted_data = mdecrypt_generic($this->td, $encrypted_data);

        //Remove Padded Data
        return $this->RemovePaddedData($decrypted_data);
    }

    public function GetKey($key)
    {
        $deskey = substr(strtoupper(md5($key)), 0, $this->ks);

        return $deskey;
    }

    public function DoPadding($data)
    {
        $len = strlen($data);
        $padding = $this->block - ($len % $this->block);
        $data .= str_repeat(chr($padding), $padding);

        return $data;
    }

    public function RemovePaddedData($data)
    {
        $packing = ord($data[strlen($data) - 1]);

        if ($packing and ($packing < $this->block)) {
            for ($P = strlen($data) - 1; $P >= strlen($data) - $packing; --$P) {
                if (ord($data[$P]) != $packing) {
                    $packing = 0;
                }
            }
        }

        $data = substr($data, 0, strlen($data) - $packing);

        return $data;
    }

    public function AddCRC($data)
    {
        $crc = crc32($data);
        $hex_crc = sprintf('%08x', $crc);
        $data .= strtoupper($hex_crc);

        return $data;
    }

    public function CheckCRC($data, $crc)
    {
        $crc_calc = crc32($data);
        $hex_crc = sprintf('%08x', $crc_calc);
        $crc_calc = strtoupper($hex_crc);
        if (strcmp($crc_calc, $crc) == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function DeInit()
    {
        mcrypt_generic_deinit($this->td);
        mcrypt_module_close($this->td);
    }
}
