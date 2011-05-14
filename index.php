<?php
/*
Twitter hashtag archiver
This scans twitter for a hashtag and stores tweets in a database for later display
More details in config.php
*/
include 'config.php';
?><!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <!--[if IE]><![endif]-->
    <title>Museum Memories #MusMem &ndash; Museum140</title>
    <link href='http://fonts.googleapis.com/css?family=Lobster' rel='stylesheet' type='text/css'>
    <link href='main.css' rel='stylesheet' type='text/css'>
    <meta name="keywords" content="Museum Memories, museum memories day, international museums day, museum, museums, memory, twitter, tweet, story, stories, musmem, museum140">
    <meta name="description" content="A collection of museum memories in tweet-form.">
    <script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js" type="text/javascript"></script>
    <![endif]-->
  </head>
  <body>
    <section id="introduction">
      <article>
        <header>
          <h1>Museum Memories</h1>
        </header>
        <p class="intro">Tweet your museum memories with the hashtag #MusMem to add them to the MemoryBank!</p>
        <p>The theme for this year's <a href="http://network.icom.museum/imd2011.html" title="IMD 2011">International Museum Day</a> is &ldquo;Museum and Memory &rdquo;. In the run up to this, Museum Memories on <time datetime="2011-05-17">Tuesday 17 May</time> invites you to share your memorable museum moments.</p>
        <p>Your earliest museum visit? The first time you saw an Egyptian mummy up close? Your first museum job? Maybe you got engaged or even married in a museum? </p>
        <p>Whether you work in a museum or are an enthusiastic visitor, tweet your museum memories with the hashtag <a href="http://twitter.com/#search?q=%23MusMem">#MusMem</a>, and check out the MemoryBank for other people&rsquo;s stories.</p>
      <footer><p>Brought to you by <a href="http://www.museum140.com/" title="Museum140">Museum140</a>.</p></footer>
      </article>
      <section id="content">
        <header>
          <h1><a href="http://twitter.com/#search?q=%23MusMem">#MusMem</a> MemoryBank</h1>
        </header>
        <?php writeOutTweets($db, $db_table, $hashtag, $limit, $offset); ?>
        <?php writeOutPaging($db, $db_table, $limit, $offset); ?>
      </section>
      <footer>
      </footer>
            <p id="ribbon"><a href="http://twitter.com/museum140"><img src="ribbon.png" alt="follow @museum140 on Twitter.com"></a></p>
    </section>
    <?php if($infinite_scroll) { writeOutInfiniteScroll();} ?>
  </body>
</html>