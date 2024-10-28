<?php
/*
 * Core bbPress functions.
 *
 * @package bbPress
 * bb-press/bb-includes/functions.bb-core.php
 */
// GMT -> so many minutes ago
//in the case that the function exists 
if(!function_exists('bb_since')){
function bb_since( $original, $do_more = 0 ) {
	$today = time();
	$original = strtotime($original);
	// array of time period chunks
	$chunks = array(
		( 60 * 60 * 24 * 365 ), // years
		( 60 * 60 * 24 * 30 ),  // months
		( 60 * 60 * 24 * 7 ),   // weeks
		( 60 * 60 * 24 ),       // days
		( 60 * 60 ),            // hours
		( 60 ),                 // minutes
		( 1 )                   // seconds
	);

	$since = $today - $original;

	for ($i = 0, $j = count($chunks); $i < $j; $i++) {
		$seconds = $chunks[$i];

		if ( 0 != $count = floor($since / $seconds) )
			break;
	}

	$trans = array(
		_n( '%d year', '%d years', $count ),
		_n( '%d month', '%d months', $count ),
		_n( '%d week', '%d weeks', $count ),
		_n( '%d day', '%d days', $count ),
		_n( '%d hour', '%d hours', $count ),
		_n( '%d minute', '%d minutes', $count ),
		_n( '%d second', '%d seconds', $count )
	);


	$print = sprintf( $trans[$i], $count );

	if ($do_more && $i + 1 < $j) {
		$seconds2 = $chunks[$i + 1];
		if ( 0 != $count2 = floor( ($since - $seconds * $count) / $seconds2) )
			$print .= sprintf( $trans[$i + 1], $count2 );
	}
	return $print;
}
}
?>