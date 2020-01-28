<?php

function strLeft($str, $length) {
     return substr($str, 0, $length);
}

function strRight($str, $length) {
     return substr($str, -$length);
}

function log_in_patron($patronID) {
    //Regenerating ID protects the admin from session fixation.
    session_regenerate_id();
    $_SESSION['patronid'] = $patronID;
    return true;
}

function is_logged_in() {
    // Having a admin_id in the session serves a dual-purpose:
    // - Its presence indicates the admin is logged in.
    // - Its value tells which admin for looking up their record.
    return isset($_SESSION['patronid']);
}

function require_login() {
  // Call require_login() at the top of any page which needs to
  // require a valid login before granting acccess to the page.
    if (!is_logged_in()) {
        redirect_to('auth_form.php');
    } else {
        // Do nothing, let the rest of the page proceed
    }
}

function is_blank($value) {
  // is_blank('abcd') // * validate data presence
  // * uses trim() so empty spaces don't count
  // * uses === to avoid false positives
  // * better than empty() which considers "0" to be empty
    return !isset($value) || trim($value) === '';
}

function has_presence($value) {
  // has_presence('abcd')
  // * validate data presence
  // * reverse of is_blank()
  // * I prefer validation names with "has_"
    return !is_blank($value);
}

function has_length_greater_than($value, $min) {
  // has_length_greater_than('abcd', 3)
  // * validate string length
  // * spaces count towards length
  // * use trim() if spaces should not count
    $length = strlen($value);
    return $length > $min;
}

function has_length_less_than($value, $max) {
  // has_length_less_than('abcd', 5)
  // * validate string length
  // * spaces count towards length
  // * use trim() if spaces should not count
    $length = strlen($value);
    return $length < $max;
}

function has_length_exactly($value, $exact) {
  // has_length_exactly('abcd', 4)
  // * validate string length
  // * spaces count towards length
  // * use trim() if spaces should not count
    $length = strlen($value);
    return $length == $exact;
}

function has_length($value, $options) {
  // has_length('abcd', ['min' => 3, 'max' => 5])
  // * validate string length
  // * combines functions_greater_than, _less_than, _exactly
  // * spaces count towards length
  // * use trim() if spaces should not count
    if (isset($options['min']) && !has_length_greater_than($value, $options['min'] - 1)) {
        return false;
    } elseif (isset($options['max']) && !has_length_less_than($value, $options['max'] + 1)) {
        return false;
    } elseif (isset($options['exact']) && !has_length_exactly($value, $options['exact'])) {
        return false;
    } else {
        return true;
    }
}

function redirect_to($location) {
    header("Location: " . $location);
    exit;
}

function is_post_request() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function is_get_request() {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

function pre($data) {
    print '<pre>' . print_r($data, true) . '</pre>';
}

function stripped($fatId) {
  $lastSlash = strrpos($fatId, '/');
  $strippedId = substr($fatId, $lastSlash + 1, strlen($fatId));
  return $strippedId;
}

function linkStripped($fatId) {
  $lastSlash = strrpos($fatId, '/');
  $strippedId = substr($fatId, $lastSlash + 1, strlen($fatId) -2);
  $justID = substr($strippedId, 0, -2);
  return $justID;
}

function strippedComma($fatId) {
  $commaLocation = strrpos($fatId, ',');
  $strippedId = substr($fatId, '0', $commaLocation);
  return $strippedId;
}

function setApiAccessToken() {
  $uri = 'https://' . appServer . ':443/iii/sierra-api/v' . apiVer . '/token/';
  $authCredentials = base64_encode(apiKey . ":" . apiSecret);

  // Build the header
  $headers = array(
  "Authorization: Basic " . $authCredentials,
  "Content-Type: application/x-www-form-urlencoded");
  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
  $result = json_decode(curl_exec($ch));
  curl_close($ch);
  // save the access token and creation time to a session variable
  $_SESSION['apiAccessToken'] = $result->access_token;
  $_SESSION['apiAccessTokenCreationDate'] = time();
}

function getCurrentApiAccessToken() {
  $now = time();
  $elapsedTime = $now - $_SESSION['apiAccessTokenCreationDate'];

  if ($elapsedTime >= 360)
  {
      // if the current token is older than 6 minutes, get a new one
      setApiAccessToken();
  }
  return $_SESSION['apiAccessToken'];
}

function validatePatron($barcode, $pin) {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/validate';
  $query_string = '
    {
      "barcode": "' . $barcode . '",
      "pin": "' . $pin . '"
    }';

  //setup the API access token
  setApiAccessToken();

  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();

  //build the headers that we are going to use to post our json to the api
  $headers = array(
    "Authorization: Bearer " . $apiToken,
    "Content-Type:  application/json");

  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  //get the result from our json api query
  $result = curl_exec($ch);
  $patron_result = json_decode($result, true);
  //var_dump($patron_result);
  return $patron_result;
  }

function getPatronDetails($patronID) {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  $uri .= '?fields=names,emails,barcodes,expirationDate,pMessage';

  $apiToken = getCurrentApiAccessToken();

  // Build the header
  $headers = array(
      "Authorization: Bearer " . $apiToken
  );

  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = json_decode(curl_exec($ch),true);

  curl_close($ch);
  return $result;
}

function findPatronIDByBarcode($barcode) {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/query';
  $uri .= '?offset=0';
  $uri .= '&limit=1';
  //echo $uri;
  $query_string = '{
  "target":
  {
      "record": {
        "type": "patron"
      },
      "field": {
        "tag": "b"
      }
    },
    "expr": {
      "op": "equals",
      "operands": [
      "' . $barcode . '",
        ""
      ]
    }
  }';
  //echo " query string is " . $query_string . " ";
  //setup the API access token
  setApiAccessToken();

  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //echo " this is the API token " . $apiToken . " ";

  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");

  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  //get the result from our json api query
  $result = curl_exec($ch);
  $patronIDArray = json_decode($result, true);
  $patronLink = $patronIDArray["entries"]["0"]["link"];
  $patronLink = stripped($patronLink);
  return $patronLink;

}

function findPatronIDByEmail($email) {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/query';
  $uri .= '?offset=0';
  $uri .= '&limit=10';
  //echo $uri;
  $query_string = '{
  "target":
  {
      "record": {
        "type": "patron"
      },
      "field": {
        "tag": "z"
      }
    },
    "expr": {
      "op": "has",
      "operands": [
      "' . $email . '",
        ""
      ]
    }
  }';

  //echo " query string is " . $query_string . " ";
  //setup the API access token
  setApiAccessToken();

  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //echo " this is the API token " . $apiToken . " ";

  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");

  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  //get the result from our json api query
  $result = curl_exec($ch);

  $patronIDArray = json_decode($result, true);
  return $patronIDArray;
  //$patronLink = $patronIDArray["entries"]["0"]["link"];
  //$patronLink = stripped($patronLink);
  //return $patronLink;
}


// NOT DONE!! get overdue patron items
function getPatronOverdueItems($patronID) {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  $uri .= '?fields=names,emails,barcodes,expirationDate';

  $apiToken = getCurrentApiAccessToken();

  // Build the header
  $headers = array(
      "Authorization: Bearer " . $apiToken
  );

  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = json_decode(curl_exec($ch),true);

  curl_close($ch);
  return $result;
}

function getPatronFines($patronID) {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  $uri .= '/fines?offset=0';

  $apiToken = getCurrentApiAccessToken();

  // Build the header
  $headers = array(
      "Authorization: Bearer " . $apiToken);

  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  $result = json_decode(curl_exec($ch),true);
  curl_close($ch);
  return $result;
}

function lb() {
  echo "<br/>";
}

function getTotalFinesOwed($patronID) {
  $finesTotal = 0.0;
  $finesDetail = getPatronFines($patronID);
  $individualFine = $finesDetail['entries'];
  foreach ($individualFine as $fine)
    {
      $finesTotal = $finesTotal + $fine['itemCharge'];
    }
  return $finesTotal;
}

function listPatronDueDates($patronID) {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  $uri .= '/checkouts?offset=0&fields=dueDate';

  $apiToken = getCurrentApiAccessToken();

  // Build the header
  $headers = array(
      "Authorization: Bearer " . $apiToken);

  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  $result = json_decode(curl_exec($ch),true);
  curl_close($ch);
  $itemDetail = $result['entries'];
  foreach ($result as $item)
  {
    if(is_array($item))
    {
      {
        foreach ($item as $itemInfo)
        {
          echo $itemInfo['dueDate'];
          lb();
          var_dump($itemInfo);
          lb();

        }
      }
    }
  }
}

