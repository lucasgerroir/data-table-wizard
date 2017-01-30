<?php


class data_table_wizard_plugin  {

   /**
    * @return array of option meta data.
    */
    public function getOptionMetaData() {

        return array(
            //'_version' => array('Installed Version'), // Leave this one commented-out. Uncomment to test upgrades.
            'ATextInput' => array(__('Enter in some text', 'my-awesome-plugin')),
            'AmAwesome' => array(__('I like this awesome plugin', 'my-awesome-plugin'), 'false', 'true'),
            'CanDoSomething' => array(__('Which user role can do something', 'my-awesome-plugin'),
                                        'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber', 'Anyone')
        );
    }

    protected function initOptions() {
        $options = $this->getOptionMetaData();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr > 1)) {
                    $this->addOption($key, $arr[1]);
                }
            }
        }
       
    }
    
   public function activate() {
   	
    	
    }
    
    public function deactivate() {
  	
    }
    
    protected function getMainPluginFileName() {
        return 'data_table_wizard_plugin.php';
    }

    public function addActionsAndFilters() {
		
        // Add options administration page
        add_action('admin_menu', [&$this, 'addSettingsSubMenuPage']);
    
       // script & style just for the options administration page
      if (is_admin()) {
      	
      	add_action('media_buttons', [&$this, 'add_wizard_button'], 15);
      	add_action('media_buttons', [&$this, 'add_wizard_app'], 14);
      	add_action('admin_init', [&$this, 'get_gravity_forms']);

      }
               
    }
    
    public function add_Thickbox() {
    	
    	add_thickbox();
	}
    
    public function registerScripts() {
    	
    	// script & style just for the options administration page
    	if (is_admin()) {
    		
    		// register scipts and styles
    		wp_register_script( 'angular_data_table_wizard', DTW_DIR_URL . '/js/angular.min.js');
    		wp_register_script( 'data_table_wizard', DTW_DIR_URL . '/js/data_table_wizard.js');
    		wp_register_script( 'jquery-ui', DTW_DIR_URL . '/js/jquery-ui.min.js');
    		
    		// only add the js if editing a page/post
    		global $pagenow;
    		
    		if (in_array( $pagenow, array( 'post.php',  ) )) {
    			
    			wp_enqueue_script( 'jquery-ui' );
    			wp_enqueue_script( 'angular_data_table_wizard' );
    			wp_enqueue_script( 'data_table_wizard' );
    			wp_enqueue_style( 'data_table_wizard_css', DTW_DIR_URL . '/css/data_table_wizard.css');
    			wp_enqueue_style( 'data_table_wizard_css', DTW_DIR_URL . '/css/font-awesome.min.css');
 
    		}

    	}
    	 
    }
    
    public function add_wizard_app() {
    	
    	echo '
    			<div id="data_table_wizard_module"  style="display:none;" ng-app="data_wizard_app" >
    			<div ng-controller="Controller">
	                	<div ng-if="view == \'view1\'" ng-include="\'' . DTW_DIR_URL . '/partial/view1.html\'"></div>
	                	<div ng-if="view == \'view2\'" ng-include="\'' . DTW_DIR_URL . '/partial/view2.html\'"></div>
	                	<div ng-if="view == \'view3\'" ng-include="\'' . DTW_DIR_URL . '/partial/view3.html\'"></div>
	                	<div ng-if="view == \'view4\'" ng-include="\'' . DTW_DIR_URL . '/partial/view4.html\'"></div>
	               <div class="bottom-half" ng-include="\'' . DTW_DIR_URL . '/partial/bottom.html\'"></div>
    			</div>
    	      </div>';
    	
    }

    
    public function add_wizard_button() {
    	
    	echo '<a href="#TB_inline?width=800&height=550&inlineId=data_table_wizard_module" title="Data Table Wizard" id="add-data-table" class="button thickbox"> <i class="fa fa-table" aria-hidden="true"></i> Review Table </a>';
	}
	
	public function get_gravity_forms() {
		
		$data = [];

		if ( method_exists  ( "RGFormsModel", "get_forms" ))  {
			$forms = RGFormsModel::get_forms( true );
			$warning["forms"] = (empty($forms)) ? "create" : NULL;
		} else {
			$forms = NULL;
			$warning["forms"] = "activate";
		}
		
		if ( method_exists ( "Groups_Group", "get_groups") ) {
			$groups = Groups_Group::get_groups();
			$warning["groups"] = (empty($groups)) ? "create" : NULL;
		} else {
			$groups = NULL;
			$warning["groups"] = "activate";
		}
		
		$url = admin_url();
		
		
		$data["forms"] = $forms;
		$data["groups"] = $groups;
		$data["warnings"] = $warning;
		$data["url"] = $url;
		
		
		wp_localize_script( 'data_table_wizard', 'php_vars', $data );
	}
   
   /**
    * Puts the configuration page in the Plugins menu by default.
    * Override to put it elsewhere or create a set of submenus
    * Override with an empty implementation if you don't want a configuration page
    * @return void
    */
    public function addSettingsSubMenuPage() {
    	
         add_options_page(  "Data Table Wizard",
         				 "Data Table Wizard",
                         'manage_options',
         				 'data_table_wizard',
                         array(&$this, 'settingsPage'));
    }
    
   /**
    * Creates HTML for the Administration page to set options for this plugin.
    * Override this method to create a customized page.
    * @return void
    */
    public function settingsPage() {
    	
    	if (!current_user_can('manage_options')) {
    		wp_die(__('You do not have sufficient permissions to access this page.', 'wp_jquery_database_creator'));
    	}
    
    	$optionMetaData = $this->getOptionMetaData();
    
    	// Save Posted Options
    	if ($optionMetaData != null) {
    		foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
    			if (isset($_POST[$aOptionKey])) {
    				$this->updateOption($aOptionKey, $_POST[$aOptionKey]);
    			}
    		}
    	}
    	
    	echo "<h2>This is the admin</h2>";
    			
       
     
    }   
        

}
