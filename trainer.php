<!DOCTYPE HTML>
<html>
	<?php
		$title = "Web Authentication via Keystroke Dynamics";
		include( 'components/head.php' );
		include_once( 'components/database_fns.php' );
	?>
	<body data-spy="scroll" data-target=".subnav" data-offset="50">
		<?php include( 'components/top_menu.php' ); ?>
        <div class="container">
            <!-- Masthead
           ================================================== -->
            <?php include( 'components/branding.php' ); ?>
			<?php
				// Set up our variables
				$phrase = cleanse_sql_and_html( $_POST['inputKeyPhrase'] );
				$i = intval(cleanse_sql_and_html( $_POST['iteration'] ));
				$totalStepsInTraining = 15;
				
				// Store previous submission's data in the DB
				insert_training_data_into_table( $phrase, $_POST['timingData'] );
			?>
			<section>
				<h2>Create an Account</h2>
				<div class="progress">
					<div class="bar" style="width: <?php echo ($i/$totalStepsInTraining)*100; ?>%;">Step <?php echo $i; ?> of <?php echo $totalStepsInTraining ?></div>
				</div>
<?php
					// If the key phrase is not set, the user has not yet chosen a key phrase
					if( isset($_POST['inputKeyPhrase']) ) {
?>
						<p>You said your key phrase should be <strong><?php echo $phrase;?></strong>.</p>
						<p>Please type your phrase in the box below.</p>
						<?php
							// Set up the form variables
							$id = "inputKeyPhrase";
							$submitTo = $_SERVER['PHP_SELF'];
							if( $i >= 14 ) {
								$submitTo = "success.php";
							}
						?>
						<form class="form-horizontal" name="formTrain" id="formTrain" action="<?php echo $submitTo; ?>" method="post">
							<div class="control-group">
								<label class="control-label" for="<?php echo $id ?>">Key phrase:</label>
								<div class="controls">
									<input type="text" id="<?php echo $id ?>" name="<?php echo $id ?>" placeholder="<?php echo $phrase ?>">
									<span class="help-inline" id="<?php echo $id."Help" ?>">Retype your key phrase so we can train our identifier.</span>
								</div>
							</div>
							<input type="hidden" id="timingData" name="timingData" />
							<input type="hidden" id="iteration" name="iteration" value="<?php echo intval($i + 1); ?>"/>
							<div class="control-group">
								<div class="controls">
									<button type="submit" class="btn btn-primary"><?php echo ($i < $totalStepsInTraining - 1 ? "Next" : "Finish training"); ?></button>
								</div>
							</div>
						</form>
<?php
					} else { // No key phrase chosen yet
?>					
						<p>It looks like you got here by mistake. Perhaps you wanted to <a href="create_account.php">create an account</a>?</p>
<?php
					}
?>
				
			</section>
			
			<section>
				<h2>Log</h2>
				<p id="theLog">
				<pre><?php
					// If the key phrase is set, the user has chosen a key phrase
					if( isset($_POST['inputKeyPhrase']) ) {
						echo $_POST['inputKeyPhrase'];
					}
					echo "\nTiming data: " . $_POST['timingData'];
				?></pre>
				</p>
				<?php //dump_training_data(); ?>
			</section>
		</div><!-- container -->
		<?php include( 'components/footer.php' ); ?>
	</body>
</html>
