<?php

/**
 * Description of tms_Settings
 *
 * @author Seth
 */
class Tms_Settings_Controller extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->db = new Database();
    }

    function index() {
        $this->template->content = View::factory('admin/settings');
        if ($_POST) {
            $post = Validation::factory($_POST);

            ORM::factory('tms_layer')->off(true);
            switch ($post->mapConfig) {
                case 'overlay':
                    // Save config
                    ORM::factory('tms_settings')->overlay();

                    //Turn on only overlay layers
                    ORM::factory('tms_layer')->overlay(true);
                    $this->base(FALSE);
                    break;
                case 'tms':
                    //Save config
                    ORM::factory('tms_settings')->tms();
                    //Turn on all layers (base/overlay)
                    ORM::factory('tms_layer')->off();
                    $this->base(TRUE);
                    break;
                case 'off':
                    //Save config
                    ORM::factory('tms_settings')->off();
                    $this->base(FALSE);
                    //Turn off all layers
                    //ORM::factory('tms_layer')->off(true);
                    break;
            }
        }
    }

    function layers() {
        $this->template->content = View::factory('admin/layers');
        if ($_POST) {
            $post = Validation::factory($_POST);
            $i = 0;
            if (isset($post->layerName)) {
                foreach ($post->layerName as $l) {

                    $Layer;
                    switch ($post->flag[$i]) {

                        case 'new':
                            $Layer = ORM::factory('tms_layer');
                            $Layer->isBase = $post->isBase[$i];

                            break;
                        case 'edit':
                            $Layer = ORM::factory('tms_layer', $post->id[$i]);
                            break;
                    }
                    $Layer->name = $post->layerName[$i];
                    $Layer->title = $post->layerTitle[$i];
                    $Layer->url = $post->layerUrl[$i];
                    $Layer->isActive = 1;
                    $Layer->save();
                    $i++;
                }
            }
        }
    }

    /**
     * Switch base layers
     * @param boolean $on 
     */
    function base($on = TRUE) {

        if ($on) {
            //Register the baselayer name..
            $default_map = ORM::factory('settings')->where('key', 'default_map')->find();
            $this->db->query("UPDATE tms_settings SET value='{$default_map->value}' where `key`='last_base'");
            $this->db->query("UPDATE " . Kohana::config('database.default.table_prefix') . 'settings SET value=\'tms_base\' WHERE `key`=\'default_map\'');
        } else {

            $layer_id = ORM::factory('tms_settings')->lastBase();
            $this->db->query("UPDATE " . Kohana::config('database.default.table_prefix') . 'settings SET value=\'' . $layer_id . '\' WHERE `key`=\'default_map\'');
        }
    }

}

?>
