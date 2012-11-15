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
                <h2>Please log in</h2>
				<?php
					include_once( 'components/login_form.php' );
					printLoginForm();
				?>
			</section>
			<section>
				<p>Don't have an account yet? <a href="create_account.php">Create one now</a>!</p>
			</section>
		</div><!-- container -->
		<?php include( 'components/footer.php' ); ?>
	</body>
</html>
