<?php
include('../../private/initialize.php');
// again we pull in the initialize file so we can access all our libraries.  This matters on this page because we are using a lot of
// functions to access Sierra via the API to pull down PCODE values, locations, etc.
// set a session variable right from the start saying the form is not complete.  We check for this later.

$_SESSION['form_complete'] = "no";
// check to see that they actually came to this page from our start page.  This ensures they went through the Google reCAPTCHA check.
if(isset($_POST['g-recaptcha-response']))  {
    if($_POST['g-recaptcha-response'] != NULL) {
        // Set a session variable that they are not a bot.
        $_SESSION['notabot'] = TRUE;
    }
}
// if the notabot session isn't set, force them back to the index page so they have to go through the reCAPTCHA
// thank-you to azmind.com for the multi step form html code.  It makes it easy for a patron to walk through creating a patron record.
// this follows the switch inside the config file.  Only redirects if it's set to 1.
if(useRecaptcha == '1') {
    if (!isset($_SESSION['notabot'])) {
        header('Location: index.php');
    }
}
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
    <!--<link rel="icon" href="../../favicon.ico">-->
    <title>MPL Online Card Registration</title>
    <!-- Bootstrap core CSS -->
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <!--<link href="../../assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">-->
    <!-- Custom styles -->
    <link href="assets/multistepform/css/style.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-formhelpers/2.3.0/css/bootstrap-formhelpers.min.css" rel="stylesheet">


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]-->
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- I am pulling in my site key from the config file -->
    <script src="https://www.google.com/recaptcha/api.js?render=<?php echo captcha_site_key; ?>"></script>



</head>
<body background="images/ecardbackground.png">


<!-- MultiStep Form -->

    <div class="col-md-6 col-md-offset-3">
        <form id="msform" action="verifynewpatron.php" method="post" autocomplete="MPLFORM2019_fhdusjandl">
            <!-- progressbar -->
            <!--    <ul id="progressbar">
                    <li class="active">Personal Details</li>
                    <li>Address Information</li>
                    <li>Contact Information</li>
                    <li>The Other Stuff</li>
                </ul> -->
            <!-- fieldsets -->
            <br><br><br><br>
