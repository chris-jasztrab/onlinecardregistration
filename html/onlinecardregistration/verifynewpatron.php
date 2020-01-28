<?php
 include('../../private/initialize.php');
 // ensure people can't just visit this page without having to solve the recaptcha first
if(!isset($_SESSION['notabot'])) {
    header('Location: \index.php');
}

$pcode2result = get_iii_pcode2();
$pcode3result = get_iii_pcode3();
$patrontypesresult = get_patron_types();
$locationresult = get_branch_locations();
$_SESSION['about_to_post'] = 'TRUE';

// check to see if a session variable exists first, if it doesn't them set it to the data posted from the form.php page
// we do this because we use all session variables below to post the patron to the next page.  If there is an issue with their
// applicaton we can auto fill in all the data back into the form from the session variables.
if(!isset($_SESSION['last_name'])) $_SESSION['last_name'] = trim(strtoupper($_POST['last_name']));
if(!isset($_SESSION['first_name'])) $_SESSION['first_name'] = trim(strtoupper($_POST['first_name']));
if(!isset($_SESSION['dob_day'])) $_SESSION['dob_day'] = $_POST['dob_day'];
if(!isset($_SESSION['dob_month'])) $_SESSION['dob_month'] = $_POST['dob_month'];
if(!isset($_SESSION['dob_year'])) $_SESSION['dob_year'] = $_POST['dob_year'];
if(!isset($_SESSION['date_of_birth'])) $_SESSION['date_of_birth'] = $_POST['date_of_birth'];

if(!isset($_SESSION['street'])) $_SESSION['street'] = trim(strtoupper($_POST['street']));
if(!isset($_SESSION['city'])) $_SESSION['city'] = trim(strtoupper($_POST['city']));
if(!isset($_SESSION['province'])) $_SESSION['province'] = trim(strtoupper($_POST['province']));
if(!isset($_SESSION['postalcode'])) $_SESSION['postalcode'] = fixPostalCode(trim(strtoupper($_POST['postalcode'])));
if(!isset($_SESSION['phonenumber'])) $_SESSION['phonenumber'] = trim(strtoupper($_POST['phone']));
if(!isset($_SESSION['email'])) $_SESSION['email'] = trim(($_POST['email']));

if(!isset($_SESSION['pcode2'])) $_SESSION['pcode2'] = $_POST['pcode2'];
if(!isset($_SESSION['pcode3'])) $_SESSION['pcode3'] = $_POST['pcode3'];
if(!isset($_SESSION['homelibrary'])) $_SESSION['homelibrary'] = $_POST['homelibrary'];
if(!isset($_SESSION['marketing_preference'])) $_SESSION['marketing_preference'] = $_POST['marketing_preference'];
if(!isset($_SESSION['notice_preference'])) $_SESSION['notice_preference'] = $_POST['notice_preference'];

//not using this yet.  Eventually we will have an option for parents to apply for kids cards by authenticating their card first.
if(isset($_POST['parentorguardian'])) {
  $_SESSION['parentorguardian'] = $_POST['parentorguardian'];
}

// old code from the internal staff tools application
if(isset($_POST['familycheck']))
{
  $_SESSION['familycheck'] = 'on';
}
else {
  unset($_SESSION['familycheck']);
}

// we determine patron type by age.

// attractive form below to show the patron the data they entered and give them an opportunity to correct it.
// this form gets submitted to patronpostpatron.php yes i am not clever with my filenames.

$patron_type = returnPatronTypeByBirthday($_SESSION['date_of_birth']);
//echo 'patron type from function is: ' . $patron_type;
$_SESSION['patron_type'] = $patron_type;
//pre($_SESSION);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">
    <title>Confirm your information</title>
    <!-- Bootstrap core CSS -->
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--<link href="../../assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">-->
    <!-- Custom styles -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-formhelpers/2.3.0/css/bootstrap-formhelpers.min.css" rel="stylesheet">
    <link href="assets/multistepform/css/style.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>
<style>

    body {
        background-image: url('images/ecardbackground.png');
    }


