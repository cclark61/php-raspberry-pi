<?php
//===============================================================
//===============================================================
// Turn off All Inputs
//===============================================================
//===============================================================
include(__DIR__ . '/lib/Core.php');
include(__DIR__ . '/lib/GPIO.php');

//===============================================================
// Script Header
//===============================================================
\PhpRaspberryPi\Core::ScriptStart('Turn Off All Inputs Script');

//===============================================================
// Turn Off All Inputs
//===============================================================
\PhpRaspberryPi\GPIO::TurnOffAll();

//===============================================================
// Done
//===============================================================
\PhpRaspberryPi\Core::ScriptEnd();
