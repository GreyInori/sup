<?



include "phpqrcode.php";    
$mmshowmss=$_GET["ewmcode"];
$errorCorrectionLevel = 'H';  // L M Q H
$matrixPointSize = 10;    //1-10
$datas= $mmshowmss;
header( "Content-type: image/jpeg");
QRcode::png($datas,false, $errorCorrectionLevel, $matrixPointSize, 1);    


?>

