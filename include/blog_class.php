<?php
/*
 * Controls database access for the blogs.
 * $db connection defined in connect.php.
 */
class blog {
	private $_db;

	private $_userId;
	private $_blogTitle;
	private $_blogText;

	/*
	 * Connect to the database
	 */
	public function __construct($db) {
		$this->_db = $db;
	}

	/*
	 *	Get the 5 most recent blogs along with the name of the user who created each one.
	 */
	public function fiveBlogs() {

		$query = "SELECT
					b.blog_id,
					b.blog_title,
					b.blog_date,
					b.blog_last_update,
					b.blog_text,
					u.first_name,
					u.last_name
				  FROM
				  	blogs b
				  LEFT OUTER JOIN
				  	user u
				  ON
				  	u.blogger_id = b.blogger_id
				  WHERE
				  	b.blog_deleted_date IS NULL
				  ORDER BY
				  	b.blog_last_update DESC LIMIT 5";

		$rows = $this->_db->query($query);
		if ($rows === false) {
			trigger_error('Wrong SQL: ' . $query . ' Error: ' . $this->_db->error, E_USER_ERROR);
		}

		return ($rows->fetchAll());
	}

	/*
	 * Find out if the current user has any blogs associated with it.
	 */
	public function userHasBlog($userid) {

		try {
			$query = "SELECT count(*) FROM blogs WHERE blogger_id = :blogger_id AND blog_deleted_date IS NULL";

			$stmt = $this->_db->prepare($query);
			$stmt->bindParam(":blogger_id",  $userId, PDO::PARAM_INT);

			if ($stmt->execute()) {
				$num_of_rows = $stmt->fetchColumn();
			}

		} catch (PDOException $e) {
			trigger_error('Invalid SQL: ' . $query . ' Error: ' . $e->getMessage(), E_USER_ERROR);
		}

		return $num_of_rows;
	}

	/*
	 * Get all the blogs associated with this user
	 */
	public function userBlogs($userId) {

		$this->_userId = $userId;						// Set up the user id (blogger id) for future admin blog queries

		try {
			$query = "SELECT blog_id, blog_title, blog_date, blog_text, blog_last_update FROM blogs WHERE blogger_id = :blogger_id AND blog_deleted_date IS NULL ORDER BY blog_last_update DESC";

			$stmt = $this->_db->prepare($query);

			$stmt->bindParam(":blogger_id", $this->_userId, PDO::PARAM_INT);
			$stmt->execute();							// Execute statement

			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);	// Fetch all rows into an associative array

		} catch (PDOException $e) {
			trigger_error('Invalid SQL: ' . $query . ' Error: ' . $e->getMessage(), E_USER_ERROR);
		}

