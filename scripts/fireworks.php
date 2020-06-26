<?php

//===============================================================
//===============================================================
// Fireworks Launcher
//===============================================================
//===============================================================

//===============================================================
// Necessary Classes
//===============================================================
include(__DIR__ . '/../lib/Core.php');
include(__DIR__ . '/../lib/GPIO.php');

//===============================================================
// Script Header
//===============================================================
\PhpRaspberryPi\Core::ScriptStart('Fireworks Launcher Script!!');

//===============================================================
// Turn Off All Inputs
//===============================================================
\PhpRaspberryPi\Core::ScriptMessage('Turning Off all pins.');
\PhpRaspberryPi\GPIO::TurnOffAll(['verbose' => true]);

//===============================================================
// Command Line Options
//===============================================================
$options = getopt("t");
$test = false;
if (array_key_exists('t', $options)) {
    $test = true;
}

//===============================================================
// Read Launch Sequence from JSON Config File
//===============================================================
if ($test) {
    $config_json = file_get_contents(__DIR__ . '/../configs/launch-sequence-test.json');
}
else {
    $config_json = file_get_contents(__DIR__ . '/../configs/launch-sequence.json');
}
if (!$config_json) {
    \PhpRaspberryPi\Core::ExitWithError('Launch sequence configuration file does not exist or cannot be accessed.');
}
$config = json_decode($config_json, true);
if (!$config) {
    $msg = 'Launch sequence configuration file content is NOT valid JSON.';
    $msg2 = json_last_error_msg();
    if ($msg2) {
        $msg .= " ({$msg2})";
    }
    \PhpRaspberryPi\Core::ExitWithError($msg);
}

//===============================================================
// Parse Config / Setup Launch Sequence
//===============================================================
if (!isset($config['default'])) {
    \PhpRaspberryPi\Core::ExitWithError('No launch sequence configuration defaults set.');
}
$launch_iterations = 1;
$verbose = false;
if (isset($config['iterations'])) {
    $launch_iterations = (int)$config['iterations'];
}
if (isset($config['verbose'])) {
    $verbose = (bool)$config['verbose'];
}
$defaults = $config['default'];
$sequence = [];
$pin_order = [];
foreach ($config['sequence'] as $el) {

    //-----------------------------------------------------------
    // No Pin? Skip it.
    //-----------------------------------------------------------
    if (!array_key_exists('pin', $el)) {
        continue;
    }
    $pin_order[] = $el['pin'];

    //-----------------------------------------------------------
    // Iterations
    //-----------------------------------------------------------
    if (!isset($el['iterations'])) {
        $el['iterations'] = 1;
        if (isset($defaults['iterations']) && $defaults['iterations'] >= 0) {
            $el['iterations'] = $defaults['iterations'];
        }
    }

    //-----------------------------------------------------------
    // Wait Before (Milliseconds)
    //-----------------------------------------------------------
    if (!isset($el['wait_before'])) {
        $el['wait_before'] = 0;
        if (isset($defaults['wait_before']) && $defaults['wait_before'] >= 0) {
            $el['wait_before'] = $defaults['wait_before'];
        }
    }

    //-----------------------------------------------------------
    // Duration (Milliseconds)
    //-----------------------------------------------------------
    if (!isset($el['duration'])) {
        $el['duration'] = 0;
        if (isset($defaults['duration']) && $defaults['duration'] >= 0) {
            $el['duration'] = $defaults['duration'];
        }
    }

    //-----------------------------------------------------------
    // Wait After (Milliseconds)
    //-----------------------------------------------------------
    if (!isset($el['wait_after'])) {
        $el['wait_after'] = 0;
        if (isset($defaults['wait_after']) && $defaults['wait_after'] >= 0) {
            $el['wait_after'] = $defaults['wait_after'];
        }
    }

    //-----------------------------------------------------------
    // Convert Milliseconds to Microseconds
    //-----------------------------------------------------------
    $el['wait_before'] *= 1000;
    $el['duration'] *= 1000;
    $el['wait_after'] *= 1000;

    //-----------------------------------------------------------
    // Add Element to Launch Sequence
    //-----------------------------------------------------------
    $sequence[] = $el;
}

//===============================================================
// Set Inputs Mode to "OUT"
//===============================================================
\PhpRaspberryPi\GPIO::SetInputsMode($pin_order, 'out');

//===============================================================
// Run Sequence Confirmation
//===============================================================
\PhpRaspberryPi\Core::ConfirmContinue('Setup complete. Execute launch sequence?');

//===============================================================
// !!! RUN LAUNCH SEQUENCE !!!
//===============================================================
$sequence_its = 0;
while ($sequence_its < $launch_iterations) {

    //-----------------------------------------------------------
    // Increment Launch Sequence Iteration #
    // Display Launch Sequence Message
    //-----------------------------------------------------------
    $sequence_its++;
    \PhpRaspberryPi\Core::ScriptMessage("Launch Sequence Iteration #{$sequence_its}:");

    //-----------------------------------------------------------
    // Loop through each element in launch sequence
    //-----------------------------------------------------------
    foreach ($sequence as $seq_num => $l_el) {
        $l_el_its = 0;

        //-------------------------------------------------------
        // Sequence Iterations
        //-------------------------------------------------------
        while ($l_el_its < $l_el['iterations']) {

            //---------------------------------------------------
            // Display Sequence Message
            //---------------------------------------------------
            $msg_seq = "Sequence #{$seq_num}, Iteration #{$l_el_its}";
            \PhpRaspberryPi\Core::ScriptMessage($msg_seq);

            //---------------------------------------------------
            // Duration <= 0 means skip
            //---------------------------------------------------
            if ($l_el['duration'] <= 0) {
                print "- Duration is 0. Skipping.\n";
                break;
            }

            //---------------------------------------------------
            // Increment Sequence Iteration #
            //---------------------------------------------------
            $l_el_its++;

            //---------------------------------------------------
            // Display Sequence Message
            //---------------------------------------------------
            if ($verbose) {
                print "- Pin: {$l_el['pin']}\n";
                print "- Wait Before: {$l_el['wait_before']}\n";
                print "- Duration: {$l_el['duration']}\n";
                print "- Wait After: {$l_el['wait_after']}\n";
            }

            //---------------------------------------------------
            // Wait (Before) (In-active)
            //---------------------------------------------------
            if ($l_el['wait_before']) {
                usleep($l_el['wait_before']);
            }

            //---------------------------------------------------
            // Activate Pin
            //---------------------------------------------------
            system("gpio write {$l_el['pin']} 1");

            //---------------------------------------------------
            // Wait (Duration)  (Active)
            //---------------------------------------------------
            if ($l_el['duration']) {
                usleep($l_el['duration']);
            }

            //---------------------------------------------------
            // De-activate Pin
            //---------------------------------------------------
            system("gpio write {$l_el['pin']} 0");

            //---------------------------------------------------
            // Wait (After) (In-active)
            //---------------------------------------------------
            if ($l_el['wait_after']) {
                usleep($l_el['wait_after']);
            }
        }
    }
}

//===============================================================
// Turn Off All Inputs
//===============================================================
\PhpRaspberryPi\Core::ScriptMessage('Turning Off all pins.');
\PhpRaspberryPi\GPIO::TurnOffAll(['verbose' => true]);

//===============================================================
// Done
//===============================================================
\PhpRaspberryPi\Core::ScriptEnd();
