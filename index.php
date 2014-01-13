<?php
require_once "config.php";
$con=mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_database);

// Check connection
if (mysqli_connect_errno())
  {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
$result = mysqli_query($con,"SELECT * FROM packages");
$path = "http://" . $_SERVER["SERVER_NAME"] . dirname($_SERVER["SCRIPT_NAME"]);

if ($_GET["op"]=="json")
  {
    $counter = 0;
    $outer = array();
    while($row = mysqli_fetch_assoc($result))
      {
        if (count(glob($row["name"].".*")) > 0)
          {
            $entry = array();
            $entry["name"] = $row["name"];
            $entry["version"] = $row["version"];
            $entry["description"] = $row["description"];
            $entry["author"] = $row["author"];
            $fileName =  glob($row["name"] . ".*");
            $entry["url"] = $path . "/" . $fileName[0];
            $outer[$counter] = $entry;
            $counter++;
          }
       }
     echo json_encode($outer);
  }else{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
  <head>
    <title>Mudlet repository listing</title>
  </head>
  <body>
    To upload new packages or manage your packages, please <a href="<?php echo $path."/login.php"; ?>">log in</a> or <a href="register.php">register</a><br/>
    <br/>
    <table>
      <tr><td>Package name</td><td>Version</td><td>Description</td><td>Author</td></tr>
<?php
    while($row = mysqli_fetch_array($result))
      {
?>
      <tr>
<?php
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['version'] . "</td>";
        echo "<td>" . $row['description'] . "</td>";
        echo "<td>" . $row['author'] . "</td>";
?>
      </tr>
<?php
       }
?>
    </table>
  </body>
</html>
<?php
  }
?>
