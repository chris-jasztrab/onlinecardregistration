<?php
include('../../../private/initialize.php');

//pre($_POST);
//pre($_SESSION);

$patronInfo = getAllPatronDetails($_SESSION['patronid']);
//pre($patronInfo);
$patronName = $patronInfo['names']['0'];
$firstName = substr($patronName, strpos($patronName, ' '));
$_SESSION['patronFirstName'] = $firstName;
$lastName = substr($patronName, 0, strpos($patronName, ' '));
$lastNameNoComma = str_replace(",", "", $lastName);
$phoneNumber = $patronInfo['phones'][0]['number'];
//echo $firstName . ' ' . $lastNameNoComma;
$patronEmail = getPatronEmailAddress($_SESSION['patronid']);
//$phoneNumber = $_SESSION['phoneNumber'] ?? '';
$issuingOrganization = $_SESSION['issuingOrganization'] ?? '';
$examLength = $_SESSION['examLength'] ?? '';
$computerRequired = $_SESSION['computerRequired'] ?? '';
$examDate = $_SESSION['examDate'] ?? '';
$_SESSION['patronFirstName'] = $firstName;
$_SESSION['patronLastName'] = $lastNameNoComma;
$_SESSION['patronEmail'] = $patronEmail;
$patronBarcode = $patronInfo['barcodes'][0];
$_SESSION['patronBarcode'] = $patronBarcode;
$tenDaysFromToday = date('m-d-Y', strtotime(' + 10 days'));
$patronPhonenumber = $_SESSION['patronPhonenumber'] ?? '';
$errorsArray = $_SESSION['errors'];
$locationSelect = $_SESSION['slct1'];
$timeSelect = $_SESSION['slct2'];

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

<script type="text/javascript">
    function populate(s1,s2){
        var s1 = document.getElementById(s1);
        var s2 = document.getElementById(s2);
        s2.innerHTML = "";
        if(s1.value == "Main"){
            var optionArray = [
                "|Choose a Time",
                "MON-10:30AM|Monday 10:30AM",
                "MON-1:30PM|Monday 1:30PM",
                "MON-5:30PM|Monday 5:30PM",
                "TUE-10:30AM|Tuesday 10:30AM",
                "TUE-1:30PM|Tuesday 1:30PM",
                "TUE-5:30PM|Tuesday 5:30PM",
                "WED-10:30AM|Wednesday 10:30AM",
                "WED-1:30PM|Wednesday 1:30PM",
                "WED-5:30PM|Wednesday 5:30PM",
                "THU-10:30AM|Thursday 10:30AM",
                "THU-1:30PM|Thursday 1:30PM",
                "THU-5:30PM|Thursday 5:30PM",
                "SATURDAY|Saturday 10:30AM (Subject to availability)"];}

        else if(s1.value == "Sherwood"){
            var optionArray = [
                "|Choose a Time",
                "MON-10:30AM|Monday 10:30AM",
                "MON-1:30PM|Monday 1:30PM",
                "MON-5:30PM|Monday 5:30PM",
                "TUE-10:30AM|Tuesday 10:30AM",
                "TUE-1:30PM|Tuesday 1:30PM",
                "TUE-5:30PM|Tuesday 5:30PM",
                "WED-10:30AM|Wednesday 10:30AM",
                "WED-1:30PM|Wednesday 1:30PM",
                "WED-5:30PM|Wednesday 5:30PM",
                "THU-10:30AM|Thursday 10:30AM",
                "THU-1:30PM|Thursday 1:30PM",
                "THU-5:30PM|Thursday 5:30PM",
                "SATURDAY|Saturday 10:30AM (Subject to availability)"];}

        else if(s1.value == "Beaty"){
            var optionArray = [
                "|Choose a Time",
                "TUE-10:30AM|Tuesday 10:30AM",
                "TUE-1:30PM|Tuesday 1:30PM",
                "TUE-5:30PM|Tuesday 5:30PM",
                "WED-10:30AM|Wednesday 10:30AM",
                "WED-1:30PM|Wednesday 1:30PM",
                "WED-5:30PM|Wednesday 5:30PM",
                "THU-10:30AM|Thursday 10:30AM",
                "THU-1:30PM|Thursday 1:30PM",
                "THU-5:30PM|Thursday 5:30PM",
                "SATURDAY|Saturday 10:30AM (Subject to availability)"];}
        for(var option in optionArray){
            var pair = optionArray[option].split("|");
            var newOption = document.createElement("option");
            newOption.value = pair[0];
            newOption.innerHTML = pair[1];
            s2.options.add(newOption);
        }
    }
