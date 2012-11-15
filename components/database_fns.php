<?php

global $db_hostname, $db_username, $db_password, $db_database;
include_once( 'site_variables.php' );

/**
 * Sanitizes a string, removing both HTML and
 * SQL special characters. 
 * @param $string The string to sanitize
 * @requires Global variables $db_hostname, $db_username, $db_password, $db_database for mysql_connect
 * @return A sanitized version of the string, safe for both
 *         SQL queries and display in HTML
 */
function cleanseSQLAndHTML( $string ) {
	// If PHP "helpfully" added backslashes to escape quotes, remove them
	if ( get_magic_quotes_gpc() )
		$string = stripslashes( $string );
	
	global $db_hostname, $db_username, $db_password, $db_database;
	// Connect to the database
	if( isset( $db_hostname ) && isset( $db_username ) 
		&& isset( $db_password ) && isset( $db_database ) ) {
		$db_server = mysql_connect( $db_hostname, $db_username, $db_password );
		
		// Strip MySQL entities
		$string = mysql_real_escape_string( $string );
		
		// Close the connection to the MySQL server
		mysql_close( $db_server );
	}
	
	// Strip html entities
    return htmlentities( $string );
}

function create_db() {
	global $db_hostname, $db_username, $db_password, $db_database;
	
	$db_server = mysql_connect( $db_hostname, $db_username, $db_password );
	if ( !$db_server ) die( "<p>Unable to connect to MySQL: [" . mysql_errno() . "] " . mysql_error() . '</p>' );
	
	mysql_query( "create database if not exists keystroke_data;" );
	
	// Close the connection to the MySQL server
	mysql_close( $db_server );
}

/**
 * Creates the following tables used by the machine learning tools:
 *   CREATE TABLE IF NOT EXISTS `training` (
 *		  `id` int(11) NOT NULL AUTO_INCREMENT,
 *		  `key_phrase` varchar(100) NOT NULL,
 *		  `timing_array` varchar(5000) NOT NULL,
 *		  PRIMARY KEY (`id`),
 *		  UNIQUE KEY `id` (`id`)
 *	) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;
 * @TODO: fix this!
 */
function create_tables() {
	global $db_hostname, $db_username, $db_password, $db_database, $table_training_data, $table_training_output;
	$db_server = mysql_connect( $db_hostname, $db_username, $db_password );
	
	if ( !$db_server ) die( "<p>Unable to connect to MySQL: " . mysql_error() . '</p>' );
	
	$training_q = "CREATE TABLE `$table_training_data` ( `key_phrase` varchar(200) default NULL, "
		. "`timing_array` varchar(5000) default NULL );";
	mysql_query( $training_q );
	
	$training_out_q = "CREATE TABLE `$table_training_output` ( `key_phrase` varchar(200) default NULL, "
		. "`output` varchar(5000) default NULL );";
	mysql_query( $training_out_q );
	
	// Close the connection to the MySQL server
	mysql_close( $db_server );
}

/**
 * During the training phase for a user, this is used to add training data
 * @param $userID int The ID of the user, taken from the authentication library's
 *                backend (or the $_SESSION data)
 * @param $keyPhrase string The key phrase chosen by the user (used as an identifier)
 *                    This is probably either the username or email address.
 * @param $timingData array The list of timing data, which comes from the client-side (Javascript)
 */
function storeTrainingData( $userID, $keyPhrase, $timingData ) {
	global $db_hostname, $db_username, $db_password, $db_database, $table_training_data, $table_training_output;
	
	$db_server = mysql_connect( $db_hostname, $db_username, $db_password );
	if ( !$db_server ) die( "<p>Unable to connect to MySQL: " . mysql_error() . '</p>' );
	
	// Select the keystroke database
	mysql_select_db( $db_database ) or die( "Unable to select database: " . mysql_error() );
	
	
	// Prevent SQL injection by using a placeholder query
	$placeholder_query = 'PREPARE insertion FROM "INSERT INTO '. $table_training_data . ' VALUES(?,?,?,?);"';
	mysql_query( $placeholder_query );
	
	$set_query = 'SET @id = null, @user_id = "' . $userID . '", @key_phrase = "' . $keyPhrase . '", ' 
				. '@timing_array = "' . $timingData . '";';
	mysql_query( $set_query );

	$execute_query = 'EXECUTE insertion USING @id, @user_id, @key_phrase, @timing_array;';
	mysql_query( $execute_query );
	
	$deallocate_query = 'DEALLOCATE PREPARE insertion;';
	mysql_query( $deallocate_query );	
	
	// Close the connection to the MySQL server
	mysql_close( $db_server );
}

