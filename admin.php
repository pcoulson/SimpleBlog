<?php
require_once ('include/connect.php');							// Connect to the database, Starts the session
require_once ('include/blog_class.php');						// Include the blog class

if (isset($_SESSION['userloggedin']) && isset($_SESSION['firstname']) && isset($_SESSION['bloggerid']) && is_numeric($_SESSION['bloggerid'])) {
	$userid = $_SESSION['bloggerid'];							// User id
	$firstname = $_SESSION['firstname'];						// Users first name
	$blogindex = $_SESSION['blogIndex'];						// Current index in the list of blogs returned from the database
} else {
	header("location: login.php");								// User is not logged in so go to the login page
}

$blogs = new blog($db);											// Create a new instance of the blog class
$userBlogs = $blogs->userBlogs($userid);						// Get the blogs associated with the current logged in user

count($userBlogs) ? $createBlog = FALSE : $createBlog = TRUE;	// If there are no blogs then you can only create one.

if (!$createBlog) {												// If we are not creating a blog
	if (isset($_GET['create'])) {								// If the user clicked on the "here" link
		$createBlog = TRUE;										// Set flag to display page to create a new blog
	} else {													// User clicked on the blog "Edit" link
		if (isset($_GET['blogIndex'])) {
			$blogindex = $_SESSION['blogIndex'] = $_GET['blogIndex'];	// Set local and session blog index
		}
		$blog_id = $userBlogs[$blogindex]['blog_id'];			// Get the blog_id from the list of blogs.
	}
}

if (isset($_POST['send'])) {									// If we are actioning a form post request
	$createBlog = FALSE;										// Default is we are not creating a new blog
	if ($_POST['send'] != 'cancel') {							// If the 'cancel' button was not clicked continue processing
		if ($_POST['send'] == 'create') {						// Is this a create request
			$createBlog = TRUE;									// Set create new blog flag
		}

		$errors = $blogs->actionChanges($_POST['send'], $blog_id);	// This is where all the changes are made.

		if (!$errors) {											// If no errors
			header("location: admin.php");						// Redisplay the page - clearing the GET and POST arrays
		}
		unset($_POST);											// Initialise the POST array (to clear fields)
		$userBlogs = $blogs->userBlogs($userid);				// Fill the list with the new changes.
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
		<h1>Simple Blog Admin</h1>
	</header>
	<nav>
		<ul>
			<li><a href="login.php">Logout</a></li>
		</ul>
	</nav>

	<div id="content">

		<div class="blogtext">
			<form id="blogForm" method="post" action="admin.php">
				<p>Hello <span><?php echo $firstname; ?></span></p>
				<p>
				<label class="updatelabel" for="title" autocomplete="off">Title:</label>
				<br><input name="title" id="title" value="<?php echo $createBlog ? '' : $userBlogs[$blogindex]['blog_title']; ?>" />
				<?php
					if (isset($errors['title'])) {
						echo "<br><span class='berror'>" . $errors['title'] . "</span>";
					}
				?>
				</p>
				<p>
				<label class="updatelabel" for="blogtext" autocomplete="off">Blog Text:</label>
				<br><textarea rows="10" cols="50" form="blogForm" name="blogtext"><?php echo  $createBlog ? '' : $userBlogs[$blogindex]['blog_text']; ?></textarea>
				<?php
					if (isset($errors['blogtext'])) {
						echo "<br><span class='berror'>" . $errors['blogtext'] . "</span>";
					}
				?>
				</p>
				<?php
					$createBlog ? $dateTime = new DateTime() : $dateTime = new DateTime($userBlogs[$blogindex]["blog_date"]);
					echo '<p><span class="updatelabel">Posted Date:</span> '  . $dateTime->format("d-M-Y H:i:s");
					if (!$createBlog) {
						$last_update = new DateTime($userBlogs[$blogindex]["blog_last_update"]);
						echo '<br><span class="updatelabel">Last Update:</span> '  . $last_update->format("d-M-Y H:i:s") . '</p>';
						echo '<button type="submit" id="updateButton" name="send" value="update">Update</button>';
						echo '<button type="submit" id="cancelButton" name="send" value="cancel">Cancel</button>';
						echo '<button type="submit" id="deleteButton" name="send" value="delete">Delete</button>';
					} else {
						echo '</p>';
						echo '<button type="submit" id="updateButton" name="send" value="create">Create</button>';
						echo '<button type="submit" id="cancelButton" name="send" value="cancel">Cancel</button>';
					}
				?>
			</form>
		</div>
	</div>

	<div class="sidebar">
		Select blog Edit or click <a href="admin.php?blogIndex=0&create=create">here</a> to create a new blog.
		<!--
		Display all theblogs created by this user in the sidebar
		-->
		<div class="scrollsidebar">
			<?php
			$i = 0;
			foreach ($userBlogs as $row) {
				echo "<p><strong>" . $row["blog_title"] . "</strong><br />" . substr($row["blog_text"], 0, 50) . '... <a href="admin.php?blogIndex=' . $i . '">Edit</a><p>';
				$i++;
			}
			?>
		</div>
	</div>
	<footer>
	&nbsp;
	</footer>
</div>
<script src="js/blog.js"></script>			<!-- Get confirmation of delete request -->
</body>
</html>