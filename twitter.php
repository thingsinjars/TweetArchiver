<?php
/* Twitter stuff 
Most of the display bits are from Wordpress BlackBird Pie by Justin Shreve
*/

function get_tweet_details($id) {
  $request_url = "http://api.twitter.com/1/statuses/show.json?id={$id}";
  $result = getViaCurl($request_url);
  $content = json_decode($result);
  if(!isset($content->error)) {
	echo '*';
    cache_tweet($id, $result);
  } else {
  }
  return $content;
}

function create_tweet_html( $tweet_details, $options = array()) {
    global $post;

    /* PROFILE DATA */
    $name = $tweet_details['screen_name'];                      //the twitter username
    $real_name = $tweet_details['real_name'];                   //the user's real name
    $profile_pic = esc_url($tweet_details['profile_pic']);      //url to the profile image
    if ( !$tweet_details['profile_bg_tile'] )
        $profile_bg_tile_HTML = " background-repeat:no-repeat"; //profile background tile
    $profile_link_color = $tweet_details['profile_link_color']; //link color
    $profile_text_color = $tweet_details['profile_text_color']; //text color
    $profile_bg_color = $tweet_details['profile_bg_color'];     //background color
    $profile_bg_image = esc_url($tweet_details['profile_bg_image']);     //background image
    $profile_url = esc_url("http://twitter.com/intent/user?screen_name={$name}"); //the URL to the twitter profile

    /* GENERAL INFO */
    $id = $tweet_details['id'];                                     //id of the actual tweet
    $url = esc_url( "http://twitter.com/#!/{$name}/status/{$id}" ); //the URL to the tweet on twitter.com

    /* TIME INFO */
    $time = $tweet_details['time_stamp'];                       //the time of the tweet
    $date = date( "h:i jS F Y", $time  );     //the local time based on the GMT offset
    $time_ago = how_long_ago( $time );                   //the friendly version of the time e.g. "1 minute ago"

    /* SOURCE of the tweet */
    $source = $tweet_details['source'];
    preg_match( '`<a href="(http(s|)://[\w#!$&+,\/:;=?@.-]+)[^\w#!$&+,\/:;=?@.-]*?" rel="nofollow">(.*?)</a>`i', $source, $matches );
    if( ! empty( $matches[1] ) || ! empty( $matches[3]) )
        $source = '<a href="' . esc_url( $matches[1] ). '" rel="nofollow" target="blank">' . esc_html( $matches[3] ) . '</a>';
    else
        $source = esc_html( $source );

    //the plugin's base URL
    $base_url = 'http://memories.museum140.com';

    // Tweet Action Urls
    $retweet_url = esc_url( "https://twitter.com/intent/retweet?tweet_id={$id}" );
    $reply_url = esc_url( "https://twitter.com/intent/tweet?in_reply_to={$id}" );
    $favorite_url = esc_url( "https://twitter.com/intent/favorite?tweet_id={$id}" );

    $tweet = $tweet_details['tweet_text'];

/*
Originally, this line below included profile text colour but it doesn't work if people choose white.
      <div class='tweet_container' style='color:#{$profile_text_color};'>
*/

    $tweetHTML = "<!-- tweet id : $id -->
    <style type='text/css'>
        #bbpBox_$id a { text-decoration:none; color:#{$profile_link_color}; }
        #bbpBox_$id a:hover { text-decoration:underline; }
    </style>
    <div id='bbpBox_$id' class='bbpBox'>
        <div class='tweet_container'>
            <span class='tweet_text'>
                {$tweet}
            </span>
            <div class='tweet_actions'>
                <img align='middle' src='{$base_url}/bird.png' />
                <a title='tweeted on {$date}' href='{$url}' target='_blank'>{$time_ago}</a> via {$source}
                <a href='https://twitter.com/intent/tweet?in_reply_to={$id}' class='bbp-action bbp-reply-action' title='Reply'>
                    <span><em style='margin-left: 1em;'></em><strong>Reply</strong></span>
                </a>
                <a href='https://twitter.com/intent/retweet?tweet_id={$id}' class='bbp-action bbp-retweet-action' title='Retweet'>
                    <span><em style='margin-left: 1em;'></em><strong>Retweet</strong></span>
                </a>
                <a href='https://twitter.com/intent/favorite?tweet_id={$id}' class='bbp-action bbp-favorite-action' title='Favorite'>
                    <span><em style='margin-left: 1em;'></em><strong>Favorite</strong></span>
                </a>
            </div>
            <div class='tweet_profile_pic'>
                <a href='{$profile_url}'>
                    <img src='{$profile_pic}' />
                </a>
            </div>
            <cite class='tweet_name'>
                <a href='{$profile_url}'>@{$name}</a>
                <div>{$real_name}</div>
            </cite>
            <div style='clear:both'></div>
        </div>
    </div>
    <!-- end of tweet -->";

    //remove any extra spacing and line breaks
    $tweetHTML = str_replace( "\r\n", '', $tweetHTML );
    $tweetHTML = str_replace( "\n", '', $tweetHTML );
    $tweetHTML = str_replace( "\t", '', $tweetHTML );

    return $tweetHTML;
}
function processTweet($id) {
  //This has caching built in so you don't go over your 150 requests per hour rate limit
  $data = get_cached_tweet($id);
  if($data=='') {
    $data = get_tweet_details($id);
  }
  if(!$data->text=='') {
  //fix for non english tweets
  $data->text = addslashes(UTF8entities($data->text));
  $data->user->screen_name = addslashes(UTF8entities($data->user->screen_name));
  $data->user->name = addslashes(UTF8entities($data->user->name));
  $timeStamp = strtotime($data->created_at);

  $args = array(
      'id' => $id,
      'screen_name' => stripslashes($data->user->screen_name),
      'real_name' => stripslashes($data->user->name),
      'tweet_text' => stripslashes($data->text),
      'source' => $data->source,

      'profile_pic' => $data->user->profile_image_url,
      'profile_bg_color' => $data->user->profile_background_color,
      'profile_bg_tile' => $data->user->profile_background_tile,
      'profile_bg_image' => $data->user->profile_background_image_url,
      'profile_text_color' => $data->user->profile_text_color,
      'profile_link_color' => $data->user->profile_link_color,

      'time_stamp' => $timeStamp,
      'utc_offset' => $data->user->utc_offset
  );
  return create_tweet_html($args);
  }
}


