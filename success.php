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
			<?php $phrase = cleanse_sql_and_html( $_POST['inputKeyPhrase'] ); ?>
			
			<section>
				<h2>Account Creation Successful!</h2>
				<p>Successfully created your account using key phrase <strong><?php echo $phrase;?></strong>.</p>
			</section>
			
			<section>
				<h2>Log</h2>
				<p id="theLog">
				</p>
			</section>
		</div><!-- container -->
		<?php include( 'components/footer.php' ); ?>
	</body>
</html>
