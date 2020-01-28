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

if(!isset($_SESSION['notabot'])) {
    header('Location: \index.php');
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


    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-formhelpers/2.3.0/css/bootstrap-formhelpers.min.css" rel="stylesheet">
    <link href="assets/multistepform/css/style.css" rel="stylesheet">

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
                <input type="button" name="next" class="next action-button" value="Next"/>
            </fieldset>
            <fieldset>
                <h2 class="fs-title">Address</h2>
                <h3 class="fs-subtitle">Please provide your address and postal code</h3>
                <div class="form_labels">Street Address</div>
                <input type="text" name="street" placeholder="" autocomplete="mpl_street_v1.0" style="margin-bottom: 10px"/>
                <div class="form_labels">Town or City</div>
                <input type="text" name="city" placeholder="" autocomplete="mpl_city_v1.0" style="margin-bottom: 10px"/>
                <div class="form_labels">Province</div>
                <div class="form-group">
                    <div class="dropdown">
                        <select class="form-control input-small" name="province" autocomplete="mpl_province_v1.0" style="height: 50px" id="province" style="margin-bottom: 10px">
                            <option value="AB">Alberta</option>
                            <option value="BC">British Columbia</option>
                            <option value="MB">Manitoba</option>
                            <option value="NB">New Brunswick</option>
                            <option value="NL">Newfoundland and Labrador</option>
                            <option value="NT">Northwest Territories</option>
                            <option value="NS">Nova Scotia</option>
                            <option value="NU">Nunavut</option>
                            <option value="ON" selected>Ontario</option>
                            <option value="PE">Prince Edward Island</option>
                            <option value="QC">Quebec</option>
                            <option value="SK">Saskatchewan</option>
                            <option value="YT">Yukon</option>
                        </select>
                    </div>
                </div>
                <div class="form_labels">Postal Code</div>
                <input type="text" name="postalcode" placeholder="" autocomplete="mpl_postal_v1.0" style="margin-bottom: 10px"/>
                <input type="button" name="previous" class="previous action-button-previous" value="Previous"/>
                <input type="button" name="next" class="next action-button" value="Next"/>
            </fieldset>


            <fieldset>
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
                <input type="button" name="previous" class="previous action-button-previous" value="Previous"/>
                <input type="button" name="next" class="next action-button" value="Next"/>
            </fieldset>

            <fieldset>
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


                <?php
                // get a list of branch locations from the ILS
                $location_result = get_branch_locations();
                ?>

                <div class="form-group">

                    <div class="form_labels">Home Library</div>
                    <div class="dropdown">
                        <select class="form-control input-small" name="homelibrary" autocomplete="mpl_homelibrary_v1.0" style="height: 46px;" id="homelibrary" style="margin-bottom: 10px">
                            <?php
                            // iterate through the branch array from the function above and put them into a drop down list
                            while ($row = pg_fetch_array($location_result)) {
                                echo '<option value="' . $row[0] . '"';
                                if($row[0] == 'm'){ echo 'selected';}
                                echo '>' . $row[1] . '</option>';
                                lb();
                            }
                            ?>
                        </select>
                    </div>
                </div>


                <?php
                // get the value of PCODE2 from the ILS via the API.  At MPL we use it for languages spoken in the home
                $pcode2_result = get_iii_pcode2();?>
                <div class="form-group">

                    <div class="form_labels">Additional Language Spoken in the Home</div>
                    <div class="dropdown">
                        <select class="form-control input-small" name="pcode2" autocomplete="mpl_lang1_v1.0" style="height: 46px;" id="pcode2" style="margin-bottom: 10px">
                            <?php
                            // iterate through the data and create a drop down list.
                            while ($row = pg_fetch_array($pcode2_result)) {
                                echo '<option value="' . $row[0] . '">';
                                if($row[1] == '---') {
                                    echo 'None';
                                }
                                else {
                                    echo $row[1];
                                }
                                echo '</option>';
                                lb();
                            }
                            ?>
                        </select>
                    </div>

                </div>

                <?php $pcode3_result = get_iii_pcode3();?>
                <div class="form-group">

                    <div class="form_labels">Additional Language Spoken in the Home</div>
                    <div class="dropdown">
                        <select class="form-control input-small" name="pcode3" autocomplete="mpl_lang2_v1.0" style="height: 46px;" id="pcode3" style="margin-bottom: 10px">
                            <?php
                            while ($row = pg_fetch_array($pcode3_result)) {
                                echo '<option value="' . $row[0] . '">' . $row[1] . '</option>';
                                lb();
                            }
                            ?>
                            <option value="0"selected>None</option>
                        </select>
                    </div>

                </div>




                <input type="button" name="previous" class="previous action-button-previous" value="Previous"/>

                <input type="submit" name="submit" class="submit action-button" value="Submit"/>

            </fieldset>



            <!-- link to designify.me code snippets -->
            <div class="dme_link">
                <!--  <p><a href="http://designify.me/code-snippets-js/" target="_blank">More Code Snippets</a></p> -->

            </div>
            <!-- /.link to designify.me code snippets -->

    </div>



<?php
// as part of the patron creation process we are actually creating a library card with a barcode and background image
// this gets created and rendered on the server, then shown to the patron and emailed to them.  The function below
// cleans up these files and erases ones that are over 2 hours old.
$files = glob("/var/www/html/onlinecardregistration/21387*");
$now   = time();
foreach ($files as $file) {
    if (is_file($file)) {
        if ($now - filemtime($file) >= 60 * 60 * 2) { // 2 hours
            unlink($file);
        }
    }
}

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
</html>


<?php
// old code that I didn't remove yet.  I had originally moved the file delete into a function, but then i moved the code to this page.
//delete_oldfiles('/var/www/html/onlinecardregistration/html/', 86400, '21387*');
?>
