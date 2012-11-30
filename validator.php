<!DOCTYPE HTML>
<html>
	<?php
		$title = "Validation Page";
		include( 'components/head.php' );

		include_once( 'components/keystroke_data_handlers.php' );
        include_once( 'components/authentication_wrappers.php' );
	?>
	
	<body data-spy="scroll" data-target=".subnav" data-offset="50">
		<?php include( 'components/top_menu.php' ); ?>
		
		<div class="container">
			<!-- Masthead
		   ================================================== -->
			<?php include( 'components/branding.php' ); ?>
			
			<section>
				<h2>Input Validation</h2>
<?php
                attemptLogin(true);

                // Check that the user has enough training examples
                $knownTrainingExamples = getNumTrainingExamples($_SESSION['uid']);
                $knownTrainingExamples = getNumTrainingExamples($_SESSION['uid']);
                if( $knownTrainingExamples >= NUM_TRAINING_EXAMPLES ) {
                    if( userIsLoggedIn() ) {
                        echo "<p>Successfully authenticated you!</p>";
                    } else {
                        echo "<p>Failed to authenticate you.</p>";
                    }

                    $msgClass = "btn";
                    $decision = "You're probably not an impersonator.";
                    $explanation = "The chances are good that you are who you say you are.";
                    $errorOccurred = false;
                    $probThatUserIsReal = 0.0;
                    $absoluteScore = 1.0;
                    echo "<p>Checking data for user &ldquo;<b>" . $_POST['user'] . "</b>&rdquo;. . . </p>";

                    // Authenticate the user
                    if( keystrokeDataMatchesUser( $errorOccurred, $probThatUserIsReal, $absoluteScore ) ) {
                        $msgClass .= " btn-success";
                    } else { // Identified as impostor!
                        $msgClass .= " btn-danger";
                        $decision = "You're an impersonator.";
                        $explanation = "The chances are very low that you are who you say you are.";
                    }

                    // Output the probability the person is an impostor
                    if( !$errorOccurred ) {
?>
                        <p>Likelihood that you are an impostor:
                            <button  rel="popover" id="theScore" class="<?php echo $msgClass; ?>"
                                     data-title="<?php echo $decision; ?>"
                                     data-content="<?php echo $explanation; ?>"
                                     data-trigger="hover">
                                <?php echo $probThatUserIsReal; ?>
                            </button>
                        </p>
                        <!--<p>Absolute score: <?php //echo $absoluteScore ?></p>-->
                        <h3>Your super-secret data</h3>
                            <p>Since you're logged in, you get to see your super-secret code. It's &ldquo;<b>Lorem ipsum dolar sit amet</b>&rdquo;.</p>
<?php
                        //displayKeystrokeDataFromPOST();
                    } else { // error occurred!
                        echo "<p>There was an error processing your login data. You may have gotten to this page in error. Perhaps you need to <a href=\"create_account.php\">create an account</a>?</p>";

                        echo "<p>Timing data: ";
                        print_r($_POST['timingData']);
                        echo "</p><p>Timing data dropdown: ";
                        print_r($_POST['timingDataDropdown']);
                        echo "</p><p>Suffix $idSuffix </p>";
                    }
                } else {
?>
                    <p>It looks like we don't have enough training data for this account. Please <a href="create_account.php?i=<?php echo $knownTrainingExamples; ?>">finish training our system</a> with your typing patterns.</p>
<?php
                }



?>
			</section>
		</div><!-- container -->
		<?php include( 'components/footer.php' ); ?>
		<script>
			$(function ()  { 
				$("#theScore").popover();  
			});
		</script>
	</body>
</html>
