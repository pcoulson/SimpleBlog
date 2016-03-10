<?php
/*
 * Start a session and make a connection to the database.
 */
session_start();

require_once ('include/globals.php');

$db = new PDO(DB_DRIVER . ':host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

date_default_timezone_set('Europe/London');		//set timezone
?>