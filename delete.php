<?php
  include 'auth.php';
  require_once 'config.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
 <head>
  <title>Restricted access</title>
 </head>
  <body>
  	<?php
  	  if (!isset($_GET["name"]) && $_SERVER['REQUEST_METHOD'] != 'POST') {
	?>
	No package selected. Please <a href="administer.php">return</a> and select a package.
	<?php
		}elseif($_SERVER['REQUEST_METHOD'] == 'POST') {
			$con=mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_database);

            // Check connection
            if (mysqli_connect_errno())
            {
              echo "Failed to connect to MySQL: " . mysqli_connect_error();
            }
            $stmt = mysqli_prepare($con,"DELETE FROM packages WHERE author = ? and name = ?");
            mysqli_stmt_bind_param($stmt,"ss", $_SESSION["username"], $_POST["name"]);
            mysqli_execute($stmt);
			$count = mysqli_stmt_affected_rows($stmt);
			if ($count == 1) {
				echo "Package successfully deleted.";
        $files = glob($_POST["name"].".*");
        unlink($files[0]);
			}else{
				echo "Couldn't delete package. Please make sure you used the right package name and are the author.";
			}
	?>
	<br />
	<a href="administer.php">Return to the package listing.</a>
	<?php
		}else{
  	?>
    Are you sure your want to delete package <?php echo $_GET["name"]; ?>?
    <form action="delete.php" method="post">
      <input type="hidden" name="name" value="<?php echo $_GET["name"]; ?>" />
      <input type="submit" value="Delete" /> or <a href="administer.php">Cancel.</a>
    </form>
    <?php
		}
    ?>
  </body>
</html>