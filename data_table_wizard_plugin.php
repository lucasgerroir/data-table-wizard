<?php

/*
 * Main class for data table wizard plugin. This class builds the data table wizard on the admin side.
 */
class data_table_wizard_plugin  {
	
	// A few global varaibles that are set on ajax call.
	protected $wp_jdt;
	protected $post_id;
	protected $post_content;
	
   /*
	* Constructor.
	*/
	function __construct() {
		
		// set global variables
		$this->wp_jdt = null;
		$this->post_id = null;
		$this->post_content = "";
	}
	
  /*
   * Activation hook.
   */
   public function activate() {
   	
    }
    
   /*
    * Deactivation hook.
    */
    public function deactivate() {
  	
    }
    
   /*
    * Main plugin file name.
    */
    protected function getMainPluginFileName() {
        return 'data_table_wizard_plugin.php';
    }

   /*
    * Add all the action and filters necessary for plugin.
    */
    public function addActionsAndFilters() {
		
       // script & style just for the options administration page
      if (is_admin()) {
      	
      	add_action('media_buttons', [&$this, 'add_wizard_button'], 15);
      	add_action('media_buttons', [&$this, 'add_wizard_app'], 14);
      	add_action('admin_menu', [&$this, 'get_external_plugin_info']);
      	add_action( 'wp_ajax_get_shotcode_data', [&$this,'get_shotcode_data'] );
      	add_action( 'wp_ajax_get_form_data', [&$this,'get_form_data'] );
      	
      }
               
    }
     
   /*
    * Initiate Wordpress Thickbox.
    */
    public function add_Thickbox() {
    	
    	add_thickbox();
	}
    
