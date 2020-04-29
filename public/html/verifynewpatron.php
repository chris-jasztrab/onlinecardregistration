<?php
 include('../../private/initialize.php');
 // ensure people can't just visit this page without having to solve the recaptcha first
if(useRecaptcha == '1') {
    if (!isset($_SESSION['notabot'])) {
        header('Location: \index.php');
    }
}

if(!isset($_SESSION['addressissue'])) {
    ($_SESSION['addressissue'] = '');
}

$_SESSION['about_to_post'] = 'TRUE';
// check to see if a session variable exists first, if it doesn't them set it to the data posted from the form.php page
// we do this because we use all session variables below to post the patron to the next page.  If there is an issue with their
// applicaton we can auto fill in all the data back into the form from the session variables.
if(!isset($_SESSION['last_name'])) $_SESSION['last_name'] = trim(strtoupper($_POST['last_name']));
if(!isset($_SESSION['first_name'])) $_SESSION['first_name'] = trim(strtoupper($_POST['first_name']));
if(!isset($_SESSION['date_of_birth'])) $_SESSION['date_of_birth'] = $_POST['date_of_birth'];

if(!isset($_SESSION['street'])) $_SESSION['street'] = trim(strtoupper($_POST['street']));
if(!isset($_SESSION['city'])) $_SESSION['city'] = trim(strtoupper($_POST['city']));
if(!isset($_SESSION['province'])) $_SESSION['province'] = trim(strtoupper($_POST['province']));
if(!isset($_SESSION['postalcode'])) $_SESSION['postalcode'] = fixPostalCode(trim(strtoupper($_POST['postalcode'])));
if(!isset($_SESSION['phonenumber'])) $_SESSION['phonenumber'] = trim(strtoupper($_POST['phone']));
if(!isset($_SESSION['email'])) $_SESSION['email'] = trim(($_POST['email']));

if(!isset($_SESSION['marketing_preference'])) $_SESSION['marketing_preference'] = $_POST['marketing_preference'];
if(!isset($_SESSION['notice_preference'])) $_SESSION['notice_preference'] = $_POST['notice_preference'];

