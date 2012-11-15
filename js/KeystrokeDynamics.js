// Data structure (treated as a list) that will store a
// series of Keystrokes
var keyLogs = [];

/**
 * Types of forms that we may work with
 */
var InputType = {
    CREATE : 'formCreate',
    LOGIN : 'formLogin',
    TRAIN : 'formTrain',
    DROPDOWN: 'formLoginDropdown',
    CAPTCHA: 'captcha'
};

/**
 * Defines the Keystroke prototype
 */
function Keystroke(keyCode, timeDown, timeUp) {
    this.keyCode = keyCode;
    this.timeDown = timeDown;
    this.timeUp = timeUp;
}
function keystrokeToString() {
    return String.fromCharCode(this.keyCode) /
        + " (key code " + this.keyCode /
        + ") pressed down at " + this.timeDown /
        + " and let up at " + this.timeUp + "!";
}
// Serializes a keystroke to [key code],[time down],[time up]
function keystrokeSerialize() {
    return this.keyCode + "," + this.timeDown + "," + this.timeUp;
}
Keystroke.prototype.toString = keystrokeToString;
Keystroke.prototype.serialize = keystrokeSerialize;


/**
 * Clears the form and displays a pop-up notifying the user
 * that they made a typo
 * @param textField The jQuery object for the input field. Probably
 *                  created by a call like: $(textFieldID)
 */
function resetAndComplainAfterTypo(textField) {
    resetFormAndTimingData(textField);

    // Insert an alert
    console.log("Hiding normal help text and displaying error");
    var textFieldID = textField.attr('id');
    $( "#" + textFieldID + "Help" ).hide();
    var spanWidth = (textFieldID.indexOf("Dropdown") !== -1 ? 3 : 6 );
    if( $('.alert').length === 0 ) {
        textField.parent().parent().before(
            '<div class="alert alert-block alert-error fade in span' + spanWidth + ' pull-right">'
                + '<button type="button" class="close" data-dismiss="alert" data-close="bindHelpPopup(\'#' + textFieldID + '\')">×</button>'
                + '<h4 class="alert-heading">It looks like you made a typo</h4>'
                + '<p>We\'ve reset the form so that you can type your password from the beginning.</p>'
                + '<p>'
                + '<a class="btn" href="#" onclick="$(\'.alert\').alert(\'close\'); bindHelpPopup()">OK</a>'
                + '</p>'
                + '</div>');
    }
}


/**
 * Uses the jQuery's keydown() and keyup() functions to monitor
 * keystrokes in the "key phrase" text box. Fills the keyLog data
 * structure with this information.
 * @param textField The jQuery object for the input field. Probably
 *                  created by a call like: $(textFieldID)
 */
function monitor( textField, inputType ) {
    console.log( "Monitoring text field with ID " + textField.attr('id') );
    textField.keydown(function(event) {
        console.log("Key pressed.");
        var eventNeedsRecording = true;
        var i = keyLogs[inputType].length;

        if( event.keyCode == 8 ) { // backspace
            resetAndComplainAfterTypo(textField);
            eventNeedsRecording = false;
        } else if( event.keyCode == 13 || event.keyCode == 9 ) { // Ignore enters and tabs
            eventNeedsRecording = false;
        } else if( i > 0 ) { // If the keylog isn't empty
            // if this isn't identical to the previous
            if( keyLogs[inputType][i - 1].timeDown === event.timeStamp
                && keyLogs[inputType][i - 1].keyCode === event.keyCode ) {
                eventNeedsRecording = false;
            }
        }

        if( eventNeedsRecording ) {
            keyLogs[inputType][i] = new Keystroke( event.keyCode, event.timeStamp, 0 );
        }
    });

    textField.keyup(function (event) {
        // Determine the last instance of this key that was pressed down
        var i; // assume it's the last character
        for( i = keyLogs[inputType].length - 1; i >= 0; i-- ) {
            if( keyLogs[inputType][i].keyCode == event.keyCode && keyLogs[inputType][i].timeUp == 0 ) {
                keyLogs[inputType][i].timeUp = event.timeStamp;
                break;
            }
        }
    });
}


/**
 * Removes the keystroke listener from the DOM object indicated
 * @param textField The jQuery object for the input field. Probably
 *                  created by a call like: $(textFieldID)
 */
function unMonitor( textField ) {
    console.log( "Unmonitoring text field with ID " + textField.attr('id') );
    textField.unbind('keyup');
    textField.unbind('keydown');
}


