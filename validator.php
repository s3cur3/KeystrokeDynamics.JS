<!DOCTYPE HTML>
<html>
	<?php
		$title = "Validation Page";
		include( 'components/head.php' );
		
		include( 'components/sanitizer.php' );
	?>
	
	<body data-spy="scroll" data-target=".subnav" data-offset="50">
		<?php include( 'components/top_menu.php' ); ?>
		
        <div class="container">
            <!-- Masthead
           ================================================== -->
            <header class="jumbotron subhead" id="overview">
                <h1>Validation page</h1>
                <p class="lead">You submitted a form!</p>
            </header>
			
			<section>
				<h2>Here's the information we got.</h2>
				<?php
					// Reconstruct the serialized data
					if(isset($_POST['timingData'])) {
						echo "<p>Great! You sent timing data!</p>";
						// note the HTML injection hole here!
						echo "<p>The phrase you entered was <code>" 
							. cleanse_sql_and_html($_POST['inputKeyPhrase']) . "</code></p>";
						
						
						
						// Split the POSTed data on spaces (to get individual keystrokes)
						$rawTimingData = explode( " ", $_POST['timingData'] );
						$timingData = array();
						foreach( $rawTimingData as $keystroke ) {
							// The Javascript sends the data as:
							//    [key code],[time down],[time up]
							// (With each keypress triplet separated by a space)
							$keyCode_Down_Up = explode( ",", $keystroke );
							
							$currentKey['keyCode'] = $keyCode_Down_Up[0];
							$currentKey['timeDown'] = $keyCode_Down_Up[1];
							$currentKey['timeUp'] = $keyCode_Down_Up[2];
							
							$currentKey['timeHeld'] = $currentKey['timeUp'] - $currentKey['timeDown'];
							$currentKey['character'] = chr($currentKey['keyCode']);
							
							// Make non-printing characters readable
							if( $currentKey['keyCode'] == 16 ) {
								$currentKey['character'] = "SHIFT";
							} else if( $currentKey['keyCode'] == 13 ) {
								$currentKey['character'] = "ENTER";
							} else if( $currentKey['keyCode'] == 32 ) {
								$currentKey['character'] = "SPACE";
							}
							
							
							$timingData[] = $currentKey; // push to the end of the array
						}
						
						
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
	</body>
</html>
