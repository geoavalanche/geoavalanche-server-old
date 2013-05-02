<?php

/*
 *  Author: @kigen *   
 * 
 */

class tms_Controller extends Controller {

    /**
     *  Creates PHP layer Objects for the plugin layers 
     */
    public function register_map_layers() {

        $layers = Event::$data;

        //Get all layers configured for the plugin.
        $layer_list = ORM::factory('tms_layer')->all();

        //Check map config, for full tms support
        if (ORM::factory('tms_settings')->istms()) {
            //reset default layer definition
            $layers = array();
            //biuld dummy layer objects 
            foreach ($layer_list as $key => $layer) {
                $layers[$key] = layers::get_layer_object($key, $layer);
            }
        } else {
            //Determine the Layer type of the base layer which is the default map.
            $default_map = Kohana::config('settings.default_map');
            $default_layer = $layers[$default_map];

            foreach ($layer_list as $key => $layer) {
                $layers[$key] = layers::get_layer_object($key, $layer, $default_layer->openlayers);
            }
        }

        Event::$data = $layers;
    }

    /**
     *  Generates Javascript layer object definitions for all the plugin layers
     */
    public function modify_layer_code() {
        $js = Event::$data;
        
        //Full tms support
        if (ORM::factory('tms_settings')->istms()) {
            $js = "";
        }

        //Get All layers from the database
        $layer_list = ORM::factory('tms_layer')->all();

        //Add layers as per above configuration.
        foreach ($layer_list as $key => $layer) {
            $js.= layers::get_layer($key, $layer);
            $js.= "";
        }

        
        
        
        //send back the results
        Event::$data = $js;
    }

}

?>