<fieldset>

                <h2 class="fs-title">Personal Details</h2>
                <h3 class="fs-subtitle">Please provide your name and date of birth</h3>
                <div class="form_labels">First Name</div>
                <input type="text" name="first_name" placeholder="" autocomplete="off" style="margin-bottom: 10px"/>
                <div class="form_labels">Last Name</div>
                <input type="text" name="last_name" placeholder="" id="last_name" autocomplete="mpl_lname_v1.0" style="margin-bottom: 10px"/>
                <div class="form_labels">Date of Birth</div>
                <div class="bfh-datepicker" id="date_of_birth" autocomplete="mpl_dob_v1.0" data-format="y-m-d" name="date_of_birth"></div>


                <h2 class="fs-title">Address</h2>
                <?php if(localization == 'CA') { ?>
                    <h3 class="fs-subtitle"><?php if(localization == 'CA') { ?> Please provide your address and postal code <?php } ?><?php if(localization == 'US') { ?> Please provide your address and zip code <?php } ?></h3>
                <?php } ?>
                <?php if(localization == 'US') { ?>
                    <h3 class="fs-subtitle">Please provide your address and zip code</h3>
                <?php } ?>

                <div class="form_labels">Street Address</div>
                <input type="text" name="street" placeholder="" autocomplete="mpl_street_v1.0" style="margin-bottom: 10px"/>
                <div class="form_labels">Town or City</div>
                <input type="text" name="city" placeholder="" autocomplete="mpl_city_v1.0" style="margin-bottom: 10px"/>
                <div class="form_labels"><?php if(localization == 'CA') { ?> Province <?php } ?> <?php if(localization == 'US') { ?> State <?php } ?> </div>
                <div class="form-group">
                    <div class="dropdown">
                        <select class="form-control input-small" name="province" autocomplete="mpl_province_v1.0" style="height: 50px" id="province" style="margin-bottom: 10px">
                            <?php if(localization == 'CA') { ?>
                                <option value="AB"<?php if(yourprovince == 'AB') {echo "selected";}?>>Alberta</option>
                                <option value="BC"<?php if(yourprovince == 'BC') {echo "selected";}?>>British Columbia</option>
                                <option value="MB"<?php if(yourprovince == 'MB') {echo "selected";}?>>Manitoba</option>
                                <option value="NB"<?php if(yourprovince == 'NB') {echo "selected";}?>>New Brunswick</option>
                                <option value="NL"<?php if(yourprovince == 'NL') {echo "selected";}?>>Newfoundland and Labrador</option>
                                <option value="NT"<?php if(yourprovince == 'NT') {echo "selected";}?>>Northwest Territories</option>
                                <option value="NS"<?php if(yourprovince == 'NS') {echo "selected";}?>>Nova Scotia</option>
                                <option value="NU"<?php if(yourprovince == 'NU') {echo "selected";}?>>Nunavut</option>
                                <option value="ON"<?php if(yourprovince == 'ON') {echo "selected";}?>>Ontario</option>
                                <option value="PE"<?php if(yourprovince == 'PE') {echo "selected";}?>Prince Edward Island</option>
                                <option value="QC"<?php if(yourprovince == 'QC') {echo "selected";}?>>Quebec</option>
                                <option value="SK"<?php if(yourprovince == 'SK') {echo "selected";}?>>Saskatchewan</option>
                                <option value="YT"<?php if(yourprovince == 'YT') {echo "selected";}?>>Yukon</option>
                            <?php } ?>
                            <?php if(localization == 'US') { ?>
                                <option value="AL"<?php if(yourprovince == 'AL') {echo "selected";}?>>Alabama</option>
                                <option value="AK"<?php if(yourprovince == 'AK') {echo "selected";}?>>Alaska</option>
                                <option value="AZ"<?php if(yourprovince == 'AZ') {echo "selected";}?>>Arizona</option>
                                <option value="AR"<?php if(yourprovince == 'AR') {echo "selected";}?>>Arkansas</option>
                                <option value="CA"<?php if(yourprovince == 'CA') {echo "selected";}?>>California</option>
                                <option value="CO"<?php if(yourprovince == 'CO') {echo "selected";}?>>Colorado</option>
                                <option value="CT"<?php if(yourprovince == 'CT') {echo "selected";}?>>Connecticut</option>
                                <option value="DE"<?php if(yourprovince == 'DE') {echo "selected";}?>>Delaware</option>
                                <option value="DC"<?php if(yourprovince == 'DC') {echo "selected";}?>>District Of Columbia</option>
                                <option value="FL"<?php if(yourprovince == 'FL') {echo "selected";}?>>Florida</option>
                                <option value="GA"<?php if(yourprovince == 'GA') {echo "selected";}?>>Georgia</option>
                                <option value="HI"<?php if(yourprovince == 'HI') {echo "selected";}?>>Hawaii</option>
                                <option value="ID"<?php if(yourprovince == 'ID') {echo "selected";}?>>Idaho</option>
                                <option value="IL"<?php if(yourprovince == 'IL') {echo "selected";}?>>Illinois</option>
                                <option value="IN"<?php if(yourprovince == 'IN') {echo "selected";}?>>Indiana</option>
                                <option value="IA"<?php if(yourprovince == 'IA') {echo "selected";}?>>Iowa</option>
                                <option value="KS"<?php if(yourprovince == 'KS') {echo "selected";}?>>Kansas</option>
                                <option value="KY"<?php if(yourprovince == 'KY') {echo "selected";}?>>Kentucky</option>
                                <option value="LA"<?php if(yourprovince == 'LA') {echo "selected";}?>>Louisiana</option>
                                <option value="ME"<?php if(yourprovince == 'ME') {echo "selected";}?>>Maine</option>
                                <option value="MD"<?php if(yourprovince == 'MD') {echo "selected";}?>>Maryland</option>
                                <option value="MA"<?php if(yourprovince == 'MA') {echo "selected";}?>>Massachusetts</option>
                                <option value="MI"<?php if(yourprovince == 'MI') {echo "selected";}?>>Michigan</option>
                                <option value="MN"<?php if(yourprovince == 'MN') {echo "selected";}?>>Minnesota</option>
                                <option value="MS"<?php if(yourprovince == 'MS') {echo "selected";}?>>Mississippi</option>
                                <option value="MO"<?php if(yourprovince == 'MO') {echo "selected";}?>>Missouri</option>
                                <option value="MT"<?php if(yourprovince == 'MT') {echo "selected";}?>>Montana</option>
                                <option value="NE"<?php if(yourprovince == 'NE') {echo "selected";}?>>Nebraska</option>
                                <option value="NV"<?php if(yourprovince == 'NV') {echo "selected";}?>>Nevada</option>
                                <option value="NH"<?php if(yourprovince == 'NH') {echo "selected";}?>>New Hampshire</option>
                                <option value="NJ"<?php if(yourprovince == 'NJ') {echo "selected";}?>>New Jersey</option>
                                <option value="NM"<?php if(yourprovince == 'NM') {echo "selected";}?>>New Mexico</option>
                                <option value="NY"<?php if(yourprovince == 'NY') {echo "selected";}?>>New York</option>
                                <option value="NC"<?php if(yourprovince == 'NC') {echo "selected";}?>>North Carolina</option>
                                <option value="ND"<?php if(yourprovince == 'ND') {echo "selected";}?>>North Dakota</option>
                                <option value="OH"<?php if(yourprovince == 'OH') {echo "selected";}?>>Ohio</option>
                                <option value="OK"<?php if(yourprovince == 'OK') {echo "selected";}?>>Oklahoma</option>
                                <option value="OR"<?php if(yourprovince == 'OR') {echo "selected";}?>>Oregon</option>
                                <option value="PA"<?php if(yourprovince == 'PA') {echo "selected";}?>>Pennsylvania</option>
                                <option value="RI"<?php if(yourprovince == 'RI') {echo "selected";}?>>Rhode Island</option>
                                <option value="SC"<?php if(yourprovince == 'SC') {echo "selected";}?>>South Carolina</option>
                                <option value="SD"<?php if(yourprovince == 'SD') {echo "selected";}?>>South Dakota</option>
                                <option value="TN"<?php if(yourprovince == 'TN') {echo "selected";}?>>Tennessee</option>
                                <option value="TX"<?php if(yourprovince == 'TX') {echo "selected";}?>>Texas</option>
                                <option value="UT"<?php if(yourprovince == 'UT') {echo "selected";}?>>Utah</option>
                                <option value="VT"<?php if(yourprovince == 'VT') {echo "selected";}?>>Vermont</option>
                                <option value="VA"<?php if(yourprovince == 'VA') {echo "selected";}?>>Virginia</option>
                                <option value="WA"<?php if(yourprovince == 'WA') {echo "selected";}?>>Washington</option>
                                <option value="WV"<?php if(yourprovince == 'WV') {echo "selected";}?>>West Virginia</option>
                                <option value="WI"<?php if(yourprovince == 'WI') {echo "selected";}?>>Wisconsin</option>
                                <option value="WY"<?php if(yourprovince == 'WY') {echo "selected";}?>>Wyoming</option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form_labels"><?php if(localization == 'CA') { ?> Postal Code <?php } ?><?php if(localization == 'US') { ?> Zip Code <?php } ?></div>
                <input type="text" name="postalcode" placeholder="" autocomplete="mpl_postal_v1.0" style="margin-bottom: 10px"/>


                <h2 class="fs-title">Contact Information</h2>
                <h3 class="fs-subtitle">Please provide your email address, phone number, and how you would like to be contacted. See our <p><a href="https://www.mpl.on.ca/policy" target="_blank">privacy policy</a> to learn how we use this information.</h3>
                <div class="form_labels">Email Address</div>
                <input type="text" name="email" placeholder="" autocomplete="mpl_email_v1.0" style="margin-bottom: 10px"/>
                <div class="form_labels">Phone Number</div>
                <input type="text" name="phone" data-format="(ddd) ddd-dddd" autocomplete="mpl_phone_v1.0" class="form-control bfh-phone form-control input-lg" style="margin-bottom: 10px" />
                <div class="form_labels">Contact Method</div>
                <select class="form-control input-small" name="notice_preference" autocomplete="mpl_notice_v1.0" style="height: 50px; padding: 0px, 0px, 6px, 0px; margin-bottom: 10px" id="notice_preference" placeholder="Contact Preference">
                    <option value="z">Email</option>
                    <option value="p">Phone</option>
                </select>


                <h2 class="fs-title">The Other Stuff</h2>
                <h3 class="fs-subtitle">Please let us know the following</h3>

                <div class="form_labels">Can we send you newsletters to keep you up-to-date on exciting upcoming events, programs and resources?</div>
                <div class="form-group">
                    <div class="dropdown">
                        <select class="form-control input-small" name="marketing_preference" autocomplete="mpl_marketing_v1.0" style="height: 50px" id="marketing_preference" style="margin-bottom: 10px">
                            <option value="y" selected>Yes</option>
                            <option value="n">No</option>
                        </select>
                    </div>
                </div>


                <input type="button" name="previous" class="previous action-button-previous" value="Previous"/>

                <input type="submit" name="submit" class="submit action-button" value="Submit"/>


            <!-- link to designify.me code snippets -->
            <div class="dme_link">
                <!--  <p><a href="http://designify.me/code-snippets-js/" target="_blank">More Code Snippets</a></p> -->

            </div>
            <!-- /.link to designify.me code snippets -->

    </div>



<?php

//$files = glob("/var/www/html/onlinecardregistration/21387*");
//$now   = time();
//foreach ($files as $file) {
//    if (is_file($file)) {
//        if ($now - filemtime($file) >= 60 * 60 * 2) { // 2 hours
//            unlink($file);
//        }
//    }
//}

?>

<!-- /.MultiStep Form -->

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js'></script>
<script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
<script src="assets/multistepform/js/bootstrap-formhelpers.js"
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="assets/multistepform/js/msform.js"></script>
<script src="assets/multistepform/js/bootstrap-formhelpers-phone.js"></script>

<!-- IE10 viewport hack for Surface/desktop Windows 8 buggy -->
<!--<script src="../../assets/js/ie10-viewport-bug-workaround.js"></script>-->
<!-- Code for the datepicker -->
<script>
    $('#date_of_birth').bfhdatepicker({
        icon:  'glyphicon glyphicon-calendar',
        name: 'date_of_birth',
        format: 'y-m-d',
        input: 'datepick',
        align: 'right'
    });


    $('#msform').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });


</script>

</form>

<script>
    grecaptcha.ready(function() {
        grecaptcha.execute('<?php echo captcha_site_key; ?>', {action: 'homepage'}).then(function(token) {
        ...
        });
    });
</script>


</body>



</body>
</html>


<?php
// as part of the patron creation process we are actually creating a library card with a barcode and background image
// this gets created and rendered on the server, then shown to the patron and emailed to them.  The function below
// cleans up these files and erases ones that are over 2 hours old.
delete_oldfiles('../../private/', 86400, barcodePrefix);
?>
