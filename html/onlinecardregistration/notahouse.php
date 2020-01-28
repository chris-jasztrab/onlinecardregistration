
<?php
include('../../private/initialize.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MPL Online Card Registration</title>
    <script>
        function onSubmit(token) {
            document.getElementById("loginpage").submit();
        }
    </script>

</head>
<style>
    body {
        background-image: url('images/ecardbackground.png');
    }
</style>
<body>
<center><img src="images/title.png" width="40%"  alt="ecard Title"/> </center>
<br>
<div class="container">
    <div class="row" style="background-color: white" >
        <div class="col-xs-1"></div>
        <div class="col-xs-10"><H2>We're sorry that we are unable to process your application at this time.  Your address appears to be a business location in our system.  Please check your address and try again. </H2><br>

            <div class="col-xs-1"></div>
        </div>
    </div>

</body>
</html>