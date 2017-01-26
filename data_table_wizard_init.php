<?php


function data_table_wizard_init($file) {

    require_once('data_table_wizard_plugin.php');
    $DTW_PLugin = new data_table_wizard_plugin();
 
    // Add callbacks to hooks
    $DTW_PLugin->addActionsAndFilters();
    $DTW_PLugin->registerScripts();
    $DTW_PLugin->add_Thickbox();
 
    if (!$file) {
        $file = __FILE__;
    }
    
    // Register the Plugin Activation Hook
    register_activation_hook($file, array(&$DTW_PLugin, 'activate'));
   
    // Register the Plugin Deactivation Hook
    register_deactivation_hook($file, array(&$DTW_PLugin, 'deactivate'));
    
    
}
