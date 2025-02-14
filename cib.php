<?php

//echo json_encode($_GET);
  $LicenceUrl = "https://test.satim.guiddini.dz/SATIM-WFGWX-YVC9B-4J6C9/GD01NI";



 $url="https://test.satim.dz/payment/rest/register.do";

 $username='SAT2301170552';
 $password='satim120';

 $order_id = $_GET['order_id'];

 if(strpos( $_GET['returnUrl'], "?"))
 $returnUrl= $_GET['returnUrl']."&orderNumber=".$order_id."&bool=0";
else
$returnUrl= $_GET['returnUrl']."?orderNumber=".$order_id."&bool=0";


 $jsonParams = '{"orderNumber":'.$order_id.',"udf1":"'.$order_id.'","udf5":"00","force_terminal_id":"E010900790"}';
 #trial=$trial&bool=$bool&total=$total&returnUrl=$returnUrl&order_id=$order_id"

 $data = array("sslverify" => "true",
"timeout" =>$_GET['timeout'],"userName" => $username,
"password" =>$password, "returnUrl" =>$returnUrl,
"orderNumber" => $order_id,"amount" => $_GET['total']*100,"currency"=>'012',"jsonParams"=>$jsonParams);
//echo $returnUrl;

$fields_string = http_build_query($data);

$ch = curl_init($url);
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_POST, 1);
curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch,CURLOPT_RETURNTRANSFER , true);
$result = curl_exec($ch);
$obj=array();
//print_r($result);
//exit();
$obj = json_decode($result);
curl_close($ch);

if(strval($obj->errorCode)=="0"){
 #   $_REQUEST['orderId']=$obj->orderId;
   # echo $obj->formUrl;
    header("location:".$obj->formUrl);

}else{
   header("location:https://guiddini.com.dz/?MessageReturn=$obj->errorMessage");

}