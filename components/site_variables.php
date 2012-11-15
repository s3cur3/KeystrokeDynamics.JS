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

// This is the number of training examples that we require when creating an account.
// 15 should probably be a minimum.
define( "NUM_TRAINING_EXAMPLES", 15 );

define( "NUM_NEGATIVE_EXAMPLES", 5 );

?>