function doesPatronHaveOverdueMaterial($patronID) {
  //returns boolean - true if materials are overdue or false if not
  $overDue = False;
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  $uri .= '/checkouts?offset=0&fields=dueDate';

  $apiToken = getCurrentApiAccessToken();

  // Build the header
  $headers = array(
      "Authorization: Bearer " . $apiToken);

  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  $result = json_decode(curl_exec($ch),true);
  curl_close($ch);
  $itemDetail = $result['entries'];
  foreach ($result as $item)
  {
    if(is_array($item))
    {
      {
        foreach ($item as $itemInfo)
        {
          $dateString = strtotime($itemInfo['dueDate']);
          $timeSinceDue = time() - strtotime($itemInfo['dueDate']);
          //echo "Item is due: " . date('Y-m-d',$dateString);
          //lb();
          if ($timeSinceDue > 0)
          {
          //echo "This item is overdue";
            $overDue = True;
          }
          else {
            //echo "Item is not due yet.";
          }
          //echo "Time Since Due: " . $timeSinceDue;
          //lb();


          //echo $itemInfo['dueDate'];
          //lb();
        }
      }
    }
  }
  //$today = strtotime('today');
  //echo date('Y-m-d', $today);
  return $overDue;
}

function getNumberOfItemsOut($patronID) {
  //returns boolean - true if materials are overdue or false if not
  $overDue = False;
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  $uri .= '/checkouts?offset=0&fields=dueDate';

  $apiToken = getCurrentApiAccessToken();

  // Build the header
  $headers = array(
      "Authorization: Bearer " . $apiToken);

  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  $result = json_decode(curl_exec($ch),true);
  curl_close($ch);
  $itemDetail = $result['entries'];
  foreach ($result as $item)
  {
    if(is_array($item))
    {
      $numberOfItems = sizeof($item);
    }
  }
  return $numberOfItems;
}

function getItemByCheckoutID($checkoutID) {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/checkouts/';
  $uri .= $checkoutID;
  //$uri .= '?fields=names,emails,barcodes,expirationDate';

  $apiToken = getCurrentApiAccessToken();

  // Build the header
  $headers = array(
      "Authorization: Bearer " . $apiToken
  );

  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  $result = json_decode(curl_exec($ch),true);
  curl_close($ch);
  return $result;
}

function getItemInfoByItemID($itemID) {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/items/';
  $uri .= $itemID;
  //$uri .= '?fields=names,emails,barcodes,expirationDate';

  $apiToken = getCurrentApiAccessToken();

  // Build the header
  $headers = array(
      "Authorization: Bearer " . $apiToken
  );

  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  $result = json_decode(curl_exec($ch),true);
  curl_close($ch);
  return $result;
}

function getAllPatronDetails($patronID) {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  $uri .= '?fields=id,updatedDate,createdDate,deletedDate,deleted,suppressed,names,barcodes,expirationDate,birthDate,emails,patronType,patronCodes,homeLibraryCode,message,blockInfo,addresses,phones,uniqueIds,moneyOwed,pMessage,langPref,varFields,fixedFields';

  $apiToken = getCurrentApiAccessToken();

  // Build the header
  $headers = array(
      "Authorization: Bearer " . $apiToken
  );

  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = json_decode(curl_exec($ch),true);
  //lb();
  //echo "mydump: ";
  //var_dump($result);
  //lb();
  //lb();

  curl_close($ch);
  if(array_key_exists("accountMessages",$result))
  {
    $patronAccountMessages = $result['message']['accountMessages'];
  }
  else {
    $patronAccountMessages = "";
  }
  $allPatronDetail = array(
    "id" => $result['id'],
    "expirationDate" => $result['expirationDate'],
    "birthDate" => $result['birthDate'],
    "patronType" => $result['patronType'],
    "homeLibraryCode" => $result['homeLibraryCode'],
    "accountMessages" => $patronAccountMessages,
    "homeLibraryCode" => $result['homeLibraryCode']);
    return $result;
}

function isPatronElegibleToRenew($patronID) {
  // only allow patrons who are within three weeks of expiration to renew
  // returns boolean
  $currentPatron = getPatronDetails($patronID);
  $todayTimeStr = strtotime('today');
  $datedifference = strtotime($currentPatron['expirationDate']) - $todayTimeStr;
  // 1814400 is three weeks in seconds
  if($datedifference > 1814400)
  {
    return false;
  }
  elseif($datedifference <= 1814400)
  {
    return true;
  }

  //return $result;
}

function isPatronExpired($patronID) {
  // only allow patrons who are within three weeks of expiration to renew
  // returns boolean
  $currentPatron = getPatronDetails($patronID);
  $todayTimeStr = strtotime('today');
  $datedifference = strtotime($currentPatron['expirationDate']) - $todayTimeStr;
  // 1814400 is three weeks in seconds
  if($datedifference > 0)
  {
    return false;
  }
  elseif($datedifference <= 0)
  {
    return true;
  }

  //return $result;
}

// still need to code a dynamic date into this function to set the expiration date ahead
function updatePatronExpirationDate($patronID) {
  $currentPatron = getPatronDetails($patronID);
  //echo "Current Expiration Date is: " . $currentPatron['expirationDate'];
  $currentExpirationDate = strtotime($currentPatron['expirationDate']);
  $updatedExpirationDate = strtotime('1 year', $currentExpirationDate);
  $updatedExpirationString = date('Y-m-d', $updatedExpirationDate);
  //echo "Updated date is: " . $updatedExpirationString;
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  //echo "URI is: " . $uri;
  $query_string = '{  "expirationDate": "';
  $query_string .=  $updatedExpirationString;
  $query_string .= '"}';
  //echo "Query String is: " . $query_string;
  //setup the API access token
  setApiAccessToken();
  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");
  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch);
  curl_close($ch);
  $todaysDate = date('m/d/Y');
  updatePatronNotes($patronID, 'Renewed Online: ' . $todaysDate);
  return $result;
}

function updatePatronAddress($patronID, $street, $city, $province, $postal) {
  // Street
  // CITY Province
  // POSTAL CODE WITH SPACE
  //echo "-- UPDATE PATRON ADDRESS FUNCTION --";
  $currentPatron = getAllPatronDetails($patronID);
  //echo "Current Expiration Date is: " . $currentPatron['expirationDate'];
  //var_dump($currentPatron);

  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;

  //{"addresses":[{"lines": ["210 Northview Heights Drive", "CAMBRIDGE, ON", "N1R 8C6"],"type": "a"}]}
  //echo "URI is: " . $uri;
  $query_string = '{"addresses":[{"lines": ["';
  $query_string .= strtoupper($street);
  $query_string .= '", "';
  $query_string .= strtoupper($city) . ', ' . strtoupper($province) . '", "' . strtoupper($postal) . '"], "type": "a"}]}';

  //$query_string .=  $updatedExpirationString;
  //echo "Query String is: " . $query_string;
  //setup the API access token
  setApiAccessToken();
  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");
  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
  //$addressDetail = $currentAddress['lines'];

}

function getPatronAddress($patronID) {
  $detail = getAllPatronDetails($patronID);
  $address = $detail['addresses'][0]['lines'];
  return $address;
}

function getPatronStreet($address) {
  return $address[0];
}

function getPatronCity($address) {
  $address_and_prov = $address[1];
  $comma_location = strpos($address_and_prov, ',');
  $length_of_add_and_prov = strlen($address_and_prov);
  $address = substr($address_and_prov, 0, $comma_location);
  return $address;
}

function getPatronPostal($address) {
  return $address[2];
}

function getPatronEmailAddress($patronID) {
  $detail = getAllPatronDetails($patronID);
  if(array_key_exists("emails",$detail))
  {
    return $detail['emails'][0];
  }
  else {
    return NULL;
  }
}

function getPatronEmailArray($patronID) {
  $detail = getAllPatronDetails($patronID);
  if(array_key_exists("emails",$detail))
  {
    return $detail['emails'];
  }
  else {
    return NULL;
  }
}

function verifyAllowedCity($city) {
  $enteredCity = strtoupper($city);
  if(in_array($enteredCity, allowedCities))
  {
    return TRUE;

  }
  else {
    return FALSE;
  }
}

function verifyMilton($city) {
  $enteredCity = strtoupper($city);
  if($enteredCity == "MILTON")
  {
    lb();
    echo "Patron Entered Milton";
    lb();
  }
  else {
    echo "Patron didn't enter milton";
  }
}

function getPatronMessages($patronID) {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  $uri .= '?fields=id,message';
  $apiToken = getCurrentApiAccessToken();
  // Build the header
  $headers = array(
      "Authorization: Bearer " . $apiToken
  );

  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = json_decode(curl_exec($ch),true);
  curl_close($ch);
  $messageArray = $result['message'];
  if(array_key_exists("accountMessages",$messageArray))
  {
    $patronAccountMessages = $result['message']['accountMessages'];
  }
  else
  {
    $patronAccountMessages = NULL;
  }
  return $patronAccountMessages;
}

function updateMoveBlock($patronid) {
  if(!isPatronMoveBlocked($patronid))
  {
    // patron isn't moved blocked yet - add block
    updatePatronAccountMessage($patronid, "MOVED");
    return TRUE;
  }
}

