
<?php
 include('../../private/initialize.php');
 include('../../private/PHPMailer.php');
 include('../../private/Exception.php');
//pre($_SESSION);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

 if (!is_post_request()) {
header('Location: \MSF\index.php');
 }

 if (is_post_request()) {

if(isset($_POST['email']))
  {
      $post_email = $_POST['email'];
      $post_name = strtoupper($_POST['lname']) . ', ' . strtoupper($_POST['fname']);
      $post_street = strtoupper($_POST['street']);
      $post_cityprovince = strtoupper($_POST['city']) . ', ' . strtoupper($_POST['province']);
      $post_postalcode = strtoupper($_POST['postalcode']);
      $post_phone = $_POST['phone'];
      $post_bithdate = $_POST['date_of_birth_field'];
      $post_homelibrary = $_POST['homelibrary'];
      $post_pcode2 = $_POST['pcode2'];
      $post_pcode3 = $_POST['pcode3'];
      $post_city = strtoupper($_POST['city']);
      $post_province = strtoupper($_POST['province']);
      $post_notice = $_POST['notice_preference'];
      $post_marketing = $_POST['marketing_preference'];

  }
  else {
    $post_email = $_SESSION['email'];
    $post_name = strtoupper($_SESSION['last_name']) . ', ' . strtoupper($_SESSION['first_name']);
    $post_street = strtoupper($_SESSION['street']);
    $post_cityprovince = strtoupper($_SESSION['city']) . ', ' . $_SESSION['province'];
    $post_postalcode = strtoupper($_SESSION['postalcode']);
    $post_phone = $_SESSION['phonenumber'];
    $post_bithdate = $_SESSION['date_of_birth'];
    $post_homelibrary = $_SESSION['homelibrary'];
    $post_pcode2 = $_SESSION['pcode2'];
    $post_pcode3 = $_SESSION['pcode3'];
    $post_notice = $_SESSION['notice_preference'];
    $post_marketing = $_SESSION['marketing_preference'];
    $post_patrontype = $_SESSION['patron_type'];
    $post_city = strtoupper($_SESSION['city']);
    //$post_city =
  }

   $newPatronInfo = array(
   'email'=>$post_email,
   'name'=>$post_name,
   'addressStreet'=>$post_street,
   '$citycommaProvince'=>$post_cityprovince,
   'postalCode'=>$post_postalcode,
   'addressType'=>'a',
   'phonenumber'=>$post_phone,
   'numberType'=>'t',
   'birthdate'=>$post_bithdate,
   'homelibrary'=>$post_homelibrary);

   $myAddress = [
     'country' => 'CA',
     'city' => $post_city,
     'postalCode' => $post_postalcode,
     'street' => $post_street];

//echo $post_email;
//pre($_SESSION);
//pre($_POST);
//pre($newPatronInfo);
//exit();
//pre($_SESSION);
   $myNewPatron = createOnlinePatron($newPatronInfo);
   $patronPIN = $myNewPatron['pin'];
   $justpatronID = linkStripped($myNewPatron['patronIDString']);
   //echo 'patron id string that was created is: ' . $justpatronID;
   lb();
   $allPatronDetails = getAllPatronDetails($justpatronID);
   $pcode1value = updatePcode1Value($justpatronID, $myAddress);
   if($pcode1value == '-') {
     $addOutsideMiltonMessage = updatePatronAccountMessage($justpatronID, "Patron Address appears to be outside Milton - Check ID");
   }
   $pcode2value = updatePcode2Value($justpatronID, $_SESSION['pcode2']);
   $pcode3value = updatePcode3Value($justpatronID, $_SESSION['pcode3']);
   $updatePatronType = updatePatronType($justpatronID, '7');
//   $updatePatronType = updatePatronType($justpatronID, $_SESSION['patron_type']);
   $updateNoticePreference = updateNoticePreference($justpatronID, $post_notice);
   if($post_marketing == 'y') {
     $updateMarketingPreference = updatePatronNotes($justpatronID, 'MARKETING_PREFERENCE = TRUE');
     }
   $todaysDate = date('m/d/Y');
   $addPatronCreateDate = updatePatronNotes($justpatronID, 'Created via OnlinePatronCreationForm v1 on ' . $todaysDate);
   if(!empty($_SESSION['parentorguardian'])) {
    updatePatronGuardian($justpatronID, $_SESSION['parentorguardian']);
   }
 }
 //pre($allPatronDetails);
   createLibraryCardImage($allPatronDetails['barcodes']['0']);
   $libraryCardFile = $allPatronDetails['barcodes']['0'] . ".png";
  ?>

 <center>
      <H1>Here is your new Library Card!</H1>
      <img src="<?php echo $libraryCardFile; ?>" alt="Library Card Image" img style="border:1px solid black">
      <h3>Your PIN is: <?php echo $patronPIN; ?></h3>
 </center>

 <?php
// CODE TO EMAIL PATRON A COPY OF THEIR CARD - NEED TO CHANGE THIS SO I ATTACH THEIR LIBRARY CARD AND THEN DISPLAY IT  - DONE IN HEADERS CID
$bodytext = '<center>';
$bodytext .= '<H1>Here is your new library card!</H1>';
$bodytext .= '<img src="cid:card.png">';
$bodytext .= '<p>';
$bodytext .= "Your PIN is: " . $patronPIN;
$bodytext .= '</center>';
 $email = new PHPMailer();
 $email->IsHTML(true);
 $email->SetFrom('registration@mpl.on.ca', 'Milton Public Library'); //Name is optional
 $email->Subject   = 'Welcome to MPL';
 $email->Body      = $bodytext;
 $email->AddAddress($post_email);

 $file_to_attach = 'C:\\xampp\\htdocs\\sites\\onlinecardreg\\public\\' . $libraryCardFile;

 //$email->AddAttachment( $file_to_attach , '21387009002003.png' );
 $email->AddEmbeddedImage($libraryCardFile, 'card.png');

 return $email->Send();

  ?>
