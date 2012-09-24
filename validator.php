<!DOCTYPE HTML>
<html>
	<?php
		$title = "Validation Page";
		include( 'components/head.php' );
		include_once( 'components/keystroke_data_handlers.php' );
	?>
	
	<body data-spy="scroll" data-target=".subnav" data-offset="50">
		<?php include( 'components/top_menu.php' ); ?>
		
        <div class="container">
            <!-- Masthead
           ================================================== -->
            <?php include( 'components/branding.php' ); ?>
			
			<section>
				<h2>Input Validation</h2>
				<h3>Here's the information we got.</h3>
<?php
					$phrase = cleanse_sql_and_html($_POST['inputKeyPhrase']);
				
					// Reconstruct the serialized data
					if( isset($_POST['timingData']) ) {
						echo "<p>Great! You sent timing data!</p>";
						echo "<p>The phrase you entered was <code>" 
							. $phrase . "</code></p>";
						
						// Split the POSTed data on spaces (to get individual keystrokes)
						$timingData = parseRawTimingData( $_POST['timingData'] );
				
						// TODO: Die if the key phrase doesn't match any known
						
						
						// Write the detection model to a file for use by the authenticator.R script
						$serializedDetectionModel = getDetectionModel( $phrase );
						$serializedDetectionModel .= "\n";
						writeStringToFileForR( $serializedDetectionModel, "dmod" );
						
						// Write the timing data for this attempted password entry to a file 
						$thisAttemptCSV = getCSVHeader( $timingData, false /* no repetition column */ );
						$thisAttemptCSV .= getCSVLineFromTimingData( $timingData );
						writeStringToFileForR( $thisAttemptCSV, "current_attempt.csv" );
						
						
						// Call the R script for validation
						exec("/usr/bin/Rscript r/authenticator.R " . '2>&1', $out, $returnStatus);
						
						if( $returnStatus === 0 ) {
						
							$score = floatval(end($out));
							
							// Evaluate whether you're an impersonator or not
							$msgClass = "btn";
							$decision = "You're probably not an impersonator.";
							$explanation = "We're not really sure what to think of you. We'll let you pass, though.";
							if( $score > 0.5 ) {
								$msgClass .= " btn-danger";
								$decision = "You're an impersonator.";
								$explanation = "The chances are very low that you are who you say you are.";
							} else if( $score < 0.3 ) {
								$msgClass .= " btn-success";
								$decision = "You're definitely not an impersonator.";
								$explanation = "The chances are very good that you are who you say you are.";
							}
	?>					
							<p>Likelihood that you are a haxx0r:
								<button  rel="popover" id="theScore" class="<?php echo $msgClass; ?>" data-title="<?php echo $decision; ?>" data-content="<?php echo $explanation; ?>" data-trigger="hover"><?php echo $score; ?></button>
							</p>
<?php
						} else { // error in the R function
?>
							<p>There was an error processing your login data. Perhaps you need to <a href="create_account.php">create an account</a>?</p>
<?php
						}
						
						echo "<h2>Log</h2>";
						
						echo "<pre>Detection model: " //. getDetectionModel($phrase)
							. "\nThis attempt: $thisAttemptCSV"
							. "\nReturn status: $returnStatus"
							. "\nData from output: ". print_r($out, true) . "</pre>";
							
?>
						<table width="80%" border="1" cellpadding="5px">
							<tr>
								<th scope="col">Character</th>
								<th scope="col">Key Code</th>
								<th scope="col">Time Down</th>
								<th scope="col">Time Up</th>
								<th scope="col">Time Held</th>
							</tr>
<?php
								foreach( $timingData as $key ) {
?>
									<tr>
										<td><?php echo  $key['character']; ?></td>
										<td><?php echo  $key['keyCode']; ?></td>
										<td><?php echo  $key['timeDown']; ?></td>
										<td><?php echo  $key['timeUp']; ?></td>
										<td><?php echo  $key['timeHeld']; ?></td>
									</tr>
<?php
								}
?>
						</table>
<?php
					} else {
						echo "<p>No timing data...</p>";
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
