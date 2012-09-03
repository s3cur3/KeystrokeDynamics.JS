<?php

/**
 * Sanitizes a string, removing both HTML and
 * SQL special characters. 
 * @param $string The string to sanitize
 * @return A sanitized version of the string, safe for both
 *         SQL queries and display in HTML
 */
function cleanse_sql_and_html( $string )
{
	global $db_hostname, $db_username, $db_password, $db_database;
	// Connect to the database
	$db_server = mysql_connect( $db_hostname, $db_username, $db_password );
	
	// If PHP "helpfully" added backslashes to escape quotes, remove them
	if ( get_magic_quotes_gpc() )
		$string = stripslashes( $string );
	
	// Strip MySQL entities
	$string = mysql_real_escape_string( $string );
	
	// Close the connection to the MySQL server
	mysql_close( $db_server );
	
	// Strip html entities
    return htmlentities( $string );
}

?>