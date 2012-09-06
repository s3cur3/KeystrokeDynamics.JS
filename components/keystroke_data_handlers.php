<?php

/**
 * Parses the timing data that we get from our HTML + JS form
 * (in the format 
 * 		[key code],[time down],[time up]
 * with each keypress triplet separated by a space) and returns
 * a list of individual keypresses.
 *
 * @param $timingDataFromPost The timing data as it is received
 *                            from the Javascript + HTML form, or
 *                            as it is stored in the database.
 * @return An array (treated like a list) of keystroke data 
 *         structures. Each element of this array is a map 
 *         containing the following keys:
 *          * keyCode: the key code corresponding to this keypress
 *          * timeDown: the timestamp of the "keydown" event
 *          * timeUp: the timestamp of the "keyup" event
 *          * timeHeld: milliseconds between timeDown and timeUp
 *          * character: the character corresponding to this keyCode
 *                       (may be a string if this is a nonprinting
 *                       character)
 */
function parseRawTimingData( $timingDataFromPost ) {
	$rawTimingData = explode( " ", $_POST['timingData'] );
	$timingData = array();
	foreach( $rawTimingData as $keystroke ) {
		// The Javascript sends the data as:
		//    [key code],[time down],[time up]
		// (With each keypress triplet separated by a space)
		$keyCode_Down_Up = explode( ",", $keystroke );
		
		$currentKey['keyCode'] = $keyCode_Down_Up[0];
		$currentKey['timeDown'] = $keyCode_Down_Up[1];
		$currentKey['timeUp'] = $keyCode_Down_Up[2];
		
		$currentKey['timeHeld'] = $currentKey['timeUp'] - $currentKey['timeDown'];
		$currentKey['character'] = chr($currentKey['keyCode']);
		
		// Make non-printing characters readable
		if( $currentKey['keyCode'] == 16 ) {
			$currentKey['character'] = "SHIFT";
		} else if( $currentKey['keyCode'] == 13 ) {
			$currentKey['character'] = "ENTER";
		} else if( $currentKey['keyCode'] == 32 ) {
			$currentKey['character'] = "SPACE";
		}
		
		
		$timingData[] = $currentKey; // push to the end of the array
	}
}


/**
 * Formats the training data for use with our R script.
 * 
 * @param $rawTrainingData An array with all the (raw) data we have
 *                         on the way thez user types their password.
 *                         Probably obtained by querying the database
 *                         for all instances of their key phrase.
 * @return The training data, formatted as a long string. Write this to
 *         a file that you subsequently pass to the R script. When you do 
 *         so, you'll have a CSV file with the following columns:
 *           repetition, hold[0], keydown[1] - keydown[0], keydown[1] - keyup[0], hold[1], . . . , keydown[n] - keydown[n-1], keydown[n] - keyup[n-1], hold[n], keydown[Return] - keydown[n], keydown[Return] - keyup[n], hold[Return] 
 */
function prepareTrainingData( $rawTrainingData ) {				
	// Parse the raw data
	$trainingData = array();
	foreach( $rawTrainingData as $dataPoint ) {
		// Push to the end of the array
		$trainingData[] = parseRawTimingData( $dataPoint );
	}
	
	// TODO: build header
	$csv = "";
	
	
	
	// For each time the user typed the password . . .
	for( $repetition = 0; $repetition < sizeof($trainingData); $repetition++ ) {
		$csv .= $repetition . ",";
		
		$passwordEntry = $trainingData[$repetition];
		$csv .= $passwordEntry[0]['timeHeld']; // only care about time held for pos. 0
		
		// For each (other) character in the password . . .
		for( $i = 1; $i < sizeof($passwordEntry); $i++ ) {
			$dd = $passwordEntry[$i]['timeDown'] - $passwordEntry[$i-1]['timeDown'];
			$ud = $passwordEntry[$i]['timeDown'] - $passwordEntry[$i-1]['timeUp'];
			$h = $passwordEntry[$i]['timeHeld'];
			
			$csv .= "," . $dd . "," . $ud . "," . $h;
		}
		
		$csv .= "\n";
	}
	
	return $csv;
}
?>