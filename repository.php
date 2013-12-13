<?php
$con=mysqli_connect("localhost","","","mudlet-repository");

// Check connection
if (mysqli_connect_errno())
  {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
$result = mysqli_query($con,"SELECT * FROM packages");

if ($_GET["op"]=="json")
  {
    $counter = 0;
    $path = "http://" . $_SERVER["SERVER_NAME"] . dirname($_SERVER["SCRIPT_NAME"]);
    while($row = mysqli_fetch_assoc($result))
      {
        if (count(glob($row["name"].".*")) == 1)
          {
            $fileName =  glob($row["name"] . ".*");
            $row["url"] = $path . "/" . $fileName[0];
            $outer[$counter] = $row;
            $counter++;
          }
       }
     echo json_encode($outer);
  }else{
?>
<html>
  <head>
    <title>Mudlet repository listing</title>
  </head>
  <body>
    <table>
      <tr><td>Package name</td><td>Version</td><td>Description</td></tr>
<?php
    while($row = mysqli_fetch_array($result))
      {
?>
      <tr>
<?php
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['version'] . "</td>";
        echo "<td>" . $row['description'] . "</td>";
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
