<?php
global $db_hostname, $db_username, $db_password, $db_database;
$db_hostname = "localhost";
$db_username = "root";
$db_password = "NTF96pafzdwy";
$db_database = "keystroke_data";
$table_training_data = "training";
$table_training_output = "training_out";

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
 *	  `id` int(11) NOT NULL,
 *	  `key_phrase` varchar(100) NOT NULL,
 *	  `timing_array` varchar(5000) NOT NULL,
 *	  PRIMARY KEY (`id`),
 *	  UNIQUE KEY `id` (`id`)
 *	);
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
 * @param $key_phrase The key phrase chosen by the user (used as an identifier)
 * @param $timing_data The list of timing data, which comes from the client-side (Javascript)
 */
function insert_training_data_into_table( $key_phrase, $timing_data ) {
	global $db_hostname, $db_username, $db_password, $db_database, $table_training_data, $table_training_output;
	
	$db_server = mysql_connect( $db_hostname, $db_username, $db_password );
	if ( !$db_server ) die( "<p>Unable to connect to MySQL: " . mysql_error() . '</p>' );
	
	// Select the keystroke database
	mysql_select_db( $db_database ) or die( "Unable to select database: " . mysql_error() );
	
	
	// Prevent SQL injection by using a placeholder query
	$placeholder_query = 'PREPARE insertion FROM "INSERT INTO '. $table_training_data . ' VALUES(?,?,?);"';
	mysql_query( $placeholder_query );
	
	$set_query = 'SET @id = null, @key_phrase = "' . $key_phrase . '", @timing_array = "' . $timing_data . '";';
	mysql_query( $set_query );

	$execute_query = 'EXECUTE insertion USING @key_phrase, @timing_array;';
	mysql_query( $execute_query );
	
	$deallocate_query = 'DEALLOCATE PREPARE insertion;';
	mysql_query( $deallocate_query );	
	
	// Close the connection to the MySQL server
	mysql_close( $db_server );
}

/**
 * Sanitizes a string, removing both HTML and
 * SQL special characters. 
 * @param $string The string to sanitize
 * @requires Global variables $db_hostname, $db_username, $db_password, $db_database for mysql_connect
 * @return A sanitized version of the string, safe for both
 *         SQL queries and display in HTML
 */
function cleanse_sql_and_html( $string ) {
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

/**
 * Gets all arrays of timing data (stored in the "training" table 
 * in the database) for a given key phrase.
 * @param $key_phrase The key phrase for which we should 
 *                    retrieve all known training data.
 * @return An array of timing data (one element per training instance 
 *         of the password).
 */
function getTrainingData( $key_phrase ) {
	global $db_hostname, $db_username, $db_password, $db_database, $table_training_data, $table_training_output;
	
	$db_server = mysql_connect( $db_hostname, $db_username, $db_password );
	if ( !$db_server ) die( "<p>Unable to connect to MySQL: " . mysql_error() . '</p>' );
	
	// Select the keystroke database
	mysql_select_db( $db_database ) or die( "Unable to select database: " . mysql_error() );
	
	
	// Prevent SQL injection by using a placeholder query
	$placeholder_query = 'PREPARE selection FROM "SELECT * from `'. $table_training_data . '` WHERE `key_phrase` = ?;"';
	mysql_query( $placeholder_query );
	
	$set_query = 'SET @key_phrase = "' . $key_phrase . '";';
	mysql_query( $set_query );

	$execute_query = 'EXECUTE selection USING @key_phrase;';
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

?>