/**
 * Handles the functionality of the help popup (assuming one exists).
 * Hides the help initially, then fades it in and out as the textField
 * is selected and deselected.
 *
 * Note that this help popup must have an ID which is identical to your
 * text field plus a suffix "Help". Thus, if your text field has ID
 * "#passwordField", your help text should have ID "#passwordFieldHelp".
 *
 * @param textField The jQuery object for the input field. Probably
 *                  created by a call like: $(textFieldID)
 */
function bindHelpPopup( textField ) {
    if( textField === undefined ) {
        textField = $("#userName"); // For the "complain after typo" modal, which doesn't seem to want to pass parms
    }
    var textFieldHelp = $( textField.attr('id') + "Help");
    textFieldHelp.fadeOut('slow');
    textField.focus(function () {
        textFieldHelp.fadeIn();
    });
    textField.blur(function () {
        textFieldHelp.fadeOut();
    });
}

/**
 * Binds the keystroke listener to an input field.
 * @param inputIDToMonitor An input ID, such as "#passwordField",
 *                         that we should monitor for keystroke dynamics.
 */
function bindKeystrokeListener( inputIDToMonitor ) {
    var inputField = $(inputIDToMonitor);

    if( !(inputIDToMonitor in keyLogs) ) {
        keyLogs[inputIDToMonitor] = [];
    }

    // Clear the key phrase, so that if you reloaded
    // this page, it's not populated with the old data
    inputField.val('');

    inputField.focus(monitor.bind(this, inputField, inputIDToMonitor));
    inputField.blur(unMonitor.bind(this, inputField));

    bindHelpPopup(inputField);
}

/**
 * Clears the specified input field, and focuses the input there.
 * Also resets all our timing data on that field.
 * @param inputField The input field to clear
 */
function resetFormAndTimingData( inputField ) {
    console.log("Resetting form and timing data.");

    inputField.val('');
    inputField.focus();
    inputField.unbind('blur');

    // Reset the keyLog and the counter
    key = "#" + inputField.attr('id');
    keyLogs[key].length = 0;
    keyLogs[key] = [];
}


/**
 * Serializes the complete key log
 * @return A string version of the full keyLog
 */
function getSerializedTimingData( inputType ) {
    var s = "";
    var key = "#" + getTextField(inputType).attr('id');
    console.log("Serializing keylog for " + key + ".");
    for (var i = 0; i < keyLogs[key].length; i++) {
        s += keyLogs[key][i].serialize() + " ";
    }
    console.log("Key log for field " + key + " was: " + s);
    return s;
}

/**
 * @param formType a SubmitType enumerated value corresponding
 *                 to the type of form on this page
 * @return the jQuery object corresponding to the text fields
 *         present in the given form type
 */
var privateTextFields = [];
function getTextField( formType ) {
    if( privateTextFields.length === 0 ) {
        privateTextFields[InputType.DROPDOWN] = $("#" + KSD_FIELD_ID + "Dropdown");
        privateTextFields[InputType.LOGIN] = privateTextFields[InputType.TRAIN] = privateTextFields[InputType.CREATE] = $("#" + KSD_FIELD_ID);
        privateTextFields[InputType.CAPTCHA] = $("#captcha");
    }

    return privateTextFields[formType];
}

/**
 * @param formType a SubmitType enumerated value corresponding
 *                 to the type of form on this page
 * @return the jQuery object corresponding to the timing data
 *         field present in the given form type
 */
var privateTimingFields = [];
function getTimingField( formType ) {
    if( privateTimingFields.length === 0 ) {
        privateTimingFields[InputType.DROPDOWN] = $("#timingDataDropdown");
        privateTimingFields[InputType.LOGIN] = privateTimingFields[InputType.TRAIN] = privateTimingFields[InputType.CREATE] = $("#timingData");
        privateTimingFields[InputType.CAPTCHA] = $("#timingDataCaptcha");
    }

    return privateTimingFields[formType];
}


/**
 * When the login form is submitted, this adds the data
 * to the form that is necessary for analysis of
 * keystroke dynamics
 */
