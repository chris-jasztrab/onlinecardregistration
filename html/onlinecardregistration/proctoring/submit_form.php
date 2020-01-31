<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
include('../../../private/initialize.php');
include('../../../private/PHPMailer.php');
//pre($_POST);
//pre($_SESSION);
$_SESSION['phoneNumber'] =  $_POST['phoneNumber'];
$_SESSION['examDate'] = $_POST['examDate'];
$_SESSION['slct1'] = $_POST['slct1'];
$_SESSION['slct2'] = $_POST['slct2'];
$_SESSION['examlength'] = $_POST['examlength'];
$_SESSION['computeracknowledge'] = $_POST['computeracknowledge'];
$_SESSION['issuingOrganization'] = $_POST['issuingOrganization'];


$_SESSION['errors'] = [];
if(is_blank($_POST['examDate'])) {
    array_push($_SESSION['errors'], 'You need to enter an exam date.');
}

if(sizeof($_SESSION['errors']) > 0) {
    redirect_to('proctor_form.php');
}
?>


<html>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
<script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>


<head>


    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../../../favicon.ico">

    <title>MPL Exam Proctoring Form</title>

    <!-- Bootstrap core CSS -->


    <!-- Custom styles for this template -->
    <link href="narrow-jumbotron.css" rel="stylesheet">
    <link href="../assets/multistepform/css/style.css" rel="stylesheet">

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../../../favicon.ico">

    <title>MPL Exam Proctoring Form</title>

    <!-- Bootstrap core CSS -->
    <link href="../../css/editor.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="narrow-jumbotron.css" rel="stylesheet">
<style>body {
        background-image: url('../images/ecardbackground.png');
    }
</style>
</head>

<body>
<!DOCTYPE html>

<center><img src="../images/beetrail.png" width="30%"  alt="ecard Title"/> </center>

<div class="container">
    <div class="header clearfix">
        <nav>
            <ul class="nav nav-pills float-right">
                <li class="nav-item">

                </li>
                <li class="nav-item">

                </li>
                <li class="nav-item">

                </li>
            </ul>
        </nav>

    </div>
    <div class="col-lg-12 ">

        <div class="row">
            <div class="col-xs-1"></div>
            <div class="col-xs-10 transbox" style="color: black">

                <h1>Thank-you for your request</h1>

            <p class="lead"><h2><?php echo $_SESSION['patronFirstName'] . ' ' . $_SESSION['patronLastName'];  ?> thank-you for your exam proctoring request. Milton Public Library staff will contact you within a week of your request.  You will also receive a confirmation email with the details you provided today.  Please check to ensure that you received this email.
                <br>
                <br>
                Return to the <a href="https://www.beinspiredatmpl.ca">Milton Public Library homepage.</a>
                <br>
                <br>
                <span style="font-weight: bold;">Please review our <a href="https://beinspiredatmpl.ca/using-the-library/services">guidelines</a> carefully as they have recently changed.</span>
            </p>

        </div>


        </div>
    </div>


    </div> <!-- /container -->
<?php

$bodytext = "This is a confirmation receipt of your recent exam proctoring request.  When submitting you provided the following information: <br><br>";
$bodytext .= "PATRON NAME: " . $_SESSION['patronFirstName'] . " " . $_SESSION['patronLastName'] . "<br>";
$bodytext .= "EMAIL ADDRESS: " . $_SESSION['patronEmail'] . "<br>";
$bodytext .= "PHONE NUMBER: " . $_SESSION['phoneNumber'] . "<br>";
$bodytext .= "LIBRARY CARD NUMBER: " . $_SESSION['patronBarcode'] . "<br>";
$bodytext .= "LOCATION CHOSEN: " . $_SESSION['slct1'] . "<br>";
$bodytext .= "TIME CHOSEN: " . $_SESSION['slct2'] . "<br>";
$bodytext .= "ISSUING ORGANIZATION: " . $_SESSION['issuingOrganization'] . "<br>";
$bodytext .= "EXAM LENGTH: " . $_SESSION['examlength'] . "<br>";
$bodytext .= "<br><br>";
$bodytext .= "Please note that this email only serves as a confirmation that MPL has received your exam proctoring request.  A staff member will follow up with you within 5 business days to go over your request and provide a final confirmation.";

$email = new PHPMailer\PHPMailer\PHPMailer();
$email->IsHTML(true);
$email->SetFrom('examproctor@beinspiredatmpl.ca', 'MPL Exam Proctoring'); //Name is optional
$email->Subject   = 'Confirmation of your proctoring request';
$email->Body      = $bodytext;
$email->AddAddress($_SESSION['patronEmail']);
$email->addBcc("exams@beinspiredatmpl.ca");

return $email->Send();


//session_destroy(); ?>

</body>
</html>