function isPatronMoveBlocked($patronID) {
  $patronNotes = getPatronMessages($patronID);
  if($patronNotes != NULL)
  {
  if(in_array("MOVED", $patronNotes))
  {
    return TRUE;
  }
  else
  {
    return FALSE;
  }
  }
}

function updatePatronAccountMessage($patronID, $message) {
  $currentPatron = getAllPatronDetails($patronID);
  $currentMessages = getPatronMessages($patronID);
  if($currentMessages != NULL)
  {
    array_push($currentMessages, $message);
  }
  else $currentMessages = array($message);
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  // {"varFields":[{ "fieldTag":"m", "content":"JSON1TEST" },{ "fieldTag":"m", "content":"JSON2TEST" },{ "fieldTag":"m", "content":"JSON3TEST" }]}
  $query_string = ' {"varFields":[';
    $numberOfMessages = sizeof($currentMessages);
  foreach ($currentMessages as $message) {
    $query_string .= '{ "fieldTag":"m", "content": "' . $message . '"}';
    $numberOfMessages = $numberOfMessages - 1;
    if($numberOfMessages > 0)
    {
      $query_string .= ',';
    }
  }
  $query_string .= ']}';

  //$query_string .=  $updatedExpirationString;

  //setup the API access token
  setApiAccessToken();
  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");
  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
  //$addressDetail = $currentAddress['lines'];

}

function updatePatronEmailAddress($patronID, $email) {
  $currentPatron = getAllPatronDetails($patronID);
  $currentEmails = getPatronEmailArray($patronID);
  if($currentEmails != NULL)
  {
    array_push($currentEmails, $email);
  }
  else $currentEmails = array($email);
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  $numberOfEmails = sizeof($currentEmails);
  $query_string = '{ "emails": [';
  foreach ($currentEmails as $email) {
    $query_string .= '"' . $email . '"';
    $numberOfEmails = $numberOfEmails - 1;
    if($numberOfEmails > 0)
    {
      $query_string .= ',';
    }
  }
  $query_string .= ']}';

  //$query_string .=  $updatedExpirationString;

  //setup the API access token
  setApiAccessToken();
  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");
  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
  //$addressDetail = $currentAddress['lines'];
}

function getPatronCodes($patronID) {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  $uri .= '?fields=patronCodes';
  $apiToken = getCurrentApiAccessToken();
  // Build the header
  $headers = array(
      "Authorization: Bearer " . $apiToken
  );

  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = json_decode(curl_exec($ch),true);
  curl_close($ch);
  return $result;
}

function getPatronType($patronID) {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  $uri .= '?fields=patronType';
  $apiToken = getCurrentApiAccessToken();
  // Build the header
  $headers = array(
      "Authorization: Bearer " . $apiToken
  );

  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = json_decode(curl_exec($ch),true);
  curl_close($ch);
  return $result['patronType'];
}

function getPatronVarFields($patronID) {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  $uri .= '?fields=varFields';
  $apiToken = getCurrentApiAccessToken();
  // Build the header
  $headers = array(
      "Authorization: Bearer " . $apiToken
  );

  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = json_decode(curl_exec($ch),true);
  curl_close($ch);
  return $result;
}

function getPatronNotes($patronID) {
  $notesArray = [];
  $patronVarFields = getPatronVarFields($patronID);
  foreach ($patronVarFields as $field) {
    if(is_array($field)) {
      foreach($field as $content)
      {
        if($content['fieldTag'] == 'x')
        {
          array_push($notesArray, $content['content']);
          //echo "Tag is: " . $content['fieldTag'] . " Content is: " . $content['content'];
          //pre($content);
          //lb();
        }
      }
      //echo "tag is :" . $field[$fun_count]['fieldTag'] . " Data is: " . $field[$fun_count]['content'];
      //lb();
    }
  }
  return $notesArray;
}

function verifyEmailHash($email_address, $hash) {
  $computedHash = md5(salt1 . $email_address . salt2);
  if($computedHash == $hash) {
    return true;
  }
  if($computedHash <> $hash) {
    return false;
  }
}

function createEmailHash($email_address) {
  $emailHash = md5(salt1 . $email_address . salt2);
    return $emailHash;
  }

function updatePatronNotes($patronID, $newPatronNote) {
  $currentPatronNotes = getPatronNotes($patronID);
  if($currentPatronNotes != NULL)
  {
    array_push($currentPatronNotes, $newPatronNote);
  }
  else $currentPatronNotes = array($newPatronNote);

  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;

  $query_string = ' {"varFields":[';
    $numberOfNotes = sizeof($currentPatronNotes);
  foreach ($currentPatronNotes as $note) {
    $query_string .= '{ "fieldTag":"x", "content": "' . $note . '"}';
    $numberOfNotes = $numberOfNotes - 1;
    if($numberOfNotes > 0)
    {
      $query_string .= ',';
    }
  }
  $query_string .= ']}';

  //$query_string .=  $updatedExpirationString;

  //setup the API access token
  setApiAccessToken();
  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");
  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
  //$addressDetail = $currentAddress['lines'];
}

function updatePatronGuardian($patronID, $guardian) {


  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;

  $query_string = ' {"varFields":[';
    $query_string .= '{ "fieldTag":"g", "content": "' . $guardian . '"}';
  $query_string .= ']}';

  //$query_string .=  $updatedExpirationString;

  //setup the API access token
  setApiAccessToken();
  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");
  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
  //$addressDetail = $currentAddress['lines'];
}

function getPatronEmailConsent($patronID) {
  //echo 'begin func';
  //lb();
  $currentPatronNotes = getPatronNotes($patronID);
  if($currentPatronNotes == NULL)  {
    return FALSE;
  }
    else {
      foreach ($currentPatronNotes as $note) {
      if($note == "EMAIL_CONSENT=TRUE")
      {
        //echo 'return true';
        return TRUE;
        exit;
      }
      elseif($note == "EMAIL_CONSENT=FALSE") {
        //echo 'return false';
        return FALSE;
        exit;
      }
    }
    return;
  }
}

function updatePatronEmailPreference($patronID, $preference = false) {
  // expectation is that preference is true or false;
  // still need to be updated to actually update an existing preference
  $currentEmailPreference = getPatronEmailConsent($patronID);
  if ($preference == false) {
    $patronPreference = 'FALSE';
  }
  if ($preference == true) {
    $patronPreference = 'TRUE';
  }
  if(!isset($currentEmailPreference)) {
    if($patronPreference == true) {
      updatePatronNotes($_SESSION['patronid'], 'EMAIL_CONSENT=TRUE');
    }
    if($patronPreference == false) {
      updatePatronNotes($_SESSION['patronid'], 'EMAIL_CONSENT=FALSE');
    }
  }
  if(isset($currentEmailPreference)) {
    $currentPatronNotes = getPatronNotes($patronID);

    $uri = 'https://';
    $uri .= appServer;
    $uri .= ':443/iii/sierra-api/v';
    $uri .= apiVer;
    $uri .= '/patrons/';
    $uri .= $patronID;

    $query_string = ' {"varFields":[';
    $numberOfNotes = sizeof($currentPatronNotes);
    foreach ($currentPatronNotes as $note) {
      if($note == 'EMAIL_CONSENT=TRUE') {
        $query_string .= '{ "fieldTag":"x", "content": "EMAIL_CONSENT=' . $patronPreference . '"}';
      }
      elseif($note == 'EMAIL_CONSENT=FALSE') {
        $query_string .= '{ "fieldTag":"x", "content": "EMAIL_CONSENT=' . $patronPreference . '"}';
      }
      else{
        $query_string .= '{ "fieldTag":"x", "content": "' . $note . '"}';
      }
      $numberOfNotes = $numberOfNotes - 1;
      if($numberOfNotes > 0)
      {
        $query_string .= ',';
      }
    }
    $query_string .= ']}';



    //$query_string .=  $updatedExpirationString;

    //setup the API access token
    setApiAccessToken();
    //get the access token we just created
    $apiToken = getCurrentApiAccessToken();
    //build the headers that we are going to use to post our json to the api
    $headers = array(
        "Authorization: Bearer " . $apiToken,
        "Content-Type:  application/json");
    //use the headers, url, and json string to query the api
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uri);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
    //$addressDetail = $currentAddress['lines'];
  }
}

function setPatronEmailPrefTrue($patronid) {
  updatePatronEmailPreference($patronid, true);
}

function setPatronEmailPrefFalse($patronid) {
  updatePatronEmailPreference($patronid, false);
}