</script>


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
        background-color: #ccc;
    }

    /* On mouse-over, add a grey background color */
    .checkcontainer:hover input ~ .checkmark {
        background-color: #ccc;
    }

    /* When the checkbox is checked, add a green background */
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

    .bfh-datepicker-calendar > table.calendar > tbody > tr > td.off {
        background: #cccccc;
        color: #999999;
    }

    .input-group-addon, input-group-btn {
        width: auto;

    }
    body {
        background-image: url('../images/ecardbackground.png');
    }


</style>

    <link href="/assets/bfh/css/bootstrap-formhelpers.css" rel="stylesheet" media="screen">
    <script src="/assets/bfh/js/bootstrap-formhelpers-datepicker.js"></script>
    <script src="/assets/bfh/js/bootstrap-formhelpers-phone.js"></script>
    <script src="/assets/bfh/js/bootstrap-formhelpers-timepicker.js"></script>
    <script src="/assets/bfh/js/bootstrap-formhelpers-number.js"></script>


    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../../../favicon.ico">

    <title>MPL Exam Proctoring Form</title>

    <!-- Bootstrap core CSS -->


    <!-- Custom styles for this template -->

</head>


<body>
<!DOCTYPE html>

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
      <h2>Exam Proctoring Request Form</h2>
                <p class="lead"><font style="color: black"><h3>Please complete the form below and click the Submit Proctoring Request button to submit your proctoring request.  Staff will respond to you within 5 business days of your request.</h3></font>
            <br>
        </p>
    </div>
        </div>
    </div>





    <div class="col-lg-12 ">

        <div class="row">
            <div class="col-xs-1"></div>
            <div class="col-xs-10 transbox" style="color: black">

        <form action="submit_form.php" method="post" autocomplete="off">
            <div class="form-group">

        <div class="row" style="">
            <div class="col-lg-12">

                <?php
                $numberofErrors = sizeof($errorsArray);
                if($numberofErrors > 0) {
                    foreach ($errorsArray as $erroritem) {
                        echo '<H3 style="color: red" align="center">' . $erroritem . '</H3>';
                        echo '<p>';
                    }
                }
                ?>
            </div>
        </div>

        <div class="row" style="">
            <div class="col-lg-6">
                <h4><label>First Name</label>
                    <input type="text" class="input-lg form-control" name="firstName" id="firstName" value="<?php echo $firstName; ?>" disabled>
                </h4>
            </div>
            <div class="col-lg-6">
                <h4><label>Last Name</label>
                    <input type="text" class="form-control" name="lastName" id="lastName" value="<?php echo $lastNameNoComma; ?>" disabled>
                </h4>
            </div>
    </div>
    <div class="row" style="">
        <div class="col-lg-6">
            <h4><label>Email Address</label>
                <br>
                <input type="text" name="emailAddress" class="form-control" id="emailAddress" value='<?php
                if(strlen($patronEmail) > 0)
                {
                    echo $patronEmail . "'";
                    //echo "' disabled";
                }
                if(strlen($patronEmail <= "0"))
                {
                    echo "' required";
                }
                       ''?>>
            </h4>
        </div>
        <div class="col-lg-6">
            <h4><label>Phone Number (Best # to contact you)</label>
                <br>
                <input type="text" id="phoneNumber" name="phoneNumber" class="form-control bfh-phone" data-format="(ddd) ddd-dddd" value="<?php echo $phoneNumber;?>" required>
            </h4>
        </div>
    </div>
<hr>

    <div class="row" style="">
        <div class="col-lg-6">
            <h4><label>Library Card Number</label>
                <input type="text" id="barcode" class="form-control" value="<?php echo $patronBarcode; ?>" disabled>
            </h4>
        </div>

    </div>

<hr>

    <div class="row" style="">

        <div class="col-lg-6">
            <br>
            <h4><label>Organization Issuing the Exam</label>
            <br>
                <input type="text" id="issuingOrganization" name="issuingOrganization" class="form-control" value="<?php echo $issuingOrganization; ?>" required>
            </h4>
        </div>
        <div class="col-lg-6">
            <br>
            <h4><label>Date of Exam</label>
                <div class="bfh-datepicker" style=" background-color : #ffffff; " id="examdate" data-align="right" data-name="examDate" data-date="<?php echo $examDate; ?>" data-min="<?php echo $tenDaysFromToday;?>" data-required>
                </div>
            </h4>
        </div>

    </div>

                <hr>

    <div class="row" style="">

        <div class="col-lg-4">
            <h4><label>Please Choose a Location</label>
                <br>
                <select id="slct1" name="slct1" onfocus="populate(this.id,'slct2')" style="width:24ch; height: 36.5px; font-size: large" required>
                    <option value=""></option>
                    <option value="Main" <?php if($locationSelect == 'Main') {echo 'selected';} ?>>Main</option>
                    <option value="Beaty" <?php if($locationSelect == 'Beaty') {echo 'selected';} ?>>Beaty</option>
                    <option value="Sherwood" <?php if($locationSelect == 'Sherwood') {echo 'selected';} ?>>Sherwood</option>
                </select>
            </h4>
        </div>

        <div class="col-lg-4">
            <h4><label>Please Choose a Time Slot</label>
                <br>
                <select id="slct2" name="slct2" onfocus="populate('slct1','slct2')" style="width:28ch; height: 36.5px; font-size: large" required>
                    <?php
                        if(isset($timeSelect)) {
                            echo '<option value ="' . $timeSelect . '">' . $timeSelect . '</option>';
                        }
                        if(!isset($timeSelect)) {
                            echo '<option value ="">Please choose a location first  </option>';
                    }
                    ?>
                </select>

            </h4>
        </div>

        <div class="col-lg-4">
            <h4><label>Length of Exam</label>
                <div class="form-group">
                    <select name="examlength" class="form-control" id="examlength">
                        <option>ONE HOUR</option>
                        <option>TWO HOURS</option>
                        <option>THREE HOURS</option>
                    </select>
                </div>
                <label>*We are unable to accommodate exams longer than 3 hours </label></h4>
        </div>

    </div>
<hr>

    <div class="row" style="">

        <div class="col-lg-12">
            <h4><label>Please note that Milton Public Library does not provide computers or laptops for online exams. You will be required to bring your own laptop, and are reminded to ensure that all software is up to date. You will have access to our free public wifi for internet purposes. </label>

                <h4><label>I acknowledge that MPL does not provide computers or laptops for online exams. </label>
                    <label class="checkcontainer">I Acknowledge
                        <input type="checkbox" name="computeracknowledge" required>
                        <span class="checkmark"></span>
                    </label>

                </h4>
        </div>
    </div>
    </div>




<hr>



<div class="row" style="">
    <div class="col-lg-12">

        <h4><label>I Have Read and Agree to the <a href="https://www.mpl.on.ca/using-the-library/services" target="_blank">Milton Public Library Proctoring Guidelines</a>   </label>
            <label class="checkcontainer">I Agree
                <input type="checkbox" name="agreeterms" required>
                <span class="checkmark"></span>
            </label>

        </h4>
    </div>
</div>



    <br>
            <input type="submit" name="submit" class="submit action-button btn btn-lg btn-success" value="Submit Proctoring Request"  />

        </form>
            </div>
        </div>
    </div>
</div>
    <footer class="footer">
        <!-- <p>&copy; Milton Public Library <?php echo date("Y"); ?>  </p>-->
    </footer>

</div> <!-- /container -->


</body>


</html>
</body>
</html>
