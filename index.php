<!DOCTYPE HTML>
<html>
	<?php
		$title = "Web Authentication via Keystroke Dynamics";
		include( 'components/head.php' );
	?>
	<body data-spy="scroll" data-target=".subnav" data-offset="50">
		<?php include( 'components/top_menu.php' ); ?>
        <div class="container">
            <!-- Masthead
           ================================================== -->
            <?php include( 'components/branding.php' ); ?>
			
			<section>
				<h2>Log in</h2>
				<form class="form-horizontal" name="formLogin" id="formLogin" action="validator.php" method="post">
					<div class="control-group">
						<label class="control-label" for="inputKeyPhrase">Key phrase:</label>
						<div class="controls">
							<input type="text" id="inputKeyPhrase" name="inputKeyPhrase" placeholder="Lorem ipsum">
							<span class="help-inline" id="inputKeyPhraseHelp">The phrase associated with your account.</span>
						</div>
					</div>
                    <input type="hidden" id="timingData" name="timingData" />
					<div class="control-group">
						<div class="controls">
							<button type="submit" class="btn btn-primary">Sign in</button>
						</div>
					</div>
				</form>
			</section>
			
			<section>
				<h2>Log</h2>
				<p id="theLog">
					<pre><?php 
						//exec("/usr/bin/Rscript examples_from_the_literature/evaluation-script_from_killourhy_and_maxion.R" . ' 2>&1', $out, $return_status);
						include('out.txt' );
						//print_r( $out );
					?>
					</pre>
				</p>
			</section>
		</div><!-- container -->
		<?php include( 'components/footer.php' ); ?>
	</body>
</html>
