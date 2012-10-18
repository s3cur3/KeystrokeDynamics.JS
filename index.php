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
				<?php include( 'components/login.php' ); ?>
			</section>
			<section>
				<p>Don't have an account yet? <a href="create_account.php">Create one now</a>!</p>
			</section>
			
			<section>
				<h2>Log</h2>
				<p id="theLog">
					<pre></pre>
				</p>
			</section>
		</div><!-- container -->
		<?php include( 'components/footer.php' ); ?>
	</body>
</html>