</style>
<body>
<!-- MultiStep Form -->

    <div class="col-md-6 col-md-offset-3">
        <form id="msform" action="patronpostpatron.php" method="POST">
       <!-- <form id="msform" action="patronpostpatron.php" method="post"> -->
            <fieldset>
                <?php if($_SESSION['addressissue'] == TRUE) { echo '<h1 class="fs-title" style="color:red">There was an error verifying your address - Please correct it and try submitting again</h1>';
                unset($_SESSION['addressissue']);
                }
                ?>
               <h1 class="fs-title">Please Confirm Your Personal Details</h1>
                <!--<button type="button" id="edit_button" value="disable/enable"  class="btn btn-link"><span class="glyphicon glyphicon-edit"></span>&nbsp;&nbsp;Edit</button>-->
                <!--<h2 class="fs-title">Personal Details</h2>-->
                <div class="form_labels">First Name</div>
                  <input type="text" name="fname" id="fname" placeholder="" style="margin-bottom: 10px" value="<?php echo $_SESSION['first_name'];?>" required/>


                <div class="form_labels">Last Name</div>
                <input type="text" name="lname" id="lname" placeholder="" style="margin-bottom: 10px"value="<?php echo $_SESSION['last_name'];?>" required/>


                <div class="form_labels">Date of Birth</div>
                <input type="date" name="date_of_birth_field" id="date_of_birth_field" placeholder="" style="margin-bottom: 10px; height: 50px" value="<?php echo $_SESSION['date_of_birth'];?>" required/>



                <h2 class="fs-title">Address</h2>

                <div class="form_labels">Street Address</div>
                <input type="text" name="street" placeholder="" id="street" style="margin-bottom: 10px" value="<?php echo $_SESSION['street'];?>" required/>


                <div class="form_labels">Town or City</div>
                <input type="text" name="city" placeholder="" id="city" style="margin-bottom: 10px" value="<?php echo $_SESSION['city'];?>" required/>


                <div class="form_labels">Postal Code (A1A 1A1)</div>
                <input type="text" name="postalcode" placeholder="" id="postalcode" pattern="[A-Za-z][0-9][A-Za-z] [0-9][A-Za-z][0-9]" style="margin-bottom: 10px" value="<?php echo $_SESSION['postalcode'];?>" required/>


                <div class="form_labels">Province</div>
                <select class="form-control input-small" name="province" style="height: 50px;" autocomplete="mpl_province_v1.0" id="province" style="margin-bottom: 10px" required/>
                   <option value="AB"<?php
                   if($_SESSION['province'] === 'AB') {
                   echo "selected";}?>>Alberta</option>
                   <option value="BC"<?php
                   if($_SESSION['province'] === 'BC') {
                   echo "selected";}?>>British Columbia</option>
                   <option value="MB"<?php
                   if($_SESSION['province'] === 'MB') {
                   echo "selected";}?>>Manitoba</option>
                   <option value="NB"<?php
                   if($_SESSION['province'] === 'NB') {
                   echo "selected";}?>>New Brunswick</option>
                   <option value="NL"<?php
                   if($_SESSION['province'] === 'NL') {
                   echo "selected";}?>>Newfoundland and Labrador</option>
                   <option value="NT"<?php
                   if($_SESSION['province'] === 'NT') {
                   echo "selected";}?>>Northwest Territories</option>
                   <option value="NS"<?php
                   if($_SESSION['province'] === 'NS') {
                   echo "selected";}?>>Nova Scotia</option>
                   <option value="NU"<?php
                   if($_SESSION['province'] === 'NU') {
                   echo "selected";}?>>Nunavut</option>
                   <option value="ON"<?php
                   if($_SESSION['province'] === 'ON') {
                   echo "selected";}?>>Ontario</option>
                   <option value="PE"<?php
                   if($_SESSION['province'] === 'PE') {
                   echo "selected";}?>>Prince Edward Island</option>
                   <option value="QC"<?php
                   if($_SESSION['province'] === 'QC') {
                   echo "selected";}?>>Quebec</option>
                   <option value="SK"<?php
                   if($_SESSION['province'] === 'SK') {
                   echo "selected";}?>>Saskatchewan</option>
                   <option value="YT"<?php
                   if($_SESSION['province'] === 'YT') {
                   echo "selected";}?>>Yukon</option>
                </select>

                <h2 class="fs-title">Contact Information</h2>

                <div class="form_labels">Email Address</div>
                <input type="email" name="email" id="email" placeholder="" style="margin-bottom: 10px" value="<?php echo $_SESSION['email'];?>" required/>


                <div class="form_labels">Phone Number</div>
                <input type="text" name="phone" id="phone" data-format="(ddd) ddd-dddd" class="bfh-phone" style="margin-bottom: 10px" value="<?php echo $_SESSION['phonenumber'];?>" required />


                <?php //pre($_SESSION); ?>

                <div class="form_labels">Contact Method</div>
                <select class="form-control input-small" name="notice_preference" style="height: 50px; padding: 0px, 0px, 6px, 0px; margin-bottom: 10px" id="notice_preference" required>
                   <option value="z"<?php
                   if($_SESSION['notice_preference'] === 'z') {
                   echo "selected";}?>>Email</option>

                   <option value="p"<?php
                   if($_SESSION['notice_preference'] === 'p') {
                   echo "selected";}?>>Phone</option>
                 </select>


                <h2 class="fs-title">The Other Stuff</h2>
                <div class="form-group">
                <div class="form_labels">Can we send you newsletters to keep you up-to-date on exciting upcoming events, programs and resources?</div>

                <select class="form-control input-small" name="marketing_preference" style="height: 50px" id="marketing_preference" style="margin-bottom: 10px" required>
                   <option value="y"<?php
                   if($_SESSION['marketing_preference'] === 'y') {
                   echo "selected";}?>>Yes</option>
                   <option value="n"<?php
                   if($_SESSION['marketing_preference'] === 'n') {
                   echo "selected";}?>>No</option>
                </select>
              </div>

                 <?php $location_result = get_branch_locations(); ?>
                 <div class="form-group">
                   <div class="form_labels">Home Library</div>
                   <select class="form-control input-small" name="homelibrary" style="height: 46px;" id="homelibrary" style="margin-bottom: 10px">
                     <?php
                       while ($row = pg_fetch_array($location_result)) {
                         echo '<option value="' . $row[0] . '"';
                         if($_SESSION['homelibrary'] == $row[0]){ echo 'selected';}
                         echo '>' . $row[1] . '</option>';
                         lb();
                       }
                       ?>
                    </select>
                 </div>


                 <?php $pcode2_result = get_iii_pcode2();?>
                 <div class="form-group">

                   <div class="form_labels">Additional Lanauages Spoken in the Home</div>
                   <select class="form-control input-small" name="pcode2" style="height: 46px;" id="pcode2" style="margin-bottom: 10px">
                     <?php
                       while ($row = pg_fetch_array($pcode2_result)) {
                         echo '<option value="' . $row[0] . '"';
                         if($_SESSION['pcode2'] == $row[0]){ echo 'selected';}
                         echo '>';
                         if($row[1] == '---') { echo 'None Selected';} else { echo $row[1]; } echo '</option>';
                         lb();
                       }
                       ?>
                    </select>

                 </div>

                 <?php $pcode3_result = get_iii_pcode3();?>
                 <div class="form-group">

                   <div class="form_labels">Additional Lanauages Spoken in the Home</div>
                   <select class="form-control input-small" name="pcode3" style="height: 46px; margin-bottom: 10px" id="pcode3">
                     <?php
                       while ($row = pg_fetch_array($pcode3_result)) {
                         echo '<option value="' . $row[0] . '"';
                         if($_SESSION['pcode3'] == $row[0]){ echo 'selected';}
                         echo '>';
                        if($row[1] == 'Unknown') { echo 'None Selected';} else { echo $row[1]; } echo '</option>';
                         lb();
                       }
                       ?>
                    </select>

                 </div>

                 <button type="submit" id="btnSubmit" class="submit action-button">Submit</button>


            </fieldset>
          </form>


        <!-- link to designify.me code snippets -->
        <div class="dme_link">
          <!--  <p><a href="http://designify.me/code-snippets-js/" target="_blank">More Code Snippets</a></p> -->

        </div>
        <!-- /.link to designify.me code snippets -->

    </div>




<!-- /.MultiStep Form -->

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js'></script>
<script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/multistepform/js/msform.js"></script>
<script src="assets/multistepform/js/bootstrap-formhelpers-phone.js"></script>
<script src="assets/multistepform/js/bootstrap-formhelpers.js"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<!--<script src="../../assets/js/ie10-viewport-bug-workaround.js"></script>-->

<script>

$(document).ready(function () {

        $("#msform").submit(function (e) {

            //disable the submit button
            $("#btnSubmit").prop("disabled", true);
            $("#btnSubmit").css('opacity', '0.6');
            $("#btnSubmit").text('Processing...');
            $("#btnSubmit").prop("disabled", true);
            console.log('testing');
            return true;
        });


    $('#date_of_birth').bfhdatepicker({
        icon:  'glyphicon glyphicon-calendar',
        name: 'date_of_birth',
        format: 'y/m/d',
        input: 'datepick',
        align: 'right'
    });


});


</script>

</body>
</html>


