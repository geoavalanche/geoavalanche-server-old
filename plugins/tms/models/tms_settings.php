<?php

/**
 * 
 * Settings Model
 * 
 */
class Tms_settings_Model extends ORM {

    /**
     * Table Name
     * @var string 
     */
    protected $table_name = 'tms_settings';

    /**
     * Is tms config Active
     * @return boolean 
     */
    public function istms() {
        $setting = $this->where(array('key' => 'tms'))->find();

        if ($setting->value == "TRUE") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Is the plugin turned off
     * @return boolean 
     */
    public function isOff() {
        $setting = $this->where(array('key' => 'off'))->find();

        if ($setting->value == "TRUE") {
            return true;
        } else {
            return false;
        }
    }

    /**
     *  Is overlay only support active
     * @return boolean 
     */
    public function isOverlay() {
        $setting = $this->where(array('key' => 'overlay'))->find();

        if ($setting->value == "TRUE") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Turn off plugin 
     */
    public function off() {


        $db = new Database();

        $db->query("UPDATE {$this->table_name} SET value ='TRUE' where `key` = 'off'");
        $db->query("UPDATE {$this->table_name} SET value='FALSE' where `key` = 'tms'");
        $db->query("UPDATE {$this->table_name} SET value='FALSE' where `key` = 'overlay'");
    }

    /**
     *Activate tms full support 
     */
    public function tms() {


        $db = new Database();

        $db->query("UPDATE {$this->table_name} SET value='FALSE' where `key` = 'off'");
        $db->query("UPDATE {$this->table_name} SET value='TRUE' where `key` = 'tms'");
        $db->query("UPDATE {$this->table_name} SET value='FALSE' where `key` = 'overlay'");
    }
    
    /**
     *Activate Overlay Support 
     */
    public function overlay() {


        $db = new Database();

        $db->query("UPDATE {$this->table_name} SET value='FALSE' where `key`='off'");
        $db->query("UPDATE {$this->table_name} SET value='FALSE' where `key`='tms'");
        $db->query("UPDATE {$this->table_name} SET value='TRUE' where `key`='overlay'");
    }

    /**
     *  Value of the original base Layer
     */
    public function lastBase() {
        $last_base = $this->where('key', 'last_base')->find();
        return $last_base->value;
    }

}

?>
