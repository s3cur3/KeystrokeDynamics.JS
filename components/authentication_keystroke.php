<?php

include_once( 'keystroke_data_handlers.php' );
include_once( 'site_variables.php' );

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
function keystrokeDataMatchesUser( &$errorOccurred=NULL,
                                   &$probabilityThatUserIsReal=NULL,
					               &$absoluteScore=NULL ) {
	$errorOccurred = false;
	
	// Figure out whether the password was input in the normal login form or the
	// dropdown one
	$phrase = cleanseSQLAndHTML($_POST[KSD_FIELD_NAME]);

	// Reconstruct the serialized data
	if( isset($_POST['timingData']) ) {
		echo "<p>The phrase you entered was <code>" 
			. $phrase . "</code></p>";
		
		// Split the POSTed data on spaces (to get individual keystrokes)
		$timingData = parseRawTimingData( $_POST['timingData'] );
		
		// Write the detection model to a file for use by the authenticator.R script
		$serializedDetectionModel = getDetectionModel( $_SESSION['uid'], $phrase );
		$serializedDetectionModel .= "\n";
		writeStringToFileForR( $serializedDetectionModel, "dmod" );
		
		// Write the timing data for this attempted password entry to a file 
		$thisAttemptCSV = getCSVHeader( $timingData, false /* no repetition column */ );
		$thisAttemptCSV .= getCSVLineFromTimingData( $timingData );
		writeStringToFileForR( $thisAttemptCSV, "current_attempt.csv" );
		
		
		// Call the R script for validation
		exec("/usr/bin/Rscript r/authenticator.R " . '2>&1', $out, $returnStatus);
		
		echo '<pre>', print_r($out, true), '</pre>';
		
		if( $returnStatus === 0 ) {
			$score = floatval(end($out));
			$cutoff = 800.0;
			$percentScore = ( $score/$cutoff > 1.0 ? 1.0 : $score/$cutoff ); 
			
			$probabilityThatUserIsReal = $percentScore;
			$absoluteScore = $score;
			
			// Evaluate whether you're an impersonator or not
			echo "<p>Percent score: $percentScore </p>";
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

/**
 * @param $userID int The ID of the user for whom we are getting a captcha (this just ensures we don't have the user
 *                    provide a negative for themselves)
 * @return string The key phrase of the user we have chosen to get a negative training example for.
 */
function getCaptcha( $userID=0 ) {
    // Select a random user who needs negatives
    $needNegatives = getUsersWhoNeedNegatives();
    $selectedUID = array_pop($needNegatives);
    while( $selectedUID == $userID && !is_null($selectedUID) ) {
        $selectedUID = array_pop($needNegatives);
    }

    if( $selectedUID == $userID || is_null($selectedUID) ) {
        // Didn't have any users who *need* training examples; now we just select a random user
        $userIDs = getRandomUserIDs();

        $selectedUID = array_pop($userIDs);
        while( $selectedUID == $userID && !is_null($selectedUID) ) {
            $selectedUID = array_pop($userIDs);
        }
    }

    // Return the key phrase of that user
    return getKeyPhrase($selectedUID);
}

?>