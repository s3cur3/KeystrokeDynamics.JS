<?php
	include_once( '/components/site_variables.php' );
	global $authenticationPage;
?>
<form class="form-horizontal" name="formLogin" id="formLogin" action="<?php echo $authenticationPage; ?>" method="post">
	<div class="control-group">
		<label class="control-label" for="inputKeyPhrase">Key phrase:</label>
		<div class="controls">
			<input type="text" id="inputKeyPhrase" name="inputKeyPhrase" placeholder="Lorem ipsum" autocomplete="off">
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