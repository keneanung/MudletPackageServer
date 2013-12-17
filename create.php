<?php
  include 'auth.php';
  require_once 'config.php';
  
  $name_valid = true;
  $version_valid = true;
  $description_valid = true;
   
  if ($_SERVER["REQUEST_METHOD"]== "POST") {
	  if (!(strlen($_POST["name"]) > 0 && strlen($_POST["name"]) < 50)) {
		  $name_valid = FALSE;
	  }
	  if (!(strlen($_POST["version"]) > 0 && strlen($_POST["version"]) < 15 && 
	      preg_match("/^\d+\.\d+\.\d+$/", $_POST["version"]) == 1)) {
          $version_valid = FALSE;
	  }
      if (!(strlen($_POST["description"]) > 0 && strlen($_POST["description"]) < 120)) {
		  $description_valid = FALSE;
	  }
  }
  
  if ($name_valid && $version_valid && $description_valid) {
    $con=mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_database);

    // Check connection
    if (mysqli_connect_errno())
    {
      echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    $stmt = mysqli_prepare($con,"SELECT count(*) FROM packages WHERE name = ?");
    mysqli_stmt_bind_param($stmt,"s", $_POST["name"]);
    mysqli_execute($stmt);
	mysqli_stmt_bind_result($stmt, $count);
	mysqli_stmt_fetch($stmt);
	if ($count > 0) {
		$name_valid = false;
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
    <form action="create.php" method="post">
      <table>
      	<tr>
          <td>Package name:</td><td><input name="name" type="text" value="<?php echo $_POST["name"] ?>"/></td><td><?php echo $name_valid ? "" : "The name should be less than 50 characters long. Additionally make sure there is no Package with that name yet." ?></td>
        </tr>
        <tr>
          <td>Package version:</td><td><input name="version" type="text"  value="<?php echo(isset($_POST["version"])?  $_POST["version"] : "1.0.0" ); ?>"/></td><td><?php echo $version_valid ? "" : "The version should be less than 15 characters long and have the format 'd.d.d'." ?></td>
        </tr>
        <tr>
           <td>Package description:</td><td><textarea name="description"><?php echo $_POST["description"] ?></textarea></td><td><?php echo $description_valid ? "" : "The description should be less than 120 characters long." ?></td>
        </tr>
        <tr>
        	<td>Package file:</td><td><input type="file" name="file"/></td>
        </tr>
      </table>    
      <input type="submit" value="Create" /> or <a href="administer.php">Cancel.</a>
    </form>
  </body>
</html>