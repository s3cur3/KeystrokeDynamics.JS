// Data structure (treated as a list) that will store a
// series of Keystrokes
var keyLog = new Array();

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
 * Uses the jQuery's keydown() and keyup() functions to monitor
 * keystrokes in the "key phrase" text box. Fills the keyLog data
 * structure with this information.
 */
function monitor( textBox ) {
    $("#inputKeyPhrase").keydown(function(event) {
        var i = keyLog.length; // index of a new item to the list
        keyLog[i] = new Keystroke( event.keyCode, event.timeStamp, 0 );
    });

    $("#inputKeyPhrase").keyup(function(event) {
        // Determine the last instance of this key that was pressed down
        var i; // assume it's the last character
        for( i = keyLog.length - 1; i >= 0; i-- ) {
            if( keyLog[i].keyCode == event.keyCode && keyLog[i].timeUp == 0 ) {
                keyLog[i].timeUp = event.timeStamp;
            }
        }
    });
}


function unMonitor( textBox ) {
    $("#inputKeyPhrase").unbind('keyup');
    $("#inputKeyPhrase").unbind('keydown');
}


/**
 * Binds functions to various events on the page.
 * @param idToMonitor The ID of the element to bind (should begin with a #)
 */
function bindKeystrokeListener( idToMonitor ) {
    // Clear the key phrase, so that if you reloaded
    // this page, it's not populated with the old data
    var textField = $(idToMonitor);
    textField.val('');

    textField.focus(monitor);
    textField.blur(unMonitor);

    var textFieldHelp = $(idToMonitor + "Help");
    // Pretty stuff
    textFieldHelp.fadeOut('slow');
    textField.focus(function () {
        textFieldHelp.fadeIn();
    });
    textField.blur(function () {
        textFieldHelp.fadeOut();
    });
}

SubmitType = {
    CREATE : 'formCreate',
    LOGIN : 'formLogin',
    TRAIN : 'formTrain'
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
                $("#inputKeyPhraseHelp").html("Sorry, you need to type your key phrase <strong>exactly as before.</strong>")
                phrase.val('');
                phrase.focus();
                phrase.unbind('blur');

                // Reset the keyLog and the counter
                keyLog = new Array();
                return false;
            }
        }

        // Serialize the timing data
        var serializedKeyLog = "";
        for (var i = 0; i < keyLog.length; i++) {
            serializedKeyLog += keyLog[i].serialize() + " ";
        }

        // Add the invisible field which will allow us to send timing data
        timingData.val(serializedKeyLog);
        return true;
    });
}

function main() {
    // Bind a listener to the key phrase input field
    if( $("#inputKeyPhrase") ) {
        bindKeystrokeListener( "#inputKeyPhrase" );
    }

    for( var i = 0; i < 10; i++ ) {
        if( $("#inputKeyPhrase" + i) ) {
            bindKeystrokeListener( "#inputKeyPhrase" + i );
        }
    }

    // Bind the listeners only if there is a log in form
    var formType;
    if( $("#formLogin").length ) {
        formType = SubmitType.LOGIN ;
    } else if( $("#formCreate").length ) {
        formType = SubmitType.CREATE;
    } else if( $("#formTrain").length ) {
        formType = SubmitType.TRAIN;
    }
    handleSubmission( formType );

    $("#inputKeyPhrase").focus();
}
$(document).ready(main);