   /*
	* Register all the scripts necessary for this plugin.
	*/
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
    			// css
    			wp_enqueue_style( 'data_table_wizard_css', DTW_DIR_URL . '/css/data_table_wizard.css');
    			wp_enqueue_style( 'data_table_wizard_css', DTW_DIR_URL . '/css/font-awesome.min.css');
    			
 
    		}

    	}
    	
    }
    
   /*
    * Create the html triggered by thickbox. As well create the html that the angularjs app is initiated  on. 
    * Add the controller for the app to the main views div. Include only the current view. All views are found in
    * the partial directory.
    */
    public function add_wizard_app() {
    	
    	echo '
    			<div id="data_table_wizard_module"  style="display:none;" ng-app="data_wizard_app" >
    			<div ng-controller="Controller" id="views">
	                	<div ng-if="view == \'view1\'" ng-include="\'' . DTW_DIR_URL . '/partial/view1.html\'"></div>
	                	<div ng-if="view == \'view2\'" ng-include="\'' . DTW_DIR_URL . '/partial/view2.html\'"></div>
	                	<div ng-if="view == \'view3\'" ng-include="\'' . DTW_DIR_URL . '/partial/view3.html\'"></div>
	                	<div ng-if="view == \'view4\'" ng-include="\'' . DTW_DIR_URL . '/partial/view4.html\'"></div>
	               <div class="bottom-half" ng-include="\'' . DTW_DIR_URL . '/partial/bottom.html\'"></div>
    			</div>
    	      </div>';
    	
    }

   /*
    * Generate the button to be included beside the add media button. This button triggers the thickbox module.
    */
    public function add_wizard_button() {
    	
    	echo '<a href="#TB_inline?width=800&height=550&inlineId=data_table_wizard_module" title="Data Table Wizard" id="add-data-table" class="button thickbox"> <i class="fa fa-table" aria-hidden="true"></i> Review Table </a>';
	}
	
   /*
	* Get the data from the external plugins such as gravity forms and groups.
	*/
	public function get_external_plugin_info() {
		
		$data = [];
		
		
		if ( method_exists  ( "RGFormsModel", "get_forms" ))  {
			
			// get all the forms.
			$forms = RGFormsModel::get_forms( true );
			// if there are no forms tell user to create one.
			$warning["forms"] = (empty($forms)) ? "create" : NULL;
		
		} else {
			
			$forms = NULL;
			// if gravity forms is not activated tell them to do so.
			$warning["forms"] = "activate";
		}
		
		// get all the groups
		if ( method_exists ( "Groups_Group", "get_groups") ) {
			
			// get all the forms.
			$groups = Groups_Group::get_groups();
			// if there are no groups tell user to create one.
			$warning["groups"] = (empty($groups)) ? "create" : NULL;
			
		} else {
			
			$groups = NULL;
			// if the groups plugin is not activated tell them to do so.
			$warning["groups"] = "activate";
		}
		
		// for all the links in the module.
		$url = admin_url();
		
		// setup the data for javascript.
		$data["forms"] = $forms;
		$data["groups"] = $groups;
		$data["warnings"] = $warning;
		$data["url"] = $url;

		// pass  the data for use in data table wizard js.
		wp_localize_script( 'data_table_wizard', 'php_vars', $data );
	}
	
   /*
	* Set the information retreived from regexing content
	* @return array  $wp_jdt  an array of strings which include the found shortcode
	*/
	public function set_data_tables_shortcode( ){
		
		$wp_jdt = self::get_shortcode('wp_jdt');
		
		return $wp_jdt[0];

	}
	
   /*
	* Get the filterby group attribute on shortcode.
	* @return string  $filterbygroup  the group the user had selected to allow access to the table.
	*/
	public function get_group_data() {
		
		if (is_null($this->wp_jdt)) {
			return null;
		}
		
		$filterbygroup = self::get_attribute("filterbygroup", $this->wp_jdt);
		
		return $filterbygroup;
	}
	
   /*
	* Get all the set column data attached to the wp data tables shortcode.
	* @return string  $column_struct  the column information.
	*/
	public function get_coumn_data() {
		
		
		if (is_null($this->wp_jdt)) {
			return null;
		}
		
		// get the edit attribute information
		$edit = self::get_attribute("edit", $this->wp_jdt);
		
		$columns = explode(",", $edit);
		
		// cycle through all the columns
		if (is_array($columns)) {
		
			$column_struct = [];
			$id = 1;
				
			foreach ($columns as $column) {
		
				$each_col_attr = explode("|", $column);
				
				// the name of the set column.
				$column_struct[$id]["name"] = $each_col_attr[0];
				
				// the direction, at this point not really important.
				$column_struct[$id]["direction"] = $each_col_attr[1];
				
				// get all the values for the column 
				$values = explode("*", $each_col_attr[2]);
				
				// the field type chosen for this column
				$column_struct[$id]["field"] = $values[0];
				
				// added this because currently text don't have values
				// when values are added to text remove this if statement
				if ($values[0] == "text") {
					
					$obj = (object) array('1' => 'value1');
					$column_struct[$id]["values"] = $obj;
				} else {
					
					unset($values[0]);
					$column_struct[$id]["values"] = $values;
				}
			
				$id++;
			}
		}
		
		return $column_struct;
		
	}
	
   /*
	* Get the chosen gravity form information
	* @return obj $form   all information on the chosen form.
	*/
	public function get_gravity_forms_dir_shortcode() {
	
		// regex the directory plugin.
		$directory = self::get_shortcode('directory');
		
		if (is_null($directory)) {
			return null;
		}
		
		// get the form id from the form attribute.
		$form = self::get_attribute("form", $directory[0]);
		
		if ( method_exists  ( "RGFormsModel", "get_form" ))  {
			
			// get all the other information stored about the form
			$form = RGFormsModel::get_form( intval ($form) );
			($form ==  false) ? NULL : $form;
		}
		
		return $form;
		
	}
	
   /*
	* Setup an array with all the shorcode data.
	* @return array $current_values   all information on the contents current shortcodes.
	*/
	public function current_values() {
		
		$this->wp_jdt = self::set_data_tables_shortcode();
	
		$current_values = [];

		$current_values["added_columns"] = self::get_coumn_data(  );
		$current_values["form"] = self::get_gravity_forms_dir_shortcode(  );
		$current_values["filterbygroup"] = self::get_group_data(  );
	
		return $current_values;
		
	}
	
   /*
	* Regex the shortcode to get the attribute.
	* @return array $matches  all the found attributes.
	*/
	public function get_attribute( $attr, $content ) {
		
		$search = '/' . $attr . '="([^"]+)"/';
		preg_match($search, $content, $matches);

		return $matches[1];
	}
	
   /*
	* Parse the content for specific shortcodes.
	* @return array $attr the string of found shortcodes.
	*/
	public function get_shortcode( $shortcode ) {
		
		$pattern = get_shortcode_regex();
		
		if (empty($this->post_content)) {
			
			$post_content = get_post($this->post_id);
			$content = $post_content->post_content;
		
		} else {
			$content = $this->post_content;
		}
		
		preg_match_all("/$pattern/", $content, $matches);
		
		$lm_shortcode = array_keys($matches[2], $shortcode);
		
		if (!empty($lm_shortcode)) {
			
			foreach($lm_shortcode as $sc) {
				$attr[] = $matches[3][$sc];
			}
			return $attr;
		}
		
		return null;
	}
	
   /*
	* AJAX call to get the entry infromation based on the chosen gravity form. 
	* @return object  $data on the field labels and entries for the form.
	*/
	public function get_form_data() {
		
		$data = [];
		
		// current chosen form id
		$form_id = $_POST["form_id"];
		
		$data["entries"] = GFAPI::get_entries($form_id);
		$data["fields"] = RGFormsModel::get_form_meta($form_id)["fields"];
	
		echo json_encode($data);
		
		exit(); 
	}
	
   /*
	* AJAX call to get the shortcode information from current page/post content.
	* @return object  $current_values  all the current data from the content shortcodes.
	*/
	public function get_shotcode_data() {
		
		// post currently being edited.
		$this->post_id =  $_POST["post_id"];
		// current post content.
		$this->post_content = urldecode ( $_POST["post_content"] );

		$current_values = self::current_values();
		echo json_encode($current_values);
	
		exit(); // this is required to return a proper result & exit is faster than die();
	}

}
