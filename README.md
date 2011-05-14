Twitter hashtag archiver
------------------------

This scans twitter for a hashtag and stores tweets in a database for later display.

It uses:
 - Wordpress BlackBird Pie by Justin Shreve
 - jQuery Infinite Scroll by Paul Irish
 - cxPDO by David Pennington
 - RSS Ingest by Daniel Iversen

#MusMem
-------
This project contains the source of the #MusMem project at [memories.museum140.com](http://memories.museum140.com/)

It is a project by @museum140 to gather memories of museums tweeted with the hashtag #musmem. As the Twitter Search API only returns results from the last 7 days, this system was built to search automatically and save the results. 

Version history
---------------
 1.  Search scanner saves all the details of a tweet in a MySQL DB and displays them.
 2.  Saves just the tweet id returned and queries the Twitter API for up-to-date details when displaying (so that user profile pics and profile colours are up-to-date)
 3.  Added caching for display so that we don't get rate-limited while displaying.


NOTE: This might show a delay in displaying older tweets if you are expecting more than 150 mentions per hour.
      It uses the unauthenticated REST API to gather tweet details which can get rate limited although it does
			cache them so each tweet is only requested once. If you have thousands per hour, it'll eventually catch up, 
			it just might take a while.
