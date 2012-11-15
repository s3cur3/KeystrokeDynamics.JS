<?php
include_once('site_variables.php');
include_once('authentication_keystroke.php');
include_once('database_fns.php');

// Set up the uLogin session
require_once( ULOGIN_DIR . 'config/all.inc.php' );
require_once( ULOGIN_DIR . 'main.inc.php' );

$ulogin = new uLogin('login', 'loginFail');

/* A wrapper for the session-start functionality of the auth library */
function start_session() {
	if (!sses_running())
		sses_start();
}

function userIsLoggedIn() {
	return isset($_SESSION['uid']) && isset($_SESSION['username']) 
	       && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn']===true);
}

function login( $uid, $username, $ulogin ) {
	$_SESSION['uid'] = $uid;
	$_SESSION['username'] = $username;
	$_SESSION['loggedIn'] = true;
}

/**
 * Use this function to do something special upon failing to
 * authenticte a user. This is currently only used by uLogin, so if you
 * aren't using uLogin as your authentication library, you probably want 
 * to ignore it.
 */
function loginFail( $uid, $username, $ulogin ) {
}

function logout(){
	unset($_SESSION['uid']);
	unset($_SESSION['username']);
	unset($_SESSION['loggedIn']);
}

/**
 * Creates a user for the "normal" authentication scheme (i.e., the one
 * using username and password)
 * @param uLogin uLogin The uLogin context
 * @param userName string The desired user name
 * @param password string The user's desired password
 * @return boolean True if the account was successfully created; false if it failed.
 */
function createUser( $uLogin, $userName, $password ) {
    $success = $uLogin->CreateUser( $userName,  $password );
    $success += attemptLogin(true);
    if( $success ) {
        // Need to get the user some negative training examples
        addUserToNeedsNegativesList( $_SESSION['uid'] );
    }
    else {
        echo "Failed!!";
    }
	return $success;
}

/**
 * Validates a captcha that we just received from a user's form data. Uses the $_POST
 * field "captchaNonce".
 * @param $captchaText string The captcha text that the user sent.
 * @return bool True if the captcha matches one we just sent, false otherwise.
 */
function captchaIsValid( $captchaText ) {
    return ulNonce::Verify($captchaText, $_POST['captchaNonce']);
}

/**
 * Logs a user in using username and password data, plus the keystroke dynamics
 * data, from $_POST.
 *
 * After calling this function, you can use userIsLoggedIn() to check whether the 
 * user is authenticated.
 *
 * Note that this function does *not* handle checking the captcha, if present.
 * @param $ignoreKeystrokeData bool True if we should allow the user to log in without
 *                             checking their keystroke dynamics data, false otherwise.
 * @return bool True if both the user's username and password matched a known one
 *         *and* the keystroke dynamics data on these fields matches the user.
 *         If either condition fails, we return false.
 */
function attemptLogin( $ignoreKeystrokeData=false ) {
    $ulogin = new uLogin('login', 'loginFail');
	
	$nonceType = $_POST['nonceType'];

	// Here we verify the nonce, so that only users can try to log in
	// to whom we've actually shown a login page. The first parameter
	// of Nonce::Verify needs to correspond to the parameter that we
	// used to create the nonce, but otherwise it can be anything
	// as long as they match.
	if( isset($_POST['nonce']) && ulNonce::Verify($nonceType, $_POST['nonce']) ) {
        // This is the line where we actually try to authenticate against some kind
        // of user database. Note that depending on the auth backend, this function might
        // redirect the user to a different page, in which case it does not return.
        // If you're using an authentication mechanism that redirects immediately,
        // you'll have to rework this code in order to check the keystroke data.
        $ulogin->Authenticate($_POST['user'],  $_POST['pwd']);

        // If we successfully authenticated the username and password, we need to
        // also check the keystroke dynamics data. If that passes, we log the user in.
        if( $ulogin->IsAuthSuccess() ) {
            if( $ignoreKeystrokeData ) {
                return true;
            } else {
                return keystrokeDataMatchesUser();
            }
        } else {
            // User failed authentication. Make sure they aren't logged in.
            logout();
        }
	} else {
        echo "Nonce check failed.";
    }
	return false;
}

function userExists( $userName ) {
    global $db_hostname, $db_username, $db_password, $db_database, $table_training_data, $table_training_output;

	$db_server = mysql_connect( $db_hostname, $db_username, $db_password );
	if ( !$db_server ) die( "<p>Unable to connect to MySQL: " . mysql_error() . '</p>' );

	// Select the keystroke database
	mysql_select_db( $db_database ) or die( "Unable to select database: " . mysql_error() );


	// Prevent SQL injection by using a placeholder query
	$placeholder_query = 'PREPARE selection FROM "SELECT * from `ul_logins` WHERE `username` = ?;"';
	mysql_query( $placeholder_query );

	$set_query = 'SET @username = "' . $userName . '";';
	mysql_query( $set_query );

	$execute_query = 'EXECUTE selection USING @username;';
	$result = mysql_query( $execute_query ) or die( "<p>Error querying the database.</p>");

	$deallocate_query = 'DEALLOCATE PREPARE selection;';
	mysql_query( $deallocate_query );


	// The result we got was just a resource handle on the SQL Server
	// Have to construct an array for the results
	$r = mysql_fetch_array($result);
    // This magically sets $xyz to the value of the column named
    // xyz in the current query.
    extract($r);

    // Close the connection to the MySQL server
	mysql_close( $db_server );

    if( isset($username) ) {
        return true;
    }
    return false;
}

/**
 * Deletes all data associated with a user.
 * @param $userID int The ID of the user to delete
 * @return bool True if account deletion succeeded, false otherwise.
 */
function deleteUser( $userID ) {
    $succeeded = true;
    if ( !$ulogin->DeleteUser( $userID ) ) {
        return false;
    }

    return $succeeded;
}

	
?>