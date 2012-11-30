<?php
/**
 * This page serves to create an account for a user and train the system
 * to recognize that user's typing patterns.
 *
 * Note that we display a very different page depending on the contents of the $_POST array.
 */

?>

<!DOCTYPE HTML>
<html>
	<?php
		$title = "Create an Account";
		include( 'components/head.php' );
		include_once( 'components/database_fns.php' );
		include_once( 'components/site_variables.php' );
        include_once( 'components/authentication_wrappers.php' );
	?>
	<body data-spy="scroll" data-target=".subnav" data-offset="50">
		<?php include( 'components/top_menu.php' ); ?>
        <div class="container">
            <!-- Masthead
           ================================================== -->
            <?php include( 'components/branding.php' ); ?>
			<section>
<?php
                $totalStepsInTraining = constant( "NUM_TRAINING_EXAMPLES" ) + 1;

                // If we don't have all the POST data that indicates the user has
                // already typed their info once . . .
                if( !( isset($_POST['timingData']) && isset($_POST['nonce']) ) ) {

                    // If the user is creating an account, we have to first log them out
                    logout();

                    // Display the Create Account page
?>
                    <h2>Create an Account</h2>
                    <div class="progress">
                        <div class="bar" style="width: <?php echo (1/$totalStepsInTraining)*100; ?>%;">1 of <?php echo $totalStepsInTraining; ?></div>
                    </div>
                    <p>Please choose a user name and password with which we can identify your account.</p>
                    <form class="form-horizontal" name="formCreate" id="formCreate" action="<?php echo TRAINING_PAGE; ?>" method="post" autocomplete="off">
                        <div class="control-group">
                            <label class="control-label" for="userName">User name:</label>
                            <div class="controls">
                                <input type="text" id="userName" name="user" placeholder="User name" autocomplete="off">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="password">Password:</label>
                            <div class="controls">
                                <input type="password" id="password" name="pwd" placeholder="************" autocomplete="off">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="passwordRepeat">Repeat password:</label>
                            <div class="controls">
                                <input type="password" id="passwordRepeat" name="pwdRepeat" placeholder="************" autocomplete="off">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="captcha">Captcha:</label>
                            <div class="controls">
                                <?php
                                    $captcha = getCaptcha();
                                ?>
                                <input type="text" id="captcha" name="captcha" placeholder="Type indicated captcha here" autocomplete="off">
                                <span class="help-inline" id="captchaHelp">Type <strong><?php echo $captcha ?></strong> here to prove you are a human.</span>
                            </div>
                        </div>
                        <input type="hidden" id="timingData" name="timingData" />
                        <input type="hidden" id="timingDataCaptcha" name="timingDataCaptcha" />
                        <?php $nonceType = "create" . strval(time()); ?>
                        <input type="hidden" id="nonce" name="nonce" value="<?php echo ulNonce::Create($nonceType); ?>">
						<input type="hidden" name="nonceType" value="<?php echo $nonceType; ?>">
                        <input type="hidden" name="captchaNonce" value="<?php echo ulNonce::Create($captcha); ?>">
                        <input type="hidden" id="iteration" name="iteration" value="2"/>
                        <div class="control-group">
                            <div class="controls">
                                <button type="submit" class="btn btn-primary">Create Account</button>
                            </div>
                        </div>
                    </form>
<?php
                } else { // We have all the required data
                    /*if( isset($_POST['timingDataCaptcha']) ) {
                        echo "<p>Got captcha timing!: " . $_POST['timingDataCaptcha'] . "</p>";
                    }
                    if( isset($_POST['timingData'])) {
                        echo "<p>Got normal timing!: " . $_POST['timingData'] . "</p>";;
                    }*/
                    //echo "<pre>", print_r($_POST, true), "</pre>";

                    // If we haven't already logged the user in, we need to
                    // create the account in the database and log them in
                    if( !userIsLoggedIn()
                        && strlen($_POST['timingDataCaptcha']) > 0
                        && strlen($_POST['timingData']) > 0 ) {
                        $accountCreationSucceeded = createUser( $ulogin, $_POST['user'],  $_POST['pwd'] );

                        if ( !$accountCreationSucceeded ) {
                            // Display a failure message
                            echo '<h2 class="alert-error">Account creation failure.</h2>';

                            if( userExists($_POST['user']) ) {
                                echo "<p>Sorry, the user name <strong>",$_POST['user'],"</strong> is already in use.</p>";
                            } else {
                                echo "<p>Sorry, there was an internal error.</p>";
                            }

                            // @TODO: Not safe to exit here!
                            exit();
                        }
                    }

                    if( userIsLoggedIn() ) {
                        // Validate the captcha
                        if( !captchaIsValid($_POST['captcha']) ) {
                            echo "<p>Captcha text did not match!</p>";
                            return false;
                        }

                        // Set up our variables
                        $userName = $_SESSION['username'];
                        $i = intval(cleanseSQLAndHTML( $_POST['iteration'] ));

                        // Store previous submission's data in the DB
                        //echo "<pre>", print_r($_SESSION), "</pre>";
                        storeTrainingData( $_SESSION['uid'], $_SESSION['username'], $_POST['timingData'] );
                        storeNegativeTrainingData( getUserID($_POST['captcha']), $_SESSION['uid'], $_POST['captcha'],
                                                   $_POST['timingDataCaptcha'] )

?>
                        <h2>Train our system to recognize you</h2>
                        <div class="progress">
                            <div class="bar" style="width: <?php echo ($i/$totalStepsInTraining)*100; ?>%;">Step <?php echo $i; ?> of <?php echo $totalStepsInTraining ?></div>
                        </div>
                            <p>You said your key phrase should be <strong><?php echo $userName;?></strong>.</p>
                            <p>Please type your phrase in the box below.</p>
                            <?php
                            // Set up the form variables
                            $submitTo = $_SERVER['PHP_SELF'];
                            if( $i >= $totalStepsInTraining ) {
                                $submitTo = POST_TRAINING_PAGE;
                            }
                            ?>
                            <form class="form-horizontal" name="formTrain" id="formTrain" action="<?php echo $submitTo; ?>" method="post">
                                <div class="control-group">
                                    <label class="control-label" for="userName">User name:</label>
                                    <div class="controls">
                                        <input type="text" id="userName" name="user" placeholder="<?php echo $_SESSION['username']; ?>">
                                        <span class="help-inline" id="userNameHelp">Retype your user name so we can train our identifier.</span>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="captcha">Captcha:</label>
                                    <div class="controls">
                                        <?php
                                            $captcha = getCaptcha($_SESSION['uid']);
                                        ?>
                                        <input type="text" id="captcha" name="captcha" placeholder="Type indicated captcha here" autocomplete="off">
                                        <span class="help-inline" id="captchaHelp">Type <strong><?php echo $captcha ?></strong> here to prove you are a human.</span>
                                    </div>
                                </div>
                                <input type="hidden" id="timingData" name="timingData" />
                                <input type="hidden" id="timingDataCaptcha" name="timingDataCaptcha" />
                                <?php $nonceType = "train" . strval($i); ?>
                                <input type="hidden" id="nonce" name="nonce" value="<?php echo ulNonce::Create($nonceType);?>">
                                <input type="hidden" name="nonceType" value="<?php echo $nonceType; ?>">
                                <input type="hidden" name="captchaNonce" value="<?php echo ulNonce::Create($captcha); ?>">
                                <input type="hidden" id="iteration" name="iteration" value="<?php echo intval($i + 1); ?>"/>
                                <div class="control-group">
                                    <div class="controls">
                                        <button type="submit" class="btn btn-primary"><?php echo ($i < $totalStepsInTraining ? "Next" : "Finish training"); ?></button>
                                    </div>
                                </div>
                            </form>
<?php
                    } else { // Failed to log the user in; account creation may have failed
                        echo "<p>Error creating account. Please try again.</p>";

                    }
               }
?>

			</section>
            <?php
            /*echo "<p>User is logged in? ", ( userIsLoggedIn() ? "Yes" : "No" ),"</p>",
                 "<p>", "User ID: ", $_SESSION['username'],"</p>"; */?>
		</div><!-- container -->
		<?php include( 'components/footer.php' ); ?>
	</body>
</html>
