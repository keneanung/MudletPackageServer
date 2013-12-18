<?php
include 'auth.php';
require_once 'config.php';

$name_valid = true;
$version_valid = true;
$description_valid = true;
$file_valid = TRUE;
$db_success;
$move_success;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (!(strlen($_POST["name"]) > 0 && strlen($_POST["name"]) < 50 && preg_match("/^\w+$/", $_POST["name"]) == 1)) {
    $name_valid = FALSE;
  }
  if (!(strlen($_POST["version"]) > 0 && strlen($_POST["version"]) < 15 && preg_match("/^\d+\.\d+\.\d+$/", $_POST["version"]) == 1)) {
    $version_valid = FALSE;
  }
  if (!(strlen($_POST["description"]) > 0 && strlen($_POST["description"]) < 120)) {
    $description_valid = FALSE;
  }
  $extension = substr($_FILES["file"]["name"], strrpos($_FILES["file"]["name"], '.') + 1);
  $mimetype = $_FILES["file"]["type"];
  if (!(($extension == "xml" || $extension == "mpackage" || $extension == "zip") && $_FILES["file"]["size"] < 20000000 && ($mimetype == "application/octet-stream" || $mimetype == "text/xml" || $mimetype == "application/zip"))) {
    $file_valid = FALSE;
  }

  if ($name_valid && $version_valid && $description_valid) {
    $con = mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_database);

    // Check connection
    if (mysqli_connect_errno()) {
      echo "Failed to connect to MySQL: " . mysqli_connect_error();
      exit ;
    }
    $stmt = mysqli_prepare($con, "SELECT count(*) FROM packages WHERE name = ?");
    mysqli_stmt_bind_param($stmt, "s", $_POST["name"]);
    mysqli_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    if ($count > 0) {
      $name_valid = false;
    } else {
      $stmt = mysqli_prepare($con, "INSERT INTO packages (name, version, description, author) VALUES (?, ?, ?, ?)");
      if (! $stmt) {
          echo mysqli_errno();
        exit;
      }
      mysqli_stmt_bind_param($stmt, "ssss", $_POST["name"], $_POST["version"], $_POST["description"], $_SESSION["username"]);
      $db_success = mysqli_stmt_execute($stmt);
      if ($db_success) {
        $move_success = move_uploaded_file($_FILES['file']['tmp_name'], $_POST["name"].".dat");
      }
    }
  }

  if ($name_valid && $version_valid && $description_valid && $file_valid && $db_success && $move_success) {

    $hostname = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['PHP_SELF']);

    if ($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.1') {
      if (php_sapi_name() == 'cgi') {
        header('Status: 303 See Other');
      } else {
        header('HTTP/1.1 303 See Other');
      }
    }

    header('Location: http://' . $hostname . ($path == '/' ? '' : $path) . '/administer.php');
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
 <head>
  <title>Restricted access</title>
 </head>
  <body>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (!$db_success) {
    echo "Database error. Please try again or contact the administrator.";
  }elseif(!$move_success) {
    echo "Error while creating the file. Please try again or contact the administrator.";
  }

}
 ?>
    <form action="create.php" method="post" enctype="multipart/form-data">
      <table>
      	<tr>
          <td>Package name:</td><td><input name="name" type="text" value="<?php echo $_POST["name"] ?>"/></td><td><?php echo $name_valid ? "" : "The name should be less than 50 pure ASCII characters long. Additionally make sure there is no Package with that name yet." ?></td>
        </tr>
        <tr>
          <td>Package version:</td><td><input name="version" type="text"  value="<?php echo(isset($_POST["version"]) ? $_POST["version"] : "1.0.0"); ?>"/></td><td><?php echo $version_valid ? "" : "The version should be less than 15 characters long and have the format 'd.d.d'." ?></td>
        </tr>
        <tr>
           <td>Package description:</td><td><textarea name="description"><?php echo $_POST["description"] ?></textarea></td><td><?php echo $description_valid ? "" : "The description should be less than 120 characters long." ?></td>
        </tr>
        <tr>
        	<td>Package file:</td><td><input type="file" name="file"/></td><td><?php echo $file_valid ? "" : "The file should be less than 20 MB big, and of the filetypes zip, mpackage or xml." ?></td>
        </tr>
      </table>
      <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
      <input type="submit" value="Create" /> or <a href="administer.php">Cancel.</a>
    </form>
  </body>
</html>