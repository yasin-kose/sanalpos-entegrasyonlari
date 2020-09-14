<?php

require  'vendor/autoload.php';

use Symfony\Component\Serializer\Encoder\XmlEncoder;
$data= '<?xml version="1.0" encoding="UTF-8"?>
<IPaySecure>
<Message>
<VERes>
<Status>E</Status>
</VERes>
</Message>
<ResultDetail>
<ErrorCode>2023</ErrorCode>
<ErrorMessage>Verify Enrollment Request Id Already exist for this
merchant</ErrorMessage>
</ResultDetail>
</IPaySecure>';
$encoder = new XmlEncoder();
        $xml = $encoder->decode($data, 'xml');
        print_r ((object) json_decode(json_encode($xml)));
?>
