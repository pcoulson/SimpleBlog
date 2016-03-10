<?php
// http://localhost/Frank/Frank/index.php

require_once ('include/connect.php');		// Include the SQL for the blog
require_once ('include/blog_class.php');	// Include the SQL for the blog

if (isset($_SESSION['userloggedin'])) {
	$_SESSION['userloggedin'] = false;		// Assume user is not logged in
}

$blogs = new blog($db);						// Create a new instance of the blog class

$latestFiveBlogs = $blogs->fiveBlogs();		// Get the latest 5 blogs from the databse

$blog_id = 0;								// Set default bog index to 0
if (isset($_GET['bId'])) {					// If an index has been sent to the page
	$blog_id = $_GET['bId'];				// Get the blog id from the table
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
		<h1>Simple Blog</h1>
	</header>
	<nav>
		<ul>
			<li><a href="login.php">Login</a></li>
		</ul>
	</nav>

	<div id="content">
		<div class="blogtext">
			<?php
			if (isset($latestFiveBlogs[$blog_id]["blog_title"])) {									// If blogs have been created get the 5 most recent
				echo "<strong>" . $latestFiveBlogs[$blog_id]["blog_title"] . "</strong><br />";
				echo "<p>" . $latestFiveBlogs[$blog_id]["blog_text"] . "</p>";
				$dateTime = new DateTime($latestFiveBlogs[$blog_id]["blog_date"]);
				echo "<p>Posted Date: "  . $dateTime->format("d-M-Y H:i:s") . "</p>";
				echo "<p>Posted by : " . $latestFiveBlogs[$blog_id]["first_name"] . " " . $latestFiveBlogs[$blog_id]["last_name"] . "</p>";
			} else {																				//if no blogs have yet been created.
				echo "<strong>No blogs have yet been created</strong><br />";
				echo "<p>Create a user (if one doesn't already exist) by clicking the login button above and use this user to create blogs. Good luck.</p>";
			}
			?>
		</div>
	</div>

	<div class="sidebar">
		<?php
		$i = 0;
		foreach ($latestFiveBlogs as $row) {			// Display the 5 most recent blogs
			echo "<p><strong>" . $row["blog_title"] . "</strong><br />" . substr($row["blog_text"], 0, 100) . '... <a href="index.php?bId=' . $i . '">View</a><p>';
			$i++;
		}
		?>
	</div>
	<footer>
	&nbsp;
	</footer>
</div>
</body>
</html>