function findPatronByNameDOB($first_name = '', $last_name = '', $dob = '', $fines_owed = '', $address = '') {
  // finds a patron via JSON query, will return a list of patrons if more than one is matched
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/query';
  $uri .= '?offset=0';
  $uri .= '&limit=1000';


  $number_of_query_items = 0;
  if(!empty($dob)) {
    $number_of_query_items = $number_of_query_items + 1;
  }

  if(!empty($first_name)) {
    $number_of_query_items = $number_of_query_items + 1;
  }

  if(!empty($last_name)) {
    $number_of_query_items = $number_of_query_items + 1;
  }

  if(!empty($fines_owed)) {
    $number_of_query_items = $number_of_query_items + 1;
  }

  if(!empty($address)) {
    $number_of_query_items = $number_of_query_items + 1;
  }
  //echo "number of items set is: " . $number_of_query_items;
  //echo "<br>";

  $query_string = '{  "queries": [';
  if(!empty($dob)){
    $query_string .='{
      "target": {
        "record": {
          "type": "patron"
        },
        "id": 51
      },
      "expr": {
        "op": "equals",
        "operands": ["'.$dob.'",
          ""
        ]
      }
    },';
    $number_of_query_items = $number_of_query_items - 1;
  }
  if($number_of_query_items > 0) {
    $query_string .= '  "and",';
  }
  if(!empty($first_name)) {
    $query_string .='{
      "target": {
        "record": {
          "type": "patron"
        },
        "id": 80101
      },
      "expr": {
        "op": "has",
        "operands": ["'.$first_name.'",
          ""
        ]
      }
    },';
      $number_of_query_items = $number_of_query_items - 1;
  }
  if($number_of_query_items > 0) {
    $query_string .= '  "and",';
  }

  if(!empty($fines_owed)) {
    $query_string .='{
      "target": {
        "record": {
          "type": "patron"
        },
        "id": 96
      },
      "expr": {
        "op": "greater_than_or_equal",
        "operands": ["'.$fines_owed.'",
          ""
        ]
      }
    },';
      $number_of_query_items = $number_of_query_items - 1;
  }
  if($number_of_query_items > 0) {
    $query_string .= '  "and",';
  }

  if(!empty($address)) {
    $query_string .='{
      "target": {
        "record": {
          "type": "patron"
        },
          "field": {
          "tag": "a"
        }
      },
      "expr": {
        "op": "has",
        "operands": ["'.$address.'",
          ""
        ]
      }
    },';
      $number_of_query_items = $number_of_query_items - 1;
  }
  if($number_of_query_items > 0) {
    $query_string .= '  "and",';
  }


  if(!empty($last_name)) {
    $query_string .='{
      "target": {
        "record": {
          "type": "patron"
        },
        "id": 80102
      },
      "expr": {
        "op": "has",
        "operands": ["'.$last_name.'",
          ""
        ]
      }
    },';
  }

  $query_string .= '  ]
  }';

  //echo " query string is " . $query_string . " ";
  //setup the API access token
  setApiAccessToken();

  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //echo " this is the API token " . $apiToken . " ";

  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");

  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  //get the result from our json api query
  $result = curl_exec($ch);
  $patronIDArray = json_decode($result, true);
  //$patronLink = $patronIDArray["entries"]["0"]["link"];
  //$patronLink = stripped($patronLink);
  return $patronIDArray['entries'];

}

function geocodeAddress($address) {
  // returns an array of lat long points for given address.
  $uri = 'http://dev.virtualearth.net/REST/v1/Locations/';
  $uri .= $address['country'] . '/';
  $uri .= $address['city'] . '/';
  $uri .= $address['postalCode'] . '/';
  $uri .= $address['street'] . '/';
  $uri .= '?o=json&key=' . bingMapsKey;
  //  echo $uri;
  $formatted_uri = str_ireplace(" ","%20",$uri);
  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $formatted_uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

  $result = (curl_exec($ch));
  curl_close($ch);
  return $result;
}

function isPrivateResidence($address) {
  // returns an array of lat long points for given address.
  $latlong = getLatLong($address);
  //pre($latlong);
  $uri = 'http://dev.virtualearth.net/REST/v1/locationrecog/';
  $uri .= $latlong[0] . ',';
  $uri .= $latlong[1];
  $uri .= '?radius=.001';
  $uri .= '&o=json&key=' . bingMapsKey;
  //  echo $uri;
  $formatted_uri = str_ireplace(" ","%20",$uri);
  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $formatted_uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $result = (curl_exec($ch));
  curl_close($ch);
  $jcode = json_decode($result, true);
  $isresidence = $jcode['resourceSets']['0']['resources']['0']['isPrivateResidence'];

  if($isresidence == 'True') {
    return true;
  }
  elseif ($isresidence == 'False') {
    return false;
  }

}

function getLatLong($address) {
  $codedAddress = geocodeAddress($address);
  $jcode = json_decode($codedAddress, true);
  $points = $jcode['resourceSets']['0']['resources']['0']['point']['coordinates'];
  return $points;
  lb();
  return $codedAddress;
}

function getPostalCode($address) {
  $codedAddress = geocodeAddress($address);
  $jcode = json_decode($codedAddress, true);
  $points = $jcode['resourceSets']['0']['resources']['0']['address']['postalCode'];
  return $points;
}

function getFormattedAddress($address) {
  $codedAddress = geocodeAddress($address);
  $jcode = json_decode($codedAddress, true);
  $formattedAddress = $jcode['resourceSets']['0']['resources']['0']['address']['formattedAddress'];
  return $formattedAddress;

}

function doesPostalCodeMatch($address, $submittedPostalCode) {
  // $address is an address array
  $strippedsubmittedpc = str_replace(' ', '', $submittedPostalCode);
  $strippedGeocodedpc =  str_replace(' ', '', getPostalCode($address));
  if($strippedsubmittedpc == $strippedGeocodedpc)
  {
    return true;
  }
  else {
    return false;
  }
}

function getNumberOfMoveBlocked() {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/query';
  $uri .= '?offset=0';
  $uri .= '&limit=100000';
  //echo $uri;
  $query_string = '{
    "target": {
      "record": {
        "type": "patron"
      },
      "field": {
        "tag": "m"
      }
    },
    "expr": {
      "op": "equals",
      "operands": [
        "MOVED",
        ""
      ]
    }
  }';
  //echo " query string is " . $query_string . " ";
  //setup the API access token
  setApiAccessToken();

  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //echo " this is the API token " . $apiToken . " ";

  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");

  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  //get the result from our json api query
  $result = curl_exec($ch);
  $jcode = json_decode($result, true);
  return $jcode['total'];

}

function getNumberOfOnlineRenewals() {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/query';
  $uri .= '?offset=0';
  $uri .= '&limit=100000';
  //echo $uri;
  $query_string = '{
    "target": {
      "record": {
        "type": "patron"
      },
      "field": {
        "tag": "x"
      }
    },
    "expr": {
      "op": "has",
      "operands": [
        "renewed online",
        ""
      ]
    }
  }';
  //echo " query string is " . $query_string . " ";
  //setup the API access token
  setApiAccessToken();

  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //echo " this is the API token " . $apiToken . " ";

  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");

  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  //get the result from our json api query
  $result = curl_exec($ch);
  $jcode = json_decode($result, true);
  return $jcode['total'];

}

class pointLocation {
    var $pointOnVertex = true; // Check if the point sits exactly on one of the vertices?

    function pointLocation() {
    }

    function pointInPolygon($point, $polygon, $pointOnVertex = true) {
        $this->pointOnVertex = $pointOnVertex;

        // Transform string coordinates into arrays with x and y values
        $point = $this->pointStringToCoordinates($point);
        $vertices = array();
        foreach ($polygon as $vertex) {
            $vertices[] = $this->pointStringToCoordinates($vertex);
        }

        // Check if the point sits exactly on a vertex
        if ($this->pointOnVertex == true and $this->pointOnVertex($point, $vertices) == true) {
            return "vertex";
        }

        // Check if the point is inside the polygon or on the boundary
        $intersections = 0;
        $vertices_count = count($vertices);

        for ($i=1; $i < $vertices_count; $i++) {
            $vertex1 = $vertices[$i-1];
            $vertex2 = $vertices[$i];
            if ($vertex1['y'] == $vertex2['y'] and $vertex1['y'] == $point['y'] and $point['x'] > min($vertex1['x'], $vertex2['x']) and $point['x'] < max($vertex1['x'], $vertex2['x'])) { // Check if point is on an horizontal polygon boundary
                return "boundary";
            }
            if ($point['y'] > min($vertex1['y'], $vertex2['y']) and $point['y'] <= max($vertex1['y'], $vertex2['y']) and $point['x'] <= max($vertex1['x'], $vertex2['x']) and $vertex1['y'] != $vertex2['y']) {
                $xinters = ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x'];
                if ($xinters == $point['x']) { // Check if point is on the polygon boundary (other than horizontal)
                    return "boundary";
                }
                if ($vertex1['x'] == $vertex2['x'] || $point['x'] <= $xinters) {
                    $intersections++;
                }
            }
        }
        // If the number of edges we passed through is odd, then it's in the polygon.
        if ($intersections % 2 != 0) {
            return "inside";
        } else {
            return "outside";
        }
    }

