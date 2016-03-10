<?php
if (isset($_POST['send'])) {
	require_once ('include/connect.php');		// Connect to the database
	require_once ('include/user_class.php');	// Include the user class

	$user = new user($db);						// Create a new instance of user

	if ($_POST['send'] == 'create') {			// If form data has been posted using the create button
		$errors = $user->validateInput();		// validate it

		if (!$errors) {							// If no errors
			$user->createNewUser();				// Create a new user
			header("location: login.php");		// Return to the admin login page
		}
	} else if ($_POST['send'] == 'clear') {		// If the clear button had been clicked
		unset($errors);							// Initialise the error array
		unset($_POST);							// Initialise the POST array (to clear fields)
	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Simple Blog</title>
	<meta charset="UTF-8" />
	<meta name="viewport"  content="width=device-width,initial-scale=1.0" />
	<link rel="icon" type="image/ico" href="favicon.ico" />
	<link type="text/css" rel="stylesheet" href="css/blogCSS.css" />
</head>

<body>
<div id="wrapper">
	<header id="header" role="banner" >
		<h1>Simple Blog Create User</h1>
	</header>

	<nav>
		<ul>
			<li><a href="login.php">Login</a></li>
			<li><a href="index.php">Home</a></li>
		</ul>
	</nav>

	<!--
	For each field in the form:
		if a value is present in the POST array display it (except for the password fields)
		if an error has been returned then display the error message
	-->
	<div id="adminContent">
		<form id="userCreate" name="loginForm" method="post" action="register.php" autocomplete="off">
			<fieldset class="loginClass">
				<p>
				<label class="label" for="fname" autocomplete="off">First Name:</label>
				<input name="fname" id="fname" <?php if (isset($_POST['fname'])) {echo 'value = "' . $_POST['fname'] . '"';} ?> />
				<?php
					if (isset($errors['fname'])) {
						echo "<br><span class='error'>" . $errors['fname'] . "</span>";
					}
				?>
				</p>
				<p>
				<label class="label" for="lname" autocomplete="off">Last Name:</label>
				<input name="lname" id="lname" <?php if (isset($_POST['lname'])) {echo 'value = "' . $_POST['lname'] . '"';} ?> />
				<?php
					if (isset($errors['lname'])) {
						echo "<br><span class='error'>" . $errors['lname'] . "</span>";
					}
				?>
				</p>
				<p>
				<label class="label" for="email" autocomplete="off">Email:</label>
				<input name="email" id="email" <?php if (isset($_POST['email'])) {echo 'value = "' . $_POST['email'] . '"';} ?> />
				<?php
					if (isset($errors['email'])) {
						echo "<br><span class='error'>" . $errors['email'] . "</span>";
					}
				?>
				</p>
				<p>
				<label class="label" for="confemail" autocomplete="off">Confirm Email:</label>
				<input name="confemail" id="confemail" <?php if (isset($_POST['confemail'])) {echo 'value = "' . $_POST['confemail'] . '"';} ?> />
				<?php
					if (isset($errors['confemail'])) {
						echo "<br><span class='error'>" . $errors['confemail'] . "</span>";
					}
				?>
				</p>
				<p>
				<label class="label" for="password" type="password">Password:</label>
				<input name="password"  id="password" type="password" />
				<?php
					if (isset($errors['password'])) {
						echo "<br><span class='error'>" . $errors['password'] . "</span>";
					}
				?>
				</p>
				<p>
				<label class="label" for="confpassword" type="password">Confirm Password:</label>
				<input name="confpassword" id="confpassword" type="password"  />
				<?php
					if (isset($errors['confpassword'])) {
						echo "<br><span class='error'>" . $errors['confpassword'] . "</span>";
					}
				?>
				</p>
				<p>
				<label class="label" for="create">&nbsp;</label>			<!-- Aligns the following button to the above fields. nbsp - non-breaking space. -->
				<button type="submit" id="create" name="send" value="create">Create User</button>
				<button type="submit" id="clear" name="send" value="clear">Clear</button>
				</p>
			</fieldset>
		</form>
	</div>
	&nbsp;
</div>
</body>
</html>