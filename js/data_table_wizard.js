(function(){
     angular.module('data_wizard_app',[])
    .controller('Controller', ['$scope', function($scope, $route) {
    
    	$scope.form_data = php_vars["forms"];
		$scope.groups_data = php_vars["groups"];
		$scope.warnings = php_vars["warnings"];
		$scope.url = php_vars["url"];
		
		// add the option to not add a group to the table
		if (php_vars["groups"]) {
			$scope.groups_data.push({name : "None", group_id : null });
		}
		
		$scope.views = ["view1", "view2", "view3", "view4"];
		$scope.column_field_types = [ 
		                             
              { "value" : "text",     "text" : "Single Line Text" },
              { "value" : "select",   "text" : "Drop Down"}, 
              { "value" : "textarea", "text" : "Paragraph Text"}, 
              { "value" : "radio",    "text" : "Radio Buttons" }
		                              
		 ];
    	
		$scope.column_count  = 1;
		

    	
		setInterval(function() {

		 	
			if (jQuery("#data-column").is(":visible") && !jQuery('#data-column').hasClass('ui-sortable') && jQuery("#data-column .column").length > 1) {
				
		      	jQuery("#data-column").sortable({
	          		stop: sortEventHandler
	          	});
			}
	  		
	  		if (jQuery(".feedback").is(":visible")) {
	  		    	 jQuery(".feedback").delay(1800).fadeOut();
	  		}
	  		   
	  		}, 1000);

		
    	$scope.nextView = function() { 
    		
    		if ($scope.nextViewNumber < $scope.views.length) {
    			
        		$scope.view = $scope.views[$scope.nextViewNumber];
        		$scope.nextViewNumber++;
    		}
    	}
    	
    	$scope.change_step = function ( num ) {
    		
    		$scope.nextViewNumber =  num + 1;
    		$scope.view = $scope.views[num];
    		
    	}
    	
    	$scope.passed_step = function( num ) {
    		
    		$scope.view_pass[num] = true;
    	}
    	
    	$scope.add_columns = function() {
       	 	
    		$scope.column_count++;
          	$scope.columns_data.push($scope.column_count);
          	$scope.column_titles[$scope.column_count] = "Column " + $scope.column_count;
          	
    	}
    	
    	var sortEventHandler = function(event, ui){
    		
    		var ordered_obj = [];
    		
    		jQuery("#data-column .column-header").each(function(i) {
    			
    			var id = jQuery(this).data("id");
    			ordered_obj.push(id);

    		});

    		$scope.columns_data = ordered_obj;
    	};
    	
    	$scope.add_value = function( id ) {
       	 	
    		var current_val  = $scope.values[id][$scope.values[id].length-1];
    		current_val++;
    		$scope.values[id].push(current_val);
    		
    	}
    	
    	$scope.input_value_blur = function(id, value_id){
    		
    		$scope.vals[id][value_id] = $scope.vals[id][value_id].replace(/\s/g, '')
    	}
    	
    	$scope.input_value = function( id, value_id ) {
    	
    		

    		$scope.vals[id][value_id].trim();
   
    		$scope.defaults[id] = $scope.vals[id][1];
    
    		if ($scope.values[1] && $scope.vals[1][1]) {
    			$scope.view_pass[2] = true;
    		}	
    	}
    	
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
        		
    		}
    	
    	}
    	
    	
    	$scope.remove_value = function( id, val_id ) {
    		
			// remove the column id from array
			var index = $scope.values[id].indexOf(val_id);
			$scope.values[id].splice(index, 1);
    		
    		delete $scope.vals[id][val_id];
    		
    	}
    	
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
    	
	  	$scope.insertShortcode = function(id) {
	  	  
	  	 var mce_content,
	  	 	 new_content,
	  	 	 shortcode = jQuery(".current-shortcode").text();
	  		
	  	  if (jQuery("#wp-content-wrap").hasClass("tmce-active")) {
	  		  mce_content = tinyMCE.activeEditor.getContent();
	  		  new_content = mce_content +  shortcode;
	  		  tinyMCE.activeEditor.setContent(new_content);

	      } else {
	    	  mce_content = jQuery('.wp-editor-area').val();
	    	  new_content = mce_content +  shortcode;
	    	  jQuery('.wp-editor-area').val(new_content);
	      }
	  	 
	  	  tb_remove();

	    }
    
    
    }]);
     
})();

jQuery(document).ready(function() {
	
	
	var controllerElement = document.querySelector('#data_table_wizard_module div');
	var $scope = angular.element(controllerElement).scope();
	var regex = php_vars["regex"];
	var current_values = php_vars["current_values"];

	jQuery('#add-data-table').click( function() {
		
		$scope.$apply(function() {
			
			var column_data = current_values["added_columns"];
			
			$scope.selected_fields = {};
			$scope.columns_data = [];
			$scope.column_titles = {};
			$scope.values = [];
			$scope.vals= {};
			$scope.selected_form = {};
			$scope.selected_group = {};
			
			if(column_data) {
				
				for (var index in column_data) { 
					
					var new_arr = [];
					$scope.columns_data.push(index);
					$scope.selected_fields[index] = column_data[index].field;
					$scope.column_titles[index] =  column_data[index].name;
					$scope.vals[index] = column_data[index].values;
				
					
					for (var id in column_data[index].values) { 
						new_arr.push(id);
					}
					
					$scope.values[index] = new_arr;
					
				}
				
			} else {
				$scope.columns_data.pish(1);
				$scope.column_titles = { 1 : "Column 1" };
			}
			
	    	$scope.view_pass = { 
    			1: (current_values["form"]) ? true : false, 
				2: true, 
				3: (current_values["filterbygroup"]) ? true : false,
				4: true 
			};
	    	
	    	$scope.columns_select = (current_values["added_columns"]) ? true : false;
			$scope.view = $scope.views[0];
			$scope.nextViewNumber = 1;
			$scope.selected_form["form"] = (current_values["form"]) ? current_values["form"].id : '';
			$scope.selected_group["group"] = (current_values["filterbygroup"]) ?  current_values["filterbygroup"] : '';
			
			
			
			$scope.defaults = {};
		
		});
			
		
	});
	

	
});

	

