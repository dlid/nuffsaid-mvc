<?php

namespace Nuffsaid\Utility;

class CTextUtil {
	static function clipText($text, $start, $length) {

	  if( $start < 0 || $start > strlen($text) ) {
	    return $text;
	  }

	  if( $length > $start + $length) {
	    $length = $start + $length;
	  }

	  if( $start > 0) {
	    for( $c = $start; $c > 0; $c--) {
	      $char = $text[$c];
	      if( preg_match('/[^a-zA-Z0-9åäöÅÄÖ]/', $char) ) {
	        $c ++;
	        $length -= ($start -$c);
	        $start = $start - ($start -$c);
	        # minska length med samma värde
	        break;
	      }
	    }
	  }

	  if( $start + $length < strlen($text) ) {
	    for( $c = $start + $length; $c < strlen($text); $c++) {
	      $char = $text[$c];
	      if( preg_match('/[^a-zA-Z0-9åäöÅÄÖ]/', $char) ) {
	        $length+= $c - ($start + $length);
	        break;
	      } else if( $c == strlen($text)-1) {
	        $length = $c- $start + 1;
	      }
	    }
	  }

	  return ($start >0 ? "..." : '') .  mb_substr($text, $start, $length) . (($start+$length) < strlen($text) ? "..." : '');
	}

}