function handleSubmission( inputType ) {
    console.log("Setting a submission handler for input type " + inputType);
    var form = $( "#" + inputType );
    var inputField = getTextField(inputType);

    form.submit(function(event) {
        var dataIsOkay = true;

        if( inputType === InputType.TRAIN ) {
            // If the password was wrong, inform the user
            if( inputField.val() !== inputField.attr('placeholder') ) {
                resetAndComplainAfterTypo( inputField );
                dataIsOkay = false;
                return false;
            }
        } else if( inputType === InputType.CREATE ) {
            // Ensure the passwords match
            var passwordField = $("#password");
            var passwordRepeatField = $("#passwordRepeat");
            if( passwordField.val() !== passwordRepeatField.val() ) {
                // Reset the passwords
                passwordField.val('');
                passwordRepeatField.val('');

                // Insert an alert
                console.log("Error: passwords don't match");
                if( $('.alert').length === 0 ) {
                    passwordField.parent().parent().before(
                        '<div class="alert alert-block alert-error fade in pull-right">'
                            + '<button type="button" class="close" data-dismiss="alert" data-close="bindHelpPopup($(#' + inputField.attr('id') + '))">×</button>'
                            + '<h4 class="alert-heading">Passwords do not match</h4>'
                            + '<p>We\'ve reset the form so that you can retype your password.</p>'
                            + '<p>'
                            + '<a class="btn" href="#" onclick="$(\'.alert\').alert(\'close\');">OK</a>'
                            + '</p>'
                            + '</div>');
                }
                return false;
            }
        }

        // Add the invisible field which will allow us to send timing data
        if( dataIsOkay ) {
            // If this is an account creation or account training, set the keystroke data for
            // the captcha (i.e., another user's key phrase)
            if( inputType === InputType.CREATE || inputType === InputType.TRAIN ) {
                getTimingField(InputType.CAPTCHA).val(getSerializedTimingData(InputType.CAPTCHA));
            }

            // Set the key phrase's timing data
            getTimingField(inputType).val(getSerializedTimingData(inputType));
            return true;
        } else {
            event.stopImmediatePropagation();
            return false;
        }
    });
}

function main() {
    // Chrome ignores our demands to disable autocomplete. No problem, we'll force it.
    if( navigator.userAgent.toLowerCase().indexOf('chrome') >= 0 ) {
        setTimeout(function () {
            document.getElementById('userName').autocomplete = 'off';
            document.getElementById('userNameDropdown').autocomplete = 'off';
            document.getElementById('captcha').autocomplete = 'off';
        }, 1);
    }

    var inputIDsToMonitor = new Array();

    // Bind a listener to the key phrase input fields
    // Note that KSD_FIELD_ID will be printed on the page by the footer.
    if( getTextField(InputType.LOGIN).length > 0 && getTextField(InputType.CREATE).length === 0 ) {
        inputIDsToMonitor.push( "#" + KSD_FIELD_ID );
    }
    if( getTextField(InputType.DROPDOWN).length ) {
        inputIDsToMonitor.push( "#" + KSD_FIELD_ID + "Dropdown" );
    }
    if( getTextField(InputType.CREATE).length > 0 ) {
        inputIDsToMonitor.push( "#" + KSD_FIELD_ID );
        inputIDsToMonitor.push( "#captcha" );
    }
    if( getTextField(InputType.TRAIN).length > 0 ) {
        inputIDsToMonitor.push( "#" + KSD_FIELD_ID );
        inputIDsToMonitor.push( "#captcha" );
    }
    for( var i = 0; i < inputIDsToMonitor.length; i++ ) {
        bindKeystrokeListener( inputIDsToMonitor[i] );
    }

    // Handle submissions
    var formTypes = new Array();
    if( $("#formLogin").length ) {
        formTypes.push( InputType.LOGIN );
    }
    if( $("#formCreate").length ) {
        formTypes.push( InputType.CREATE );
    }
    if( $("#formTrain").length ) {
        formTypes.push( InputType.TRAIN );
    }
    if( $("#formLoginDropdown").length ) {
        formTypes.push( InputType.DROPDOWN );
    }
    for( var j = 0; j < formTypes.length; j++ ) {
        handleSubmission( formTypes[j] );
    }

    getTextField(InputType.LOGIN).focus();
    $('#login-dropdown-button').click( function(event) {
        console.log("Focusing on the dropdown login form.");
        getTextField(InputType.DROPDOWN).focus();
    } );
}


window.onerror = function(message, url, lineNumber) {
    console.log("Error occurred in keystroke dynamics Javascript. Perhaps your browser is out of date?");
    return true;
};
$(document).ready(main);