    function pointOnVertex($point, $vertices) {
        foreach($vertices as $vertex) {
            if ($point == $vertex) {
                return true;
            }
        }

    }

    function pointStringToCoordinates($pointString) {
        $coordinates = explode(" ", $pointString);
        return array("x" => $coordinates[0], "y" => $coordinates[1]);
    }

}

function getWard($address) {
  //takes an address and gets the milton ward for it
  $latlong = getLatLong($address);
  $latitude = $latlong[0];
  $longitude = $latlong[1];
  $sampleCoords = $latitude . ', ' . $longitude;
  $pointLocation = new pointLocation();

  if($pointLocation->pointInPolygon($sampleCoords, ward1) == 'inside') {
    return "WARD 1";
  }

  if($pointLocation->pointInPolygon($sampleCoords, ward2) == 'inside') {
    return "WARD 2";
  }

  if($pointLocation->pointInPolygon($sampleCoords, ward3) == 'inside') {
    return "WARD 3";
  }

  if($pointLocation->pointInPolygon($sampleCoords, ward4) == 'inside') {
    return "WARD 4";
  }

  if($pointLocation->pointInPolygon($sampleCoords, oakville) == 'inside') {
    return "OAKVILLE";
  }

  if($pointLocation->pointInPolygon($sampleCoords, haltonhills) == 'inside') {
    return "HALTONHILLS";
  }

  if($pointLocation->pointInPolygon($sampleCoords, burlington) == 'inside') {
    return "BURLINGTON";
  }

  else {
    return "Coords are out of Milton Wards";
  }

}

function getPatronWardByID($patronID) {
  $patronAddress = getPatronAddress($patronID);
  $wardResult = getWard($patronAddress);
  if(!in_array($wardResult, array("WARD 1", "WARD 2", "WARD 3", "WARD 4"))) {
    echo "Patron is outside of Milton";
  }
}

function isPatronLocal($patronID) {
  $patronWard = getPatronWardByID($patronID);
  if($patronWard == 'Patron is outside of Milton') {
    echo 'outsider';
  }
}

function delLineFromFile($fileName, $lineNum){
 // check the file exists
   if(!is_writable($fileName))
     {
     // print an error
     print "The file $fileName is not writable";
     // exit the function
     exit;
     }
   else
       {
     // read the file into an array
     $arr = file($fileName);
     }

   // the line to delete is the line number minus 1, because arrays begin at zero
   $lineToDelete = $lineNum-1;

   // check if the line to delete is greater than the length of the file
   if($lineToDelete > sizeof($arr))
     {
       // print an error
     print "You have chosen a line number, <b>[$lineNum]</b>,  higher than the length of the file.";
     // exit the function
     exit;
     }

   //remove the line
   unset($arr["$lineToDelete"]);

   // open the file for reading
   if (!$fp = fopen($fileName, 'w+'))
     {
     // print an error
         print "Cannot open file ($fileName)";
       // exit the function
         exit;
         }
   // if $fp is valid
   if($fp)
     {
         // write the array to the file
         foreach($arr as $line) { fwrite($fp,$line); }

         // close the file
         fclose($fp);
         }
 return true;
 }

function getNextLibraryCard() {
  $cardlist = fopen('cards.txt', 'r') or die("Unable to open file");
  $result = fgets($cardlist);
  //delLineFromFile('cards.txt', 1);
  fclose($cardlist);
  return $result;
}

function getRandomString($length) {
  $rand = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz"), 0, $length);
  return $rand;
}

function getCard() {
    global $db;
    $sql = "SELECT * FROM librarybarcodes ";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    //$cardlist = mysqli_fetch_assoc($result);
    return $result;
}

function claimBlankCard() {
  global $db;
  $rand = getRandomString(20);
  $sql = "UPDATE librarybarcodes SET ";
  $sql .= "nonce = '" . $rand . "'";
  $sql .= ", used = '1' ";
  $sql .= " WHERE used != '1' ";
  $sql .= " LIMIT 1";
  $result = mysqli_query($db, $sql);
  // FOR UPDATE statements, the result is true or false
  if ($result) {
    $sql = "SELECT * FROM librarybarcodes ";
    $sql .= "WHERE nonce = '" . $rand . "' ";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    //$cardlist = mysqli_fetch_assoc($result);
    $blankCard = $result->fetch_assoc();
    return $blankCard['barcode'];
    }
    else { // UPDDATE FAILED
      echo $sql;
      echo "<br/>";
      echo mysqli_error($db);
      db_dissconnect($db);
    }

}

function createNewPatron($patronArrayObj) {

  $yearFromToday = strtotime('+1 year');
  $expirationString = date('Y-m-d', $yearFromToday);

  $email = $patronArrayObj['email'];
  $name = $patronArrayObj['name'];
  $addressStreet = $patronArrayObj['addressStreet'];
  $citycommaProvince = $patronArrayObj['$citycommaProvince'];
  $postalCode = $patronArrayObj['postalCode'];
  $addressType = $patronArrayObj['addressType'];
  $phoneNumber = $patronArrayObj['phonenumber'];
  $numberType = $patronArrayObj['numberType'];
  $pin = substr($phoneNumber, -4,4);
  $barcode = claimBlankCard();
  //$barcode = '21387001717764';  TEST CARD USED SO I DONT CHEW UP EXTRA CARDS IN MY CARD DB
  $expirationDate = $expirationString;
  $birthDate = $patronArrayObj['birthdate'];
  $homelibrary = $patronArrayObj['$homelibrary'];

  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';

  $query_string ='{"emails": ["';
  $query_string .=$email;
  $query_string .='"],"names": ["';
  $query_string .=$name;
  $query_string .='"],"addresses":[{"lines": ["';
  $query_string .= $addressStreet;
  $query_string .='", "';
  $query_string .= $citycommaProvince;
  $query_string .= '", "';
  $query_string .= $postalCode;
  $query_string .='"],"type": "';
  $query_string .= $addressType;
  $query_string .= '"}],"phones": [{"number": "';
  $query_string .= $phoneNumber;
  $query_string .= '","type": "';
  $query_string .= $numberType;
  $query_string .= '"}],"pin": "';
  $query_string .= $pin;
  $query_string .= '","barcodes": ["';
  $query_string .= $barcode;
  $query_string .= '"],"expirationDate": "';
  $query_string .= $expirationDate;
  $query_string .='","birthDate": "';
  $query_string .= $birthDate;
  $query_string .= '","homeLibraryCode": "';
  $query_string .= $homelibrary;
  $query_string .= '"}';

  //setup the API access token
  setApiAccessToken();
  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");
  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
  //$addressDetail = $currentAddress['lines'];

}

function createNewPatronStaff($patronArrayObj) {
  $yearFromToday = strtotime('+1 year');
  $expirationString = date('Y-m-d', $yearFromToday);
  $email = $patronArrayObj['email'];
  $name = $patronArrayObj['name'];
  $addressStreet = $patronArrayObj['addressStreet'];
  $citycommaProvince = $patronArrayObj['$citycommaProvince'];
  $postalCode = $patronArrayObj['postalCode'];
  $addressType = $patronArrayObj['addressType'];
  $phoneNumber = $patronArrayObj['phonenumber'];
  $numberType = $patronArrayObj['numberType'];
  $homelibrary = $patronArrayObj['homelibrary'];
  $pin = '9812';
  //$pin = substr($phoneNumber, -4,4);
  $barcode = $patronArrayObj['barcode'];
  //$barcode = '21387001717764';  TEST CARD USED SO I DONT CHEW UP EXTRA CARDS IN MY CARD DB
  $expirationDate = $expirationString;
  $birthDate = $patronArrayObj['birthdate'];
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $query_string ='{"emails": ["';
  $query_string .=strip_tags($email);
  $query_string .='"],"names": ["';
  $query_string .=strip_tags($name);
  $query_string .='"],"addresses":[{"lines": ["';
  $query_string .= strip_tags($addressStreet);
  $query_string .='", "';
  $query_string .= strip_tags($citycommaProvince);
  $query_string .= '", "';
  $query_string .= strip_tags($postalCode);
  $query_string .='"],"type": "';
  $query_string .= strip_tags($addressType);
  $query_string .= '"}],"phones": [{"number": "';
  $query_string .= strip_tags($phoneNumber);
  $query_string .= '","type": "';
  $query_string .= strip_tags($numberType);
  $query_string .= '"}],"pin": "';
  $query_string .= $pin;
  $query_string .= '","barcodes": ["';
  $query_string .= strip_tags($barcode);
  $query_string .= '"],"expirationDate": "';
  $query_string .= strip_tags($expirationDate);
  $query_string .='","birthDate": "';
  $query_string .= strip_tags($birthDate);
  $query_string .= '","homeLibraryCode": "';
  $query_string .= $homelibrary;
  $query_string .= '"}';
  //  echo $query_string;

  //setup the API access token
  setApiAccessToken();
  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");
  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
  //$addressDetail = $currentAddress['lines'];

}

