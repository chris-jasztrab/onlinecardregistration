<?php

include('../../private/initialize.php');
?>
<html>
<head>
    <script
        src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous">

    </script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.js"></script>


    <script>
        $(document).ready( function () {
            $('#table_id').DataTable( {
                dom: 'Bfrtip',
                buttons: ['copy', 'excel', 'pdf']
            });
        } );

    </script>
</head>

<?php
if($_GET['secretphrase'] == 'ShowTheStats') {
lb();
echo 'Number of people who have renewed online: ' . getNumberOfOnlineRenewals();
lb();
echo 'Number of people who have move blocks: ' . getNumberOfMoveBlocked();
lb();
echo 'Number of people who we can market to: ' . getNumberOfMarketingTargets();
lb();
echo 'Number of people who have registered online: ' . getNumberOfOnlineRegistrations();
lb();

echo 'Here is a list of online e-card registrations: ';
lb();
echo '<hr>';
echo '<table id="table_id" class="display">
    <thead>
        <tr>
            <th>Name</th>
            <th>Barcode</th>
            <th>Email</th>
            <td>Is Duplicate</td>
        </tr>
    </thead>
    <tbody>';
$onlineRegistrations = getOnlineRegistrations();
foreach($onlineRegistrations['entries'] as $patronInfo)
    {
        echo '<tr>';
        //pre($patronInfo);
        $strippedID = stripped($patronInfo['link']);
        //echo $strippedID;
        $patronDetails = getPatronDetails($strippedID);
        $emailResult = findPatronIDByEmail($patronDetails['emails'][0]);
        $num = count($emailResult['entries']);
        $patronName = $patronDetails[names][0];
        if($num > 1) { echo '<font color="red">';}
        echo '<td>' . $patronName . '</td>';
        echo '<td><a href="https://www.google.ca" target="_blank">' . $patronDetails['barcodes'][0] . '</a></td>';
        echo '<td>' . $patronDetails['emails'][0] . '</td>';
        if($num > 1) { echo '<td>YES</td>';}
        else { echo '<td>NO</td>';}
    }
//pre($onlineRegistrations);

}

?>

</html>

