<div class="navbar navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container">
			<button type="button"class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="brand" href="index.php">Home</a>
			<div class="nav-collapse collapse">
				<ul class="nav">
					<li class="">
						<a href="create_account.php">Create Account</a>
					</li>
					<li class="">
						<a href="http://github.com/s3cur3/KeystrokeDynamics.JS" target="_blank">GitHub</a>
					</li>
					<li class="">
						<a href="https://github.com/s3cur3/KeystrokeDynamics.JS#readme" target="_blank">Readme</a>
					</li>
					<li class="">
						<a href="https://github.com/s3cur3/KeystrokeDynamics.JS/wiki" target="_blank">Wiki</a>
					</li>
					<li class="dropdown login-dropdown">
					  <a class="dropdown-toggle" id="login-label" role="button" data-toggle="dropdown" data-target="#" href="#">
						Log in
						<b class="caret"></b>
					  </a>
					  <form class="dropdown-menu" name="formLoginDropdown" id="formLoginDropdown" action="validator.php" method="post" aria-labelledby="login-label">
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
				</ul>
			</div>
			
		</div>
	</div>
</div>
