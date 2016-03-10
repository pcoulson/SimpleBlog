/* DROP DATABASE sales;*/
DROP DATABASE IF EXISTS simpleblog;

CREATE DATABASE simpleblog;

USE simpleblog;

CREATE TABLE blogs (
	blog_id			MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
	blogger_id		MEDIUMINT UNSIGNED NOT NULL,
	blog_title		VARCHAR(40) NOT NULL,
	blog_date		DATETIME NOT NULL,
	blog_last_update	DATETIME NOT NULL,
	blog_deleted_date	DATETIME NULL DEFAULT NULL,
	blog_text		LONGTEXT,
	PRIMARY KEY 		(blog_id));

CREATE TABLE user (
	blogger_id		MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
	first_name		VARCHAR(40) NOT NULL,
	last_name		VARCHAR(40) NOT NULL,
	email_address		VARCHAR(40) NOT NULL,
	password		VARCHAR(255) NOT NULL,
	PRIMARY KEY 		(blogger_id));

