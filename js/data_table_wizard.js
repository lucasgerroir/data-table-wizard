/*
 * Angularjs app for data tables wizard.
 */

(function(){
     angular.module('data_wizard_app',[])
    .controller('Controller', ['$scope', function($scope, $route) {
    
    	// add all the retrieved php info to the application scope.
    	$scope.form_data = php_vars["forms"];
		$scope.groups_data = php_vars["groups"];
		$scope.warnings = php_vars["warnings"];
		$scope.url = php_vars["url"];
		
		// add tool tips for each page here
		$scope.tool_tips = {
				view1 : "The table is built from gravity form entries. You have to select what form's entry data you want to display in the table. This is a list of all the forms created on your wordpress site.",
				view2 : "You can add and remove new columns. For each column you must select a field type. Based off of the field types you can create values.",
				view3 : "This is a list of all the groups created on this wordpress site. If you only want certain users to be able to view the table add them to a specific group and select it here.",
				view4 : 
				[   // add multiple per page by adding them in an array and specifying the index in the view. ex tool_tips['view4'][0]
				 	"This is a render of what the columns you have created will look like. The Gravity form's data will be displayed in columns beside this table",
				 	"You can click on the above steps to go back and change these values at any time."
				],
				bottom : "You can watch the shortcodes for the table be generated here."
		}
		
		// add the option to not add a group to the table
		if (php_vars["groups"]) {
			$scope.groups_data.push({name : "None", group_id : null });
		}
		
		// list of current views.
		$scope.views = ["view1", "view2", "view3", "view4"];
		
		// all the allowed field types with values and names.
		// if you want to add a new field type add it to the list.
		$scope.column_field_types = [ 
              { "value" : "text",     "text" : "Single Line Text" },
              { "value" : "select",   "text" : "Drop Down"}, 
              { "value" : "textarea", "text" : "Paragraph Text"}, 
              { "value" : "radio",    "text" : "Radio Buttons" }                    
		 ];
    	
		// amount of created columns
		$scope.column_count  = 1;
		
		// this is a timely check. This was necessary based on the way the views are setup.
		setInterval(function() {
			
			// trigger the ability to sort the columns. Only if on the view, there is more than one column and the event hasn't already been added.
			if (jQuery("#data-column").is(":visible") && !jQuery('#data-column').hasClass('ui-sortable') && jQuery("#data-column .column").length > 1) {
				
		      	jQuery("#data-column").sortable({
	          		stop: sortEventHandler
	          	});
			}
	  		
			// Fade out the feedback divs
	  		if (jQuery(".feedback").is(":visible")) {
	  		    	 jQuery(".feedback").delay(1800).fadeOut();
	  		}
	  		
	  		   
	  	}, 1000);
		
	   /*
		* Make sure the user has filled in all the column fields if they haven't stop them from going to the next step.
		*/
		$scope.form_feedback = function ( id , va_id ) {
		    
			// cycle through all inputs.
    		var val = true;
    		jQuery("#data-column").find("input").each(function() {
    		   
    		   var element = jQuery(this);
    		   var col_id = element.data("col");
    		   var value_id = element.data("val");
    		   
    		   // when removing make sure we are not looking at the removed inputs. 
    		   if (id + "." + va_id == value_id || !va_id && col_id == id) {
    			   return false;
    		   }    
    		   // an input is empty.	
			   if (element.val() == "") {
				   val = false;
			   }
	
    		});
    		
    		// disable the user from going to the next step.
    		if (val) {
    			$scope.view_pass[2] = true;
    		} else {
    			$scope.view_pass[2] = false;
    		}

    	}
		

	   /*
		* When clicking on the main step button get the next view.
		*/
    	$scope.nextView = function() { 
    		
    		// if the view is render view get all the entries.
    		if ($scope.nextViewNumber == 3) {
    			$scope.form_name = $scope.form_data.filter(function(obj){ return (obj.id==$scope.selected_form.form); })[0].title;
    		}
    		
    		// check if its the last view.
    		if ($scope.nextViewNumber < $scope.views.length) {
    			
    			// set the view to the new view.
        		$scope.view = $scope.views[$scope.nextViewNumber];
        		$scope.nextViewNumber++;
    		}
    	}
    	
       /*
		* When clicking on a step shown at the top of module go to that view.
		*/
    	$scope.change_step = function ( num ) {
    		
    		$scope.nextViewNumber =  num + 1;
    		$scope.view = $scope.views[num];
    		
    	}
    	
       /*
		* Tell the module the user has chosen a necessary field and can go to the next one. 
		*/
    	$scope.passed_step = function( num ) {
    		
    		$scope.view_pass[num] = true;
    	}
    	
       /*
		* When clicking on the add colum button add a new column id.
		*/
    	$scope.add_columns = function() {
       	 	
    		$scope.column_count++;
          	$scope.columns_data.push($scope.column_count);
          	// add a default column title based on the id.
          	$scope.column_titles[$scope.column_count] = "Column " + $scope.column_count;
          	
          	$scope.view_pass[2] = false;
          	
    	}
    	
       /*
		* This function is triggered once the user has dropped the column into place.
		*/
    	var sortEventHandler = function(event, ui) {
    		
    		var ordered_obj = [];
    		
    		jQuery("#data-column .column-header").each(function(i) {
    			
    			var id = jQuery(this).data("id");
    			ordered_obj.push(id);

    		});
    		
    		// reorder the column ids based on the users choice.
    		$scope.columns_data = ordered_obj;
    	};
    	
       /*
		* Add a inputted value into the values array
		*/
    	$scope.add_value = function( id ) {
       	 	
    		var current_val  = $scope.values[id][$scope.values[id].length-1];
    		current_val++;
    		$scope.values[id].push(current_val);
    		
    		$scope.view_pass[2] = false;
    		
    	}
    	
       /*
		* This was added because values cannot currently have spaces. When the input is blurred
		* remove the spaces so it can be added to the shortcode. When spaces are allowed this function 
		* can be removed. As well the function call on the inputs in view 2 need to be removed.
		*/
    	$scope.input_value_blur = function(id, value_id){
    
    		if ($scope.vals[id] && $scope.vals[id][value_id]) {
    			$scope.vals[id][value_id] = $scope.vals[id][value_id].replace(/\s/g, '');
    			$scope.defaults[id] = $scope.vals[id][1];
    		}
    	}
    	
       /*
		* when user inputs a value into the values field add it to the values object.
		*/
    	$scope.input_value = function( id, value_id ) {
    	
    		if ($scope.vals[id][value_id]  != "") {
        		
    			$scope.vals[id][value_id].trim();   
        		$scope.defaults[id] = $scope.vals[id][1];
    		}
    		
    		$scope.form_feedback(0);
    	
    	}
    	

    	
       /*
		* When remove button is pushed on column, remove that column and delete all its data.
		*/
    	$scope.remove_columns = function( id ) {
    		
    		// make sure there is at least one column.
    		if ($scope.columns_data.length > 1) {
    			
    			// remove the column id from array
    			var index = $scope.columns_data.indexOf(id);
        		$scope.columns_data.splice(index, 1);
        		
        		delete $scope.column_titles[id];
        		
        		// remove the id from the fields object
        		delete $scope.selected_fields[id]; 
        		
        		// remove values
        		delete $scope.values[id];
        		
        		// remove values
        		delete $scope.vals[id];
        		
        		$scope.form_feedback( id );
        		
    		}
    	
    	}
    	
       /*
		* When remove button is pushed beside value inputs remove value from values object.
		*/
    	$scope.remove_value = function( id, val_id ) {
    		
			// remove the column id from array
			var index = $scope.values[id].indexOf(val_id);
			$scope.values[id].splice(index, 1);
    		
    		delete $scope.vals[id][val_id];
    		
    		$scope.form_feedback(id, val_id);
    		
    	}
    	
       /*
		* When you select a field type remove the previous field values.
		*/
    	$scope.selected_field_type = function(id) {
    		
    		// the user has chosen to add a column
    		$scope.columns_select  = true;
    		
    		//delete the previous choice values
    		delete $scope.values[id];
    		delete $scope.vals[id];
    		
    		var val_obj = [];
			val_obj.push(1);
			$scope.values[id] = val_obj;
			
    		
    	}
    	
       /*
		* Inserts the shortcode into the current content and closes the thickbox window.
		*/
	  	$scope.insertShortcode = function(id) {
	  	  
	  	 var mce_content,
	  	 	 new_content,
	  	 	 shortcode = jQuery(".current-shortcode").text();
	  	
	  	  
	  	  if (jQuery("#wp-content-wrap").hasClass("tmce-active")) {
	  		  
	  		  // if the user is on the visual editor.
	  		  mce_content = tinyMCE.activeEditor.getContent();
	  		  new_content = mce_content +  shortcode;
	  		  tinyMCE.activeEditor.setContent(new_content);

	      } else {
	    	  
	    	  //if thr user is on the text editor.
	    	  mce_content = jQuery('.wp-editor-area').val();
	    	  new_content = mce_content +  shortcode;
	    	  jQuery('.wp-editor-area').val(new_content);
	      }
	  	 
	  	  // close the thickbox.
	  	  tb_remove();

	    }
    
    }]);
     
})();

