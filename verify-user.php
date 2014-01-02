<?php
// Connect to the database
require_once 'config.php';

// Connect to the database
$con = mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_database);
$sth = mysqli_prepare($con,'UPDATE user SET verified = 1 WHERE email = ? AND verify_string = ? AND verified = 0');
mysqli_stmt_bind_param($sth, "ss", $_GET['email'], $_GET['verify_string']);
$res = mysqli_execute($sth);
if (!$res) {
  print "Please try again later due to a database error.";
} else {
  if (mysqli_stmt_affected_rows($sth) == 1) {
    print "Thank you, your account is verified.";
  } else {
    print "Sorry, you could not be verified.";
  }
}
?>