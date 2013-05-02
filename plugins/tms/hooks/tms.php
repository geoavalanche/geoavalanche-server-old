<?php

/*
 * Author: @kigen
 * Enable tms Layer on to ushahidi.
 */

class tms {

    public function __construct() {
        // Hook into routing        
        Event::add('system.pre_controller', array($this, 'add'));
    }

    public function add() {
        
        //include Openlayers Libaries to support TMS
         plugin::add_javascript('tms/views/js/Grid.js');
         plugin::add_javascript('tms/views/js/HTTPRequest.js');
         plugin::add_javascript('tms/views/js/Image.js');
         plugin::add_javascript('tms/views/js/TMS.js');
         
        //Check if the plugin has been turned off
        if (!ORM::factory('tms_settings')->isOff()) {
            Event::add('ushahidi_filter.map_base_layers', array("Tms_Controller", 'register_map_layers'));
            Event::add('ushahidi_filter.map_layers_js', array("Tms_Controller", 'modify_layer_code'));
        }
    }

}

new tms;
?>
