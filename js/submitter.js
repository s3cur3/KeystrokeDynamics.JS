// Data structure (treated as a list) that will store a
// series of Keystrokes
var keyLog = [];
var thisPagesForm;
var thisPagesInputField;
var thisPagesInputFieldId;

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
 */
function resetAndComplainAfterTypo() {
    resetFormAndTimingData(thisPagesInputField);
    // Insert an alert
    $("#inputKeyPhraseHelp").hide();
    //$("#inputKeyPhrase").unbind('focus');
    if ($('.alert').length == 0) {
        thisPagesForm.prev().before(
            '<div class="alert alert-block alert-error fade in span6 pull-right">'
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
 */
function monitor( textBox ) {
    $("#inputKeyPhrase").keydown(function(event) {
        var eventNeedsRecording = true;
        var i = keyLog.length;

        if( event.keyCode == 8 ) { // backspace
            resetAndComplainAfterTypo();
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

    $("#inputKeyPhrase").keyup(function(event) {
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


function unMonitor( textBox ) {
    $("#inputKeyPhrase").unbind('keyup');
    $("#inputKeyPhrase").unbind('keydown');
}


/**
 * Binds the listeners that
 * @param textField
 */
function bindHelpPopup() {
    var textFieldHelp = $( "#" + thisPagesInputFieldId + "Help");
    // Pretty stuff
    textFieldHelp.fadeOut('slow');
    thisPagesInputField.focus(function () {
        textFieldHelp.fadeIn();
    });
    thisPagesInputField.blur(function () {
        textFieldHelp.fadeOut();
    });
}

/**
 * Binds functions to various events on the page.
 */
function bindKeystrokeListener() {
    // Clear the key phrase, so that if you reloaded
    // this page, it's not populated with the old data
    thisPagesInputField.val('');

    thisPagesInputField.focus(monitor);
    thisPagesInputField.blur(unMonitor);

    bindHelpPopup();
}

SubmitType = {
    CREATE : 'formCreate',
    LOGIN : 'formLogin',
    TRAIN : 'formTrain'
}

function resetFormAndTimingData( inputField ) {
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
 * When the login form is submitted, this adds the data
 * to the form that is necessary for analysis of
 * keystroke dynamics
 */
function handleSubmission( submitType ) {
    var form = $( "#" + submitType );

    var timingData = $("#timingData");
    var phrase = $("#inputKeyPhrase");

    form.submit(function (event) {
        var dataIsOkay = true;

        if( submitType == SubmitType.LOGIN ) {
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
            if( phrase.val() != phrase.attr('placeholder') ) {
                resetAndComplainAfterTypo();
                dataIsOkay = false;
                return false;
            }
        }

        // Add the invisible field which will allow us to send timing data
        if( dataIsOkay ) {
            timingData.val(getSerializedTimingData());
            return true;
        }
        return false;
    });
}



function main() {
    // Bind a listener to the key phrase input field
    if( $("#inputKeyPhrase").length ) {
        thisPagesInputFieldId = "inputKeyPhrase";
        thisPagesInputField = $("#" + thisPagesInputFieldId );
        bindKeystrokeListener();
    }

    // Bind the listeners only if there is a log in form
    var formType;
    if( $("#formLogin").length ) {
        thisPagesForm = $("#formLogin");
        formType = SubmitType.LOGIN ;
    } else if( $("#formCreate").length ) {
        thisPagesForm = $("#formCreate");
        formType = SubmitType.CREATE;
    } else if( $("#formTrain").length ) {
        thisPagesForm = $("#formTrain");
        formType = SubmitType.TRAIN;
    }
    handleSubmission( formType );

    $("#inputKeyPhrase").focus();
}
$(document).ready(main);