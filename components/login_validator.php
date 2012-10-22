<?php

include_once( '/components/keystroke_data_handlers.php' );
include_once( '/components/site_variables.php' );

// Sets an error status for the is_real_user() function.
// Don't call this directly!!
function error( &$errorOccurred=NULL,
                &$probabilityThatUserIsReal=NULL,
				&$absoluteScore=NULL ) {
	$errorOccurred = true;
	$absoluteScore = -1;
	$probabilityThatUserIsReal = -1;
	
	return false;
}

/**
 * Checks the user's keystroke data against the training model for that user.
 * 
 * Note that it is up to you to first validate that the input data matches a known
 * username/password combination!
 * 
 * @uses the global $_POST data (which contains the keystroke data).
 * @optional $errorOccurred Pass in a variable here and we will set it to True if an
 *                          error occurs (i.e., we can't complete the test for some 
 *				            reason).
 * @optional $probabilityThatUserIsReal Pass in a variable here and we will set it to
 *                                      an estimated percentage that the user is who
 *                                      they claim to be. Useful for testing purposes.
 * @optional $absoluteScore Pass in a variable here and we will set it to the user's
 *                          absolute score. Useful for testing purposes.					 
 * @return True if we judge that this is indeed the real user (i.e., not an imposter)
 * 		   and False if we judge that this is an imposter.
 */
function is_real_user( &$errorOccurred=NULL,
                       &$probabilityThatUserIsReal=NULL,
					   &$absoluteScore=NULL ) {
	$errorOccurred = false;
	
	$phrase = cleanse_sql_and_html($_POST['inputKeyPhrase']);
	$phraseDropdown = cleanse_sql_and_html($_POST['inputKeyPhraseDropdown']);
	
	$idSuffix = '';
	if( strlen($phrase) == 0 && strlen($phraseDropdown) > 0 ) {
		$idSuffix = 'Dropdown';
		$phrase = $phraseDropdown;
	}

	// Reconstruct the serialized data
	if( isset($_POST['timingData' . $idSuffix]) ) {
		echo "<p>The phrase you entered was <code>" 
			. $phrase . "</code></p>";
		
		// Split the POSTed data on spaces (to get individual keystrokes)
		$timingData = parseRawTimingData( $_POST['timingData' . $idSuffix] );

		// TODO: Die if the key phrase doesn't match any known
		
		
		// Write the detection model to a file for use by the authenticator.R script
		$serializedDetectionModel = getDetectionModel( $phrase );
		$serializedDetectionModel .= "\n";
		writeStringToFileForR( $serializedDetectionModel, "dmod" );
		
		// Write the timing data for this attempted password entry to a file 
		$thisAttemptCSV = getCSVHeader( $timingData, false /* no repetition column */ );
		$thisAttemptCSV .= getCSVLineFromTimingData( $timingData );
		writeStringToFileForR( $thisAttemptCSV, "current_attempt.csv" );
		
		
		// Call the R script for validation
		exec("/usr/bin/Rscript r/authenticator.R " . '2>&1', $out, $returnStatus);
		
		if( $returnStatus === 0 ) {
			$score = floatval(end($out));
			$cutoff = 800.0;
			$percentScore = ( $score/$cutoff > 1.0 ? 1.0 : $score/$cutoff ); 
			
			$probabilityThatUserIsReal = $percentScore;
			$absoluteScore = $score;
			
			// Evaluate whether you're an impersonator or not
			
			if( $percentScore >= 1.0 ) {
				return false;
			} else {
				return true;
			}
		} else {
			return error( $errorOccurred, $probabilityThatUserIsReal, $absoluteScore ); 
		}
	}
	return error( $errorOccurred, $probabilityThatUserIsReal, $absoluteScore );
}

?>