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
                attemptLogin();


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
				echo "<p>Checking data . . . </p>";
				
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
					<p>Likelihood that you are a haxx0r:
						<button  rel="popover" id="theScore" class="<?php echo $msgClass; ?>" 
							data-title="<?php echo $decision; ?>" 
							data-content="<?php echo $explanation; ?>" 
							data-trigger="hover">
								<?php echo $probThatUserIsReal; ?>
						</button>
					</p>
					<p>Absolute score: <?php echo $absoluteScore ?></p>
					
					<table width="80%" border="1" cellpadding="5px">
						<tr>
							<th scope="col">Character</th>
							<th scope="col">Key Code</th>
							<th scope="col">Time Down</th>
							<th scope="col">Time Up</th>
							<th scope="col">Time Held</th>
						</tr>
<?php
							$phrase = cleanseSQLAndHTML($_POST['inputKeyPhrase']);
							$phraseDropdown = cleanseSQLAndHTML($_POST['inputKeyPhraseDropdown']);
							$idSuffix = '';
							if( strlen($phrase) == 0 && strlen($phraseDropdown) > 0 ) {
								$idSuffix = 'Dropdown';
								$phrase = $phraseDropdown;
							}
							$timingData = parseRawTimingData($_POST['timingData'.$idSuffix]);
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
				} else { // error occurred!
					echo "<p>There was an error processing your login data. You may have gotten to this page in error. Perhaps you need to <a href=\"create_account.php\">create an account</a>?</p>";
					
					echo "<p>Timing data: ";
					print_r($_POST['timingData']);
					echo "</p><p>Timing data dropdown: ";
					print_r($_POST['timingDataDropdown']);
					echo "</p><p>Suffix $idSuffix </p>";
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
