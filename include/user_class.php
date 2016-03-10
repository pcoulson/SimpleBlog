<?php
/*
 * Create a class to handle user functions.
 * $db connection defined in connect.php.
 */
class user {

	private $_db;

	// Variables used to define a user.
	private $_fname;
	private $_lname;
	private $_email;
	private $_password;
	private $_bloggerId;

	/*
	 * Connect to the database
	 */
	public function __construct($db) {
		$this->_db = $db;
	}

	/*
	 * Is this user logged in?
	 */
	public function loggedIn() {


	}

	/*
	 * Check if the input email address already exists in the database.
	 * This will allow an existing user to login or prevent duplicate users.
	 */
	public function checkEmail($email) {

		$email = strtolower($email);			// Make all email addresses lower case.
		$emails = 0;

		try {
			// Build the select statement to see if the email address is already being used.
			$query = "SELECT count(*) FROM user WHERE email_address = :email_address";

			$stmt = $this->_db->prepare($query);
			$stmt->bindParam(":email_address", $email, PDO::PARAM_STR);

			if ($stmt->execute()) {
				$emails = $stmt->fetchColumn();
			}

		} catch (PDOException $e) {
			trigger_error('Invalid SQL: ' . $query . ' Error: ' . $e->getMessage(), E_USER_ERROR);
		}

		return($emails);		// Return the number of rows with this email address.
	}

	/*
	 * Login the user using the email and password.
	 */
	public function userLogin($email, $password) {

		$hashpassword = $this->getHashedPassword($email);		// Get the stored hashed password for the given email address

		if (password_verify($password,$hashpassword)) {
			$_SESSION['userloggedin'] = true;
			$_SESSION['firstname'] = $this->_fname;
			$_SESSION['bloggerid'] = $this->_bloggerId;
			$_SESSION['blogIndex'] = 0;
			return true;
		} else {
			$_SESSION['userloggedin'] = false;					// password does not match the stored password.
			return false;
		}
	}

	/*
	 * Validate all the input which hill be used to create a new user.
	 */
	public function validateInput() {

		$errors = array();		// Initialise an error array

		// Remove leading spaces and escape the input strings.
		// Save the validated values to be used for the new user.
		$this->_fname = trim($_POST['fname']);
		$this->_lname = trim($_POST['lname']);
		$this->_email = trim(strtolower($_POST['email']));
		$this->_password = trim($_POST['password']);
		$confemail = trim($_POST['confemail']);
		$confpassword = trim($_POST['confpassword']);

		if ($this->_fname == '') {
			$errors['fname'] = 'First name must be supplied.';
		}

		if ($this->_lname == '') {
			$errors['lname'] = 'Last name must be supplied.';
		}

		if ($this->_email == '') {												// Check an email has been entered
			$errors['email'] = 'A valid email address must be supplied.';
		} else if (!filter_var($this->_email, FILTER_VALIDATE_EMAIL)) {			// Does it conform to the email standards
			$errors['email'] = 'This is an invalid email format.';
		} else if ($this->checkEmail($this->_email)) {							// Make sure it doesnt already exist
			$errors['email'] = 'This email address already exists.';
		}

		if ($this->_email != $confemail) {										// Compare email addresses
			$errors['confemail'] = 'Does not match Email address.';
		}

		if (strlen($this->_password) < 8) {										// Password should be at least 8 characters
			$errors['password'] = 'Password must be at least 8 characters.';
		}

		if ($this->_password != $confpassword) {								// Compare passwords
			$errors['confpassword'] = 'Invalid confirmation password.';
		}

		return $errors;			// Return with any errors encountered.
	}

	/*
	 * Create a new user using only validated input.
	 */
	public function createNewUser() {

		// Get the hash value of the password
		$hashedPassword = $this->passwordHash($this->_password);				// Has the password

		try {
			// Prepare the insert statement.
			$query = "INSERT INTO user (first_name, last_name, email_address, password) VALUES (:first_name, :last_name, :email_address, :password)";

			$stmt = $this->_db->prepare($query);

			$stmt->bindParam(":first_name",  $this->_fname, PDO::PARAM_STR);
			$stmt->bindParam(":last_name", $this->_lname, PDO::PARAM_STR);
			$stmt->bindParam(":email_address", $this->_email, PDO::PARAM_STR);
			$stmt->bindParam(":password", $hashedPassword, PDO::PARAM_STR);

			// Execute the prepared statement - insert a new user row.
			$stmt->execute();

		} catch (PDOException $e) {
			trigger_error('Invalid SQL: ' . $query . ' Error: ' . $e->getMessage(), E_USER_ERROR);
		}

		return;
	}

	/*
	 * Return a hash of the input password.
	 */
	private function passwordHash($password) {
		return password_hash($password, PASSWORD_BCRYPT);
	}

	/*
	 * Get the hash password value from the user table.
	 */
	private function getHashedPassword($email) {

		try {

			$query = "SELECT password, blogger_id, first_name FROM user WHERE email_address = :email_address";
			$stmt = $this->_db->prepare($query);

			$stmt->bindParam(":email_address", $email, PDO::PARAM_STR);

			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			$this->_fname = $row['first_name'];
			$this->_bloggerId = $row['blogger_id'];

		} catch (PDOException $e) {
			trigger_error('Invalid SQL: ' . $query . ' Error: ' . $e->getMessage(), E_USER_ERROR);
		}

		return $row['password'];
	 }
}
?>