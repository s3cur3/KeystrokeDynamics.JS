Introduction
===============

This is KeystrokeDynamics.JS, a tool for analyzing keystroke dynamics in Web applications.

To see the application in action, check out [our website](http://www.tylerayoung.com/keystroke/).

[Keystroke dynamics](http://en.wikipedia.org/wiki/Keystroke_dynamics) is a means of authenticating users based on information about the way they type. It can be used as a (behavioral) biometric in a multi-factor authentication system, so that even if a person's password becomes compromised, their account will not.

The source code for this project represents a minimal working example of a site that uses a keystroke dynamics-aware login. Accessing this "site" on your local machine (assuming you can execute PHP and access a MySQL database) will allow you to create an account, train the anomoly detector, and authenticate yourself.

To implement a login form that is aware of keystroke dynamics on your own site, you'll need to do the following:

1. Add the directories `js` and `components` to your own site's root directory.
2. In the `components/site_variables.php` file, set up the variables
for database access, login validator page, training page, and post-training page to match your own site's configuration. (See steps 5 and 6 for more on those special pages.)
3. Include `js/submitter.js` on any page from which you want to authenticate users or train your detector. Include JQuery (`js/jquery.js`) as well if you don't have it on your pages already. You can do this by placing the following code into your page (probably in the footer somewhere so you don't slow down page load times):
	- `<script src="js/jquery.js"></script>`
	- `<script src="js/submitter.js"></script>`
4. Include login forms wherever you'd like them to go. You can do this in a number of ways.
    - If you're using [Bootstrap][] (with the Bootstrap Javascript, either `bootstrap.min.js` or `bootstrap.js`), you can add a neat drop-down login form by including `dropdown_login.php` in a menu on your page. (See [Example 1][Example 1: A Dropdown Login Form in a Nav Menu].)
	- To add a "normal" login form to a page, just include
      `components/login.php` somewhere on your page. (See [Example 2][Example 2: A Standard Login Form].)
5. Create a login validation page. This is the page that users are sent to after they log in. This might be a user's "control panel," your normal home page, or any number of other things. What's important is that this page call the `is_real_user()` function, as illustrated in [Example 3][Example 3: A Validation Page].
6. Create a training page and a training success page. **TODO**: Document this further.
	
**Note**: For this code to be a true drop-in solution, you'll need to use it with Twitter [Bootstrap][]. For the most part, you don't *have* to use Bootstrap, but if you don't, you'll have to provide your own CSS styling and maybe your own Javascript animations.

[Bootstrap]: http://twitter.github.com/bootstrap/
	
## Example 1: A Dropdown Login Form in a Nav Menu ##
What follows is an example of a Bootstrap navigation bar which includes the drop-down login button as the final button in the bar. Note that in order for this to work, you must be using the Bootstrap Javascript on your page, either `bootstrap.min.js` or `bootstrap.js`.

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
							<a href="http://www.other.site">Example Link 1</a>
						</li>
						<li class="">
							<a href="www.other.site/page">Example Link 2</a>
						</li>
	<!-- The magic -->  <?php include( 'dropdown_login.php' ); ?>
					</ul>
				</div>				
			</div>
		</div>
	</div>


## Example 2: A Standard Login Form ##
What follows is an example of a standard (i.e., non-dropdown) login form. If you're using the [Bootstrap][] CSS and Javascript, this will be nicely styled.

	<section>
		<h2>Log in</h2>
		<?php include( 'components/login.php' ); ?>
	</section>

## Example 3: A Validation Page ##
The following is an example of a validation page. This is the page that users get sent to after they input their login credentials. Note that we have a placeholder call to `your_normal_validation_function()`, since our package doesn't necessarily check the username and password against your master password database.

    <body>		
		<div class="container">
			<section>
				<h1>User Home Page</h1>
	<?php
				include_once( '/components/login_validator.php' );
				if( is_real_user() && your_normal_validation_function() ) {
	?>              
                    <p>Hello, user!</p>
	<?php		
	            } else { // Identified as impostor!
	?>          
		            <p>Failed to authenticate you.</p>
					<p>Perhaps you didn't type your credentials in the way you normally do.</p>
	<?php		
	            }
	?>					

Compatibility notes
======================
Our Javascript uses the `Function.prototype.bind()` function, new in
ECMAScript 5, which is supported in:
- IE9 and greater
- Firefox 4 and greater
- Safari 6 and greater
- Chrome 7 and greater
- Opera 12 and greater
