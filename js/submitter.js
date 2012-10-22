// Data structure (treated as a list) that will store a
// series of Keystrokes
var keyLog = [];

/**
 * Types of forms that we may work with
 */
SubmitType = {
    CREATE : 'formCreate',
    LOGIN : 'formLogin',
    TRAIN : 'formTrain',
    DROPDOWN: 'formLoginDropdown'
}

/**
 * Defines the Keystroke prototype
 */
function Keystroke(keyCode, timeDown, timeUp) {
    this.keyCode = keyCode;
    this.timeDown = timeDown;
    this.timeUp = timeUp;
}
function keystrokeToString() {
    return String.fromCharCode(this.keyCode)
        + " (key code " + this.keyCode
        + ") pressed down at " + this.timeDown
        + " and let up at " + this.timeUp + "!";
}
// Serializes a keystroke to [key code],[time down],[time up]
function keystrokeSerialize() {
    return this.keyCode + "," + this.timeDown
        + "," + this.timeUp;
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
    var spanWidth = (textFieldID.indexOf("Dropdown") != -1 ? 3 : 6 );
    if( $('.alert').length == 0 ) {
        textField.parent().parent().before(
            '<div class="alert alert-block alert-error fade in span' + spanWidth + ' pull-right">'
                + '<button type="button" class="close" data-dismiss="alert" data-close="bindHelpPopup()">×</button>'
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
function monitor( textField ) {
    console.log( "Monitoring text field with ID " + textField.attr('id') );
    textField.keydown(function(event) {
        console.log("Key pressed.");
        var eventNeedsRecording = true;
        var i = keyLog.length;

        if( event.keyCode == 8 ) { // backspace
            resetAndComplainAfterTypo(textField);
            eventNeedsRecording = false;
        } else if( event.keyCode == 13 ) { // Ignore enters
            eventNeedsRecording = false;
        } else if( i > 0 ) { // If the keylog isn't empty
            if( keyLog[i - 1].timeDown == event.timeStamp
                && keyLog[i - 1].keyCode == event.keyCode ) { // if this isn't identical to the previous
                eventNeedsRecording = false;
            }
        }

        if( eventNeedsRecording ) {
            keyLog[i] = new Keystroke( event.keyCode, event.timeStamp, 0 );
        }
    });

    textField.keyup(function(event) {
        // Determine the last instance of this key that was pressed down
        var i; // assume it's the last character
        for( i = keyLog.length - 1; i >= 0; i-- ) {
            if( keyLog[i].keyCode == event.keyCode && keyLog[i].timeUp == 0 ) {
                keyLog[i].timeUp = event.timeStamp;
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

    // Clear the key phrase, so that if you reloaded
    // this page, it's not populated with the old data
    inputField.val('');

    inputField.focus(monitor.bind(this, inputField));
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
    keyLog.length = 0;
    keyLog = [];
}


/**
 * Serializes the complete key log
 * @return A string version of the full keyLog
 */
function getSerializedTimingData() {
    var s = "";
    for (var i = 0; i < keyLog.length; i++) {
        s += keyLog[i].serialize() + " ";
    }
    return s;
}

/**
 * @param formType a SubmitType enumerated value corresponding
 *                 to the type of form on this page
 * @return the jQuery object corresponding to the text fields
 *         present in the given form type
 */
function getTextFieldFromFormType( formType ) {
    var suffix = '';
    if( formType == SubmitType.DROPDOWN ) {
        suffix = "Dropdown";
    }

    return $("#inputKeyPhrase" + suffix);
}

/**
 * @param formType a SubmitType enumerated value corresponding
 *                 to the type of form on this page
 * @return the jQuery object corresponding to the timing data
 *         field present in the given form type
 */
function getTimingFieldFromFormType( formType ) {
    var suffix = '';
    if( formType == SubmitType.DROPDOWN ) {
        suffix = "Dropdown";
    }

    return $("#timingData" + suffix);
}


/**
 * When the login form is submitted, this adds the data
 * to the form that is necessary for analysis of
 * keystroke dynamics
 */
function handleSubmission( submitType ) {
    var form = $( "#" + submitType );



    var timingData = getTimingFieldFromFormType(submitType);
    var inputField = getTextFieldFromFormType(submitType);


    form.submit(function (event) {
        var dataIsOkay = true;

        if( submitType == SubmitType.LOGIN
            || submitType == SubmitType.DROPDOWN ) {
            // Write to diagnostic log
            var theLog = $("#theLog");
            theLog.empty();
            theLog.append('<ul>');
            for (var i = 0; i < keyLog.length; i++) {
                theLog.append('<li>' + keyLog[i].toString() + '</li>');
            }
            theLog.append('</ul>');
        } else if( submitType == SubmitType.TRAIN ) {
            // If the password was wrong, inform the user
            if( inputField.val() != inputField.attr('placeholder') ) {
                resetAndComplainAfterTypo( inputField );
                dataIsOkay = false;
                return false;
            }
        }

        // Add the invisible field which will allow us to send timing data
        if( dataIsOkay ) {
            console.log( keyLog );
            timingData.val(getSerializedTimingData());
            return true;
        }
        return false;
    });
}



function main() {
    inputIDsToMonitor = new Array();

    // Bind a listener to the key phrase input fields
    if( $("#inputKeyPhrase").length ) {
        inputIDsToMonitor.push( "#inputKeyPhrase" );
    }
    if( $("#inputKeyPhraseDropdown").length ) {
        inputIDsToMonitor.push( "#inputKeyPhraseDropdown" );
    }
    for( var i = 0; i < inputIDsToMonitor.length; i++ ) {
        bindKeystrokeListener( inputIDsToMonitor[i] );
    }

    // Handle submissions
    var formTypes = new Array();
    if( $("#formLogin").length ) {
        formTypes.push( SubmitType.LOGIN );
    }
    if( $("#formCreate").length ) {
        formTypes.push( SubmitType.CREATE );
    }
    if( $("#formTrain").length ) {
        formTypes.push( SubmitType.TRAIN );
    }
    if( $("#formLoginDropdown").length ) {
        formTypes.push( SubmitType.DROPDOWN );
    }
    for( var j = 0; j < formTypes.length; j++ ) {
        handleSubmission( formTypes[j] );
    }

    $("#inputKeyPhrase").focus();
    $('#login-dropdown-button').click( function(event) {
        console.log("Focusing on the dropdown login form.");
        getTextFieldFromFormType(SubmitType.DROPDOWN).focus();
    } );
}

window.onerror = function(message, url, lineNumber) {
    console.log("Error occurred in keystroke dynamics Javascript. Perhaps your browser is out of date?");
    return true;
};
$(document).ready(main);