<?php
include('auth.php');
require_once "config.php";
$con=mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_database);

// Check connection
if (mysqli_connect_errno())
  {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
$stmt = mysqli_prepare($con,"SELECT name, version, description FROM packages WHERE author = ?");
mysqli_stmt_bind_param($stmt,"s", $_SESSION["username"]);
mysqli_execute($stmt);
mysqli_stmt_bind_result($stmt, $name, $version, $description);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
 <head>
  <title>Restricted access</title>
 </head>
 <body>
  <h1>Here you can administer your packages.</h1>
  <p>You are now logged in.</p>
  <p>You can also <a href="logout.php">log off</a>.</p>
    <table>
      <tr><td>Package name</td><td>Version</td><td>Description</td><td>Actions</td></tr>
<?php
    while(mysqli_stmt_fetch($stmt))
      {
?>
      <tr>
<?php
        echo "<td>" . $name . "</td>";
        echo "<td>" . $version . "</td>";
        echo "<td>" . $description . "</td>";
?>
        <td><a href="delete.php?name=<?php echo $name; ?>">Delete package</a> <a href="change.php?name=<?php echo $name; ?>">Modify Package</a></td>
      </tr>
<?php
       }
?>
    </table>
 </body>
</html>
