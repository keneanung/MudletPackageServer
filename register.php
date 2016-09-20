<?php

$hostname = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['PHP_SELF']);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  require_once 'config.php';

  // Connect to the database
  $con = mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_database);
  $name = $_POST["username"];
  $email = $_POST["email"];
  $clear_pass = $_POST["password"];

  $password = sha1($clear_pass . $salt);

  $sth = mysqli_prepare($con, "INSERT INTO user " . "(name, email, password, salt, created_on, verify_string, verified) " . "VALUES (?, ?, ?, ?, NOW(), ?, 0)");
  mysqli_stmt_bind_param($sth, "sssss", $name, $email, $password, $salt, $verify_string);
  mysqli_stmt_execute($sth);
  if (mysqli_stmt_errno($sth)) {
    echo mysqli_stmt_error($sth);
  }
  //print "$email, $mail_body";
  // Weiterleitung zur geschützten Startseite

  if ($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.1') {
    if (php_sapi_name() == 'cgi') {
      header('Status: 303 See Other');
    } else {
      header('HTTP/1.1 303 See Other');
    }
  }

  header('Location: http://' . $hostname . ($path == '/' ? '' : $path) . '/administer.php');
  exit ;
}
?>

