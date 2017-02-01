<?php


class data_table_wizard_plugin  {
	
	protected $wp_jdt;
	protected $post_id;
	protected $post_content;
	
	function __construct() {
		
		$this->wp_jdt = null;
		$this->post_id = null;
		$this->post_content = "";
	}
	
    
   public function activate() {
   	
    }
    
    public function deactivate() {
  	
    }
    
    protected function getMainPluginFileName() {
        return 'data_table_wizard_plugin.php';
    }

    public function addActionsAndFilters() {
		
       // script & style just for the options administration page
      if (is_admin()) {
      	
      	add_action('media_buttons', [&$this, 'add_wizard_button'], 15);
      	add_action('media_buttons', [&$this, 'add_wizard_app'], 14);
      	add_action('admin_menu', [&$this, 'get_external_plugin_info']);
      	add_action( 'wp_ajax_get_shotcode_data', [&$this,'get_shotcode_data'] );
      	
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
    			<div ng-controller="Controller" id="views">
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
	
	public function get_external_plugin_info() {
		
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
	
	public function set_data_tables_shortcode( ){
		
		$wp_jdt = self::get_shortcode('wp_jdt');
		
		return $wp_jdt[0];

	}
	
	public function get_group_data() {
		
		if (is_null($this->wp_jdt)) {
			return null;
		}
		
		$filterbygroup = self::get_attribute("filterbygroup", $this->wp_jdt);
		
		return $filterbygroup;
	}
	
	public function get_coumn_data() {
		
		
		if (is_null($this->wp_jdt)) {
			return null;
		}
		
		$edit = self::get_attribute("edit", $this->wp_jdt);
		
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
	

	public function get_gravity_forms_dir_shortcode() {
	
		$directory = self::get_shortcode('directory');
		
		if (is_null($directory)) {
			return null;
		}
			
		$form = self::get_attribute("form", $directory[0]);
		
		if ( method_exists  ( "RGFormsModel", "get_form" ))  {
			$form = RGFormsModel::get_form( intval ($form) );
			($form ==  false) ? NULL : $form;
		}
		
		return $form;
		
	}
	
	public function current_values() {
		
		$this->wp_jdt = self::set_data_tables_shortcode();
	
		$current_values = [];

		$current_values["added_columns"] = self::get_coumn_data(  );
		$current_values["form"] = self::get_gravity_forms_dir_shortcode(  );
		$current_values["filterbygroup"] = self::get_group_data(  );
	
		return $current_values;
		
	}
	
	public function get_attribute( $attr, $content ) {
		
		$search = '/' . $attr . '="([^"]+)"/';
		preg_match($search, $content, $matches);

		return $matches[1];
	}
	
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
	

	public function get_shotcode_data() {

		$this->post_id =  $_POST["post_id"];
		$this->post_content = urldecode ( $_POST["post_content"] );

		$current_values = self::current_values();
		echo json_encode($current_values);
	
		exit(); // this is required to return a proper result & exit is faster than die();
	}

}
