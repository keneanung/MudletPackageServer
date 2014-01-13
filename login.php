<?php
require_once "config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$con = mysqli_connect($sql_server, $sql_user, $sql_pass, $sql_database);
	session_start();

	$username = $_POST['username'];
	$password = $_POST['password'];

	$hostname = $_SERVER['HTTP_HOST'];
	$path = dirname($_SERVER['PHP_SELF']);

	$stmt = mysqli_prepare($con, "SELECT name, password, salt FROM user WHERE  name = ? AND verified = 1");
	mysqli_stmt_bind_param($stmt, "s", $username);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_bind_result($stmt, $name, $pass, $salt);

	if (mysqli_stmt_fetch($stmt)) {

		// check username and password
		if (sha1($password . $salt) == $pass) {
			$_SESSION['loggedin'] = true;
			$_SESSION['username'] = $username;

			// Wredirect to the restricted content
			if ($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.1') {
				if (php_sapi_name() == 'cgi') {
					header('Status: 303 See Other');
				} else {
					header('HTTP/1.1 303 See Other');
				}
			}

			header('Location: http://' . $hostname . ($path == '/' ? '' : $path) . '/administer.php');
			exit ;
		}
	}
}
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
		<div class="box center" style="margin-top: 75px">
			<h1 class="center" style="width: 250px">Package Admin</h1>
			<?php
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				echo '<div class="center error-message" style="width: 290px">Username and/or password wrong.</div>';
			}
			?>
			<form action="login.php" method="post" class="center" id="login-form">
				<div>
					<div>
						<div class="left">
							Username:
							<br/>
							Password:
						</div>
						<div>
							<input type="text" name="username" />
							<br />
							<input type="password" name="password" />
							<br />
						</div>

					</div>
					<div>
						<input type="submit" value="Log In" />
						or <a href="index.php">Cancel</a>
					</div>
				</div>
			</form>
		</div>
		</form>
	</body>
</html>
