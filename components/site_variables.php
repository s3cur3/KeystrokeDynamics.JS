<?php
/* Database variables */
$db_hostname = "localhost";
$db_username = "root";
$db_password = "bitnami";
$db_database = "keystroke_data"; // the database containing the login data
$table_training_data = "training"; // the table containing the training data
$table_training_output = "training_out"; // table containing the finished training model
$table_training_negatives = "training_negatives";
$table_users_in_need_of_negatives = "users_requiring_negatives";


/* information about the uLogin location */
// Note that there is no reason you *must* use uLogin, but if you don't, you'll need to
// modify components/authentication.php to provides similar functionality

// The location of the uLogin installation; **Must end in a slash**
define( "ULOGIN_DIR", "/mnt/hgfs/Dropbox/school/Research/KeystrokeDynamics.JS/ulogin/" );

define( "SITE_ROOT", "/mnt/hgfs/Dropbox/school/Research/KeystrokeDynamics.JS/" );

/* Information about the field that we monitor for keystroke dynamics */
// The name of the form field that we monitor.
// You probably don't want to monitor a field that should be secret (like
// the password). Note that the name attribute should be the same for both
// the normal login form and the dropdown form.
define( "KSD_FIELD_NAME", "user" );

// The DOM ID of the form field that we monitor.
define( "KSD_FIELD_ID", "userName" );




/* Pages relevant to log-in */

// This is the page that the all login forms direct you to. This page
// should check that the username/password match a known combination, then 
// check that the keystroke dynamics data matches that of the known account.
// This should probably be an absolute path.
define( "AUTHENTICATION_PAGE", "/validator.php" );

// This page repeatedly collects training data on a user.
// This should probably be an absolute path.
define( "TRAINING_PAGE", "/create_account.php" );

// This is the page we send users to *after* they finish training (i.e.,
// upon successful creation of an account)
// This should probably be an absolute path.
define( "POST_TRAINING_PAGE", "/success.php" );


/* Various constants related to account creation and training */

// This is the number of training examples that we require when creating an account.
// 15 should probably be a minimum.
define( "NUM_TRAINING_EXAMPLES", 15 );

// The target number of negative training examples when creating an account
// 5 is probably enough.
define( "NUM_NEGATIVE_EXAMPLES", 5 );

// The max length of a username, in characters
define( "MAX_USERNAME_LENGTH", 99 );

// The minimum length of a username, in characters
define( "MIN_USERNAME_LENGTH", 8 );

// The max length of a password, in characters
// Depending on your underlying encryption system, it probably doesn't make sense
// for passwords to be longer than 60 characters
define( "MAX_PASSWORD_LENGTH", 60 );

// The minimum length of a password, in characters
define( "MIN_PASSWORD_LENGTH", 0 );



?>