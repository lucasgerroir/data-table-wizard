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
      	add_action('admin_menu', [&$this, 'get_gravity_forms']);
      	add_action( 'wp_ajax_my_action_name', [&$this,'my_action_callback'] );
      	
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
		$ajax_url = admin_url('admin-ajax.php');
		
		$data["forms"] = $forms;
		$data["groups"] = $groups;
		$data["warnings"] = $warning;
		$data["url"] = $url;
		$data["regex"] =  get_shortcode_regex();
		$data["current_values"] = self::current_values();

		wp_localize_script( 'data_table_wizard', 'php_vars', $data );
	}
	
	public function current_values() {
		
		$current_values = [];
		
		$wp_jdt = self::get_shortcode('wp_jdt');
		$directory = self::get_shortcode('directory');
		
		$form = self::get_attribute("form", $directory[0]);
		$edit = self::get_attribute("edit", $wp_jdt[0]);
		$filterbygroup = self::get_attribute("filterbygroup", $wp_jdt[0]);
		
		$columns = explode(",", $edit);
		
		if (is_array($columns)) {
				
			$column_struct = [];
			$id = 1;
			foreach ($columns as $column) {
		
				$each_col_attr = explode("|", $column);
				$column_struct[$id]["name"] = $each_col_attr[0];
				$column_struct[$id]["direction"] = $each_col_attr[1];
		
				$values = explode("*", $each_col_attr[2]);
		
				$column_struct[$id]["field"] = $values[0];
				unset($values[0]);
		
				$column_struct[$id]["values"] = $values;
		
				$id++;
			}
		}
		
		if ( method_exists  ( "RGFormsModel", "get_form" ))  {
			$form = RGFormsModel::get_form( intval ($form) );
			($form ==  false) ? NULL : $form;
		} 
		
		$current_values["added_columns"] = $column_struct;
		$current_values["form"] = $form;
		$current_values["filterbygroup"] = $filterbygroup;
	
		return $current_values;
		
	}
	
	public function get_attribute( $attr, $content ){
		
		$search = '/' . $attr . '="([^"]+)"/';
		preg_match($search, $content, $matches);

		return $matches[1];
	}
	
	public function get_shortcode( $shortcode ) {
		

		$pattern = get_shortcode_regex();
		
		$post_content = get_post($_GET['post']);
		$content = $post_content->post_content;
		
		preg_match_all("/$pattern/", $content, $matches);
		
		$lm_shortcode = array_keys($matches[2], $shortcode);
		
		if (!empty($lm_shortcode)) {
			
			foreach($lm_shortcode as $sc) {
				$attr[] = $matches[3][$sc];
			}
			return $attr;
		}
		
		return;
	}
	
	public function my_action_callback() {
		global $wpdb; // this is how you get access to the database
		
		echo "hello";
		$whatever = $_POST['data'];
	
		echo $whatever;
	
		exit(); // this is required to return a proper result & exit is faster than die();
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
