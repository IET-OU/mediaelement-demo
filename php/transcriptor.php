#!/usr/bin/env php
<?php
/**
  Convert SRT/WebVTT subtitles/captions to an SRT word-level timed-transcript.

  Nick Freear, 16 September 2014.
*/
date_default_timezone_set( 'Europe/London' );


  $captions_file = 'media/ou-student-experiences-openings-captions.srt';

  $out_file = preg_replace( '/\.(srt|vtt)/', '-words.$1', $captions_file );

  $captions_r = @file( $captions_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
  $output_r = $debug_r = array();

  if (!$captions_r) {
    _error( "Failed to read file, $capions_file" );
  }
  //var_dump( $captions_r );

  $count = 1;
  for ($i = 0; $i < count($captions_r); $i++) {
  #foreach ($captions_r as $line ) {
    $line = $captions_r[ $i ];
    if ($line == 'WEBVTT') {
      $output_r[] = $line;
    }
    elseif (preg_match('/^(\d+)/', $line)) {
      $idx = $line;
      $times = $captions_r[ $i + 1 ];
      $text  = $captions_r[ $i + 2 ];
      $i += 2;

      //if ('/(\d+):(\d):(\d+.\d*) \-\->'
      if (preg_match('/^(?P<start>\d[\d\:]+)(?P<ms>\.\d+) \-\-> (?P<end>\d[\d\:]+)(?P<ms_2>\.\d+)/', $times, $match)) {
        $t_init = strtotime($match[ 'start' ]) . $match[ 'ms' ];
        $t_end = strtotime($match[ 'end' ]) . $match[ 'ms_2' ];
      } else {
        #ERROR.
      }

      $words_r = explode( ' ', $text );
      $per_word =  ($t_end - $t_init) / (count( $words_r ) - 1);

      $debug_r[] = $t_init;
      $debug_r[] = $t_end;
      $debug_r[] = $t_end - $t_init;
      $debug_r[] = $per_word;
      $debug_r[] = $words_r;

      $t_start = $t_init;
      foreach ($words_r as $word) {
        if ('' == $word) break;

        //
        $output_r[] = "\n" . $count;
        $output_r[] = caption_times( $t_start, $t_start + $per_word );
        $output_r[] = $word .' ';

        $t_start += $per_word;
        $count++;
      }
    }
	else {
      #ERROR?
    }
  }


  $bytes = file_put_contents( $out_file, implode( "\n", $output_r ));


  var_dump( $output_r );
  #var_dump( $debug_r );



  function caption_times( $t_start, $t_end ) {
    return _time( $t_start ) .' --> '. _time( $t_end );
  }

  function _time( $stamp_ms ) {
    #return date_format(date_create( $stamp );
    preg_match( '/^(?P<t>\d+)\.(?P<ms>\d+)/', $stamp_ms, $match );
    $stamp = $match[ 't' ];
    $ms = $match[ 'ms' ];
    return date( 'H:i:s', $stamp ) .'.'.
        substr( sprintf( '%0-3s', $ms ), 0, 3 );
  }

  function _error( $msg ) {
    echo 'ERROR. ' . $msg;
    die();
  }

#End.
