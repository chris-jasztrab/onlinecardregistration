<?php
include('../../../private/initialize.php');
$barcode = '';
$pin = '';

if (is_post_request()) {
    $barcode = $_POST['barcode'] ?? '';
    $pin = $_POST['pin'] ?? '';

    // if there were no errors, try to login
    // Using one variable ensures that msg is the same
    $login_failure_msg = "Log in was unsuccessful.";
    $result = validatePatron($barcode, $pin);
    if($result == NULL) {
        $patronID = findPatronIDByBarcode($barcode);
        if ($patronID) {
            $_SESSION['patronid'] = $patronID;
            log_in_patron($patronID);
            redirect_to('proctor_form.php');
            //echo 'logged in';
        }
    }

    else {
        // username found, but password does not match
        $_SESSION['failedlogin'] = '1';
        redirect_to('/proctoring/auth_form.php');
        //echo 'failed login';
    }
}

session_destroy();
?>

<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
<script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<style>
    .checkcontainer {
        display: block;
        position: relative;
        padding-left: 35px;
        margin-bottom: 12px;
        cursor: pointer;
        font-size: 22px;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    /* Hide the browser's default checkbox */
    .checkcontainer input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    /* Create a custom checkbox */
    .checkmark {
        position: absolute;
        top: 0;
        left: 0;
        height: 25px;
        width: 25px;
        background-color: #eee;
    }

    /* On mouse-over, add a grey background color */
    .checkcontainer:hover input ~ .checkmark {
        background-color: #ccc;
    }

    /* When the checkbox is checked, add a blue background */
    .checkcontainer input:checked ~ .checkmark {
        background-color: #009933;
    }

    /* Create the checkmark/indicator (hidden when not checked) */
    .checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }

    /* Show the checkmark when checked */
    .checkcontainer input:checked ~ .checkmark:after {
        display: block;
    }

    /* Style the checkmark/indicator */
    .checkcontainer .checkmark:after {
        left: 9px;
        top: 5px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 3px 3px 0;
        -webkit-transform: rotate(45deg);
        -ms-transform: rotate(45deg);
        transform: rotate(45deg);
    }
    .form-control {
        font-size: large;
    }

    .submit {
        background-color: #006368;

    }

    body {
        background-image: url('../images/ecardbackground.png');
    }


</style>

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
    <link href="../assets/multistepform/css/style.css" rel="stylesheet">
</head>

<body>

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
                <h1 style="font-weight: bold">Exam Proctoring Request Form</h1>
                <p class="lead">  <h2>Milton Public Library supports lifelong learning, and offers an exam proctoring service for a fee of $40 plus HST based on the library’s availability and staffing.<p><p> An assigned staff member will check on students periodically, but currently does not invigilate exams.<p> It is the sole responsibility of the student to ensure that the educational institution is supportive of MPL's approach prior to booking an exam proctor appointment.


                    <br><br>
                <span style="font-weight: bold;">Please review our <a href="https://beinspiredatmpl.ca/using-the-library/services">guidelines</a> carefully as they have recently changed.</span>
                 </h2></p>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-1"></div>
            <div class="col-xs-10 transbox" style="color: black">
                <form style="" action="auth_form.php" method="post" autocomplete="off">
                    <div class="form-group">
                        <?php if($_SESSION['failedlogin'] == '1')
                        {
                            echo ' <div class="row" style="">
                                <div class="col-lg-8">
                                    <font color="red"> <h3>Invalid Login - Please check your library card number and pin and try again.</label></h3></font>
                                </div>
                            </div>';
                            lb();
                        } ?>
                        <div class="row" style="">
                            <h3>Please login with your library card # and PIN.</h3>
                        </div>
                        <br>
                        <div class="row" style="">
                            <div class="col-lg-6">
                                <h4><label>Library Card Number</label>
                                    <input type="text" class="form-control" name="barcode">
                                </h4>
                            </div>
                            <div class="col-lg-6">
                                <h4><label>PIN</label>
                                    <input type="password" class="form-control" name="pin">
                                </h4>
                            </div>
                        </div>
                        <br>
                        <input type="submit" name="submit" class="submit action-button btn btn-lg btn-success"  value="Login"/>
                    </div>
                </form>
                <footer class="footer">
                    <p>&copy; Milton Public Library <?php echo date("Y"); ?>  </p>
                </footer>
            </div>
        </div>
    </div>
</div>



</body>
</html>


