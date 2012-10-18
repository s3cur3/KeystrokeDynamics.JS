<?php
/* Database variables */
$db_hostname = "localhost";
$db_username = "root";
$db_password = "NTF96pafzdwy";
$db_database = "keystroke_data";
$table_training_data = "training";
$table_training_output = "training_out";

/* Pages relevant to log-in */

// This is the page that the all login forms direct you to. This page
// should check that the username/password match a known combination, then 
// check taht the keystroke dynamics data matches that of the known account.
// This should probably be an absolute path.
$authenticationPage = "/validator.php";

// This page repeatedly collects training data on a user.
// This should probably be an absolute path.
$trainingPage = "/trainer.php";

// This is the page we send users to *after* they finish training (i.e.,
// upon successful creation of an account)
// This should probably be an absolute path.
$postTrainingPage = "/success.php";

?>