/**
 * Makes a note in the database that this user needs negative examples for the sake of 
 * training the detector
 * @param $userID int The ID of the user, taken from the authentication library's
 *                backend (or the $_SESSION data)
 */
function addUserToNeedsNegativesList( $userID ) {
	global $db_hostname, $db_username, $db_password, $db_database, $table_users_in_need_of_negatives;
	
	$db_server = mysql_connect( $db_hostname, $db_username, $db_password );
	if ( !$db_server ) die( "<p>Unable to connect to MySQL: " . mysql_error() . '</p>' );
	
	// Select the keystroke database
	mysql_select_db( $db_database ) or die( "Unable to select database: " . mysql_error() );
	
	
	// Prevent SQL injection by using a placeholder query
	$placeholder_query = 'PREPARE insertion FROM "INSERT INTO '. $table_users_in_need_of_negatives . ' VALUES(?);"';
	mysql_query( $placeholder_query );
	
	$set_query = 'SET @user_id = "' . $userID . '"';
	mysql_query( $set_query );

	$execute_query = 'EXECUTE insertion USING @user_id;';
	mysql_query( $execute_query );
	
	$deallocate_query = 'DEALLOCATE PREPARE insertion;';
	mysql_query( $deallocate_query );	
	
	// Close the connection to the MySQL server
	mysql_close( $db_server );
}

/**
 * Store a negative training example for a user
 * @param $userID int The ID of the *real* user, taken from the authentication library's
 *                backend (or the $_SESSION data)
 * @param $impostorID int The ID of the "impostor" (the user who isn't actually associated with this key phrase)
 * @param $keyPhrase string The key phrase chosen by the user (used as an identifier)
 *                    This is probably either the username or email address.
 * @param $timingData array The list of timing data, which comes from the client-side (Javascript)
 */
function storeNegativeTrainingData( $userID, $impostorID, $keyPhrase, $timingData ) {
    global $db_hostname, $db_username, $db_password, $db_database, $table_training_negatives;

    $db_server = mysql_connect( $db_hostname, $db_username, $db_password );
    if ( !$db_server ) die( "<p>Unable to connect to MySQL: " . mysql_error() . '</p>' );

    // Select the keystroke database
    mysql_select_db( $db_database ) or die( "Unable to select database: " . mysql_error() );


    // Prevent SQL injection by using a placeholder query
    $placeholder_query = 'PREPARE insertion FROM "INSERT INTO '. $table_training_negatives . ' VALUES(?,?,?,?,?);"';
    mysql_query( $placeholder_query );

    $set_query = 'SET @id = null, @user_id = "' . $userID . '", @impostor_id = "' . $impostorID .
        '", @key_phrase = "' . $keyPhrase . '", ' . '@timing_array = "' . $timingData . '";';
    mysql_query( $set_query );

    $execute_query = 'EXECUTE insertion USING @id, @impostor_id, @user_id, @key_phrase, @timing_array;';
    mysql_query( $execute_query );

    $deallocate_query = 'DEALLOCATE PREPARE insertion;';
    mysql_query( $deallocate_query );

    // Close the connection to the MySQL server
    mysql_close( $db_server );

    // Now delete the real user from the list of users requiring negative training examples as necessary
    if( getNumNegatives($userID) >= NUM_NEGATIVE_EXAMPLES ) {
        removeUserFromNeedsNegativesList( $userID );
    }
}

/**
 * Deletes a user from the list of users who need negative training examples
 * @param $userID int The ID of the user who now has enough negative examples
 */
