<?php
include 'auth.php';
require_once 'config.php';

$name_valid = true;
$version_valid = true;
$description_valid = true;
$file_valid = TRUE;
$db_success = TRUE;
$move_success = TRUE;
$con = mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_database);

// Check connection
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  exit ;
}

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

  if ($name_valid && $version_valid && $description_valid && $file_valid) {
    $stmt = mysqli_prepare($con, "SELECT count(*) FROM packages WHERE name = ?");
    mysqli_stmt_bind_param($stmt, "s", $_POST["name"]);
    mysqli_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    if (!(($count == 1 && $_POST["mode"] == "modify") || ($count == 0 && $_POST["mode"] == "create"))) {
      $name_valid = false;
    } else {
      $stmt_string = $_POST["mode"] == "modify" ? "UPDATE packages SET version = ?, description = ?, author = ?, extension = ? WHERE name = ?" : "INSERT INTO packages (version, description, author, extension, name) VALUES (?, ?, ?, ?, ?)";
      $stmt = mysqli_prepare($con, $stmt_string);
      if (!$stmt) {
        echo mysqli_stmt_errno($stmt);
        exit ;
      }
      mysqli_stmt_bind_param($stmt, "sssss", $_POST["version"], $_POST["description"], $_SESSION["username"], $extension, $_POST["name"]);
      $db_success = mysqli_stmt_execute($stmt);
      if ($db_success) {
        $move_success = move_uploaded_file($_FILES['file']['tmp_name'], $_POST["name"] . ".dat");
      }
    }
  }

  if ($name_valid && $version_valid && $description_valid && $file_valid && $db_success && $move_success) {

    if ($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.1') {
      if (php_sapi_name() == 'cgi') {
        header('Status: 303 See Other');
      } else {
        header('HTTP/1.1 303 See Other');
      }
    }

    $hostname = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['PHP_SELF']);
    header('Location: http://' . $hostname . ($path == '/' ? '' : $path) . '/administer.php');
    exit ;
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
 <head>
  <title>Restricted access</title>
  <script src="functions.js" type="text/javascript" charset="utf-8">  </script>
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <script type="text/javascript">
        <?php
    $result = mysqli_query($con, "SELECT name FROM packages");
    $outer = array();
    $counter = 0;
    while ($row = mysqli_fetch_row($result)) {
      $outer[$counter] = $row[0];
      $counter++;
    }
    $outer_json = json_encode($outer);
    mysqli_free_result($result);
    ?>
    package_json = '<?php echo "$outer_json"; ?>';
  </script>
 </head>
  <body>
<?php
$name_value;
$version_value;
$description_value;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (!$db_success) {
    echo "Database error. Please try again or contact the administrator.";
  } elseif (!$move_success) {
    echo "Error while creating the file. Please try again or contact the administrator.";
  }
}
if ($_SERVER["REQUEST_METHOD"] == "GET" && $_GET["mode"] == "modify") {
  $name_value = $_GET["name"];
  $stmt = mysqli_prepare($con, "SELECT version, description FROM packages WHERE name = ? AND author = ?");
  if (!$stmt) {
    echo mysqli_stmt_error($stmt);
  }
  mysqli_stmt_bind_param($stmt, "ss", $name_value, $_SESSION["username"]);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_store_result($stmt);
  mysqli_stmt_bind_result($stmt, $version_value, $description_value);
  if (mysqli_stmt_num_rows($stmt) == 1) {
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_free_result($stmt);
  }else {
      echo "Unexpected number of results retrieved. Please contact the administrator.";
      mysqli_stmt_free_result($stmt);
      exit;
  }
}
if ($_SERVER["REQUEST_METHOD"] == "POST"){
  $name_value = $_POST["name"];
  $description_value = $_POST["description"];
}
$name_value = isset($name_value)?$name_value:"";
$mode_value = $_SERVER["REQUEST_METHOD"] == "GET" ? $_GET["mode"] : $_POST["mode"];
$version_value = isset($_POST["version"]) ? $_POST["version"] : "1.0.0";
$description_value = isset($description_value)?$description_value:"";
 ?>
    <form action="create.php" method="post" enctype="multipart/form-data">
      <table>
      	<tr>
          <td>Package name:</td><td><input name="name" type="text" value="<?php echo $name_value ?>"/></td><td><?php echo $name_valid ? "" : "The name should be less than 50 pure ASCII characters long. Additionally make sure there is no Package with that name yet." ?></td>
        </tr>
        <tr>
          <td>Package version:</td><td><input name="version" type="text"  value="<?php echo $version_value ?>"/></td><td><?php echo $version_valid ? "" : "The version should be less than 15 characters long and have the format 'd.d.d'." ?></td>
        </tr>
        <tr>
           <td>Package description:</td><td><textarea name="description"><?php echo $description_value ?></textarea></td><td><?php echo $description_valid ? "" : "The description should be less than 120 characters long." ?></td>
        </tr>
        <tr>
        	<td>Package file:</td><td><input type="file" name="file"/></td><td><?php echo $file_valid ? "" : "The file should be less than 20 MB big, and of the filetypes zip, mpackage or xml." ?></td>
        </tr>
      </table>
      <div id="dependencies">
        <input type="button" value="Add dependency" name="AddDependency" onclick="AddDependencyRow(package_json);" />
      </div>
      <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
      <input type="hidden" name="mode" value="<?php echo $mode_value; ?>" />
      <input type="submit" value="Submit" /> or <a href="administer.php">Cancel.</a>
    </form>
  </body>
</html>