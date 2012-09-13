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
	$rawTimingData = explode( " ", $timingDataFromPost );
	$timingData = array();
	foreach( $rawTimingData as $keystroke ) {
		// The Javascript sends the data as:
		//    [key code],[time down],[time up]
		// (With each keypress triplet separated by a space)
		$keyCode_Down_Up = explode( ",", $keystroke );
		
		// If this is good data (e.g., not just a space)
		if( isset($keyCode_Down_Up[1]) ) {
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
			
			// push to the end of the array
			$timingData[] = $currentKey;
		}
	}
	
	return $timingData;
}

/**
 * Constructs a header for the CSV file corresponding the key phrase.
 *
 * @param $timingData Timing data for the string, obtained from the 
 *                    Javascript form submission. Should be the result of a 
 *                    single password entry.
 *                    
 *                    This is an array of associative arrays. For instance,
 *                    if you have a 10-character password, $timingData should
 *                    contain about 10 elements (more if you use the Shift key or
 *                    other nonprinting characters), and each of those elements 
 *                    should be an array with indices for "character" (indicating
 *                    what character this is), "key code", "timeUp", and 
 *                    "timeDown".
 *                    
 *                    If you have data from $_POST['timingData'], you'll need
 *                    to first run it through parseRawTimingData() before
 *                    passing it as a parameter here.
 * @param $hasRepetitionColumn True if the CSV file you want to generate
 *                             begins with a column labeled "repetition,"
 *                             false otherwise.
 * @return A string, terminated with a \n (newline), to be used as the CSV's
 *         header.
 */
function getCSVHeader( $timingData, $hasRepetitionColumn=true ) {
	$header = "";
	if( $hasRepetitionColumn ) {
		$header .= "repetition,";
	}
	$header .= "hold[" . $timingData[0]['character'] . "]";
	
	$prevChar = $timingData[0]['character'];
	for( $i = 1; $i < sizeof($timingData); $i++ ) {
		$thisChar = $timingData[$i]['character'];
		
		$dd = "keydown[" . $thisChar . "] - keydown[" . $prevChar . "]";
		$ud = "keydown[" . $thisChar . "] - keyup[" . $prevChar . "]";
		$h = "hold[" . $thisChar . "]";
		
		$header .= "," . $dd . "," . $ud . "," . $h;
		
		$prevChar = $thisChar;
	}
	$header .= "\n";
	
	return $header;
}

/**
 * Formats the training data for use with our R script.
 * 
 * @param $rawTrainingData An array with all the (raw) data we have
 *                         on the way the user types their password.
 *                         Probably obtained by querying the database
 *                         for all instances of their key phrase.
 * @return The training data, formatted as a long string. Write this to
 *         a file that you subsequently pass to the R script. When you do 
 *         so, you'll have a CSV file with the following columns:
 *           repetition, hold[0], keydown[1] - keydown[0], keydown[1] - keyup[0], hold[1], . . . , keydown[n] - keydown[n-1], keydown[n] - keyup[n-1], hold[n], keydown[Return] - keydown[n], keydown[Return] - keyup[n], hold[Return] 
 */
function prepareTrainingData( $rawTrainingData ) {
	$print_diagnostic = FALSE;
				
	// Parse the raw data
	$trainingData = array(); // an array of timing data
	foreach( $rawTrainingData as $dataPoint ) {
		// Push to the end of the array
		$trainingData[] = parseRawTimingData( $dataPoint );
	}
	
	// Build header
	$csv = "";
	if( $print_diagnostic ) {
		$csv = "\nRaw training data: " . print_r($rawTrainingData, true) . "\n";
	}
	$csv .= getCSVHeader( $trainingData[0] /* timing data on a single password entry */ );
	
	
	
	// For each time the user typed the password . . .
	for( $repetition = 0; $repetition < sizeof($trainingData); $repetition++ ) {
		//$csv .= print_r($trainingData[$repetition], true) . "\n\t";
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
	
	// For testing purposes: append the whole password
	if( $print_diagnostic ) {
		foreach( $canonicalPW as $char ) {
			$csv .= $char['character'];
		}
		$csv .= "\n";
		foreach( $canonicalPW as $char ) {
			$csv .= $char['keyCode'] . ",";
		}
		$csv .= "\n";
	}
	
	return $csv;
}
?>