		return $rows;									// Return the blogs for processing.
	}

	/*
	 * Validate the user input for changing or creating a blog. Both title and text are
	 * mandatory. Make sure the title of a new blog does not already exist for this user.
	 */
	public function validateBlogs() {

		$errors = array();								// Initialise an error array

		$this->_blogTitle = trim($_POST['title']);
		$this->_blogText = trim($_POST['blogtext']);

		if ($this->_blogTitle == '') {					// If the title is blank
			$errors['title'] = 'Title is mandatory';	// Report error
		}
		if ($this->_blogText == '') {					// If the blog text is blank
			$errors['blogtext'] = 'Text is mandatory';	// Report error
		}

		if ($_POST['send'] == 'create') {				// Only do this check if we are creating a new blog
			try {
				// Check to see if an active blog already exists for this user with this title.
				$query = "SELECT count(*) FROM blogs WHERE blogger_id = :blogger_id AND blog_title = :blog_title AND blog_deleted_date IS NULL";

				$stmt = $this->_db->prepare($query);
				$stmt->bindParam(":blogger_id",  $this->_userId, PDO::PARAM_INT);
				$stmt->bindParam(":blog_title", $this->_blogTitle, PDO::PARAM_STR);

				if ($stmt->execute()) {
					$num_of_rows = $stmt->fetchColumn();
				}

				if ($num_of_rows > 0) {						// If the user already has a blog with this title
					$errors['title'] = 'Blog already exists with this title';	// Report error

				}

			} catch (PDOException $e) {
				trigger_error('Invalid SQL: ' . $query . ' Error: ' . $e->getMessage(), E_USER_ERROR);
			}
		}

		return $errors;									// Return with any errors encountered.
	}

	/*
	 * Make sure that either the title or text of the blogs has changed before making the change.
	 */
	public function checkForChanges($blogid) {

		$errors = array();								// Initialise an error array

		$this->_blogTitle = trim($_POST['title']);
		$this->_blogText = trim($_POST['blogtext']);

		try {
			$query = "SELECT blog_title, blog_text FROM blogs WHERE blog_id = :blog_id";

			$stmt = $this->_db->prepare($query);
			$stmt->bindParam(":blog_id", $blogid, PDO::PARAM_INT);

			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

		} catch (PDOException $e) {
			trigger_error('Invalid SQL: ' . $query . ' Error: ' . $e->getMessage(), E_USER_ERROR);
		}

		if ($this->_blogTitle == $row['blog_title'] && $this->_blogText == $row['blog_text']) {
			$errors['blogtext'] = 'Not Updated - nothing has changed!';
		}

		return $errors;									// Return with any errors encountered.
	}

	/*
	 * This is where the form action is performed.
	 */
	public function actionChanges($action, $blog_id) {

		$errors = '';											// Initialise the errors array

		switch ($action) {

			case 'update':										// UPDATE
				$errors = $this->validateBlogs();				// 		Validate the input

				if (!$errors) {									// 		If no errors
					$errors = $this->checkForChanges($blog_id);	// 		Make sure something has been changed
				}
				if (!$errors) {									// 		If no errors
					$this->modifyBlog($blog_id);				// 		Update the blog
					$_SESSION['blogIndex'] = 0;					//		Set the session blog index to the first blog in the list
				}

				break;

			case 'delete':										// DELETE
				$this->deleteBlog($blog_id);					// 		Delete the blog - Set deleted date
				$_SESSION['blogIndex'] = 0;						//		Set the session blog index to the first blog in the list

				break;

			case 'create':										// CREATE
				$errors = $this->validateBlogs();				// 		Validate the input

				if (!$errors) {									// 		If no errors
					$this->createBlog();						// 		Create a new blog record
					$blogindex = 0;								//  	point to the most recent blog added
				}
				$_SESSION['blogIndex'] = 0;						// 		Set the session blog index to the first blog in the list.

				break;
		}
		return $errors;
	}

	/*
	 * Update the blog with the new title, text and last update date.
	 */
	private function modifyBlog($blogid) {

		$currDate = new DateTime();

		try {
			$query = "UPDATE blogs SET blog_title = :blog_title, blog_text = :blog_text, blog_last_update = :blog_last_update WHERE blog_id = :blog_id";
			$stmt = $this->_db->prepare($query);

			$stmt->bindParam(":blog_title", $this->_blogTitle, PDO::PARAM_STR);
			$stmt->bindParam(":blog_text", $this->_blogText, PDO::PARAM_STR);
			$stmt->bindParam(":blog_last_update", $currDate->format("Y-m-d H:i:s"), PDO::PARAM_STR);
			$stmt->bindParam(":blog_id", $blogid, PDO::PARAM_INT);

			$stmt->execute();

		} catch (PDOException $e) {
			trigger_error('Invalid SQL: ' . $query . ' Error: ' . $e->getMessage(), E_USER_ERROR);
		}

		return;
	}

	/*
	 * If the blog information has been validated successfully create a new row in the table - the blog created date and
	 * last update date are initially the same.
	 */
	private function createBlog() {

		$currDate = new DateTime();

		try {
			$query = "INSERT INTO blogs (blogger_id, blog_title, blog_date, blog_last_update, blog_text) VALUES (:blogger_id, :blog_title, :blog_date, :blog_last_update, :blog_text)";
			$stmt = $this->_db->prepare($query);

			$stmt->bindParam(":blogger_id", $this->_userId, PDO::PARAM_INT);
 			$stmt->bindParam(":blog_title", $this->_blogTitle, PDO::PARAM_STR);
 			$stmt->bindParam(":blog_date", $currDate->format("Y-m-d H:i:s"), PDO::PARAM_STR);
			$stmt->bindParam(":blog_last_update", $currDate->format("Y-m-d H:i:s"), PDO::PARAM_STR);
			$stmt->bindParam(":blog_text", $this->_blogText, PDO::PARAM_STR);
			$stmt->execute();

		} catch (PDOException $e) {
			trigger_error('Invalid SQL: ' . $query . ' Error: ' . $e->getMessage(), E_USER_ERROR);
		}

		return;
	}

	/*
	 * Delete the blog by setting the blog_deleted_date to the current date. This
	 * cannot be undone from within this program.
	 */
	private function deleteBlog($blogid) {

		$currDate = new DateTime();

		try {
			$query = "UPDATE blogs SET blog_deleted_date = :blog_deleted_date WHERE blog_id = :blog_id";
			$stmt = $this->_db->prepare($query);

			$stmt->bindParam(":blog_deleted_date", $currDate->format("Y-m-d H:i:s"), PDO::PARAM_STR);
			$stmt->bindParam(":blog_id", $blogid, PDO::PARAM_INT);

			$stmt->execute();

		} catch (PDOException $e) {
			trigger_error('Invalid SQL: ' . $query . ' Error: ' . $e->getMessage(), E_USER_ERROR);
		}

		return;
	}
}
?>