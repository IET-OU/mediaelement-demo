<?php
/**
 * Modify and output player.xml RSS feeds.
 *
 * @copyright Nick Freear, 26 February 2015.
 */

ini_set( 'display_errors', 1 );
error_reporting( E_ALL );

define( 'REGEX_URL', '@(?P<collection>[^\/]+)\/(?P<file>(rss2|player)\.xml)@' );
define( 'PAT_PODCAST', 'http://podcast.open.ac.uk/feeds/@collection/' );
define( 'FEED_FILE', __DIR__ . '/../media/@collection-@file' );


$feed = filter_input( INPUT_GET, 'url', FILTER_SANITIZE_URL );

preg_match( REGEX_URL, $feed, $matches );

if (!$matches) {
  header( 'HTTP/1.1 400 Bad Request' );
  echo 'Missing or invalid feed {url}: '. $feed;
  exit;
}

$feed_file = strtr( FEED_FILE, array(
  '@collection' => $matches[ 'collection' ],
  '@file' => $matches[ 'file' ]
));

$rss = file_get_contents( $feed_file );

$count = null;
$rss = str_replace(_rss_podcast( $matches ), _rss_replace(), $rss, $count );


header( 'Content-Type: application/xml; charset=utf-8' );
header( 'X-feed-debug: '. json_encode(array(
  'url' => $feed,
  'feed_file' => $feed_file,
  'count' => $count,
)));

echo $rss;



# =================================================

function _rss_podcast( $matches ) {
  return strtr( PAT_PODCAST, array( '@collection' => $matches[ 'collection' ]));
}

function _rss_replace() {
  return 'http://' . $_SERVER[ 'HTTP_HOST' ] .
    str_replace(
      '?'. $_SERVER[ 'QUERY_STRING' ], '../media/', $_SERVER[ 'REQUEST_URI' ]);
}


#End.