function updatePatronType($patronID, $patronTypeString) {
  $currentPatron = getPatronDetails($patronID);
  //echo "Updated date is: " . $updatedExpirationString;
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  //echo "URI is: " . $uri;
  $query_string = '{  "patronType": ';
  $query_string .=  $patronTypeString;
  $query_string .= '}';
  //setup the API access token
  setApiAccessToken();
  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");
  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
}

function getPcode1FromAddress($address) {
  //takes an address and gets the milton ward for it

  $latlong = getLatLong($address);
  $latitude = $latlong[0];
  $longitude = $latlong[1];
  $sampleCoords = $latitude . ', ' . $longitude;
  $pointLocation = new pointLocation();

  if($pointLocation->pointInPolygon($sampleCoords, ward1) == 'inside') {
    return "1";
  }

  if($pointLocation->pointInPolygon($sampleCoords, ward2) == 'inside') {
    return "2";
  }

  if($pointLocation->pointInPolygon($sampleCoords, ward3) == 'inside') {
    return "3";
  }

  if($pointLocation->pointInPolygon($sampleCoords, ward4) == 'inside') {
    return "4";
  }

  else {
    return "-";
  }

}

function updatePcode1Value($patronID, $address) {

  $patronAddress = [
    'country' => 'CA',
    'city' => $address['city'],
    'postalCode' => $address['postalCode'],
    'street' => $address['street']];

  $pcode1value = getPcode1FromAddress($patronAddress);

  //echo "Updated date is: " . $updatedExpirationString;
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  //echo "URI is: " . $uri;
  $query_string = '{"patronCodes": {"pcode1": "';
  $query_string .=  $pcode1value;
  $query_string .= '"}}';
  //echo "Query String is: " . $query_string;
  //setup the API access token
  setApiAccessToken();
  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");
  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch);
  curl_close($ch);
  //return $result;
  return $pcode1value;
}

function updatePcode2Value($patronID, $pcode2Value) {

  //echo "Updated date is: " . $updatedExpirationString;
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  //echo "URI is: " . $uri;
  $query_string = '{"patronCodes": {"pcode2": "';
  $query_string .=  $pcode2Value;
  $query_string .= '"}}';
  //echo "Query String is: " . $query_string;
  //setup the API access token
  setApiAccessToken();
  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");
  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch);
  curl_close($ch);
  //return $result;
  return $pcode2Value;
}

function updatePcode3Value($patronID, $pcode3Value) {

  //echo "Updated date is: " . $updatedExpirationString;
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  //echo "URI is: " . $uri;
  $query_string = '{"patronCodes": {"pcode3": ';
  $query_string .=  $pcode3Value;
  $query_string .= '}}';
  //echo "Query String is: " . $query_string;
  //setup the API access token
  setApiAccessToken();
  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");
  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch);
  curl_close($ch);
  //return $result;
  return $pcode3Value;
}

function connect_to_iii_db() {
  $pgconn_string = "host='" . dbhost . "' port='" . dbport . "' dbname='" . dbname . "' user='" . dbuser . "' password='" . dbpassword . "' sslmode='" . dbsslmode . "' connect_timeout='" . dbtimeout . "'";
  $pgconn = pg_connect($pgconn_string);
  $stat = pg_connection_status($pgconn);
    if ($stat === PGSQL_CONNECTION_OK) {
  //      echo 'Connection status ok';
    } else {
        echo 'Connection status bad';
    }
    return $pgconn;
  }

function get_iii_pcode2() {
  $pgconn = connect_to_iii_db();
  $stat = pg_connection_status($pgconn);
    if ($stat === PGSQL_CONNECTION_OK) {
  //      echo 'Connection status ok';
    } else {
        return 'Connection status bad';
    }
  $query = "SELECT user_defined_property.code, user_defined_property_name.name
  FROM sierra_view.user_defined_category
  INNER JOIN sierra_view.user_defined_property ON user_defined_property.user_defined_category_id = user_defined_category.id
  INNER JOIN sierra_view.user_defined_property_name ON user_defined_property_name.user_defined_property_id = user_defined_property.id
  WHERE user_defined_category.code = 'pcode2' AND iii_language_id = 1;";

  $result = pg_query($pgconn, $query);
  return $result;
  $conn = pg_close($pgconn);
}

function get_iii_pcode3() {
  $pgconn = connect_to_iii_db();
  $stat = pg_connection_status($pgconn);
    if ($stat === PGSQL_CONNECTION_OK) {
  //      echo 'Connection status ok';
    } else {
        return 'Connection status bad';
    }
  $query = "SELECT user_defined_property.code, user_defined_property_name.name
  FROM sierra_view.user_defined_category
  INNER JOIN sierra_view.user_defined_property ON user_defined_property.user_defined_category_id = user_defined_category.id
  INNER JOIN sierra_view.user_defined_property_name ON user_defined_property_name.user_defined_property_id = user_defined_property.id
  WHERE user_defined_category.code = 'pcode3' AND iii_language_id = 1;";

  $result = pg_query($pgconn, $query);
  return $result;
  $conn = pg_close($pgconn);
}

function get_patron_types() {
  $pgconn = connect_to_iii_db();
  $stat = pg_connection_status($pgconn);
  if ($stat === PGSQL_CONNECTION_OK) {
  //      echo 'Connection status ok';
  } else {
    return 'Connection status bad';
    }
  $query = "Select
  sierra_view.ptype_property_name.description,
  sierra_view.ptype_property_name.ptype_id
  From
    sierra_view.ptype_property_name
  Where
    Char_Length(sierra_view.ptype_property_name.description) > 0
  Group By
  sierra_view.ptype_property_name.description,
  sierra_view.ptype_property_name.ptype_id";

  $result = pg_query($pgconn, $query);
  return $result;
  $conn = pg_close($pgconn);
  }

function get_branch_locations() {
  $pgconn = connect_to_iii_db();
  $stat = pg_connection_status($pgconn);
    if ($stat === PGSQL_CONNECTION_OK) {
  //      echo 'Connection status ok';
    } else {
        return 'Connection status bad';
    }
  $query = "  Select
        sierra_view.location.code,
        sierra_view.location_name.name
    From
        sierra_view.location Inner Join
        sierra_view.location_name On sierra_view.location.id = sierra_view.location_name.location_id
    Where
        Char_Length(sierra_view.location.code) = 1
    Group By
        sierra_view.location.code,
        sierra_view.location_name.name";

  $result = pg_query($pgconn, $query);
  return $result;
  $conn = pg_close($pgconn);
  }

function updateNoticePreference($patronID, $preferenceValue) {

  //echo "Updated date is: " . $updatedExpirationString;
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  //echo "URI is: " . $uri;
  $query_string = '{"fixedFields": {"268": {
  "label": "Notice Preference",
  "value": "';
  $query_string .=  $preferenceValue;
  $query_string .= '"}}}';
  //echo "Query String is: " . $query_string;
  //setup the API access token
  setApiAccessToken();
  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");
  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch);
  curl_close($ch);
  //return $result;
  return $result;
}

function getAgeFromBirthday($date) {
  return((int)date_diff(date_create($date),date_create('today'))->y);
}

function returnPatronTypeByBirthday($date) {
  $age = getAgeFromBirthday($date);
  if($age <= 4) {
    //preschool
    return 13;
  }
  if($age >= 5 && $age <= 12) {
    //child
    return 1;
  }
  if($age >= 13 && $age <= 18) {
    //youth
    return 18;
  }
  if($age >= 19 && $age <=64) {
    //adult
    return 0;
  }
  if($age >= 65) {
    //senior
    return 3;
  }
}

function fixPostalCode($postalcode) {
  // checks to see if they omitted a space in their postal code and adds one of they did.
  if(strlen($postalcode) == 6) {
    $fixedPC = substr($postalcode, 0, 3);
    $fixedPC .= ' ';
    $fixedPC .= substr($postalcode, 3, 3);
    return $fixedPC;
  }
  else return $postalcode;
}

