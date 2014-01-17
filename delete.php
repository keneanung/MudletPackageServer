<?php
include 'auth.php';
require_once 'config.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
	<head>
		<title>Restricted access</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link type="text/css" href="styles.css" rel="stylesheet" />
	</head>
	<body>
		<?php
    $htmlContent = "";
    $boxSize = "";
    if (!isset($_GET["name"]) && $_SERVER['REQUEST_METHOD'] != 'POST') {
      $htmlContent = 'No package selected. Please <a href="administer.php">return</a> and select a package.';
      $boxSize = "250";
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $con = mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_database);

      // Check connection
      if (mysqli_connect_errno()) {
        $htmlContent = "Failed to connect to MySQL: " . mysqli_connect_error();
      }
      $stmt = mysqli_prepare($con, "DELETE FROM packages WHERE author = ? and name = ?");
      mysqli_stmt_bind_param($stmt, "ss", $_SESSION["username"], $_POST["name"]);
      mysqli_execute($stmt);
      $count = mysqli_stmt_affected_rows($stmt);
      if ($count == 1) {
        $htmlContent = "Package successfully deleted.";
        $files = glob($_POST["name"] . ".dat");
        unlink($files[0]);
      } else {
        $htmlContent = "Couldn't delete package. Please make sure you used the right package name and are the author.";
      }
      $htmlContent .= '<br /><a href="administer.php">Return to the package listing.</a>';
      $boxSize = "200";
    } else {
      $htmlContent = "Are you sure your want to delete package $_GET[name] ?
      <form action=\"delete.php\" method=\"post\">
        <input type=\"hidden\" name=\"name\" value=\"$_GET[name]\" />
        <input type=\"submit\" value=\"Delete\" /> or <a href=\"administer.php\">Cancel.</a>
      </form>";
      $boxSize = "700";
    }
		?>
		<div class="box center">
			<div class="center" style="width: <?php echo "$boxSize"; ?>px">
				<?php
        echo "$htmlContent";
				?>
			</div>
		</div>
	</body>
</html>