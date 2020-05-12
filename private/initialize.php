<?php

  ob_start(); // output buffering is turned on
  //ini_set( 'session.cookie_httponly', 1 );
  session_start(); // turn on sessions
  include('config.php');
  include('public_header.php');
  include('functions.php');
  include('phpqrcode.php');
  include('barcode39.php');
  configFileCheck();
  setApiAccessToken();
  $errors = [];
  ?>
