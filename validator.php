<?php include( 'components/database_fns.php' ); ?>
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
            <?php include( 'components/page_header.php' ); ?>
			
			<section>
				<h2>Input Validation</h2>
				<h3>Here's the information we got.</h3>
				<?php
					$phrase = cleanse_sql_and_html($_POST['inputKeyPhrase']);
				
					// Reconstruct the serialized data
					if(isset($_POST['timingData'])) {
						echo "<p>Great! You sent timing data!</p>";
						echo "<p>The phrase you entered was <code>" 
							. $phrase . "</code></p>";
						
						
						
						
						// Split the POSTed data on spaces (to get individual keystrokes)
						$timingData = parseRawTimingData( $_POST['timingData'] );
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
