<?php
	include_once( 'site_variables.php' );
?>
<li class="dropdown login-dropdown">
	<a class="dropdown-toggle" id="login-dropdown-button" role="button" data-toggle="dropdown" data-target="#" href="#">
		Log in
	<b class="caret"></b>
	</a>
	<form class="dropdown-menu" name="formLoginDropdown" id="formLoginDropdown" action="<?php echo $authenticationPage; ?>" method="post" aria-labelledby="login-label">
		<div class="control-group">
			<label class="control-label" for="inputKeyPhraseDropdown">Key phrase:</label>
			<div class="controls">
				<input type="text" id="inputKeyPhraseDropdown" name="inputKeyPhraseDropdown" placeholder="Lorem ipsum" autocomplete="off">
			</div>
		</div>
		<input type="hidden" id="timingDataDropdown" name="timingDataDropdown" />
		<div class="control-group">
			<div class="controls">
				<button type="submit" class="btn btn-primary">Sign in</button>
			</div>
		</div>
	</form>
</li>