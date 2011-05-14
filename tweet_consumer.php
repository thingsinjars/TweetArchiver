<?php
/*
Tweet consumer based on
RSS Ingest v 0.1 by Daniel Iversen, daniel (a dot) iversen (the at sign) gmail (another dot) com

It has been simplified to only store the tweet unique ID and use twitter intents now instead of storing the whole tweet and related info.

*/
include 'config.php';

// Check a few bits and pieces

if(isset($_GET['feed_url']))
{
  $feed_url = $_GET['feed_url'];
}
else
{
  die("Need to pass the (consistent) 'feed url'");
}


if(isset($_GET['access_key']))
{
  if($_GET['access_key']==$private_access_key)
  {
    echo "Access key correct, proceeding...<br/><br/>";
  }
  else
  {
    die("wrong access key");
  }
}
else
{
  die("Need to pass the 'access_key' URL parameter");
}


try
{
  /*  query the database */
  // $db = getCon();

  $db = mysql_connect($db_hostname,$db_username,$db_password);
  if (!$db)
  {
    die("Could not connect: " . mysql_error());
  }
  mysql_select_db($db_db, $db);

  echo "Starting to work with feed URL '" . $feed_url . "'";

  /* Parse XML from  http://www.instapaper.com/starred/rss/580483/qU7TKdkHYNmcjNJQSMH1QODLc */
  //$RSS_DOC = simpleXML_load_file('http://www.instapaper.com/starred/rss/580483/qU7TKdkHYNmcjNJQSMH1QODLc');

  libxml_use_internal_errors(true);
  $RSS_DOC = simpleXML_load_file($feed_url);
  if (!$RSS_DOC) {
    echo "Failed loading XML\n";
    foreach(libxml_get_errors() as $error) {
      echo "\t", $error->message;
    }
  }

  /* Get title, link, managing editor, and copyright from the document  */
  $rss_title = $RSS_DOC->channel->title;
  $rss_link = $RSS_DOC->channel->link;
  $rss_editor = $RSS_DOC->channel->managingEditor;
  $rss_copyright = $RSS_DOC->channel->copyright;
  $rss_date = $RSS_DOC->channel->pubDate;

  //Loop through each item in the RSS document

  foreach($RSS_DOC->channel->item as $RSSitem)
  {
    $item_id  = md5($RSSitem->title);
    $fetch_date = date("Y-m-j G:i:s"); //NOTE: we don't use a DB SQL function so its database independant
//    $item_title = mysql_real_escape_string($RSSitem->title);
    $item_title = mysql_real_escape_string(substr($RSSitem->guid, strrpos($RSSitem->guid, '/')+1));
    $item_content = mysql_real_escape_string($RSSitem->description);
    $item_date  = date("Y-m-j G:i:s", strtotime($RSSitem->pubDate));
    $item_url = $RSSitem->link;
    $matches = array();
    preg_match('/^(.*)@(.*) \((.*)\)$/', $RSSitem->author, $matches);
    $item_author = $matches[1];
    $item_author_name = $matches[3];

    // echo "Processing item '" , $item_id , "' on " , $fetch_date  , "<br/>";
    // echo $item_title, " - ";
    // echo $item_date, "<br/>";
    // echo $item_url, "<br/>";

    // Does record already exist? Only insert if new item...

    $item_exists_sql = "SELECT item_id FROM ".$db_table." where item_id = '" . $item_id . "'";
    $item_exists = mysql_query($item_exists_sql, $db);
    if(mysql_num_rows($item_exists)<1) {
      echo "<font color=green>Inserting new item..</font><br/>";
      $item_insert_sql = "INSERT INTO ".$db_table."(item_id, hash_tag, item_title, item_date) VALUES ('" . $item_id . "', '" . $hashtag . "', '" . $item_title . "', '" . $item_date . "')";
      $insert_item = mysql_query($item_insert_sql, $db);
    }
    else
    {
      echo "<font color=blue>Not inserting existing item..</font><br/>";
    }

    echo "<br/>";
  }

  // End of form //
} catch (Exception $e)
{
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
?>