<?php
     require_once "config.php";
     $con=mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_database);

     if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      session_start();

      $username = $_POST['username'];
      $password = $_POST['password'];

      $hostname = $_SERVER['HTTP_HOST'];
      $path = dirname($_SERVER['PHP_SELF']);

      $stmt = mysqli_prepare($con, "SELECT name, password, salt FROM user WHERE  name = ?");
      mysqli_stmt_bind_param($stmt, "s", $username);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_bind_result($stmt, $name, $pass, $salt);

      if(mysqli_stmt_fetch($stmt)){

        // Benutzername und Passwort werden überprüft
        if (sha1($password . $salt) == $pass) {
         $_SESSION['loggedin'] = true;
		 $_SESSION['username'] = $username;

         // Weiterleitung zur geschützten Startseite
         if ($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.1') {
          if (php_sapi_name() == 'cgi') {
           header('Status: 303 See Other');
           }
          else {
           header('HTTP/1.1 303 See Other');
           }
          }

         header('Location: http://'.$hostname.($path == '/' ? '' :
$path).'/administer.php');
         exit;
         }
        }
      }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
 <head>
  <title>Restricted access</title>
 </head>
 <body>
<?php
     if ($_SERVER['REQUEST_METHOD'] == 'POST') {
       echo "Username and/or password wrong.";
     }
?>
  <form action="login.php" method="post">
   Username: <input type="text" name="username" /><br />
   Passwort: <input type="password" name="password" /><br />
   <input type="submit" value="Log In" />
  </form>
 </body>
</html>