function removeUserFromNeedsNegativesList( $userID ) {
    global $db_hostname, $db_username, $db_password, $db_database, $table_training_negatives;
    $db_server = mysql_connect( $db_hostname, $db_username, $db_password );
    if ( !$db_server ) die( "<p>Unable to connect to MySQL: " . mysql_error() . '</p>' );

    // Select the keystroke database
    mysql_select_db( $db_database ) or die( "Unable to select database: " . mysql_error() );

    // Make sure we don't already have a training model for this guy
    $placeholder_query = 'PREPARE deletion FROM "DELETE FROM '. $table_training_negatives . ' WHERE `user_id` = ?;"';
    mysql_query( $placeholder_query );

    $set_query = 'SET @user_id = "' . $userID . '";';
    mysql_query( $set_query );

    $execute_query = 'EXECUTE deletion USING @user_id;';
    mysql_query( $execute_query );

    $deallocate_query = 'DEALLOCATE PREPARE deletion;';
    mysql_query( $deallocate_query );

    mysql_close( $db_server );
}

/**
 * Counts the number of negative training examples we have for the user with the indicated ID
 * @param $userID int The user for whom we should check the "needs negatives" table
 * @return int The number of negative training examples we have for the user with the specified user ID
 */
function getNumNegatives($userID) {
    global $db_hostname, $db_username, $db_password, $db_database, $table_training_negatives;
    $db_server = mysql_connect( $db_hostname, $db_username, $db_password );
    if ( !$db_server ) die( "<p>Unable to connect to MySQL: " . mysql_error() . '</p>' );

    // Select the keystroke database
    mysql_select_db( $db_database ) or die( "Unable to select database: " . mysql_error() );


    // Prevent SQL injection by using a placeholder query
    $placeholder_query = 'PREPARE selection FROM "SELECT COUNT(*) from `'. $table_training_negatives . '` WHERE `user_id` = ?;"';
    mysql_query( $placeholder_query );

    $set_query = 'SET @user_id = "' . $userID . '";';
    mysql_query( $set_query );

    $execute_query = 'EXECUTE selection USING @user_id;';
    $result = mysql_query( $execute_query ) or die( "<p>Error querying the database.</p>");

    $deallocate_query = 'DEALLOCATE PREPARE selection;';
    mysql_query( $deallocate_query );


    // The result we got was just a resource handle on the SQL Server
    // Have to construct an array for the results
    $r = mysql_fetch_array($result);

    // Close the connection to the MySQL server
    mysql_close( $db_server );

    return $r[0];
}

/**
 * Checks whether the user needs negative training examples
 * @param $userID int The user for whom we should check the "needs negatives" table
 * @return bool True if the user needs negative training examples, false otherwise
 */
function userNeedsNegatives( $userID ) {
    global $db_hostname, $db_username, $db_password, $db_database, $table_users_in_need_of_negatives;
	$db_server = mysql_connect( $db_hostname, $db_username, $db_password );
	if ( !$db_server ) die( "<p>Unable to connect to MySQL: " . mysql_error() . '</p>' );
	
	// Select the keystroke database
	mysql_select_db( $db_database ) or die( "Unable to select database: " . mysql_error() );
	
	
	// Prevent SQL injection by using a placeholder query
	$placeholder_query = 'PREPARE selection FROM "SELECT * from `'. $table_users_in_need_of_negatives . '` WHERE `user_id` = ?;"';
	mysql_query( $placeholder_query );
	
	$set_query = 'SET @user_id = "' . $userID . '";';
	mysql_query( $set_query );

	$execute_query = 'EXECUTE selection USING @user_id;';
	$result = mysql_query( $execute_query ) or die( "<p>Error querying the database.</p>");
	
	$deallocate_query = 'DEALLOCATE PREPARE selection;';
	mysql_query( $deallocate_query );	
	
	
	// The result we got was just a resource handle on the SQL Server
	// Have to construct an array for the results
	$data = array();
	$r = mysql_fetch_array($result);
	extract($r);
		
	// Close the connection to the MySQL server
	mysql_close( $db_server );
	
	return  isset($user_id);
}

/**
 * Returns a short list of users, *in random order*, who need negative training examples.
 * @return array up to 10 randomly ordered user IDs corresponding to users requiring
 *         negative training examples
 */