/* String utilities */
  function unicode_string_to_array( $string ) { //adjwilli
    if ( function_exists('mb_strlen') )
      $strlen = mb_strlen($string);
    else
      $strlen = strlen($string);
    while ($strlen) {
      $array[] = mb_substr( $string, 0, 1, "UTF-8" );
      $string = mb_substr( $string, 1, $strlen, "UTF-8" );
      if ( function_exists('mb_strlen') )
        $strlen = mb_strlen( $string );
      else
        $strlen = strlen( $string );
    }
    return $array;
  }

  function unicode_entity_replace($c) { //m. perez
    $h = ord($c{0});
    if ($h <= 0x7F) {
      return $c;
    } else if ($h < 0xC2) {
      return $c;
    }

    if ($h <= 0xDF) {
      $h = ($h & 0x1F) << 6 | (ord($c{1}) & 0x3F);
      $h = "&#" . $h . ";";
      return $h;
    } else if ($h <= 0xEF) {
      $h = ($h & 0x0F) << 12 | (ord($c{1}) & 0x3F) << 6 | (ord($c{2}) & 0x3F);
      $h = "&#" . $h . ";";
      return $h;
    } else if ($h <= 0xF4) {
      $h = ($h & 0x0F) << 18 | (ord($c{1}) & 0x3F) << 12 | (ord($c{2}) & 0x3F) << 6 | (ord($c{3}) & 0x3F);
      $h = "&#" . $h . ";";
      return $h;
    }
  }
  function UTF8entities($content) {
    $contents = unicode_string_to_array($content);
    $swap = "";
    $iCount = count($contents);
    for ($o=0;$o<$iCount;$o++) {
      $contents[$o] = unicode_entity_replace($contents[$o]);
      $swap .= $contents[$o];
    }
    if ( function_exists('mb_convert_encoding') )
      return mb_convert_encoding( $swap, "UTF-8" ); //not really necessary, but why not.
    else
      return utf8_encode( $swap );
  }
  function esc_url($str) {
    return $str;
  }
  function esc_html($str) {
    return $str;
  }

