<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  require_once 'config.php';

  // Connect to the database
  $con = mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_database);
  $name = $_POST["username"];
  $email = $_POST["email"];
  $clear_pass = $_POST["password"];
  // generate verify_string
  $verify_string = '';
  for ($i = 0; $i < 16; $i++) {
    $verify_string .= chr(mt_rand(32, 126));
  }
  $salt = "";
  for ($i = 0; $i < 50; $i++) {
    $salt .= chr(mt_rand(32, 126));
  }
  $password = sha1($clear_pass . $salt);

  $sth = mysqli_prepare($con, "INSERT INTO user " . "(name, email, password, salt, created_on, verify_string, verified) " . "VALUES (?, ?, ?, ?, NOW(), ?, 0)");
  mysqli_stmt_bind_param($sth, "sssss", $name, $email, $password, $salt, $verify_string);
  mysqli_stmt_execute($sth);
  if (mysqli_stmt_errno($sth)) {
    echo mysqli_stmt_error($sth);
  }
  $verify_string = urlencode($verify_string);
  $safe_email = urlencode($email);
  $verify_url = "http://schova.de/mudlet-repository/verify-user.php";
  $mail_body = <<<_MAIL_
To $name:
Please click on the following link to verify your account creation:

$verify_url?email=$safe_email&verify_string=$verify_string

If you do not verify your account in the next seven days, it will be deleted.
_MAIL_;

  $hostname = $_SERVER['HTTP_HOST'];
  $path = dirname($_SERVER['PHP_SELF']);

  mail($email, "User Verification", $mail_body, "FROM: noreply@$hostname");
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
 <head>
  <title>Register user</title>
 </head>
 <body>
  <form action="register.php" method="post">
   Username: <input type="text" name="username" /><br />
   E-Mail: <input type="text" name="email" /><br />
   Password: <input type="password" name="password" /><br />
   <input type="submit" value="Register" />
  </form>
 </body>
</html>