function getUsersWhoNeedNegatives() {
    global $db_hostname, $db_username, $db_password, $db_database, $table_users_in_need_of_negatives;

    $db_server = mysql_connect( $db_hostname, $db_username, $db_password );
    if ( !$db_server ) die( "<p>Unable to connect to MySQL: " . mysql_error() . '</p>' );

    // Select the keystroke database
    mysql_select_db( $db_database ) or die( "Unable to select database: " . mysql_error() );

    $selection_query = 'SELECT `user_id` from `'. $table_users_in_need_of_negatives . '` ORDER BY RAND() LIMIT 10;';

    $result = mysql_query( $selection_query ) or die( "<p>Error querying the database.</p>");

    // The result we got was just a resource handle on the SQL Server
    // Have to construct an array for the results
    $data = array();
    while ($r = mysql_fetch_array($result)) {
        // This magically sets $xyz to the value of the column named
        // xyz in the current query.
        extract($r);

        // push the value from the column "user_id" to the end of the list
        if( $user_id != 0 ) { // 0 indicates an error
            $data[] = $user_id;
        }
    }

    // Close the connection to the MySQL server
    mysql_close( $db_server );

    return $data;
}

/**
 * If the list of users who need negative training examples was too short, you can use this to get a list
 * of *any* users
 * @return array[int] The user IDs of 10 randomly selected users
 */
function getRandomUserIDs() {
    global $db_hostname, $db_username, $db_password, $db_database, $table_training_data;

    $db_server = mysql_connect( $db_hostname, $db_username, $db_password );
    if ( !$db_server ) die( "<p>Unable to connect to MySQL: " . mysql_error() . '</p>' );

    // Select the keystroke database
    mysql_select_db( $db_database ) or die( "Unable to select database: " . mysql_error() );

    $query = 'SELECT `user_id` from `'. $table_training_data . '` ORDER BY RAND() LIMIT 10;';
    $result = mysql_query( $query ) or die( "<p>Error querying the database.</p>");

    // The result we got was just a resource handle on the SQL Server
    // Have to construct an array for the results
    $data = array();
    while ($r = mysql_fetch_array($result)) {
        // This magically sets $xyz to the value of the column named
        // xyz in the current query.
        extract($r);

        // push the value from the column "user_id" to the end of the list
        if( $user_id != 0 ) { // 0 indicates an error
            $data[] = $user_id;
        }
    }

    return $data; // the value from the column "key_phrase" in the SQL query

}

/**
 * Gets all arrays of timing data (stored in the "training" table 
 * in the database) for a given key phrase.
 * @param $userID int The user for whom we should retrieve all known training data.
 * @return array: An array of timing data (one element per training instance
 *         of the password).
 */
function getTrainingData( $userID ) {
	global $db_hostname, $db_username, $db_password, $db_database, $table_training_data, $table_training_output;
	
	$db_server = mysql_connect( $db_hostname, $db_username, $db_password );
	if ( !$db_server ) die( "<p>Unable to connect to MySQL: " . mysql_error() . '</p>' );
	
	// Select the keystroke database
	mysql_select_db( $db_database ) or die( "Unable to select database: " . mysql_error() );

	// Prevent SQL injection by using a placeholder query
	$placeholder_query = 'PREPARE selection FROM "SELECT * from `'. $table_training_data . '` WHERE `user_id` = ?;"';
	mysql_query( $placeholder_query );
	
	$set_query = 'SET @user_id = "' . $userID . '";';
	mysql_query( $set_query );

	$execute_query = 'EXECUTE selection USING @user_id;';
	$result = mysql_query( $execute_query ) or die( "<p>Error querying the database.</p>");
	
	$deallocate_query = 'DEALLOCATE PREPARE selection;';
	mysql_query( $deallocate_query );	
	
	
	// The result we got was just a resource handle on the SQL Server
	// Have to construct an array for the results
	$data = array();
	while ($r = mysql_fetch_array($result)) {
		// This magically sets $xyz to the value of the column named
		// xyz in the current query.
		extract($r);
		
		// push the value from the column "timing_array" to the end of the list
		$data[] = $timing_array;
	}
	
	// Close the connection to the MySQL server
	mysql_close( $db_server );
	
	return $data;
}

