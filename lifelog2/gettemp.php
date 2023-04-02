<?php

//header("Access-Control-Allow-Origin: *");
//echo file_get_contents("http://192.168.1.220:45890/gettemp.php");

date_default_timezone_set("America/Vancouver");

$json = file_get_contents('http://192.168.1.23/gettemp.php');
$obj = json_decode($json);

$t=time();
unset ($obj->Sensoren[0]->Timstamp); //typo in ESP program!
$obj->Sensoren[0]->Timestamp=$t;
$obj->Sensoren[0]->Datum=date("d.m.Y H:i:s",$t);
$obj->Sensoren[0]->Sensor="Vancouver au&szlig;en";
unset ($obj->Sensoren[1]->Timstamp); //typo in ESP program!
$obj->Sensoren[1]->Timestamp=$t;
$obj->Sensoren[1]->Datum=date("d.m.Y H:i:s",$t);
$obj->Sensoren[1]->Sensor="Vancouver Wohnbereich";

$json2 = file_get_contents('http://192.168.90.240:8002');
$obj2 = json_decode($json2);

$temp3=array();
$temp3["Sensor"]="Vancouver Badezimmer";
$temp3["Timestamp"]=$obj->Sensoren[0]->Timestamp;
$temp3["Datum"]=$obj->Sensoren[0]->Datum;
$temp3["temp"]=$obj2->temp3;
$obj->Sensoren[]=$temp3;

$temp4=array();
$temp4["Sensor"]="Vancouver Schlafzimmer";
$temp4["Timestamp"]=$obj->Sensoren[0]->Timestamp;
$temp4["Datum"]=$obj->Sensoren[0]->Datum;
$temp4["temp"]=$obj2->temp4;
$obj->Sensoren[]=$temp4;

$obj->Sensoren[1]->Sensor="Vancouver Wohnbereich";


header("Access-Control-Allow-Origin: *");
header("Content-type: Application/json");
echo json_encode($obj);
?>