function createNewPatronInternalPatron($patronArrayObj) {
  $yearFromToday = strtotime('+1 year');
  $expirationString = date('Y-m-d', $yearFromToday);
  $email = $patronArrayObj['email'];
  $name = $patronArrayObj['name'];
  $addressStreet = $patronArrayObj['addressStreet'];
  $citycommaProvince = $patronArrayObj['$citycommaProvince'];
  $postalCode = $patronArrayObj['postalCode'];
  $addressType = $patronArrayObj['addressType'];
  $phoneNumber = $patronArrayObj['phonenumber'];
  $numberType = $patronArrayObj['numberType'];
  $homelibrary = $patronArrayObj['homelibrary'];
  $pin = '9812';
  //$pin = substr($phoneNumber, -4,4);
  //$barcode = $patronArrayObj['barcode'];
  //$barcode = '21387001717764';  TEST CARD USED SO I DONT CHEW UP EXTRA CARDS IN MY CARD DB
  $expirationDate = $expirationString;
  $birthdate = $patronArrayObj['birthdate'];
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $query_string ='{"emails": ["';
  $query_string .=strip_tags($email);
  $query_string .='"],"names": ["';
  $query_string .=strip_tags($name);
  $query_string .='"],"addresses":[{"lines": ["';
  $query_string .= strip_tags($addressStreet);
  $query_string .='", "';
  $query_string .= strip_tags($citycommaProvince);
  $query_string .= '", "';
  $query_string .= strip_tags($postalCode);
  $query_string .='"],"type": "';
  $query_string .= strip_tags($addressType);
  $query_string .= '"}],"phones": [{"number": "';
  $query_string .= strip_tags($phoneNumber);
  $query_string .= '","type": "';
  $query_string .= strip_tags($numberType);
  $query_string .= '"}],"pin": "';
  $query_string .= $pin;
  $query_string .= '","barcodes": ["';
  //$query_string .= ;
  $query_string .= '"],"expirationDate": "';
  $query_string .= strip_tags($expirationDate);
  $query_string .='","birthDate": "';
  $query_string .= strip_tags($birthdate);
  $query_string .= '","homeLibraryCode": "';
  $query_string .= $homelibrary;
  $query_string .= '"}';
    //echo $query_string;

  //setup the API access token
  setApiAccessToken();
  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");
  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
  //$addressDetail = $currentAddress['lines'];

}

function getExpiredHolds() {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/items/query';
  $uri .= '?offset=0';
  $uri .= '&limit=100000';
  //echo $uri;
  $query_string = '{
  "target": {
    "record": {
      "type": "item"
    },
    "id": 8018
  },
  "expr": {
    "op": "equals",
    "operands": [
      "08-07-2019",
      ""
    ]
  }
  }';
  //echo " query string is " . $query_string . " ";
  //setup the API access token
  setApiAccessToken();

  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //echo " this is the API token " . $apiToken . " ";

  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");

  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  //get the result from our json api query
  $result = curl_exec($ch);
  $jcode = json_decode($result, true);
  return $jcode;

}

function getItemDetails($itemID) {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/items/';
  $uri .= $itemID;
  $uri .= '?fields=status,fixedFields,varFields,barcode,bibIds';

  $apiToken = getCurrentApiAccessToken();

  // Build the header
  $headers = array(
      "Authorization: Bearer " . $apiToken
  );

  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = json_decode(curl_exec($ch),true);

  curl_close($ch);
  return $result;
}

function getBibDetails($bibID) {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/bibs/';
  $uri .= $bibID;
  $uri .= '?fields=fixedFields,varFields,title';

  $apiToken = getCurrentApiAccessToken();

  // Build the header
  $headers = array(
      "Authorization: Bearer " . $apiToken
  );

  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = json_decode(curl_exec($ch),true);

  curl_close($ch);
  return $result;
}

function addFinetoPatron($patronID, $fineAmount, $fineReason) {

  //echo "Updated date is: " . $updatedExpirationString;
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  $uri .= '/fines/charge';

  //echo "URI is: " . $uri;
  $query_string = '{"amount": '. $fineAmount . ',';
  $query_string .= '"reason": "' . $fineReason . '",';
  $query_string .= '"location": "m"}';

  //echo "Query String is: " . $query_string;
  //setup the API access token
  setApiAccessToken();
  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");
  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $result = curl_exec($ch);
  curl_close($ch);
  //return $result;
  return $result;
}

function generateBarcodeCheckDigit($number, $iterations = 1) {
  while ($iterations-- >= 1)
  {
      $stack = 0;
      $number = str_split(strrev($number), 1);

      foreach ($number as $key => $value)
      {
          if ($key % 2 == 0)
          {
              $value = array_sum(str_split($value * 2, 1));
          }
          $stack += $value;
      }
      $stack %= 10;
      if ($stack != 0)
      {
          $stack -= 10;
      }
      $number = implode('', array_reverse($number)) . abs($stack);
  }
  return $number;
  }

function getPatronsWithHoldsOnHoldshelf() {
  // returns an array of patron id strings
  //echo "Updated date is: " . $updatedExpirationString;
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/query?offset=0&limit=600';

  //echo "URI is: " . $uri;
  $query_string = '{
    "queries": [
      {
        "target": {
          "record": {
            "type": "patron"
          },
          "id": 808080
        },
        "expr": [
          {
            "op": "equals",
            "operands": [
              "i"
            ]
          }
        ]
      },
      "or",
      {
        "target": {
          "record": {
            "type": "patron"
          },
          "id": 808080
        },
        "expr": [
          {
            "op": "equals",
            "operands": [
              "b"
            ]
          }
        ]
      }
    ]
  }';
  //echo "Query String is: " . $query_string;
  //setup the API access token
  setApiAccessToken();
  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");
  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $result = curl_exec($ch);
  curl_close($ch);
  //return $result;
  $patronIdArray = json_decode($result, true);
  $patronIdArray = $patronIdArray["entries"];
  return $patronIdArray;
}

function getHoldInfo($holdID) {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/holds/';
  $uri .= $holdID;
  $apiToken = getCurrentApiAccessToken();
  // Build the header
  $headers = array(
      "Authorization: Bearer " . $apiToken);

  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  $result = json_decode(curl_exec($ch),true);
  curl_close($ch);
  return $result;
}

function getPatronsExpiredHolds($patronID) {
  // returns one patrons expired holds - just the ids
  $today_date = strtotime('today');
  $yesterday = date('Y-m-d', strtotime('-1 day', $today_date));
  $tomorrow = date('Y-m-d', strtotime('+1 day', $today_date));
  $fourteendaysfromtoday = date('Y-m-d',strtotime('+14 days',$today_date));
  $yesterday = date('Y-m-d', strtotime('-1 day', $today_date));
  $today = date("Y-m-d",strtotime("now"));
  $holdresult = getPatronHolds($patronID);
  $onePatronExpiredHoldList = [];
  //pre($holdresult);
  foreach ($holdresult as $hold) {
    if(is_array($hold)) {
      foreach($hold as $individualHold) {
        if(isset($individualHold['pickupByDate']))
        {
          $holdpickupDate = strLeft($individualHold['pickupByDate'],10);
          if($holdpickupDate < $today) {
            array_push($onePatronExpiredHoldList, $individualHold['id']);
            //echo 'hold id is: ' . $individualHold['id'];
            //echo " Hold is due to be picked up today " . $holdpickupDate;
            //lb();
            //$patronInfo = getPatronDetails($patronID);
            //pre($patronInfo);
            //lb();
            //$holdInfo = getHoldInfo(stripped($individualHold['id']));
            //pre($holdInfo);
            //lb();
            //echo 'patron id is: ' . $patronID;
            //$details = getPatronDetails($patronID);
            //pre($details);

          }
        }
      }
    }
  }
  return $onePatronExpiredHoldList;
}

function getPatronHolds($patronID) {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';
  $uri .= $patronID;
  $uri .= '/holds?limit=1000&offset=0';
  $apiToken = getCurrentApiAccessToken();
  // Build the header
  $headers = array(
      "Authorization: Bearer " . $apiToken
  );

  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = json_decode(curl_exec($ch),true);
  curl_close($ch);
  return $result;
}

function getNumberOfHolds($patronID) {
  $patronHoldsInfo = getPatronHolds($patronID);
  $numberOfHolds = $patronHoldsInfo['total'];
  return $numberOfHolds;
}

function cancelPatronHold($patronID, $holdid) {
  echo 'inside function';
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/holds/';
  $uri .= $holdid;
  $apiToken = getCurrentApiAccessToken();
  // Build the header
  $headers = array(
      "Authorization: Bearer " . $apiToken
  );
  lb();
  echo 'URI is: ' . $uri;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
  //curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
}

function expiredHoldsList() {
  $allExpiredholds = [];
  $result = getPatronsWithHoldsOnHoldshelf();
  foreach ($result as $thisId) {
    $patronID = stripped($thisId['link']);
    $patronDetails = getPatronDetails($patronID);
    $patronName = $patronDetails['names']['0'];
    //get the patron detail from the api
    //$patron_detail = getPatronDetails(stripped($thisId['link']));
    //$patronID = $patron_detail['id'];
    //$holdresult = getPatronHolds($patronID);
    //pre($holdresult);
    //lb();
    //doesPatronHaveExpiredHoldOnShelf($patronID);
    $expiredHolds = getPatronsExpiredHolds($patronID);
    if(!empty($expiredHolds)) {
      foreach ($expiredHolds as $oneHold) {
        array_push($allExpiredholds,$oneHold);
      }
    }
  }
  return $allExpiredholds;
}

