<?php
if (isset($_SESSION['userloggedin'])) {
	session_destroy();
	$_SESSION = array();
}

if (isset($_POST['send'])) {
	require_once ('include/connect.php');		// Connect to the database
	require_once ('include/blog_class.php');	// Include the blog class
	require_once ('include/user_class.php');	// Include the user class

	$user = new user($db);						// Create a new instance of user
	$blog = new blog($db);				// Create a new instance of the blog class

	$email = trim(strtolower($_POST['email']));
	$password = trim($_POST['password']);

	$error = '';								// initialise an error array

	if ($user->userLogin($email, $password)) {								// Check the user login id and password
		if ($_SESSION['userloggedin']) {									// If the user has been logged in
			if ($blog->userHasBlog($_SESSION['bloggerid'])) {				// If no blog exists for this user
				header("location: admin.php?blogIndex=0&create=create");	// 	Go to the admin page and display the create form
			} else {														// else
				header("location: admin.php?blogIndex=0");					// 	Go to the admin page and display the admin form
			}
		}
	} else {
		$error = 'Invalid email or password - re-enter';
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Simple Blog Login</title>
	<meta charset="UTF-8" />
	<meta name="viewport"  content="width=device-width,initial-scale=1.0" />
	<link rel="icon" type="image/ico" href="favicon.ico" />
	<link type="text/css" rel="stylesheet" href="css/blogCSS.css" />
</head>

<body>
<div id="wrapper">
	<header id="header" role="banner" >
		<h1>Simple Blog Admin</h1>
	</header>

	<nav>
		<ul>
			<li><a href="index.php">Home</a></li>
		<ul>
	</nav>

	<div id="adminCcontent">
		<form id="login" name="loginForm" method="post" action="" autocomplete="off">
			<fieldset class="loginClass">
				<p>
				<label class="label" for="email" autocomplete="off">Email Address:</label>
				<input name="email" id="email"  />
				</p>
				<p>
				<label class="label" for="password" type="password">Password:</label>
				<input name="password" type="password" type="password"  />
				</p>
				<p>
					<label class="label" for="admin">&nbsp;</label>			<!-- Aligns the following button to the above fields. nbsp - non-breaking space. -->
					<button type="submit" id="admin" name="send" >Login</button>
					<?php
					if (isset($error)) {
						echo "<br><span class='error'>" . $error . "</span>";
					}
					?>
				</p>
				<p>
					<label class="label" for="newUser">or:</label>
					<a id="newUser" href="register.php">Create New User</a>
				</p>
			</fieldset>
		</form>
	</div>
	&nbsp;
</div>
</body>
</html>