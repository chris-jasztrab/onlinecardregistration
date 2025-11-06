<?php

//include('../../private/public_header.php');
include('../../private/functions.php');
include('../../private/phpqrcode.php');
include('../../private/barcode39.php');
// again we pull in the initialize file so we can access all our libraries.  This matters on this page because we are using a lot of
// functions to access Sierra via the API to pull down PCODE values, locations, etc.
// set a session variable right from the start saying the form is not complete.  We check for this later.

$_SESSION['install_complete'] = "no";

// check to see if the gd extension is loaded.  This extension is used to create the library card image.  If not loaded give error and die.
if(!extension_loaded('gd')) {
    echo 'This script requires the php-gd extension to work correctly.  Please install/enable it before proceeding.';
    die();
}
// check to see if the curl extension is loaded.  This extension is used to create the library card image.  If not loaded give error and die.
if(!extension_loaded('curl')) {
    echo 'This script requires the php curl extension to work correctly.  Please install/enable it before proceeding.';
    die();
}



if(is_post_request()) {
    $barcodeprefix = substr($_POST['startbarcode'], 0, $_POST['barcodeprefix']);
    $providedConfig['library_name'] = $_POST['library_name'];
    $providedConfig['appserver_name'] = $_POST['appserver_name'];
    $providedConfig['api_ver'] = '5';
    $providedConfig['api_key'] = $_POST['api_key'];
    $providedConfig['api_secret'] = $_POST['api_secret'];
    $providedConfig['country'] = $_POST['country'];
    $providedConfig['pinlength'] = $_POST['pinlength'];
    $providedConfig['startbarcode'] = $_POST['startbarcode'];
    $providedConfig['barcodeprefix'] = $barcodeprefix;
    $providedConfig['use_recaptcha'] = $_POST['use_recaptcha'];
    $providedConfig['recaptcha_site'] = $_POST['recaptcha_site'] ?? '';
    $providedConfig['recaptcha_secret'] = $_POST['recaptcha_secret'] ?? '';
    $providedConfig['google_analytics'] = $_POST['google_analytics'];
    $providedConfig['ga_property'] = $_POST['ga_property'] ?? '';
    $providedConfig['address_verification'] = $_POST['address_verification'];
    $providedConfig['bing_key'] = $_POST['bing_key'] ?? '';
    $providedConfig['verify_catchment'] = $_POST['verify_catchment'];
    $providedConfig['catchment_fail'] = $_POST['catchment_fail'] ?? '';
    $providedConfig['yourprovince'] = $_POST['provinceState'];
    $providedConfig['patrontypenumber'] = $_POST['patrontypenumber'];
    $providedConfig['patronStatsSecret'] = $_POST['patronStatsSecret'];
    $providedConfig['mailFrom'] = $_POST['mailFrom'];

    initializeConfigFile($providedConfig);
    initializeBarcodeFile($_POST['startbarcode']);

    header('Location: index.php');
    die();
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
    <title>Online Card Registration Setup</title>
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
    <script src="https://www.google.com/recaptcha/api.js?render=<?php echo defined('recaptchaSiteKey') ? recaptchaSiteKey : ''; ?>"></script>

        <script type="text/javascript">
            function populate(s1,s2){
                var s1 = document.getElementById(s1);
                var s2 = document.getElementById(s2);
                s2.innerHTML = "";
    if(s1.value == "CA"){
        var optionArray = [
            "|Choose a Province",
            "AB|Alberta",
            "BC|British Columbia",
            "MB|Manitoba",
            "NB|New Brunswick",
            "NL|Newfoundland and Labrador",
            "NU|Nunavut",
            "ON|Ontario",
            "PE|Prince Edward Island",
            "NT|Northwest Territories",
            "NS|Nova Scotia",
            "QC|Quebec",
            "SK|Saskatchewan",
            "YT|Yukon"];}

        else if(s1.value == "US"){
            var optionArray = [
                "|Choose a State",
        "AL|Alabama",
        "AK|Alaska",
        "AZ|Arizona",
        "AR|Arkansas",
        "CA|California",
        "CO|Colorado",
        "CT|Connecticut",
        "DE|Delaware",
        "DC|District Of Columbia",
        "FL|Florida",
        "GA|Georgia",
        "HI|Hawaii",
        "ID|Idaho",
        "IL|Illinois",
        "IN|Indiana",
        "IA|Iowa",
        "KS|Kansas",
        "KY|Kentucky",
        "LA|Louisiana",
        "ME|Maine",
        "MD|Maryland",
        "MI|Michigan",
        "MN|Minnesota",
        "MS|Mississippi",
        "MO|Missouri",
        "MT|Montana",
        "NE|Nebraska",
        "NV|Nevada",
        "NH|New Hampshire",
        "NJ|New Jersey",
        "NM|New Mexico",
        "NY|New York",
        "NC|North Carolina",
        "ND|North Dakota",
        "OH|Ohio",
        "OK|Oklahoma",
        "OR|Oregon",
        "PA|Pennsylvania",
        "RI|Rhode Island",
        "SC|South Carolina",
        "SD|South Dakota",
        "TN|Tennessee",
        "TX|Texas",
        "UT|Utah",
        "VT|Vermont",
        "VA|Virginia",
        "WA|Washington",
        "WV|West Virginia",
        "WI|Wisconsin",
        "WY|Wyoming"];}
                for(var option in optionArray){
                    var pair = optionArray[option].split("|");
                    var newOption = document.createElement("option");
                    newOption.value = pair[0];
                    newOption.innerHTML = pair[1];
                    s2.options.add(newOption);
                }
            }
        </script>

</head>
<body background="images/ecardbackground.png">


<!-- MultiStep Form -->

    <div class="col-md-6 col-md-offset-3">
        <form id="msform" action="install.php" method="post" autocomplete="MPLFORM2019_fdsa43cvC">
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

                <h2 class="fs-title">Required Configuration</h2>
                <h3 class="fs-subtitle">Please provide the following information about your library system and ILS</h3>
                <div class="form_labels">Library Name</div>
                <input type="text" name="library_name" placeholder="Your Library" autocomplete="off" style="margin-bottom: 10px" required/>
                <div class="form_labels">FQDN of your App Server</div>
                <input type="text" name="appserver_name" placeholder="ex: app.mpl.on.ca" id="appserver_name" autocomplete="mpl_appserver_v1.0" style="margin-bottom: 10px" required/>
                <div class="form_labels">Sierra API Key</div>
                <input type="text" name="api_key" placeholder="" id="api_key" autocomplete="mpl_api_key_v1.0" style="margin-bottom: 10px" required/>
                <div class="form_labels">Sierra API Secret</div>
                <input type="text" name="api_secret" placeholder="" id="api_secret" autocomplete="mpl_api_secret_v1.0" style="margin-bottom: 10px" required/>
                <div class="form_labels">Canada or United States</div>
                <div class="form-group">
                    <div class="dropdown">
                        <select class="form-control input-small" onchange="populate(this.id,'provinceState')" name="country" autocomplete="mpl_country_v1.0" style="height: 50px" id="country" style="margin-bottom: 10px">
                            <option value=""></option>
                            <option value="CA">Canada</option>
                            <option value="US">United States</option>
                        </select>
                    </div>
                </div>
        <div class="form_labels">Province/State</div>
                <div class="form-group">
                    <div class="dropdown">
                        <select class="form-control input-small" onfocus="populate('country','provinceState')" name="provinceState" autocomplete="mpl_provinceState_2.0" style="height: 50px" id="provinceState" style="margin-bottom: 10px">

                        </select>
                    </div>
                </div>
            <div class="form_labels">PIN Length</div>
            <input type="number" name="pinlength" placeholder="" id="pinlength" autocomplete="mpl_pin_length" style="margin-bottom: 10px" required/>
    <div class="form_labels">Starting Barcode</div>
    <input type="text" name="startbarcode" placeholder="" id="startbarcode" autocomplete="mpl_start_barcode" style="margin-bottom: 10px" required/>
    <div class="form_labels">How many digits in the barcode prefix (# of digits that never change)</div>
    <input type="number" name="barcodeprefix" placeholder="" id="barcodeprefix" autocomplete="barcodeprefix" style="margin-bottom: 10px" required/>
    <div class="form_labels">Patron Type ID (the number of letter associated with your e-only patron type)</div>
    <input type="text" name="patrontypenumber" placeholder="" id="patrontypenumber" autocomplete="patrontypenumber" style="margin-bottom: 10px" required/>
    <div class="form_labels">Statistics Page Secret (used to access the statistics page)</div>
    <input type="text" name="patronStatsSecret" placeholder="" id="patronStatsSecret" autocomplete="patronStatsSecret" style="margin-bottom: 10px" required/>

    <div class="form_labels">Email address to send registrations from</div>
    <input type="text" name="mailFrom" placeholder="ex: onlineregistration@yourlibrary.ca" id="mailFrom" autocomplete="mailFrom" style="margin-bottom: 10px" required/>


    <h2 class="fs-title">Optional Configuration Switches</h2>

    <div class="form_labels">Use Google ReCaptcha</div>
    <div class="form-group">
        <div class="dropdown">
            <select class="form-control input-small" name="use_recaptcha" autocomplete="use_recaptcha" style="height: 50px" id="province" style="margin-bottom: 10px">
                <option value="1">Yes</option>
                <option value="0" selected>No</option>
            </select>
        </div>
    </div>

    <div class="form_labels">ReCaptcha Site Key</div>
    <input type="text" name="recaptcha_site" placeholder="" id="recaptcha_site" autocomplete="recaptcha_site" style="margin-bottom: 10px"/>

    <div class="form_labels">ReCaptcha Secret Key</div>
    <input type="text" name="recaptcha_secret" placeholder="" id="recaptcha_secret" autocomplete="recaptcha_secret" style="margin-bottom: 10px"/>

    <div class="form_labels">Use Google Analytics</div>
    <div class="form-group">
        <div class="dropdown">
            <select class="form-control input-small" name="google_analytics" autocomplete="google_analytics" style="height: 50px" id="province" style="margin-bottom: 10px">
                <option value="1">Yes</option>
                <option value="0" selected>No</option>
            </select>
        </div>
    </div>

    <div class="form_labels">Google Analytics Property ID</div>
    <input type="text" name="ga_property" placeholder="" id="ga_property" autocomplete="ga_property" style="margin-bottom: 10px"/>

    <div class="form_labels">Use Address Verification</div>
    <div class="form-group">
        <div class="dropdown">
            <select class="form-control input-small" name="address_verification" autocomplete="address_verification" style="height: 50px" id="province" style="margin-bottom: 10px">
                <option value="1">Yes</option>
                <option value="0" selected>No</option>
            </select>
        </div>
    </div>

    <div class="form_labels">Bing Maps API Key</div>
    <input type="text" name="bing_key" placeholder="" id="bing_key" autocomplete="bing_key" style="margin-bottom: 10px"/>

    <div class="form_labels">Verify Catchment Area Check</div>
    <div class="form-group">
        <div class="dropdown">
            <select class="form-control input-small" name="verify_catchment" autocomplete="verify_catchment" style="height: 50px" id="province" style="margin-bottom: 10px">
                <option value="1">Yes</option>
                <option value="0" selected>No</option>
            </select>
        </div>
    </div>

    <div class="form_labels">Catchment Area Failure Redirect URL</div>
    <input type="text" name="catchment_fail" placeholder="https://page_explaining_card_policy" id="catchment_fail" autocomplete="catchment_fail" style="margin-bottom: 10px"/>



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
        grecaptcha.execute('<?php echo defined('recaptchaSiteKey') ? recaptchaSiteKey : ''; ?>', {action: 'homepage'}).then(function(token) {
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

?>
