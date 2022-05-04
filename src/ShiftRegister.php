<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Shift Register Class
 *
 * @package         Cclark61\RPi
 * @author          Christian J. Clark
 * @copyright       Christian J. Clark
 * @link            https://github.com/cclark61/php-raspberry-pi
 **/
//*****************************************************************************
//*****************************************************************************

namespace Cclark61\RPi;

class ShiftRegister
{
    //=========================================================================
    // Class Members
    //=========================================================================
    protected $type = '74HC595';
    protected $mode = 'out';
    protected $num_registers = 8;
    protected $data_pin = 3;
    protected $clock_pin = 4;
    protected $latch_pin = 5;

    //=========================================================================
    //=========================================================================
    // Constructor
    //=========================================================================
    //=========================================================================
    public function __construct(Array $args=[])
    {
        extract($args);

        //-------------------------------------------------------------
        // App Config
        //-------------------------------------------------------------
        $app_config = new \phpOpenFW\Core\AppConfig();

        //-------------------------------------------------------------
        // Class Setup
        //-------------------------------------------------------------
        if (!empty($type)) {
            $this->type = $type;
        }
        else if (!empty($app_config->shift_register_type)) {
            $this->type = $app_config->shift_register_type;
        }
        if (!empty($mode)) {
            $this->mode = $mode;
        }
        else if (!empty($app_config->shift_register_mode)) {
            $this->mode = $app_config->shift_register_mode;
        }
        if (!empty($num_registers)) {
            $this->num_registers = $num_registers;
        }
        else if (!empty($app_config->shift_register_count)) {
            $this->num_registers = $app_config->shift_register_count;
        }
        if (!empty($data_pin)) {
            $this->data_pin = $data_pin;
        }
        else if (!empty($app_config->shift_register_data_pin)) {
            $this->data_pin = $app_config->shift_register_data_pin;
        }
        if (!empty($clock_pin)) {
            $this->clock_pin = $clock_pin;
        }
        else if (!empty($app_config->shift_register_clock_pin)) {
            $this->clock_pin = $app_config->shift_register_clock_pin;
        }
        if (!empty($latch_pin)) {
            $this->latch_pin = $latch_pin;
        }
        else if (!empty($app_config->shift_register_latch_pin)) {
            $this->latch_pin = $app_config->shift_register_latch_pin;
        }

        //-------------------------------------------------------------
        // Turn off pins
        //-------------------------------------------------------------
        GPIO::PinWrite($this->data_pin, 0, $args);
        GPIO::PinWrite($this->clock_pin, 0, $args);
        GPIO::PinWrite($this->latch_pin, 0, $args);

        //-------------------------------------------------------------
        // Set mode on pins
        //-------------------------------------------------------------
        $pins = [$this->data_pin, $this->clock_pin, $this->latch_pin];
        GPIO::SetInputsMode($pins, 'out', $args);

        //-------------------------------------------------------------
        // Clear Registers
        //-------------------------------------------------------------
        if (!empty($args['clear'])) {
            $this->ClearRegisters();
        }
    }

    //=========================================================================
    //=========================================================================
    // Test
    //=========================================================================
    //=========================================================================
    public function Test(Array $args=[])
    {
        //---------------------------------------------------------------------
        // Set all registers to 1
        //---------------------------------------------------------------------
        for ($i = 0; $i < $this->num_registers; $i++) {
            $this->SetDataPin(1, $args);
            $this->CycleClockPin($args);
        }
        $this->CycleLatchPin($args);

        //---------------------------------------------------------------------
        // Sleep
        //---------------------------------------------------------------------
        sleep(3);

        //---------------------------------------------------------------------
        // Set all registers to 0
        //---------------------------------------------------------------------
        $this->ClearRegisters($args);
    }

    //=========================================================================
    //=========================================================================
    // Clear Registers
    //=========================================================================
    //=========================================================================
    public function ClearRegisters(Array $args=[])
    {
        //---------------------------------------------------------------------
        // Defaults / Extract Args
        //---------------------------------------------------------------------
        extract($args);

        //---------------------------------------------------------------------
        // Set all registers to 0
        //---------------------------------------------------------------------
        for ($i = 0; $i < $this->num_registers; $i++) {
            $this->SetDataPin(0, $args);
            $this->CycleClockPin($args);
        }

        //---------------------------------------------------------------------
        // Send the data
        //---------------------------------------------------------------------
        $this->CycleLatchPin($args);
    }

    //=========================================================================
    //=========================================================================
    // Write Bits
    //=========================================================================
    //=========================================================================
    public function WriteBits(String $bits, Array $args=[])
    {
        $bits = str_split($bits);
        if (!is_array($bits)) {
            return false;
        }
        foreach ($bits as $bit) {
            if ($bit != '0' && $bit != 1) {
                $bit = 0;
            }
            $this->SetDataPin($bit, $args);
            $this->CycleClockPin($args);
        }
        return true;
    }

    //=========================================================================
    //=========================================================================
    // Set Data Pin
    //=========================================================================
    //=========================================================================
    public function SetDataPin($val, Array $args=[])
    {
        return GPIO::PinWrite($this->data_pin, $val, $args);
    }

    //=========================================================================
    //=========================================================================
    // Cycle Clock Pin
    //=========================================================================
    //=========================================================================
    public function CycleClockPin(Array $args=[])
    {
        return GPIO::CyclePin($this->clock_pin, 1, $args);
    }

    //=========================================================================
    //=========================================================================
    // Cycle Latch Pin
    //=========================================================================
    //=========================================================================
    public function CycleLatchPin(Array $args=[])
    {
        return GPIO::CyclePin($this->latch_pin, 1, $args);
    }
}