/**
 * Dumps the training data to an HTML table. *DO NOT* use if you have a large data set!
 * @TODO: update this
 */
function dump_training_data() {
	// Connect to the database server
	global $db_hostname, $db_username, $db_password, $db_database, $table_training_data, $table_training_output;
	$db_server = mysql_connect( $db_hostname, $db_username, $db_password );
	if ( !$db_server ) die( "<p>Unable to connect to MySQL: " . mysql_error() . '</p>' );
	
	mysql_select_db( $db_database ) or die( "Unable to select database: " . mysql_error() );
	
	
	echo "<table>";
	
	// Query the database
	$query = "SELECT key_phrase, timing_array from $table_training_data order by key_phrase;";
	$result = mysql_query( $query ) or die( "<tr><td colspan=\"4\">Mysql query failed</td></tr>");
	
	
	
	// Loop through the results
	while ($r = mysql_fetch_array($result)) {
		// This magically sets $xyz to the value of the column named
		// xyz in the current query.
		extract($r);
		
		// Key Phrase | Timing Data
		echo "<tr><td>$key_phrase</td> <td>$timing_array</td></tr>";
	}
	
	echo "</table>";
	
	// Close the connection to the MySQL server
	mysql_close( $db_server );
}

/**
 * During the training phase for a user, this is used to add training data
 * @param $userID The user's user ID from your authentication library (*not*
 *                the same as the username)
 * @param $key_phrase
 * @param $output The output training model
 */
function storeDetectionModel( $userID, $key_phrase, $output ) {
	global $db_hostname, $db_username, $db_password, $db_database, $table_training_data, $table_training_output;
	
	$db_server = mysql_connect( $db_hostname, $db_username, $db_password );
	if ( !$db_server ) die( "<p>Unable to connect to MySQL: " . mysql_error() . '</p>' );
	
	// Select the keystroke database
	mysql_select_db( $db_database ) or die( "Unable to select database: " . mysql_error() );
	
	// Make sure we don't already have a training model for this guy
	$placeholder_query = 'PREPARE deletion FROM "DELETE FROM '. $table_training_output . ' WHERE `key_phrase` = ?;"';
	mysql_query( $placeholder_query );

	$set_query = 'SET @key_phrase = "' . $key_phrase . '";';
	mysql_query( $set_query );

	$execute_query = 'EXECUTE deletion USING @key_phrase;';
	mysql_query( $execute_query );
	
	$deallocate_query = 'DEALLOCATE PREPARE deletion;';
	mysql_query( $deallocate_query );
	
	// Prevent SQL injection by using a placeholder query
	$placeholder_query = 'PREPARE insertion FROM "INSERT INTO '. $table_training_output . ' VALUES(?,?,?,?);"';
	mysql_query( $placeholder_query );

	$set_query = 'SET @modified=NULL, @user_id = "' . $userID  . '", @key_phrase = "' . $key_phrase . '", @serialized_model = "' . $output . '";';
	mysql_query( $set_query );

	$execute_query = 'EXECUTE insertion USING @modified, @user_id, @key_phrase, @serialized_model;';
	mysql_query( $execute_query );
	
	$deallocate_query = 'DEALLOCATE PREPARE insertion;';
	mysql_query( $deallocate_query );	
	
	// Close the connection to the MySQL server
	mysql_close( $db_server );
}

/**
 * Gets the detection model generated by trainer.R.
 * @param $user_id The ID of the user (taken from our authentication library)
 *                 for whom we should retrieve the detection model.
 * @return the comma-separated-value detection model
 * @TODO: Make a note on how you use this in R (how do you read it in?)
 */
