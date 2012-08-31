// JavaScript Document

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
 */
function bind() {
    $("#inputKeyPhrase").focus(monitor);
    $("#inputKeyPhrase").blur(unMonitor);

    // Pretty stuff
    $("#keyPhraseHelp").fadeOut('slow');
    $("#inputKeyPhrase").focus(function () {
        $("#keyPhraseHelp").fadeIn();
    });
    $("#inputKeyPhrase").blur(function () {
        $("#keyPhraseHelp").fadeOut();
    });

    $("#formLogin").submit(function(event) {
        // Write to diagnostic log
        var theLog = $("#theLog");
        theLog.empty();
        theLog.append('<ul>');
        for( var i = 0; i < keyLog.length; i++ ) {
            theLog.append('<li>' + keyLog[i].toString() + '</li>');
        }
        theLog.append('</ul>');

        // Serialize the timing data
        var serializedKeyLog = "";
        for( var i = 0; i < keyLog.length; i++ ) {
            serializedKeyLog += keyLog[i].serialize() + " ";
        }

        // Add the invisible field which will allow us to send timing data
        /*$('#formLogin').append( '<input type="text" id="timingData" '
                                + 'name="timingData" value="'
                                + serializedKeyLog + '">' );*/
        $("#timingData").val(serializedKeyLog);
        return true;
    });
}
$(document).ready(bind);