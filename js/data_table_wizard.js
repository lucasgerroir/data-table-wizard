(function(){
     angular.module('data_wizard_app',[])
    .controller('Controller', ['$scope', function($scope, $route) {
    	
    	$scope.form_data = php_vars["forms"];
		$scope.groups_data = php_vars["groups"];
		$scope.warnings = php_vars["warnings"];
		
		// add the option to not add a group to the table
		$scope.groups_data.push({name : "None", group_id : null });
		
		$scope.views = ["view1", "view2", "view3", "view4"];
		$scope.column_field_types = ["text", "dropdown", "textarea", "radio"];
    	
		$scope.column_count  = 1;
		

    	
		setInterval(function() {
		  	
	  		
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
          	
          	jQuery("#data-column").sortable({
          		stop: sortEventHandler
          	});
    	}
    	
    	var sortEventHandler = function(event, ui){
    		
    		var ordered_obj = [];
    			
    		jQuery("#data-column .column-header").each(function() {
    			var id = jQuery(this).data("id");
    			ordered_obj.push(id);
    		});
    		
    		$scope.columns_data = ordered_obj;
    	};
    	
    	$scope.add_value= function( id ) {
       	 	
    		var current_val  = $scope.values[id][$scope.values[id].length-1];
    		current_val++;
    		$scope.values[id].push(current_val);
    		
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
    	
    	$scope.input_value = function( id ) {
    		
    		$scope.defaults[id] = $scope.vals[id][1];
    		
    		if ($scope.values[1] && $scope.vals[1][1]) {
    			$scope.view_pass[2] = true;
    		}	
    	}
    	
    	$scope.remove_value = function( id, val_id ) {
    		
			// remove the column id from array
			var index = $scope.values[id].indexOf(val_id);
			$scope.values[id].splice(index, 1);
    		
    		delete $scope.vals[id][val_id];
    		
    	}
    	
    	$scope.selected_field_type = function(id) {
    		
    		//delete the previous choice values
    		delete $scope.values[id];
    		delete $scope.vals[id];
    		
    		var val_obj = [];
			val_obj.push(1);
			$scope.values[id] = val_obj;
    		
    	}
    	

    
    }]);
     

     
})();

jQuery(document).ready(function(){
	

	
	function init_app() {
		
		
		

	}
 
	jQuery('#add-data-table').click( function(){
		
		var controllerElement = document.querySelector('#data_table_wizard_module div');
		var $scope = angular.element(controllerElement).scope();
		
		$scope.$apply(function(){
			
	    	$scope.view_pass = { 1: false, 2: false, 3: false, 4: true };

			$scope.columns_data = [1];

			$scope.view = $scope.views[0];
			$scope.nextViewNumber = 1;
	    	
	    	
			$scope.selected_form = { "selected" : null };
			$scope.selected_group = { "selected" : null };
			
			$scope.selected_fields = {};
			$scope.values= {};
			$scope.vals = {};
			$scope.defaults = {};
			$scope.placeholder = {};
			
			$scope.column_titles = { 1 : "Column 1" };
			
		});
			
		
	});
	
});

	

