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
            <header class="jumbotron subhead" id="overview">
                <h1>KeystrokeDynamics.JS</h1>
                <p class="lead">Nothing to see here yet.</p>
            </header>
			
			<section>
				<h2>Log in</h2>
				<form class="form-horizontal" name="formLogin" id="formLogin" action="validator.php" method="post">
					<div class="control-group">
						<label class="control-label" for="inputKeyPhrase">Key phrase:</label>
						<div class="controls">
							<input type="text" id="inputKeyPhrase" name="inputKeyPhrase" placeholder="Lorem ipsum">
							<span class="help-inline" id="keyPhraseHelp">The phrase associated with your account.</span>
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
				<p id="theLog"> </p>
			</section>
		</div><!-- container -->
		<?php include( 'components/footer.php' ); ?>
	</body>
</html>
