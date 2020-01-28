
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
        <div class="col-xs-10"><H2>We're sorry that we are unable to provide you with a Milton Public Library e-card.  Currently MPL only offers library cards to residents of Milton.  Members of the following libraries are eligible for a MPL library card free of charge by visiting any MPL branch and showing their library card.</H2><br>
        <h3>
            <ul>
                <li>Oakville</li>
                <li>Burlington</li>
                <li>Halton Hills</li>
                <li>Hamilton</li>
                <li>Wellington County</li>
            </ul>
        </h3>
        <div class="col-xs-1"></div>
    </div>
</div>

</body>
</html>