function getPatronsCreatedOnDate($date) {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/?createdDate=';
  $uri .= $date;

  $apiToken = getCurrentApiAccessToken();

  // Build the header
  $headers = array(
      "Authorization: Bearer " . $apiToken
  );

  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = json_decode(curl_exec($ch),true);

  curl_close($ch);
  return $result;
}

function getNextBarcode() {
  $barcodefile = fopen('../../private/currentbarcode.txt', 'r');
  $line = fgets($barcodefile);
  fclose($barcodefile);
  $barcodefloat = floatval($line);
  $newbarcode = generateBarcodeCheckDigit($barcodefloat);
  $writefile = fopen('../../private/currentbarcode.txt', "w");
  if(flock($writefile,LOCK_EX)) {
    fwrite($writefile, $barcodefloat + 1);
    flock($writefile,LOCK_UN);
  }
  fclose($writefile);
  return $newbarcode;
}

function generateRandomPIN($min, $max, $quantity) {
//  $random_number = intval( "0" . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9)); // random(ish) 6 digit int
//  return $random_number;
$numbers = range($min, $max);
shuffle($numbers);
$pinarray = array_slice($numbers, 0, $quantity);
$pinholder = '';
foreach($pinarray as $pinnumber) {
  $pinholder = $pinholder . $pinnumber;
  }
  return $pinholder;
}

function createOnlinePatron($patronArrayObj) {
  $randomPIN = generateRandomPIN(0,9,5);
  $newBarcode = getNextBarcode();
  $yearFromToday = strtotime('+1 year');
  $expirationString = date('Y-m-d', $yearFromToday);
  $email = $patronArrayObj['email'];
  $name = $patronArrayObj['name'];
  $addressStreet = $patronArrayObj['addressStreet'];
  $citycommaProvince = $patronArrayObj['$citycommaProvince'];
  $postalCode = $patronArrayObj['postalCode'];
  $addressType = $patronArrayObj['addressType'];
  $phoneNumber = $patronArrayObj['phonenumber'];
  $numberType = $patronArrayObj['numberType'];
  $homelibrary = $patronArrayObj['homelibrary'];
  //$pin = substr($phoneNumber, -4,4);
  //$barcode = $patronArrayObj['barcode'];
  //$barcode = '21387001717764';  TEST CARD USED SO I DONT CHEW UP EXTRA CARDS IN MY CARD DB
  $expirationDate = $expirationString;
  $birthdate = $patronArrayObj['birthdate'];

  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/';

  $query_string ='{"emails": ["';
  $query_string .=strip_tags($email);
  $query_string .='"],"names": ["';
  $query_string .=strip_tags($name);
  $query_string .='"],"addresses":[{"lines": ["';
  $query_string .= strip_tags($addressStreet);
  $query_string .='", "';
  $query_string .= strip_tags($citycommaProvince);
  $query_string .= '", "';
  $query_string .= strip_tags($postalCode);
  $query_string .='"],"type": "';
  $query_string .= strip_tags($addressType);
  $query_string .= '"}],"phones": [{"number": "';
  $query_string .= strip_tags($phoneNumber);
  $query_string .= '","type": "';
  $query_string .= strip_tags($numberType);
  $query_string .= '"}],"pin": "';
  $query_string .= $randomPIN;
  $query_string .= '","barcodes": ["';
  $query_string .= $newBarcode;
  $query_string .= '"],"expirationDate": "';
  $query_string .= strip_tags($expirationDate);
  $query_string .='","birthDate": "';
  $query_string .= strip_tags($birthdate);
  $query_string .= '","homeLibraryCode": "';
  $query_string .= $homelibrary;
  $query_string .= '"}';
  //echo $query_string;

  //setup the API access token
  setApiAccessToken();
  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");
  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch);
  curl_close($ch);
  //return $result;
  $newPatronInfo = array(
    'patronIDString'=>$result,
    'pin'=>$randomPIN,
    'barcode'=>$newBarcode);
  return $newPatronInfo;
  //$addressDetail = $currentAddress['lines'];

}

function createLibraryCardImage($nextbarcode) {
  $bc = new Barcode39($nextbarcode);
  // set text size
  $bc->barcode_text_size = 5;
  // set barcode bar thickness (thick bars)
  $bc->barcode_bar_thick = 4;
  // set barcode bar thickness (thin bars)
  $bc->barcode_bar_thin = 2;
  // save barcode GIF file
  $gifname = $nextbarcode . '.gif';
  $bc->draw($gifname);
  $src = imagecreatefromgif($gifname);
  $dest = imagecreatefromjpeg('images/card.jpg');
  imagealphablending($dest, false);
  imagesavealpha($dest, true);
  imagecopymerge($dest, $src, 230, 430, 0, 0, 424, 80, 100); //have to play with these numbers for it to work for you, etc.
  $imageName = $nextbarcode . '.png';
  imagepng($dest, $imageName);
  imagedestroy($dest);
  imagedestroy($src);

}

function delete_oldfiles($dir,$secs,$pattern = "/*")
{
  //delete files in folder based on path and patterns
  $now = time();
  foreach(glob("$dir$pattern") as $f) {
    if (is_file($f) && ($now - filemtime($f) > $secs)) unlink($f);
  }
}

function isAddressValid($address) {
  // check to see if the address that the patron provided is valid.  Specifically does the postal code match the address
  $formattedAddress = getFormattedAddress($address);
  $providedPostalCode = $address['postalCode'];
  $geoPostalcode = getPostalCode($address);
  //echo 'Provided Postal Code is: ' . $providedPostalCode;
  //echo 'GeoCoded Postal Code is: ' . $geoPostalcode;
  if(str_replace(' ', '', $providedPostalCode) == str_replace(' ', '', $geoPostalcode)) {
    return true;
  }
  else
  {
    return false;
  }
}

function setUpakneeApiAccessToken() {
  $uri = 'https://rest.upaknee.com';
  $authCredentials = base64_encode(apiKey . ":" . apiSecret);

  // Build the header
  $headers = array(
      "Authorization: Basic " . $authCredentials,
      "Content-Type: application/x-www-form-urlencoded");
  // make the request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
  $result = json_decode(curl_exec($ch));
  curl_close($ch);
  // save the access token and creation time to a session variable
  $_SESSION['apiAccessToken'] = $result->access_token;
  $_SESSION['apiAccessTokenCreationDate'] = time();
}

function getNumberOfOnlineRegistrations() {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/query';
  $uri .= '?offset=0';
  $uri .= '&limit=100000';
  //echo $uri;
  $query_string = '{
    "target": {
      "record": {
        "type": "patron"
      },
      "field": {
        "tag": "x"
      }
    },
    "expr": {
      "op": "has",
      "operands": [
        "Created via OnlinePatronCreationForm v1",
        ""
      ]
    }
  }';
  //echo " query string is " . $query_string . " ";
  //setup the API access token
  setApiAccessToken();

  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //echo " this is the API token " . $apiToken . " ";

  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");

  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  //get the result from our json api query
  $result = curl_exec($ch);
  $jcode = json_decode($result, true);
  return $jcode['total'];

}

function getNumberOfMarketingTargets() {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/query';
  $uri .= '?offset=0';
  $uri .= '&limit=100000';
  //echo $uri;
  $query_string = '{
    "target": {
      "record": {
        "type": "patron"
      },
      "field": {
        "tag": "x"
      }
    },
    "expr": {
      "op": "equals",
      "operands": [
        "MARKETING_PREFERENCE = TRUE",
        ""
      ]
    }
  }';
  //echo " query string is " . $query_string . " ";
  //setup the API access token
  setApiAccessToken();

  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //echo " this is the API token " . $apiToken . " ";

  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");

  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  //get the result from our json api query
  $result = curl_exec($ch);
  $jcode = json_decode($result, true);
  return $jcode['total'];

}


function getOnlineRegistrations() {
  $uri = 'https://';
  $uri .= appServer;
  $uri .= ':443/iii/sierra-api/v';
  $uri .= apiVer;
  $uri .= '/patrons/query';
  $uri .= '?offset=0';
  $uri .= '&limit=100000';
  //echo $uri;
  $query_string = '{
    "target": {
      "record": {
        "type": "patron"
      },
      "field": {
        "tag": "x"
      }
    },
    "expr": {
      "op": "has",
      "operands": [
        "Created via OnlinePatronCreationForm v1",
        ""
      ]
    }
  }';
  //echo " query string is " . $query_string . " ";
  //setup the API access token
  setApiAccessToken();

  //get the access token we just created
  $apiToken = getCurrentApiAccessToken();
  //echo " this is the API token " . $apiToken . " ";

  //build the headers that we are going to use to post our json to the api
  $headers = array(
      "Authorization: Bearer " . $apiToken,
      "Content-Type:  application/json");

  //use the headers, url, and json string to query the api
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  //get the result from our json api query
  $result = curl_exec($ch);
  $jcode = json_decode($result, true);
  return $jcode;

}


?>
