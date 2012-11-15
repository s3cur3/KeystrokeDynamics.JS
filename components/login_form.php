<?php
require_once( 'site_variables.php' );

final class LoginFormType
{
    const Standard = 0;
    const DropdownLong = 1;
    const DropdownShort = 2;
}

/**
 * A function to spit out a login form wherever you called it on your page.
 *
 * @param $containerType The type of HTML element surrounding the form. If this is the
 *                      empty string, you will just get the raw form. If it is, e.g.,
 *                      'div', the form will be surrounded by <div> tags.
 *                      Note that in the dropdown form, no container is not an option;
 *                      instead, we default to a <li> container (thus assuming the 
 *                      dropdown will be a part of some menu you're creating.
 * @param typeOfLogin The type of login form you want.
 */
function printLoginForm( $containerType='', $typeOfLogin=LoginFormType::Standard ) {
	$suffix = '';
    $formClass = "form-horizontal";
	$nonceType = "login";
	if( $typeOfLogin===LoginFormType::DropdownLong || $typeOfLogin===LoginFormType::DropdownShort ) {
		$suffix = 'Dropdown';
        $formClass = "dropdown-menu";
		$nonceType .= "Dropdown";
		if( $containerType==='' ) {
			$containerType = 'li';
		}
?>
		<<?php echo $containerType; ?>  class="dropdown login-dropdown">
			<a class="dropdown-toggle" id="login-dropdown-button" role="button" data-toggle="dropdown" data-target="#" href="#">
				Log in
				<b class="caret"></b>
			</a>
<?php
	}
?>
    <form class="<?php echo $formClass; ?>" name="formLogin<?php echo $suffix; ?>" id="formLogin<?php echo $suffix; ?>" action="<?php echo AUTHENTICATION_PAGE; ?>" method="post">
<?php
        if( $typeOfLogin!==LoginFormType::DropdownShort ) { // The short dropdown form is different from all others
?>
            <div class="control-group">
                <label class="control-label" for="userName<?php echo $suffix; ?>">User name:</label>
                <div class="controls">
                    <input type="text" id="userName<?php echo $suffix; ?>" name="user" placeholder="User name" autocomplete="off">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="password<?php echo $suffix; ?>">Password:</label>
                <div class="controls">
                    <input type="password" id="password<?php echo $suffix; ?>" name="pwd" placeholder="************" autocomplete="off">
                </div>
            </div>
<?php
        } else { // The short dropdown login gets treated differently
?>
            <input type="text" class="input-block-level" id="userName<?php echo $suffix; ?>" name="user" placeholder="User name" autocomplete="off">
            <input type="password" class="input-block-level" placeholder="Password" id="password<?php echo $suffix; ?>" name="pwd" autocomplete="off">
<?php
        }
?>
        <input type="hidden" id="timingData<?php echo $suffix; ?>" name="timingData" />
        <input type="hidden" id="nonce<?php echo $suffix; ?>" name="nonce" value="<?php echo ulNonce::Create($nonceType);?>">
		<input type="hidden" name="nonceType" value="<?php echo $nonceType; ?>">
        <div class="control-group">
            <div class="controls">
                <button type="submit" class="btn btn-primary">Sign in</button>
            </div>
        </div>
    </form>
<?php
    if( $typeOfLogin===LoginFormType::DropdownLong || $typeOfLogin===LoginFormType::DropdownShort ) {
        echo "</$containerType>";
    }
}
?>