/*
* When the document is ready initalize the default settings.
*/
jQuery(document).ready(function() {
	
	var controllerElement = document.querySelector('#data_table_wizard_module div');
	
	// get the controllers scope object.
	var $scope = angular.element(controllerElement).scope();
	
	// get the current post id.
	var id_post = jQuery("#post_ID").attr('value');
	
	// triggered when the Review Table Button is pressed.
	jQuery('#add-data-table').click( function() {
		
		// get the current content for the ajax call.
    	var mce_content = (jQuery("#wp-content-wrap").hasClass("tmce-active")) ? tinyMCE.activeEditor.getContent() :  mce_content = jQuery('.wp-editor-area').val();

    	// hide the view content so it loads in nicely after ajax call.
    	jQuery("#views").hide();
		
    	// triggered when ajax request is successful. Sets all the setting values.
    	init_module = function(data) {
   
    		// apply these setting values to the controller scope.
	    	$scope.$apply(function() {
	    			
	    			var column_data = data["added_columns"];
	    			
	    			// define scope variables
	    			$scope.selected_fields = {};
	    			$scope.form_entries = {};
	    			$scope.columns_data = [];
	    			$scope.column_titles = {};
	    			$scope.values = [];
	    			$scope.vals= {};
	    			$scope.selected_form = {};
	    			$scope.selected_group = {};
	    			$scope.defaults = {};
	    			
	    			// if there is column data from previous shortcodes set it to the setting values.
	    			if(column_data) {
	    				
	    				// cycle through content shortcodes column data
	    				for (var index in column_data) { 
	    					
	    					var new_arr = [];
	    					
	    					// add column id
	    					$scope.columns_data.push(index);
	    					
	    					// add column field
	    					$scope.selected_fields[index] = column_data[index].field;
	    					
	    					// add column name
	    					$scope.column_titles[index] =  column_data[index].name;
	    					
	    					// add column values
	    					$scope.vals[index] = column_data[index].values;
	    					
	    					// right now defaults are not stored on shortcode so I set each default to the first value
	    					$scope.defaults[index] = column_data[index]["default"];
	    							
	    					for (var id in column_data[index].values) { 
	    						// this is for values id.
	    						new_arr.push(id);
	    					}
	    					
	    					$scope.values[index] = new_arr;
	    				}
	    				
	    			} else {
	    				
	    				$scope.columns_data.push(1);
	    				$scope.column_titles = { 1 : "Column 1" };
	    			}
	    			
	    			// set what steps the user is allowed to pass.
	    	    	$scope.view_pass = { 
	        			1: (data["form"]) ? true : false, 
	    				2: (column_data) ? true : false,
	    				3: (data["filterbygroup"]) ? true : false,
	    				4: true 
	    			};
	    	    	
	    	
	    	    	$scope.columns_select = (data["added_columns"]) ? true : false;
	    			$scope.view = $scope.views[0];
	    			$scope.nextViewNumber = 1;
	    			
	    			// form chosen.
	    			$scope.form_name = (data["form"]) ? data["form"].title : '';
	    			$scope.selected_form["form"] = (data["form"]) ? data["form"].id : '';
	    			
	    			// group chosen.
	    			$scope.selected_group["group"] = (data["filterbygroup"]) ?  data["filterbygroup"] : '';

	    		});
	    		
	    		// once the ajax call is complete fade content in
	    		jQuery("#views").fadeIn();
    		}
		
    	// ajax call to parse content for previous shotcodes.
    	jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'get_shotcode_data',
                post_id: id_post,
                post_content: encodeURIComponent(mce_content)
            },
            success: function(data) {
            	data = JSON.parse(data);
            	init_module(data)
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("Error with retrieving column data: " + errorThrown);
            }
        });
    	
		
	});
	

	
});

	