function getDetectionModel( $user_id ) {
	global $db_hostname, $db_username, $db_password, $db_database, $table_training_data, $table_training_output;
	
	$db_server = mysql_connect( $db_hostname, $db_username, $db_password );
	if ( !$db_server ) die( "<p>Unable to connect to MySQL: " . mysql_error() . '</p>' );
	
	// Select the keystroke database
	mysql_select_db( $db_database ) or die( "Unable to select database: " . mysql_error() );
	
	
	// Prevent SQL injection by using a placeholder query
	$placeholder_query = 'PREPARE selection FROM "SELECT * from `'. $table_training_output . '` WHERE `user_id` = ?;"';
	mysql_query( $placeholder_query );
	
	$set_query = 'SET @user_id = "' . $user_id . '";';
	mysql_query( $set_query );

	$execute_query = 'EXECUTE selection USING @user_id;';
	$result = mysql_query( $execute_query ) or die( "<p>Error querying the database.</p>");
	
	$deallocate_query = 'DEALLOCATE PREPARE selection;';
	mysql_query( $deallocate_query );	
	
	
	// The result we got was just a resource handle on the SQL Server
	// Have to construct an array for the results
	$r = mysql_fetch_array($result);
	// This magically sets $xyz to the value of the column named
	// xyz in the current query.
	extract($r);
	
	return $serialized_model; // the value from the column "serialized_model" in the SQL query
}

/**
 * Gets the key phrase associated with a user ID
 * @param int $userID The user ID of the user whose key phrase you want
 * @return string The user's key phrase
 */
function getKeyPhrase( $userID ) {
    global $db_hostname, $db_username, $db_password, $db_database, $table_training_data;

    $db_server = mysql_connect( $db_hostname, $db_username, $db_password );
    if ( !$db_server ) die( "<p>Unable to connect to MySQL: " . mysql_error() . '</p>' );

    // Select the keystroke database
    mysql_select_db( $db_database ) or die( "Unable to select database: " . mysql_error() );


    // Prevent SQL injection by using a placeholder query
    $placeholder_query = 'PREPARE selection FROM "SELECT `key_phrase` from `'. $table_training_data . '` WHERE `user_id` = ? LIMIT 1;"';
    mysql_query( $placeholder_query );

    $set_query = 'SET @user_id = "' . $userID . '";';
    mysql_query( $set_query );

    $execute_query = 'EXECUTE selection USING @user_id;';
    $result = mysql_query( $execute_query ) or die( "<p>Error querying the database.</p>");

    $deallocate_query = 'DEALLOCATE PREPARE selection;';
    mysql_query( $deallocate_query );


    // The result we got was just a resource handle on the SQL Server
    // Have to construct an array for the results
    $r = mysql_fetch_array($result);
    // This magically sets $xyz to the value of the column named
    // xyz in the current query.
    extract($r);

    return $key_phrase; // the value from the column "key_phrase" in the SQL query
}

/**
 * Gets the user ID associated with a given key phrase. You should probably only use this if your
 * key phrase is globally unique (e.g., it is the user's username)
 * @param string $keyPhrase The user's key phrase
 * @return int The user ID of the user whose key phrase you want
 */
function getUserID( $keyPhrase ) {
    global $db_hostname, $db_username, $db_password, $db_database, $table_training_data;

    $db_server = mysql_connect( $db_hostname, $db_username, $db_password );
    if ( !$db_server ) die( "<p>Unable to connect to MySQL: " . mysql_error() . '</p>' );

    // Select the keystroke database
    mysql_select_db( $db_database ) or die( "Unable to select database: " . mysql_error() );


    // Prevent SQL injection by using a placeholder query
    $placeholder_query = 'PREPARE selection FROM "SELECT `user_id` from `'. $table_training_data . '` WHERE `key_phrase` = ? LIMIT 1;"';
    mysql_query( $placeholder_query );

    $set_query = 'SET @key_phrase = "' . $keyPhrase . '";';
    mysql_query( $set_query );

    $execute_query = 'EXECUTE selection USING @key_phrase;';
    $result = mysql_query( $execute_query ) or die( "<p>Error querying the database.</p>");

    $deallocate_query = 'DEALLOCATE PREPARE selection;';
    mysql_query( $deallocate_query );


    // The result we got was just a resource handle on the SQL Server
    // Have to construct an array for the results
    $r = mysql_fetch_array($result);
    // This magically sets $xyz to the value of the column named
    // xyz in the current query.
    extract($r);
    return $user_id; // the value from the column "user_id" in the SQL query
}

?>