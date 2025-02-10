<?php
$username = 'SAT2405190928';
$password = 'satim120';

// Order Status Request

$gatewayApiUrlOrder =  'https://test.satim.dz/payment/rest/confirmOrder.do';


$data = array(
    "sslverify" => "true",
    "userName" => $username,
    "password" => $password,
    "orderId" => $_GET['gatewayOrderId'],
    'language' => 'fr'
);
//echo $url;
$fields_string = http_build_query($data);

$ch = curl_init($gatewayApiUrlOrder);
curl_setopt($ch, CURLOPT_URL, $gatewayApiUrlOrder);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
//print_r($response);
curl_close($ch);


$gatewayApiUrl = 'https://test.satim.dz/payment/rest/getOrderStatus.do';

$ch1 = curl_init($gatewayApiUrl);
curl_setopt($ch1, CURLOPT_URL, $gatewayApiUrl);
curl_setopt($ch1, CURLOPT_POST, 1);
curl_setopt($ch1, CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
$responseConfirm = curl_exec($ch1);
//print_r($responseConfirm);
curl_close($ch1);

$json = json_decode($responseConfirm);
$jsonOrder = json_decode($response);
//print($json);

$ErrorCode = 0;
$MessageReturn = "";
$color = "";
$code = "";
if ($json->ErrorCode != 0) {
    #error£
    $ErrorCode = $json->ErrorCode;

    $color = "red";
    if ($jsonOrder->params->respCode_desc) {

        $MessageReturn = $jsonOrder->params->respCode_desc ?? $jsonOrder->actionCodeDescription;
        // $MessageReturn=$json->ErrorMessage;


    } else {

        $MessageReturn = $jsonOrder->params->respCode_desc ?? $jsonOrder->actionCodeDescription;
        //  $MessageReturn=$json->ErrorMessage;


    }
} else {
    $color = "#00FF00";
    $MessageReturn = $json->params->respCode_desc ?? $json->actionCodeDescription;
    $code = $json->approvalCode;

    $total = number_format($json->Amount, 2);
}
$orderId = $_GET['gatewayOrderId'];
$orderNumber = $_GET['orderNumber'];
// header("location:".$_GET['returnUrl']."?&orderId=$orderId&orderNumber=$orderNumber&bool=1&ErrorCode=$ErrorCode&MessageReturn=$MessageReturn&code=$code");



$MessageReturn = str_replace("'", "\'", $MessageReturn);

/*echo $orderId;
    echo $orderNumber;
    echo $MessageReturn;*/
if (strpos($_GET['returnUrl'], "?")) $returnUrl = $_GET['returnUrl'] . "&orderId=$orderId&orderNumber=$orderNumber&bool=1";
else
    $returnUrl = $_GET['returnUrl'] . "?orderId=$orderId&orderNumber=$orderNumber&bool=1";


//header("location:".$returnUrl."&orderId=$orderId&orderNumber=$orderNumber&bool=1&ErrorCode=$ErrorCode&MessageReturn=$MessageReturn&code=$code");
?>
<form id="myForm" action="<?php echo $returnUrl; ?>" method="GET">
    <input type="hidden" name="orderId" value="<?php echo $orderId; ?>">
    <input type="hidden" name="orderNumber" value="<?php echo $orderNumber; ?>">
    <input type="hidden" name="bool" value="1">
    <input type="hidden" name="ErrorCode" value="<?php echo $ErrorCode; ?>">
    <input type="hidden" name="MessageReturn" value="<?php echo $MessageReturn; ?>">
    <input type="hidden" name="code" value="<?php echo $code; ?>">
    <input type="hidden" name="total" value="<?php echo $total; ?>">


</form>
<script type="text/javascript">
    document.getElementById('myForm').submit();
</script>