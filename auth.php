<?php
     session_start();

     $hostname = $_SERVER['HTTP_HOST'];
     $path = dirname($_SERVER['PHP_SELF']);

     if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
      header('Location: http://'.$hostname.($path == '/' ? '' :
$path).'/login.php');
      exit;
      }
?>