/* Other utilities */
    function how_long_ago( $date ) {
        $current = time();
        $difference = $current - $date;

        if ( strtotime( '-1 min', $current ) < $date)
            $output = 'less than a minute ago';
        elseif ( strtotime( '-1 hour', $current ) < $date )
            $output = ( floor($difference / 60 ) == 1 ) ? '1 minute ago' : floor( $difference / 60 ) . ' minutes ago';
        elseif ( strtotime( '-1 day', $current ) < $date )
            $output = ( floor( $difference / 60 / 60 ) == 1 ) ? 'about 1 hour ago' : 'about ' . floor( $difference / 60 / 60 ) . ' hours ago';
        else
            $output = date( "h:i jS F Y", ( $date  ) );

        return $output;
    }

function getViaCurl($file) {
  $ch = curl_init($file);
  curl_setopt($ch, CURLOPT_TIMEOUT, 50);
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 $output = curl_exec($ch);
  curl_close($ch);
  return $output;
}

function writeOutTweets($db, $db_table, $hash_tag, $limit, $offset) {
?>
          <ul class="tweets"><?php
          $data               = array();
          $data['tables']     = $db_table;
          $data['columns']    = array($db_table.'.item_title');
          $data['conditions'] = array('hash_tag' => $hash_tag);
          $data['order']      = 'item_date DESC';
          $data['limit']      = $limit;
          $data['offset']     = $offset;
          $data['columns']    = array($db_table.'.item_title');

          $result = $db->select($data);

          while($tweet = $result->fetch(PDO::FETCH_ASSOC)) {
            echo '<li>';
            echo processTweet($tweet['item_title']);
            echo '</li>';
          }
?>
        </ul>
<?php
}

function writeOutPaging($db, $db_table, $limit, $offset) {
$result = $db->count($db_table);
$item_count = $result->fetchColumn();

if($offset > 0 || $item_count > $offset * $limit) {
  echo '<ul class="paging">';
  if($offset > 0) {
    echo '<li><a href="index.php?offset=' . ($offset - $limit) . '">Newer</a>';
  }
  if($item_count > $offset * $limit) {
    echo '<li><a href="index.php?page=' . (($offset + $limit)/$limit + 1). '" class="older">Older</a>';
  }
  echo '</ul>';
}


}
function writeOutInfiniteScroll() {
?>    <script src="http://code.jquery.com/jquery-1.5.min.js"></script>
    <script src="https://github.com/paulirish/infinite-scroll/raw/master/jquery.infinitescroll.min.js"></script>
    <script>
    $('.tweets').infinitescroll({

    navSelector  : ".paging",          // selector for the paged navigation (it will be hidden)
    nextSelector : ".paging a.older",   // selector for the NEXT link (to page 2)
    itemSelector : ".tweets li",              // selector for all items you'll retrieve
    loadingImg   : 'ajax-loader.gif',
    loadingText  : '<em>Loading more memories...</em>'
  });
      </script>
<?php
}
function get_cached_tweet($id) {
  global $db, $db_table;

  $data               = array();
  $data['tables']     = $db_table;
  $data['columns']    = array($db_table.'.item_content');
  $data['conditions'] = array('item_title' => $id);


  $result = $db->select($data);
  $tweet = $result->fetch(PDO::FETCH_ASSOC);
  return json_decode($tweet['item_content']);
}

function cache_tweet($id, $content) {
  global $db, $db_table;
  $db->update(array($db_table), array('item_content'=>$content), array('item_title' => $id));
}