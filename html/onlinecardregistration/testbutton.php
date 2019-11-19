<?php
    echo 'test';

    include('../../private/initialize.php');

    $myid = findPatronIDByBarcode('21387001464003');
    echo $myid;

    lb();
echo 'test2';
lb();
$result = isPatronLocal($myid);
$mycountry = 'CA';
$mycity = 'Cambridge';
$mystreet = '210 Northview Heights Drive';
$mypostal = 'N1R8C6';



?>
