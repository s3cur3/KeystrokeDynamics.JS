<!DOCTYPE HTML>
<html>
	<?php
		$title = "Finished Training the Identity Verification Component";
		include( 'components/head.php' );
		include_once( 'components/database_fns.php' );
		include_once( 'components/keystroke_data_handlers.php' );
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
				<h2>Account Creation Successful!</h2>
				<div class="progress">
					<?php $totalStepsInTraining = 15; ?>
					<div class="bar" style="width: 100%;">Step <?php echo $totalStepsInTraining ?> of <?php echo $totalStepsInTraining ?></div>
				</div>
				<p>Training the system with your data . . .</p>
				<?php
					// Get all known data for this user's key phrase
					$rawTrainingData = getTrainingData( $phrase );
					
					// Format the user's data for the R script
					$formattedTrainingData = prepareTrainingData( $rawTrainingData );
					
					// Write the training data to a CSV file for the R script
					writeStringToFileForR( $formattedTrainingData, "training_data.csv" );
					
					
					// Call the R script for training
					exec("/usr/bin/Rscript r/trainer.R" . ' 2>&1', $out, $return_status);
					
					// Parse the output
					$startingKey = array_search( "[1] \"Serializing detection model\"", $out );
					$serializedData = "";
					for( $i = $startingKey + 1; $i < sizeof($out); $i++ ) {
						// Remove the damn line number (like "[1234]") from R's output
						$serializedData .= preg_replace( "/\[[0-9]+\] /", "", $out[$i] );
					}
					
					// Compress spaces in the serialized data
					$serializedData = preg_replace( '/\s+/', ' ', $serializedData );
					// Remove quotes as necessary
					$serializedData = preg_replace( '/"/', '', $serializedData );
					
					
					/*echo( "<pre>Started at array index $startingKey \n Detection model: " . $serializedData 
						. "\nAll out: " . print_r($out, true)
						. "</pre>" );*/
					
					// Store the output from the script (a vector with the trained classification)
					storeDetectionModel( $phrase, $serializedData );
				?>
				<p>Successfully created your account using key phrase <strong><?php echo $phrase;?></strong>.</p>
			</section>
			
			<section>
				<h2>Log</h2>
				<p id="theLog">
				<pre>Training data: <?php echo $formattedTrainingData; ?>
				</p>
			</section>
		</div><!-- container -->
		<?php include( 'components/footer.php' ); ?>
	</body>
</html>
