<?php
/*
Twitter hashtag archiver by Simon Madine (@thingsinjars)

This scans twitter for a hashtag and stores tweets in a database for later display

It uses:
Wordpress BlackBird Pie by Justin Shreve
jQuery Infinite Scroll by Paul Irish
cxPDO by David Pennington
RSS Ingest by Daniel Iversen

NOTE: This might show a delay in displaying older tweets if you are expecting more than 150 mentions per hour.
      It uses the unauthenticated REST API to gather tweet details which can get rate limited although it does
			cache them so each tweet is only requested once. If you have thousands per hour, it'll eventually catch up, 
			it just might take a while.


INSTALLATION AND USAGE:
Create your database table using this (choose your own table name if you like):
CREATE TABLE SQL:
- - - - - - - - - - - - - - -
  CREATE TABLE  `table_name` (
   `item_id` VARCHAR( 32 ) NOT NULL ,
   `hash_tag` VARCHAR( 32 ) NOT NULL ,
   `item_title` VARCHAR( 255 ) NOT NULL,
   `item_content` VARCHAR( 4000 ) NULL ,
   `item_date` TIMESTAMP NOT NULL
  ) ENGINE = MYISAM ;


Fill in your DB connection details here:*/
$db_hostname="mysql.yourhost.com";
$db_username="dbusername";
$db_password="dbpassword";
$db_db="dbname";
$db_table="table_name";

/* What is the hashtag you are using? */
$hashtag = 'toast';

/* Make up a random word here */
$private_access_key="sssshhhh";

/* Do you want to use infinite scroll or pagination links? */
$infinite_scroll = true;

/* Set the number of tweets you want to appear per page*/
$limit = 10;

/* Set your server to run this shell script every 10 minutes
wget -q --spider http://<yourserver>/<path to this directory>/tweet_consumer.php?access_key=YOUR_PRIVATE_ACCESS_KEY\&feed_url=http://search.twitter.com/search.rss%3Fq=%2523YOUR_HASH_TAG

(note the escaped \& that may or may not be necessary depending on how you call this)


Pay no attention to the gubbins below, I was in a rush.
/* Database connection details*/
$config = array();
$config['user'] = $db_username;
$config['pass'] = $db_password;
$config['name'] = $db_db;
$config['host'] = $db_hostname;
$config['type'] = 'mysql';
$config['port'] = 3306;
$config['persistent'] = true;
include 'cxpdo.php';
$db = db::instance($config);



include 'twitter.php';


$offset = (isset($_GET['page'])?($_GET['page']-1)*$limit:(isset($_GET['offset'])?$_GET['offset']:'0'));