// attractive form below to show the patron the data they entered and give them an opportunity to correct it.
// this form gets submitted to patronpostpatron.php yes i am not clever with my filenames.


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


                <div class="form_labels"><?php if(localization == 'CA') { ?>Postal Code (A1A 1A1)<?php } ?><?php if(localization == 'US') { ?>Zip Code<?php } ?></div>
                <input type="text"
                       name="postalcode"
                       placeholder="" id="postalcode"
                       <?php if(localization == 'CA') { ?>pattern="[A-Za-z][0-9][A-Za-z] [0-9][A-Za-z][0-9]"<?php } ?>
                       <?php if(localization == 'US') { ?>pattern="(\d{5}([\-]\d{4})?)"<?php } ?>
                       style="margin-bottom: 10px"
                       value="<?php echo $_SESSION['postalcode'];?>" required/>


                <div class="form_labels"><?php if(localization == 'CA') { ?> Province <?php } ?> <?php if(localization == 'US') { ?> State <?php } ?>  </div>
                <select class="form-control input-small" name="province" style="height: 50px;" autocomplete="mpl_province_v1.0" id="province" style="margin-bottom: 10px" required/>
                <?php if(localization == 'CA') { ?>
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
                <?php } ?>
                <?php if(localization == 'US') { ?>
                    <option value="AL"<?php if($_SESSION['province'] === 'AL') {echo "selected";}?>>Alabama</option>
                    <option value="AK"<?php if($_SESSION['province'] === 'AK') {echo "selected";}?>>Alaska</option>
                    <option value="AZ"<?php if($_SESSION['province'] === 'AZ') {echo "selected";}?>>Arizona</option>
                    <option value="AR"<?php if($_SESSION['province'] === 'AR') {echo "selected";}?>>Arkansas</option>
                    <option value="CA"<?php if($_SESSION['province'] === 'CA') {echo "selected";}?>>California</option>
                    <option value="CO"<?php if($_SESSION['province'] === 'CO') {echo "selected";}?>>Colorado</option>
                    <option value="CT"<?php if($_SESSION['province'] === 'CT') {echo "selected";}?>>Connecticut</option>
                    <option value="DE"<?php if($_SESSION['province'] === 'DE') {echo "selected";}?>>Delaware</option>
                    <option value="DC"<?php if($_SESSION['province'] === 'DC') {echo "selected";}?>>District Of Columbia</option>
                    <option value="FL"<?php if($_SESSION['province'] === 'FL') {echo "selected";}?>>Florida</option>
                    <option value="GA"<?php if($_SESSION['province'] === 'GA') {echo "selected";}?>>Georgia</option>
                    <option value="HI"<?php if($_SESSION['province'] === 'HI') {echo "selected";}?>>Hawaii</option>
                    <option value="ID"<?php if($_SESSION['province'] === 'ID') {echo "selected";}?>>Idaho</option>
                    <option value="IL"<?php if($_SESSION['province'] === 'IL') {echo "selected";}?>>Illinois</option>
                    <option value="IN"<?php if($_SESSION['province'] === 'IN') {echo "selected";}?>>Indiana</option>
                    <option value="IA"<?php if($_SESSION['province'] === 'IA') {echo "selected";}?>>Iowa</option>
                    <option value="KS"<?php if($_SESSION['province'] === 'KS') {echo "selected";}?>>Kansas</option>
                    <option value="KY"<?php if($_SESSION['province'] === 'KY') {echo "selected";}?>>Kentucky</option>
                    <option value="LA"<?php if($_SESSION['province'] === 'LA') {echo "selected";}?>>Louisiana</option>
                    <option value="ME"<?php if($_SESSION['province'] === 'ME') {echo "selected";}?>>Maine</option>
                    <option value="MD"<?php if($_SESSION['province'] === 'MD') {echo "selected";}?>>Maryland</option>
                    <option value="MA"<?php if($_SESSION['province'] === 'MA') {echo "selected";}?>>Massachusetts</option>
                    <option value="MI"<?php if($_SESSION['province'] === 'MI') {echo "selected";}?>>Michigan</option>
                    <option value="MN"<?php if($_SESSION['province'] === 'MN') {echo "selected";}?>>Minnesota</option>
                    <option value="MS"<?php if($_SESSION['province'] === 'MS') {echo "selected";}?>>Mississippi</option>
                    <option value="MO"<?php if($_SESSION['province'] === 'MO') {echo "selected";}?>>Missouri</option>
                    <option value="MT"<?php if($_SESSION['province'] === 'MT') {echo "selected";}?>>Montana</option>
                    <option value="NE"<?php if($_SESSION['province'] === 'NE') {echo "selected";}?>>Nebraska</option>
                    <option value="NV"<?php if($_SESSION['province'] === 'NV') {echo "selected";}?>>Nevada</option>
                    <option value="NH"<?php if($_SESSION['province'] === 'NH') {echo "selected";}?>>New Hampshire</option>
                    <option value="NJ"<?php if($_SESSION['province'] === 'NJ') {echo "selected";}?>>New Jersey</option>
                    <option value="NM"<?php if($_SESSION['province'] === 'NM') {echo "selected";}?>>New Mexico</option>
                    <option value="NY"<?php if($_SESSION['province'] === 'NY') {echo "selected";}?>>New York</option>
                    <option value="NC"<?php if($_SESSION['province'] === 'NC') {echo "selected";}?>>North Carolina</option>
                    <option value="ND"<?php if($_SESSION['province'] === 'ND') {echo "selected";}?>>North Dakota</option>
                    <option value="OH"<?php if($_SESSION['province'] === 'OH') {echo "selected";}?>>Ohio</option>
                    <option value="OK"<?php if($_SESSION['province'] === 'OK') {echo "selected";}?>>Oklahoma</option>
                    <option value="OR"<?php if($_SESSION['province'] === 'OR') {echo "selected";}?>>Oregon</option>
                    <option value="PA"<?php if($_SESSION['province'] === 'PA') {echo "selected";}?>>Pennsylvania</option>
                    <option value="RI"<?php if($_SESSION['province'] === 'RI') {echo "selected";}?>>Rhode Island</option>
                    <option value="SC"<?php if($_SESSION['province'] === 'SC') {echo "selected";}?>>South Carolina</option>
                    <option value="SD"<?php if($_SESSION['province'] === 'SD') {echo "selected";}?>>South Dakota</option>
                    <option value="TN"<?php if($_SESSION['province'] === 'TN') {echo "selected";}?>>Tennessee</option>
                    <option value="TX"<?php if($_SESSION['province'] === 'TX') {echo "selected";}?>>Texas</option>
                    <option value="UT"<?php if($_SESSION['province'] === 'UT') {echo "selected";}?>>Utah</option>
                    <option value="VT"<?php if($_SESSION['province'] === 'VT') {echo "selected";}?>>Vermont</option>
                    <option value="VA"<?php if($_SESSION['province'] === 'VA') {echo "selected";}?>>Virginia</option>
                    <option value="WA"<?php if($_SESSION['province'] === 'WA') {echo "selected";}?>>Washington</option>
                    <option value="WV"<?php if($_SESSION['province'] === 'WV') {echo "selected";}?>>West Virginia</option>
                    <option value="WI"<?php if($_SESSION['province'] === 'WI') {echo "selected";}?>>Wisconsin</option>
                    <option value="WY"<?php if($_SESSION['province'] === 'WY') {echo "selected";}?>>Wyoming</option>
                <?php } ?>
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


