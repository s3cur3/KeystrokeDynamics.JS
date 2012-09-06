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
            <?php include( 'components/page_header.php' ); ?>
			<section>
				<h2>Create an Account (Step 1 of 15)</h2>
					<p>Please choose a key phrase with which we can identify your account.</p>
					<form class="form-horizontal" name="formCreate" id="formCreate" action="trainer.php" method="post">
						<div class="control-group">
							<label class="control-label" for="inputKeyPhrase">Key phrase:</label>
							<div class="controls">
								<input type="text" id="inputKeyPhrase" name="inputKeyPhrase" placeholder="Lorem ipsum">
								<span class="help-inline" id="inputKeyPhraseHelp">The phrase associated with your account.</span>
							</div>
						</div>
						<input type="hidden" id="timingData" name="timingData" />
						<input type="hidden" id="iteration" name="iteration" value="2"/>
						<div class="control-group">
							<div class="controls">
								<button type="submit" class="btn btn-primary">Submit</button>
							</div>
						</div>
					</form>
			</section>
		</div><!-- container -->
		<?php include( 'components/footer.php' ); ?>
	</body>
</html>
